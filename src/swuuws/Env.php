<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws;

class Env
{
    private static $env = [];
    /**
     * Initialize the environment.
     *
     * @param  none
     * @return string
     */
    public static function init()
    {
        mb_internal_encoding('UTF-8');
        $env = ROOT . '.env';
        if(is_file($env)){
            self::$env = parse_ini_file($env, true);
        }
        else{
            self::$env = Load::loadFile(['config', 'db', 'custom'], 'config');
        }
        date_default_timezone_set(isset(self::$env['TIME_ZONE']) ? self::$env['TIME_ZONE'] : 'UTC');
    }
    /**
     * Get environment variables.
     *
     * @param  $conf
     * @return string
     */
    public static function get($conf = null)
    {
        if($conf == null){
            return self::$env;
        }
        else{
            $conf = trim($conf);
            if(strpos($conf, '.') !== false){
                $confarr = explode('.', $conf);
                $result = self::$env;
                foreach($confarr as $val){
                    $result = $result[$val];
                }
                return $result;
            }
            else{
                return self::$env[$conf];
            }
        }
    }
    public static function set($name, $value)
    {
        self::$env[$name] = $value;
    }
    public static function has($name)
    {
        $name = trim($name);
        if(strpos($name, '.') !== false){
            $confarr = explode('.', $name);
            $env = self::$env;
            $result = true;
            foreach($confarr as $val){
                if(isset($env[$val])){
                    $env = $env[$val];
                }
                else{
                    $result = false;
                    break;
                }
            }
            return $result;
        }
        else{
            return isset(self::$env[$name]);
        }
    }
}