<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws;

class Debug
{
    private static $start = null;
    /**
     * Start the timer.
     */
    public static function startTime()
    {
        self::$start = microtime(true);
    }
    /**
     * Break the timer.
     *
     * @param  $places
     * @return float
     */
    public static function breakTime($places = 5)
    {
        $start = empty(self::$start) ? SWUUWS_START : self::$start;
        $interval = microtime(true) - $start;
        return sprintf("%.{$places}f",$interval);
    }
    /**
     * Format display output.
     *
     * @param  $param
     */
    public static function dump($param)
    {
        echo '<pre>';
        var_dump($param);
        echo '</pre>';
    }
    public static function version()
    {
        return 'swuuws 2.1.1';
    }
}