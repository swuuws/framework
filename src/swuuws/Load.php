<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws;

use swuuws\exception\RouteException;

class Load
{
    public static function load($controller, $method, $parameter = [], $isThrow = true)
    {
        $controller = trim(str_replace('/', '\\', $controller), '\\');
        try{
            $refl = new \ReflectionClass('app' . '\\' . $controller);
        } catch(\Exception $e){
            if($isThrow){
                throw new RouteException(6, ': ' . $controller);
            }
            else{
                return false;
            }
        }
        $instance = $refl->newInstance();
        $hasMethod = false;
        $methods = $refl->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach($methods as $val){
            if($method == $val->name){
                $hasMethod = true;
                break;
            }
        }
        if($hasMethod){
            $instance_method = $refl->getMethod($method);
            if($instance_method->isPublic()){
                $args = [];
                $params = $instance_method->getParameters();
                foreach($params as $param){
                    if(isset($parameter[$param->getName()])){
                        $args[] = $parameter[$param->getName()];
                    }
                    else{
                        try{
                            $args[] = $param->getDefaultValue();
                        } catch(\Exception $e){
                            throw new RouteException(10, ': ' . $controller . '\\' . $method);
                        }
                    }
                }
                $result = $instance_method->invokeArgs($instance, $args);
            }
            else{
                throw new Exception('Cannot call private methods: ' . $method);
            }
        }
        else{
            throw new RouteException(5, ': ' . $method);
        }
        if(!empty($result)){
            Response::write($result);
        }
        return true;
    }
    public static function loadFile($file, $prefix = '')
    {
        $result = [];
        if(is_array($file)){
            foreach($file as $val){
                $re = self::loadSingleFile(rtrim($prefix, DS) . DS . ltrim($val, DS));
                if(is_array($re)){
                    $result = array_merge($result, $re);
                }
            }
        }
        else{
            $result = self::loadSingleFile(rtrim($prefix, DS) . DS . ltrim($file, DS));
        }
        return $result;
    }
    public static function loadSingleFile($file, $regulated = false){
        if(!$regulated){
            $file = str_replace(['/', '\\'], DS, $file);
            if(stripos($file, APP) === false){
                $file = APP . trim($file, DS);
            }
            if(substr($file, -4) != '.php'){
                $file .= '.php';
            }
        }
        $result = '';
        if(is_file($file)){
            $result = require_once $file;
        }
        return $result;
    }
}