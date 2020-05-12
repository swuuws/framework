<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws\paginate;

class Bootstrap4 implements iPaginate
{
    public static function pages($pageArr)
    {
        $result = [];
        $li = '';
        if(isset($pageArr['prev'])){
            $disabled = '';
            if($pageArr['prev'][2] == false){
                $disabled = ' disabled';
            }
            $li .= '<li class="page-item' . $disabled . '"><a class="page-link" href="' . $pageArr['prev'][0] . '">' . $pageArr['prev'][1] . '</a></li>';
        }
        if(count($pageArr['list']) > 0){
            foreach($pageArr['list'] as $key => $val){
                $active = '';
                if($val[2] == true){
                    $active = ' active';
                }
                $li .= '<li class="page-item' . $active . '"><a class="page-link" href="' . $val[0] . '">' . $val[1] . '</a></li>';
            }
        }
        if(isset($pageArr['next'])){
            $disabled = '';
            if($pageArr['next'][2] == false){
                $disabled = ' disabled';
            }
            $li .= '<li class="page-item' . $disabled . '"><a class="page-link" href="' . $pageArr['next'][0] . '">' . $pageArr['next'][1] . '</a></li>';
        }
        $result[] = $li;
        $li = '<ul class="pagination">' . $li . '</ul>';
        $result[] = $li;
        return $result;
    }
}