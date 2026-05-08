<?php



namespace LARAVEL\Controllers\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use LARAVEL\Core\Support\Facades\File;
use LARAVEL\Models\OrdersModel;
use LARAVEL\Models\OrderHistoryModel;
use LARAVEL\Models\OrderStatusModel;
use LARAVEL\Models\ProductPropertiesModel;
use LARAVEL\Models\SettingModel;
use LARAVEL\Models\UserModel;
use LARAVEL\Models\Place\CityModel;
use LARAVEL\Models\Place\WardModel;
use LARAVEL\Core\Support\Facades\Auth;
use LARAVEL\Core\Support\Facades\Func;
use LARAVEL\Core\Support\Facades\Flash;
use LARAVEL\Core\Support\Facades\Validator;
use LARAVEL\Traits\TraitOrderInventory;
use LARAVEL\Traits\TraitSave;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Helper\Sample;

class OrderController
{
    use TraitSave, TraitOrderInventory;
    public function man($com, $act, $type, Request $request)
    {
        if ($request->isMethod('post') && (int) $request->input('dashboard_target_submit', 0) === 1) {
            return $this->handleDashboardTargetSave($com, $act, $type, $request);
        }

        $order_status = (isset($request->order_status)) ? htmlspecialchars($request->order_status) : 0;
        $order_payment = (isset($request->order_payment)) ? htmlspecialchars($request->order_payment) : 0;
        $order_date = (isset($request->order_date)) ? htmlspecialchars($request->order_date) : 0;
        $price_from = (isset($request->price_from)) ? htmlspecialchars($request->price_from) : 0;
        $price_to = (isset($request->price_to)) ? htmlspecialchars($request->price_to) : 0;
        $city = (isset($request->id_city)) ? htmlspecialchars($request->id_city) : 0;
        $district = (isset($request->id_district)) ? htmlspecialchars($request->id_district) : 0;
        $ward = (isset($request->id_ward)) ? htmlspecialchars($request->id_ward) : 0;
        $keyword = (isset($request->keyword)) ? htmlspecialchars($request->keyword) : '';
        $keyword_code = (isset($request->keyword_code)) ? htmlspecialchars($request->keyword_code) : '';
        $keyword_customer = (isset($request->keyword_customer)) ? htmlspecialchars($request->keyword_customer) : '';

        $query = OrdersModel::selectRaw('id,numb,code,created_at,order_payment,total_price,order_status')
            ->selectRaw('JSON_UNQUOTE(JSON_EXTRACT(info_user, "$.fullname")) AS fullname')
            ->selectRaw('JSON_UNQUOTE(JSON_EXTRACT(info_user, "$.email")) AS email')
            ->selectRaw('JSON_UNQUOTE(JSON_EXTRACT(info_user, "$.phone")) AS phone')
            ->where('id', '<>', 0);

        if (!empty($order_status)) $query->where('order_status', $order_status);
        if (!empty($order_payment)) $query->where('order_payment', $order_payment);

        if (!empty($order_date)) {
            $orderDateParts = array_values(array_filter(array_map('trim', explode(' to ', (string) $order_date))));
            try {
                if (count($orderDateParts) >= 2) {
                    $date_from = Carbon::createFromFormat('d/m/Y H:i:s', $orderDateParts[0] . ' 00:00:00')->toDateTimeString();
                    $date_to = Carbon::createFromFormat('d/m/Y H:i:s', $orderDateParts[1] . ' 23:59:59')->toDateTimeString();
                    $query->where('created_at', '<=', $date_to);
                    $query->where('created_at', '>=', $date_from);
                } elseif (count($orderDateParts) === 1) {
                    $singleDateFrom = Carbon::createFromFormat('d/m/Y H:i:s', $orderDateParts[0] . ' 00:00:00')->toDateTimeString();
                    $singleDateTo = Carbon::createFromFormat('d/m/Y H:i:s', $orderDateParts[0] . ' 23:59:59')->toDateTimeString();
                    $query->where('created_at', '<=', $singleDateTo);
                    $query->where('created_at', '>=', $singleDateFrom);
                }
            } catch (\Throwable $e) {
            }
        }

        if (!empty($price_from) && !empty($price_to)) {
            $query->where('total_price', '<=', $price_to);
            $query->where('total_price', '>=', $price_from);
        }


        if (!empty($city)) $query->whereRaw('JSON_UNQUOTE(JSON_EXTRACT(info_user, "$.city")) = ?', array($city));
        if (!empty($district)) $query->whereRaw('JSON_UNQUOTE(JSON_EXTRACT(info_user, "$.district")) = ?', array($district));
        if (!empty($ward)) $query->whereRaw('JSON_UNQUOTE(JSON_EXTRACT(info_user, "$.ward")) = ?', array($ward));


        if (!empty($keyword_code)) {
            $query->where('code', 'LIKE', '%' . $keyword_code . '%');
        }

        if (!empty($keyword_customer)) {
            $query->where(function ($query) use ($keyword_customer) {
                $query->whereRaw('JSON_UNQUOTE(JSON_EXTRACT(info_user, "$.fullname")) LIKE ?', ['%' . $keyword_customer . '%'])
                    ->orWhereRaw('JSON_UNQUOTE(JSON_EXTRACT(info_user, "$.phone")) LIKE ?', ['%' . $keyword_customer . '%']);
            });
        }

        if (empty($keyword_code) && empty($keyword_customer) && !empty($keyword)) {
            $query->where(function ($query) use ($keyword) {
                $query->where('code', 'LIKE', '%' . $keyword . '%')
                    ->orWhereRaw('JSON_UNQUOTE(JSON_EXTRACT(info_user, "$.email")) LIKE ?', ['%' . $keyword . '%'])
                    ->orWhereRaw('JSON_UNQUOTE(JSON_EXTRACT(info_user, "$.fullname")) LIKE ?', ['%' . $keyword . '%'])
                    ->orWhereRaw('JSON_UNQUOTE(JSON_EXTRACT(info_user, "$.phone")) LIKE ?', ['%' . $keyword . '%']);
            });
        }

        $items = $query->orderBy('numb', 'asc')
            ->orderBy('id', 'desc')
            ->paginate(10);

        $statusIds = $items->getCollection()
            ->pluck('order_status')
            ->map(function ($id) {
                return (int) $id;
            })
            ->filter()
            ->unique()
            ->values()
            ->all();

        $statusNameMap = [];
        if (!empty($statusIds)) {
            $statusNameMap = OrderStatusModel::select('id', 'namevi')
                ->whereIn('id', $statusIds)
                ->get()
                ->pluck('namevi', 'id')
                ->map(function ($name) {
                    return trim((string) $name);
                })
                ->toArray();
        }

        $newOrderThreshold = Carbon::now()->subDay();
        $items->setCollection(
            $items->getCollection()->map(function ($row) use ($statusNameMap, $newOrderThreshold) {
                $createdAtRaw = trim((string) ($row['created_at'] ?? ''));
                $createdAtObj = null;
                if ($createdAtRaw !== '') {
                    try {
                        $createdAtObj = Carbon::createFromFormat('Y-m-d H:i:s', $createdAtRaw);
                    } catch (\Throwable $e) {
                        try {
                            $createdAtObj = Carbon::parse($createdAtRaw);
                        } catch (\Throwable $e) {
                            $createdAtObj = null;
                        }
                    }
                }

                $statusId = (int) ($row['order_status'] ?? 0);
                $orderStatusName = trim((string) ($statusNameMap[$statusId] ?? ''));
                if ($orderStatusName === '') {
                    $orderStatusName = trim((string) Func::showName('order_status', $statusId, 'namevi'));
                }

                $statusForCheck = mb_strtolower($orderStatusName, 'UTF-8');
                $isCanceledStatus = str_contains($statusForCheck, 'hủy') ||
                    str_contains($statusForCheck, 'huy') ||
                    str_contains($statusForCheck, 'cancel');
                $isNewOrder = !$isCanceledStatus && !empty($createdAtObj) &&
                    $createdAtObj->greaterThanOrEqualTo($newOrderThreshold);

                $row['created_at_display'] = !empty($createdAtObj) ? $createdAtObj->format('d/m/Y H:i:s') : '-';
                $row['order_status_name'] = $orderStatusName;
                $row['is_new_order'] = $isNewOrder;

                return $row;
            })
        );

        $orderQuickStats = $this->buildOrderQuickStats();
        $dashboardFilters = $this->resolveDashboardFilters($request);
        $dashboardData = $this->buildDashboardData($type, $dashboardFilters);

        return view('order.man.man', [
            'items' => $items,
            'orderQuickStats' => $orderQuickStats,
            'dashboardFilters' => $dashboardFilters,
            'dashboardData' => $dashboardData,
        ]);
    }

