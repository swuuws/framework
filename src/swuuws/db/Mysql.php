<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws\db;

use swuuws\Db;
use swuuws\Env;
use swuuws\exception\PdoException;
use swuuws\Pdo;
use swuuws\Swuuws;

class Mysql implements iDb
{
    private static $lastInsertId;
    private static $lastName = null;
    private static $result;
    private static $pdo;
    private static $index;
    private static $primary;
    private static $hasId;
    public static function createDb($dbname, $username = '', $password = '', $host = 'localhost', $port = '3306')
    {
        $pdo = Pdo::connect('mysql:host=' . $host . ';port=' . $port . ';charset=utf8', $username, $password);
        $sql = 'CREATE DATABASE IF NOT EXISTS ' . $dbname . ' DEFAULT CHARSET utf8 COLLATE utf8_general_ci';
        return $pdo->exec($sql);
    }
    public static function execute($sql, $array = [])
    {
        $pdo = self::connect();
        if(is_array($array) && count($array) > 0){
            $pre = $pdo->prepare($sql);
            if(count($array) != count($array, 1)){
                $result = true;
                foreach($array as $subArr){
                    $result = $pre->execute($subArr);
                }
            }
            else{
                $result = $pre->execute($array);
            }
        }
        else{
            $result = $pdo->exec($sql);
        }
        self::$lastInsertId = $pdo->lastInsertId(self::$lastName);
        return $result;
    }
    public static function query($sql, $array = [], $mode = 'name')
    {
        self::$result = null;
        $pdo = self::connect();
        switch($mode){
            case 'number':
                $queryMode = PDO::FETCH_NUM;
                break;
            case 'both':
                $queryMode = PDO::FETCH_BOTH;
                break;
            default:
                $queryMode = PDO::FETCH_ASSOC;
                break;
        }
        if(is_array($array) && count($array) > 0){
            $pre = $pdo->prepare($sql);
            $pre->setFetchMode($queryMode);
            $pre->execute($array);
            self::$result = $pre;
        }
        else{
            self::$result = $pdo->query($sql);
            self::$result->setFetchMode($queryMode);
        }
        if(!empty(self::$result)){
            return true;
        }
        else{
            return false;
        }
    }
    private static function connect()
    {
        if(empty(self::$pdo)){
            self::$pdo = Pdo::connect('mysql:host=' . Env::get('DB_HOST') . ';port=' . Env::get('DB_PORT') . ';dbname=' . Env::get('DB_DATABASE') . ';charset=utf8', Env::get('DB_USERNAME'), Env::get('DB_PASSWORD'));
        }
        return self::$pdo;
    }
    public static function beginTransaction()
    {
        $pdo = self::connect();
        $pdo->beginTransaction();
    }
    public static function commit()
    {
        $pdo = self::connect();
        $pdo->commit();
    }
    public static function rollBack()
    {
        $pdo = self::connect();
        $pdo->rollBack();
    }
    public static function transaction($func)
    {
        try{
            self::beginTransaction();
            $func();
            self::commit();
        } catch(PdoException $e){
            self::rollBack();
        }
    }
    public static function get()
    {
        return self::$result->fetch();
    }
    public static function getAll()
    {
        return self::$result->fetchAll();
    }
    public static function setLast($name)
    {
        self::$lastName = $name;
    }
    public static function getLast()
    {
        return self::$lastInsertId;
    }
    public static function map($key)
    {
        $key = strtolower($key);
        $value = [
            'tinyint' => 'TINYINT',
            'smallint' => 'SMALLINT',
            'mediumint' => 'MEDIUMINT',
            'int' => 'INT',
            'bigint' => 'BIGINT',
            'float' => 'FLOAT',
            'double' => 'DOUBLE',
            'decimal' => 'DECIMAL',
            'real' => 'REAL',
            'boolean' => 'BOOLEAN',
            'date' => 'DATE',
            'time' => 'TIME',
            'year' => 'YEAR',
            'datetime' => 'DATETIME',
            'timestamp' => 'TIMESTAMP',
            'char' => 'CHAR',
            'varchar' => 'VARCHAR',
            'tinyblob' => 'TINYBLOB',
            'tinytext' => 'TINYTEXT',
            'blob' => 'BLOB',
            'text' => 'TEXT',
            'mediumblob' => 'MEDIUMBLOB',
            'mediumtext' => 'MEDIUMTEXT',
            'longblob' => 'LONGBLOB',
            'longtext' => 'LONGTEXT'
        ];
        if(isset($value[$key])){
            return $value[$key];
        }
        else{
            throw new PdoException(1, ': ' . $key);
        }
    }
    public static function newTable($array, $index)
    {
        $key = array_keys($array)[0];
        $name = Swuuws::capitalUnderline($key);
        $tableName = rtrim(Env::get('TABLE_PREFIX'), '_') . '_' . $name;
        self::$index = '';
        self::$primary = false;
        self::$hasId = false;
        $result = false;
        $sql = 'CREATE TABLE IF NOT EXISTS `' . $tableName . '` (';
        $subsql = '';
        foreach($array[$key] as $akey => $aval){
            $subsql .= self::statement($akey, $aval);
        }
        if(!self::$primary){
            if(!self::$hasId){
                $subsql = '`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,' . $subsql;
                $result = ['name' => 'id', 'type' => 'int'];
            }
            self::$index = 'PRIMARY KEY (`id`),' . self::$index;
        }
        $sql .= $subsql;
        if(is_array($index) && count($index) > 0){
            foreach($index as $key => $val){
                $varr = explode(',', $val);
                $varr = array_map(function($item){
                    return '`' . trim($item) . '`';
                }, $varr);
                self::$index .= 'INDEX `' . $key . '` (' . implode(',', $varr) . '),';
            }
        }
        $sql .= self::$index;
        $sql = rtrim($sql, ',');
        $sql .= ') ENGINE=' . Env::get('DB_ENGINE') . '  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;';
        try{
            self::execute($sql);
            if(is_array($result)){
                return $result;
            }
            else{
                return true;
            }
        } catch(PdoException $e){
            return false;
        }
    }
    private static function statement($field, $attribute)
    {
        if($field == 'id'){
            self::$hasId = true;
        }
        $statement = '';
        $statement .= '`' . $field . '`';
        if(isset($attribute['type'])){
            $statement .= ' ' . $attribute['type'];
        }
        if(isset($attribute['len'])){
            $attribute['len'] = trim(rtrim(ltrim(trim($attribute['len']), '('), ')'));
            $statement .= '(' . $attribute['len'] . ')';
        }
        if(isset($attribute['unsigned']) && $attribute['unsigned'] == true){
            $statement .= ' UNSIGNED';
        }
        if(isset($attribute['notnull'])){
            if($attribute['notnull'] == true){
                $statement .= ' NOT NULL';
            }
            else{
                $statement .= ' NULL';
            }
        }
        if(isset($attribute['increment']) && $attribute['increment'] == true){
            $statement .= ' AUTO_INCREMENT';
        }
        if(isset($attribute['defaults'])){
            if(is_numeric($attribute['defaults'])){
                $statement .= ' DEFAULT ' . $attribute['defaults'];
            }
            elseif(empty($attribute['defaults'])){
                $statement .= ' DEFAULT \'\'';
            }
            else{
                $statement .= ' DEFAULT \'' . $attribute['defaults'] . '\'';
            }
        }
        if(isset($attribute['primary']) && $attribute['primary'] == true){
            self::$index .= 'PRIMARY KEY (`' . $field . '`),';
            self::$primary = true;
        }
        if(isset($attribute['unique'])){
            if(!empty($attribute['unique'])){
                self::$index .= 'UNIQUE KEY `' . $attribute['unique'] . '` (`' . $field . '`),';
            }
            else{
                self::$index .= 'UNIQUE KEY `' . $field . '` (`' . $field . '`),';
            }
        }
        if(isset($attribute['index'])){
            if(!empty($attribute['index'])){
                self::$index .= 'INDEX `' . $attribute['index'] . '` (`' . $field . '`),';
            }
            else{
                self::$index .= 'INDEX `' . $field . '` (`' . $field . '`),';
            }
        }
        return $statement . ',';
    }
    public static function hasTable($name)
    {
        $name = self::addPrefix($name);
        $sql = 'SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = \'' . $name . '\' AND TABLE_SCHEMA = \'' . Env::get('DB_DATABASE') . '\'';
        self::query($sql);
        $result = self::getAll();
        if(count($result) > 0){
            return true;
        }
        else{
            return false;
        }
    }
    public static function delTable($name)
    {
        $name = self::addPrefix($name);
        $sql = 'DROP TABLE ' . $name;
        try{
            self::execute($sql);
            return true;
        } catch(PdoException $e){
            return false;
        }
    }
    public static function clearTable($name)
    {
        $name = self::addPrefix($name);
        $sql = 'TRUNCATE TABLE ' . $name;
        try{
            self::execute($sql);
            return true;
        } catch(PdoException $e){
            return false;
        }
    }
    private static function addPrefix($name)
    {
        $prefix = Env::get('TABLE_PREFIX');
        $prefixLen = strlen($prefix);
        if(substr($name, 0, $prefixLen) != $prefix){
            $name = $prefix . $name;
        }
        return $name;
    }
    public static function modifyTable($array, $index, $type, $delIndex = [])
    {
        $key = array_keys($array)[0];
        $name = Swuuws::capitalUnderline($key);
        $tableName = rtrim(Env::get('TABLE_PREFIX'), '_') . '_' . $name;
        self::$index = '';
        $sql = 'ALTER TABLE `' . $tableName . '`';
        $type = strtolower(trim($type));
        if($type == 'add'){
            foreach($array[$key] as $akey => $aval){
                $sql .= ' ADD `' . $akey . '`';
                if(isset($aval['type'])){
                    $sql .= ' ' . $aval['type'];
                }
                if(isset($aval['len'])){
                    $aval['len'] = trim(rtrim(ltrim(trim($aval['len']), '('), ')'));
                    $sql .= '(' . $aval['len'] . ')';
                }
                if(isset($aval['unsigned']) && $aval['unsigned'] == true){
                    $sql .= ' UNSIGNED';
                }
                if(isset($aval['notnull'])){
                    if($aval['notnull'] == true){
                        $sql .= ' NOT NULL';
                    }
                    else{
                        $sql .= ' NULL';
                    }
                }
                if(isset($aval['defaults'])){
                    if(empty($aval['defaults'])){
                        $sql .= ' DEFAULT \'\'';
                    }
                    elseif(is_numeric($aval['defaults'])){
                        $sql .= ' DEFAULT ' . $aval['defaults'];
                    }
                    else{
                        $sql .= ' DEFAULT \'' . $aval['defaults'] . '\'';
                    }
                }
                if(isset($aval['after'])){
                    $sql .= ' AFTER `' . $aval['after'] . '`';
                }
                $sql .= ',';
                if(isset($aval['unique'])){
                    if(!empty($aval['unique'])){
                        $sql .= ' ADD UNIQUE KEY `' . $aval['unique'] . '` (`' . $akey . '`),';
                    }
                    else{
                        $sql .= ' ADD UNIQUE KEY `' . $akey . '` (`' . $akey . '`),';
                    }
                }
                if(isset($aval['index'])){
                    if(!empty($aval['index'])){
                        $sql .= ' ADD INDEX `' . $aval['index'] . '` (`' . $akey . '`),';
                    }
                    else{
                        $sql .= ' ADD INDEX `' . $akey . '` (`' . $akey . '`),';
                    }
                }
            }
            if(is_array($index) && count($index) > 0){
                foreach($index as $key => $val){
                    $varr = explode(',', $val);
                    $varr = array_map(function($item){
                        return '`' . trim($item) . '`';
                    }, $varr);
                    self::$index .= ' ADD INDEX `' . $key . '` (' . implode(',', $varr) . '),';
                }
            }
        }
        elseif($type == 'change'){
            foreach($array[$key] as $akey => $aval){
                $toname = $aval['toname'];
                if(empty($toname)){
                    $toname = $akey;
                }
                $sql .= ' CHANGE `' . $akey . '` `' . $toname . '`';
                if(isset($aval['type'])){
                    $sql .= ' ' . $aval['type'];
                }
                if(isset($aval['len'])){
                    $aval['len'] = trim(rtrim(ltrim(trim($aval['len']), '('), ')'));
                    $sql .= '(' . $aval['len'] . ')';
                }
                if(isset($aval['unsigned']) && $aval['unsigned'] == true){
                    $sql .= ' UNSIGNED';
                }
                if(isset($aval['notnull'])){
                    if($aval['notnull'] == true){
                        $sql .= ' NOT NULL';
                    }
                    else{
                        $sql .= ' NULL';
                    }
                }
                if(isset($aval['defaults'])){
                    if(empty($aval['defaults'])){
                        $sql .= ' DEFAULT \'\'';
                    }
                    elseif(is_numeric($aval['defaults'])){
                        $sql .= ' DEFAULT ' . $aval['defaults'];
                    }
                    else{
                        $sql .= ' DEFAULT \'' . $aval['defaults'] . '\'';
                    }
                }
                $sql .= ',';
                if(isset($aval['unique'])){
                    if(!empty($aval['unique'])){
                        $sql .= ' ADD UNIQUE KEY `' . $aval['unique'] . '` (`' . $akey . '`),';
                    }
                    else{
                        $sql .= ' ADD UNIQUE KEY `' . $akey . '` (`' . $akey . '`),';
                    }
                }
                if(isset($aval['index'])){
                    if(!empty($aval['index'])){
                        $sql .= ' ADD INDEX `' . $aval['index'] . '` (`' . $akey . '`),';
                    }
                    else{
                        $sql .= ' ADD INDEX `' . $akey . '` (`' . $akey . '`),';
                    }
                }
            }
            if(is_array($delIndex) && count($delIndex) > 0){
                foreach($delIndex as $dval){
                    $sql .= ' DROP INDEX `' . $dval . '`,';
                }
            }
            if(is_array($index) && count($index) > 0){
                foreach($index as $key => $val){
                    $varr = explode(',', $val);
                    $varr = array_map(function($item){
                        return '`' . trim($item) . '`';
                    }, $varr);
                    $sql .= ' ADD INDEX `' . $key . '` (' . implode(',', $varr) . '),';
                }
            }
        }
        $sql = rtrim($sql, ',');
        try{
            self::execute($sql);
            return true;
        } catch(PdoException $e){
            return false;
        }
    }
    public static function deleteField($tableName, $fieldName)
    {
        $name = Swuuws::capitalUnderline($tableName);
        $name = rtrim(Env::get('TABLE_PREFIX'), '_') . '_' . $name;
        $fieldName = trim($fieldName);
        $sql = 'ALTER TABLE `' . $name . '` DROP `' . $fieldName . '`';
        try{
            self::execute($sql);
            return true;
        } catch(PdoException $e){
            return false;
        }
    }
    public static function deletePrimary($tableName)
    {
        $name = Swuuws::capitalUnderline($tableName);
        $name = rtrim(Env::get('TABLE_PREFIX'), '_') . '_' . $name;
        $sql = 'ALTER TABLE `' . $name . '` DROP PRIMARY KEY';
        try{
            self::execute($sql);
            return true;
        } catch(PdoException $e){
            return false;
        }
    }
    public static function addPrimary($tableName, $fieldName)
    {
        $name = Swuuws::capitalUnderline($tableName);
        $name = rtrim(Env::get('TABLE_PREFIX'), '_') . '_' . $name;
        $fieldName = trim(rtrim(ltrim(trim($fieldName), '('), ')'));
        $varr = explode(',', $fieldName);
        $varr = array_map(function($item){
            return '`' . trim($item) . '`';
        }, $varr);
        $sql = 'ALTER TABLE `' . $name . '` ADD PRIMARY KEY(' . implode(',', $varr) . ')';
        try{
            self::execute($sql);
            return true;
        } catch(PdoException $e){
            return false;
        }
    }
}