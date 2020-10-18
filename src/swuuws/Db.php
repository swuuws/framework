<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws;

class Db
{
    private static $type = '';
    /**
     * Create a database.
     *
     * @param  $dbname, $db, $username, $password, $host, $port
     * @return number
     */
    public static function createDb($dbname, $db = 'sqlite', $username = '', $password = '', $host = 'localhost', $port = '3306')
    {
        if(empty(self::$type)){
            self::$type = ucfirst(strtolower($db));
        }
        return call_user_func('swuuws\\db\\' . self::$type . '::createDb', $dbname, $username, $password, $host, $port);
    }
    /**
     * Execute statement.
     *
     * @param  $sql, $array
     * @return number
     */
    public static function execute($sql, $array = [])
    {
        self::prepare();
        return call_user_func('swuuws\\db\\' . self::$type . '::execute', $sql, $array);
    }
    /**
     * Query the records.
     *
     * @param  $sql, $array
     * @return boolean
     */
    public static function query($sql, $array = [], $mode = 'name')
    {
        self::prepare();
        return call_user_func('swuuws\\db\\' . self::$type . '::query', $sql, $array, $mode);
    }
    public static function setLast($name = null)
    {
        self::prepare();
        return call_user_func('swuuws\\db\\' . self::$type . '::setLast', $name);
    }
    public static function getLast()
    {
        self::prepare();
        return call_user_func('swuuws\\db\\' . self::$type . '::getLast');
    }
    public static function get()
    {
        self::prepare();
        return call_user_func('swuuws\\db\\' . self::$type . '::get');
    }
    public static function getAll()
    {
        self::prepare();
        return call_user_func('swuuws\\db\\' . self::$type . '::getAll');
    }
    public static function transaction($func)
    {
        self::prepare();
        return call_user_func('swuuws\\db\\' . self::$type . '::transaction', $func);
    }
    public static function beginTransaction()
    {
        self::prepare();
        return call_user_func('swuuws\\db\\' . self::$type . '::beginTransaction');
    }
    public static function commit()
    {
        self::prepare();
        return call_user_func('swuuws\\db\\' . self::$type . '::commit');
    }
    public static function rollBack()
    {
        self::prepare();
        return call_user_func('swuuws\\db\\' . self::$type . '::rollBack');
    }
    private static function prepare()
    {
        if(empty(self::$type)){
            self::$type = ucfirst(strtolower(Env::get('DB_CONNECTION')));
        }
    }
    public static function hasTable($name)
    {
        self::prepare();
        return call_user_func('swuuws\\db\\' . self::$type . '::hasTable', $name);
    }
    public static function delTable($name)
    {
        self::prepare();
        return call_user_func('swuuws\\db\\' . self::$type . '::delTable', $name);
    }
    public static function clearTable($name)
    {
        self::prepare();
        return call_user_func('swuuws\\db\\' . self::$type . '::clearTable', $name);
    }
}