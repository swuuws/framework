<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws;

use swuuws\exception\HookException;

class Hook
{
    private static $swuuws_hook = [];
    public static function add($name, $exec)
    {
        $name = trim($name);
        if(is_array($exec)){
            self::$swuuws_hook[$name] = array_merge(self::$swuuws_hook[$name], $exec);
        }
        else{
            self::$swuuws_hook[$name][] = trim($exec);
        }
    }
    public static function listen($name, &$args = null)
    {
        $name = trim($name);
        if(isset(self::$swuuws_hook[$name])){
            if(!empty(self::$swuuws_hook[$name])){
                $result = [];
                foreach(self::$swuuws_hook[$name] as $nkey => $nval){
                    $exec = str_replace('\\', '/', $nval);
                    $execArr = explode('/', $exec);
                    $class = array_pop($execArr);
                    $class = trim($class);
                    if(count($execArr) > 0){
                        $namespace = implode('\\', $execArr);
                    }
                    else{
                        $namespace = 'plugin';
                    }
                    $namespace .= '\\' . $class;
                    $execClass = $namespace . '\\' . Swuuws::transform($class);
                    $swuuws_class = new $execClass();
                    if(method_exists($swuuws_class, $name)){
                        $result[$exec] = call_user_func_array([$swuuws_class, $name], [&$args]);
                    }
                }
                return $result;
            }
            else{
                throw new HookException(1, ': ' . $name);
            }
        }
        else{
            throw new HookException(0, ': ' . $name);
        }
    }
}