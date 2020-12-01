<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws;

use swuuws\exception\ImageException;
use swuuws\gif\Decoder;
use swuuws\gif\Encoder;

class Gif
{
    private $frames = [];
    private $delays = [];
    public function __construct($gifFile = null, $mod = 'uri')
    {
        if(!is_null($gifFile)){
            if($mod == 'uri' && is_file($gifFile)){
                $gifFile = file_get_contents($gifFile);
            }
            /* 解码GIF图片 */
            try{
                $gif = new Decoder($gifFile);
                $this->frames = $gif->getFrames();
                $this->delays = $gif->getDelays();
            }catch(\Exception $e){
                throw new ImageException(1);
            }
        }
    }
    public function frame($stream = null)
    {
        if(is_null($stream)){
            $current = current($this->frames);
            return false === $current ? reset($this->frames) : $current;
        }
        $this->frames[key($this->frames)] = $stream;
        return '';
    }
    public function nextFrame()
    {
        return next($this->frames);
    }
    public function save($gifFile)
    {
        $gif = new Encoder($this->frames, $this->delays, 0, 2, 0, 0, 0, 'bin');
        file_put_contents($gifFile, $gif->getAnimation());
    }
}