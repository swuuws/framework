<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws;

use swuuws\exception\RouteException;

class Swuuws
{
    private static $param = [];
    private static $route = [];
    private static $url = null;
    public static function match($route, $anon = false)
    {
        self::$param = [];
        self::$route = [
            'route' => [],
            'param' => [],
            'delimiter' => '/',
            'connection' => 0,
        ];
        $route = str_replace(['\\', ' '], ['/', ''], $route);
        $url = self::parseURL();
        if($route == '/' && $url == ''){
            self::$route['route'][] = '/';
            if($anon == true){
                return false;
            }
            return true;
        }
        elseif($route != '/' && substr($route, 0, 1) == '/'){
            self::$route['route'][] = '/';
            $paramStr = trim($route, '/');
            $paramStr = str_replace('/,', ',', $paramStr);
            $paramStr = str_replace('/', ',', $paramStr);
            $paramArr = explode(',', trim($paramStr, ','));
            $delimiter = '/';
            if($paramArr[0] == '-' || $paramArr[0] == '_'){
                $delimiter = array_shift($paramArr);
                self::$route['delimiter'] = $delimiter;
            }
            self::$route['param'] = array_values($paramArr);
            if($anon == true){
                return false;
            }
            $minlen = 0;
            foreach($paramArr as $val){
                if(substr($val, -1) == '?'){
                    break;
                }
                else{
                    $minlen ++;
                }
            }
            $urlArr = explode($delimiter, $url);
            $urlArrLen = count($urlArr);
            if($urlArrLen >= $minlen && $urlArrLen <= count($paramArr)){
                foreach($paramArr as $key => $val){
                    $val = trim($val, '?');
                    if(isset($urlArr[$key])){
                        self::$param[$val] = $urlArr[$key];
                    }
                }
                return true;
            }
        }
        else{
            $routeArr = [];
            $paramArr = [];
            $delimiter = '/';
            $connection = false;
            $routes = explode('/', $route);
            $startParam = false;
            foreach($routes as $val){
                if(strpos($val, ',') !== false || $startParam){
                    $startParam = true;
                    $paramExp = explode(',', trim($val, ','));
                    if(in_array($paramExp[0], ['-', '_', '+-', '+_', '-+', '_+'])){
                        $delimStr = array_shift($paramExp);
                        if(strpos($delimStr, '-') !== false){
                            $delimiter = '-';
                        }
                        elseif(strpos($delimStr, '_') !== false){
                            $delimiter = '_';
                        }
                        if(strpos($delimStr, '+') !== false){
                            $connection = true;
                            self::$route['connection'] = 1;
                        }
                        self::$route['delimiter'] = $delimiter;
                    }
                    $paramArr = array_merge($paramArr, $paramExp);
                }
                else{
                    $routeArr[] = $val;
                    self::$route['route'][] = $val;
                }
            }
            self::$route['param'] = array_values($paramArr);
            if($anon == true){
                return false;
            }
            $urlArr = explode('/', $url);
            $tmpRoute = '';
            if($connection){
                $tmpRoute = array_pop($routeArr);
            }
            if(count($routeArr) > 0){
                foreach($routeArr as $val){
                    $tmp = strtolower(array_shift($urlArr));
                    if($tmp != strtolower($val)){
                        return false;
                    }
                }
            }
            $urlRemain = [];
            if($delimiter != '/'){
                foreach($urlArr as $val){
                    $tmpArr = explode($delimiter, $val);
                    $urlRemain = array_merge($urlRemain, $tmpArr);
                }
            }
            else{
                $urlRemain = array_merge($urlRemain, $urlArr);
            }
            if(!empty($tmpRoute)){
                if($delimiter == '_' && strpos($tmpRoute, '_') !== false){
                    $tmpArr = explode('_', $tmpRoute);
                    foreach($tmpArr as $val){
                        $tmp = strtolower(array_shift($urlRemain));
                        if(strtolower($val) != $tmp){
                            return false;
                        }
                    }
                }
                elseif($delimiter == '-' && strpos($tmpRoute, '-') !== false){
                    $tmpArr = explode('-', $tmpRoute);
                    foreach($tmpArr as $val){
                        $tmp = strtolower(array_shift($urlRemain));
                        if(strtolower($val) != $tmp){
                            return false;
                        }
                    }
                }
                else{
                    $tmp = strtolower(array_shift($urlRemain));
                    if(strtolower($tmpRoute) != $tmp){
                        return false;
                    }
                }
            }
            $minlen = 0;
            foreach($paramArr as $val){
                if(substr($val, -1) == '?'){
                    break;
                }
                else{
                    $minlen ++;
                }
            }
            $urlRemainLen = count($urlRemain);
            if($urlRemainLen >= $minlen && $urlRemainLen <= count($paramArr)){
                foreach($paramArr as $key => $val){
                    $val = trim($val, '?');
                    if(isset($urlRemain[$key])){
                        self::$param[$val] = $urlRemain[$key];
                    }
                }
                return true;
            }
        }
        return false;
    }
    public static function load($mm)
    {
        if(strpos($mm, '-') !== false){
            throw new RouteException(4);
        }
        $mm = str_replace('\\', '/', $mm);
        $mm = trim($mm, '/');
        if(empty($mm)){
            $mm = 'index/index';
        }
        $mmArr = explode('/', $mm);
        if(!isset($mmArr[1])){
            $mmArr[1] = 'index';
        }
        if(count($mmArr) != 2){
            throw new RouteException(2);
        }
        else{
            $_ENV['SWUUWS_VIEW'] = implode('/', $mmArr);
            Lang::handler($mmArr[0], $mmArr[1]);
            if(strpos($mmArr[0], '_') !== false){
                $mclass = self::transform($mmArr[0]);
            }
            else{
                $mclass = ucfirst($mmArr[0]);
            }
            if(strpos($mmArr[1], '_') !== false){
                $mmArr[1] = self::transform($mmArr[1], false);
            }
            $mm = $mmArr[0] . '\\' . $mclass;
            Load::load($mm, $mmArr[1], self::$param);
        }
    }
    public static function loadUrl()
    {
        $url = self::parseURL();
        $param = [];
        if(empty($url)){
            $module = 'index';
            $method = 'index';
        }
        elseif($url == 'swuuwscaptcha'){
            call_user_func('swuuws\\Captcha::captchaShow');
            exit();
        }
        else{
            $urlArr = explode('/', $url);
            if(count($urlArr) % 2 != 0){
                $module = 'index';
            }
            else{
                $module = array_shift($urlArr);
            }
            $method = array_shift($urlArr);
            while(count($urlArr) > 0){
                $key = array_shift($urlArr);
                $val = array_shift($urlArr);
                $param[$key] = $val;
            }
            $module = str_replace('-', '_', $module);
            $method = str_replace('-', '_', $method);
        }
        $_ENV['SWUUWS_VIEW'] = $module;
        if(strpos($module, '_') !== false){
            $mclass = self::transform($module);
        }
        else{
            $mclass = ucfirst($module);
        }
        $mm = $module . '\\' . $mclass;
        $_ENV['SWUUWS_VIEW'] .= '/' . $method;
        Lang::handler($module, $method);
        if(strpos($method, '_') !== false){
            $method = self::transform($method, false);
        }
        $isThrow = false;
        if(Env::get('APP_DEBUG') == true){
            $isThrow = true;
        }
        $result = Load::load($mm, $method, $param, $isThrow);
        if(!$result && !$isThrow){
            $_ENV['SWUUWS_VIEW'] = 'index/missed';
            $result = Load::load('index\Index', 'missed', $param, $isThrow);
        }
        return $result;
    }
    public static function getParam()
    {
        return self::$param;
    }
    public static function getRoute()
    {
        return self::$route;
    }
    private static function parseURL()
    {
        if(self::$url !== null){
            return self::$url;
        }
        $requesturi = self::mature(Request::uri());
        if(false !== $indexques = strpos($requesturi, '?')){
            $requesturi = substr($requesturi, 0, $indexques);
        }
        if(false !== $indexphp = strpos($requesturi, 'index.php')){
            $requesturi = substr($requesturi, $indexphp + 9);
        }
        else{
            $phpself = str_replace('\\', '/', Request::script());
            $indexphp = strpos($phpself, '.php');
            $phpself = substr($phpself, 0, $indexphp);
            $phpselfArr = explode('/', trim($phpself, '/'));
            array_pop($phpselfArr);
            if(count($phpselfArr) > 0){
                $phpself = '/' . implode('/', $phpselfArr) . '/';
            }
            else{
                $phpself = '/';
            }
            $phpselflen = strlen($phpself);
            if(substr($requesturi, 0, $phpselflen) == $phpself){
                $requesturi = substr($requesturi, $phpselflen);
            }
        }
        $suffix = Env::get('ADDRESS_SUFFIX');
        if(substr($suffix, 0, 1) !== '.'){
            $suffix = '.' . $suffix;
        }
        $suffixlen = strlen($suffix);
        if(substr($requesturi, - $suffixlen) == $suffix){
            $requesturi = substr($requesturi, 0, -$suffixlen);
        }
        self::$url = trim($requesturi, '/');
        return self::$url;
    }
    private static function mature($string)
    {
        return htmlspecialchars(stripslashes($string));
    }
    public static function transform($string, $first = true)
    {
        $mArr = explode('_', $string);
        $mArr = array_map(function($key, $val) use ($first){
            if(!$first && $key == 0){
                return $val;
            }
            else{
                return ucfirst($val);
            }
        }, array_keys($mArr), $mArr);
        return implode('', $mArr);
    }
    public static function capitalUnderline($string)
    {
        $name = lcfirst($string);
        $nameArr = preg_split('/(?=[A-Z])/', $name);
        $nameArr = array_map(function($val){
            return strtolower($val);
        }, $nameArr);
        $name = implode('_', $nameArr);
        return $name;
    }
}