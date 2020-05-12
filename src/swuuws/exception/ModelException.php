<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws\exception;

use swuuws\Exception;

class ModelException extends Exception
{
    public function __construct($code = 0, $append = '')
    {
        $message = '';
        if($code == 0){
            $message = 'String length exceeds allowed value';
        }
        elseif($code == 1){
            $message = 'Requires an integer';
        }
        elseif($code == 2){
            $message = 'Requirement is a number';
        }
        elseif($code == 3){
            $message = 'Date format error';
        }
        elseif($code == 4){
            $message = 'Year is malformed';
        }
        elseif($code == 5){
            $message = 'Time format error';
        }
        elseif($code == 6){
            $message = 'Model element error';
        }
        elseif($code == 7){
            $message = 'Model elements are inconsistent';
        }
        elseif($code == 8){
            $message = 'The parameter of limit must be a number';
        }
        elseif($code == 9){
            $message = 'The parameter of offset must be a number';
        }
        parent::__construct($message . $append, $code);
    }
}