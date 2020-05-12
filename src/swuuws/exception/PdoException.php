<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws\exception;

class PdoException extends \PDOException
{
    public function __construct($code = 0, $append = '')
    {
        $message = '';
        if($code == 0){
            $message = 'Database connection failed';
        }
        elseif($code == 1){
            $message = 'Data type error';
        }
        elseif($code == 2){
            $message = 'Sqlite database cannot change field';
        }
        elseif($code == 3){
            $message = 'Sqlite database cannot delete field';
        }
        elseif($code == 4){
            $message = 'Sqlite database cannot delete primary key';
        }
        parent::__construct($message . $append, $code);
    }
}