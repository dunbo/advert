<?php

/**
 * 分页页码
 * @category PhalconDSP
 * @author haoshisuo 2017-9-12
 */

namespace Marser\App\Helpers;

class PaginatorHelper {
    /**
     * 获取分页页码
     * @param $totalRows 记录总条数
     * @param $page  当前页码
     * @param $pagesize  每页条数
     * @param int $num  分页页码数，默认显示5个页码
     */
    public static function get_paginator($totalRows, $page, $pagesize=10, $num=10){
        $page = intval($page);
        $page <= 0 && $page = 1;
        //总页码
        $totalPage = ceil($totalRows / $pagesize);
        if($totalPage > 0) {
            $page > $totalPage && $page = $totalPage;
            //根据$num计算起始页码
            $space = floor($num / 2);
            if ($page == 1) {//当前页码为1
                $startPage = 1;
                $endPage = $num;
            } else if ($page == $totalPage) {//当前页码为最后一页
                $endPage = $totalPage;
                $startPage = $endPage - $num + 1;
            } else if ($page - $space <= 0) { //当前页码小于间隔
                $startPage = 1;
                $endPage = $num;
            } else if ($page - $space > 0) { //当前页码大于间隔
                $startPage = $page - $space;
                $endPage = $startPage + $num - 1;
                if ($endPage > $totalPage) {
                    $startPage = $totalPage - $num + 1;
                }
            }
            $startPage <= 0 && $startPage = 1;
            $endPage > $totalPage && $endPage = $totalPage;
        }else{
            $startPage = $endPage = $page;
        }
        $paginator = range($startPage, $endPage);
        return $paginator;
    }

    /**
     * 生成分页链接
     * @param int $page
     * @param null $url
     * @return string
     */
    public static function get_page_url($page, $pagesize=10, $url=null){
        $page = intval($page);
        $page <= 0 && $page = 1;
        empty($url) && $url = $_SERVER['REQUEST_URI'];
        $url = rtrim($url, '/');
        /** 组装URL */
        $index = strpos($url, '?');
        if($index === false){
            $url = "{$url}?page={$page}&pagesize={$pagesize}";
        }else {
            $url = "{$url}&page={$page}&pagesize={$pagesize}";
        }
        $array = parse_url($url);
        //print_r($array['query']);die;
        $str = isset($array['path']) ? $array['path'] : '';
        if(!empty($array['query'])){
            parse_str($array['query'], $queryArray);
            $query = http_build_query($queryArray);
            $str = "{$str}?{$query}";
        }
        return $str;
    }

     public static function get_jump_param()
     {
        $param_arr = $_GET;
        unset($param_arr['_url']);
         unset($param_arr['page']);
        if($param_arr) {
            $str = "";
            foreach ($param_arr as $key => $value) {
                $str .= "<input type='hidden' name='{$key}' value='{$value}'/>";
            }
            return $str;
        }else {
            return '';
        }
     }

     public static function deal_image($image_url,$image_width=0,$image_height=0,$image_name='图片',$expression='jpg|png|jpeg'){
        if(!$_FILES[$image_url]['tmp_name']){
            throw new \Exception("请上传{$image_name}！");
        }
        // 取得图片后缀
        $suffix = preg_match("/\.({$expression})$/", $_FILES[$image_url]['name'], $matches);
        if ($matches) {
            $suffix = $matches[0];
        } else {
            throw new \Exception("{$image_name}格式不正确！");
        }
        // 判断图片长和宽
        $img_info_arr = getimagesize($_FILES[$image_url]['tmp_name']);
        if (!$img_info_arr) {
            throw new \Exception("{$image_name}格式非法！");
        }
        $width = $img_info_arr[0];
        $height = $img_info_arr[1];
        if($image_width!=0 && $image_height!=0){
            if ($width!=$image_width || $height!=$image_height){
                throw new \Exception("{$image_name}尺寸错误，宽需为{$image_width}px，高需为{$image_height}px");
            }
        }
        $dir_name = "/dsp/".date("Ym/d/");
        if(!is_dir(UPLOAD_PATH.$dir_name)){
            mkdir(UPLOAD_PATH.$dir_name, 0755, true);
        }
        $save_name = $dir_name.time().'_'.rand(1000,9999).$suffix;
        $img_path = UPLOAD_PATH.$save_name;
        if(!move_uploaded_file($_FILES[$image_url]['tmp_name'], $img_path)){
            throw new \Exception("上传{$image_name}出错");
        }
        return $save_name;
    }

}