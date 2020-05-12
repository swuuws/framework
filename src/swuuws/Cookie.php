<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws;

class Cookie
{
    /**
     * Setting cookie.
     *
     * @param  $name, $value, $expire, $path, $domain, $secure, $httponly
     */
    public static function set($name, $value = '', $expire = 3600, $path = '/', $domain = '', $secure = false, $httponly = true)
    {
        if(self::has($name)){
            self::delete($name, $path, $domain);
        }
        setcookie($name, $value, time()+$expire, $path, $domain, $secure, $httponly);
    }
    /**
     * Delete cookie.
     *
     * @param  $name, $path, $domain
     */
    public static function delete($name, $path = '/', $domain = '')
    {
        setcookie($name, '', time() - 3600, $path, $domain);
    }
    /**
     * Get cookie.
     *
     * @param  $name
     * @return cookie
     */
    public static function get($name)
    {
        $result = isset($_COOKIE[$name]) ? $_COOKIE[$name] : false;
        return $result;
    }
    /**
     * Determine if a cookie exists.
     *
     * @param  $name
     * @return boolean
     */
    public static function has($name)
    {
        return isset($_COOKIE[$name]);
    }
}