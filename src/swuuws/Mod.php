<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws;

use swuuws\exception\ModelException;

class Mod
{
    private $_swuuws_library;
    private $_swuuws_select;
    private $_swuuws_serial;
    private static $_swuuws_sub_query = [];
    private static $order = 0;
    private static $queue = 0;
    public function __construct()
    {
        $this->_swuuws_library = $this->getAttr(true);
        $this->clearAttr($this->_swuuws_library);
        self::$order ++;
        $this->_swuuws_serial = self::$order;
    }
    public function get()
    {
        $resultArr = $this->getStatement();
        return $this->runQuery($resultArr);
    }
    public function getSet($func = '')
    {
        $result = $this->get();
        $reArr = [];
        if($result){
            $reArr =  Db::getAll();
            if(empty($reArr)){
                $reArr = [];
            }
        }
        if($func != ''){
            $func($reArr);
            return null;
        }
        else{
            return $reArr;
        }
    }
    public function getOne($func = '')
    {
        $result = $this->get();
        $reArr = [];
        if($result){
            $reArr = Db::get();
            if(empty($reArr)){
                $reArr = [];
            }
        }
        if($func != ''){
            $func($reArr);
            return null;
        }
        else{
            return $reArr;
        }
    }
    public function paginate($perPage, $total = 0, $parameter = [], $maxShow = -1, $pnShow = null, $prev = '&laquo;', $next = '&raquo;')
    {
        $resultArr = $this->getStatement();
        $total = intval($total);
        if(empty($total) || $total <= 0){
            $total = $this->getCount($resultArr);
            if(empty($total)){
                $total = 0;
            }
        }
        $query = '';
        if(is_array($parameter) && count($parameter) > 0){
            foreach($parameter as $key => $val){
                $query .= '&' . $key . '=' . $val;
            }
        }
        $perPage = intval($perPage);
        $currentPage = 1;
        if(Request::hasGet('page')){
            $currentPage = intval(Request::getGet('page'));
            if($currentPage < 1){
                $currentPage = 1;
            }
        }
        $offset = $perPage * ($currentPage - 1);
        if(substr($resultArr[0], 0, 1) == '('){
            $resultArr[0] = 'SELECT * FROM (' . $resultArr[0] . ') AS count_swuuws LIMIT ' . $perPage . ' OFFSET ' . $offset;
        }
        else{
            $resultArr[0] = $resultArr[0] . ' LIMIT ' . $perPage . ' OFFSET ' . $offset;
        }
        $this->runQuery($resultArr);
        $totalPage = ceil($total / $perPage);
        $pages = Paginate::pages($currentPage, $totalPage, $query, $maxShow, $pnShow, $prev, $next);
        $result = [
            'total' => $total,
            'currentPage' => $currentPage,
            'perPage' => $perPage,
            'totalPage' => $totalPage,
            'query' => $query,
            'item' => Model::getAll(),
            'pages' => $pages[1],
            'pagesInner' => $pages[0]
        ];
        return $result;
    }
    public function max($field)
    {
        $resultArr = $this->getStatement();
        return $this->getAgg($resultArr, $field, 'max');
    }
    public function min($field)
    {
        $resultArr = $this->getStatement();
        return $this->getAgg($resultArr, $field, 'min');
    }
    public function avg($field)
    {
        $resultArr = $this->getStatement();
        return $this->getAgg($resultArr, $field, 'avg');
    }
    public function sum($field)
    {
        $resultArr = $this->getStatement();
        return $this->getAgg($resultArr, $field, 'sum');
    }
    private function getAgg($resultArr, $field, $op)
    {
        $upop = strtoupper($op);
        $lwop = strtolower($op);
        if(substr($resultArr[0], 0, 1) == '('){
            $resultArr[0] = 'SELECT ' . $upop . '(' . $lwop . '_swuuws_tb.' . $field . ') as ' . $lwop . '_swuuws FROM (' . $resultArr[0] . ') AS ' . $lwop . '_swuuws_tb';
        }
        else{
            $findex = stripos($resultArr[0], ' FROM ');
            $resultArr[0] = 'SELECT ' . $upop . '(' . $field . ') as ' . $lwop . '_swuuws' . substr($resultArr[0], $findex);
        }
        $this->runQuery($resultArr);
        $row = Model::getRow();
        return $row[$lwop . '_swuuws'];
    }
    public function count($field = '*')
    {
        $resultArr = $this->getStatement();
        return $this->getCount($resultArr, $field);
    }
    private function getCount($resultArr, $field = '*')
    {
        $tmpArr = $this->wToArr($resultArr[0]);
        $tmpArr = array_map(function($key, $val){
            if($key % 2 == 0){
                return preg_replace(['/( LIMIT \d+)/i', '/( OFFSET \d+)/i'], '', $val);
            }
            else{
                return $val;
            }
        }, array_keys($tmpArr), $tmpArr);
        $statement = implode('', $tmpArr);
        if(substr($resultArr[0], 0, 1) == '('){
            $resultArr[0] = 'SELECT COUNT(' . $field . ') as total FROM (' . $statement . ') AS count_swuuws';
        }
        else{
            $findex = stripos($statement, ' FROM ');
            $resultArr[0] = 'SELECT COUNT(' . $field . ') as total' . substr($statement, $findex);
        }
        $this->runQuery($resultArr);
        $row = Model::getRow();
        return $row['total'];
    }
    private function runQuery(&$resultArr)
    {
        $mode = 'name';
        if(Env::has('GET_RECORD_MODE')){
            $emode = strtolower(trim(Env::get('GET_RECORD_MODE')));
            if(in_array($emode, ['name', 'number', 'both'])){
                $mode = $emode;
            }
        }
        if(count($resultArr[1]) > 0){
            $result = Db::query($resultArr[0], $resultArr[1], $mode);
        }
        else{
            $result = Db::query($resultArr[0], [], $mode);
        }
        return $result;
    }
    private function getStatement()
    {
        if(count($this->_swuuws_select) > 1){
            $resultArr = $this->multi();
        }
        else{
            $keyArr = array_keys($this->_swuuws_select);
            $tableKey = $keyArr[0];
            $resultArr = $this->single($this->_swuuws_select[$tableKey]);
        }
        if(count(self::$_swuuws_sub_query) > 0){
            foreach(self::$_swuuws_sub_query as $skey => $sval){
                $submark = '::' . $skey;
                if(false !== $sindex = strpos($resultArr[0], $submark)){
                    $subleft = substr($resultArr[0], 0, $sindex);
                    $subright = substr($resultArr[0], $sindex + strlen($submark));
                    $tmpArr = $this->wToArr($subleft);
                    $qnumber = 0;
                    foreach($tmpArr as $tkey => $tval){
                        if($tkey % 2 == 0){
                            $qnumber += substr_count($tval, '?');
                        }
                    }
                    $resultArr[0] = $subleft . $sval[0] . $subright;
                    array_splice($resultArr[1], $qnumber, 0, $sval[1]);
                }
            }
        }
        return $resultArr;
    }
    public function subQuery($alias)
    {
        $alias = trim($alias);
        $className = $this->className();
        $result = $this->single($this->_swuuws_select[$className], false);
        $result[0] = '(' . $result[0] . ')';
        self::$_swuuws_sub_query[$alias] = $result;
        unset($this->_swuuws_select[$className]);
    }
    private function multi()
    {
        $keyArr = array_keys($this->_swuuws_select);
        $this->parse($keyArr[0]);
        $keys = array_keys($this->_swuuws_select);
        $first = $this->_swuuws_select[$keys[0]];
        if(empty($first['joinStr'])){
            $sql = $first['tableName'];
            $uarr = $first['whereArr'][0][1];
        }
        else{
            $sql = 'SELECT ' . $first['fieldstr'] . ' FROM ';
            $sql .= (substr(strtoupper($first['tableName']), 0, 8) == '(SELECT ' || substr(strtoupper($first['tableName']), 0, 7) == 'SELECT ') ? '(' . $first['tableName'] . ')' : $first['tableName'];
            $sql .= ' AS ' . $first['alias'];
            if(isset($first['joinStr']) && !empty($first['joinStr'])){
                $sql .= $first['joinStr'];
            }
            $where = '';
            $uarr = [];
            if(isset($first['whereArr'])){
                foreach($first['whereArr'] as $wkey => $wval){
                    if(!empty($wval[0])){
                        $where .= empty($where) ? '(' . $wval[0] . ')' : ' AND (' . $wval[0] . ')';
                    }
                    $uarr = array_merge($uarr, $wval[1]);
                }
            }
            if(!empty($where)){
                $sql .= ' WHERE ' . $where;
            }
            if(isset($first['group'])){
                $sql .= ' GROUP BY ' . $first['group'];
            }
            if(isset($first['having'])){
                $sql .= ' HAVING (' . $first['having'] . ')';
            }
            if(isset($first['order'])){
                $sql .= ' ORDER BY ' . $first['order'];
            }
            if(isset($first['limit'])){
                $sql .= ' LIMIT ' . $first['limit'];
            }
            if(isset($first['offset'])){
                $sql .= ' OFFSET ' . $first['offset'];
            }
        }
        return [$sql, $uarr];
    }
    private function parse($name)
    {
        if(!isset($this->_swuuws_select[$name]['union']) && !isset($this->_swuuws_select[$name]['join'])){
            $this->process($name);
            return [$name, $this->_swuuws_select[$name]];
        }
        else{
            if(isset($this->_swuuws_select[$name]['union']) && isset($this->_swuuws_select[$name]['join'])){
                $containerArr = array_merge($this->_swuuws_select[$name]['join'], $this->_swuuws_select[$name]['union']);
                $sortArr = [];
                foreach($containerArr as $val){
                    $sortArr[] = $val[2];
                }
                array_multisort($sortArr, $containerArr);
                $groupArr = [];
                $tmpArr = [];
                $way = '';
                foreach($containerArr as $key => $val){
                    if($way != $val[3]){
                        if(count($tmpArr) > 0){
                            $groupArr[] = [$way, $tmpArr];
                            $tmpArr = [];
                        }
                        $way = $val[3];
                    }
                    $tmpArr[] = $val;
                }
                if(count($tmpArr) > 0){
                    $groupArr[] = [$way, $tmpArr];
                }
                $this->process($name);
                $joinArr = [];
                $unionArr = [];
                foreach($groupArr as $gkey => $gval){
                    $union = [];
                    $join = [];
                    if($gval[0] == 'join'){
                        foreach($gval[1] as $jkey => $jval){
                            $join[] = $this->parse($jval[0]);
                            $joinArr[$jval[0]] = $jval[1];
                        }
                        $joinStr = '';
                        foreach($join as $key => $val){
                            $this->_swuuws_select[$name]['fieldstr'] .= ' ,' . $val[1]['fieldstr'];
                            if(isset($joinArr[$val[0]]) && $joinArr[$val[0]] == 'left'){
                                $joinStr .= ' LEFT JOIN ';
                            }
                            elseif(isset($joinArr[$val[0]]) && $joinArr[$val[0]] == 'right'){
                                $joinStr .= ' RIGHT JOIN ';
                            }
                            else{
                                $joinStr .= ' INNER JOIN ';
                            }
                            $tableName = $val[1]['tableName'];
                            if(strtoupper(substr($tableName, 0, 8)) == '(SELECT ' || strtoupper(substr($tableName, 0, 7)) == 'SELECT '){
                                $tableName = '(' . $tableName . ')';
                            }
                            $joinStr .= $tableName . ' AS ' . $val[1]['alias'] . ' ON ' . $val[1]['alias'] . '.' . $val[1]['equal']['currentname'] . ' = ' . $this->_swuuws_select[$name]['alias'] . '.' . $val[1]['equal']['uppername'];
                            if(isset($val[1]['joinStr'])){
                                $joinStr .= $val[1]['joinStr'];
                            }
                            $this->_swuuws_select[$name]['whereArr'] = array_merge($this->_swuuws_select[$name]['whereArr'], $val[1]['whereArr']);
                            if(isset($val[1]['order'])){
                                $this->_swuuws_select[$name]['order'] = empty($this->_swuuws_select[$name]['order']) ? $val[1]['order'] : $this->_swuuws_select[$name]['order'] . ', ' . $val[1]['order'];
                            }
                            if(isset($val[1]['group'])){
                                $this->_swuuws_select[$name]['group'] = empty($this->_swuuws_select[$name]['group']) ? $val[1]['group'] : $this->_swuuws_select[$name]['group'] . ', ' . $val[1]['group'];
                            }
                            if(isset($val[1]['having'])){
                                $this->_swuuws_select[$name]['having'] = empty($this->_swuuws_select[$name]['having']) ? $val[1]['having'] : $this->_swuuws_select[$name]['having'] . ' AND ' . $val[1]['having'];
                            }
                        }
                        $this->_swuuws_select[$name]['joinStr'] = $joinStr;
                    }
                    elseif($gval[0] == 'union'){
                        foreach($gval[1] as $ukey => $uval){
                            $union[] = $this->parse($uval[0]);
                            $unionArr[$uval[0]] = $uval[1];
                        }
                        $unionStr = '(SELECT ' . $this->_swuuws_select[$name]['fieldstr'] . ' FROM ';
                        $unionStr .= (substr(strtoupper($this->_swuuws_select[$name]['tableName']), 0, 8) == '(SELECT ' || substr(strtoupper($this->_swuuws_select[$name]['tableName']), 0, 7) == 'SELECT ') ? '(' . $this->_swuuws_select[$name]['tableName'] . ')' : $this->_swuuws_select[$name]['tableName'];
                        $unionStr .= ' AS ' . $this->_swuuws_select[$name]['alias'];
                        if(isset($this->_swuuws_select[$name]['joinStr']) && !empty($this->_swuuws_select[$name]['joinStr'])){
                            $unionStr .= $this->_swuuws_select[$name]['joinStr'];
                            $this->_swuuws_select[$name]['joinStr'] = '';
                        }
                        $where = '';
                        $whereArr = [];
                        if(isset($this->_swuuws_select[$name]['whereArr'])){
                            foreach($this->_swuuws_select[$name]['whereArr'] as $wkey => $wval){
                                if(!empty($wval[0])){
                                    $where .= empty($where) ? '(' . $wval[0] . ')' : ' AND (' . $wval[0] . ')';
                                }
                                $whereArr = array_merge($whereArr, $wval[1]);
                            }
                        }
                        if(!empty($where)){
                            $unionStr .= ' WHERE ' . $where;
                        }
                        if(isset($this->_swuuws_select[$name]['group'])){
                            $unionStr .= ' GROUP BY ' . $this->_swuuws_select[$name]['group'];
                        }
                        if(isset($this->_swuuws_select[$name]['having'])){
                            $unionStr .= ' HAVING (' . $this->_swuuws_select[$name]['having'] . ')';
                        }
                        if(isset($this->_swuuws_select[$name]['order'])){
                            $unionStr .= ' ORDER BY ' . $this->_swuuws_select[$name]['order'];
                        }
                        if(isset($this->_swuuws_select[$name]['limit'])){
                            $unionStr .= ' LIMIT ' . $this->_swuuws_select[$name]['limit'];
                        }
                        if(isset($this->_swuuws_select[$name]['offset'])){
                            $unionStr .= ' OFFSET ' . $this->_swuuws_select[$name]['offset'];
                        }
                        $unionStr .= ')';
                        foreach($union as $key => $val){
                            if(!empty($unionStr)){
                                if($unionArr[$val[0]] == 'all'){
                                    $unionStr .= ' UNION ALL ';
                                }
                                else{
                                    $unionStr .= ' UNION ';
                                }
                            }
                            $unionStr .= '(SELECT ' . $val[1]['fieldstr'] . ' FROM ' . $val[1]['tableName'] . ' AS ' . $val[1]['alias'];
                            if(isset($val[1]['joinStr']) && !empty($val[1]['joinStr'])){
                                $unionStr .= $val[1]['joinStr'];
                            }
                            $where = '';
                            if(isset($val[1]['whereArr'])){
                                foreach($val[1]['whereArr'] as $wkey => $wval){
                                    if(!empty($wval[0])){
                                        $where .= empty($where) ? '(' . $wval[0] . ')' : ' AND (' . $wval[0] . ')';
                                    }
                                    $whereArr = array_merge($whereArr, $wval[1]);
                                }
                            }
                            if(!empty($where)){
                                $unionStr .= ' WHERE ' . $where;
                            }
                            if(isset($val[1]['group'])){
                                $unionStr .= ' GROUP BY ' . $val[1]['group'];
                            }
                            if(isset($val[1]['having'])){
                                $unionStr .= ' HAVING (' . $val[1]['having'] . ')';
                            }
                            if(isset($val[1]['order'])){
                                $unionStr .= ' ORDER BY ' . $val[1]['order'];
                            }
                            if(isset($val[1]['limit'])){
                                $unionStr .= ' LIMIT ' . $val[1]['limit'];
                            }
                            if(isset($val[1]['offset'])){
                                $unionStr .= ' OFFSET ' . $val[1]['offset'];
                            }
                            $unionStr .= ')';
                        }
                        $oldalias = $this->_swuuws_select[$name]['alias'];
                        $newalias = 'swuuws_' . $oldalias;
                        $this->_swuuws_select[$name]['tableName'] = $unionStr;
                        $this->_swuuws_select[$name]['alias'] = $newalias;
                        $this->_swuuws_select[$name]['whereArr'] = [['', $whereArr]];
                        $fieldasArr = [];
                        $fieldas = explode(',', $this->_swuuws_select[$name]['fieldstr']);
                        foreach($fieldas as $akey => $aval){
                            $aval = trim($aval);
                            if(stripos($aval, ' AS ') !== false){
                                $findex = strpos($aval, ' ');
                                $atmpk = substr($aval, 0, $findex);
                                $tindex = strripos($aval, ' ');
                                $atmp = $newalias . '.' . substr($aval, $tindex + 1);
                                $fieldasArr[$atmpk] = $atmp;
                            }
                        }
                        unset($fieldas);
                        if(isset($this->_swuuws_select[$name]['order'])){
                            $tmp = ' ' . $this->_swuuws_select[$name]['order'] . ' ';
                            foreach($fieldasArr as $askey => $asval){
                                $tmp = preg_replace('/([^\w\.])(' . $askey . ')([^\w\.])/', '${1}' . $asval . '${3}', $tmp);
                            }
                            $tmp = preg_replace('/([^\w\.])(' . $oldalias . '\.)/', '${1}' . $newalias . '.', $tmp);
                            $this->_swuuws_select[$name]['order'] = trim($tmp);
                        }
                        if(isset($this->_swuuws_select[$name]['group'])){
                            $tmp = ' ' . $this->_swuuws_select[$name]['group'] . ' ';
                            foreach($fieldasArr as $askey => $asval){
                                $tmp = preg_replace('/([^\w\.])(' . $askey . ')([^\w\.])/', '${1}' . $asval . '${3}', $tmp);
                            }
                            $tmp = preg_replace('/([^\w\.])(' . $oldalias . '\.)/', '${1}' . $newalias . '.', $tmp);
                            $this->_swuuws_select[$name]['group'] = trim($tmp);
                        }
                        if(isset($this->_swuuws_select[$name]['having'])){
                            $tmp = ' ' . $this->_swuuws_select[$name]['having'] . ' ';
                            foreach($fieldasArr as $askey => $asval){
                                $tmp = preg_replace('/([^\w\.])(' . $askey . ')([^\w\.])/', '${1}' . $asval . '${3}', $tmp);
                            }
                            $tmp = preg_replace('/([^\w\.])(' . $oldalias . '\.)/', '${1}' . $newalias . '.', $tmp);
                            $this->_swuuws_select[$name]['having'] = trim($tmp);
                        }
                        if(isset($this->_swuuws_select[$name]['where'][0])){
                            $tmp = ' ' . $this->_swuuws_select[$name]['where'][0] . ' ';
                            $tmpArr = $this->wToArr($tmp);
                            $tmpArr = array_map(function($key, $val) use($oldalias, $newalias, $fieldasArr){
                                if($key % 2 == 0){
                                    foreach($fieldasArr as $askey => $asval){
                                        $val = preg_replace('/([^\w\.])(' . $askey . ')([^\w\.])/', '${1}' . $asval . '${3}', $val);
                                    }
                                    return preg_replace('/([^\w\.])(' . $oldalias . '\.)/', '${1}' . $newalias . '.', $val);
                                }
                                else{
                                    return $val;
                                }
                            }, array_keys($tmpArr), $tmpArr);
                            $tmp = implode('', $tmpArr);
                            $this->_swuuws_select[$name]['where'][0] = trim($tmp);
                        }
                        if(isset($this->_swuuws_select[$name]['fieldstr'])){
                            $fieldArr = explode(',', $this->_swuuws_select[$name]['fieldstr']);
                            foreach($fieldArr as $fkey => $fval){
                                $tmp = ' ' . $fval . ' ';
                                $replaced = false;
                                foreach($fieldasArr as $askey => $asval){
                                    if(preg_match('/[^\w\.]' . $askey . '[^\w\.]/', $tmp)){
                                        $tmp = $asval;
                                        $replaced = true;
                                        break;
                                    }
                                }
                                if($replaced == false){
                                    if(preg_match('/[^\w\.]' . $oldalias . '\./', $tmp)){
                                        $tmp = preg_replace('/([^\w\.])(' . $oldalias . '\.)/', '${1}' . $newalias . '.', $tmp);
                                        $tmp = trim($tmp);
                                    }
                                    else{
                                        $tmp = rtrim($tmp);
                                        if(stripos($tmp, ' AS ') !== false){

                                            $tindex = strripos($tmp, ' ');
                                            $tmp = $newalias . '.' . substr($tmp, $tindex + 1);
                                        }
                                        else{
                                            $tindex = strripos($tmp, '.');
                                            $tmp = $newalias . substr($tmp, $tindex);
                                        }
                                    }
                                }
                                $fieldArr[$fkey] = $tmp;
                            }
                            $this->_swuuws_select[$name]['fieldstr'] = implode(', ', $fieldArr);
                        }
                    }
                }
            }
            elseif(isset($this->_swuuws_select[$name]['union'])){
                $union = [];
                $unionArr = [];
                foreach($this->_swuuws_select[$name]['union'] as $ukey => $uval){
                    $union[] = $this->parse($uval[0]);
                    $unionArr[$uval[0]] = $uval[1];
                }
                $this->process($name);
                $unionStr = '(SELECT ' . $this->_swuuws_select[$name]['fieldstr'] . ' FROM ';
                $unionStr .= (substr(strtoupper($this->_swuuws_select[$name]['tableName']), 0, 8) == '(SELECT ' || substr(strtoupper($this->_swuuws_select[$name]['tableName']), 0, 7) == 'SELECT ') ? '(' . $this->_swuuws_select[$name]['tableName'] . ')' : $this->_swuuws_select[$name]['tableName'];
                $unionStr .= ' AS ' . $this->_swuuws_select[$name]['alias'];
                if(isset($this->_swuuws_select[$name]['joinStr']) && !empty($this->_swuuws_select[$name]['joinStr'])){
                    $unionStr .= $this->_swuuws_select[$name]['joinStr'];
                    $this->_swuuws_select[$name]['joinStr'] = '';
                }
                $where = '';
                $whereArr = [];
                if(isset($this->_swuuws_select[$name]['whereArr'])){
                    foreach($this->_swuuws_select[$name]['whereArr'] as $wkey => $wval){
                        if(!empty($wval[0])){
                            $where .= empty($where) ? '(' . $wval[0] . ')' : ' AND (' . $wval[0] . ')';
                        }
                        $whereArr = array_merge($whereArr, $wval[1]);
                    }
                }
                if(!empty($where)){
                    $unionStr .= ' WHERE ' . $where;
                }
                if(isset($this->_swuuws_select[$name]['group'])){
                    $unionStr .= ' GROUP BY ' . $this->_swuuws_select[$name]['group'];
                }
                if(isset($this->_swuuws_select[$name]['having'])){
                    $unionStr .= ' HAVING (' . $this->_swuuws_select[$name]['having'] . ')';
                }
                if(isset($this->_swuuws_select[$name]['order'])){
                    $unionStr .= ' ORDER BY ' . $this->_swuuws_select[$name]['order'];
                }
                if(isset($this->_swuuws_select[$name]['limit'])){
                    $unionStr .= ' LIMIT ' . $this->_swuuws_select[$name]['limit'];
                }
                if(isset($this->_swuuws_select[$name]['offset'])){
                    $unionStr .= ' OFFSET ' . $this->_swuuws_select[$name]['offset'];
                }
                $unionStr .= ')';
                foreach($union as $key => $val){
                    if(!empty($unionStr)){
                        if($unionArr[$val[0]] == 'all'){
                            $unionStr .= ' UNION ALL ';
                        }
                        else{
                            $unionStr .= ' UNION ';
                        }
                    }
                    $unionStr .= '(SELECT ' . $val[1]['fieldstr'] . ' FROM ' . $val[1]['tableName'] . ' AS ' . $val[1]['alias'];
                    if(isset($val[1]['joinStr']) && !empty($val[1]['joinStr'])){
                        $unionStr .= $val[1]['joinStr'];
                    }
                    $where = '';
                    if(isset($val[1]['whereArr'])){
                        foreach($val[1]['whereArr'] as $wkey => $wval){
                            if(!empty($wval[0])){
                                $where .= empty($where) ? '(' . $wval[0] . ')' : ' AND (' . $wval[0] . ')';
                            }
                            $whereArr = array_merge($whereArr, $wval[1]);
                        }
                    }
                    if(!empty($where)){
                        $unionStr .= ' WHERE ' . $where;
                    }
                    if(isset($val[1]['group'])){
                        $unionStr .= ' GROUP BY ' . $val[1]['group'];
                    }
                    if(isset($val[1]['having'])){
                        $unionStr .= ' HAVING (' . $val[1]['having'] . ')';
                    }
                    if(isset($val[1]['order'])){
                        $unionStr .= ' ORDER BY ' . $val[1]['order'];
                    }
                    if(isset($val[1]['limit'])){
                        $unionStr .= ' LIMIT ' . $val[1]['limit'];
                    }
                    if(isset($val[1]['offset'])){
                        $unionStr .= ' OFFSET ' . $val[1]['offset'];
                    }
                    $unionStr .= ')';
                }
                $oldalias = $this->_swuuws_select[$name]['alias'];
                $newalias = 'swuuws_' . $oldalias;
                $this->_swuuws_select[$name]['tableName'] = $unionStr;
                $this->_swuuws_select[$name]['alias'] = $newalias;
                $this->_swuuws_select[$name]['whereArr'] = [['', $whereArr]];
                if(isset($this->_swuuws_select[$name]['order'])){
                    $tmp = ' ' . $this->_swuuws_select[$name]['order'];
                    $tmp = preg_replace('/([^\w\.])(' . $oldalias . '\.)/', '${1}' . $newalias . '.', $tmp);
                    $this->_swuuws_select[$name]['order'] = substr($tmp, 1);
                }
                if(isset($this->_swuuws_select[$name]['group'])){
                    $tmp = ' ' . $this->_swuuws_select[$name]['group'];
                    $tmp = preg_replace('/([^\w\.])(' . $oldalias . '\.)/', '${1}' . $newalias . '.', $tmp);
                    $this->_swuuws_select[$name]['group'] = substr($tmp, 1);
                }
                if(isset($this->_swuuws_select[$name]['having'])){
                    $tmp = ' ' . $this->_swuuws_select[$name]['having'];
                    $tmp = preg_replace('/([^\w\.])(' . $oldalias . '\.)/', '${1}' . $newalias . '.', $tmp);
                    $this->_swuuws_select[$name]['having'] = substr($tmp, 1);
                }
                if(isset($this->_swuuws_select[$name]['fieldstr'])){
                    $tmp = ' ' . $this->_swuuws_select[$name]['fieldstr'];
                    $tmp = preg_replace('/([^\w\.])(' . $oldalias . '\.)/', '${1}' . $newalias . '.', $tmp);
                    $this->_swuuws_select[$name]['fieldstr'] = substr($tmp, 1);
                }
                if(isset($this->_swuuws_select[$name]['where'][0])){
                    $tmp = ' ' . $this->_swuuws_select[$name]['where'][0];
                    $tmpArr = $this->wToArr($tmp);
                    $tmpArr = array_map(function($key, $val) use($oldalias, $newalias){
                        if($key % 2 == 0){
                            return preg_replace('/([^\w\.])(' . $oldalias . '\.)/', '${1}' . $newalias . '.', $val);
                        }
                        else{
                            return $val;
                        }
                    }, array_keys($tmpArr), $tmpArr);
                    $tmp = implode('', $tmpArr);
                    $this->_swuuws_select[$name]['where'][0] = substr($tmp, 1);
                }
            }
            elseif(isset($this->_swuuws_select[$name]['join'])){
                $join = [];
                $joinArr = [];
                foreach($this->_swuuws_select[$name]['join'] as $jkey => $jval){
                    $join[] = $this->parse($jval[0]);
                    $joinArr[$jval[0]] = $jval[1];
                }
                $this->process($name);
                $joinStr = '';
                foreach($join as $key => $val){
                    $this->_swuuws_select[$name]['fieldstr'] .= ' ,' . $val[1]['fieldstr'];
                    if(isset($joinArr[$val[0]]) && $joinArr[$val[0]] == 'left'){
                        $joinStr .= ' LEFT JOIN ';
                    }
                    elseif(isset($joinArr[$val[0]]) && $joinArr[$val[0]] == 'right'){
                        $joinStr .= ' RIGHT JOIN ';
                    }
                    else{
                        $joinStr .= ' INNER JOIN ';
                    }
                    $tableName = $val[1]['tableName'];
                    if(strtoupper(substr($tableName, 0, 8)) == '(SELECT ' || strtoupper(substr($tableName, 0, 7)) == 'SELECT '){
                        $tableName = '(' . $tableName . ')';
                    }
                    $joinStr .= $tableName . ' AS ' . $val[1]['alias'] . ' ON ' . $val[1]['alias'] . '.' . $val[1]['equal']['currentname'] . ' = ' . $this->_swuuws_select[$name]['alias'] . '.' . $val[1]['equal']['uppername'];
                    if(isset($val[1]['joinStr'])){
                        $joinStr .= $val[1]['joinStr'];
                    }
                    $this->_swuuws_select[$name]['whereArr'] = array_merge($this->_swuuws_select[$name]['whereArr'], $val[1]['whereArr']);
                    if(isset($val[1]['order'])){
                        $this->_swuuws_select[$name]['order'] = empty($this->_swuuws_select[$name]['order']) ? $val[1]['order'] : $this->_swuuws_select[$name]['order'] . ', ' . $val[1]['order'];
                    }
                    if(isset($val[1]['group'])){
                        $this->_swuuws_select[$name]['group'] = empty($this->_swuuws_select[$name]['group']) ? $val[1]['group'] : $this->_swuuws_select[$name]['group'] . ', ' . $val[1]['group'];
                    }
                    if(isset($val[1]['having'])){
                        $this->_swuuws_select[$name]['having'] = empty($this->_swuuws_select[$name]['having']) ? $val[1]['having'] : $this->_swuuws_select[$name]['having'] . ' AND ' . $val[1]['having'];
                    }
                }
                $this->_swuuws_select[$name]['joinStr'] = $joinStr;
            }
            return [$name, $this->_swuuws_select[$name]];
        }
    }
    private function process($name)
    {
        if(isset($this->_swuuws_select[$name]['sub'])){
            $tname = $this->_swuuws_select[$name]['sub'];
        }
        else{
            $tname = $this->tableName($name);
        }
        $this->_swuuws_select[$name]['tableName'] = $tname;
        if(isset($this->_swuuws_select[$name]['alias'])){
            $talias = $this->_swuuws_select[$name]['alias'];
        }
        else{
            $talias = $this->capitalUnderline($name);
        }
        $this->_swuuws_select[$name]['alias'] = $talias;
        if(isset($this->_swuuws_select[$name]['field'])){
            $field = $this->multiField($this->_swuuws_select[$name]['field'], $talias, $this->_swuuws_select[$name]['allField']);
        }
        elseif(isset($this->_swuuws_select[$name]['list'])){
            $list = $this->breakStrToArr($this->_swuuws_select[$name]['list']);
            $list = array_map(function($itm) use($name, $talias){
                if(preg_match('/^[A-Za-z_]+\(.+\)/', $itm)){
                    return $this->addFunAlias($itm, $this->_swuuws_select[$name]['allField'], $talias);
                }
                else{
                    return $talias . '.' . $itm;
                }
            }, $list);
            $field = implode(',', $list);
        }
        else{
            $field = $talias . '.*';
        }
        $this->_swuuws_select[$name]['fieldstr'] = $field;
        if(isset($this->_swuuws_select[$name]['order'])){
            $this->_swuuws_select[$name]['order'] = $this->addAlias($this->_swuuws_select[$name]['order'], $talias);
        }
        if(isset($this->_swuuws_select[$name]['group'])){
            $this->_swuuws_select[$name]['group'] = $this->addAlias($this->_swuuws_select[$name]['group'], $talias);
        }
        if(isset($this->_swuuws_select[$name]['where'])){
            $allf = implode('|', $this->_swuuws_select[$name]['allField']);
            $tmpwhere = ' ' . $this->_swuuws_select[$name]['where'][0];
            $tmpArr = $this->wToArr($tmpwhere);
            $tmpArr = array_map(function($key, $val) use($allf, $talias){
                if($key % 2 == 0){
                    return preg_replace('/([^\w\.])(' . $allf . ')([^\w\.])/', '${1}' . $talias . '.$2$3', $val);
                }
                else{
                    return $val;
                }
            }, array_keys($tmpArr), $tmpArr);
            $tmpwhere = implode('', $tmpArr);
            $this->_swuuws_select[$name]['where'][0] = substr($tmpwhere, 1);
            $this->_swuuws_select[$name]['whereArr'][] = $this->_swuuws_select[$name]['where'];
        }
        else{
            $this->_swuuws_select[$name]['whereArr'] = [];
        }
        if(isset($this->_swuuws_select[$name]['having'])){
            $allf = implode('|', $this->_swuuws_select[$name]['allField']);
            $this->_swuuws_select[$name]['having'] = preg_replace('/([^\w\.])(' . $allf . ')([^\w\.])/', '${1}' . $talias . '.$2$3', $this->_swuuws_select[$name]['having']);
        }
    }
    private function wToArr($string)
    {
        $result = [];
        $symbol = '';
        $begin = 1;
        while(true){
            if($symbol == ''){
                $aposIndex = strpos($string, '\'');
                $doubleIndex = strpos($string, '"');
                if($aposIndex === false){
                    $aposIndex = strlen($string);
                }
                if($doubleIndex === false){
                    $doubleIndex = strlen($string);
                }
                if($aposIndex == $doubleIndex){
                    $result[] = $string;
                    break;
                }
                else{
                    $start = $aposIndex;
                    $symbol = '\'';
                    if($doubleIndex < $aposIndex){
                        $start = $doubleIndex;
                        $symbol = '"';
                    }
                    $result[] = substr($string, 0, $start);
                    $string = substr($string, $start);
                    $begin = 1;
                    continue;
                }
            }
            else{
                $index = strpos($string, $symbol, $begin);
                if($index === false){
                    $index = strlen($string) - 1;
                }
                if(substr($string, $index - 1, 1) == '\\'){
                    $begin = $index + 1;
                    continue;
                }
                else{
                    $result[] = substr($string, 0, $index + 1);
                    $string = substr($string, $index + 1);
                    $symbol = '';
                    if(empty($string)){
                        break;
                    }
                    else{
                        continue;
                    }
                }
            }
        }
        return $result;
    }
    private function addAlias($string, $talias)
    {
        $tmpArr = explode(',', $string);
        $tmpArr = array_map(function($itm) use($talias){
            return $talias . '.' . trim($itm);
        }, $tmpArr);
        return implode(',', $tmpArr);
    }
    private function breakStrToArr($string, $separate = ',')
    {
        $result = [];
        $tmp = '';
        while(false !== $index = strpos($string, $separate)){
            $sub = substr($string, 0, $index);
            if($tmp != ''){
                $sub = $tmp . $separate . $sub;
            }
            if((strpos($sub, '(') === false && strpos($sub, ')') === false) || (substr_count($sub, '(') == substr_count($sub, ')'))){
                $result[] = trim($sub);
                $string = substr($string, $index + strlen($separate));
                $tmp = '';
            }
            else{
                $tmp = $sub;
                $string = substr($string, $index + strlen($separate));
            }
        }
        if(!empty($string)){
            $result[] = trim($string);
        }
        return $result;
    }
    private function multiField($fieldArr, $talias, $allField)
    {
        $field = '';
        foreach($fieldArr as $key => $val){
            $val = trim($val);
            if(empty($val)){
                $field .= empty($field) ? $talias . '.' . $key : ', ' . $talias . '.' . $key;
            }
            elseif(preg_match('/^[A-Za-z_]+\(.+\)/', $val)){
                $tval = $this->addFunAlias($val, $allField, $talias);
                $field .= empty($field) ? $tval : ', ' . $tval;
            }
            elseif(strtolower(substr($val, 0, 3)) == 'as '){
                $field .= empty($field) ? $talias . '.' . $key . ' ' . $val : ', ' . $talias . '.' . $key . ' ' . $val;
            }
            else{
                $field .= empty($field) ? $talias . '.' . $key . ' AS ' . $val : ', ' . $talias . '.' . $key . ' AS ' . $val;
            }
        }
        return $field;
    }
    private function addFunAlias($val, $allField, $talias)
    {
        $allf = implode('|', $allField);
        $leftIndex = strpos($val, '(');
        $rightIndex = strpos($val, ')');
        $left = substr($val, 0, $leftIndex);
        $right = substr($val, $rightIndex + 1);
        $tval = substr($val, $leftIndex, $rightIndex - $leftIndex + 1);
        $tval = preg_replace('/([^\w\.])(' . $allf . ')([^\w\.])/', '${1}' . $talias . '.$2$3', $tval);
        return $left . $tval . $right;
    }
    private function single($tableArr, $tableAlias = true)
    {
        $field = '';
        if(isset($tableArr['field'])){
            foreach($tableArr['field'] as $key => $val){
                $val = trim($val);
                if(empty($val)){
                    $field .= empty($field) ? $key : ', ' . $key;
                }
                elseif(preg_match('/^[A-Za-z_]+\(.+\)/', $val)){
                    $field .= empty($field) ? $val : ', ' . $val;
                }
                elseif(strtolower(substr($val, 0, 3)) == 'as '){
                    $field .= empty($field) ? $key . ' ' . $val : ', ' . $key . ' ' . $val;
                }
                else{
                    $field .= empty($field) ? $key . ' AS ' . $val : ', ' . $key . ' AS ' . $val;
                }
            }
        }
        elseif(isset($tableArr['list'])){
            $field = $tableArr['list'];
        }
        if(empty($field)){
            $field = '*';
        }
        $uarr = [];
        if(isset($tableArr['sub'])){
            $tableName = $tableArr['sub'];
        }
        else{
            $tableName = $this->tableName();
        }
        $sql = 'SELECT ' . $field . ' FROM ' . $tableName;
        if($tableAlias && isset($tableArr['alias'])){
            $sql .= ' AS ' . $tableArr['alias'];
        }
        if(isset($tableArr['where'])){
            $relation = $tableArr['where'][0];
            if(isset($tableArr['where'][1]) && is_array($tableArr['where'][1]) && count($tableArr['where'][1]) > 0){
                $tmpArr = array_keys($tableArr['where'][1]);
                if(substr($tmpArr[0], 0, 1) == ':'){
                    foreach($tableArr['where'][1] as $rkey => $rval){
                        $relation = str_replace($rkey, '?', $relation);
                        $uarr[] = $rval;
                    }
                }
                else{
                    $uarr = $tableArr['where'][1];
                }
            }
            $sql .= ' WHERE ' . $relation;
        }
        if(isset($tableArr['group'])){
            $sql .= ' GROUP BY ' . $tableArr['group'];
        }
        if(isset($tableArr['having'])){
            $sql .= ' HAVING (' . $tableArr['having'] . ')';
        }
        if(isset($tableArr['order'])){
            $sql .= ' ORDER BY ' . $tableArr['order'];
        }
        if(isset($tableArr['limit'])){
            $sql .= ' LIMIT ' . $tableArr['limit'];
        }
        if(isset($tableArr['offset'])){
            $sql .= ' OFFSET ' . $tableArr['offset'];
        }
        return [$sql, $uarr];
    }
    public function union($union)
    {
        $this->unionFunc($union, 'distinct');
        return $this;
    }
    public function unionAll($union)
    {
        $this->unionFunc($union, 'all');
        return $this;
    }
    private function unionFunc($union, $way)
    {
        $className = $this->className();
        $subClassName = $this->className($union);
        if(!isset($this->_swuuws_select[$className]['allField'])){
            $this->_swuuws_select[$className]['allField'] = array_keys($this->getAttr(true));
        }
        if(!isset($union->_swuuws_select[$subClassName]['allField'])){
            $union->_swuuws_select[$subClassName]['allField'] = array_keys($this->getAttr(true, $union));
        }
        $this->_swuuws_select[$className]['union'][] = [$subClassName, $way, self::$queue ++, 'union'];
        $this->_swuuws_select = array_merge($this->_swuuws_select, $union->_swuuws_select);
    }
    public function pick($list)
    {
        $className = $this->className();
        $this->_swuuws_select[$className]['list'] = $list;
        return $this;
    }
    public function equal($current, $upper)
    {
        $className = $this->className();
        $this->_swuuws_select[$className]['equal']['currentname'] = $current;
        $this->_swuuws_select[$className]['equal']['currentobject'] = $className;
        $this->_swuuws_select[$className]['equal']['uppername'] = $upper;
        return $this;
    }
    public function join($join)
    {
        $this->joinfunc($join, 'inner');
        return $this;
    }
    public function joinLeft($join)
    {
        $this->joinfunc($join, 'left');
        return $this;
    }
    public function joinRight($join)
    {
        $this->joinfunc($join, 'right');
        return $this;
    }
    private function joinfunc($join, $way)
    {
        $className = $this->className();
        $subClassName = $this->className($join);
        if(!isset($this->_swuuws_select[$className]['allField'])){
            $this->_swuuws_select[$className]['allField'] = array_keys($this->getAttr(true));
        }
        if(!isset($join->_swuuws_select[$subClassName]['allField'])){
            $join->_swuuws_select[$subClassName]['allField'] = array_keys($this->getAttr(true, $join));
        }
        $join->_swuuws_select[$subClassName]['equal']['upperobject'] = $className;
        $this->_swuuws_select[$className]['join'][] = [$subClassName, $way, self::$queue ++, 'join'];
        $this->_swuuws_select = array_merge($this->_swuuws_select, $join->_swuuws_select);
    }
    public function alias($name)
    {
        $className = $this->className();
        $this->_swuuws_select[$className]['alias'] = $name;
        return $this;
    }
    public function sub($name)
    {
        $className = $this->className();
        $name = '::' . ltrim(trim($name), ':');
        $this->_swuuws_select[$className]['sub'] = $name;
        return $this;
    }
    public function having($having)
    {
        $className = $this->className();
        $this->_swuuws_select[$className]['having'] = $having;
        return $this;
    }
    public function group($group)
    {
        $className = $this->className();
        $this->_swuuws_select[$className]['group'] = $group;
        return $this;
    }
    public function order($order)
    {
        $className = $this->className();
        $this->_swuuws_select[$className]['order'] = $order;
        return $this;
    }
    public function offset($offset)
    {
        if(!preg_match('/^\d+$/', $offset)){
            throw new ModelException(9);
        }
        $className = $this->className();
        $this->_swuuws_select[$className]['offset'] = $offset;
        return $this;
    }
    public function limit($low, $high = null)
    {
        if(!preg_match('/^\d+$/', $low)){
            throw new ModelException(8);
        }
        if($high != null && !preg_match('/^\d+$/', $high)){
            throw new ModelException(8);
        }
        $className = $this->className();
        if($high == null){
            $this->_swuuws_select[$className]['limit'] = $low;
        }
        else{
            $this->_swuuws_select[$className]['limit'] = $high;
            $this->_swuuws_select[$className]['offset'] = $low;
        }
        return $this;
    }
    public function select($relation = null, $relationArr = null)
    {
        $attrArr = $this->getAttr();
        $className = $this->className();
        if(count($attrArr) > 0){
            $this->_swuuws_select[$className]['field'] = $attrArr;
        }
        if($relation != null){
            $uarr = [];
            if($relationArr != null){
                if(!is_array($relationArr)){
                    $relationArr = [$relationArr];
                }
                $tmpArr = array_keys($relationArr);
                if(substr($tmpArr[0], 0, 1) == ':'){
                    foreach($relationArr as $rkey => $rval){
                        $relation = str_replace($rkey, '?', $relation);
                        $uarr[] = $rval;
                    }
                    $condition = $relation;
                }
                else{
                    $condition = $relation;
                    $uarr = $relationArr;
                }
            }
            else{
                $condition = $relation;
            }
            $this->_swuuws_select[$className]['where'] = [$condition, $uarr];
        }
        $this->clearAttr($attrArr);
        return $this;
    }
    public function update($relation = null, $relationArr = null)
    {
        $attrArr = $this->getAttr();
        $condition = '';
        $setv = '';
        $uarr = [];
        foreach($attrArr as $key => $val){
            if(is_array($val)){
                $dcon = $key . ' = ' . $key . ' ' . $val[0] . ' ?';
                $uarr[] = $val[1];
            }
            elseif(preg_match('/^[A-Za-z_]*\(.+\)$/', $val)){
                $dcon = $key . ' = ' . $val;
            }
            else{
                $dcon = $key . ' = ?';
                $uarr[] = $val;
            }
            $setv .= empty($setv) ? $dcon : ', ' . $dcon;
        }
        $sql = 'UPDATE ' . $this->tableName() . ' SET ' . $setv;
        if($relation != null){
            if($relationArr != null){
                if(!is_array($relationArr)){
                    $relationArr = [$relationArr];
                }
                $tmpArr = array_keys($relationArr);
                if(substr($tmpArr[0], 0, 1) == ':'){
                    foreach($relationArr as $rkey => $rval){
                        $relation = str_replace($rkey, '?', $relation);
                        $uarr[] = $rval;
                    }
                    $condition = $relation;
                }
                else{
                    $condition = $relation;
                    $uarr = array_merge($uarr, $relationArr);
                }
            }
            else{
                $condition = $relation;
            }
        }
        if($condition != ''){
            $sql .= ' WHERE ' . $condition;
        }
        if(count($uarr) < 1){
            $result = Db::execute($sql);
        }
        else{
            $result = Db::execute($sql, $uarr);
        }
        $this->clearAttr($attrArr);
        return $result;
    }
    public function delete($relation = null, $relationArr = null)
    {
        $attrArr = $this->getAttr();
        $condition = '';
        $uarr = [];
        if($relation == null){
            foreach($attrArr as $key => $val){
                if(is_array($val)){
                    $dcon = $key . ' ' . $val[0] . ' ?';
                    $vcon = $val[1];
                }
                else{
                    $dcon = $key . ' = ?';
                    $vcon = $val;
                }
                $condition .= empty($condition) ? $dcon : ' and ' . $dcon;
                $uarr[] = $vcon;
            }
        }
        else{
            $condition = $relation;
            if($relationArr != null){
                if(!is_array($relationArr)){
                    $relationArr = [$relationArr];
                }
                $uarr = $relationArr;
            }
        }
        $sql = 'DELETE FROM ' . $this->tableName() . ' WHERE ' . $condition;
        if(count($uarr) < 1){
            $result = Db::execute($sql);
        }
        else{
            $result = Db::execute($sql, $uarr);
        }
        $this->clearAttr($attrArr);
        return $result;
    }
    public function insert($insertArr = null)
    {
        if(is_array($insertArr)){
            if(count($insertArr) != count($insertArr, 1)){
                $refer = [];
                $dbarr = [];
                foreach($insertArr as $ival){
                    $tarr = array_keys($ival);
                    if(count($refer) < 1){
                        $refer = $tarr;
                    }
                    elseif($refer != $tarr){
                        throw new ModelException(7, ': ' . implode(', ', $tarr));
                    }
                    $this->checkConstraint($ival);
                    $uarr = [];
                    foreach($ival as $key => $val){
                        $uarr[':' . $key] = $val;
                    }
                    $dbarr[] = $uarr;
                }
                $setstr = '';
                $vstr = '';
                foreach($refer as $val){
                    $setstr .= empty($setstr) ? $val : ',' . $val;
                    $vstr .= empty($vstr) ? ':' . $val : ', :' . $val;
                }
                $sql = 'INSERT INTO ' . $this->tableName() . ' (' . rtrim($setstr, ',') . ') VALUES (' . rtrim($vstr, ',') . ')';
                Db::execute($sql, $dbarr);
                $result = Db::getLast();
            }
            else{
                $this->checkConstraint($insertArr);
                $result = $this->runInsert($insertArr);
            }
        }
        else{
            $attrArr = $this->getAttr();
            $this->checkConstraint($attrArr);
            $result = $this->runInsert($attrArr);
            $this->clearAttr($attrArr);
        }
        return $result;
    }
    private function runInsert($attrArr)
    {
        $setstr = '';
        $vstr = '';
        $uarr = [];
        foreach($attrArr as $key => $val){
            $setstr .= $key . ',';
            $vstr .= ':' . $key . ',';
            $uarr[':' . $key] = $val;
        }
        $sql = 'INSERT INTO ' . $this->tableName() . ' (' . rtrim($setstr, ',') . ') VALUES (' . rtrim($vstr, ',') . ')';
        Db::execute($sql, $uarr);
        $result = Db::getLast();
        return $result;
    }
    private function checkConstraint($array)
    {
        foreach($array as $key => $val){
            if(!isset($this->_swuuws_library[$key])){
                throw new ModelException(6, ': ' . $key);
            }
            $cons = $this->_swuuws_library[$key];
            $consArr = explode(',', $cons);
            $cons = array_shift($consArr);
            $cons = strtolower(trim($cons));
            if(in_array($cons, ['char', 'varchar'])){
                if(isset($consArr[0])){
                    if(mb_strlen($val) > intval($consArr[0])){
                        throw new ModelException(0, ': ' . $key . '(' . $consArr[0] . ')');
                    }
                }
            }
            elseif(strpos($cons, 'int') !== false){
                if(!preg_match('/^\d+$/', $val)){
                    throw new ModelException(1, ': ' . $key);
                }
            }
            elseif(in_array($cons, ['float', 'double', 'decimal', 'real'])){
                if(!is_numeric($val)){
                    throw new ModelException(2, ': ' . $key);
                }
            }
            elseif(in_array($cons, ['datetime', 'timestamp'])){
                if(!preg_match('/^\d{4}-(0?[1-9]|1[0-2])-(0?[1-9]|[1-2][0-9]|3[0-1]) (0?[0-9]|1[0-9]|2[0-4]):(0?[0-9]|[1-5][0-9]|60):(0?[0-9]|[1-5][0-9]|60)$/', $val)){
                    throw new ModelException(3, ': ' . $key . '(yyyy-mm-dd hh:mm:ss)');
                }
            }
            elseif($cons == 'year'){
                if(!preg_match('/^\d{4}$/', $val)){
                    throw new ModelException(4, ': ' . $key . '(yyyy)');
                }
            }
            elseif($cons == 'date'){
                if(!preg_match('/^\d{4}-(0?[1-9]|1[0-2])-(0?[1-9]|[1-2][0-9]|3[0-1])$/', $val)){
                    throw new ModelException(3, ': ' . $key . '(yyyy-mm-dd)');
                }
            }
            elseif($cons == 'time'){
                if(!preg_match('/^(0?[0-9]|1[0-9]|2[0-4]):(0?[0-9]|[1-5][0-9]|60):(0?[0-9]|[1-5][0-9]|60)$/', $val)){
                    throw new ModelException(5, ': ' . $key . '(hh:mm:ss)');
                }
            }
        }
        return true;
    }
    private function className($object = null)
    {
        if($object == null){
            $name = str_replace('/', '\\', get_class($this));
        }
        else{
            $name = str_replace('/', '\\', get_class($object));
        }
        $nameArr = explode('\\', $name);
        if($object == null){
            return end($nameArr) . '#' . $this->_swuuws_serial;
        }
        else{
            return end($nameArr) . '#' . $object->_swuuws_serial;
        }
    }
    private function tableName($name = null)
    {
        if($name == null){
            $name = $this->className();
        }
        $name = $this->capitalUnderline($name);
        $name = $this->addPrefix($name);
        return $name;
    }
    private function capitalUnderline($name)
    {
        if(false !== $nindex = strrpos($name, '#')){
            $name = substr($name, 0, $nindex);
        }
        return Swuuws::capitalUnderline($name);
    }
    private function addPrefix($name)
    {
        return rtrim(Env::get('TABLE_PREFIX'), '_') . '_' . $name;
    }
    private function clearAttr($attrArr)
    {
        foreach($attrArr as $key => $val){
            $this->$key = null;
        }
    }
    private function getAttr($all = false, $object = null)
    {
        $reArr = [];
        if($object != null && is_object($object)){
            $attr = get_object_vars($object);
        }
        else{
            $attr = get_object_vars($this);
        }
        if(isset($attr['_swuuws_library']) || $attr['_swuuws_library'] == null){
            unset($attr['_swuuws_library']);
        }
        if(isset($attr['_swuuws_select']) || $attr['_swuuws_select'] == null){
            unset($attr['_swuuws_select']);
        }
        if(isset($attr['_swuuws_serial']) || $attr['_swuuws_serial'] == null){
            unset($attr['_swuuws_serial']);
        }
        foreach($attr as $key => $val){
            if($val !== null){
                $reArr[$key] = $val;
            }
            elseif($all){
                $reArr[$key] = null;
            }
        }
        return $reArr;
    }
    public static function transaction($func)
    {
        Db::transaction($func);
    }
}