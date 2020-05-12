<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws\files;

use swuuws\Date;
use swuuws\Env;
use swuuws\File;
use swuuws\Lang;

class Upload
{
    private static $swuuws_file = [];
    private static $swuuws_error = [];
    private static $swuuws_message = [];
    private static $swuuws_ignore = false;
    private static $swuuws_block = false;
    private static $swuuws_path = '';
    private static $swuuws_uploaded = [];
    private static $instance;
    private static function instance()
    {
        if(empty(self::$instance)){
            self::$instance = new Upload();
        }
        return self::$instance;
    }
    public function __construct()
    {
        Lang::load(File::parentDirectory(__DIR__, 4) . DS . 'lang' . DS . Lang::getAuto() . '.php', true);
    }
    public static function getInstance()
    {
        return self::instance();
    }
    public static function get($name)
    {
        $instance = self::instance();
        if(isset($_FILES[$name])){
            self::$swuuws_file = $_FILES[$name];
            if(is_array(self::$swuuws_file['error'])){
                foreach(self::$swuuws_file['error'] as $key => $val){
                    if($val != 0){
                        self::recordError($val);
                    }
                }
            }
            elseif(self::$swuuws_file['error'] != 0){
                self::recordError(self::$swuuws_file['error']);
            }
            if(count(self::$swuuws_error) > 0){
                self::$swuuws_ignore = true;
            }
        }
        else{
            self::$swuuws_ignore = true;
        }
        return $instance;
    }
    private static function recordError($error)
    {
        switch($error){
            case 1:
            case UPLOAD_ERR_INI_SIZE:
                self::$swuuws_message[] = Lang::lang('The uploaded file exceeds the value of the upload_max_filesize option limit in php.ini');
                break;
            case 2:
            case UPLOAD_ERR_FORM_SIZE:
                self::$swuuws_message[] = Lang::lang('The size of the uploaded file exceeds the value specified by the MAX_FILE_SIZE option in the HTML form');
                break;
            case 3:
            case UPLOAD_ERR_PARTIAL:
                self::$swuuws_message[] = Lang::lang('Only part of the file was uploaded');
                break;
            case 4:
            case UPLOAD_ERR_NO_FILE:
                self::$swuuws_message[] = Lang::lang('No file was uploaded');
                break;
            case 6:
            case UPLOAD_ERR_NO_TMP_DIR:
                self::$swuuws_message[] = Lang::lang('Cannot find temporary folder');
                break;
            case 7:
            case UPLOAD_ERR_CANT_WRITE:
                self::$swuuws_message[] = Lang::lang('File write failed');
                break;
            case 8:
            case UPLOAD_ERR_EXTENSION:
                self::$swuuws_message[] = Lang::lang('A PHP extension stopped the file upload');
                break;
            default:
                $error = 9;
                self::$swuuws_message[] = Lang::lang('Unknown upload error');
                break;
        }
        self::$swuuws_error[] = $error;
    }
    public static function checkExt()
    {
        if(!self::$swuuws_ignore){
            $args = func_get_args();
            if(count($args) > 0){
                $extArr = self::convert($args);
                if(is_array(self::$swuuws_file['name'])){
                    foreach(self::$swuuws_file['name'] as $key => $val){
                        self::ext($val, $extArr);
                    }
                }
                else{
                    self::ext(self::$swuuws_file['name'], $extArr);
                }
            }
        }
        return self::instance();
    }
    private static function ext($filePath, &$extArr)
    {
        $pathArr = pathinfo($filePath);
        if(!in_array($pathArr['extension'], $extArr)){
            self::$swuuws_error[] = 10;
            self::$swuuws_message[] = Lang::lang('Extension is not allowed') . ': ' . $pathArr['basename'];
            self::$swuuws_block = true;
        }
    }
    public static function checkType()
    {
        if(!self::$swuuws_ignore)
        {
            $args = func_get_args();
            if(count($args) > 0){
                $extArr = self::convert($args);
                if(is_array(self::$swuuws_file['type'])){
                    foreach(self::$swuuws_file['type'] as $key => $val){
                        self::type($val, $extArr, self::$swuuws_file['name'][$key]);
                    }
                }
                else{
                    self::type(self::$swuuws_file['type'], $extArr, self::$swuuws_file['name']);
                }
            }
        }
        return self::instance();
    }
    private static function convert($in)
    {
        $result = [];
        foreach($in as $val){
            if(is_array($val)){
                $result = array_merge($result, $val);
            }
            else{
                if(strpos($val, ',')){
                    $tmpArr = explode(',', $val);
                    $result = array_merge($result, $tmpArr);
                }
                else{
                    $result[] = $val;
                }
            }
        }
        $result = array_map(function($itm){
            return strtolower(trim($itm));
        }, $result);
        $result = array_filter($result);
        return $result;
    }
    private static function type($fileType, &$extArr, $filePath)
    {
        $fileType = strtolower(trim($fileType));
        $basename = pathinfo($filePath, PATHINFO_BASENAME);
        if(!in_array($fileType, $extArr)){
            self::$swuuws_error[] = 11;
            self::$swuuws_message[] = Lang::lang('File type is not allowed') . ': ' . $basename;
            self::$swuuws_block = true;
        }
    }
    public static function maxSize($maxSize = 0)
    {
        if(!self::$swuuws_ignore){
            if($maxSize !== 0){
                $size = str_replace(' ', '', trim($maxSize));
                $unit = '';
                $last = substr($size, -1);
                while(!is_numeric($last)){
                    $unit = $last . $unit;
                    $size = substr($size, 0, -1);
                    $last = substr($size, -1);
                }
                if($unit != ''){
                    $unit = strtolower($unit);
                    switch($unit){
                        case 'k':
                        case 'kb':
                            $size *= 1024;
                            break;
                        case 'm':
                        case 'mb':
                            $size *= 1024 * 1024;
                            break;
                        case 'g':
                        case 'gb':
                            $size *= 1024 * 1024 * 1024;
                            break;
                        default:
                            $size *= 1;
                            break;
                    }
                }
                if(is_array(self::$swuuws_file['size'])){
                    foreach(self::$swuuws_file['size'] as $key => $val){
                        self::size($val, $size, self::$swuuws_file['name'][$key], $maxSize);
                    }
                }
                else{
                    self::size(self::$swuuws_file['size'], $size, self::$swuuws_file['name'], $maxSize);
                }
            }
        }
        return self::instance();
    }
    private static function size($fileSize, $maxSize, $filePath, $limitSize)
    {
        $basename = pathinfo($filePath, PATHINFO_BASENAME);
        if($fileSize > $maxSize){
            self::$swuuws_error[] = 12;
            self::$swuuws_message[] = Lang::lang('The file size exceeds the maximum allowed') . '(' . $limitSize . '): ' . $basename;
            self::$swuuws_block = true;
        }
    }
    public static function path($path = '')
    {
        if(!self::$swuuws_ignore){
            if($path != ''){
                self::setPath($path);
            }
        }
        return self::instance();
    }
    public static function ifSaved($raw = false, $folder = 'day')
    {
        if(!self::$swuuws_ignore && !self::$swuuws_block){
            self::setSavePath($folder);
            if(is_array(self::$swuuws_file['name'])){
                foreach(self::$swuuws_file['name'] as $key => $val){
                    self::saveFile($val, $raw, self::$swuuws_file['tmp_name'][$key]);
                }
            }
            else{
                self::saveFile(self::$swuuws_file['name'], $raw, self::$swuuws_file['tmp_name']);
            }
        }
        return self::instance();
    }
    public static function save($raw = false, $folder = 'day')
    {
        if(!self::$swuuws_ignore && !self::$swuuws_block){
            self::setSavePath($folder);
            $result = true;
            if(is_array(self::$swuuws_file['name'])){
                foreach(self::$swuuws_file['name'] as $key => $val){
                    $re = self::saveFile($val, $raw, self::$swuuws_file['tmp_name'][$key]);
                    if(!$re){
                        $result = $re;
                    }
                }
            }
            else{
                $result = self::saveFile(self::$swuuws_file['name'], $raw, self::$swuuws_file['tmp_name']);
            }
            return $result;
        }
        return false;
    }
    private static function setSavePath($folder)
    {
        if(self::$swuuws_path == ''){
            self::setPath(Env::get('DATA_PATH'));
        }
        switch($folder){
            case 'day':
                self::$swuuws_path .= DS . Date::yearMonthDay();
                break;
            case 'month':
                self::$swuuws_path .= DS . Date::yearMonth();
                break;
        }
        File::newFolder(self::$swuuws_path);
    }
    private static function setPath($path)
    {
        $path = str_replace(['\\', '/'], DS, trim($path));
        self::$swuuws_path = trim($path, DS);
        File::newFolder($path);
    }
    private static function saveFile($fileName, $raw, $tmp_name)
    {
        $fileName = basename($fileName);
        if(!$raw){
            $extName = pathinfo($fileName, PATHINFO_EXTENSION);
            $fileName = md5($tmp_name . $fileName . uniqid()) . '.' . $extName;
        }
        $upload = self::$swuuws_path . DS . $fileName;
        if(move_uploaded_file($tmp_name, ROOT . $upload)){
            self::$swuuws_uploaded[] = str_replace(DS, '/', $upload);
            return true;
        }
        return false;
    }
    public static function uploaded()
    {
        return self::$swuuws_uploaded;
    }
    public static function uploadedSingle()
    {
        if(isset(self::$swuuws_uploaded[0])){
            return self::$swuuws_uploaded[0];
        }
        else{
            return '';
        }
    }
    public static function getUploaded()
    {
        return implode(',', self::$swuuws_uploaded);
    }
    public static function message()
    {
        return self::$swuuws_message;
    }
    public static function firstMessage()
    {
        if(isset(self::$swuuws_message[0])){
            return self::$swuuws_message[0];
        }
        else{
            return '';
        }
    }
}