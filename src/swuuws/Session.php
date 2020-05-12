<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws;

class Session
{
    private static $session = false;
    private static function start()
    {
        if(!self::$session){
            self::$session = session_start();
        }
    }
    public static function set($name, $val)
    {
        self::start();
        if(self::$session){
            $_SESSION[$name] = $val;
            return true;
        }
        else{
            return false;
        }
    }
    public static function get($name)
    {
        self::start();
        if(isset($_SESSION[$name])){
            return $_SESSION[$name];
        }
        else{
            return false;
        }
    }
    public static function has($name)
    {
        self::start();
        return isset($_SESSION[$name]);
    }
    public static function delete($name)
    {
        self::start();
        unset($_SESSION[$name]);
    }
    public static function destroy()
    {
        self::start();
        $result = session_destroy();
        if($result){
            unset($_SESSION);
        }
        self::$session = !$result;
        return $result;
    }
}