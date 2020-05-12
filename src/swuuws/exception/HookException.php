<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws\exception;

use swuuws\Exception;

class HookException extends Exception
{
    public function __construct($code = 0, $append = '')
    {
        $message = '';
        if($code == 0){
            $message = 'Hook is not registered';
        }
        elseif($code == 1){
            $message = 'The hook does not specify the action';
        }
        parent::__construct($message . $append, $code);
    }
}