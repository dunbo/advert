<?php
/**
 * 首页
 */
namespace Marser\App\Frontend\Controllers;

use \Marser\App\Frontend\Controllers\BaseController,
     \Marser\App\Helpers\PaginatorHelper,
     \Marser\App\Helpers\Common;

class StatisticsController extends BaseController{
    public function initialize(){
        parent::initialize();
    }

    /**
     * 媒体
     */
    public function mediaAction(){
        $time = time();
        $def_min_date = date("Y-m-d",strtotime("-1 day"));
        $def_max_date = date("Y-m-d",strtotime("-1 day"));
        $page		=	$this -> request -> get('page', 'int', 1);
        $pagesize   =   $this -> request -> get('pagesize', 'int', 10);
        $start_tm	=	$this -> request -> get('start_tm', 'trim', $def_min_date);
        $end_tm		=	$this -> request -> get('end_tm', 'trim', $def_max_date);
        $order_type =   $this -> request -> get('order_type', 'trim');
        $order      =   $this -> request -> get('order', 'int',0);
        $export     =   $this -> request -> get('export', 'int', 0);

        $paginator = $this -> get_repository('Statistics') -> get_list($page, $pagesize, array(
            'muid'      =>  $this->userinfo['muid'],
            'start_tm'	=>	$start_tm,
            'end_tm'	=>	$end_tm,
            'type'      =>  0,
            'export'    =>  $export,
            'order_type'=>  $order_type,
            'order'     =>  $order,
        ));
        if( $export ) {
            $this -> exportAppData($paginator);
        }else {
            $list  = $paginator->items->toArray();
        }
        /**获取分页页码*/
        $pageNum = PaginatorHelper::get_paginator($paginator->total_items, $page, $pagesize);
        $char_date = array();
        $char_arr = array(
                array(
                    'title' =>'收益',
                    'data'  =>  array(),
                ),
                 array(
                    'title' =>'点击量',
                    'data'  =>  array(),
                ),
                array(
                    'title' =>'点击率',
                    'data'  =>  array(),
                ),
                array(
                    'title' =>'曝光量',
                    'data'  =>  array(),
                ),
                array(
                    'title' =>'eCPM',
                    'data'  =>  array(),
                )
            );

        $chart_list = $this -> get_repository('Statistics') -> get_chart($this->userinfo['muid'], $start_tm, $end_tm);
        foreach ($chart_list as $key => $val) {
            $char_arr[0]['data'][]  = $val['earnings'];
            $char_arr[1]['data'][]  = $val['click'];
            $char_arr[2]['data'][]  = Common::click_rate($val['click'],$val['exposure']);
            $char_arr[3]['data'][]  = $val['exposure'];
            $char_arr[4]['data'][]  = Common::ecpm($val['exposure'], $val['earnings']);
            $char_date[] = $val['create_date'];
        }

        $count = $paginator->items->count();
        $order = $order?0:1;
        $char_json = json_encode($char_arr);
        $char_date = json_encode($char_date);
        $this -> view -> setVars(array(
            'paginator'	=>	$paginator,
            'pageNum'	=>	$pageNum,
            'list'		=>	$list,
            'start_tm'	=>	$start_tm,
            'end_tm'	=>	$end_tm,
            'page'          =>  $page,
            'pagesize'      =>  $pagesize,
            'cur_page_num'  =>  $count,
            'prefix'        =>  'statistics_md',
            'char_json'     =>  $char_json,
            'char_date'     =>  $char_date,
            'def_min_date'  =>  $def_min_date,
            'def_max_date'  =>  $def_max_date,
            'order_type'    =>  $order_type,
            'order'         =>  $order,
        ));
        $this -> view -> pick('statistics/media');
    }


