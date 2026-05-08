<?php
namespace LARAVEL\Core\Support\Facades;

/**
 * @method static getTodayRecord()
 * @method static getYesterdayRecord()
 * @method static getWeekRecord()
 * @method static getMonthRecord()
 * @method static getTotalRecord()
 * @method static getOnline()
 */
class Statistic extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'statistic';
    }
}