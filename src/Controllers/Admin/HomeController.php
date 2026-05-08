<?php



namespace LARAVEL\Controllers\Admin;

use Illuminate\Http\Request;
use LARAVEL\Models\CounterModel;
use LARAVEL\Models\OrderStatusModel;
use LARAVEL\Models\OrdersModel;
use Carbon\Carbon;
use DB;

class HomeController
{
    public function index(Request $request)
    {
        if ((isset($request->month) && $request->month != '') && (isset($request->year) && $request->year != '')) {
            $time = $request->year . '-' . $request->month . '-1';
            $date = strtotime($time);
        } else {
            $date = strtotime(date('y-m-d'));
        }
        $day = date('d', $date);
        $month = date('m', $date);
        $year = date('Y', $date);
        $firstDay = mktime(0, 0, 0, $month, 1, $year);
        $dayOfWeek = date('D', $firstDay);
        $daysInMonth = cal_days_in_month(0, $month, $year);
        $timestamp = strtotime('next Sunday');
        $weekDays = array();
        /* Make data for js chart */
        $charts = array();
        $charts['month'] = $month;
        $startDate = Carbon::create($year, $month, 1, 0, 0, 0)->timestamp;
        $endDate = Carbon::create($year, $month, 28, 23, 59, 59)->timestamp;
        // Truy vấn duy nhất lấy tổng số lượt truy cập theo từng ngày trong tháng
        $records = CounterModel::selectRaw('DATE(FROM_UNIXTIME(tm)) as date, COUNT(*) as total')
            ->whereBetween('tm', [$startDate, $endDate])
            ->groupBy('date')
            ->pluck('total', 'date');

        // Tạo dữ liệu cho biểu đồ
        $charts = ['series' => [], 'labels' => []];
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $date = Carbon::create($year, $month, $i)->toDateString(); // Tạo chuỗi ngày: YYYY-MM-DD
            $charts['series'][] = $records[$date] ?? 0; // Lấy dữ liệu hoặc mặc định là 0
            $charts['labels'][] = 'D' . $i;
        }
        $quickOrderStats = $this->buildQuickOrderStats();
        $orderDashboardUrl = url('admin', ['com' => 'order', 'act' => 'man', 'type' => 'don-hang']);

