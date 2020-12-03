<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws;

class Request
{
    private static $dataArr = null;
    private static $pretreatment = false;
    public static function isHttps()
    {
        if(isset($_SERVER['HTTPS']) && in_array(strtolower($_SERVER['HTTPS']), ['on', '1'])){
            return true;
        }
        elseif(isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == 'https'){
            return true;
        }
        elseif(isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443'){
            return true;
        }
        elseif(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'){
            return true;
        }
        elseif(isset($_SERVER['HTTP_FRONT_END_HTTPS']) && in_array(strtolower($_SERVER['HTTP_FRONT_END_HTTPS']), ['on', '1'])){
            return true;
        }
        return false;
    }
    public static function isRewrite()
    {
        if(function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules())){
            return true;
        }
        elseif(isset($_SERVER['IIS_UrlRewriteModule'])){
            return true;
        }
        else{
            return false;
        }
    }
    public static function script()
    {
        $script = empty($_SERVER['SCRIPT_NAME']) ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
        return $script;
    }
    public static function host()
    {
        $host = (self::isHttps() ? 'https://' : 'http://') . $_SERVER['SERVER_NAME'];
        $host .= ($_SERVER['SERVER_PORT'] == '80' ? '' : ':' . $_SERVER['SERVER_PORT']);
        return $host;
    }
    public static function root()
    {
        $script = self::script();
        if(substr($script, -4) == '.php'){
            $script = dirname($script);
            $script = str_replace('\\', '/', $script);
        }
        return self::host() . rtrim($script, '/') . '/';
    }
    public static function uri()
    {
        if(isset($_SERVER['HTTP_X_REWRITE_URL'])){
            $uri = $_SERVER['HTTP_X_REWRITE_URL'];
        }
        elseif(isset($_SERVER['REDIRECT_URL'])){
            $uri = $_SERVER['REDIRECT_URL'];
        }
        elseif(isset($_SERVER['REQUEST_URI'])){
            $uri = $_SERVER['REQUEST_URI'];
        }
        elseif(isset($_SERVER['ORIG_PATH_INFO'])){
            $uri = $_SERVER['ORIG_PATH_INFO'] . (empty($_SERVER['QUERY_STRING']) ? '' : '?' . $_SERVER['QUERY_STRING']);
        }
        else{
            $uri = '';
        }
        $host = self::host();
        $hostlen = strlen($host);
        if(substr($uri, 0, $hostlen) == $host){
            $uri = substr($uri, $hostlen);
        }
        return $uri;
    }
    public static function fullUrl()
    {
        $url = self::host() . self::uri();
        return $url;
    }
    public static function ip()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        return $ip;
    }
    public static function isPost()
    {
        if(strtolower($_SERVER['REQUEST_METHOD']) == 'post'){
            return true;
        }
        return false;
    }
    public static function hasPost($name)
    {
        if(isset($_POST[$name])){
            return true;
        }
        return false;
    }
    public static function getPost($name = null, $conversion = true)
    {
        self::pretreatment();
        if(empty($name)){
            if($conversion){
                foreach($_POST as $key => $val){
                    if(is_array($val)){
                        foreach($val as $skey => $sval){
                            $_POST[$key][$skey] = htmlspecialchars(urldecode($sval), ENT_QUOTES, 'UTF-8');
                        }
                    }
                    else{
                        $_POST[$key] = htmlspecialchars(urldecode($val), ENT_QUOTES, 'UTF-8');
                    }
                }
            }
            else{
                foreach($_POST as $key => $val){
                    if(is_array($val)){
                        foreach($val as $skey => $sval){
                            $_POST[$key][$skey] = urldecode($sval);
                        }
                    }
                    else{
                        $_POST[$key] = urldecode($val);
                    }
                }
            }
            return $_POST;
        }
        elseif(isset($_POST[$name])){
            if($conversion){
                if(is_array($_POST[$name])){
                    foreach($_POST[$name] as $skey => $sval){
                        $_POST[$name][$skey] = htmlspecialchars(urldecode($sval), ENT_QUOTES, 'UTF-8');
                    }
                    return $_POST[$name];
                }
                else{
                    return htmlspecialchars(urldecode($_POST[$name]), ENT_QUOTES, 'UTF-8');
                }
            }
            else{
                if(is_array($_POST[$name])){
                    foreach($_POST[$name] as $skey => $sval){
                        $_POST[$name][$skey] = urldecode($sval);
                    }
                    return $_POST[$name];
                }
                else{
                    return urldecode($_POST[$name]);
                }
            }
        }
        else{
            return false;
        }
    }
    public static function isGet()
    {
        if(strtolower($_SERVER['REQUEST_METHOD']) == 'get'){
            return true;
        }
        return false;
    }
    public static function hasGet($name)
    {
        if(isset($_GET[$name])){
            return true;
        }
        return false;
    }
    public static function getGet($name = null, $conversion = true)
    {
        if(empty($name)){
            if($conversion){
                foreach($_GET as $key => $val){
                    if(is_array($val)){
                        foreach($val as $skey => $sval){
                            $_GET[$key][$skey] = htmlspecialchars(urldecode($sval), ENT_QUOTES, 'UTF-8');
                        }
                    }
                    else{
                        $_GET[$key] = htmlspecialchars(urldecode($val), ENT_QUOTES, 'UTF-8');
                    }
                }
            }
            else{
                foreach($_GET as $key => $val){
                    if(is_array($val)){
                        foreach($val as $skey => $sval){
                            $_GET[$key][$skey] = urldecode($sval);
                        }
                    }
                    else{
                        $_GET[$key] = urldecode($val);
                    }
                }
            }
            return $_GET;
        }
        elseif(isset($_GET[$name])){
            if($conversion){
                if(is_array($_GET[$name])){
                    foreach($_GET[$name] as $skey => $sval){
                        $_GET[$name][$skey] = htmlspecialchars(urldecode($sval), ENT_QUOTES, 'UTF-8');
                    }
                    return $_GET[$name];
                }
                else{
                    return htmlspecialchars(urldecode($_GET[$name]), ENT_QUOTES, 'UTF-8');
                }
            }
            else{
                if(is_array($_GET[$name])){
                    foreach($_GET[$name] as $skey => $sval){
                        $_GET[$name][$skey] = urldecode($sval);
                    }
                    return $_GET[$name];
                }
                else{
                    return urldecode($_GET[$name]);
                }
            }
        }
        else{
            return false;
        }
    }
    public static function isPut()
    {
        return self::chkMethod('put');
    }
    public static function isDelete()
    {
        return self::chkMethod('delete');
    }
    public static function isPatch()
    {
        return self::chkMethod('patch');
    }
    private static function chkMethod($name)
    {
        if((isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']) && strtolower($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']) == $name) || strtolower($_SERVER['REQUEST_METHOD']) == $name){
            return true;
        }
        return false;
    }
    public static function isMobile()
    {
        if(isset($_SERVER['HTTP_VIA']) && stristr($_SERVER['HTTP_VIA'], "wap")){
            return true;
        }
        elseif(isset($_SERVER['HTTP_ACCEPT']) && strpos(strtoupper($_SERVER['HTTP_ACCEPT']), "VND.WAP.WML")){
            return true;
        }
        elseif(isset($_SERVER['HTTP_X_WAP_PROFILE']) || isset($_SERVER['HTTP_PROFILE'])){
            return true;
        }
        elseif(isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/(blackberry|configuration\/cldc|hp |hp-|htc |htc_|htc-|iemobile|kindle|midp|mmp|motorola|mobile|nokia|opera mini|opera |Googlebot-Mobile|YahooSeeker\/M1A1-R2D2|android|iphone|ipod|mobi|palm|palmos|pocket|portalmmm|ppc;|smartphone|sonyericsson|sqh|spv|symbian|treo|up.browser|up.link|vodafone|windows ce|xda |xda_)/i', $_SERVER['HTTP_USER_AGENT'])){
            return true;
        }
        return false;
    }
    public static function isAjax()
    {
        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
            return true;
        }
        return false;
    }
    public static function isPjax()
    {
        if(isset($_SERVER['HTTP_X_PJAX'])){
            return true;
        }
        return false;
    }
    private static function getData($name = null, $conversion = true)
    {
        self::preData();
        if(empty($name)){
            if(empty(self::$dataArr)){
                return [];
            }
            else{
                if($conversion){
                    foreach(self::$dataArr as $key => $val){
                        if(is_array($val)){
                            foreach($val as $skey => $sval){
                                self::$dataArr[$key][$skey] = htmlspecialchars(urldecode($sval), ENT_QUOTES, 'UTF-8');
                            }
                        }
                        else{
                            self::$dataArr[$key] = htmlspecialchars(urldecode($val), ENT_QUOTES, 'UTF-8');
                        }
                    }
                }
                else{
                    foreach(self::$dataArr as $key => $val){
                        if(is_array($val)){
                            foreach($val as $skey => $sval){
                                self::$dataArr[$key][$skey] = urldecode($sval);
                            }
                        }
                        else{
                            self::$dataArr[$key] = urldecode($val);
                        }
                    }
                }
                return self::$dataArr;
            }
        }
        elseif(isset(self::$dataArr[$name])){
            if($conversion){
                if(is_array(self::$dataArr[$name])){
                    foreach(self::$dataArr[$name] as $skey => $sval){
                        self::$dataArr[$name][$skey] = htmlspecialchars(urldecode($sval), ENT_QUOTES, 'UTF-8');
                    }
                    return self::$dataArr[$name];
                }
                else{
                    return htmlspecialchars(urldecode(self::$dataArr[$name]), ENT_QUOTES, 'UTF-8');
                }
            }
            else{
                if(is_array(self::$dataArr[$name])){
                    foreach(self::$dataArr[$name] as $skey => $sval){
                        self::$dataArr[$name][$skey] = urldecode($sval);
                    }
                    return self::$dataArr[$name];
                }
                else{
                    return urldecode(self::$dataArr[$name]);
                }
            }
        }
        else{
            return false;
        }
    }
    public static function getPut($name = null, $conversion = true)
    {
        return self::getData($name, $conversion);
    }
    public static function getDelete($name = null, $conversion = true)
    {
        return self::getData($name, $conversion);
    }
    public static function getPatch($name = null, $conversion = true)
    {
        return self::getData($name, $conversion);
    }
    public static function hasPut($name)
    {
        return self::hasData($name);
    }
    public static function hasDelete($name)
    {
        return self::hasData($name);
    }
    public static function hasPatch($name)
    {
        return self::hasData($name);
    }
    private static function hasData($name)
    {
        self::preData();
        if(isset(self::$dataArr[$name])){
            return true;
        }
        else{
            return false;
        }
    }
    private static function pretreatment()
    {
        if(!self::$pretreatment){
            $tmpArr = self::getinput();
            $_POST = array_merge($_POST, $tmpArr);
            self::$pretreatment = true;
        }
    }
    private static function getinput()
    {
        $result = [];
        if(isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false){
            $result = json_decode(file_get_contents('php://input'), true);
        }
        elseif(isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'text/xml') !== false){
            $object = simplexml_load_string(file_get_contents('php://input'));
            $result = json_decode(json_encode($object), true);
        }
        return $result;
    }
    private static function preData()
    {
        if(self::$dataArr == null){
            self::$dataArr = self::getinput();
            if(count(self::$dataArr) < 1){
                parse_str(file_get_contents('php://input'), self::$dataArr);
            }
        }
    }
}