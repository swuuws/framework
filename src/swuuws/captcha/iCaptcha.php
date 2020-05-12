<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws\captcha;

interface iCaptcha
{
    public static function show($number, $fontSize, $width, $height, $bgcolor, $noise, $noiseNumber, $interference);
}