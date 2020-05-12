<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws;

use swuuws\model\ModifyModel;
use swuuws\model\NewModel;

class Model
{
    private static $hasModel = [];
    private static $noModel = [];
    private static $instance;
    private static function instance()
    {
        if(empty(self::$instance)){
            self::$instance = new Model();
        }
        return self::$instance;
    }
    public static function newModel($name)
    {
        if(count(self::$noModel) == 0 || !isset(self::$noModel[$name]) || (isset(self::$noModel[$name]) && self::$noModel[$name] == true)){
            return NewModel::newModel($name);
        }
        return NewModel::newModel($name, true);
    }
    public static function modifyModel($name)
    {
        if(count(self::$hasModel) == 0 || !isset(self::$hasModel[$name]) || (isset(self::$hasModel[$name]) && self::$hasModel[$name] == true)){
            return ModifyModel::modifyModel($name);
        }
        return ModifyModel::modifyModel($name, true);
    }
    public static function hasModel($name)
    {
        $tableName = Swuuws::capitalUnderline($name);
        if(Db::hasTable($tableName)){
            self::$hasModel[$name] = true;
            return true;
        }
        else{
            self::$hasModel[$name] = false;
        }
        return false;
    }
    public static function ifHasModel($name)
    {
        $tableName = Swuuws::capitalUnderline($name);
        if(Db::hasTable($tableName)){
            self::$hasModel[$name] = true;
        }
        else{
            self::$hasModel[$name] = false;
        }
        return self::instance();
    }
    public static function ifNoModel($name)
    {
        $tableName = Swuuws::capitalUnderline($name);
        if(!Db::hasTable($tableName)){
            self::$noModel[$name] = true;
        }
        else{
            self::$noModel[$name] = false;
        }
        return self::instance();
    }
    public static function delModel($name)
    {
        if(count(self::$hasModel) == 0 || !isset(self::$hasModel[$name]) || (isset(self::$hasModel[$name]) && self::$hasModel[$name] == true)){
            $className = ucfirst($name);
            $tableName = Swuuws::capitalUnderline($name);
            $result = Db::delTable($tableName);
            if($result){
                $mpath = APP . 'model' . DS . $className . '.php';
                @unlink($mpath);
                return true;
            }
        }
        return false;
    }
    public static function clearModel($name)
    {
        if(count(self::$hasModel) == 0 || !isset(self::$hasModel[$name]) || (isset(self::$hasModel[$name]) && self::$hasModel[$name] == true)){
            $tableName = Swuuws::capitalUnderline($name);
            Db::clearTable($tableName);
        }
    }
    public static function getRow()
    {
        return Db::get();
    }
    public static function getAll()
    {
        return Db::getAll();
    }
}