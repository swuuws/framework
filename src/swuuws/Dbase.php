<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws;

class Dbase
{
    private static $type = '';
    public static function map($name)
    {
        self::prepare();
        return call_user_func('swuuws\\db\\' . self::$type . '::map', $name);
    }
    public static function newTable($array, $index)
    {
        self::prepare();
        return call_user_func('swuuws\\db\\' . self::$type . '::newTable', $array, $index);
    }
    public static function modifyTable($array, $index, $type, $delIndex)
    {
        self::prepare();
        return call_user_func('swuuws\\db\\' . self::$type . '::modifyTable', $array, $index, $type, $delIndex);
    }
    public static function deleteField($tableName, $fieldName)
    {
        self::prepare();
        return call_user_func('swuuws\\db\\' . self::$type . '::deleteField', $tableName, $fieldName);
    }
    private static function prepare()
    {
        if(empty(self::$type)){
            self::$type = ucfirst(strtolower(Env::get('DB_CONNECTION')));
        }
    }
    public static function deletePrimary($tableName)
    {
        self::prepare();
        return call_user_func('swuuws\\db\\' . self::$type . '::deletePrimary', $tableName);
    }
    public static function addPrimary($tableName, $fieldName)
    {
        self::prepare();
        return call_user_func('swuuws\\db\\' . self::$type . '::addPrimary', $tableName, $fieldName);
    }
}