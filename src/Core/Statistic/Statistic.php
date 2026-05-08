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
    public function getCounter(): Statistic
    {
        $yesterDayStart = $this->today->copy()->subDay()->startOfDay()->timestamp;
        $dayStart = $this->today->copy()->startOfDay()->timestamp;
        $monthStart = $this->today->copy()->startOfMonth()->timestamp;
        $weekStart = $this->today->copy()->startOfWeek()->timestamp;
        $ip = request()->ip();
        $t = DB::table('counter')->selectRaw("MAX(id) as totalrec, SUM( CASE WHEN tm > ? THEN 1 ELSE 0 END ) as todayrecord, SUM( CASE WHEN tm > ? THEN 1 ELSE 0 END ) as yesterdayrec, SUM( CASE WHEN tm > ? THEN 1 ELSE 0 END ) as weekrec, SUM( CASE WHEN tm > ? THEN 1 ELSE 0 END ) as monthrec, SUM( CASE WHEN (ip = ? AND os = ? AND device = ? AND browser = ? AND (tm + ?) > ?) THEN 1 ELSE 0 END ) as visitip", [
            $dayStart,
            $yesterDayStart,
            $weekStart,
            $monthStart,
            $ip,
            strtolower(agent()->platform()),
            strtolower(agent()->deviceType()),
            str_replace(' ', '_', strtolower(agent()->browser())),
            $this->lockTime,
            $this->today->timestamp
        ])
            ->first();
        $all_visitors = $t->totalrec;
        if ($all_visitors !== NULL) $all_visitors += $this->initialValue;
        else $all_visitors = $this->initialValue;
        $temp = $all_visitors - $this->records;
        if ($temp > 0)  DB::table('counter')->where('id', '<', $temp)->delete();
        // $vip = DB::table('counter')
        //     ->selectRaw("count(id) as visitip")
        //     ->where('ip', $ip)
        //     ->where('os', strtolower(agent()->platform()))
        //     ->where('device', strtolower(agent()->deviceType()))
        //     ->where('browser', str_replace(' ', '_', strtolower(agent()->browser())))
        //     ->whereRaw('(tm + ?) > ?', [$this->lockTime, $this->today->timestamp])->first();
        // $items = $vip->visitip;
        $items = $t->visitip;
        if (empty($items) && request()->segment(1) != 'admin')  DB::table('counter')->insert(['tm' => $this->today->timestamp, 'ip' => $ip, 'os' => strtolower(agent()->platform()), 'browser' => str_replace(' ', '_', strtolower(agent()->browser())), 'device' => agent()->deviceType()]);
        $n = $all_visitors;
        // $div = 100000;
        // while ($n > $div) $div *= 10;
        // $toDayRec = DB::table('counter')->selectRaw("count(*) as todayrecord")->where('tm', '>', $dayStart)->first();
        // $yesRec = DB::table('counter')->selectRaw("count(*) as yesterdayrec")->where('tm', '>', $yesterDayStart)->where('tm', '<', $dayStart)->first();
        // $weekRec = DB::table('counter')->selectRaw("count(*) as weekrec")->where('tm', '>=', $weekStart)->first();
        // $monthRec = DB::table('counter')->selectRaw("count(*) as monthrec")->where('tm', '>=', $monthStart)->first();
        // $totalRec = DB::table('counter')->selectRaw("max(id) as totalrec")->first();


        // $this->toDayRec = $toDayRec->todayrecord;
        // $this->yesRec = $yesRec->yesterdayrec;
        // $this->weekRec = $weekRec->weekrec;
        // $this->monthRec = $monthRec->monthrec;
        // $this->totalRec = $totalRec->totalrec;

        $this->toDayRec = $t->todayrecord;
        $this->yesRec = $t->yesterdayrec;
        $this->weekRec = $t->weekrec;
        $this->monthRec = $t->monthrec;
        $this->totalRec = $t->totalrec;

        return $this;
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
