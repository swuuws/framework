<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws;

use swuuws\exception\ViewException;

class View
{
    private static $view = [];
    private static $instance;
    private static $way = 'cover';
    private static $viewPath = '';
    private static $loop = [];
    private static $require = '';
    private static function instance()
    {
        if(empty(self::$instance)){
            self::$instance = new View();
        }
        return self::$instance;
    }
    public static function assign($name, $value = '')
    {
        if(is_array($name)){
            foreach($name as $key => $val){
                if(self::$way == 'append' && isset(self::$view[$key])){
                    self::$view[$key] .= $val;
                }
                else{
                    self::$view[$key] = $val;
                }
            }
        }
        else{
            if(self::$way == 'append' && isset(self::$view[$name])){
                self::$view[$name] .= $value;
            }
            else{
                self::$view[$name] = $value;
            }
        }
        return self::instance();
    }
    public static function cover()
    {
        self::$way = 'cover';
        return self::instance();
    }
    public static function append()
    {
        self::$way = 'append';
        return self::instance();
    }
    public static function viewPath($path)
    {
        self::$viewPath = rtrim(str_replace(['/', '\\'], DS, $path), DS);
        return self::instance();
    }
    public static function view($name = '')
    {
        $template = self::load($name);
        $path = ROOT . DS . 'runtime' . DS . 'solve';
        File::newFolder($path, true);
        $tempMd5 = $path . DS . md5($template) . '.php';
        if(!is_file($tempMd5)){
            $html = self::parseView($template);
            file_put_contents($tempMd5, $html);
        }
        $func = Load::loadSingleFile($tempMd5, true);
        $result = call_user_func($func, self::$view);
        return $result;
    }
    private static function getPath($name)
    {
        if(empty($name) && !isset($_ENV['SWUUWS_VIEW'])){
            throw new ViewException();
        }
        if(!empty($name)){
            $name = rtrim(str_replace(['/', '\\'], DS, $name), DS);
            if(stripos($name, APP) === false){
                $name = ltrim($name, DS);
                if(empty(self::$viewPath)){
                    if(strtolower(substr($name, 0, 5)) !== 'view' . DS){
                        $name = 'view' . DS . $name;
                    }
                    $name = APP . $name;
                }
                else{
                    $name = self::$viewPath . DS . $name;
                }
            }
        }
        else{
            $name = APP .'view' . DS . str_replace('/', DS, $_ENV['SWUUWS_VIEW']);
        }
        $name = self::chkSuffix($name);
        return $name;
    }
    private static function load($name)
    {
        $path = self::getPath($name);
        $dir = dirname($path);
        $template = file_get_contents($path);
        while(preg_match('/{\s*include\s+([A-Za-z0-9_\.\/\\\]+)\s*}/', $template, $matches) > 0){
            $tfile = $dir . DS . trim(str_replace(['\\', '/'], DS, $matches[1]), DS);
            $tfile = self::chkSuffix($tfile);
            $subfile = file_get_contents($tfile);
            $template = str_replace($matches[0], $subfile, $template);
        }
        return $template;
    }
    private static function chkSuffix($file)
    {
        $suffix = '.' . trim(Env::get('TEMPLATE_SUFFIX'), '.');
        $suffixLen = strlen($suffix);
        if(substr($file, - $suffixLen) != $suffix){
            $file .= $suffix;
        }
        return $file;
    }
    private static function parseView($template)
    {
        $front = Env::get('FRONT_BORDER');
        $back = Env::get('BACK_BORDER');
        $backLen = strlen($back);
        $tempArr = explode($front, $template);
        $midArr[] = ['type' => 'str', 'string' => array_shift($tempArr)];
        while(count($tempArr) > 0){
            $tmp = array_shift($tempArr);
            $backPos = strrpos($tmp, $back);
            if($backPos === false){
                while($backPos === false){
                    $tmp .= $front . array_shift($tempArr);
                    $backPos = strrpos($tmp, $back);
                    $wleftStr = substr($tmp, 0, $backPos);
                    $wleftStr = str_replace(['\\\'', '\\"'], '', $wleftStr);
                    $wleftStr = preg_replace('/".*?"/', '', $wleftStr);
                    $wleftStr = preg_replace('/\'.*?\'/', '', $wleftStr);
                    $wleftStr = str_replace('\'', '"', $wleftStr);
                    if(false !== $rindex = strpos($wleftStr, ')')){
                        $subrStr = substr($wleftStr, 0, $rindex);
                        if(substr_count($subrStr, '"') % 2 != 0){
                            $lave = substr($wleftStr, $rindex + 1);
                            if(false !== $lindex = strpos($lave, '"')){
                                $lave = substr($lave, $lindex);
                            }
                            else{
                                $lave = '';
                            }
                            $wleftStr = $subrStr . $lave;
                        }
                    }
                    if((substr_count($wleftStr,'(') != substr_count($wleftStr,')'))){
                        $backPos = false;
                    }
                }
            }
            $leftStr = substr($tmp, 0, $backPos);
            $ltmpStr = str_replace(['\\\'', '\\"'], '', $leftStr);
            $ltmpStr = str_replace('\'', '"', $ltmpStr);
            $ltmpStr = preg_replace('/".*?"/', '', $ltmpStr);
            if(strpos($leftStr, PHP_EOL) !== false || strpos($ltmpStr, ':') !== false || strpos($ltmpStr, ';') !== false){
                $midArr[] = ['type' => 'str', 'string' => $front . $tmp];
            }
            else{
                $midArr[] = ['type' => 'exp', 'string' => $leftStr];
                $rightStr = substr($tmp, $backPos + $backLen);
                if($rightStr === false){
                    $rightStr = '';
                }
                $midArr[] = ['type' => 'str', 'string' => $rightStr];
            }
        }
        $php = '';
        $php .= self::line('return function($swuuws){');
        $php .= self::line('$reStr = \'\';');
        foreach($midArr as $val){
            if($val['type'] == 'str'){
                $php .= self::line('$reStr .= \'' . str_replace('\'', '\\\'', $val['string']) . '\';');
            }
            elseif($val['type'] == 'exp'){
                $tmpStr = trim($val['string']);
                if(substr($tmpStr, 0, 3) == 'if '){
                    $statement = trim(substr($tmpStr, 3));
                    $php .= self::line('if(' . self::parseIf($statement) . '){');
                }
                elseif(substr($tmpStr, 0, 7) == 'elseif '){
                    $statement = trim(substr($tmpStr, 7));
                    $php .= self::line('} elseif(' . self::parseIf($statement) . '){');
                }
                elseif(substr($tmpStr, 0, 6) == 'exist '){
                    $statement = trim(substr($tmpStr, 6));
                    $php .= self::line(self::parseExist($statement));
                }
                elseif(substr($tmpStr, 0, 5) == 'loop '){
                    $statement = trim(substr($tmpStr, 5));
                    $php .= self::line(self::parseLoop($statement));
                }
                elseif($tmpStr == 'else'){
                    $php .= self::line('}else{');
                }
                elseif(in_array($tmpStr, ['endexist', 'endif'])){
                    $php .= self::line('}');
                }
                elseif($tmpStr == 'endloop'){
                    array_pop(self::$loop);
                    $php .= self::line('}');
                }
                elseif(self::judge($tmpStr, '|')){
                    $php .= self::line('$reStr .= ' . self::parseFilter($tmpStr) . ';');
                }
                elseif(self::judge($tmpStr, '=')){
                    if(substr($tmpStr, 0, 1) == '='){
                        $tmpStr = trim(substr($tmpStr, 1));
                        $php .= self::line('$reStr .= ' . self::parseSwuuws($tmpStr) . ';');
                    }
                    else{
                        $php .= self::line(self::parseAssign($tmpStr) . ';');
                    }
                }
                else{
                    $keyArr = self::toOne();
                    $testStr = explode('.', $tmpStr)[0];
                    if(in_array($testStr, $keyArr)){
                        $php .= self::line('$reStr .= $' . self::swuuws($tmpStr, true) . ';');
                    }
                    else{
                        $php .= self::line('$reStr .= ' . self::swuuws($tmpStr) . ';');
                    }
                }
            }
        }
        $php .= self::line('return $reStr;');
        $php .= self::line('};');
        if(!empty(self::$require)){
            $php = self::$require . $php;
        }
        $php = self::line('<?php') . $php;
        return $php;
    }
    private static function line($string)
    {
        return $string . PHP_EOL;
    }
    private static function judge($string, $symbol)
    {
        $string = str_replace('\'', '"', $string);
        $string = str_replace('\\"', '', $string);
        $string = preg_replace('/".*?"/', '', $string);
        if(strpos($string, $symbol) !== false){
            return true;
        }
        return false;
    }
    private static function findQuot($statement, $pos = 0)
    {
        $statLen = strlen($statement);
        $apost = strpos($statement, '\'', $pos);
        if($apost === false){
            $apost = $statLen;
        }
        $double = strpos($statement, '"', $pos);
        if($double === false){
            $double = $statLen;
        }
        if($apost == $double){
            return false;
        }
        else{
            if($apost < $double){
                return ['\'', $apost];
            }
            else{
                return ['"', $double];
            }
        }
    }
    private static function funcName($fun)
    {
        switch($fun){
            case 'url':
                $string = '\swuuws\Url::' . $fun;
                break;
            case 'lang':
                $string = '\swuuws\Lang::' . $fun;
                break;
            case 'run':
            case 'substring':
                $string = '\swuuws\Template::' . $fun;
                break;
            case 'captcha':
                $string = '\swuuws\Captcha::' . $fun;
                break;
            case 'captchaUrl':
                $string = '\swuuws\Captcha::' . $fun;
                break;
            default:
                if(empty(self::$require)){
                    self::$require = self::line('require_once APP .\'swuuws\' . DS . \'functions.php\';');
                }
                $string = $fun;
                break;
        }
        return $string;
    }
    private static function parseSwuuws($statement)
    {
        $pos = strpos($statement, '(');
        $fun = trim(substr($statement, 0, $pos));
        $param = trim(substr($statement, $pos));
        $param = substr($param, 1, strlen($param) - 2);
        $string = self::funcName($fun) . '(';
        $param = self::parseParam($param);
        $string .= $param . ')';
        return $string;
    }
    private static function parseAssign($statement)
    {
        $pos = strpos($statement, '=');
        $left = self::swuuws(trim(substr($statement, 0, $pos)));
        $right = trim(substr($statement, $pos + 1));
        if(preg_match('/^(\w+)\s*\((.*)\)$/', $right, $matches)){
            $string = $left . ' = ' . self::funcName($matches[1]) . '(' . self::parseParam($matches[2]) . ')';
        }
        else{
            $states = [];
            $stateArr = self::separate($right);
            foreach($stateArr as $val){
                if($val['type'] == 'exp'){
                    $states[] = self::analyze($val['string']);
                }
                else{
                    $states[] = $val['string'];
                }
            }
            $right = implode(' ', $states);
            $string = $left . ' = ' . $right;
        }
        return $string;
    }
    private static function parseFilter($statement)
    {
        $statement = trim($statement);
        $pos = strpos($statement, '|');
        $param = trim(substr($statement, 0, $pos));
        $func = trim(substr($statement, $pos + 1));
        $leftIndex = strpos($func, '(');
        $funName = trim(substr($func, 0, $leftIndex));
        $others = trim(substr($func, $leftIndex + 1, strlen($func) - $leftIndex - 2));
        $othersParam = self::parseParam($others);
        $string = self::funcName($funName) . '(' . self::swuuws($param) . ', ' . $othersParam . ')';
        return $string;
    }
    private static function parseParam($statement)
    {
        $result = '';
        $param = [];
        $stateArr = explode(',', $statement);
        $tmpStr = '';
        foreach($stateArr as $val){
            $tmpStr .= $val;
            $tmp = str_replace(['\\\'', '\\"'], '', $tmpStr);
            $tmp = str_replace('\'', '"', $tmp);
            $tmp = preg_replace('/".*?"/', '', $tmp);
            if(strpos($tmp, '"') !== false){
                $tmpStr .= ',';
                continue;
            }
            else{
                $param[] = trim($tmpStr);
                $tmpStr = '';
            }
        }
        unset($stateArr);
        $append = '[';
        foreach($param as $val){
            $first = substr($val, 0, 1);
            $last = substr($val, -1);
            if(($first == '"' && $last == '"') || ($first == '\'' && $last == '\'')){
                $result .= ',' . $val;
            }
            else{
                if(false !== $eqindex = strpos($val, '=')){
                    $ekey = trim(substr($val, 0, $eqindex));
                    $eval = trim(substr($val, $eqindex + 1));
                    $append .= '\'' . $ekey . '\' => ' . $eval . ',';
                }
                else{
                    $result .= ',' . $val;
                }
            }
        }
        if($append != '['){
            $result .= ',' . rtrim($append, ',') . ']';
        }
        return ltrim($result, ',');
    }
    private static function parseLoop($statement)
    {
        $loop = [];
        $statement = trim($statement);
        $stateArr = explode('=', $statement);
        $key = array_shift($stateArr);
        foreach($stateArr as $val){
            $val = trim($val);
            $rpos = strrpos($val, ' ');
            if($rpos !== false){
                $lval = substr($val, 0, $rpos);
                $loop[trim($key)] = trim(trim(trim(trim($lval), '"'), '\''));
                $key = substr($val, $rpos);
            }
            else{
                $loop[trim($key)] = trim(trim(trim(trim($val), '"'), '\''));
            }
        }
        self::$loop[] = [$loop['item'], $loop['key']];
        $string = '';
        if(isset($loop['offset']) && !empty($loop['offset'])){
            $string .= self::line('$' . $loop['item'] . '_offset = ' . $loop['offset'] . ';');
        }
        else{
            $string .= self::line('$' . $loop['item'] . '_offset = -1;');
        }
        if(isset($loop['len']) && !empty($loop['len'])){
            $string .= self::line('$' . $loop['item'] . '_len = ' . $loop['len'] . ';');
        }
        else{
            $string .= self::line('$' . $loop['item'] . '_len = -1;');
        }
        $string .= self::line('foreach(' . self::swuuws($loop['name']) . ' as $' . $loop['key'] . ' => $' . $loop['item'] . '){');
        $string .= self::line('if($' . $loop['item'] . '_offset > 0){');
        $string .= self::line('$' . $loop['item'] . '_offset -- ;');
        $string .= self::line('continue;');
        $string .= self::line('} else {');
        $string .= self::line('if($' . $loop['item'] . '_len > 0){');
        $string .= self::line('$' . $loop['item'] . '_len -- ;');
        $string .= self::line('} elseif($' . $loop['item'] . '_len == 0) {');
        $string .= self::line('break;');
        $string .= self::line('}');
        $string .= self::line('}');
        return $string;
    }
    private static function swuuws($string, $inloop = false)
    {
        $tmpArr = explode('.', $string);
        if($inloop){
            $tmp = array_shift($tmpArr);
        }
        else{
            $tmp = '$swuuws';
        }
        foreach($tmpArr as $tval){
            $tmp .= '[\'' . $tval . '\']';
        }
        return $tmp;
    }
    private static function parseExist($statement)
    {
        $statement = trim($statement);
        $isNot = false;
        $space = strpos($statement, ' ');
        if($space !== false){
            $not = substr($statement, 0, $space);
            if(strtolower(trim($not)) == 'not'){
                $isNot = true;
                $statement = trim(substr($statement, $space));
            }
        }
        $stateArr = explode('.', $statement);
        $tmp = '$swuuws';
        foreach($stateArr as $tval){
            $tmp .= '[\'' . $tval . '\']';
        }
        if($isNot){
            $result = 'if(!isset(' . $tmp . ')){';
        }
        else{
            $result = 'if(isset(' . $tmp . ')){';
        }
        return $result;
    }
    private static function parseIf($statement)
    {
        $states = [];
        $stateArr = self::separate($statement);
        foreach($stateArr as $val){
            if($val['type'] == 'exp'){
                $states[] = self::analyze($val['string']);
            }
            else{
                $states[] = $val['string'];
            }
        }
        $string = implode(' ', $states);
        return $string;
    }
    private static function separate($statement)
    {
        $pos = 0;
        $statLen = strlen($statement);
        $stateArr = [];
        while(true){
            $findArr = self::findQuot($statement, $pos);
            if($findArr === false){
                $stateArr[] = ['type' => 'exp', 'string' => substr($statement, $pos)];
                break;
            }
            else{
                $stateArr[] = ['type' => 'exp', 'string' => substr($statement, $pos, $findArr[1] - $pos)];
                $quot = $findArr[0];
                $start = $findArr[1];
                $find = $start;
                $end = false;
                while(true){
                    $end = strpos($statement, $quot, $find + 1);
                    if($end !== false){
                        if(substr($statement, $end - 1, 1) == '\\'){
                            $find = $end;
                            continue;
                        }
                        else{
                            break;
                        }
                    }
                    else{
                        break;
                    }
                }
                if($end !== false){
                    $stateArr[] = ['type' => 'str', 'string' => substr($statement, $start, $end - $start + 1)];
                    $pos = $end + 1;
                    if($pos >= $statLen){
                        break;
                    }
                    else{
                        continue;
                    }
                }
                else{
                    $stateArr[] = ['type' => 'str', 'string' => substr($statement, $start) . $quot];
                    break;
                }
            }
        }
        return $stateArr;
    }
    private static function analyze($statement)
    {
        $statement = preg_replace('/(==|>=|<=|!=|\+|\-|\*|\/|%)/', ' $1 ', $statement);
        $statement = preg_replace('/(>(?!=)|<(?!=))/', ' $1 ', $statement);
        $statement = str_replace(['(', ')'], [' ( ', ' ) '], $statement);
        $statement = preg_replace('/( )+/', ' ', $statement);
        $statement = trim($statement);
        $stateArr = explode(' ', $statement);
        $keyArr = self::toOne();
        foreach($stateArr as $key => $val){
            if(!in_array($val, ['==', '>=', '<=', '!=', '+', '-', '*', '/', '%', '>', '<', '(', ')']) && !is_numeric($val) && !empty($val)){
                if($val == 'not'){
                    $stateArr[$key] = '!';
                }
                elseif($val == 'and'){
                    $stateArr[$key] = '&&';
                }
                elseif($val == 'or'){
                    $stateArr[$key] = '||';
                }
                elseif(strtolower($val) == 'true'){
                    $stateArr[$key] = 'true';
                }
                elseif(strtolower($val) == 'false'){
                    $stateArr[$key] = 'false';
                }
                else{
                    $testStr = explode('.', $val)[0];
                    if(in_array($testStr, $keyArr)){
                        $stateArr[$key] = '$' . self::swuuws($val, true);
                    }
                    else{
                        $stateArr[$key] = self::swuuws($val);
                    }
                }
            }
        }
        $string = implode(' ', $stateArr);
        return $string;
    }
    private static function toOne()
    {
        $reArr = [];
        if(count(self::$loop) > 0){
            foreach(self::$loop as $val){
                foreach($val as $sval){
                    $reArr[] = $sval;
                }
            }
        }
        return $reArr;
    }
}