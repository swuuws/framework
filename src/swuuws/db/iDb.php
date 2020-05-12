<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws\db;

interface iDb
{
    public static function createDb($dbname, $username, $password, $host, $port);
    public static function execute($sql, $array);
    public static function query($sql);
    public static function setLast($name);
    public static function getLast();
    public static function get();
    public static function getAll();
    public static function beginTransaction();
    public static function commit();
    public static function rollBack();
    public static function transaction($func);
    public static function map($key);
    public static function newTable($array, $index);
    public static function hasTable($name);
    public static function delTable($name);
    public static function clearTable($name);
    public static function modifyTable($array, $index, $type, $delIndex);
    public static function deleteField($tableName, $fieldName);
    public static function deletePrimary($tableName);
    public static function addPrimary($tableName, $fieldName);
}