    /**
     * 媒体每天的列表
     */
    public function mediadaysAction(){
        $page       =   $this -> request -> get('page', 'int', 1);
        $pagesize   =   $this -> request -> get('pagesize', 'int', 10);
        $mmid       =   $this -> request -> get('mmid', 'int');
        $start_tm   =   $this -> request -> get('start_tm', 'trim');
        $end_tm     =   $this -> request -> get('end_tm', 'trim');
        $platform   =   $this -> request -> get('platform', 'trim');
        $order_type =   $this -> request -> get('order_type', 'trim');
        $order      =   $this -> request -> get('order', 'int',0);
        $export     =   $this -> request -> get('export', 'int', 0);
        $media_info = $this -> get_repository('Media') -> get_media_by_id($mmid, $this->userinfo['muid']);
        $paginator = $this -> get_repository('Statistics') -> get_days_list($page, $pagesize, array(
            'muid'      =>  $this->userinfo['muid'],
            'start_tm'  =>  $start_tm,
            'end_tm'    =>  $end_tm,
            'mmid'      =>  $mmid,
            'type'      =>  0,
            'export'    =>  $export,
            'order_type'=>  $order_type,
            'order'     =>  $order,
        ));
        if( $export ) {
            $this -> exportDaysData($paginator);
        }else {
            $list  = $paginator->items->toArray();
        }
        /**获取分页页码*/
        $pageNum = PaginatorHelper::get_paginator($paginator->total_items, $page, $pagesize);
        $order = $order?0:1;
        $count = $paginator->items->count();
        $this -> view -> setVars(array(
            'paginator' =>  $paginator,
            'pageNum'   =>  $pageNum,
            'list'      =>  $list,
            'start_tm'  =>  $start_tm,
            'end_tm'    =>  $end_tm,
            'page'          =>  $page,
            'pagesize'      =>  $pagesize,
            'cur_page_num'  =>  $count,
            'prefix'        =>  'statistics_md',
            'platform'      =>  $platform,
            'media_info'    =>  $media_info,
            'order_type'    =>  $order_type,
            'order'         =>  $order,
        ));
        $this -> view -> pick('statistics/mediadays');
    }

    //广告位
    public function advertAction(){
        $time = time();
        $def_min_date = date("Y-m-d",strtotime("-1 day"));
        $def_max_date = date("Y-m-d",strtotime("-1 day"));
        $page       =   $this -> request -> get('page', 'int', 1);
        $pagesize   =   $this -> request -> get('pagesize', 'int', 10);
        $start_tm   =   $this -> request -> get('start_tm', 'trim', $def_min_date);
        $end_tm     =   $this -> request -> get('end_tm', 'trim', $def_max_date);
        $order_type =   $this -> request -> get('order_type', 'trim');
        $order      =   $this -> request -> get('order', 'int',0);
        $export     =   $this -> request -> get('export', 'int', 0);
        $paginator = $this -> get_repository('Statistics') -> get_list($page, $pagesize, array(
            'muid'      =>  $this->userinfo['muid'],
            'start_tm'  =>  $start_tm,
            'end_tm'    =>  $end_tm,
            'type'      =>  1,
            'export'    =>  $export,
            'order_type'=>  $order_type,
            'order'     =>  $order,
        ));
        if( $export ) {
            $this -> exportAppData($list, 1);
        }else {
            $list  = $paginator->items->toArray();
        }
        $pageNum = PaginatorHelper::get_paginator($paginator->total_items, $page, $pagesize);
        $char_date = array();
        $char_arr = array(
                array(
                    'title' =>'收益',
                    'data'  =>  array(),
                ),
                 array(
                    'title' =>'点击量',
                    'data'  =>  array(),
                ),
                array(
                    'title' =>'点击率',
                    'data'  =>  array(),
                ),
                array(
                    'title' =>'曝光量',
                    'data'  =>  array(),
                ),
                array(
                    'title' =>'eCPM',
                    'data'  =>  array(),
                )

            );
        $chart_list = $this -> get_repository('Statistics') -> get_chart($this->userinfo['muid'], $start_tm, $end_tm);
        foreach ($chart_list as $key => $val) {
            $char_arr[0]['data'][]  =   $val['earnings'];
            $char_arr[1]['data'][]  =   $val['click'];
            $char_arr[2]['data'][]  =   Common::click_rate($val['click'],$val['exposure']);
            $char_arr[3]['data'][]  =   $val['exposure'];
            $char_arr[4]['data'][]  =   Common::ecpm($val['exposure'], $val['earnings']);
            $char_date[] = $val['create_date'];
        }

        $count = $paginator->items->count();

        $char_json = json_encode($char_arr);
        $char_date = json_encode($char_date);
        $order = $order?0:1;
        $this -> view -> setVars(array(
            'paginator' =>  $paginator,
            'pageNum'   =>  $pageNum,
            'list'      =>  $list,
            'start_tm'  =>  $start_tm,
            'end_tm'    =>  $end_tm,
            'page'          =>  $page,
            'pagesize'      =>  $pagesize,
            'cur_page_num'  =>  $count,
            'prefix'        =>  'statistics_ad',
            'char_json'     =>  $char_json,
            'char_date'     =>  $char_date,
            'def_min_date'  =>  $def_min_date,
            'def_max_date'  =>  $def_max_date,
            'order_type'    =>  $order_type,
            'order'         =>  $order,
        ));
        $this -> view -> pick('statistics/advert');
    }


