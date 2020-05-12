<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws;

class Cache
{
    private static $name = '';
    private static $group = '';
    private static $type = '';
    private static $instance;
    private static function instance($swuuws)
    {
        if(empty(self::$instance)){
            self::$instance = new Cache();
        }
        return self::$instance;
    }
    /**
     * Delete cache.
     *
     * @param  $name
     */
    public static function delete($name)
    {
        self::carry('delete', $name);
    }
    /**
     * Delete cache group.
     *
     * @param  $group
     */
    public static function delGroup($group)
    {
        self::carry('delGroup', $group);
    }
    /**
     * Delete all caches.
     *
     * @param  none
     */
    public static function delAll()
    {
        self::carry('delAll');
    }
    /**
     * Determine if there is a cache.
     *
     * @param  $name
     * @return boolean
     */
    public static function has($name)
    {
        return self::carry('has', $name);
    }
    /**
     * Get cache.
     *
     * @param  $name
     * @return mixed
     */
    public static function get($name)
    {
        return self::carry('get', $name);
    }
    /**
     * Set cache.
     *
     * @param  $name, $value, $expire
     */
    public static function set($name, $value, $expire = 3600)
    {
        self::$name = $name;
        if(empty(self::$type)){
            self::$type = ucfirst(strtolower(Env::get('CACHE_TYPE')));
        }
        call_user_func('swuuws\\cache\\' . self::$type . '::set', $name, $value, $expire);
        if(!empty(self::$group)){
            call_user_func('swuuws\\cache\\' . self::$type . '::group', self::$group, self::$name);
            self::$group = '';
            self::$name = '';
        }
        return self::instance(self::swuuws());
    }
    /**
     * Cache group.
     *
     * @param  $name
     * @return boolean
     */
    public static function group($name)
    {
        self::$group = $name;
        return self::instance(self::swuuws());
    }
    private static function swuuws()
    {
        if(!empty(self::$group) && !empty(self::$name)){
            call_user_func('swuuws\\cache\\' . self::$type . '::group', self::$group, self::$name);
            self::$group = '';
            self::$name = '';
        }
        return false;
    }
    private static function carry($behavior, $name = '')
    {
        if(empty(self::$type)){
            self::$type = ucfirst(strtolower(Env::get('CACHE_TYPE')));
        }
        if(empty($name)){
            return call_user_func('swuuws\\cache\\' . self::$type . '::' . $behavior);
        }
        else{
            return call_user_func('swuuws\\cache\\' . self::$type . '::' . $behavior, $name);
        }
    }
}