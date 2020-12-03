<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws\cache;

use swuuws\File as sF;

class File implements iCache
{
    public static function set($name, $value, $expire = 3600)
    {
        $mdname = md5($name);
        $folder = substr($mdname, 0, 3);
        $file = md5($mdname);
        $path = ROOT . 'runtime' . DS . 'cache' . DS . $folder;
        sF::newFolder($path, true);
        $time = time() + $expire;
        $filePath = $path . DS . $file . '.php';
        file_put_contents($filePath, self::serialize($value));
        touch($filePath, $time);
        return true;
    }
    public static function group($group, $name)
    {
        $group = '_swuuws_cache_group_' . $group;
        $mdname = md5($group);
        $folder = substr($mdname, 0, 3);
        $file = md5($mdname);
        $path = ROOT . 'runtime' . DS . 'cache' . DS . $folder;
        sF::newFolder($path, true);
        $filePath = $path . DS . $file . '.php';
        $garr = [];
        if(is_file($filePath)){
            $gfile = file_get_contents($filePath);
            $garr = self::unserialize($gfile);
        }
        if(!in_array($name, $garr)){
            $garr[] = $name;
        }
        file_put_contents($filePath, self::serialize($garr));
        return true;
    }
    public static function get($name)
    {
        $mdname = md5($name);
        $folder = substr($mdname, 0, 3);
        $file = md5($mdname);
        $filePath = ROOT . 'runtime' . DS . 'cache' . DS . $folder . DS . $file . '.php';
        if(is_file($filePath)){
            $mtime = filemtime($filePath);
            if($mtime > time()){
                $content = file_get_contents($filePath);
                return self::unserialize($content);
            }
            else{
                @unlink($filePath);
                return false;
            }
        }
        else{
            return false;
        }
    }
    public static function has($name)
    {
        $mdname = md5($name);
        $folder = substr($mdname, 0, 3);
        $file = md5($mdname);
        $filePath = ROOT . 'runtime' . DS . 'cache' . DS . $folder . DS . $file . '.php';
        if(is_file($filePath)){
            $mtime = filemtime($filePath);
            if($mtime > time()){
                return true;
            }
            else{
                @unlink($filePath);
                return false;
            }
        }
        else{
            return false;
        }
    }
    public static function delete($name)
    {
        $mdname = md5($name);
        $folder = substr($mdname, 0, 3);
        $file = md5($mdname);
        $filePath = ROOT . 'runtime' . DS . 'cache' . DS . $folder . DS . $file . '.php';
        if(is_file($filePath)){
            @unlink($filePath);
        }
    }
    public static function delGroup($group)
    {
        $group = '_swuuws_cache_group_' . $group;
        $mdname = md5($group);
        $folder = substr($mdname, 0, 3);
        $file = md5($mdname);
        $filePath = ROOT . 'runtime' . DS . 'cache' . DS . $folder . DS . $file . '.php';
        if(is_file($filePath)){
            $gfile = file_get_contents($filePath);
            $garr = self::unserialize($gfile);
            foreach($garr as $val){
                self::delete($val);
            }
            @unlink($filePath);
        }
    }
    public static function delAll()
    {
        $path = ROOT . 'runtime' . DS . 'cache';
        sF::clearFolder($path);
    }
    private static function serialize($value)
    {
        return '<?php' . PHP_EOL . 'exit();//' . serialize($value);
    }
    private static function unserialize($value)
    {
        $pos = strpos($value, '//');
        $value = substr($value, $pos + 2);
        return unserialize($value);
    }
}