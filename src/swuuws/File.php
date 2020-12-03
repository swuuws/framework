<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws;

use swuuws\files\Upload;

class File
{
    /**
     * New folder.
     *
     * @param  $folder, $fullPath
     * @return boolean
     */
    public static function newFolder($folder, $fullPath = false)
    {
        $folder = str_replace(['\\', '/'], DS, $folder);
        if(!$fullPath){
            $folder = ROOT . ltrim($folder, DS);
        }
        if(!is_dir($folder)){
            return mkdir($folder, 0777, true);
        }
        return true;
    }
    /**
     * Get parent directory.
     *
     * @param  $path, $series
     * @return string
     */
    public static function parentDirectory($path, $series = 1)
    {
        for($i = 0; $i < $series; $i++){
            $path = dirname($path);
        }
        return $path;
    }
    /**
     * Delete the contents of the folder.
     *
     * @param  $path
     */
    public static function clearFolder($path)
    {
        $dir = dir($path);
        while(false !== ($entry = $dir->read())){
            if($entry!='.' && $entry!='..'){
                $entry = $path . DS . $entry;
                if(is_dir($entry)){
                    self::clearFolder($entry);
                    @rmdir($entry);
                }
                else{
                    @unlink($entry);
                }
            }
        }
        $dir->close();
    }
    public static function deleteFolder($path)
    {
        $dir = dir($path);
        while(false !== ($entry = $dir->read())){
            if($entry!='.' && $entry!='..'){
                $entry = $path . DS . $entry;
                if(is_file($entry)){
                    @unlink($entry);
                }
                else{
                    self::deleteFolder($entry);
                }
            }
        }
        $dir->close();
        @rmdir($path);
    }
    public static function get($name = 'file')
    {
        return Upload::get($name);
    }
    public static function uploadFile()
    {
        return Upload::getInstance();
    }
    public static function listFiles($folder, $ext = '', $fullPath = false)
    {
        $file = '*.*';
        $ext = trim($ext);
        if($ext != ''){
            $file = '*.' . trim($ext, '.');
        }
        $folder = str_replace(['\\', '/'], DS, $folder);
        if(!$fullPath){
            $folder = ROOT . ltrim($folder, DS);
        }
        $result = glob(rtrim($folder, DS) . DS . $file);
        $result = array_map(function($itm){
            return basename($itm);
        }, $result);
        return $result;
    }
    public static function listFolders($path, $fullPath = false)
    {
        $path = str_replace(['\\', '/'], DS, $path);
        if(!$fullPath){
            $path = ROOT . ltrim($path, DS);
        }
        $result = glob(rtrim($path, DS) . DS . '*', GLOB_ONLYDIR);
        $result = array_map(function($itm){
            return basename($itm);
        }, $result);
        return $result;
    }
}