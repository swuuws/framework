<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws;

use swuuws\exception\ValidateException;

class Validate
{
    private static $verification_goal = '';
    private static $verification_rule = '';
    private static $verification_rule_attach = '';
    private static $verification_error = '';
    private static $verification_goal_mark = false;
    private static $verification_result = true;
    private static $verification_message = [];
    private static $verification_continue = true;
    private static $verification_parameter = [];
    private static $verification_name = '';
    private static $verification_lang = false;
    private static $verification_isrule = false;
    private static $verification_prevrule = [];
    private static $verification_shortfailure = false;
    private static $instance;
    private static function instance()
    {
        if(empty(self::$instance)){
            self::$instance = new Validate();
        }
        return self::$instance;
    }
    public static function postValue($name)
    {
        if(self::$verification_continue){
            self::process();
            $name = trim($name);
            if(Request::hasPost($name)){
                self::$verification_name = $name;
                $tmpval = Request::getPost($name);
                self::$verification_parameter[$name] = $tmpval;
                return self::goalValue($tmpval);
            }
            else{
                throw new ValidateException(0, ': ' . $name);
            }
        }
        return self::instance();
    }
    public static function hasPostValue($name)
    {
        if(self::$verification_continue){
            self::process();
            $name = trim($name);
            if(Request::hasPost($name)){
                self::$verification_name = $name;
                $tmpval = Request::getPost($name);
                self::$verification_parameter[$name] = $tmpval;
                return self::goalValue($tmpval);
            }
            else{
                self::$verification_shortfailure = true;
            }
        }
        return self::instance();
    }
    public static function getValue($name)
    {
        if(self::$verification_continue){
            self::process();
            $name = trim($name);
            if(Request::hasGet($name)){
                self::$verification_name = $name;
                $tmpval = Request::getGet($name);
                self::$verification_parameter[$name] = $tmpval;
                return self::goalValue($tmpval);
            }
            else{
                throw new ValidateException(1, ': ' . $name);
            }
        }
        return self::instance();
    }
    public static function hasGetValue($name)
    {
        if(self::$verification_continue){
            self::process();
            $name = trim($name);
            if(Request::hasGet($name)){
                self::$verification_name = $name;
                $tmpval = Request::getGet($name);
                self::$verification_parameter[$name] = $tmpval;
                return self::goalValue($tmpval);
            }
            else{
                self::$verification_shortfailure = true;
            }
        }
        return self::instance();
    }
    public static function putValue($name)
    {
        if(self::$verification_continue){
            self::process();
            $name = trim($name);
            if(Request::hasPut($name)){
                self::$verification_name = $name;
                $tmpval = Request::getPut($name);
                self::$verification_parameter[$name] = $tmpval;
                return self::goalValue($tmpval);
            }
            else{
                throw new ValidateException(2, ': ' . $name);
            }
        }
        return self::instance();
    }
    public static function hasPutValue($name)
    {
        if(self::$verification_continue){
            self::process();
            $name = trim($name);
            if(Request::hasPut($name)){
                self::$verification_name = $name;
                $tmpval = Request::getPut($name);
                self::$verification_parameter[$name] = $tmpval;
                return self::goalValue($tmpval);
            }
            else{
                self::$verification_shortfailure = true;
            }
        }
        return self::instance();
    }
    public static function deleteValue($name)
    {
        if(self::$verification_continue){
            self::process();
            $name = trim($name);
            if(Request::hasDelete($name)){
                self::$verification_name = $name;
                $tmpval = Request::getDelete($name);
                self::$verification_parameter[$name] = $tmpval;
                return self::goalValue($tmpval);
            }
            else{
                throw new ValidateException(3, ': ' . $name);
            }
        }
        return self::instance();
    }
    public static function hasDeleteValue($name)
    {
        if(self::$verification_continue){
            self::process();
            $name = trim($name);
            if(Request::hasDelete($name)){
                self::$verification_name = $name;
                $tmpval = Request::getDelete($name);
                self::$verification_parameter[$name] = $tmpval;
                return self::goalValue($tmpval);
            }
            else{
                self::$verification_shortfailure = true;
            }
        }
        return self::instance();
    }
    public static function patchValue($name)
    {
        if(self::$verification_continue){
            self::process();
            $name = trim($name);
            if(Request::hasPatch($name)){
                self::$verification_name = $name;
                $tmpval = Request::getPatch($name);
                self::$verification_parameter[$name] = $tmpval;
                return self::goalValue($tmpval);
            }
            else{
                throw new ValidateException(4, ': ' . $name);
            }
        }
        return self::instance();
    }
    public static function hasPatchValue($name)
    {
        if(self::$verification_continue){
            self::process();
            $name = trim($name);
            if(Request::hasPatch($name)){
                self::$verification_name = $name;
                $tmpval = Request::getPatch($name);
                self::$verification_parameter[$name] = $tmpval;
                return self::goalValue($tmpval);
            }
            else{
                self::$verification_shortfailure = true;
            }
        }
        return self::instance();
    }
    private static function goalValue($goal)
    {
        if(self::$verification_continue){
            self::$verification_goal = $goal;
            self::$verification_goal_mark = true;
        }
        return self::instance();
    }
    public static function goal($goal)
    {
        if(self::$verification_continue){
            self::process();
            self::$verification_name = '';
            self::$verification_goal = $goal;
            self::$verification_goal_mark = true;
        }
        return self::instance();
    }
    public static function rule($rule, $attach = '')
    {
        if(self::$verification_continue && !self::$verification_shortfailure){
            if(self::$verification_goal_mark == true && self::$verification_isrule){
                self::$verification_rule = self::$verification_prevrule[0];
                self::$verification_rule_attach = self::$verification_prevrule[1];
                self::verify();
            }
            self::$verification_rule = $rule;
            self::$verification_rule_attach = $attach;
            if(self::$verification_goal_mark == true && self::$verification_error !== ''){
                self::verify();
            }
            else{
                self::$verification_isrule = true;
                self::$verification_prevrule = [$rule, $attach];
            }
        }
        return self::instance();
    }
    public static function ifError($error)
    {
        if(self::$verification_continue && !self::$verification_shortfailure){
            self::$verification_error = $error;
            if(self::$verification_goal_mark == true && self::$verification_rule !== ''){
                self::verify();
            }
            self::$verification_isrule = false;
            self::$verification_prevrule = [];
        }
        return self::instance();
    }
    public static function isPass()
    {
        return self::$verification_result;
    }
    public static function message()
    {
        return self::$verification_message;
    }
    public static function firstMessage()
    {
        if(isset(self::$verification_message[0])){
            return self::$verification_message[0];
        }
        else{
            return '';
        }
    }
    private static function verify()
    {
        $rule = strtolower(trim(self::$verification_rule));
        if(!self::$verification_lang){
            Lang::load(File::parentDirectory(__DIR__, 1) . DS . 'lang' . DS . Lang::getAuto() . '.php', true);
            self::$verification_lang = true;
        }
        switch($rule){
            case 'must':
                if(empty(self::$verification_goal)){
                    self::handlingError(Lang::lang('Can not be empty'));
                }
                break;
            case 'int':
            case 'integer':
                if(trim(self::$verification_goal) != '' && filter_var(self::$verification_goal, FILTER_VALIDATE_INT) === false){
                    self::handlingError(Lang::lang('Must be an integer'));
                }
                break;
            case 'positiveint':
            case 'positiveinteger':
                if(trim(self::$verification_goal) != '' && filter_var(self::$verification_goal, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]) === false){
                    self::handlingError(Lang::lang('Must be an positive integer'));
                }
                break;
            case 'bool':
            case 'boolean':
                if(trim(self::$verification_goal) != '' && !filter_var(self::$verification_goal, FILTER_VALIDATE_BOOLEAN)){
                    self::handlingError(Lang::lang('Must be Boolean'));
                }
                break;
            case 'oneorzero':
            case 'zeroorone':
                if(trim(self::$verification_goal) != '' && self::$verification_goal != 0 && self::$verification_goal != 1){
                    self::handlingError(Lang::lang('Must be zero or one'));
                }
                break;
            case 'float':
                if(trim(self::$verification_goal) != '' && !filter_var(self::$verification_goal, FILTER_VALIDATE_FLOAT)){
                    self::handlingError(Lang::lang('Must be a floating point number'));
                }
                break;
            case 'url':
                if(trim(self::$verification_goal) != '' && !filter_var(self::$verification_goal, FILTER_VALIDATE_URL)){
                    self::handlingError(Lang::lang('Must be url'));
                }
                break;
            case 'email':
                if(trim(self::$verification_goal) != '' && !filter_var(self::$verification_goal, FILTER_VALIDATE_EMAIL)){
                    self::handlingError(Lang::lang('Must be email'));
                }
                break;
            case 'ip':
                if(trim(self::$verification_goal) != '' && !filter_var(self::$verification_goal, FILTER_VALIDATE_IP)){
                    self::handlingError(Lang::lang('Must be ip'));
                }
                break;
            case 'number':
                if(trim(self::$verification_goal) != '' && !is_numeric(self::$verification_goal)){
                    self::handlingError(Lang::lang('Must be a number'));
                }
                break;
            case 'date':
                if(trim(self::$verification_goal) != '' && strtotime(self::$verification_goal) === false){
                    self::handlingError(Lang::lang('Must be a date'));
                }
                break;
            case 'regexp':
            case 'reg':
                if(trim(self::$verification_goal) != '' && !filter_var(self::$verification_goal, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => self::$verification_rule_attach]])){
                    self::handlingError(Lang::lang('Must match regular expression'));
                }
                break;
            case 'letter':
                if(trim(self::$verification_goal) != '' && !filter_var(self::$verification_goal, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => '/^[A-Za-z]+$/']])){
                    self::handlingError(Lang::lang('Must be letters'));
                }
                break;
            case 'letternumber':
                if(trim(self::$verification_goal) != '' && !filter_var(self::$verification_goal, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => '/^[A-Za-z0-9]+$/']])){
                    self::handlingError(Lang::lang('Can only be letters and numbers'));
                }
                break;
            case 'startletternumber':
                if(trim(self::$verification_goal) != '' && !filter_var(self::$verification_goal, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => '/^[A-Za-z][A-Za-z0-9]*$/']])){
                    self::handlingError(Lang::lang('Can only be letters and numbers, and start with a letter'));
                }
                break;
            case 'letterunder':
                if(trim(self::$verification_goal) != '' && !filter_var(self::$verification_goal, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => '/^[A-Za-z\_]+$/']])){
                    self::handlingError(Lang::lang('Only letters and underscores'));
                }
                break;
            case 'startletterunder':
                if(trim(self::$verification_goal) != '' && !filter_var(self::$verification_goal, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => '/^[A-Za-z][A-Za-z\_]*$/']])){
                    self::handlingError(Lang::lang('Can only be letters and underscores, and start with a letter'));
                }
                break;
            case 'letternumberunder':
                if(trim(self::$verification_goal) != '' && !filter_var(self::$verification_goal, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => '/^[A-Za-z0-9\_]+$/']])){
                    self::handlingError(Lang::lang('Only letters, numbers and underscores'));
                }
                break;
            case 'startletternumberunder':
                if(trim(self::$verification_goal) != '' && !filter_var(self::$verification_goal, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => '/^[A-Za-z][A-Za-z0-9\_]*$/']])){
                    self::handlingError(Lang::lang('Can only be letters, numbers and underscores, and start with a letter'));
                }
                break;
            case 'letterhyphen':
                if(trim(self::$verification_goal) != '' && !filter_var(self::$verification_goal, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => '/^[A-Za-z\-]+$/']])){
                    self::handlingError(Lang::lang('Only letters and connecting lines'));
                }
                break;
            case 'startletterhyphen':
                if(trim(self::$verification_goal) != '' && !filter_var(self::$verification_goal, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => '/^[A-Za-z][A-Za-z\-]*$/']])){
                    self::handlingError(Lang::lang('Can only be letters and connecting lines, and start with a letter'));
                }
                break;
            case 'letternumberhyphen':
                if(trim(self::$verification_goal) != '' && !filter_var(self::$verification_goal, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => '/^[A-Za-z0-9\-]+$/']])){
                    self::handlingError(Lang::lang('Only letters, numbers and connecting lines'));
                }
                break;
            case 'startletternumberhyphen':
                if(trim(self::$verification_goal) != '' && !filter_var(self::$verification_goal, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => '/^[A-Za-z][A-Za-z0-9\-]*$/']])){
                    self::handlingError(Lang::lang('Can only be letters, numbers and connecting lines, and start with a letter'));
                }
                break;
            case 'letterhyphenunder':
            case 'letterunderhyphen':
                if(trim(self::$verification_goal) != '' && !filter_var(self::$verification_goal, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => '/^[A-Za-z\-\_]+$/']])){
                    self::handlingError(Lang::lang('Only letters, underscores and connecting lines'));
                }
                break;
            case 'startletterhyphenunder':
            case 'startletterunderhyphen':
                if(trim(self::$verification_goal) != '' && !filter_var(self::$verification_goal, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => '/^[A-Za-z][A-Za-z\-\_]*$/']])){
                    self::handlingError(Lang::lang('Can only be letters, underscores and connecting lines, and start with a letter'));
                }
                break;
            case 'letternumberhyphenunder':
            case 'letternumberunderhyphen':
                if(trim(self::$verification_goal) != '' && !filter_var(self::$verification_goal, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => '/^[A-Za-z0-9\-\_]+$/']])){
                    self::handlingError(Lang::lang('Can only be letters, numbers, underscores, connecting lines'));
                }
                break;
            case 'startletternumberhyphenunder':
            case 'startletternumberunderhyphen':
                if(trim(self::$verification_goal) != '' && !filter_var(self::$verification_goal, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => '/^[A-Za-z][A-Za-z0-9\-\_]*$/']])){
                    self::handlingError(Lang::lang('Can only be letters, numbers, underscores and connecting lines, and start with a letter'));
                }
                break;
            case 'maxlen':
                if(trim(self::$verification_goal) != '' && (mb_strlen(self::$verification_goal) > intval(trim(self::$verification_rule_attach)))){
                    self::handlingError(Lang::lang('The length exceeds the allowable range'));
                }
                break;
            case 'minlen':
                if(trim(self::$verification_goal) != '' && (mb_strlen(self::$verification_goal) < intval(trim(self::$verification_rule_attach)))){
                    self::handlingError(Lang::lang('Length is less than allowable range'));
                }
                break;
            case 'equal':
                if(trim(self::$verification_goal) != '' && (self::$verification_goal != self::$verification_rule_attach)){
                    self::handlingError(Lang::lang('Values must be equal'));
                }
                break;
            case 'notin':
                if(!is_array(self::$verification_rule_attach)){
                    self::$verification_rule_attach = explode(',', self::$verification_rule_attach);
                }
                self::$verification_rule_attach = array_map(function($v){
                    return trim($v);
                }, self::$verification_rule_attach);
                if(trim(self::$verification_goal) != '' && in_array(trim(self::$verification_goal), self::$verification_rule_attach)){
                    self::handlingError(Lang::lang('Cannot contain content that is not allowed'));
                }
                break;
            case 'mustin':
                if(!is_array(self::$verification_rule_attach)){
                    self::$verification_rule_attach = explode(',', self::$verification_rule_attach);
                }
                self::$verification_rule_attach = array_map(function($v){
                    return trim($v);
                }, self::$verification_rule_attach);
                if(trim(self::$verification_goal) != '' && !in_array(trim(self::$verification_goal), self::$verification_rule_attach)){
                    self::handlingError(Lang::lang('Must be within the allowed range'));
                }
                break;
            case 'captcha':
                if(Env::has('CAPTCHA_MARK')){
                    $captcha = Captcha::setKey(Env::get('CAPTCHA_MARK'))->verify(self::$verification_goal);
                }
                else{
                    $captcha = Captcha::verify(self::$verification_goal);
                }
                if(!$captcha){
                    self::handlingError(Lang::lang('Verification code error'));
                }
                break;
        }
        self::$verification_rule = '';
        self::$verification_rule_attach = '';
        self::$verification_error = '';
    }
    public static function isPost($condition = true)
    {
        if(!Request::isPost() || !$condition){
            self::$verification_continue = false;
        }
        return self::instance();
    }
    public static function isGet($condition = true)
    {
        if(!Request::isGet() || !$condition){
            self::$verification_continue = false;
        }
        return self::instance();
    }
    public static function isPut($condition = true)
    {
        if(!Request::isPut() || !$condition){
            self::$verification_continue = false;
        }
        return self::instance();
    }
    public static function isDelete($condition = true)
    {
        if(!Request::isDelete() || !$condition){
            self::$verification_continue = false;
        }
        return self::instance();
    }
    public static function isPatch($condition = true)
    {
        if(!Request::isPatch() || !$condition){
            self::$verification_continue = false;
        }
        return self::instance();
    }
    public static function isAjax($condition = true)
    {
        if(!Request::isAjax() || !$condition){
            self::$verification_continue = false;
        }
        return self::instance();
    }
    public static function isPjax($condition = true)
    {
        if(!Request::isPjax() || !$condition){
            self::$verification_continue = false;
        }
        return self::instance();
    }
    public static function success($fun)
    {
        if(self::$verification_continue){
            self::process();
            self::$verification_name = '';
            if(self::$verification_result){
                $fun(self::$verification_parameter);
            }
        }
        return self::instance();
    }
    public static function failure($fun)
    {
        self::process();
        if(!self::$verification_result){
            $message = [];
            if(count(self::$verification_message) != count(self::$verification_message, 1)){
                foreach(self::$verification_message as $val){
                    $message[$val[0]][] = $val[1];
                }
            }
            else{
                $message = self::$verification_message;
            }
            $fun($message);
        }
        return self::instance();
    }
    private static function process()
    {
        if(self::$verification_goal_mark == true && self::$verification_rule !== ''){
            self::verify();
        }
        self::$verification_isrule = false;
        self::$verification_prevrule = [];
        self::$verification_shortfailure = false;
    }
    private static function handlingError($defaultInfo)
    {
        self::$verification_result = false;
        if(empty(self::$verification_error)){
            self::$verification_error = $defaultInfo;
        }
        self::$verification_message[] = empty(self::$verification_name) ? self::$verification_error : [self::$verification_name, self::$verification_error];
    }
    public static function outValue($name)
    {
        if(isset(self::$verification_parameter[$name])){
            return self::$verification_parameter[$name];
        }
        return '';
    }
    public static function errorToString($data, $haskey = false)
    {
        $result = '';
        foreach($data as $akey => $aval){
            if($haskey){
                $result .= empty($result) ? $akey . ': ' : '; ' . $akey . ': ';
            }
            $tmp = '';
            foreach($aval as $val){
                $tmp .= empty($tmp) ? $val : ', ' . $val;
            }
            if($haskey){
                $result .= $tmp;
            }
            else{
                $result .= empty($result) ? $tmp : ', ' . $tmp;
            }
        }
        return $result;
    }
}