<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws;

use swuuws\exception\ImageException;

class Image
{
    private static $instance;
    private static $width;
    private static $height;
    private static $type;
    private static $mime;
    private static $gif;
    private static $img;
    private static $file;
    private static function instance()
    {
        if(empty(self::$instance)){
            self::$instance = new Image();
        }
        return self::$instance;
    }
    public static function get($file)
    {
        if(!is_file($file)){
            throw new ImageException(0);
        }
        $imageInfo = getimagesize($file);
        if($imageInfo === false || ($imageInfo[2] == IMAGETYPE_GIF && empty($imageInfo['channels']))){
            throw new ImageException(0);
        }
        else{
            self::$file = $file;
            self::$width = $imageInfo[0];
            self::$height = $imageInfo[1];
            self::$type = strtolower(image_type_to_extension($imageInfo[2], false));
            self::$mime = $imageInfo['mime'];
            if(self::$type == 'gif'){
                self::$gif = new Gif($file);
                self::$img = imagecreatefromstring(self::$gif->frame());
            }
            else{
                $imgfunc = 'imagecreatefrom' . self::$type;
                self::$img = $imgfunc($file);
            }
            if(empty(self::$img)){
                throw new ImageException(2);
            }
        }
        return self::instance();
    }
    public static function width()
    {
        return self::$width;
    }
    public static function height()
    {
        return self::$height;
    }
    public static function resize($width = null, $height = null, $tofile = null, $quality = 80, $interlace = true)
    {
        if(empty($width) && empty($height)){
            $width = self::$width;
            $height = self::$height;
        }
        elseif(empty($width) && !empty($height)){
            $width = self::$width * $height / self::$height;
        }
        elseif(!empty($width) && empty($height)){
            $height = self::$height * $width / self::$width;
        }
        elseif($width < 0 || $height < 0){
            throw new ImageException(4);
        }
        return self::changeImage($width, $height, $tofile, $quality, $interlace);
    }
    public static function cut($width = null, $height = null, $tofile = null, $position = 'center', $quality = 80, $interlace = true)
    {
        if(empty($width) && empty($height)){
            $width = self::$width;
            $height = self::$height;
            $dst_x = 0;
            $dst_y = 0;
            $src_x = 0;
            $src_y = 0;
        }
        elseif(empty($width) && !empty($height)){
            $width = self::$width * $height / self::$height;
            list($dst_x, $dst_y, $src_x, $src_y) = self::cutPosition($width, $height, $position);
        }
        elseif(!empty($width) && empty($height)){
            $height = self::$height * $width / self::$width;
            list($dst_x, $dst_y, $src_x, $src_y) = self::cutPosition($width, $height, $position);
        }
        elseif($width < 0 || $height < 0){
            throw new ImageException(4);
        }
        else{
            list($dst_x, $dst_y, $src_x, $src_y) = self::cutPosition($width, $height, $position);
        }
        if(self::$width > $width){
            self::$width = $width;
        }
        if(self::$height > $height){
            self::$height = $height;
        }
        return self::changeImage($width, $height, $tofile, $quality, $interlace, $dst_x, $dst_y, $src_x, $src_y);
    }
    private static function cutPosition($width, $height, $position)
    {
        $dst_x = 0;
        $dst_y = 0;
        $src_x = 0;
        $src_y = 0;
        if(is_array($position)){
            if(isset($position[0])){
                $src_x = intval($position[0]);
            }
            if(isset($position[1])){
                $src_y = intval($position[1]);
            }
        }
        elseif(strpos($position, ',') !== false){
            $xyArr = explode(',', str_replace(' ', '', $position));
            if(isset($xyArr[0])){
                $src_x = intval($xyArr[0]);
            }
            if(isset($xyArr[1])){
                $src_y = intval($xyArr[1]);
            }
        }
        elseif($position == 'center'){
            $src_x = round((self::$width - $width) / 2);
            $src_y = round((self::$height - $height) / 2);
        }
        elseif($position == 'centerleft'){
            $src_y = round((self::$height - $height) / 2);
        }
        elseif($position == 'centerright'){
            $src_x = self::$width - $width;
            $src_y = round((self::$height - $height) / 2);
        }
        elseif($position == 'centertop'){
            $src_x = round((self::$width - $width) / 2);
        }
        elseif($position == 'centerbottom'){
            $src_x = round((self::$width - $width) / 2);
            $src_y = self::$height - $height;
        }
        elseif($position == 'righttop'){
            $src_x = self::$width - $width;
        }
        elseif($position == 'leftbottom'){
            $src_y = self::$height - $height;
        }
        elseif($position == 'rightbottom'){
            $src_x = self::$width - $width;
            $src_y = self::$height - $height;
        }
        if($src_x < 0){
            $src_x = 0;
        }
        if($src_y < 0){
            $src_y = 0;
        }
        return [$dst_x, $dst_y, $src_x, $src_y];
    }
    private static function changeImage($width, $height, $tofile = null, $quality = 80, $interlace = true, $dst_x = 0, $dst_y = 0, $src_x = 0, $src_y = 0)
    {
        do {
            $img = imagecreatetruecolor($width, $height);
            $color = imagecolorallocatealpha($img, 255, 255, 255, 127);
            imagefill($img, 0, 0, $color);
            imagecopyresampled($img, self::$img, $dst_x, $dst_y, $src_x, $src_y, $width, $height, self::$width, self::$height);
            imagedestroy(self::$img);
            self::$img = $img;
        } while (!empty(self::$gif) && self::nextGif());
        return self::save($tofile, $quality, $interlace);
    }
    private static function save($file = null, $quality = 80, $interlace = true)
    {
        if(empty($file)){
            $file = self::$file;
        }
        try{
            if(self::$type == 'jpeg' || self::$type == 'jpg'){
                imageinterlace(self::$img, $interlace);
                imagejpeg(self::$img, $file, $quality);
            }
            elseif(self::$type == 'gif' && !empty(self::$gif)){
                self::$gif->save($file);
            }
            elseif(self::$type == 'png'){
                imagesavealpha(self::$img, true);
                imagepng(self::$img, $file, min((int)($quality / 10), 9));
            }
            else{
                $fun = 'image' . self::$type;
                $fun(self::$img, $file, $quality);
            }
            imagedestroy(self::$img);
            return true;
        }catch (\Exception $e){
            imagedestroy(self::$img);
            return false;
        }
    }
    public static function rotate($degree = 90, $tofile = null, $quality = 80, $interlace = true)
    {
        do{
            $img = imagerotate(self::$img, -$degree, imagecolorallocatealpha(self::$img, 0, 0, 0, 127));
            imagedestroy(self::$img);
            self::$img = $img;
        } while (!empty(self::$gif) && self::nextGif());
        return self::save($tofile, $quality, $interlace);
    }
    public static function flip($axis = 'x', $tofile = null, $quality = 80, $interlace = true)
    {
        do{
            $img = imagecreatetruecolor(self::$width, self::$height);
            imagealphablending($img, false);
            imagesavealpha($img, true);
            switch($axis){
                case 'x':
                    for($x = 0; $x < self::$width; $x++){
                        imagecopy($img, self::$img, self::$width - $x - 1, 0, $x, 0, 1, self::$height);
                    }
                    break;
                case 'y':
                    for($y = 0; $y < self::$height; $y++){
                        imagecopy($img, self::$img, 0, self::$height - $y - 1, 0, $y, self::$width, 1);
                    }
                    break;
                default:
                    throw new ImageException(5);
            }
            imagedestroy(self::$img);
            self::$img = $img;
        } while (!empty(self::$gif) && self::nextGif());
        return self::save($tofile, $quality, $interlace);
    }
    public static function watermark($file, $tofile = null, $position = 'center', $offsetx = 0, $offsety = 0, $alpha = 60, $quality = 80, $interlace = true)
    {
        if(!is_file($file)){
            throw new ImageException(0);
        }
        $imageInfo = getimagesize($file);
        if($imageInfo === false || ($imageInfo[2] == IMAGETYPE_GIF && empty($imageInfo['channels']))){
            throw new ImageException(0);
        }
        $wfun = 'imagecreatefrom' . strtolower(image_type_to_extension($imageInfo[2], false));
        $watermark = $wfun($file);
        imagealphablending($watermark, true);
        list($wm_x, $wm_y) = self::watermarkPosition($imageInfo[0], $imageInfo[1], $position, $offsetx, $offsety);
        do {
            $img = imagecreatetruecolor($imageInfo[0], $imageInfo[1]);
            $color = imagecolorallocate($img, 255, 255, 255);
            imagefill($img, 0, 0, $color);
            imagecopy($img, self::$img, 0, 0, $wm_x, $wm_y, $imageInfo[0], $imageInfo[1]);
            imagecopy($img, $watermark, 0, 0, 0, 0, $imageInfo[0], $imageInfo[1]);
            imagecopymerge(self::$img, $img, $wm_x, $wm_y, 0, 0, $imageInfo[0], $imageInfo[1], $alpha);
            imagedestroy($img);
        } while (!empty(self::$gif) && self::nextGif());
        imagedestroy($watermark);
        return self::save($tofile, $quality, $interlace);
    }
    private static function watermarkPosition($width, $height, $position, $offsetx, $offsety)
    {
        $wm_x = 0;
        $wm_y = 0;
        if($position == 'center'){
            $wm_x = round((self::$width - $width) / 2);
            $wm_y = round((self::$height - $height) / 2);
        }
        elseif($position == 'centerleft'){
            $wm_x += $offsetx;
            $wm_y = round((self::$height - $height) / 2);
        }
        elseif($position == 'centerright'){
            $wm_x = self::$width - $width - $offsetx;
            $wm_y = round((self::$height - $height) / 2);
        }
        elseif($position == 'centertop'){
            $wm_x = round((self::$width - $width) / 2);
            $wm_y += $offsety;
        }
        elseif($position == 'centerbottom'){
            $wm_x = round((self::$width - $width) / 2);
            $wm_y = self::$height - $height - $offsety;
        }
        elseif($position == 'lefttop'){
            $wm_x += $offsetx;
            $wm_y += $offsety;
        }
        elseif($position == 'righttop'){
            $wm_x = self::$width - $width - $offsetx;
            $wm_y += $offsety;
        }
        elseif($position == 'leftbottom'){
            $wm_x += $offsetx;
            $wm_y = self::$height - $height - $offsety;
        }
        elseif($position == 'rightbottom'){
            $wm_x = self::$width - $width - $offsetx;
            $wm_y = self::$height - $height - $offsety;
        }
        if($wm_x < 0){
            $wm_x = 0;
        }
        if($wm_y < 0){
            $wm_y = 0;
        }
        return [$wm_x, $wm_y];
    }
    public static function addText($text, $tofile = null, $size = 16, $color = '200,200,200,50', $position = 'center', $offsetx = 0, $offsety = 0, $angle = 0, $font = '', $quality = 80, $interlace = true)
    {
        if(empty($font)){
            $font = File::parentDirectory(__DIR__) . DS . 'ttfscn' . DS . '1.ttf';
        }
        if(!is_file($font)){
            throw new ImageException(6);
        }
        $textInfo = imagettfbbox($size, $angle, $font, $text);
        $minx = min($textInfo[0], $textInfo[2], $textInfo[4], $textInfo[6]);
        $maxx = max($textInfo[0], $textInfo[2], $textInfo[4], $textInfo[6]);
        $miny = min($textInfo[1], $textInfo[3], $textInfo[5], $textInfo[7]);
        $maxy = max($textInfo[1], $textInfo[3], $textInfo[5], $textInfo[7]);
        $tt_x = $minx;
        $tt_y = $miny;
        $width = $maxx - $minx;
        $height = $maxy - $miny;
        list($tt_x, $tt_y) = self::addTextPosition($tt_x, $tt_y, $width, $height, $position, $offsetx, $offsety);
        if(!is_array($color)){
            if(strpos($color, ',') !== false){
                $color = explode(',', $color);
                $color = array_map(function($val){
                    $val = intval(trim($val));
                    if($val < 0){
                        $val = 0;
                    }
                    if($val > 255){
                        $val = 255;
                    }
                    return $val;
                }, $color);
            }
            elseif(substr($color, 0, 1) == '#'){
                $color = str_split(substr($color, 1), 2);
                $color = array_map('hexdec', $color);
            }
            else{
                $color = str_split($color, 2);
                $color = array_map('hexdec', $color);
            }
            if(empty($color[3]) || $color[3] > 127){
                $color[3] = 0;
            }
        }
        if(!is_array($color) || count($color) != 4){
            throw new ImageException(7);
        }
        do {
            $col = imagecolorallocatealpha(self::$img, $color[0], $color[1], $color[2], $color[3]);
            imagettftext(self::$img, $size, $angle, $tt_x, $tt_y, $col, $font, $text);
        } while (!empty(self::$gif) && self::nextGif());
        return self::save($tofile, $quality, $interlace);
    }
    private static function addTextPosition($o_x, $o_y, $width, $height, $position, $offsetx, $offsety)
    {
        $tt_x = 0;
        $tt_y = 0;
        if($position == 'center'){
            $tt_x = round((self::$width - $width) / 2);
            $tt_y = round((self::$height - $height) / 2);
        }
        elseif($position == 'centerleft'){
            $tt_x += $offsetx;
            $tt_y = round((self::$height - $height) / 2);
        }
        elseif($position == 'centerright'){
            $tt_x = self::$width - $width - $offsetx;
            $tt_y = round((self::$height - $height) / 2);
        }
        elseif($position == 'centertop'){
            $tt_x = round((self::$width - $width) / 2);
            $tt_y += $offsety;
        }
        elseif($position == 'centerbottom'){
            $tt_x = round((self::$width - $width) / 2);
            $tt_y = self::$height - $height - $offsety;
        }
        elseif($position == 'lefttop'){
            $tt_x += $offsetx;
            $tt_y += $offsety;
        }
        elseif($position == 'righttop'){
            $tt_x = self::$width - $width - $offsetx;
            $tt_y += $offsety;
        }
        elseif($position == 'leftbottom'){
            $tt_x += $offsetx;
            $tt_y = self::$height - $height - $offsety;
        }
        elseif($position == 'rightbottom'){
            $tt_x = self::$width - $width - $offsetx;
            $tt_y = self::$height - $height - $offsety;
        }
        $tt_x -= $o_x;
        $tt_y -= $o_y;
        if($tt_x < 0){
            $tt_x = 0;
        }
        if($tt_y < 0){
            $tt_y = 0;
        }
        return [$tt_x, $tt_y];
    }
    private static function nextGif()
    {
        ob_start();
        ob_implicit_flush(0);
        imagegif(self::$img);
        $img = ob_get_clean();
        self::$gif->frame($img);
        $next = self::$gif->nextFrame();
        if($next){
            imagedestroy(self::$img);
            self::$img = imagecreatefromstring($next);
            return $next;
        }
        else{
            imagedestroy(self::$img);
            self::$img = imagecreatefromstring(self::$gif->frame());
            return false;
        }
    }
}