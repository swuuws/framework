<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws;

class Response
{
    private static $out = '';
    private static $needOut = true;
    private static $inlay = '';
    private static $adorn = '';
    private static $attribute = '';
    private static $instance;
    private static function instance()
    {
        if(empty(self::$instance)){
            self::$instance = new Response();
        }
        return self::$instance;
    }
    public static function inlay($string)
    {
        self::$inlay = $string;
        return self::instance();
    }
    public static function clearInlay()
    {
        self::$inlay = '';
        return self::instance();
    }
    public static function adorn($name, $attribute = '')
    {
        self::$adorn = $name;
        self::$attribute = $attribute;
        return self::instance();
    }
    public static function clearAdorn()
    {
        self::$adorn = '';
        self::$attribute = '';
        return self::instance();
    }
    public static function write()
    {
        $args = func_get_args();
        self::$out .= self::assembly($args);
        self::$needOut = true;
        return self::instance();
    }
    public static function dump()
    {
        $args = func_get_args();
        echo self::$out . self::assembly($args);
        self::clear();
    }
    public static function clear()
    {
        self::$out = '';
        self::$needOut = false;
    }
    public static function clearDump()
    {
        $args = func_get_args();
        echo self::assembly($args);
        self::clear();
    }
    public static function needOutput()
    {
        return self::$needOut;
    }
    private static function assembly($args)
    {
        $string = '';
        $left_adorn = '';
        $right_adorn = '';
        if(!empty(self::$adorn)){
            $attribute = empty(self::$attribute) ? '' : ' ' . self::$attribute;
            $left_adorn = '<' . self::$adorn . $attribute . '>';
            $right_adorn = '</' . self::$adorn . '>';
        }
        foreach($args as $val){
            if(empty($string)){
                $string .= $left_adorn . $val . $right_adorn;
            }
            else{
                $string .= self::$inlay . $left_adorn . $val . $right_adorn;
            }
        }
        return $string;
    }
    public static function type($type)
    {
        header('Content-type: ' . $type);
        return self::instance();
    }
    public static function cleanBuffer()
    {
        ob_clean();
        return self::instance();
    }
    public static function writeJson($array, $unicode = false)
    {
        header('Content-Type:application/json; charset=utf-8');
        if(is_array($array)){
            if($unicode){
                $array = json_encode($array);
            }
            else{
                $array = json_encode($array, JSON_UNESCAPED_UNICODE);
            }
        }
        self::$out = $array;
        exit();
    }
    public static function writeXml($xml)
    {
        header('Content-Type:text/xml; charset=utf-8');
        self::$out = $xml;
        exit();
    }
}