        return view('index.index', compact(
            'charts',
            'day',
            'month',
            'year',
            'quickOrderStats',
            'orderDashboardUrl'
        ));
    }

    private function buildQuickOrderStats(): array
    {
        $now = Carbon::now();
        $todayStart = $now->copy()->startOfDay();
        $todayEnd = $now->copy()->endOfDay();
        $weekStart = $now->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $weekEnd = $now->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();
        $monthStart = $now->copy()->startOfMonth()->startOfDay();
        $monthEnd = $now->copy()->endOfMonth()->endOfDay();

        $monthScopedQuery = function () use ($monthStart, $monthEnd) {
            return OrdersModel::query()->whereBetween('created_at', [
                $monthStart->toDateTimeString(),
                $monthEnd->toDateTimeString()
            ]);
        };

        $stats = [
            'today' => (int) OrdersModel::whereBetween('created_at', [
                $todayStart->toDateTimeString(),
                $todayEnd->toDateTimeString()
            ])->count(),
            'week' => (int) OrdersModel::whereBetween('created_at', [
                $weekStart->toDateTimeString(),
                $weekEnd->toDateTimeString()
            ])->count(),
            'month' => (int) $monthScopedQuery()->count(),
            'month_revenue' => (float) $monthScopedQuery()->sum('total_price'),
            'pending_confirm' => 0,
            'processing_packing' => 0,
            'shipping' => 0,
            'delivered' => 0,
            'canceled' => 0,
            'need_attention' => 0,
            'delivery_rate' => 0.0,
            'month_diff' => 0,
            'updated_at' => $now->format('d/m/Y H:i'),
        ];

        $statusGroups = $this->resolveOrderQuickStatStatusGroups();
        foreach ($statusGroups as $key => $ids) {
            if (isset($stats[$key]) && !empty($ids)) {
                $stats[$key] = (int) $monthScopedQuery()->whereIn('order_status', $ids)->count();
            }
        }

        $stats['need_attention'] = (int) ($stats['pending_confirm'] + $stats['processing_packing'] + $stats['shipping']);
        $stats['delivery_rate'] = $stats['month'] > 0
            ? round(((float) $stats['delivered'] * 100) / (float) $stats['month'], 1)
            : 0.0;

        $previousMonthStart = $now->copy()->subMonthNoOverflow()->startOfMonth()->startOfDay();
        $previousMonthEnd = $now->copy()->subMonthNoOverflow()->endOfMonth()->endOfDay();
        $previousMonthTotal = (int) OrdersModel::whereBetween('created_at', [
            $previousMonthStart->toDateTimeString(),
            $previousMonthEnd->toDateTimeString()
        ])->count();
        $stats['month_diff'] = (int) ($stats['month'] - $previousMonthTotal);

        return $stats;
    }

    private function resolveOrderQuickStatStatusGroups(): array
    {
        $groups = [
            'pending_confirm' => [],
            'processing_packing' => [],
            'shipping' => [],
            'delivered' => [],
            'canceled' => [],
        ];

        $statuses = OrderStatusModel::select('id', 'namevi')->get();
        $allStatusIds = $statuses->pluck('id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->filter(function ($id) {
                return $id > 0;
            })
            ->values()
            ->all();

        foreach ($statuses as $status) {
            $statusId = (int) ($status->id ?? 0);
            if ($statusId <= 0) continue;

            $bucket = $this->resolveOrderQuickStatBucket((string) ($status->namevi ?? ''));
            if ($bucket !== '' && isset($groups[$bucket])) {
                $groups[$bucket][] = $statusId;
            }
        }

        $fallbackById = [
            'pending_confirm' => 1,
            'processing_packing' => 2,
            'shipping' => 3,
            'delivered' => 4,
            'canceled' => 5,
        ];

        foreach ($fallbackById as $groupKey => $statusId) {
            if (empty($groups[$groupKey]) && in_array($statusId, $allStatusIds, true)) {
                $groups[$groupKey][] = $statusId;
            }
        }

        foreach ($groups as $groupKey => $ids) {
            $groups[$groupKey] = array_values(array_unique(array_filter(array_map('intval', (array) $ids), function ($id) {
                return $id > 0;
            })));
        }

        return $groups;
    }

    private function resolveOrderQuickStatBucket(string $statusName = ''): string
    {
        $normalized = $this->normalizeOrderStatusName($statusName);
        if ($normalized === '') return '';

        if ($this->containsStatusKeywords($normalized, ['huy', 'cancel'])) {
            return 'canceled';
        }

        if ($this->containsStatusKeywords($normalized, ['giao thanh cong', 'da giao', 'delivered', 'hoan tat', 'completed'])) {
            return 'delivered';
        }

        if ($this->containsStatusKeywords($normalized, ['dang giao', 'van chuyen', 'shipping', 'giao hang', 'delivery'])) {
            return 'shipping';
        }

        if ($this->containsStatusKeywords($normalized, ['dang xu ly', 'xu ly', 'dong goi', 'processing', 'packing', 'xac nhan', 'confirmed'])) {
            return 'processing_packing';
        }

        if ($this->containsStatusKeywords($normalized, ['cho xac nhan', 'moi dat', 'pending', 'new', 'awaiting'])) {
            return 'pending_confirm';
        }

        return '';
    }

    private function normalizeOrderStatusName(string $value = ''): string
    {
        $value = mb_strtolower(trim($value), 'UTF-8');
        if ($value === '') return '';

        if (function_exists('iconv')) {
            $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
            if (!empty($ascii)) {
                $value = strtolower((string) $ascii);
            }
        }

        $value = preg_replace('/[^a-z0-9]+/', ' ', (string) $value);
        $value = preg_replace('/\s+/', ' ', (string) $value);

        return trim((string) $value);
    }

    private function containsStatusKeywords(string $normalizedStatus, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            $keyword = trim((string) $keyword);
            if ($keyword !== '' && str_contains($normalizedStatus, $keyword)) {
                return true;
            }
        }

        return false;
    }
}
