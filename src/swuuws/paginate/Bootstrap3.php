<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws\paginate;

class Bootstrap3 implements iPaginate
{
    public static function pages($pageArr)
    {
        $result = [];
        $li = '';
        if(isset($pageArr['prev'])){
            $disabled = '';
            if($pageArr['prev'][2] == false){
                $disabled = ' class="disabled"';
            }
            $li .= '<li' . $disabled . '><a href="' . $pageArr['prev'][0] . '"><span>' . $pageArr['prev'][1] . '</span></a></li>';
        }
        if(count($pageArr['list']) > 0){
            foreach($pageArr['list'] as $key => $val){
                $active = '';
                if($val[2] == true){
                    $active = ' class="active"';
                }
                $li .= '<li' . $active . '><a href="' . $val[0] . '">' . $val[1] . '</a></li>';
            }
        }
        if(isset($pageArr['next'])){
            $disabled = '';
            if($pageArr['next'][2] == false){
                $disabled = ' class="disabled"';
            }
            $li .= '<li' . $disabled . '><a href="' . $pageArr['next'][0] . '"><span>' . $pageArr['next'][1] . '</span></a></li>';
        }
        $result[] = $li;
        if(count($pageArr['list']) > 0){
            $li = '<ul class="pagination">' . $li . '</ul>';
        }
        else{
            $li = '<ul class="pager">' . $li . '</ul>';
        }
        $result[] = $li;
        return $result;
    }
}