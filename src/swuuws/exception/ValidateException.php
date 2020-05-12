<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws\exception;

use swuuws\Exception;

class ValidateException extends Exception
{
    public function __construct($code = 0, $append = '')
    {
        $message = '';
        if($code == 0){
            $message = 'Post item not found';
        }
        elseif($code == 1){
            $message = 'Get item not found';
        }
        elseif($code == 2){
            $message = 'Put item not found';
        }
        elseif($code == 3){
            $message = 'Delete item not found';
        }
        elseif($code == 4){
            $message = 'Patch item not found';
        }
        parent::__construct($message . $append, $code);
    }
}