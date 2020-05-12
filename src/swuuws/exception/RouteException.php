<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws\exception;

use swuuws\Exception;

class RouteException extends Exception
{
    public function __construct($code = 0, $append = '')
    {
        $message = '';
        if($code == 0){
            $message = 'Access is not allowed and may be mismatched';
        }
        elseif($code == 1){
            $message = 'Route alias cannot be duplicated';
        }
        elseif($code == 2){
            $message = 'Error in routing settings';
        }
        elseif($code == 3){
            $message = 'Page not found';
        }
        elseif($code == 4){
            $message = 'The controller or method can only contain underscores "_", and do not allow connecting lines "-"';
        }
        elseif($code == 5){
            $message = 'Controller method not found';
        }
        elseif($code == 6){
            $message = 'Controller not found';
        }
        elseif($code == 7){
            $message = 'Controller or method not found';
        }
        elseif($code == 8){
            $message = 'Cannot call private methods';
        }
        elseif($code == 9){
            $message = 'Method not found, or not public method';
        }
        elseif($code == 10){
            $message = 'Parameter of method is missing';
        }
        parent::__construct($message . $append, $code);
    }
}