<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws\captcha;

use swuuws\File;

class Separate implements iCaptcha
{
    private static $swuuws_img;
    private static $swuuws_interval = 3;
    private static $swuuws_font_color_same = true;
    private static $swuuws_bgcolor_random = false;
    public static function show($number, $fontSize, $width, $height, $bgcolor, $noise, $noiseNumber, $interference)
    {

        self::$swuuws_img = imagecreatetruecolor($width, $height);
        if(self::$swuuws_bgcolor_random){
            $bg_color = imagecolorallocate(self::$swuuws_img, mt_rand(200, 225), mt_rand(200, 225), mt_rand(200, 225));
        }
        else{
            $bg_color = imagecolorallocate(self::$swuuws_img, $bgcolor[0], $bgcolor[1], $bgcolor[2]);
        }
        imagefilledrectangle(self::$swuuws_img, 0, 0, $width, $height, $bg_color);
        $char = '2345678abcdefhijkmnpqrstuvwxyzABCDEFGHJKLMNPQRTUVWXY';
        $ttfPath = File::parentDirectory(__DIR__, 2) . DS . 'ttfs';
        $ttfArr = File::listFiles($ttfPath, 'ttf', true);
        $ttfc = count($ttfArr) - 1;
        $font = $ttfPath . DS . $ttfArr[mt_rand(0, $ttfc)];
        if(self::$swuuws_font_color_same){
            $text_color = imagecolorallocate(self::$swuuws_img, mt_rand(0, 150), mt_rand(0, 150), mt_rand(0, 150));
        }
        else{
            $text_color = null;
        }
        if($noise){
            for($i = 0; $i < $noiseNumber; $i ++){
                $noise_color = imagecolorallocate(self::$swuuws_img, mt_rand(100, 225), mt_rand(100, 225), mt_rand(100, 225));
                $angle = mt_rand(-60, 60);
                $proportion = mt_rand(20, 60) / 100;
                $size = round($fontSize * $proportion);
                $noise_left = mt_rand(0, $width);
                $noise_top = mt_rand(0, $height);
                imagettftext(self::$swuuws_img, $size, $angle, $noise_left, $noise_top, $noise_color, $font, $char[mt_rand(0, 51)]);
            }
        }
        if($interference){
            if(self::$swuuws_font_color_same){
                $curve_color = $text_color;
            }
            else{
                $curve_color = imagecolorallocate(self::$swuuws_img, mt_rand(0, 150), mt_rand(0, 150), mt_rand(0, 150));
            }
            $randx = mt_rand(0, $width);
            $tmprandy = round($height / 5);
            $randy = mt_rand(- $tmprandy, $tmprandy);
            $margin= round(mt_rand(0, $height / 2));
            $cycle = mt_rand($height / 2, $width * 2);
            $factor = round($width / 4);
            $halfWidth = round(($width / 2) + mt_rand(- $factor, $factor));
            $tmpy = 0;
            $extentMax = round($fontSize / 4);
            $extentMin = 1;
            $extent = ceil(mt_rand($extentMin, $extentMax) / 2);
            for($i = 0; $i < $halfWidth; $i ++){
                $y = $margin * sin(((2 * M_PI) / $cycle) * $i + $randx) + $randy + $height / 2;
                for($j = - $extent; $j < $extent; $j ++){
                    imagesetpixel(self::$swuuws_img, ($i + $j), ($y + $j), $curve_color);
                }
                if($i == $halfWidth -1){
                    $tmpy = $y;
                }
            }
            $randx = mt_rand(0, $width);
            $tmprandy = round($height / 5);
            $randy = mt_rand(- $tmprandy, $tmprandy);
            $margin= round(mt_rand(0, $height / 2));
            $cycle = mt_rand($height / 2, $width * 2);
            $extent = ceil(mt_rand($extentMin, $extentMax) / 2);
            $difference = null;
            for($i = $halfWidth; $i < $width; $i ++){
                $y = $margin * sin(((2 * M_PI) / $cycle) * $i + $randx) + $randy + $height / 2;
                if($difference === null){
                    $difference = $tmpy - $y;
                    $y = $tmpy;
                }
                else{
                    $y += $difference;
                }
                for($j = - $extent; $j < $extent; $j ++){
                    imagesetpixel(self::$swuuws_img, ($i + $j), ($y + $j), $curve_color);
                }
            }
        }
        $top = round(($height - $fontSize) / 2) + $fontSize;
        $step = $fontSize + self::$swuuws_interval;
        $left = round(($width - $step * $number) / 2);
        $string = '';
        for($i = 0; $i < $number; $i ++){
            if(!self::$swuuws_font_color_same){
                $text_color = imagecolorallocate(self::$swuuws_img, mt_rand(0, 150), mt_rand(0, 150), mt_rand(0, 150));
            }
            $angle = mt_rand(-60, 60);
            $font = $ttfPath . DS . $ttfArr[mt_rand(0, $ttfc)];
            $onechar = $char[mt_rand(0, 51)];
            imagettftext(self::$swuuws_img, $fontSize, $angle, ($left + $step * $i), $top, $text_color, $font, $onechar);
            $string .= $onechar;
        }
        return [self::$swuuws_img, $string];
    }
}