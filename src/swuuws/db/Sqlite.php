<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws\db;

use swuuws\Env;
use swuuws\exception\PdoException;
use swuuws\File;
use swuuws\Pdo;
use swuuws\Swuuws;

class Sqlite implements iDb
{
    private static $lastInsertId;
    private static $lastName = null;
    private static $result;
    private static $index;
    private static $primary;
    private static $hasId;
    public static function createDb($dbname, $username = '', $password = '', $host = '', $port = '')
    {
        $path = APP . 'sqlite';
        File::newFolder($path, true);
        $name = $path . DS . basename($dbname, '.db') . '.db';
        Pdo::connect('sqlite:' . $name);
        if(is_file($name)){
            return true;
        }
        else{
            return false;
        }
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
        $name = APP . 'sqlite' . DS . basename(Env::get('DB_DATABASE'), '.db') . '.db';
        return Pdo::connect('sqlite:' . $name);
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
            return true;
        } catch(PdoException $e){
            self::rollBack();
            return $e->getMessage();
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
            'time' => 'NUMERIC',
            'year' => 'NUMERIC',
            'datetime' => 'DATETIME',
            'timestamp' => 'NUMERIC',
            'char' => 'CHARACTER',
            'varchar' => 'VARCHAR',
            'tinyblob' => 'BLOB',
            'tinytext' => 'TEXT',
            'blob' => 'BLOB',
            'text' => 'TEXT',
            'mediumblob' => 'BLOB',
            'mediumtext' => 'TEXT',
            'longblob' => 'BLOB',
            'longtext' => 'TEXT'
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
        self::$index = [];
        self::$primary = false;
        self::$hasId = false;
        $result = false;
        $sql = 'CREATE TABLE IF NOT EXISTS ' . $tableName . '(';
        $subsql = '';
        foreach($array[$key] as $akey => $aval){
            $subsql .= self::statement($akey, $aval, $tableName);
        }
        if(!self::$primary){
            if(!self::$hasId){
                $subsql = 'id INTEGER PRIMARY KEY AUTOINCREMENT,' . $subsql;
                $result = ['name' => 'id', 'type' => 'int'];
            }
            else{
                $subsql .= 'PRIMARY KEY (id),';
            }
        }
        $sql .= $subsql;
        if(is_array($index) && count($index) > 0){
            foreach($index as $key => $val){
                self::$index[] = 'CREATE INDEX IF NOT EXISTS ' . $key . ' ON ' . $tableName . '(' . $val . ');';
            }
        }
        $sql = rtrim($sql, ',');
        $sql .= ');';
        try{
            self::execute($sql);
            foreach(self::$index as $vsql){
                self::execute($vsql);
            }
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
    private static function statement($field, $attribute, $tableName)
    {
        if($field == 'id'){
            self::$hasId = true;
        }
        $statement = '';
        $statement .= $field;
        if(isset($attribute['unsigned']) && $attribute['unsigned'] == true){
            $statement .= ' UNSIGNED BIG';
        }
        if(isset($attribute['type'])){
            $statement .= ' ' . $attribute['type'];
        }
        if(isset($attribute['len'])){
            $attribute['len'] = trim(rtrim(ltrim(trim($attribute['len']), '('), ')'));
            $statement .= '(' . $attribute['len'] . ')';
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
            $statement .= ' AUTOINCREMENT';
        }
        if(isset($attribute['defaults'])){
            if(empty($attribute['defaults'])){
                $statement .= ' DEFAULT \'\'';
            }
            elseif(is_numeric($attribute['defaults'])){
                $statement .= ' DEFAULT ' . $attribute['defaults'];
            }
            else{
                $statement .= ' DEFAULT \'' . $attribute['defaults'] . '\'';
            }
        }
        if(isset($attribute['primary']) && $attribute['primary'] == true){
            $statement .= ' PRIMARY KEY';
            self::$primary = true;
        }
        if(isset($attribute['unique'])){
            $statement .= ' UNIQUE';
        }
        if(isset($attribute['index'])){
            if(!empty($attribute['index'])){
                self::$index[] = 'CREATE INDEX IF NOT EXISTS ' . $attribute['index'] . ' ON ' . $tableName . '(' . $field . ');';
            }
            else{
                self::$index[] = 'CREATE INDEX IF NOT EXISTS ' . $field . ' ON ' . $tableName . '(' . $field . ');';
            }
        }
        return $statement . ',';
    }
    public static function hasTable($name)
    {
        $name = self::addPrefix($name);
        $sql = 'select * from sqlite_master where type = \'table\' and name = \'' . $name . '\'';
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
        try{
            $sql = 'DELETE FROM ' . $name;
            self::execute($sql);
            $sql = 'UPDATE sqlite_sequence SET seq = 0 WHERE name =\'' . $name . '\'';
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
        self::$index = [];
        $sql = 'ALTER TABLE ' . $tableName;
        $type = strtolower(trim($type));
        if($type == 'add'){
            foreach($array[$key] as $akey => $aval){
                $sql .= ' ADD COLUMN ' . $akey;
                if(isset($aval['unsigned']) && $aval['unsigned'] == true){
                    $sql .= ' UNSIGNED BIG';
                }
                if(isset($aval['type'])){
                    $sql .= ' ' . $aval['type'];
                }
                if(isset($aval['len'])){
                    $aval['len'] = trim(rtrim(ltrim(trim($aval['len']), '('), ')'));
                    $sql .= '(' . $aval['len'] . ')';
                }
                if(isset($aval['notnull'])){
                    if($aval['notnull'] == true){
                        $sql .= ' NOT NULL';
                    }
                    else{
                        $sql .= ' NULL';
                    }
                }
                if(isset($aval['increment']) && $aval['increment'] == true){
                    $sql .= ' AUTOINCREMENT';
                }
                if(isset($aval['defaults'])){
                    if(is_numeric($aval['defaults'])){
                        $sql .= ' DEFAULT ' . $aval['defaults'];
                    }
                    elseif(empty($aval['defaults'])){
                        $sql .= ' DEFAULT \'\'';
                    }
                    else{
                        $sql .= ' DEFAULT \'' . $aval['defaults'] . '\'';
                    }
                }
                if(isset($aval['unique'])){
                    $sql .= ' UNIQUE';
                }
                if(isset($aval['index'])){
                    if(!empty($aval['index'])){
                        self::$index[] = 'CREATE INDEX IF NOT EXISTS ' . $aval['index'] . ' ON ' . $tableName . '(' . $akey . ');';
                    }
                    else{
                        self::$index[] = 'CREATE INDEX IF NOT EXISTS ' . $akey . ' ON ' . $tableName . '(' . $akey . ');';
                    }
                }
                $sql .= ',';
            }
            if(is_array($index) && count($index) > 0){
                foreach($index as $key => $val){
                    self::$index[] = 'CREATE INDEX IF NOT EXISTS ' . $key . ' ON ' . $tableName . '(' . $val . ');';
                }
            }
        }
        elseif($type == 'change'){
            throw new PdoException(2);
        }
        $sql = rtrim($sql, ',');
        $sql .= ';';
        try{
            self::execute($sql);
            foreach(self::$index as $vsql){
                self::execute($vsql);
            }
            return true;
        } catch(PdoException $e){
            return false;
        }
    }
    public static function deleteField($tableName, $fieldName)
    {
        throw new PdoException(3);
    }
    public static function deletePrimary($tableName)
    {
        throw new PdoException(4);
    }
    public static function addPrimary($tableName, $fieldName)
    {
        throw new PdoException(2);
    }
}