<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws;

class Error
{
    private static $error = [];
    private static $line = 15;
    public static function handler()
    {
        error_reporting(E_ALL);
        set_error_handler([__CLASS__, 'swuuwsError']);
        set_exception_handler([__CLASS__, 'swuuwsException']);
        register_shutdown_function([__CLASS__, 'swuuwsShutdown']);
    }
    public static function swuuwsError($errno, $errstr, $errfile, $errline)
    {
        self::$error['prompt'] = ['Error', $errstr, $errno, $errfile, $errline];
        exit();
    }
    public static function swuuwsException($exception)
    {
        self::$error['prompt'] = ['Exception', $exception->getMessage(), $exception->getCode(), $exception->getFile(), $exception->getLine()];
        exit();
    }
    public static function swuuwsShutdown()
    {
        $logLevel = strtolower(Env::get('LOG_LEVEL'));
        if(isset(self::$error['prompt'])){
            $debug = (bool)Env::get('APP_DEBUG');
            $dir = File::parentDirectory(__DIR__, 1);
            Lang::load($dir . DS . 'lang' . DS . Lang::getAuto() . '.php', true);
            self::$error['prompt'][0] = Lang::lang(self::$error['prompt'][0]);
            $colon = strpos(self::$error['prompt'][1], ':');
            if($colon !== false){
                self::$error['prompt'][1] = Lang::lang(substr(self::$error['prompt'][1], 0, $colon)) . substr(self::$error['prompt'][1], $colon);
            }
            else{
                self::$error['prompt'][1] = Lang::lang(self::$error['prompt'][1]);
            }
            if($debug){
                $template = file_get_contents($dir . DS . 'template' . DS . 'error_details.html');
                $template = str_replace(['{{prompt}}', '{{message}}', '{{code}}', '{{file}}', '{{line}}'], self::$error['prompt'], $template);
                $template = str_replace('{{errorCode}}', self::errorFile(), $template);
            }
            else{
                $report = (bool)Env::get('ERROR_REPORT');
                if($report){
                    $template = file_get_contents($dir . DS . 'template' . DS . 'error_message.html');
                    $template = str_replace(['{{prompt}}', '{{message}}'], [self::$error['prompt'][0], self::$error['prompt'][1]], $template);
                }
                else{
                    $template = file_get_contents($dir . DS . 'template' . DS . 'error.html');
                    $template = str_replace(['{{Error}}', '{{Page error}}'], [Lang::lang('Error'), Lang::lang('Page error')], $template);
                }
            }
            Response::clearDump($template);
            if($logLevel == 'all' || $logLevel == 'error'){
                $logFile = 'runtime' . DS . 'log' . DS . Date::yearMonth();
                $logMessage = '[' . Date::now() . '] ' . self::$error['prompt'][0] . ': ' . self::$error['prompt'][1] . "\n" . self::$error['prompt'][3] . ' on line: ' . self::$error['prompt'][4] . "\nUrl: " . Request::fullUrl() . ' (' . Request::ip() . ")\n\n";
                File::newFolder($logFile);
                error_log($logMessage, 3, ROOT . $logFile . DS . Date::day() . '.log');
            }
        }
        elseif($logLevel == 'all'){
            $logMessage = '[' . Date::now() . '] Url: ' . Request::fullUrl() . ' (' . Request::ip() . ") OK\n\n";
            $logFile = 'runtime' . DS . 'log' . DS . Date::yearMonth();
            File::newFolder($logFile);
            error_log($logMessage, 3, ROOT . $logFile . DS . Date::day() . '.log');
        }
        else{
            if(Response::needOutput()){
                Response::dump();
            }
        }
    }
    private static function errorFile()
    {
        $file = file(self::$error['prompt'][3]);
        $line = self::$error['prompt'][4] - 1;
        $lower = $line - self::$line;
        if($lower < 0){
            $lower = 0;
        }
        $cap = $line + self::$line;
        $result = '';
        foreach($file as $key => $val){
            if($key > $lower && $key < $cap){
                if($line == $key){
                    $result .= '<div style="padding: 3px 8px;color: brown;background-color: beige"><span style="margin-right: 2px;width: 60px;display: inline-block">' . ($key + 1) . '. </span><span>' . str_replace(' ', '&nbsp;', $val) . '</span></div>';
                }
                else{
                    $result .= '<div style="padding: 3px 8px;"><span style="margin-right: 2px;color: #999;width: 60px;display: inline-block">' . ($key + 1) . '. </span><span style="color: #666">' . str_replace(' ', '&nbsp;', $val) . '</span></div>';
                }
            }
        }
        return $result;
    }
}