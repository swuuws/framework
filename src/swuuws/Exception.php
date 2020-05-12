<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws;

class Exception extends \Exception
{
    public function __construct($message = '', $code = 0, \Exception $previous = NULL)
    {
        parent::__construct($message, $code, $previous);
    }
    public function __toString()
    {
        return "[{$this->code}] {$this->file} ({$this->line}): {$this->message}<br>";
    }
}
