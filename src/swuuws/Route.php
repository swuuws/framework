<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws;

use swuuws\exception\RouteException;
use swuuws\exception\UrlException;

class Route
{
    private static $instance;
    private static $func;
    private static $interrupt = false;
    private static $matched = false;
    private static $reverse = [];
    private static $missed = '';
    private static $param = [];
    /**
     * Handler.
     */
    public static function handler()
    {
        $routeType = strtolower(Env::get('ROUTE_TYPE'));
        if($routeType == 'auto'){
            Swuuws::loadUrl();
        }
        else{
            Load::loadSingleFile(APP . 'route' . DS . 'web.php');
            if(!self::$matched){
                if(!empty(self::$missed)){
                    Swuuws::load(self::$missed);
                }
                elseif(Swuuws::match('swuuwscaptcha')){
                    call_user_func('swuuws\\Captcha::captchaShow');
                    exit();
                }
                else{
                    throw new RouteException(3);
                }
            }
            else{
                if(is_string(self::$func)){
                    Swuuws::load(self::$func);
                }
                else{
                    call_user_func_array(self::$func, self::$param);
                }
            }
        }
    }
    /**
     * Get.
     *
     * @param  $route, $fun
     */
    public static function get($route, $fun)
    {
        return self::aisle(Request::isGet(), $route, $fun);
    }
    /**
     * Post.
     *
     * @param  $route, $fun
     */
    public static function post($route, $fun)
    {
        return self::aisle(Request::isPost(), $route, $fun);
    }
    /**
     * Get or Post.
     *
     * @param  $route, $fun
     */
    public static function getOrPost($route, $fun)
    {
        return self::aisle((Request::isGet() || Request::isPost()), $route, $fun);
    }
    /**
     * Put.
     *
     * @param  $route, $fun
     */
    public static function put($route, $fun)
    {
        return self::aisle(Request::isPut(), $route, $fun);
    }
    /**
     * Delete.
     *
     * @param  $route, $fun
     */
    public static function delete($route, $fun)
    {
        return self::aisle(Request::isDelete(), $route, $fun);
    }
    /**
     * Patch.
     *
     * @param  $route, $fun
     */
    public static function patch($route, $fun)
    {
        return self::aisle(Request::isPatch(), $route, $fun);
    }
    /**
     * Any.
     *
     * @param  $route, $fun
     */
    public static function any($route, $fun)
    {
        return self::aisle(true, $route, $fun);
    }
    /**
     * Match.
     *
     * @param  $array, $route, $fun
     */
    public static function match($array, $route, $fun)
    {
        $match = false;
        foreach($array as $val){
            $val = strtolower(trim($val));
            if($val == 'get' && Request::isGet()){
                $match = true;
                break;
            }
            if($val == 'post' && Request::isPost()){
                $match = true;
                break;
            }
            if($val == 'put' && Request::isPut()){
                $match = true;
                break;
            }
            if($val == 'delete' && Request::isDelete()){
                $match = true;
                break;
            }
            if($val == 'patch' && Request::isPatch()){
                $match = true;
                break;
            }
        }
        return self::aisle($match, $route, $fun);
    }
    /**
     * If not found.
     *
     * @param  $target, $type
     */
    public static function missed($target, $type = 'any')
    {
        if(strpos($type, 'any') !== false){
            self::$missed = $target;
        }
        else{
            $type = str_replace(' ', '', strtolower($type));
            $typeArr = explode(',', $type);
            foreach($typeArr as $val){
                if($val == 'get' && Request::isGet()){
                    self::$missed = $target;
                    break;
                }
                if($val == 'post' && Request::isPost()){
                    self::$missed = $target;
                    break;
                }
                if($val == 'put' && Request::isPut()){
                    self::$missed = $target;
                    break;
                }
                if($val == 'delete' && Request::isDelete()){
                    self::$missed = $target;
                    break;
                }
                if($val == 'patch' && Request::isPatch()){
                    self::$missed = $target;
                    break;
                }
            }
        }
    }
    private static function instance()
    {
        if(empty(self::$instance)){
            self::$instance = new Route();
        }
        return self::$instance;
    }
    private static function aisle($isRun, $route, $fun)
    {
        if(!self::$matched){
            self::$interrupt = false;
            if($isRun && Swuuws::match($route)){
                self::$param = Swuuws::getParam();
                self::$matched = true;
                self::$func = $fun;
            }
            else{
                self::$interrupt = true;
            }
        }
        else{
            Swuuws::match($route, true);
            self::$interrupt = true;
        }
        return self::instance();
    }
    /**
     * Regular match.
     *
     * @param  $param, $regular
     */
    public function where($param, $regular = '')
    {
        if(!self::$interrupt){
            $paramArr = self::$param;
            if(is_array($param)){
                foreach($param as $key => $val){
                    if(substr($val, 0, 1) != '/' && substr($val, -1) != '/'){
                        $val = '/^' . $val . '$/';
                    }
                    if(isset($paramArr[$key]) && !preg_match($val, $paramArr[$key])){
                        throw new RouteException();
                    }
                }
            }
            else{
                if(substr($regular, 0, 1) != '/' && substr($regular, -1) != '/'){
                    $regular = '/^' . $regular . '$/';
                }
                if(isset($paramArr[$param]) && !preg_match($regular, $paramArr[$param])){
                    throw new RouteException();
                }
            }
        }
        return $this;
    }
    /**
     * Alias.
     *
     * @param  $name
     */
    public function alias($name)
    {
        if(isset(self::$reverse[$name])){
            throw new RouteException(1);
        }
        else{
            self::$reverse[trim($name)] = Swuuws::getRoute();
        }
        return $this;
    }
    /**
     * Url.
     *
     * @param  $name, $array
     */
    public static function url($name, $array = [])
    {
        $routeType = strtolower(Env::get('ROUTE_TYPE'));
        if($routeType == 'auto'){
            $name = self::autoUrl($name, $array);
        }
        else{
            $name = trim($name);
            if(isset(self::$reverse[$name])){
                $urlArr = self::$reverse[$name];
                $route = implode('/', $urlArr['route']);
                if(count($urlArr['param']) > 0){
                    $param = '';
                    foreach($urlArr['param'] as $val){
                        $optional = false;
                        if(substr($val, -1) == '?'){
                            $optional = true;
                            $val = substr($val, 0, -1);
                        }
                        if(!$optional && !isset($array[$val])){
                            throw new UrlException(0, ': ' . $val);
                        }
                        if(isset($array[$val])){
                            if($param == ''){
                                $param = $array[$val];
                            }
                            else{
                                $param .= $urlArr['delimiter'] . $array[$val];
                            }
                        }
                    }
                    if($urlArr['connection'] == 1){
                        $name = $route . $urlArr['delimiter'] . $param;
                    }
                    else{
                        $name = $route . '/' . $param;
                    }
                }
                else{
                    $name = $route;
                }
            }
            else{
                $name = self::autoUrl($name, $array);
            }
        }
        if(!empty($name) && $name != '/'){
            $suffix = Env::get('ADDRESS_SUFFIX');
            if(!empty($suffix)){
                $name .= '.' . trim($suffix, '.');
            }
        }
        $root = Request::root();
        if(strpos(Request::uri(), 'index.php') !== false){
            $root .= 'index.php/';
        }
        return $root . ltrim($name, '/');
    }
    private static function autoUrl($name, $array = [])
    {
        $name = trim(str_replace(['\\', ' '], ['/', ''], strtolower($name)), '/');
        if(!empty($name)){
            if(strpos($name, '/') !== false){
                $nameArr = explode('/', $name);
                $nameArrLen = count($nameArr);
                if($nameArrLen > 2){
                    throw new UrlException(1, ': ' . $name);
                }
                else{
                    $tmp = array_shift($nameArr);
                    $name = ($tmp != 'index') ? $tmp : '';
                    if(empty($name)){
                        if($nameArr[0] != 'index'){
                            $name = $nameArr[0];
                        }
                    }
                    else{
                        $name .= '/' . $nameArr[0];
                    }
                }
            }
            else{
                if($name == 'index'){
                    $name = '';
                }
            }
        }
        if(count($array) > 0){
            foreach($array as $key => $val){
                $name .= '/' . $key . '/' . $val;
            }
        }
        return $name;
    }
}