    private function resolveDashboardFilters(Request $request): array
    {
        $allowedTabs = ['list', 'tong-quan', 'san-pham', 'van-chuyen', 'khach-hang'];
        $tab = trim((string) $request->query('dashboard_tab', 'list'));
        if (!in_array($tab, $allowedTabs, true)) {
            $tab = 'list';
        }

        $allowedRanges = ['today', '7d', '30d', 'month', 'year', 'custom'];
        $range = trim((string) $request->query('dashboard_range', '30d'));
        if (!in_array($range, $allowedRanges, true)) {
            $range = '30d';
        }

        $now = Carbon::now();
        $from = $now->copy()->subDays(29)->startOfDay();
        $to = $now->copy()->endOfDay();
        $dashboardDateRaw = trim((string) $request->query('dashboard_date', ''));
        $dashboardMonthRaw = trim((string) $request->query('dashboard_month', $now->format('Y-m')));
        $dashboardYearRaw = trim((string) $request->query('dashboard_year', (string) $now->year));

        if ($range === 'today') {
            $from = $now->copy()->startOfDay();
            $to = $now->copy()->endOfDay();
        } elseif ($range === '7d') {
            $from = $now->copy()->subDays(6)->startOfDay();
            $to = $now->copy()->endOfDay();
        } elseif ($range === 'month') {
            try {
                $monthDate = Carbon::createFromFormat('Y-m', $dashboardMonthRaw)->startOfMonth();
            } catch (\Throwable $e) {
                $monthDate = $now->copy()->startOfMonth();
                $dashboardMonthRaw = $monthDate->format('Y-m');
            }
            $from = $monthDate->copy()->startOfMonth();
            $to = $monthDate->copy()->endOfMonth();
        } elseif ($range === 'year') {
            $dashboardYear = (int) $dashboardYearRaw;
            if ($dashboardYear < 2000 || $dashboardYear > ((int) $now->year + 5)) {
                $dashboardYear = (int) $now->year;
            }
            $dashboardYearRaw = (string) $dashboardYear;
            $from = Carbon::create($dashboardYear, 1, 1, 0, 0, 0)->startOfDay();
            $to = Carbon::create($dashboardYear, 12, 31, 23, 59, 59)->endOfDay();
        } elseif ($range === 'custom') {
            $parsedRange = $this->resolveDashboardDateRangeFromRaw($dashboardDateRaw);
            if (!empty($parsedRange)) {
                $from = $parsedRange['from'];
                $to = $parsedRange['to'];
            } else {
                $range = '30d';
            }
        }

        $status = max(0, (int) $request->query('dashboard_status', 0));
        $channel = trim((string) $request->query('dashboard_channel', 'website'));
        if ($channel !== 'website') {
            $channel = 'website';
        }

        return [
            'tab' => $tab,
            'range' => $range,
            'status' => $status,
            'channel' => $channel,
            'dashboard_date' => $dashboardDateRaw,
            'dashboard_month' => $dashboardMonthRaw,
            'dashboard_year' => $dashboardYearRaw,
            'from' => $from,
            'to' => $to,
            'range_days' => max(1, (int) $from->diffInDays($to) + 1),
            'order_date_filter' => $this->formatDashboardOrderDateRange($from, $to),
            'range_label' => match ($range) {
                'today' => 'Hôm nay',
                '7d' => '7 ngày gần nhất',
                'month' => 'Tháng ' . $from->format('m/Y'),
                'year' => 'Năm ' . $from->format('Y'),
                'custom' => 'Tùy chọn',
                default => '30 ngày gần nhất',
            },
        ];
    }

    private function resolveDashboardDateRangeFromRaw(string $dashboardDateRaw = ''): array
    {
        $dashboardDateRaw = trim($dashboardDateRaw);
        if ($dashboardDateRaw === '') {
            return [];
        }

        $parts = array_values(array_filter(array_map('trim', explode(' to ', $dashboardDateRaw))));
        if (empty($parts)) {
            return [];
        }

        try {
            if (count($parts) >= 2) {
                $from = Carbon::createFromFormat('d/m/Y H:i:s', $parts[0] . ' 00:00:00')->startOfDay();
                $to = Carbon::createFromFormat('d/m/Y H:i:s', $parts[1] . ' 23:59:59')->endOfDay();
            } else {
                $from = Carbon::createFromFormat('d/m/Y H:i:s', $parts[0] . ' 00:00:00')->startOfDay();
                $to = Carbon::createFromFormat('d/m/Y H:i:s', $parts[0] . ' 23:59:59')->endOfDay();
            }
        } catch (\Throwable $e) {
            return [];
        }

        if ($from->gt($to)) {
            $tmp = $from;
            $from = $to;
            $to = $tmp;
        }

        return ['from' => $from, 'to' => $to];
    }

    private function buildDashboardData(string $type, array $filters): array
    {
        $dashboardConfig = (array) (config('type.order.' . $type . '.dashboard') ?? []);
        $dashboardConfig = array_replace_recursive([
            'enabled' => false,
            'tabs' => [
                'overview' => true,
                'product' => true,
                'shipping' => true,
                'customer' => true,
            ],
            'sections' => [
                'header_filter' => true,
                'kpi_cards' => true,
                'core_charts' => true,
                'ai_actions' => true,
                'drilldown' => true,
            ],
            'channel_mode' => 'website_only',
        ], $dashboardConfig);

        $targets = $this->getDashboardTargets();
        $statusOptions = $this->resolveDashboardStatusOptions();
        $drilldownBase = $this->resolveDashboardDrilldownBase($filters);

        $dashboardData = [
            'enabled' => !empty($dashboardConfig['enabled']),
            'config' => $dashboardConfig,
            'status_options' => $statusOptions,
            'status_groups' => $this->resolveOrderQuickStatStatusGroups(),
            'targets' => $targets,
            'summary' => [],
            'charts' => [],
            'alerts' => [],
            'product' => [],
            'shipping' => [],
            'customers' => [],
            'drilldown_base' => $drilldownBase,
        ];

        if (empty($dashboardData['enabled']) || ($filters['tab'] ?? 'list') === 'list') {
            return $dashboardData;
        }

        $summary = $this->buildDashboardSummary($filters);
        $dashboardData['summary'] = $summary;

        $tab = (string) ($filters['tab'] ?? 'list');
        if ($tab === 'tong-quan') {
            $dashboardData['charts'] = $this->buildDashboardCharts($filters, $summary);
            $dashboardData['alerts'] = $this->buildDashboardAlerts($filters, $summary);
        } elseif ($tab === 'san-pham') {
            $dashboardData['product'] = $this->buildDashboardProductInsights($filters);
        } elseif ($tab === 'van-chuyen') {
            $dashboardData['shipping'] = $this->buildDashboardShippingInsights($filters);
        } elseif ($tab === 'khach-hang') {
            $dashboardData['customers'] = $this->buildDashboardCustomerInsights($filters);
        }

        return $dashboardData;
    }

    private function buildDashboardSummary(array $filters): array
    {
        $baseQuery = $this->buildDashboardBaseQuery($filters);
        $aggregate = (array) ($baseQuery->selectRaw('COUNT(*) AS total_orders, COALESCE(SUM(total_price), 0) AS total_revenue, COALESCE(SUM(ship_price), 0) AS total_ship')->first()?->toArray() ?? []);

        $orders = (int) ($aggregate['total_orders'] ?? 0);
        $revenue = (float) ($aggregate['total_revenue'] ?? 0);
        $shipTotal = (float) ($aggregate['total_ship'] ?? 0);
        $aov = $orders > 0 ? $revenue / $orders : 0;

        $statusGroups = $this->resolveOrderQuickStatStatusGroups();
        $deliveredCount = $this->countOrdersByStatusIds($filters, (array) ($statusGroups['delivered'] ?? []));
        $canceledCount = $this->countOrdersByStatusIds($filters, (array) ($statusGroups['canceled'] ?? []));
        $failedShippingIds = $this->resolveDashboardStatusIdsByKeywords(['that bai', 'khong giao', 'giao that bai', 'hoan giao', 'failed']);
        $failedShippingCount = $this->countOrdersByStatusIds($filters, $failedShippingIds);

        $targets = $this->getDashboardTargets();
        $targetRange = $this->getDashboardTargetMonthRange((string) ($targets['month'] ?? ''));
        $targetMonthQuery = OrdersModel::query()
            ->whereBetween('created_at', [
                $targetRange['start']->toDateTimeString(),
                $targetRange['end']->toDateTimeString(),
            ]);
        $targetMonthOrders = (int) $targetMonthQuery->count();
        $targetMonthRevenue = (float) ($targetMonthQuery->sum('total_price') ?? 0);

        $dayPassed = (int) min((int) Carbon::now()->day, (int) $targetRange['days_in_month']);
        if ((string) ($targetRange['month'] ?? '') !== Carbon::now()->format('Y-m')) {
            $dayPassed = (int) $targetRange['days_in_month'];
        }
        $runRateRevenue = $dayPassed > 0 ? ($targetMonthRevenue / $dayPassed) : 0;
        $runRateOrders = $dayPassed > 0 ? ($targetMonthOrders / $dayPassed) : 0;
        $forecastRevenue = $runRateRevenue * (int) $targetRange['days_in_month'];
        $forecastOrders = $runRateOrders * (int) $targetRange['days_in_month'];

        $targetRevenue = (float) ($targets['revenue_target'] ?? 0);
        $targetOrders = (int) ($targets['orders_target'] ?? 0);
        $targetRevenueRate = $targetRevenue > 0 ? round(($targetMonthRevenue * 100) / $targetRevenue, 1) : 0;
        $targetOrdersRate = $targetOrders > 0 ? round(($targetMonthOrders * 100) / $targetOrders, 1) : 0;
        $forecastRevenueRate = $targetRevenue > 0 ? round(($forecastRevenue * 100) / $targetRevenue, 1) : 0;
        $forecastOrdersRate = $targetOrders > 0 ? round(($forecastOrders * 100) / $targetOrders, 1) : 0;

        return [
            'orders' => $orders,
            'revenue' => $revenue,
            'aov' => $aov,
            'ship_total' => $shipTotal,
            'delivered_count' => $deliveredCount,
            'canceled_count' => $canceledCount,
            'failed_shipping_count' => $failedShippingCount,
            'delivered_rate' => $orders > 0 ? round(($deliveredCount * 100) / $orders, 1) : 0,
            'canceled_rate' => $orders > 0 ? round(($canceledCount * 100) / $orders, 1) : 0,
            'failed_shipping_rate' => $orders > 0 ? round(($failedShippingCount * 100) / $orders, 1) : 0,
            'targets' => [
                'month' => $targets['month'],
                'month_label' => $targets['month_label'],
                'revenue_target' => $targetRevenue,
                'orders_target' => $targetOrders,
                'current_revenue' => $targetMonthRevenue,
                'current_orders' => $targetMonthOrders,
                'current_revenue_rate' => $targetRevenueRate,
                'current_orders_rate' => $targetOrdersRate,
                'forecast_revenue' => $forecastRevenue,
                'forecast_orders' => $forecastOrders,
                'forecast_revenue_rate' => $forecastRevenueRate,
                'forecast_orders_rate' => $forecastOrdersRate,
            ],
        ];
    }

