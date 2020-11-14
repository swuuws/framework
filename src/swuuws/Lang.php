<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws;

class Lang
{
    private static $lang = [];
    private static $used = '';
    private static $langs = [];
    private static $isGet = false;
    private static $isCookie = false;
    private static $module;
    private static $method;
    private static $judgment = [];
    private static $auto = false;
    public static function load($file, $regulated = false, $similar = true)
    {
        $lang = Load::loadSingleFile($file, $regulated);
        if(is_array($lang)){
            self::$lang = array_merge(self::$lang, $lang);
        }
        elseif($similar){
            $base = basename($file);
            $dir = dirname($file);
            if(false !== $index = strpos($base, '-')){
                $base = substr($base, 0, $index + 1) . '*.php';
            }
            else{
                $dindex = strpos($base, '.');
                if($dindex === false){
                    $dindex = strlen($base);
                }
                $base = substr($base, 0, $dindex) . '-*.php';
            }
            $sfile = $dir . DS . $base;
            if(!$regulated){
                $sfile = str_replace(['/', '\\'], DS, $sfile);
                if(stripos($sfile, APP) === false){
                    $sfile = APP . trim($sfile, DS);
                }
            }
            $sarr = glob($sfile);
            if(is_array($sarr) && count($sarr) > 0){
                if(is_file($sarr[0])){
                    $lang = Load::loadSingleFile($sarr[0], $regulated);
                    if(is_array($lang)){
                        self::$lang = array_merge(self::$lang, $lang);
                    }
                }
            }
        }
    }
    public static function loadPack($path, $regulated = false)
    {
        $find = false;
        $path = rtrim(str_replace(['/', '\\'], DS, $path), DS);
        if(!$regulated){
            if(stripos($path, ROOT) === false){
                $path = ROOT . DS . ltrim($path, DS);
            }
        }
        $path .= DS;
        foreach(self::$langs as $val){
            if(!self::$isGet && !self::$isCookie && self::$auto && !in_array($val, self::$judgment)){
                continue;
            }
            $file = $path . $val . '.php';
            if(is_file($file)){
                self::load($file, true, false);
                $find = true;
                break;
            }
            else{
                if(false !== $index = strpos($val, '-')){
                    $sval = substr($val, 0, $index + 1) . '*.php';
                }
                else{
                    $dindex = strpos($val, '.');
                    if($dindex === false){
                        $dindex = strlen($val);
                    }
                    $sval = substr($val, 0, $dindex) . '-*.php';
                }
                $sfile = $path . $sval;
                $sarr = glob($sfile);
                if(is_array($sarr) && count($sarr) > 0){
                    if(is_file($sarr[0])){
                        self::load($sarr[0], true, false);
                        $find = true;
                        break;
                    }
                }
            }
        }
        if(!$find){
            $applang = trim(Env::get('DEFAULT_LANG'));
            if(!empty($applang)){
                $file = $path . $applang . '.php';
                self::load($file, true, false);
            }
        }
    }
    public static function lang($lang)
    {
        if(isset(self::$lang[$lang])){
            return self::$lang[$lang];
        }
        else{
            return $lang;
        }
    }
    public static function set($lang)
    {
        self::$used = trim($lang);
        self::loadUsed();
    }
    public static function auto()
    {
        if(!self::$isGet && !self::$isCookie){
            self::$langs = self::judgment();
            self::autoPick();
        }
    }
    public static function handler($module = 'index', $method = 'index')
    {
        self::$module = $module;
        self::$method = $method;
        $lang = trim(Env::get('LANG_REQUEST'));
        if(Request::hasGet($lang)){
            $la = Request::getGet($lang);
            self::$langs = self::analysis($la);
            Cookie::set('swuuws_lang', $la);
            self::$isGet = true;
        }
        elseif(Cookie::has('swuuws_lang')){
            $la = Cookie::get('swuuws_lang');
            self::$langs = self::analysis($la);
            Cookie::set('swuuws_lang', $la);
            self::$isCookie = true;
        }
        else{
            $applang = strtolower(trim(Env::get('APP_LANG')));
            if($applang == 'auto'){
                self::$langs = self::judgment();
            }
            else{
                if(strpos($applang, 'auto') !== false){
                    self::$auto = true;
                    $applang = str_replace(' ', '', $applang);
                    $applang = str_replace(',auto,', ',', $applang);
                    $applang = str_replace(['auto,', ',auto'], '', $applang);
                }
                self::$langs = self::analysis($applang);
            }
        }
        self::pick();
    }
    public static function getAuto()
    {
        self::judgment();
        if(isset(self::$judgment[0])){
            $result = self::$judgment[0];
        }
        else{
            $result = trim(Env::get('DEFAULT_LANG'));
        }
        return $result;
    }
    private static function judgment()
    {
        if(is_array(self::$judgment) && count(self::$judgment) > 0){
            return self::$judgment;
        }
        else{
            self::$judgment = [];
            $la = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
            $larr = explode(';', $la);
            $la = strtolower($larr[0]);
            $larr = explode(',', $la);
            $la = trim($larr[0]);
            $larr[1] = trim($larr[1]);
            if(strlen($larr[1]) >= strlen($la)){
                self::$judgment[] = $larr[1];
                self::$judgment[] = $la;
            }
            else{
                self::$judgment[] = $la;
                self::$judgment[] = $larr[1];
            }
            return self::$judgment;
        }
    }
    private static function analysis($lang)
    {
        return array_map(function($item){
            return strtolower(trim($item));
        }, explode(',', $lang));
    }
    private static function autoPick()
    {
        $module = APP . 'lang' . DS . self::$module . DS;
        foreach(self::$langs as $val){
            if(self::loadInArr([$module . $val, $module . DS . self::$method . DS . $val])){
                break;
            }
        }
    }
    private static function pick()
    {
        $find = false;
        $module = APP . 'lang' . DS . self::$module . DS;
        foreach(self::$langs as $val){
            if(!self::$isGet && !self::$isCookie && self::$auto && !in_array($val, self::$judgment)){
                continue;
            }
            $find = self::loadInArr([$module . $val, $module . DS . self::$method . DS . $val]);
            if($find){
                break;
            }
            elseif(false !== $index = strpos($val, '-')){
                $sval = substr($val, 0, $index + 1) . '*';
                $find = self::similar([$module . $sval, $module . DS . self::$method . DS . $sval]);
                if($find){
                    break;
                }
            }
            else{
                $dindex = strpos($val, '.');
                if($dindex === false){
                    $dindex = strlen($val);
                }
                $sval = substr($val, 0, $dindex) . '-*';
                $find = self::similar([$module . $sval, $module . DS . self::$method . DS . $sval]);
                if($find){
                    break;
                }
            }
        }
        if(!$find){
            $applang = trim(Env::get('DEFAULT_LANG'));
            if(!empty($applang)){
                self::loadInArr([$module . $applang, $module . DS . self::$method . DS . $applang]);
            }
        }
    }
    private static function loadUsed()
    {
        $result = false;
        if(!empty(self::$used)){
            $module = APP . 'lang' . DS . self::$module . DS;
            $result = self::loadInArr([$module . self::$used, $module . DS . self::$method . DS . self::$used]);
        }
        return $result;
    }
    private static function amend($string)
    {
        if(substr($string, -4) != '.php'){
            $string .= '.php';
        }
        return $string;
    }
    private static function loadInArr($arr)
    {
        $result = false;
        if(is_array($arr)){
            foreach($arr as $val){
                $lang = self::amend($val);
                if(is_file($lang)){
                    self::load($lang, true, false);
                    $result = true;
                }
            }
        }
        elseif(is_string($arr)){
            $lang = self::amend($arr);
            if(is_file($lang)){
                self::load($lang, true, false);
                $result = true;
            }
        }
        return $result;
    }
    private static function similar($arr)
    {
        $result = false;
        if(is_array($arr)){
            foreach($arr as $val){
                $lang = self::amend($val);
                $sarr = glob($lang);
                if(is_array($sarr) && count($sarr) > 0){
                    if(is_file($sarr[0])){
                        self::load($sarr[0], true, false);
                        $result = true;
                    }
                }
            }
        }
        elseif(is_string($arr)){
            $lang = self::amend($arr);
            $sarr = glob($lang);
            if(is_array($sarr) && count($sarr) > 0){
                if(is_file($sarr[0])){
                    self::load($sarr[0], true, false);
                    $result = true;
                }
            }
        }
        return $result;
    }
}