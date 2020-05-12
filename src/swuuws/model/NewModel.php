<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws\model;

use swuuws\Db;
use swuuws\Dbase;

class NewModel
{
    private static $field;
    private static $fields = [];
    private static $model;
    private static $modelName;
    private static $index;
    private static $hang = false;
    private static $instance;
    private static function instance()
    {
        if(empty(self::$instance)){
            self::$instance = new NewModel();
        }
        return self::$instance;
    }
    public static function newModel($name, $hang = false)
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
            $name = trim($name);
            self::$field = $name;
            self::$model[self::$modelName][self::$field] = [];
        }
        return self::instance();
    }
    public static function type($name)
    {
        if(!self::$hang){
            $name = trim($name);
            self::$fields[self::$field]['type'] = $name;
            self::$model[self::$modelName][self::$field]['type'] = Dbase::map($name);
        }
        return self::instance();
    }
    public static function len($len)
    {
        if(!self::$hang){
            self::$fields[self::$field]['len'] = $len;
            self::$model[self::$modelName][self::$field]['len'] = $len;
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
    public static function unsigned()
    {
        if(!self::$hang){
            self::$model[self::$modelName][self::$field]['unsigned'] = true;
        }
        return self::instance();
    }
    public static function increment()
    {
        if(!self::$hang){
            self::$model[self::$modelName][self::$field]['increment'] = true;
        }
        return self::instance();
    }
    public static function primary()
    {
        if(!self::$hang){
            self::$model[self::$modelName][self::$field]['primary'] = true;
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
    public static function notnull()
    {
        if(!self::$hang){
            self::$model[self::$modelName][self::$field]['notnull'] = true;
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
    public static function create()
    {
        if(!self::$hang){
            $result = Dbase::newTable(self::$model, self::$index);
            if($result !== false){
                $attributes = '';
                $indent = '    ';
                if(is_array($result)){
                    $rname = trim($result['name']);
                    $attributes .= $indent . 'public $' . $rname . ';' . PHP_EOL;
                }
                foreach(self::$fields as $item => $val){
                    $tmp = $val['type'];
                    if(isset($val['len'])){
                        $tmp .= ',' . trim(rtrim(ltrim(trim($val['len']), '('), ')'));
                    }
                    $attributes .= $indent . 'public $' . $item . ' = \'' . $tmp . '\';' . PHP_EOL;
                }
                $attributes = rtrim($attributes, PHP_EOL);
                $name = ucfirst(self::$modelName);
                $mpath = APP . 'model' . DS . $name . '.php';
                if(!is_file($mpath)){
                    $model = '<?php
namespace model;

use swuuws\Mod;

class ' . $name . ' extends Mod
{
' . $attributes . '
}';
                    file_put_contents($mpath, $model);
                }
            }
        }
    }
}