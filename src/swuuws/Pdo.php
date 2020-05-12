<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws;

class Pdo extends \PDO
{
    protected static $instance;
    public function __construct($dsn, $username, $password){
        return parent::__construct($dsn, $username, $password);
    }
    public static function connect($dsn, $username = '', $password = ''){
        if(!isset(self::$instance)){
            self::$instance = new self($dsn, $username, $password);
            self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return self::$instance;
    }
    public static function unconnect()
    {
        if(!empty(self::$instance)){
            self::$instance = null;
        }
    }
}