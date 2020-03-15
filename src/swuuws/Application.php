<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws;

class Application
{
    /**
     * Start up.
     *
     * @param  none
     */
    public static function launch()
    {
        Error::handler();
        Env::init();
        Route::handler();
    }
}