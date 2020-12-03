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
    public static function year($time = null)
    {
        if(!empty($time)){
            if(gettype($time) == 'integer'){
                return date('Y', $time);
            }
            else{
                return date('Y', strtotime($time));
            }
        }
        return date('Y');
    }
    /**
     * Get month.
     *
     * @param  none
     * @return string
     */
    public static function month($time = null)
    {
        if(!empty($time)){
            if(gettype($time) == 'integer'){
                return date('m', $time);
            }
            else{
                return date('m', strtotime($time));
            }
        }
        return date('m');
    }
    /**
     * Get day.
     *
     * @param  none
     * @return string
     */
    public static function day($time = null)
    {
        if(!empty($time)){
            if(gettype($time) == 'integer'){
                return date('d', $time);
            }
            else{
                return date('d', strtotime($time));
            }
        }
        return date('d');
    }
    /**
     * Get hour.
     *
     * @param  none
     * @return string
     */
    public static function hour($time = null)
    {
        if(!empty($time)){
            if(gettype($time) == 'integer'){
                return date('H', $time);
            }
            else{
                return date('H', strtotime($time));
            }
        }
        return date('H');
    }
    /**
     * Get minute.
     *
     * @param  none
     * @return string
     */
    public static function minute($time = null)
    {
        if(!empty($time)){
            if(gettype($time) == 'integer'){
                return date('i', $time);
            }
            else{
                return date('i', strtotime($time));
            }
        }
        return date('i');
    }
    /**
     * Get second.
     *
     * @param  none
     * @return string
     */
    public static function second($time = null)
    {
        if(!empty($time)){
            if(gettype($time) == 'integer'){
                return date('s', $time);
            }
            else{
                return date('s', strtotime($time));
            }
        }
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