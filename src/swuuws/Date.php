<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws;

class Date
{
    /**
     * Formatting the current date.
     *
     * @param  $format
     * @return string
     */
    public static function now($format = null)
    {
        if($format == null){
            $format = 'Y-m-d H:i:s';
        }
        return date($format);
    }
    /**
     * Get year.
     *
     * @param  none
     * @return string
     */
    public static function year()
    {
        return date('Y');
    }
    /**
     * Get month.
     *
     * @param  none
     * @return string
     */
    public static function month()
    {
        return date('m');
    }
    /**
     * Get day.
     *
     * @param  none
     * @return string
     */
    public static function day()
    {
        return date('d');
    }
    /**
     * Get hour.
     *
     * @param  none
     * @return string
     */
    public static function hour()
    {
        return date('H');
    }
    /**
     * Get minute.
     *
     * @param  none
     * @return string
     */
    public static function minute()
    {
        return date('i');
    }
    /**
     * Get second.
     *
     * @param  none
     * @return string
     */
    public static function second()
    {
        return date('s');
    }
    /**
     * Get year and month.
     *
     * @param  $joiner
     * @return string
     */
    public static function yearMonth($joiner = '')
    {
        return date('Y') . $joiner . date('m');
    }
    /**
     * Get month and day.
     *
     * @param  $joiner
     * @return string
     */
    public static function monthDay($joiner = '')
    {
        return date('m') . $joiner . date('d');
    }
    /**
     * Get year, month and day.
     *
     * @param  $joiner
     * @return string
     */
    public static function yearMonthDay($joiner = '')
    {
        return date('Y') . $joiner . date('m') . $joiner . date('d');
    }
}