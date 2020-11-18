<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws\model;

use swuuws\Dbase;

class ModifyModel
{
    private static $field;
    private static $fields = [];
    private static $hasField = [];
    private static $noField = [];
    private static $model;
    private static $modelName;
    private static $index;
    private static $delindex;
    private static $hang = false;
    private static $type;
    private static $after;
    private static $toname = [];
    private static $instance;
    private static function instance()
    {
        if(empty(self::$instance)){
            self::$instance = new ModifyModel();
        }
        return self::$instance;
    }
    public static function modifyModel($name, $hang = false)
    {
        if(!$hang){
            $name = trim($name);
            self::$modelName = $name;
            self::$model = [];
            self::$index = [];
            self::$model[self::$modelName] = [];
        }
        else{
            self::$hang = $hang;
        }
        return self::instance();
    }
    public static function add($name)
    {
        if(!self::$hang){
            if(count(self::$noField) == 0 || !isset(self::$noField[$name]) || (isset(self::$noField[$name]) && self::$noField[$name] == true)){
                $name = trim($name);
                self::$field = $name;
                self::$type = 'add';
                self::$model[self::$modelName][self::$field] = [];
            }
        }
        return self::instance();
    }
    public static function change($name, $toname = '')
    {
        $name = trim($name);
        $toname = trim($toname);
        self::$field = $name;
        self::$toname[self::$field]['toname'] = $toname;
        self::$type = 'change';
        self::$model[self::$modelName][self::$field] = ['toname' => $toname];
        return self::instance();
    }
    public static function type($name)
    {
        if(!self::$hang){
            $name = trim($name);
            self::$toname[self::$field]['type'] = $name;
            self::$fields[self::$field]['type'] = $name;
            self::$model[self::$modelName][self::$field]['type'] = Dbase::map($name);
        }
        return self::instance();
    }
    public static function len($len)
    {
        if(!self::$hang){
            self::$toname[self::$field]['len'] = $len;
            self::$fields[self::$field]['len'] = $len;
            self::$model[self::$modelName][self::$field]['len'] = $len;
        }
        return self::instance();
    }
    public static function unsigned()
    {
        if(!self::$hang){
            self::$model[self::$modelName][self::$field]['unsigned'] = true;
        }
        return self::instance();
    }
    public static function notnull()
    {
        if(!self::$hang){
            self::$model[self::$modelName][self::$field]['notnull'] = true;
        }
        return self::instance();
    }
    public static function null()
    {
        if(!self::$hang){
            self::$model[self::$modelName][self::$field]['notnull'] = false;
        }
        return self::instance();
    }
    public static function defaults($value)
    {
        if(!self::$hang){
            self::$model[self::$modelName][self::$field]['defaults'] = $value;
        }
        return self::instance();
    }
    public static function after($value)
    {
        if(!self::$hang){
            $value = trim($value);
            self::$after = $value;
            self::$model[self::$modelName][self::$field]['after'] = $value;
        }
        return self::instance();
    }
    public static function unique($name = null)
    {
        if(!self::$hang){
            self::$model[self::$modelName][self::$field]['unique'] = $name;
        }
        return self::instance();
    }
    public static function index($name = '')
    {
        if(!self::$hang){
            $name = trim($name);
            self::$model[self::$modelName][self::$field]['index'] = $name;
        }
        return self::instance();
    }
    public static function addIndex($name, $index = null)
    {
        if(!self::$hang){
            if(is_array($name)){
                self::$index = array_merge(self::$index, $name);
            }
            else{
                self::$index = array_merge(self::$index, [$name => $index]);
            }
        }
        return self::instance();
    }
    public static function delIndex($name)
    {
        if(!self::$hang){
            if(is_array($name)){
                self::$delindex = array_merge(self::$delindex, $name);
            }
            else{
                self::$delindex[] = $name;
            }
        }
        return self::instance();
    }
    public static function modify()
    {
        if(!self::$hang){
            $result = Dbase::modifyTable(self::$model, self::$index, self::$type, self::$delindex);
            if($result){
                $modelFile = self::getModelFile();
                preg_match('/{([\s\S]*)}/', $modelFile, $matches);
                if(self::$type == 'add'){
                    $old = trim($matches[1], PHP_EOL);
                    $oldArr = explode(PHP_EOL, $old);
                    $indent = '    ';
                    $new = [];
                    foreach(self::$fields as $item => $val){
                        $tmp = $val['type'];
                        if(isset($val['len'])){
                            $tmp .= ',' . trim(rtrim(ltrim(trim($val['len']), '('), ')'));
                        }
                        $new[] = $indent . 'public $' . $item . ' = \'' . $tmp . '\';';
                    }
                    $attArr = [];
                    $isAfter = false;
                    foreach($oldArr as $val){
                        $attArr[] = $val;
                        if(stripos($val, 'public $' . self::$after . ' =') !== false){
                            $attArr = array_merge($attArr, $new);
                            $isAfter = true;
                        }
                    }
                    if(!$isAfter){
                        $attArr = array_merge($attArr, $new);
                    }
                    $attributes = implode(PHP_EOL, array_unique($attArr));
                    $attributes = '{' . PHP_EOL . $attributes . PHP_EOL . '}';
                    $modelFile = preg_replace('/{([\s\S]*)}/', $attributes, $modelFile);
                    file_put_contents(self::modelFile(), $modelFile);
                }
                elseif(self::$type == 'change'){
                    if(count(self::$toname) > 0){
                        $old = trim($matches[1], PHP_EOL);
                        $oldArr = explode(PHP_EOL, $old);
                        $indent = '    ';
                        foreach(self::$toname as $key => $val){
                            foreach($oldArr as $okey => $oval){
                                if(stripos($oval, 'public $' . $key . ' =') !== false){
                                    $eqindex = strpos($oval, '=');
                                    $left = substr($oval, 0, $eqindex);
                                    $right = substr($oval, $eqindex);
                                    if(false !== $commaindex = strpos($right, ',')){
                                        $commaLeft = substr($right, 0, $commaindex);
                                        $commaRight = substr($right, $commaindex);
                                    }
                                    else{
                                        $commaLeft = $right;
                                        $commaRight = '';
                                    }
                                    if(!empty($val['toname'])){
                                        $left = $indent . 'public $' . $val['toname'] . ' ';
                                    }
                                    if(!empty($val['type'])){
                                        $commaLeft = '= \'' . $val['type'];
                                    }
                                    if(!empty($val['type'])){
                                        if(!isset($val['len'])){
                                            $commaRight = '\';';
                                        }
                                        else{
                                            $commaRight = ',' . trim(rtrim(ltrim(trim($val['len']), '('), ')')) . '\';';
                                        }
                                    }
                                    $oldArr[$okey] = $left . $commaLeft . $commaRight;
                                }
                                else{
                                    continue;
                                }
                            }
                        }
                        $attributes = implode(PHP_EOL, array_unique($oldArr));
                        $attributes = '{' . PHP_EOL . $attributes . PHP_EOL . '}';
                        $modelFile = preg_replace('/{([\s\S]*)}/', $attributes, $modelFile);
                        file_put_contents(self::modelFile(), $modelFile);
                    }
                }
            }
        }
    }
    public static function delPrimary()
    {
        Dbase::deletePrimary(self::$modelName);
        return self::instance();
    }
    public static function addPrimary($name)
    {
        $name = trim($name);
        Dbase::addPrimary(self::$modelName, $name);
        return self::instance();
    }
    public static function del($name)
    {
        $name = trim($name);
        if(count(self::$hasField) == 0 || !isset(self::$hasField[$name]) || (isset(self::$hasField[$name]) && self::$hasField[$name] == true)){
            $result = Dbase::deleteField(self::$modelName, $name);
            if($result){
                $modelFile = self::getModelFile();
                preg_match('/{([\s\S]*)}/', $modelFile, $matches);
                $old = trim($matches[1], PHP_EOL);
                $oldArr = explode(PHP_EOL, $old);
                $newArr = [];
                foreach($oldArr as $val){
                    if(stripos($val, 'public $' . $name . ' =') !== false || stripos($val, 'public $' . $name . ';') !== false){
                        continue;
                    }
                    $newArr[] = $val;
                }
                $attributes = implode(PHP_EOL, $newArr);
                $attributes = '{' . PHP_EOL . $attributes . PHP_EOL . '}';
                $modelFile = preg_replace('/{([\s\S]*)}/', $attributes, $modelFile);
                file_put_contents(self::modelFile(), $modelFile);
            }
        }
        return self::instance();
    }
    public static function ifHas($name)
    {
        $name = trim($name);
        $modelFile = self::getModelFile();
        if(stripos($modelFile, 'public $' . $name . ' =') !== false || stripos($modelFile, 'public $' . $name . ';') !== false){
            self::$hasField[$name] = true;
        }
        else{
            self::$hasField[$name] = false;
        }
        return self::instance();
    }
    public static function ifNo($name)
    {
        $name = trim($name);
        $modelFile = self::getModelFile();
        if(stripos($modelFile, 'public $' . $name . ' =') !== false || stripos($modelFile, 'public $' . $name . ';') !== false){
            self::$noField[$name] = false;
        }
        else{
            self::$noField[$name] = true;
        }
        return self::instance();
    }
    private static function getModelFile()
    {
        return file_get_contents(self::modelFile());
    }
    private static function modelFile()
    {
        $fname = ucfirst(self::$modelName);
        return APP . 'model' . DS . $fname . '.php';
    }
}