<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws;

class V
{
    public static function must()
    {
        return 'must';
    }
    public static function int()
    {
        return 'integer';
    }
    public static function positiveInt()
    {
        return 'positiveinteger';
    }
    public static function bool()
    {
        return 'boolean';
    }
    public static function zeroOrOne()
    {
        return 'zeroorone';
    }
    public static function float()
    {
        return 'float';
    }
    public static function url()
    {
        return 'url';
    }
    public static function email()
    {
        return 'email';
    }
    public static function ip()
    {
        return 'ip';
    }
    public static function number()
    {
        return 'number';
    }
    public static function date()
    {
        return 'date';
    }
    public static function regexp()
    {
        return 'regexp';
    }
    public static function letter()
    {
        return 'letter';
    }
    public static function letterUnder()
    {
        return 'letterUnder';
    }
    public static function letterHyphen()
    {
        return 'letterHyphen';
    }
    public static function letterHyphenUnder()
    {
        return 'letterHyphenUnder';
    }
    public static function captcha()
    {
        return 'captcha';
    }
    public static function maxlen()
    {
        return 'maxlen';
    }
    public static function minlen()
    {
        return 'minlen';
    }
    public static function equal()
    {
        return 'equal';
    }
    public static function getValue($name)
    {
        return Validate::outValue($name);
    }
    public static function letterNumber()
    {
        return 'letterNumber';
    }
    public static function startLetterNumber()
    {
        return 'startLetterNumber';
    }
    public static function startLetterUnder()
    {
        return 'startLetterUnder';
    }
    public static function letterNumberUnder()
    {
        return 'letterNumberUnder';
    }
    public static function startLetterNumberUnder()
    {
        return 'startLetterNumberUnder';
    }
    public static function startLetterHyphen()
    {
        return 'startLetterHyphen';
    }
    public static function letterNumberHyphen()
    {
        return 'letterNumberHyphen';
    }
    public static function startLetterNumberHyphen()
    {
        return 'startLetterNumberHyphen';
    }
    public static function startLetterHyphenUnder()
    {
        return 'startLetterHyphenUnder';
    }
    public static function letterNumberHyphenUnder()
    {
        return 'letterNumberHyphenUnder';
    }
    public static function startLetterNumberHyphenUnder()
    {
        return 'startLetterNumberHyphenUnder';
    }
    public static function notIn()
    {
        return 'notIn';
    }
    public static function mustIn()
    {
        return 'mustIn';
    }
}