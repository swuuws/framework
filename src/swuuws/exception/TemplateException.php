<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws\exception;

use swuuws\Exception;

class TemplateException extends Exception
{
    public function __construct($code = 0, $append = '')
    {
        $message = '';
        if($code == 0){
            $message = 'Template method not found';
        }
        elseif($code == 1){
            $message = 'Template functions can only call private methods';
        }
        elseif($code == 2){
            $message = 'Template method not found, template method must be private';
        }
        parent::__construct($message . $append, $code);
    }
}