    private function buildDashboardCharts(array $filters, array $summary = []): array
    {
        $from = $filters['from']->copy();
        $to = $filters['to']->copy();

        $labels = [];
        $dateKeys = [];
        $revenueSeries = [];
        $orderSeries = [];
        $lineGranularity = ((string) ($filters['range'] ?? '') === 'year' || (int) ($filters['range_days'] ?? 0) > 90) ? 'month' : 'day';
        if ($lineGranularity === 'month') {
            $monthlyRows = $this->buildDashboardBaseQuery($filters)
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') AS month_key, COUNT(*) AS total_orders, COALESCE(SUM(total_price), 0) AS total_revenue")
                ->groupBy('month_key')
                ->orderBy('month_key', 'asc')
                ->get()
                ->keyBy('month_key');

            $cursor = $from->copy()->startOfMonth();
            while ($cursor->lte($to)) {
                $monthKey = $cursor->format('Y-m');
                $rangeFrom = $cursor->copy()->startOfMonth();
                $rangeTo = $cursor->copy()->endOfMonth();
                if ($rangeFrom->lt($from)) {
                    $rangeFrom = $from->copy();
                }
                if ($rangeTo->gt($to)) {
                    $rangeTo = $to->copy();
                }

                $labels[] = $cursor->format('m/Y');
                $dateKeys[] = $rangeFrom->format('d/m/Y') . ' to ' . $rangeTo->format('d/m/Y');
                $revenueSeries[] = (float) ($monthlyRows[$monthKey]->total_revenue ?? 0);
                $orderSeries[] = (int) ($monthlyRows[$monthKey]->total_orders ?? 0);
                $cursor->addMonthNoOverflow()->startOfMonth();
            }
        } else {
            $dailyRows = $this->buildDashboardBaseQuery($filters)
                ->selectRaw('DATE(created_at) AS day_key, COUNT(*) AS total_orders, COALESCE(SUM(total_price), 0) AS total_revenue')
                ->groupBy('day_key')
                ->orderBy('day_key', 'asc')
                ->get()
                ->keyBy('day_key');

            $cursor = $from->copy();
            while ($cursor->lte($to)) {
                $dateKey = $cursor->format('Y-m-d');
                $labels[] = $cursor->format('d/m');
                $dateKeys[] = $cursor->format('d/m/Y');
                $revenueSeries[] = (float) ($dailyRows[$dateKey]->total_revenue ?? 0);
                $orderSeries[] = (int) ($dailyRows[$dateKey]->total_orders ?? 0);
                $cursor->addDay();
            }
        }

        $statusCounts = $this->buildDashboardBaseQuery($filters)
            ->selectRaw('order_status, COUNT(*) AS total')
            ->groupBy('order_status')
            ->pluck('total', 'order_status')
            ->toArray();

        $statusLabels = [];
        $statusSeries = [];
        $statusIds = [];
        foreach (OrderStatusModel::select('id', 'namevi')->orderBy('id', 'asc')->get() as $status) {
            $statusId = (int) ($status->id ?? 0);
            $statusTotal = (int) ($statusCounts[$statusId] ?? 0);
            if ($statusId > 0 && $statusTotal > 0) {
                $statusLabels[] = (string) ($status->namevi ?? ('#' . $statusId));
                $statusSeries[] = $statusTotal;
                $statusIds[] = $statusId;
            }
        }

        if (empty($statusLabels)) {
            $statusLabels[] = 'Không có dữ liệu';
            $statusSeries[] = 0;
            $statusIds[] = 0;
        }

        $statusGroups = $this->resolveOrderQuickStatStatusGroups();
        $totalOrders = (int) ($summary['orders'] ?? 0);
        $pendingCount = $this->countOrdersByStatusIds($filters, (array) ($statusGroups['pending_confirm'] ?? []));
        $processingCount = $this->countOrdersByStatusIds($filters, (array) ($statusGroups['processing_packing'] ?? []));
        $shippingCount = $this->countOrdersByStatusIds($filters, (array) ($statusGroups['shipping'] ?? []));
        $deliveredCount = $this->countOrdersByStatusIds($filters, (array) ($statusGroups['delivered'] ?? []));
        $canceledCount = $this->countOrdersByStatusIds($filters, (array) ($statusGroups['canceled'] ?? []));

        $confirmedCount = max(0, $totalOrders - $pendingCount - $canceledCount);
        $packingCount = max($processingCount, $shippingCount + $deliveredCount);
        $funnelStatusIds = [
            (int) ($statusGroups['pending_confirm'][0] ?? 0),
            (int) ($statusGroups['pending_confirm'][0] ?? 0),
            (int) ($statusGroups['processing_packing'][0] ?? ($statusGroups['shipping'][0] ?? 0)),
            (int) ($statusGroups['shipping'][0] ?? 0),
            (int) ($statusGroups['delivered'][0] ?? 0),
        ];

        return [
            'line' => [
                'granularity' => $lineGranularity,
                'labels' => $labels,
                'date_keys' => $dateKeys,
                'revenue_series' => $revenueSeries,
                'order_series' => $orderSeries,
            ],
            'funnel' => [
                'labels' => ['Tạo đơn', 'Xác nhận', 'Đóng gói', 'Đang giao', 'Thành công'],
                'series' => [
                    $totalOrders,
                    $confirmedCount,
                    $packingCount,
                    $shippingCount + $deliveredCount,
                    $deliveredCount,
                ],
                'status_ids' => $funnelStatusIds,
            ],
            'status' => [
                'labels' => $statusLabels,
                'series' => $statusSeries,
                'ids' => $statusIds,
            ],
        ];
    }

    private function buildDashboardProductInsights(array $filters): array
    {
        $orders = $this->buildDashboardBaseQuery($filters)
            ->select('id', 'order_detail')
            ->get();

        $productStats = [];
        $normalizeOrderDetail = function ($rawOrderDetail): array {
            if ($rawOrderDetail instanceof \Illuminate\Support\Collection) {
                return $rawOrderDetail->toArray();
            }
            if (is_array($rawOrderDetail)) {
                return $rawOrderDetail;
            }
            if (is_object($rawOrderDetail)) {
                return (array) $rawOrderDetail;
            }
            if (is_string($rawOrderDetail)) {
                $decoded = json_decode($rawOrderDetail, true);
                return is_array($decoded) ? $decoded : [];
            }
            return [];
        };
        foreach ($orders as $order) {
            $orderDetail = $normalizeOrderDetail($order->order_detail ?? []);
            foreach ($orderDetail as $item) {
                $item = is_array($item) ? $item : (array) $item;
                $qty = max(0, (int) ($item['qty'] ?? 0));
                if ($qty <= 0) {
                    continue;
                }

                $options = is_array($item['options'] ?? null) ? $item['options'] : (array) ($item['options'] ?? []);
                $itemProduct = is_array($options['itemProduct'] ?? null) ? $options['itemProduct'] : (array) ($options['itemProduct'] ?? []);
                $productId = (int) ($itemProduct['id'] ?? ($item['id'] ?? 0));
                $productName = trim((string) ($item['name'] ?? ($itemProduct['namevi'] ?? '')));
                if ($productName === '') {
                    $productName = $productId > 0 ? ('Sản phẩm #' . $productId) : 'Sản phẩm';
                }

                $price = max(0, (float) ($item['price'] ?? 0));
                if ($price <= 0) {
                    $salePrice = max(0, (float) ($itemProduct['sale_price'] ?? 0));
                    $regularPrice = max(0, (float) ($itemProduct['regular_price'] ?? 0));
                    $price = $salePrice > 0 ? $salePrice : $regularPrice;
                }
                $lineRevenue = $qty * $price;

                $key = $productId > 0 ? ('id:' . $productId) : ('name:' . mb_strtolower($productName, 'UTF-8'));
                if (empty($productStats[$key])) {
                    $productStats[$key] = [
                        'product_id' => $productId,
                        'name' => $productName,
                        'qty' => 0,
                        'revenue' => 0.0,
                    ];
                }
                $productStats[$key]['qty'] += $qty;
                $productStats[$key]['revenue'] += $lineRevenue;
            }
        }

        $topProducts = array_values($productStats);
        usort($topProducts, function ($a, $b) {
            if ((int) $a['qty'] === (int) $b['qty']) {
                return (float) $b['revenue'] <=> (float) $a['revenue'];
            }
            return (int) $b['qty'] <=> (int) $a['qty'];
        });
        $topProducts = array_slice($topProducts, 0, 10);

        $recommendations = [];
        $rangeDays = max(1, (int) ($filters['range_days'] ?? 30));
        foreach ($topProducts as $productRow) {
            $productId = (int) ($productRow['product_id'] ?? 0);
            if ($productId <= 0) {
                continue;
            }

            $stockQty = (int) (ProductPropertiesModel::where('id_parent', $productId)->sum('quantity') ?? 0);
            $dailySales = ((int) ($productRow['qty'] ?? 0)) / $rangeDays;
            $daysLeft = $dailySales > 0 ? (int) floor($stockQty / $dailySales) : 9999;

            if (($dailySales >= 1 && $daysLeft <= 14) || $stockQty <= 5) {
                $suggestQty = max(1, (int) ceil(($dailySales * 30) - $stockQty));
                $recommendations[] = [
                    'product_id' => $productId,
                    'name' => (string) ($productRow['name'] ?? ('Sản phẩm #' . $productId)),
                    'stock_qty' => $stockQty,
                    'daily_sales' => round($dailySales, 2),
                    'days_left' => $daysLeft,
                    'suggest_qty' => $suggestQty,
                ];
            }
        }

        usort($recommendations, function ($a, $b) {
            return (int) $a['days_left'] <=> (int) $b['days_left'];
        });

        return [
            'top_products' => $topProducts,
            'restock_recommendations' => array_slice($recommendations, 0, 10),
        ];
    }

