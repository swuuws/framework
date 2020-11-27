<?php
/**
 * Project: swuuws
 * Producer: swuuws [ http://www.swuuws.com ]
 * Author: A.J <804644245@qq.com>
 * Copyright: http://www.swuuws.com All rights reserved.
 */
namespace swuuws;

class Paginate
{
    public static function pages($currentPage, $totalPage, $query, $maxShow, $pnShow, $prev, $next)
    {
        $pageArr = self::getPage($currentPage, $totalPage, $query, $maxShow, $pnShow, $prev, $next);
        $paginate = ucfirst(strtolower(Env::get('PAGINATE')));
        return call_user_func('swuuws\\paginate\\' . $paginate . '::pages', $pageArr);
    }
    private static function getPage($currentPage, $totalPage, $query, $maxShow, $pnShow, $prev, $next)
    {
        $purl = Request::uri();
        if(false !== $pindex = strpos($purl, '?')){
            $purl = substr($purl, 0, $pindex);
        }
        $pageq = $purl . '?page=';
        $hashtag = $purl . '#';
        if($currentPage > 1){
            $previousnum = $currentPage - 1;
        }
        else{
            $previousnum = -1;
        }
        if($previousnum > -1){
            $previousUrl = $pageq . $previousnum . $query;
        }
        else{
            $previousUrl = $hashtag;
        }
        if($currentPage < $totalPage){
            $nextnum = $currentPage + 1;
        }
        else{
            $nextnum = -1;
        }
        if($nextnum > -1){
            $nextUrl = $pageq . $nextnum . $query;
        }
        else{
            $nextUrl = $hashtag;
        }
        $maxnum = Env::get('PAGINATE_MAX_NUM');
        if($maxShow > -1){
            $maxnum = intval($maxShow);
        }
        $prevnext = Env::get('PAGINATE_PREV_NEXT');
        if($pnShow != null && is_bool($pnShow)){
            $prevnext = $pnShow;
        }
        $pageArr = [];
        if($maxnum > 0){
            if($totalPage <= $maxnum){
                for($i = 1; $i <= $totalPage; $i ++){
                    $pageArr[] = [($currentPage == $i) ? $hashtag : $pageq . $i . $query, $i, ($currentPage == $i) ? true : false];
                }
            }
            else{
                switch($maxnum){
                    case 1:
                        if($currentPage == 1){
                            $pageArr[] = [$hashtag, $currentPage, true];
                            $pageArr[] = [$hashtag, '...', false];
                        }
                        elseif($currentPage == $totalPage){
                            $pageArr[] = [$hashtag, '...', false];
                            $pageArr[] = [$hashtag, $currentPage, true];
                        }
                        else{
                            $pageArr[] = [$hashtag, '...', false];
                            $pageArr[] = [$hashtag, $currentPage, true];
                            $pageArr[] = [$hashtag, '...', false];
                        }
                        break;
                    case 2:
                        if($currentPage == 1){
                            $pageArr[] = [$hashtag, $currentPage, true];
                            $pageArr[] = [$pageq . 2 . $query, 2, false];
                            $pageArr[] = [$hashtag, '...', false];
                        }
                        elseif($currentPage == $totalPage){
                            $tmpPage = $currentPage - 1;
                            $pageArr[] = [$hashtag, '...', false];
                            $pageArr[] = [$pageq . $tmpPage . $query, $tmpPage, false];
                            $pageArr[] = [$hashtag, $currentPage, true];
                        }
                        else{
                            $tmpPage = $currentPage + 1;
                            $pageArr[] = [$hashtag, '...', false];
                            $pageArr[] = [$hashtag, $currentPage, true];
                            $pageArr[] = [$pageq . $tmpPage . $query, $tmpPage, false];
                            if($tmpPage < $totalPage){
                                $pageArr[] = [$hashtag, '...', false];
                            }
                        }
                        break;
                    case 3:
                        if($currentPage == 1){
                            $pageArr[] = [$hashtag, $currentPage, true];
                            $pageArr[] = [$pageq . 2 . $query, 2, false];
                            $pageArr[] = [$hashtag, '...', false];
                            $pageArr[] = [$pageq . $totalPage . $query, $totalPage, false];
                        }
                        elseif($currentPage == 2){
                            $pageArr[] = [$pageq . 1 . $query, 1, false];
                            $pageArr[] = [$hashtag, $currentPage, true];
                            $pageArr[] = [$hashtag, '...', false];
                            $pageArr[] = [$pageq . $totalPage . $query, $totalPage, false];
                        }
                        elseif($currentPage == $totalPage - 1){
                            $pageArr[] = [$pageq . 1 . $query, 1, false];
                            $pageArr[] = [$hashtag, '...', false];
                            $pageArr[] = [$hashtag, $currentPage, true];
                            $pageArr[] = [$pageq . $totalPage . $query, $totalPage, false];
                        }
                        elseif($currentPage == $totalPage){
                            $tmpPage = $totalPage - 1;
                            $pageArr[] = [$pageq . 1 . $query, 1, false];
                            $pageArr[] = [$hashtag, '...', false];
                            $pageArr[] = [$pageq . $tmpPage . $query, $tmpPage, false];
                            $pageArr[] = [$hashtag, $currentPage, true];
                        }
                        else{
                            $pageArr[] = [$pageq . 1 . $query, 1, false];
                            $pageArr[] = [$hashtag, '...', false];
                            $pageArr[] = [$hashtag, $currentPage, true];
                            $pageArr[] = [$hashtag, '...', false];
                            $pageArr[] = [$pageq . $totalPage . $query, $totalPage, false];
                        }
                        break;
                    default:
                        if($currentPage == 1){
                            $surplus = $maxnum - 1;
                            for($i = 1; $i <= $surplus; $i ++){
                                $pageArr[] = [($currentPage == $i) ? $hashtag : $pageq . $i . $query, $i, ($currentPage == $i) ? true : false];
                            }
                            $pageArr[] = [$hashtag, '...', false];
                            $pageArr[] = [$pageq . $totalPage . $query, $totalPage, false];
                        }
                        elseif($currentPage == $totalPage){
                            $surplus = $totalPage - $maxnum + 2;
                            $pageArr[] = [$pageq . 1 . $query, 1, false];
                            $pageArr[] = [$hashtag, '...', false];
                            for($i = $surplus; $i <= $totalPage; $i ++){
                                $pageArr[] = [($currentPage == $i) ? $hashtag : $pageq . $i . $query, $i, ($currentPage == $i) ? true : false];
                            }
                        }
                        else{
                            $surplus = $maxnum - 2;
                            $start = $currentPage - round($surplus / 2);
                            if($start <= 1){
                                $start = 2;
                            }
                            $end = $start + $surplus - 1;
                            $pageArr[] = [$pageq . 1 . $query, 1, false];
                            if($start > 2){
                                $pageArr[] = [$hashtag, '...', false];
                            }
                            for($i = $start; $i <= $end; $i ++){
                                $pageArr[] = [($currentPage == $i) ? $hashtag : $pageq . $i . $query, $i, ($currentPage == $i) ? true : false];
                            }
                            if($end < $totalPage - 1){
                                $pageArr[] = [$hashtag, '...', false];
                            }
                            if($end < $totalPage){
                                $pageArr[] = [$pageq . $totalPage . $query, $totalPage, false];
                            }
                        }
                        break;
                }
            }
        }
        else{
            $prevnext = true;
        }
        $resultArr = [];
        if($prevnext){
            $resultArr['prev'] = [$previousUrl, $prev, ($currentPage > 1) ? true : false];
            $resultArr['next'] = [$nextUrl, $next, ($currentPage < $totalPage) ? true : false];
        }
        $resultArr['list'] = $pageArr;
        $resultArr['alwaysShow'] = Env::get('PAGINATE_ALWAYS_SHOW');
        return $resultArr;
    }
}