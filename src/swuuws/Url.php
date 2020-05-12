<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws;

class Url
{
    /**
     * Get url.
     *
     * @param  $name, $array
     */
    public static function url($name, $array = [])
    {
        return Route::url($name, $array);
    }
    /**
     * Turn to url.
     *
     * @param  $url
     */
    public static function to($url)
    {
        header('Location: ' . Route::url($url));
        exit();
    }
    /**
     * Permanently turn to url.
     *
     * @param  $url
     */
    public static function moveto($url)
    {
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: ' . Route::url($url));
        exit();
    }
}