    private function buildDashboardShippingInsights(array $filters): array
    {
        $summary = $this->buildDashboardSummary($filters);
        $orders = (int) ($summary['orders'] ?? 0);
        $shipTotal = (float) ($summary['ship_total'] ?? 0);
        $deliveredRate = (float) ($summary['delivered_rate'] ?? 0);
        $canceledRate = (float) ($summary['canceled_rate'] ?? 0);
        $failedRate = (float) ($summary['failed_shipping_rate'] ?? 0);

        $shippingStatuses = $this->resolveDashboardStatusIdsByKeywords(['dang giao', 'giao', 'shipping', 'van chuyen']);
        $shippingCount = $this->countOrdersByStatusIds($filters, $shippingStatuses);

        return [
            'orders' => $orders,
            'ship_total' => $shipTotal,
            'avg_ship' => $orders > 0 ? round($shipTotal / $orders, 2) : 0,
            'delivered_rate' => $deliveredRate,
            'canceled_rate' => $canceledRate,
            'failed_shipping_rate' => $failedRate,
            'shipping_count' => $shippingCount,
        ];
    }

    private function buildDashboardCustomerInsights(array $filters): array
    {
        $rows = $this->buildDashboardBaseQuery($filters)
            ->selectRaw('id,total_price')
            ->selectRaw('JSON_UNQUOTE(JSON_EXTRACT(info_user, "$.fullname")) AS fullname')
            ->selectRaw('JSON_UNQUOTE(JSON_EXTRACT(info_user, "$.phone")) AS phone')
            ->get();

        $customers = [];
        foreach ($rows as $row) {
            $fullname = trim((string) ($row->fullname ?? ''));
            $phone = trim((string) ($row->phone ?? ''));
            $key = $phone !== '' ? ('phone:' . $phone) : ('name:' . mb_strtolower($fullname, 'UTF-8'));
            if ($key === 'name:') {
                $key = 'anonymous';
            }

            if (empty($customers[$key])) {
                $customers[$key] = [
                    'fullname' => $fullname !== '' ? $fullname : 'Khách lẻ',
                    'phone' => $phone,
                    'orders' => 0,
                    'revenue' => 0.0,
                ];
            }

            $customers[$key]['orders'] += 1;
            $customers[$key]['revenue'] += (float) ($row->total_price ?? 0);
        }

        $topCustomers = array_values($customers);
        usort($topCustomers, function ($a, $b) {
            if ((float) $a['revenue'] === (float) $b['revenue']) {
                return (int) $b['orders'] <=> (int) $a['orders'];
            }
            return (float) $b['revenue'] <=> (float) $a['revenue'];
        });

        $newCustomers = 0;
        foreach ($topCustomers as $customer) {
            if ((int) ($customer['orders'] ?? 0) === 1) {
                $newCustomers++;
            }
        }

        return [
            'top_customers' => array_slice($topCustomers, 0, 10),
            'total_customers' => count($topCustomers),
            'new_customers' => $newCustomers,
        ];
    }

