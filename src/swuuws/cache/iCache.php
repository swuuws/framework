<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws\cache;

interface iCache
{
    public static function set($name, $value, $expire);
    public static function group($group, $name);
    public static function get($name);
    public static function has($name);
    public static function delete($name);
    public static function delGroup($name);
    public static function delAll();
}