    /**
     * 媒体每天的列表
     */
    public function advertdaysAction(){
        $page       =   $this -> request -> get('page', 'int', 1);
        $pagesize   =   $this -> request -> get('pagesize', 'int', 10);
        $ad_id      =   $this -> request -> get('ad_id', 'int');
        $start_tm   =   $this -> request -> get('start_tm', 'trim');
        $end_tm     =   $this -> request -> get('end_tm', 'trim');
        $platform   =   $this -> request -> get('platform', 'trim');
         $order_type=   $this -> request -> get('order_type', 'trim');
        $order      =   $this -> request -> get('order', 'int',0);
        $export     =   $this -> request -> get('export', 'int', 0);

        $media_info = $this -> get_repository('Position') -> get_position_by_id($ad_id,$this->userinfo['muid']);
        $paginator = $this -> get_repository('Statistics') -> get_days_list($page, $pagesize, array(
            'muid'      =>  $this->userinfo['muid'],
            'start_tm'  =>  $start_tm,
            'end_tm'    =>  $end_tm,
            'ad_id'     =>  $ad_id,
            'type'      =>  1,
            'export'    =>  $export,
            'order_type'=>  $order_type,
            'order'     =>  $order, 
        ));
        if( $export ) {
            $this -> exportAppData($list, 1);
        }else {
            $list  = $paginator->items->toArray();
        }
        $pageNum = PaginatorHelper::get_paginator($paginator->total_items, $page, $pagesize);
        $count = $paginator->items->count();
        $order = $order?0:1;
        $this -> view -> setVars(array(
            'paginator' =>  $paginator,
            'pageNum'   =>  $pageNum,
            'list'      =>  $list,
            'start_tm'  =>  $start_tm,
            'end_tm'    =>  $end_tm,
            'page'          =>  $page,
            'pagesize'      =>  $pagesize,
            'cur_page_num'  =>  $count,
            'prefix'        =>  'statistics_ad',
            'platform'      =>  $platform,
            'media_info'    =>  $media_info,
            'order_type'    =>  $order_type,
            'order'         =>  $order,
        ));
        $this -> view -> pick('statistics/advertdays');
    }

