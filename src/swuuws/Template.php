<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws;

use swuuws\exception\TemplateException;

class Template
{
    public static function run($mm, $parameter = [])
    {
        $mm = str_replace('\\', '/', $mm);
        if(strpos($mm, '/') !== false){
            $mmArr = explode('/', $mm);
        }
        else{
            $mmArr = explode('/', $_ENV['SWUUWS_VIEW']);
            $mmArr[1] = $mm;
        }
        if(strpos($mmArr[0], '_') !== false){
            $mclass = Swuuws::transform($mmArr[0]);
        }
        else{
            $mclass = ucfirst($mmArr[0]);
        }
        $module = 'app' . '\\' . $mmArr[0] . '\\' . $mclass;
        if(strpos($mmArr[1], '_') !== false){
            $mmArr[1] = Swuuws::transform($mmArr[1], false);
        }
        try{
            $refl = new \ReflectionClass($module);
        } catch(\Exception $e){
            throw new TemplateException(0, ': ' . $mmArr[1]);
        }
        $instance = $refl->newInstance();
        $hasMethod = false;
        $methods = $refl->getMethods(\ReflectionMethod::IS_PRIVATE);
        foreach($methods as $val){
            if($mmArr[1] == $val->name){
                $hasMethod = true;
                break;
            }
        }
        if($hasMethod){
            $instance_method = $refl->getMethod($mmArr[1]);
            if($instance_method->isPrivate()){
                $instance_method->setAccessible(true);
                $args = [];
                $params = $instance_method->getParameters();
                foreach($params as $param){
                    if(isset($parameter[$param->getName()])){
                        $args[] = $parameter[$param->getName()];
                    }
                    else{
                        $args[] = $param->getDefaultValue();
                    }
                }
                $result = $instance_method->invokeArgs($instance, $args);
            }
            else{
                throw new TemplateException(1, ': ' . $mmArr[1]);
            }
        }
        else{
            throw new TemplateException(2, ': ' . $mmArr[1]);
        }
        return $result;
    }
    public static function substring($string, $len, $suffix = '...')
    {
        if(mb_strlen($string) > $len){
            $string = mb_substr($string, 0, $len) . $suffix;
        }
        return $string;
    }
}