    private function buildDashboardAlerts(array $filters, array $summary): array
    {
        $alerts = [];

        $today = Carbon::now()->startOfDay();
        $todayRevenue = (float) OrdersModel::query()
            ->whereBetween('created_at', [$today->copy()->startOfDay()->toDateTimeString(), $today->copy()->endOfDay()->toDateTimeString()])
            ->sum('total_price');

        $recentRevenueRows = OrdersModel::query()
            ->selectRaw('DATE(created_at) AS day_key, COALESCE(SUM(total_price), 0) AS total_revenue')
            ->whereBetween('created_at', [
                Carbon::now()->copy()->subDays(7)->startOfDay()->toDateTimeString(),
                Carbon::now()->copy()->endOfDay()->toDateTimeString(),
            ])
            ->groupBy('day_key')
            ->pluck('total_revenue', 'day_key')
            ->toArray();

        $avgRevenue7d = 0.0;
        $daysCount = 0;
        for ($i = 1; $i <= 7; $i++) {
            $key = Carbon::now()->copy()->subDays($i)->format('Y-m-d');
            $avgRevenue7d += (float) ($recentRevenueRows[$key] ?? 0);
            $daysCount++;
        }
        $avgRevenue7d = $daysCount > 0 ? ($avgRevenue7d / $daysCount) : 0;

        if ($avgRevenue7d > 0) {
            $changeRate = (($todayRevenue - $avgRevenue7d) * 100) / $avgRevenue7d;
            if (abs($changeRate) >= 20) {
                $alerts[] = [
                    'type' => $changeRate > 0 ? 'success' : 'warning',
                    'title' => 'Doanh thu hôm nay biến động so với trung bình 7 ngày',
                    'message' => 'Mức thay đổi ' . number_format($changeRate, 1) . '%.',
                ];
            }
        }

        $statusGroups = $this->resolveOrderQuickStatStatusGroups();
        $canceledIds = (array) ($statusGroups['canceled'] ?? []);
        $cancelRateRecent3 = $this->calculateRateByPeriod(
            Carbon::now()->copy()->subDays(2)->startOfDay(),
            Carbon::now()->copy()->endOfDay(),
            $canceledIds
        );
        $cancelRatePrev7 = $this->calculateRateByPeriod(
            Carbon::now()->copy()->subDays(9)->startOfDay(),
            Carbon::now()->copy()->subDays(3)->endOfDay(),
            $canceledIds
        );
        if ($cancelRateRecent3 - $cancelRatePrev7 >= 5) {
            $alerts[] = [
                'type' => 'danger',
                'title' => 'Tỷ lệ hủy đơn tăng bất thường',
                'message' => '3 ngày gần nhất: ' . number_format($cancelRateRecent3, 1) . '%, cao hơn nền 7 ngày trước.',
            ];
        }

        $recent7Orders = (int) OrdersModel::query()
            ->whereBetween('created_at', [
                Carbon::now()->copy()->subDays(6)->startOfDay()->toDateTimeString(),
                Carbon::now()->copy()->endOfDay()->toDateTimeString(),
            ])
            ->count();
        $recent7Ship = (float) OrdersModel::query()
            ->whereBetween('created_at', [
                Carbon::now()->copy()->subDays(6)->startOfDay()->toDateTimeString(),
                Carbon::now()->copy()->endOfDay()->toDateTimeString(),
            ])
            ->sum('ship_price');
        $prev30Orders = (int) OrdersModel::query()
            ->whereBetween('created_at', [
                Carbon::now()->copy()->subDays(36)->startOfDay()->toDateTimeString(),
                Carbon::now()->copy()->subDays(7)->endOfDay()->toDateTimeString(),
            ])
            ->count();
        $prev30Ship = (float) OrdersModel::query()
            ->whereBetween('created_at', [
                Carbon::now()->copy()->subDays(36)->startOfDay()->toDateTimeString(),
                Carbon::now()->copy()->subDays(7)->endOfDay()->toDateTimeString(),
            ])
            ->sum('ship_price');

        $recentShipPerOrder = $recent7Orders > 0 ? ($recent7Ship / $recent7Orders) : 0;
        $prevShipPerOrder = $prev30Orders > 0 ? ($prev30Ship / $prev30Orders) : 0;
        if ($recent7Orders >= 5 && $prevShipPerOrder > 0 && $recentShipPerOrder > ($prevShipPerOrder * 1.2)) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Phí vận chuyển trung bình đang tăng',
                'message' => '7 ngày gần nhất tăng hơn 20% so với nền trước đó.',
            ];
        }

        if (empty($alerts)) {
            $alerts[] = [
                'type' => 'info',
                'title' => 'Hệ thống ổn định',
                'message' => 'Chưa phát hiện bất thường đáng chú ý trong kỳ lọc hiện tại.',
            ];
        }

        return $alerts;
    }

    private function buildDashboardBaseQuery(array $filters)
    {
        $query = OrdersModel::query()->where('id', '<>', 0);
        $from = $filters['from'] instanceof Carbon ? $filters['from'] : Carbon::now()->copy()->subDays(29)->startOfDay();
        $to = $filters['to'] instanceof Carbon ? $filters['to'] : Carbon::now()->copy()->endOfDay();

        $query->whereBetween('created_at', [$from->toDateTimeString(), $to->toDateTimeString()]);

        $status = (int) ($filters['status'] ?? 0);
        if ($status > 0) {
            $query->where('order_status', $status);
        }

        return $query;
    }

    private function resolveDashboardStatusOptions(): array
    {
        return OrderStatusModel::select('id', 'namevi')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($row) {
                return [
                    'id' => (int) ($row->id ?? 0),
                    'name' => trim((string) ($row->namevi ?? '')),
                ];
            })
            ->filter(function ($row) {
                return (int) ($row['id'] ?? 0) > 0;
            })
            ->values()
            ->all();
    }

    private function resolveDashboardDrilldownBase(array $filters): array
    {
        $query = [
            'dashboard_tab' => 'list',
            'order_date' => (string) ($filters['order_date_filter'] ?? ''),
        ];

        if ((int) ($filters['status'] ?? 0) > 0) {
            $query['order_status'] = (int) $filters['status'];
        }

        return $query;
    }

    private function resolveDashboardStatusIdsByKeywords(array $keywords): array
    {
        $keywords = array_filter(array_map(function ($keyword) {
            return trim($this->normalizeOrderStatusName((string) $keyword));
        }, $keywords));
        if (empty($keywords)) {
            return [];
        }

        $ids = [];
        foreach (OrderStatusModel::select('id', 'namevi')->get() as $status) {
            $statusId = (int) ($status->id ?? 0);
            if ($statusId <= 0) {
                continue;
            }

            $normalized = $this->normalizeOrderStatusName((string) ($status->namevi ?? ''));
            if ($normalized === '') {
                continue;
            }

            foreach ($keywords as $keyword) {
                if ($keyword !== '' && str_contains($normalized, $keyword)) {
                    $ids[] = $statusId;
                    break;
                }
            }
        }

        return array_values(array_unique($ids));
    }

    private function countOrdersByStatusIds(array $filters, array $statusIds): int
    {
        $statusIds = array_values(array_filter(array_map('intval', $statusIds), function ($id) {
            return $id > 0;
        }));
        if (empty($statusIds)) {
            return 0;
        }

        return (int) $this->buildDashboardBaseQuery($filters)
            ->whereIn('order_status', $statusIds)
            ->count();
    }

    private function calculateRateByPeriod(Carbon $from, Carbon $to, array $statusIds): float
    {
        $statusIds = array_values(array_filter(array_map('intval', $statusIds), function ($id) {
            return $id > 0;
        }));
        if (empty($statusIds)) {
            return 0;
        }

        $periodQuery = OrdersModel::query()->whereBetween('created_at', [$from->toDateTimeString(), $to->toDateTimeString()]);
        $total = (int) $periodQuery->count();
        if ($total <= 0) {
            return 0;
        }

        $statusCount = (int) OrdersModel::query()
            ->whereBetween('created_at', [$from->toDateTimeString(), $to->toDateTimeString()])
            ->whereIn('order_status', $statusIds)
            ->count();

        return round(($statusCount * 100) / $total, 1);
    }

    private function getDashboardTargets(): array
    {
        $options = $this->getDashboardSettingOptions();
        $month = trim((string) ($options['order_dashboard_target_month'] ?? Carbon::now()->format('Y-m')));
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = Carbon::now()->format('Y-m');
        }

        $revenueTarget = (int) preg_replace('/[^\d]/', '', (string) ($options['order_dashboard_target_revenue'] ?? 0));
        $ordersTarget = (int) preg_replace('/[^\d]/', '', (string) ($options['order_dashboard_target_orders'] ?? 0));

        $range = $this->getDashboardTargetMonthRange($month);

        return [
            'month' => $range['month'],
            'month_label' => $range['start']->format('m/Y'),
            'revenue_target' => max(0, $revenueTarget),
            'orders_target' => max(0, $ordersTarget),
        ];
    }

    private function getDashboardTargetMonthRange(string $month): array
    {
        $month = trim($month);
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = Carbon::now()->format('Y-m');
        }

        try {
            $start = Carbon::createFromFormat('Y-m-d H:i:s', $month . '-01 00:00:00')->startOfMonth()->startOfDay();
        } catch (\Throwable $e) {
            $start = Carbon::now()->startOfMonth()->startOfDay();
        }
        $end = $start->copy()->endOfMonth()->endOfDay();

        return [
            'month' => $start->format('Y-m'),
            'start' => $start,
            'end' => $end,
            'days_in_month' => (int) $start->daysInMonth,
        ];
    }

    private function getDashboardSettingOptions(): array
    {
        $rawOptions = (string) (SettingModel::where('type', 'cau-hinh')->value('options') ?? '');
        $options = json_decode($rawOptions, true);
        return is_array($options) ? $options : [];
    }

    private function formatDashboardOrderDateRange(Carbon $from, Carbon $to): string
    {
        return $from->format('d/m/Y') . ' to ' . $to->format('d/m/Y');
    }

    private function handleDashboardTargetSave($com, $act, $type, Request $request)
    {
        if (function_exists('isPermissions') && !isPermissions('setting.cau-hinh.edit')) {
            return transfer('Bạn không có quyền cập nhật mục tiêu tháng.', false, linkReferer());
        }

        $targetRevenue = (int) preg_replace('/[^\d]/', '', (string) $request->input('dashboard_target_revenue', 0));
        $targetOrders = (int) preg_replace('/[^\d]/', '', (string) $request->input('dashboard_target_orders', 0));
        $targetMonth = trim((string) $request->input('dashboard_target_month', Carbon::now()->format('Y-m')));
        if (!preg_match('/^\d{4}-\d{2}$/', $targetMonth)) {
            $targetMonth = Carbon::now()->format('Y-m');
        }

        $setting = SettingModel::where('type', 'cau-hinh')->first();
        $options = [];
        if (!empty($setting)) {
            $options = json_decode((string) ($setting->options ?? ''), true);
            if (!is_array($options)) {
                $options = [];
            }
        }

        $options['order_dashboard_target_revenue'] = max(0, $targetRevenue);
        $options['order_dashboard_target_orders'] = max(0, $targetOrders);
        $options['order_dashboard_target_month'] = $targetMonth;

        if (!empty($setting)) {
            SettingModel::where('id', (int) $setting->id)->update(['options' => json_encode($options)]);
        } else {
            SettingModel::create([
                'type' => 'cau-hinh',
                'options' => json_encode($options),
            ]);
        }

        $redirectUrl = url('admin', ['com' => $com, 'act' => 'man', 'type' => $type]);
        $returnQuery = trim((string) $request->input('dashboard_return_query', ''));
        $returnQuery = ltrim($returnQuery, '?');
        if ($returnQuery !== '') {
            $redirectUrl .= '?' . $returnQuery;
        }

        return transfer('Cập nhật mục tiêu tháng thành công.', true, $redirectUrl);
    }

    public function edit($com, $act, $type, Request $request)
    {
        $id = (isset($request->id)) ? htmlspecialchars($request->id) : 0;

        $item = OrdersModel::select('*')
            ->where('id', $id)
            ->first();

        $history = OrderHistoryModel::select('*')
            ->where('id_order', $id)
            ->orderBy('id', 'desc')
            ->get();

        $infoUserRaw = $item['info_user'] ?? [];
        $infoUser = is_array($infoUserRaw) ? $infoUserRaw : (array) $infoUserRaw;
        $cityId = (int) ($infoUser['city'] ?? 0);
        $wardId = (int) ($infoUser['ward'] ?? 0);
        $cityName = trim((string) ($infoUser['city_name'] ?? ''));
        $wardName = trim((string) ($infoUser['ward_name'] ?? ''));

        if ($wardId > 0 && (empty($wardName) || empty($cityId))) {
            $ward = WardModel::select('id_city', 'namevi')->find($wardId);
            if (!empty($ward)) {
                if (empty($wardName)) $wardName = trim((string) ($ward->namevi ?? ''));
                if (empty($cityId)) $cityId = (int) ($ward->id_city ?? 0);
            }
        }

        if ($cityId > 0 && empty($cityName)) {
            $cityName = trim((string) (CityModel::where('id', $cityId)->value('namevi') ?? ''));
        }

        $addressParts = array_filter([
            trim((string) ($infoUser['address'] ?? '')),
            $wardName,
            $cityName
        ]);

        $infoUser['city'] = $cityId;
        $infoUser['ward'] = $wardId;
        $infoUser['city_name'] = $cityName;
        $infoUser['ward_name'] = $wardName;
        $infoUser['full_address'] = implode(', ', $addressParts);
        $infoOrder = $item['order_detail'];
        $orderStatuses = OrderStatusModel::select('id', 'namevi')->orderBy('id', 'asc')->get();
        $allowedOrderStatusIds = $this->resolveAllowedOrderStatusIds($orderStatuses, (int) ($item['order_status'] ?? 0));

        return view('order.man.add', [
            'item' => $item,
            'infoUser' => $infoUser,
            'infoOrder' => $infoOrder,
            'history' => $history,
            'orderStatuses' => $orderStatuses,
            'allowedOrderStatusIds' => $allowedOrderStatusIds
        ]);
    }

    public function save($com, $act, $type, Request $request)
    {
        if (!empty($request->csrf_token)) {

            $message = '';
            $response = array();
            $id = (!empty($request->id)) ? htmlspecialchars($request->id) : 0;
            $data = (!empty($request->data)) ? $request->data : null;
            if ($data) {
                foreach ($data as $column => $value) {
                    if (strpos($column, 'content') !== false || strpos($column, 'desc') !== false) {
                        $data[$column] = htmlspecialchars(Func::sanitize($value, 'iframe'));
                    } else {
                        $data[$column] = htmlspecialchars(Func::sanitize($value));
                    }
                }
            }
            $order = null;
            $orderStatuses = collect();
            $shouldRestoreReservedInventory = false;
            if (!empty($response)) {
                if (!empty($data)) {
                    foreach ($data as $k => $v) {
                        if (!empty($v)) {
                            Flash::set($k, $v);
                        }
                    }
                }
                $response['status'] = 'danger';
                $message = base64_encode(json_encode($response));
                Flash::set('message', $message);
                response()->redirect(linkReferer());
            }
            if (!empty($id)) {
                $order = OrdersModel::select('id', 'order_status', 'order_detail', 'info_user')->where('id', $id)->first();
                if (empty($order)) {
                    $response['messages'][] = 'Đơn hàng không tồn tại.';
                } else {
                    $currentStatusId = (int) ($order->order_status ?? 0);
                    $nextStatusId = (int) ($data['order_status'] ?? 0);
                    $orderStatuses = OrderStatusModel::select('id', 'namevi')->orderBy('id', 'asc')->get();
                    $allowedOrderStatusIds = $this->resolveAllowedOrderStatusIds($orderStatuses, $currentStatusId);
                    $shouldRestoreReservedInventory = $this->isCanceledStatusTransition($currentStatusId, $nextStatusId, $orderStatuses);

                    if ($nextStatusId <= 0) {
                        $response['messages'][] = 'Vui lòng chọn tình trạng đơn hàng.';
                    } elseif (!in_array($nextStatusId, $allowedOrderStatusIds, true)) {
                        $currentStatusName = (string) ($orderStatuses->firstWhere('id', $currentStatusId)->namevi ?? 'Không xác định');
                        $nextStatusName = (string) ($orderStatuses->firstWhere('id', $nextStatusId)->namevi ?? 'Không xác định');
                        $response['messages'][] = 'Không thể chuyển trạng thái từ "' . $currentStatusName . '" sang "' . $nextStatusName . '".';
                    }
                }
            }

            if (!empty($response)) {
                if (!empty($data)) {
                    foreach ($data as $k => $v) {
                        if (!empty($v)) {
                            Flash::set($k, $v);
                        }
                    }
                }
                $response['status'] = 'danger';
                $message = base64_encode(json_encode($response));
                Flash::set('message', $message);
                response()->redirect(linkReferer());
            }

            if (!empty($id)) {
                if ($shouldRestoreReservedInventory && !empty($order)) {
                    $infoUser = is_array($order->info_user ?? null) ? $order->info_user : [];
                    if ($this->hasReservedInventoryFlag($infoUser)) {
                        $this->releaseOrderInventory($order->order_detail ?? []);
                        $data['info_user'] = $this->clearReservedInventoryFlag($infoUser);
                    }
                }

                if (OrdersModel::where('id', $id)->update($data)) {
                    $historyNotes = trim((string) ($data['notes'] ?? ''));
                    $historyActor = $this->resolveOrderHistoryActorName();
                    if ($historyActor !== '') {
                        $historyNotes = $this->appendHistoryActorTag($historyNotes, $historyActor);
                    }

                    $history = [];
                    $history['id_order'] = $id;
                    $history['order_status'] = $data['order_status'];
                    $history['notes'] = $historyNotes;
                    OrderHistoryModel::create($history);
                    return $this->linkRequest($com, 'man', $type, $id, $request);
                } else {
                    return $this->linkRequest($com, 'man', $type, $id, $request);
                }
            } else {
                $itemSave = OrdersModel::create($data);
                if (!empty($itemSave)) {
                    $id_insert = $itemSave->id;
                    return $this->linkRequest($com, 'man', $type, $id_insert, $request);
                } else {
                    return transfer('Thêm dữ liệu thất bại.', false, linkReferer());
                }
            }
        }
    }

    private function resolveOrderHistoryActorName(): string
    {
        $actorName = '';

        try {
            if (Auth::guard('admin')->check()) {
                $adminUser = Auth::guard('admin')->user();
                $actorName = trim((string) ($adminUser->username ?? ''));
                if ($actorName === '') {
                    $actorName = trim((string) ($adminUser->fullname ?? ''));
                }
            }
        } catch (\Throwable $e) {
        }

        if ($actorName !== '') {
            return $actorName;
        }

        $actorName = trim((string) session()->get('admin.username'));
        if ($actorName === '') {
            $actorName = trim((string) session()->get('admin.fullname'));
        }
        if ($actorName !== '') {
            return $actorName;
        }

        $adminId = (int) session()->get('admin');
        if ($adminId > 0) {
            $adminUser = UserModel::select('username', 'fullname')->where('id', $adminId)->first();
            if (!empty($adminUser)) {
                $actorName = trim((string) ($adminUser->username ?? ''));
                if ($actorName === '') {
                    $actorName = trim((string) ($adminUser->fullname ?? ''));
                }
            }
        }

        return $actorName;
    }

    private function appendHistoryActorTag(string $notes, string $actorName): string
    {
        $encodedActor = base64_encode($actorName);
        if ($encodedActor === '') {
            return $notes;
        }

        return trim($notes . PHP_EOL . '[__admin__:' . $encodedActor . ']');
    }

    private function resolveAllowedOrderStatusIds($orderStatuses, int $currentStatusId): array
    {
        $orderStatuses = $orderStatuses instanceof \Illuminate\Support\Collection ? $orderStatuses : collect($orderStatuses);
        $allStatusIds = $orderStatuses->pluck('id')->map(function ($id) {
            return (int) $id;
        })->values()->all();

        if (empty($allStatusIds) || $currentStatusId <= 0) {
            return $allStatusIds;
        }

        $idByAlias = [];
        foreach ($orderStatuses as $row) {
            $id = (int) ($row['id'] ?? $row->id ?? 0);
            $name = (string) ($row['namevi'] ?? $row->namevi ?? '');
            if ($id > 0) {
                $alias = $this->resolveOrderStatusAlias($name);
                if ($alias !== '' && empty($idByAlias[$alias])) {
                    $idByAlias[$alias] = $id;
                }
            }
        }

        // Priority: fixed IDs (1..5) from current system, then fallback by name alias.
        $newId = in_array(1, $allStatusIds, true) ? 1 : (int) ($idByAlias['new'] ?? 0);
        $confirmedId = in_array(2, $allStatusIds, true) ? 2 : (int) ($idByAlias['confirmed'] ?? 0);
        $shippingId = in_array(3, $allStatusIds, true) ? 3 : (int) ($idByAlias['shipping'] ?? 0);
        $deliveredId = in_array(4, $allStatusIds, true) ? 4 : (int) ($idByAlias['delivered'] ?? 0);
        $canceledId = in_array(5, $allStatusIds, true) ? 5 : (int) ($idByAlias['canceled'] ?? 0);

        $allowedByStatusId = [];
        if ($newId > 0) {
            $allowedByStatusId[$newId] = array_values(array_unique(array_filter([$newId, $confirmedId, $canceledId])));
        }
        if ($confirmedId > 0) {
            $allowedByStatusId[$confirmedId] = array_values(array_unique(array_filter([$confirmedId, $shippingId, $canceledId])));
        }
        if ($shippingId > 0) {
            $allowedByStatusId[$shippingId] = array_values(array_unique(array_filter([$shippingId, $deliveredId, $canceledId])));
        }
        if ($deliveredId > 0) {
            $allowedByStatusId[$deliveredId] = [$deliveredId];
        }
        if ($canceledId > 0) {
            $allowedByStatusId[$canceledId] = [$canceledId];
        }

        if (!empty($allowedByStatusId[$currentStatusId])) {
            return $allowedByStatusId[$currentStatusId];
        }

        // Unknown status: keep only current status, avoid free transitions.
        return in_array($currentStatusId, $allStatusIds, true) ? [$currentStatusId] : $allStatusIds;
    }

    private function resolveOrderStatusAlias(string $statusName = ''): string
    {
        $normalized = $this->normalizeOrderStatusName($statusName);
        if ($normalized === '') return '';

        if (str_contains($normalized, 'huy') || str_contains($normalized, 'cancel')) {
            return 'canceled';
        }

        if ((str_contains($normalized, 'dang') && str_contains($normalized, 'giao')) || str_contains($normalized, 'shipping')) {
            return 'shipping';
        }

        if ((str_contains($normalized, 'da') && str_contains($normalized, 'giao')) || str_contains($normalized, 'delivered')) {
            return 'delivered';
        }

        if (str_contains($normalized, 'xac nhan') || str_contains($normalized, 'confirm')) {
            return 'confirmed';
        }

        if (str_contains($normalized, 'moi dat') || str_contains($normalized, 'moi') || str_contains($normalized, 'new') || str_contains($normalized, 'cho xac nhan')) {
            return 'new';
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

    private function buildOrderQuickStats(): array
    {
        $now = Carbon::now();
        $monthStart = $now->copy()->startOfMonth()->startOfDay();
        $monthEnd = $now->copy()->endOfMonth()->endOfDay();
        $monthStartText = $monthStart->toDateTimeString();
        $monthEndText = $monthEnd->toDateTimeString();
        $monthScopedQuery = function () use ($monthStartText, $monthEndText) {
            return OrdersModel::query()->whereBetween('created_at', [$monthStartText, $monthEndText]);
        };

        $weekStart = $now->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $weekEnd = $now->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();
        if ($weekStart->lt($monthStart)) {
            $weekStart = $monthStart->copy();
        }
        if ($weekEnd->gt($monthEnd)) {
            $weekEnd = $monthEnd->copy();
        }

        $stats = [
            'total_today' => (int) OrdersModel::whereBetween('created_at', [
                $now->copy()->startOfDay()->toDateTimeString(),
                $now->copy()->endOfDay()->toDateTimeString()
            ])->count(),
            'total_week' => (int) OrdersModel::whereBetween('created_at', [
                $weekStart->toDateTimeString(),
                $weekEnd->toDateTimeString()
            ])->count(),
            'total_month' => (int) $monthScopedQuery()->count(),
            'pending_confirm' => 0,
            'processing_packing' => 0,
            'shipping' => 0,
            'delivered' => 0,
            'canceled' => 0,
        ];

        $statusGroups = $this->resolveOrderQuickStatStatusGroups();
        foreach ($statusGroups as $key => $ids) {
            if (isset($stats[$key]) && !empty($ids)) {
                $stats[$key] = (int) $monthScopedQuery()->whereIn('order_status', $ids)->count();
            }
        }

        $stats['need_attention'] = (int) ($stats['pending_confirm'] + $stats['processing_packing'] + $stats['shipping']);
        $stats['delivery_rate'] = $stats['total_month'] > 0
            ? round(((float) $stats['delivered'] * 100) / (float) $stats['total_month'], 1)
            : 0.0;

        $previousMonthStart = $now->copy()->subMonthNoOverflow()->startOfMonth()->startOfDay();
        $previousMonthEnd = $now->copy()->subMonthNoOverflow()->endOfMonth()->endOfDay();
        $previousMonthTotal = (int) OrdersModel::whereBetween('created_at', [
            $previousMonthStart->toDateTimeString(),
            $previousMonthEnd->toDateTimeString()
        ])->count();
        $stats['prev_month_total'] = $previousMonthTotal;
        $stats['month_diff'] = (int) ($stats['total_month'] - $previousMonthTotal);

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

    public function delete($com, $act, $type, Request $request)
    {
        if (!empty($request->id)) {
            $id = $request->id;
            OrdersModel::where('id', $id)->delete();
            OrderHistoryModel::where('id_order', $id)->delete();
        } elseif (!empty($request->listid)) {
            $listid = explode(",", $request->listid);

            for ($i = 0; $i < count($listid); $i++) {
                $id = htmlspecialchars($listid[$i]);
                OrdersModel::where('id', $id)->delete();
                OrderHistoryModel::where('id_order', $id)->delete();
            }
        }
        response()->redirect(url('admin', ['com' => $com, 'act' => 'man', 'type' => $type], ['page' => $request->page]));
    }


    public function manExcel($com, $act, $type, Request $request)
    {
        $id = (int) ($request->id ?? 0);
        $rows = OrdersModel::select('*')->where('id', $id)->first();
        if (empty($rows)) {
            return transfer('Don hang khong ton tai.', false, linkReferer());
        }

        $normalizeArray = function ($value): array {
            if ($value instanceof \Illuminate\Support\Collection) {
                return $value->toArray();
            }
            if (is_string($value)) {
                $decoded = json_decode($value, true);
                return is_array($decoded) ? $decoded : [];
            }
            if (is_array($value)) {
                return $value;
            }
            if (is_object($value)) {
                return (array) $value;
            }
            return [];
        };

        $infoOrder = array_values($normalizeArray($rows['order_detail'] ?? []));
        $infoUser = $normalizeArray($rows['info_user'] ?? []);

        $createdAt = $rows['created_at'] ?? null;
        $createdAtText = '-';
        if (is_numeric($createdAt)) {
            $createdAtText = date('d/m/Y H:i:s', (int) $createdAt);
        } elseif (!empty($createdAt)) {
            $createdAtTimestamp = strtotime((string) $createdAt);
            if (!empty($createdAtTimestamp)) {
                $createdAtText = date('d/m/Y H:i:s', $createdAtTimestamp);
            }
        }

        $tempPrice = max(0, (float) ($rows['temp_price'] ?? 0));
        $shipPrice = max(0, (float) ($rows['ship_price'] ?? 0));
        $totalPrice = max(0, (float) ($rows['total_price'] ?? 0));
        $voucherDiscount = isset($rows['voucher_discount'])
            ? max(0, (float) ($rows['voucher_discount'] ?? 0))
            : max(0, $tempPrice + $shipPrice - $totalPrice);
        $orderStatusName = trim((string) (OrderStatusModel::where('id', (int) ($rows['order_status'] ?? 0))->value('namevi') ?? ''));
        $orderStatusName = $orderStatusName !== '' ? $orderStatusName : '-';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Chi tiết đơn');
        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);

        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', 'CHI TIẾT ĐƠN HÀNG');
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F4E78']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        $sheet->setCellValue('A3', 'Mã đơn');
        $sheet->setCellValueExplicit('B3', (string) ($rows['code'] ?? ''), DataType::TYPE_STRING);
        $sheet->setCellValue('D3', 'Ngày đặt');
        $sheet->setCellValue('E3', $createdAtText);
        $sheet->setCellValue('A4', 'Khách hàng');
        $sheet->setCellValue('B4', (string) ($infoUser['fullname'] ?? ''));
        $sheet->setCellValue('D4', 'Điện thoại');
        $sheet->setCellValueExplicit('E4', (string) ($infoUser['phone'] ?? ''), DataType::TYPE_STRING);
        $sheet->setCellValue('A5', 'Email');
        $sheet->setCellValue('B5', (string) ($infoUser['email'] ?? ''));
        $sheet->setCellValue('D5', 'Địa chỉ');
        $sheet->setCellValue('E5', (string) ($infoUser['address'] ?? ''));
        $sheet->setCellValue('A6', 'Tình trạng');
        $sheet->setCellValue('B6', $orderStatusName);
        $sheet->mergeCells('B3:C3');
        $sheet->mergeCells('E3:G3');
        $sheet->mergeCells('B4:C4');
        $sheet->mergeCells('E4:G4');
        $sheet->mergeCells('B5:C5');
        $sheet->mergeCells('E5:G5');
        $sheet->mergeCells('B6:G6');
        $sheet->getStyle('A3:G6')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD9D9D9']]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle('B3:G6')->getAlignment()->setWrapText(true);
        $sheet->getRowDimension(5)->setRowHeight(36);
        $sheet->getRowDimension(6)->setRowHeight(26);
        $sheet->getStyle('A3:A6')->getFont()->setBold(true);
        $sheet->getStyle('D3:D5')->getFont()->setBold(true);

        $headerRow = 7;
        $dataStartRow = 8;
        $headers = ['A' => 'STT', 'B' => 'Mã SP', 'C' => 'Tên sản phẩm', 'D' => 'Phân loại', 'E' => 'Đơn giá', 'F' => 'SL', 'G' => 'Thành tiền'];
        foreach ($headers as $col => $label) {
            $sheet->setCellValue($col . $headerRow, $label);
        }
        $sheet->getStyle('A' . $headerRow . ':G' . $headerRow)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2F75B5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF1D3557']]],
        ]);
        $sheet->getRowDimension($headerRow)->setRowHeight(22);

        $rowIndex = $dataStartRow;
        $stt = 1;
        foreach ($infoOrder as $item) {
            $item = $normalizeArray($item);
            $options = $normalizeArray($item['options'] ?? []);
            $itemProduct = $normalizeArray($options['itemProduct'] ?? []);

            $productCode = '';
            $productId = (int) ($itemProduct['id'] ?? ($item['id'] ?? 0));
            $propertyNames = [];
            $propertyIds = [];
            foreach ($normalizeArray($options['properties'] ?? []) as $property) {
                $property = $normalizeArray($property);
                $propertyId = (int) ($property['id'] ?? 0);
                if ($propertyId > 0) {
                    $propertyIds[] = $propertyId;
                }
                $propertyName = trim((string) ($property['namevi'] ?? ($property['name'] ?? '')));
                if ($propertyName !== '') {
                    $propertyNames[] = $propertyName;
                }
            }
            $propertyIds = array_values(array_unique($propertyIds));
            if ($productId > 0 && !empty($propertyIds)) {
                $variantCodeQuery = ProductPropertiesModel::select('code')
                    ->where('id_parent', $productId);
                foreach ($propertyIds as $propertyId) {
                    $variantCodeQuery->whereRaw('FIND_IN_SET(?, id_properties)', [$propertyId]);
                }
                $variantCodeQuery->whereRaw(
                    "(LENGTH(id_properties) - LENGTH(REPLACE(id_properties, ',', '')) + 1) = ?",
                    [count($propertyIds)],
                );
                $variantCode = trim((string) ($variantCodeQuery->value('code') ?? ''));
                if ($variantCode !== '') {
                    $productCode = $variantCode;
                }
            }
            if ($productCode === '') {
                $productCode = trim((string) ($options['productCode'] ?? ''));
            }
            if ($productCode === '') {
                $productCode = trim((string) ($itemProduct['code'] ?? ''));
            }
            if ($productCode === '') {
                $productCode = '-';
            }
            $propertyText = !empty($propertyNames) ? implode(', ', $propertyNames) : '-';

            $name = trim((string) ($item['name'] ?? 'Sản phẩm'));
            $price = max(0, (float) ($item['price'] ?? 0));
            $qty = max(0, (int) ($item['qty'] ?? 0));
            $lineTotal = $price * $qty;

            $sheet->setCellValue('A' . $rowIndex, $stt);
            $sheet->setCellValueExplicit('B' . $rowIndex, $productCode, DataType::TYPE_STRING);
            $sheet->setCellValue('C' . $rowIndex, $name);
            $sheet->setCellValue('D' . $rowIndex, $propertyText);
            $sheet->setCellValue('E' . $rowIndex, $price);
            $sheet->setCellValue('F' . $rowIndex, $qty);
            $sheet->setCellValue('G' . $rowIndex, $lineTotal);

            $stt++;
            $rowIndex++;
        }

        $dataEndRow = max($headerRow, $rowIndex - 1);
        if ($dataEndRow >= $dataStartRow) {
            $sheet->getStyle('A' . $dataStartRow . ':G' . $dataEndRow)->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE0E0E0']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getStyle('A' . $dataStartRow . ':A' . $dataEndRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $dataStartRow . ':F' . $dataEndRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E' . $dataStartRow . ':G' . $dataEndRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('C' . $dataStartRow . ':D' . $dataEndRow)->getAlignment()->setWrapText(true);
            $sheet->getStyle('E' . $dataStartRow . ':E' . $dataEndRow)->getNumberFormat()->setFormatCode('#,##0" đ"');
            $sheet->getStyle('G' . $dataStartRow . ':G' . $dataEndRow)->getNumberFormat()->setFormatCode('#,##0" đ"');
        }

        $summaryStartRow = $dataEndRow + 2;
        $summaryRows = [
            ['label' => 'Tạm tính', 'value' => $tempPrice],
            ['label' => 'Phí vận chuyển', 'value' => $shipPrice],
            ['label' => 'Giảm voucher', 'value' => -$voucherDiscount],
            ['label' => 'Tổng thanh toán', 'value' => $totalPrice],
        ];
        foreach ($summaryRows as $index => $summary) {
            $targetRow = $summaryStartRow + $index;
            $sheet->mergeCells('E' . $targetRow . ':F' . $targetRow);
            $sheet->setCellValue('E' . $targetRow, $summary['label']);
            $sheet->setCellValue('G' . $targetRow, $summary['value']);
        }
        $summaryEndRow = $summaryStartRow + count($summaryRows) - 1;
        $sheet->getStyle('E' . $summaryStartRow . ':G' . $summaryEndRow)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCCCCCC']]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle('E' . $summaryStartRow . ':F' . $summaryEndRow)->getFont()->setBold(true);
        $sheet->getStyle('E' . $summaryStartRow . ':F' . $summaryEndRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('G' . $summaryStartRow . ':G' . $summaryEndRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('G' . $summaryStartRow . ':G' . $summaryEndRow)->getNumberFormat()->setFormatCode('#,##0" đ"');
        $sheet->getStyle('E' . $summaryEndRow . ':G' . $summaryEndRow)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FF1F4E78']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE8F1FB']],
        ]);

        $columnWidths = ['A' => 12, 'B' => 24, 'C' => 56, 'D' => 36, 'E' => 18, 'F' => 12, 'G' => 20];
        foreach ($columnWidths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }
        $sheet->freezePane('A' . $dataStartRow);
        $sheet->setAutoFilter('A' . $headerRow . ':G' . $headerRow);

        while (ob_get_level() > 0) {
            @ob_end_clean();
        }
        $orderCodeForFile = preg_replace('/[^A-Za-z0-9\-_]/', '', (string) ($rows['code'] ?? ''));
        if ($orderCodeForFile === '') {
            $orderCodeForFile = 'don_' . (int) ($rows['id'] ?? 0);
        }
        $exportedAt = date('YmdHis');
        $fileName = 'Chi_tiet_don_hang_' . $orderCodeForFile . '_' . $exportedAt . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');
        header('Expires: 0');

        $writer = new Xlsx($spreadsheet);
        $writer->setPreCalculateFormulas(false);
        $writer->save('php://output');
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        exit;
    }


    public function manExcelLegacy($com, $act, $type, Request $request)
    {
        $id = $request->id;
        $rows = OrdersModel::select('*')
            ->where('id', $id)
            ->orderBy('numb', 'asc')
            ->first();
        if (!empty($rows)) {
            $infoOrderRaw = $rows['order_detail'] ?? [];
            if (is_string($infoOrderRaw)) {
                $decodedOrder = json_decode($infoOrderRaw, true);
                $infoOrder = is_array($decodedOrder) ? $decodedOrder : [];
            } elseif (is_array($infoOrderRaw)) {
                $infoOrder = $infoOrderRaw;
            } else {
                $infoOrder = (array) $infoOrderRaw;
            }

            $infoUserRaw = $rows['info_user'] ?? [];
            if (is_string($infoUserRaw)) {
                $decodedUser = json_decode($infoUserRaw, true);
                $infoUser = is_array($decodedUser) ? $decodedUser : [];
            } elseif (is_array($infoUserRaw)) {
                $infoUser = $infoUserRaw;
            } else {
                $infoUser = (array) $infoUserRaw;
            }
            $array_columns = array(
                'numb' => 'STT',
                'name' => "Tên sản phẩm",
                'price' => "Giá",
                'qty' => "Số lượng",
                'sum' => "Tổng tiền"
            );
            $array_width = [5, 50, 20, 10, 20];
            // Tạo một đối tượng spreadsheet mới
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Thêm đoạn văn bản vào 3 dòng đầu tiên
            $sheet->setCellValue('A1', "THÔNG TIN ĐƠN HÀNG");
            $sheet->setCellValue('A2', (string) ($infoUser['fullname'] ?? ''));
            $sheet->setCellValue('A3', (string) ($infoUser['email'] ?? ''));
            $sheet->setCellValue('A4', (string) ($infoUser['phone'] ?? ''));
            $sheet->setCellValue('A5', (string) ($infoUser['address'] ?? ''));
            $sheet->setCellValue('A6', 'Mã đơn hàng: ' . $rows['code']);

            // Gộp các ô cho đoạn văn bản
            $sheet->mergeCells('A1:E1');
            $sheet->mergeCells('A2:E2');
            $sheet->mergeCells('A3:E3');
            $sheet->mergeCells('A4:E4');
            $sheet->mergeCells('A5:E5');
            $sheet->mergeCells('A6:E6');

            // Áp dụng kiểu dáng cho đoạn văn bản
            $textStyle = [
                'font' => [
                    'bold' => true,
                    'size' => 16,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ];

            $textStyle1 = [
                'font' => [
                    'size' => 13,
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ];
            $sheet->getStyle('A1:E1')->applyFromArray($textStyle);
            $sheet->getStyle('A2:E2')->applyFromArray($textStyle1);
            $sheet->getStyle('A3:E3')->applyFromArray($textStyle1);
            $sheet->getStyle('A4:E4')->applyFromArray($textStyle1);
            $sheet->getStyle('A5:E5')->applyFromArray($textStyle1);
            $sheet->getStyle('A6:E6')->applyFromArray($textStyle1);

            // Tiêu đề
            $rowIndex = 8;
            $colIndex = 'A';
            foreach ($array_columns as  $cellValue) {
                $sheet->setCellValue($colIndex . $rowIndex, $cellValue);
                $colIndex++;
            }

            // Điền dữ liệu vào sheet bắt đầu từ dòng thứ tư
            $rowIndex = 9;
            $num = 1;
            foreach ($infoOrder as $v) {
                $v = is_array($v) ? $v : (array) $v;
                $name = (string) ($v['name'] ?? '');
                $price = (float) ($v['price'] ?? 0);
                $qty = max(0, (int) ($v['qty'] ?? 0));
                $colIndex = 'A';
                foreach ($array_columns as $k => $cellValue) {
                    if ($k == 'numb') {
                        $sheet->setCellValue($colIndex . $rowIndex, $num);
                    } elseif ($k == 'sum') {
                        $sheet->setCellValue($colIndex . $rowIndex, $price * $qty);
                    } elseif ($k == 'name') {
                        $sheet->setCellValue($colIndex . $rowIndex, $name);
                    } elseif ($k == 'price') {
                        $sheet->setCellValue($colIndex . $rowIndex, $price);
                    } elseif ($k == 'qty') {
                        $sheet->setCellValue($colIndex . $rowIndex, $qty);
                    } else {
                        $sheet->setCellValue($colIndex . $rowIndex, (string) ($v[$k] ?? ''));
                    }
                    $colIndex++;
                }
                $num++;
                $rowIndex++;
            }

            // Áp dụng kiểu dáng cho tiêu đề
            $headerStyle = [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF4CAF50'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FF000000'],
                    ],
                ],
            ];
            $sheet->getStyle('A8:E8')->applyFromArray($headerStyle);

            // Áp dụng kiểu dáng cho dữ liệu
            $dataStyle = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FF000000'],
                    ],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrap' => true
                ],
            ];

            $sheet->getStyle('A8:E' . ($rowIndex - 1))->applyFromArray($dataStyle)->getAlignment()->setWrapText(true);

            // Thiết lập độ rộng cột

            foreach (range('A', 'E') as $k => $columnID) {
                $sheet->getColumnDimension($columnID)->setWidth($array_width[$k]);
            }

            // Thiết lập header để tải xuống file Excel
            while (ob_get_level() > 0) {
                @ob_end_clean();
            }
            $orderCodeForFile = preg_replace('/[^A-Za-z0-9\-_]/', '', (string) ($rows['code'] ?? ''));
            if ($orderCodeForFile === '') {
                $orderCodeForFile = 'don_' . (int) ($rows['id'] ?? 0);
            }
            $exportedAt = date('YmdHis');
            $fileName = 'Chi_tiet_don_hang_' . $orderCodeForFile . '_' . $exportedAt . '.xlsx';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $fileName . '"');
            header('Cache-Control: max-age=0');
            header('Pragma: public');
            header('Expires: 0');

            // Tạo đối tượng writer để ghi file Excel
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
            exit;
        } else {
            return transfer('Đơn hàng không tồn tại.', false, linkReferer());
        }
    }
}