    //媒体或广告位数据
    public function exportAppData($list, $type = 0 ){
        $date_str = date('Y-m-d',time());
        $data = array();
        $count = count($list);
        $exposure = $click = $earnings = 0;
        if( !$type ) {
            //媒体
            $filename = "媒体统计_{$date_str}.csv"; //设置文件名
            $str = '媒体ID,媒体名称,平台,曝光量,点击量,点击率,预期收益,eCPM';
            $str = iconv('utf-8','gb2312', $str);
            $str = $str."\n";
            foreach ( $list as $k => $val ) {
                $data[$k][]     =   $val['mmid'];
                $data[$k][]     =   iconv('utf-8','gb2312', $val['mname']);
                $data[$k][]     =   iconv('utf-8','gb2312', $val['platform']);
                $data[$k][]     =   $val['exposure'];
                $data[$k][]     =   $val['click'];
                $data[$k][]     =   Common::click_rate($val['click'],$val['exposure']).'%';
                $data[$k][]     =   Common::number_format($val['earnings']);
                $data[$k][]     =   Common::ecpm($val['exposure'], $val['earnings']);
                $exposure   += $val['exposure'];
                $click      += $val['click'];
                $earnings   += $val['earnings'];
            }
            if(!empty($list)) {
                $data[$count][] = iconv('utf-8','gb2312', "总计");
                $data[$count][] = "-";
                $data[$count][] = "-";
                $data[$count][] = $exposure;
                $data[$count][] = $click;
                $data[$count][] = Common::click_rate($click,$exposure).'%';
                $data[$count][] = Common::number_format($earnings);
                $data[$count][] = Common::ecpm($exposure, $earnings); 
            }
        }else {
            //广告位
            $filename = "广告位统计_{$date_str}.csv"; //设置文件名
            $str = '广告ID,平台,曝光量,点击量,点击率,预期收益,eCPM';
            $str = iconv('utf-8','gb2312', $str);
            $str = $str."\n";
            foreach ( $list as $k => $val ) {
                $data[$k][]   =   $val['ad_id'];
                $data[$k][]   =   iconv('utf-8','gb2312', $val['platform']);
                $data[$k][]   =   $val['exposure'];
                $data[$k][]   =   $val['click'];
                $data[$k][]   =   Common::click_rate($val['click'],$val['exposure']).'%';
                $data[$k][]   =   Common::number_format($val['earnings']);
                $data[$k][]   =   Common::ecpm($val['exposure'], $val['earnings']);
                $exposure   += $val['exposure'];
                $click      += $val['click'];
                $earnings   += $val['earnings'];
            }
            if(!empty($list)) {
                $data[$count][] = iconv('utf-8','gb2312', "总计");
                $data[$count][] = "-";
                $data[$count][] = $exposure;
                $data[$count][] = $click;
                $data[$count][] = Common::click_rate($click,$exposure).'%';
                $data[$count][] = Common::number_format($earnings);
                $data[$count][] = Common::ecpm($exposure, $earnings); 
            }
        }
        echo $str;
        $this -> export_csv($filename, $data);
    }

    //每日数据
    public function exportDaysData($list, $type = 0 ){
        $count = count($list);
        $exposure = $click = $earnings = 0;
        if( !$type ) {
            $filename = '媒体每日统计数据.csv'; //设置文件名
            $str = '日期,曝光量,点击量,点击率,预期收益,eCPM';
            $str = iconv('utf-8','gb2312', $str);
            $str = $str."\n";
            foreach ( $list as $k => $val ) {
                $data[$k][]   =   iconv('utf-8','gb2312', $val['create_date']);
                $data[$k][]   =   $val['exposure'];
                $data[$k][]   =   $val['click'];
                $data[$k][]   =   Common::click_rate($val['click'],$val['exposure']).'%';
                $data[$k][]   =   Common::number_format($val['earnings']);
                $data[$k][]   =   Common::ecpm($val['exposure'], $val['earnings']);
                $exposure   += $val['exposure'];
                $click      += $val['click'];
                $earnings   += $val['earnings'];
            }
        }else {
            //广告位
            $filename = '广告位每日统计数据.csv'; //设置文件名
            $str = '日期,曝光量,点击量,点击率,预期收益,eCPM';
            $str = iconv('utf-8','gb2312', $str);
            $str = $str."\n";
            $data = array();
            foreach ( $list as $k => $val ) {
                $data[$k][]   =   iconv('utf-8','gb2312', $val['create_date']);
                $data[$k][]   =   $val['exposure'];
                $data[$k][]   =   $val['click'];
                $data[$k][]   =   Common::click_rate($val['click'],$val['exposure']).'%';
                $data[$k][]   =   Common::number_format($val['earnings']);
                $data[$k][]   =   Common::ecpm($val['exposure'], $val['earnings']);
                $exposure   += $val['exposure'];
                $click      += $val['click'];
                $earnings   += $val['earnings'];
            }
        }
        if(!empty($list)) {
            $data[$count][] = iconv('utf-8','gb2312', "总计");
            $data[$count][] = $exposure;
            $data[$count][] = $click;
            $data[$count][] = Common::click_rate($click,$exposure);
            $data[$count][] = Common::number_format($earnings);
            $data[$count][] = Common::ecpm($exposure, $earnings); 
        }
        echo $str;
        $this -> export_csv($filename, $data);
    }

   function export_csv($filename, $data){
        $fp = fopen('php://output', 'w');
        foreach ($data as $val) {
            fputcsv($fp, $val);
        }
        fclose($fp);
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=".$filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');die;
    }

}