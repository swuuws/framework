<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws\exception;

use swuuws\Exception;

class ImageException extends Exception
{
    public function __construct($code = 0, $append = '')
    {
        $message = '';
        if($code == 0){
            $message = 'Invalid image file';
        }
        elseif($code == 1){
            $message = 'Failed to decode GIF image';
        }
        elseif($code == 2){
            $message = 'Failed to get image';
        }
        elseif($code == 3){
            $message = 'Image save failed';
        }
        elseif($code == 4){
            $message = 'The image width or height cannot be less than 0';
        }
        elseif($code == 5){
            $message = 'Only horizontal flip (x) and vertical flip (y)';
        }
        elseif($code == 6){
            $message = 'Font file not found';
        }
        elseif($code == 7){
            $message = 'Wrong color value';
        }
        parent::__construct($message . $append, $code);
    }
}