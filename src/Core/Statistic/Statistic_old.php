<?php
namespace LARAVEL\Core\Statistic;

use Carbon\Carbon;
use DB;
use LARAVEL\Core\Container;
use LARAVEL\Core\Support\Facades\Session;

class Statistic
{
    public int $toDayRec;
    public int $yesRec;
    public int $weekRec;
    public int $monthRec;
    public int $totalRec;
    protected Carbon $today;
    protected int $lockTime = 900;
    protected int $initialValue = 1;
    protected int $records = 100000;
    public function __construct()
    {
        $this->today = Carbon::now();
        $this->getCounter();
    }
    public function getCounter(): void
    {
        // Thiết lập các mốc thời gian
        $yesterdayStart = $this->today->copy()->subDay()->startOfDay()->timestamp;
        $todayStart = $this->today->copy()->startOfDay()->timestamp;
        $monthStart = $this->today->copy()->startOfMonth()->timestamp;
        $weekStart = $this->today->copy()->startOfWeek()->timestamp;

        // Lấy thông tin IP và user agent
        $ip = request()->ip();
        $platform = strtolower(agent()->platform());
        $device = strtolower(agent()->deviceType());
        $browser = str_replace(' ', '_', strtolower(agent()->browser()));

        // Lấy tổng số lượt truy cập
        $totalVisitors = DB::table('counter')->max('id') ?? 0;
        $allVisitors = $totalVisitors + $this->initialValue;

        // Xóa dữ liệu cũ nếu vượt quá số lượng giới hạn
        $excessRecords = $allVisitors - $this->records;
        if ($excessRecords > 0) {
            DB::table('counter')->where('id', '<', $excessRecords)->delete();
        }

        // Kiểm tra nếu IP đã truy cập trong thời gian khóa
        $recentVisit = DB::table('counter')
            ->where('ip', $ip)
            ->where('os', $platform)
            ->where('device', $device)
            ->where('browser', $browser)
            ->whereRaw('(tm + ?) > ?', [$this->lockTime, $this->today->timestamp])
            ->exists();

        // Ghi nhận lượt truy cập mới nếu không phải admin và không bị khóa
        if (!$recentVisit && request()->segment(1) != 'admin') {
            DB::table('counter')->insert([
                'tm' => $this->today->timestamp,
                'ip' => $ip,
                'os' => $platform,
                'browser' => $browser,
                'device' => $device,
            ]);
        }

        // Truy vấn số liệu thống kê
        $stats = DB::table('counter')
            ->selectRaw("
                COUNT(CASE WHEN tm >= ? THEN 1 END) AS todayRecord,
                COUNT(CASE WHEN tm >= ? AND tm < ? THEN 1 END) AS yesterdayRecord,
                COUNT(CASE WHEN tm >= ? THEN 1 END) AS weekRecord,
                COUNT(CASE WHEN tm >= ? THEN 1 END) AS monthRecord,
                MAX(id) AS totalRecord
            ", [$todayStart, $yesterdayStart, $todayStart, $weekStart, $monthStart])
            ->first();

        // Gán số liệu vào thuộc tính của class
        $this->toDayRec = $stats->todayRecord ?? 0;
        $this->yesRec = $stats->yesterdayRecord ?? 0;
        $this->weekRec = $stats->weekRecord ?? 0;
        $this->monthRec = $stats->monthRecord ?? 0;
        $this->totalRec = $stats->totalRecord ?? 0;
    }

    public function getTodayRecord(): int
    {
        return $this->toDayRec;
    }
    public function getYesterdayRecord(): int
    {
        return $this->yesRec;
    }
    public function getWeekRecord(): int
    {
        return $this->weekRec;
    }
    public function getMonthRecord(): int
    {
        return $this->monthRec;
    }
    public function getTotalRecord(): int
    {
        return $this->totalRec;
    }
    public function getOnline()
    {
        $session = Session::getID();
        $time_check = $this->today->timestamp - 50;
        $ip = request()->ip();
        $result = DB::table('user_online')->where('session', $session)->get();
        if (count($result) == 0) {
            DB::table('user_online')->insert(['session' => $session, 'time' => $this->today->timestamp, 'ip' => $ip]);
        } else {
            DB::table('user_online')->where('session', $session)->update(['time' => $this->today->timestamp]);
        }
        DB::table('user_online')->where('time', '<', $time_check)->delete();
        return DB::table('user_online')->count();
    }
}
