<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws;

class Captcha
{
    private static $swuuws_type = '';
    private static $swuuws_width = 200;
    private static $swuuws_high = 80;
    private static $swuuws_font_size = 30;
    private static $swuuws_number = 5;
    private static $swuuws_noise_number = 30;
    private static $swuuws_isNoise = true;
    private static $swuuws_isLine = true;
    private static $swuuws_bgcolor = [218, 232, 237];
    private static $swuuws_session = 'swuuws.com';
    private static $swuuws_session_key = '';
    private static $swuuws_expired = 300;
    private static $instance;
    private static function instance()
    {
        if(empty(self::$instance)){
            self::$instance = new Captcha();
        }
        return self::$instance;
    }
    /**
     * Show captcha.
     *
     * @param  $number, $fontSize, $width, $height, $bgcolor, $noise, $noiseNumber, $interference
     */
    public static function show($number = null, $fontSize = null, $width = null, $height = null, $bgcolor = null, $noise = true, $noiseNumber = null, $interference = true)
    {
        if(!empty($number) && is_numeric($number)){
            self::$swuuws_number = $number;
        }
        elseif(Env::has('CAPTCHA_NUMBER')){
            self::$swuuws_number = intval(Env::get('CAPTCHA_NUMBER'));
        }
        if(!empty($fontSize) && is_numeric($fontSize)){
            self::$swuuws_font_size = $fontSize;
        }
        elseif(Env::has('CAPTCHA_FONTSIZE')){
            self::$swuuws_font_size = intval(Env::get('CAPTCHA_FONTSIZE'));
        }
        if(!empty($width) && is_numeric($width)){
            self::$swuuws_width = $width;
        }
        elseif(Env::has('CAPTCHA_WIDTH')){
            self::$swuuws_width = intval(Env::get('CAPTCHA_WIDTH'));
        }
        if(!empty($height) && is_numeric($height)){
            self::$swuuws_high = $height;
        }
        elseif(Env::has('CAPTCHA_HEIGHT')){
            self::$swuuws_high = intval(Env::get('CAPTCHA_HEIGHT'));
        }
        if(!empty($bgcolor)){
            if(!is_array($bgcolor)){
                $bgcolor = str_replace(' ', '', $bgcolor);
                $bgcolor = explode(',', $bgcolor);
            }
            self::$swuuws_bgcolor = $bgcolor;
        }
        elseif(Env::has('CAPTCHA_BGCOLOR')){
            $color = Env::get('CAPTCHA_BGCOLOR');
            if(!is_array($color)){
                $color = str_replace(' ', '', $color);
                $color = explode(',', $color);
            }
            self::$swuuws_bgcolor = $color;
        }
        if(is_bool($noise)){
            self::$swuuws_isNoise = $noise;
        }
        elseif(Env::has('CAPTCHA_HASNOISE')){
            self::$swuuws_isNoise = boolval(Env::get('CAPTCHA_HASNOISE'));
        }
        if(!empty($noiseNumber) && is_numeric($noiseNumber)){
            self::$swuuws_noise_number = $noiseNumber;
        }
        elseif(Env::has('CAPTCHA_NOISENUMBER')){
            self::$swuuws_noise_number = intval(Env::get('CAPTCHA_NOISENUMBER'));
        }
        if(is_bool($interference)){
            self::$swuuws_isLine = $interference;
        }
        elseif(Env::has('CAPTCHA_HASINTERFERENCE')){
            self::$swuuws_isLine = boolval(Env::get('CAPTCHA_HASINTERFERENCE'));
        }
        if(Env::has('CAPTCHA_TYPE')){
            self::$swuuws_type = ucfirst(strtolower(Env::get('CAPTCHA_TYPE')));
        }
        else{
            self::$swuuws_type = 'Separate';
        }
        if(Env::has('CAPTCHA_EXPIRED')){
            self::$swuuws_expired = Env::get('CAPTCHA_EXPIRED');
        }
        self::initSession();
        $canvas = call_user_func('swuuws\\captcha\\' . self::$swuuws_type . '::show', self::$swuuws_number, self::$swuuws_font_size, self::$swuuws_width, self::$swuuws_high, self::$swuuws_bgcolor, self::$swuuws_isNoise, self::$swuuws_noise_number, self::$swuuws_isLine);
        Session::set(self::sessionName(), self::sessionValue(strtolower($canvas[1])));
        Response::cleanBuffer()->type(T::png());
        imagepng($canvas[0]);
        imagedestroy($canvas[0]);
    }
    /**
     * Set key.
     *
     * @param  $key
     */
    public static function setKey($key)
    {
        self::$swuuws_session_key = $key;
        return self::instance();
    }
    /**
     * Test and verify.
     *
     * @param  $captcha, $key
     */
    public static function verify($captcha, $key = null)
    {
        self::initSession();
        $name = self::sessionName($key);
        if(Session::has($name)){
            $result = false;
            $session = Session::get($name);
            if($session['time'] > time() && $session['value'] == md5(strtolower($captcha))){
                $result = true;
            }
            Session::delete($name);
            return $result;
        }
        else{
            return false;
        }
    }
    private static function initSession()
    {
        if(Env::has('CAPTCHA_SESSION')){
            self::$swuuws_session = Env::get('CAPTCHA_SESSION');
        }
    }
    private static function sessionValue($value)
    {
        $result = [
            'value' => md5($value),
            'time' => time() + self::$swuuws_expired
        ];
        return $result;
    }
    private static function sessionName($key = null)
    {
        if(empty($key)){
            return md5(self::$swuuws_session . self::$swuuws_session_key);
        }
        else{
            return md5(self::$swuuws_session . $key);
        }
    }
    public static function captcha()
    {
        return '<img style="cursor: pointer" src="' . Url::url('swuuwscaptcha') . '" onclick="this.src=\'' . Url::url('swuuwscaptcha') . '?\' + Math.random()">';
    }
    public static function captchaUrl()
    {
        return Url::url('swuuwscaptcha');
    }
    public static function captchaShow()
    {
        if(Env::has('CAPTCHA_MARK')){
            self::setKey(Env::get('CAPTCHA_MARK'))->show();
        }
        else{
            self::show();
        }
    }
}