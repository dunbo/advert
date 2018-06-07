<?php
/**
 * 首页
 */
namespace Marser\App\Backend\Controllers;

use \Marser\App\Backend\Controllers\BaseController,
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
        $def_min_date = date("Y-m-d",strtotime("-1 day"));
        $page		=	$this -> request -> get('page', 'int', 1);
        $pagesize   =   $this -> request -> get('pagesize', 'int', 10);
        $mname      =   $this -> request -> get('mname', 'trim');
        $start_tm	=	$this -> request -> get('start_tm', 'trim',$def_min_date);
        $end_tm		=	$this -> request -> get('end_tm', 'trim',$def_min_date);
        $order_type =   $this -> request -> get('order_type', 'trim');
        $order      =   $this -> request -> get('order', 'int',0);
        $export     =   $this -> request -> get('export', 'int', 0);
        $paginator = $this -> get_repository('Statistics') -> get_list($page, $pagesize, array(
            'mname'     =>  $mname,
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
        $pageNum = PaginatorHelper::get_paginator($paginator->total_items, $page, $pagesize);
        $count = $paginator->items->count();
        //获取总计
        $tatol = $this -> get_repository('Statistics') -> get_list_count(array(
            'mname'     =>  $mname,
            'start_tm'  =>  $start_tm,
            'end_tm'    =>  $end_tm,
        ));
        //print_r($_GET);die;
        $order = $order?0:1;
        $this -> view -> setVars(array(
            'title'     =>  '媒体数据',
            'paginator'	=>	$paginator,
            'pageNum'	=>	$pageNum,
            'list'		=>	$list,
            'start_tm'	=>	$start_tm,
            'end_tm'	=>	$end_tm,
            'mname'     =>  $mname,
            'page'          =>  $page,
            'pagesize'      =>  $pagesize,
            'cur_page_num'  =>  $count,
            'order_type'    =>  $order_type,
            'order'         =>  $order,
            'tatol'         =>  $tatol,
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
        $mname      =   $this -> request -> get('mname', 'trim');
        $start_tm   =   $this -> request -> get('start_tm', 'trim');
        $end_tm     =   $this -> request -> get('end_tm', 'trim');
        $order_type =   $this -> request -> get('order_type', 'trim');
        $order      =   $this -> request -> get('order', 'int',0);
        $export     =   $this -> request -> get('export', 'int', 0);

        $paginator = $this -> get_repository('Statistics') -> get_days_list($page, $pagesize, array(
            'mname'     =>  $mname,
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
        $order = $order?0:1;
        $pageNum = PaginatorHelper::get_paginator($paginator->total_items, $page, $pagesize);
        $count = $paginator->items->count();

        //获取总计
        $tatol = $this -> get_repository('Statistics') -> get_days_list_count(array(
            'mmid'      =>  $mmid,
            'mname'     =>  $mname,
            'start_tm'  =>  $start_tm,
            'end_tm'    =>  $end_tm,
        ));
        $this -> view -> setVars(array(
            'title'     =>  '媒体每日统计',
            'paginator' =>  $paginator,
            'pageNum'   =>  $pageNum,
            'mmid'      =>  $mmid,
            'list'      =>  $list,
            'start_tm'  =>  $start_tm,
            'end_tm'    =>  $end_tm,
            'mname'     =>  $mname,
            'page'          =>  $page,
            'pagesize'      =>  $pagesize,
            'cur_page_num'  =>  $count,
            'order_type'    =>  $order_type,
            'order'         =>  $order,
            'tatol'         =>  $tatol,
        ));
        $this -> view -> pick('statistics/mediadays');
    }


    //广告位
    public function advertAction(){
        $def_min_date = date("Y-m-d",strtotime("-1 day"));
        $page       =   $this -> request -> get('page', 'int', 1);
        $pagesize   =   $this -> request -> get('pagesize', 'int', 10);
        $mname      =   $this -> request -> get('mname', 'trim');
        $aname      =   $this -> request -> get('aname', 'trim');
        $start_tm   =   $this -> request -> get('start_tm', 'trim',$def_min_date);
        $end_tm     =   $this -> request -> get('end_tm', 'trim',$def_min_date);
        $order_type =   $this -> request -> get('order_type', 'trim');
        $order      =   $this -> request -> get('order', 'int',0);
        $export     =   $this -> request -> get('export', 'int', 0);
        $paginator = $this -> get_repository('Statistics') -> get_list($page, $pagesize, array(
            'mname'     =>  $mname,
            'aname'     =>  $aname,
            'start_tm'  =>  $start_tm,
            'end_tm'    =>  $end_tm,
            'type'      =>  1,
            'export'    =>  $export,
            'order_type'=>  $order_type,
            'order'     =>  $order,
        ));
        if( $export ) {
            $this -> exportAppData($paginator,1);
        }else {
            $list  = $paginator->items->toArray();
        }
        $pageNum = PaginatorHelper::get_paginator($paginator->total_items, $page, $pagesize);
        $count = $paginator->items->count();

        //获取总计
        $tatol = $this -> get_repository('Statistics') -> get_list_count(array(
            'mname'     =>  $mname,
            'aname'     =>  $aname,
            'start_tm'  =>  $start_tm,
            'end_tm'    =>  $end_tm,
        ));

        $order = $order?0:1;
        $this -> view -> setVars(array(
            'title'     =>  '广告位数据',
            'paginator' =>  $paginator,
            'pageNum'   =>  $pageNum,
            'list'      =>  $list,
            'mname'     =>  $mname,
            'aname'     =>  $aname,
            'start_tm'  =>  $start_tm,
            'end_tm'    =>  $end_tm,
            'page'          =>  $page,
            'pagesize'      =>  $pagesize,
            'cur_page_num'  =>  $count,
            'order_type'    =>  $order_type,
            'order'         =>  $order,
            'tatol'         =>  $tatol,
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
        $aname      =   $this -> request -> get('aname', 'trim');
        $start_tm   =   $this -> request -> get('start_tm', 'trim');
        $end_tm     =   $this -> request -> get('end_tm', 'trim');
        $order_type =   $this -> request -> get('order_type', 'trim');
        $order      =   $this -> request -> get('order', 'int',0);
        $export     =   $this -> request -> get('export', 'int', 0);

        $paginator = $this -> get_repository('Statistics') -> get_days_list($page, $pagesize, array(
            'aname'     =>  $aname,
            'start_tm'  =>  $start_tm,
            'end_tm'    =>  $end_tm,
            'ad_id'     =>  $ad_id,
            'type'      =>  1,
            'export'    =>  $export,
            'order_type'=>  $order_type,
            'order'     =>  $order,
            'order_type'    =>  $order_type,
            'order'         =>  $order,
        ));
        if( $export ) {
            $this -> exportDaysData($paginator,1);
        }else {
            $list  = $paginator->items->toArray();
        }
        $pageNum = PaginatorHelper::get_paginator($paginator->total_items, $page, $pagesize);
        $count = $paginator->items->count();

         //获取总计
        $tatol = $this -> get_repository('Statistics') -> get_days_list_count(array(
            'ad_id'     =>  $ad_id,
            'aname'     =>  $aname,
            'start_tm'  =>  $start_tm,
            'end_tm'    =>  $end_tm,
        ));
        $order = $order?0:1;
        $this -> view -> setVars(array(
            'title'     =>  '广告位每日统计',
            'paginator' =>  $paginator,
            'pageNum'   =>  $pageNum,
            'list'      =>  $list,
            'start_tm'  =>  $start_tm,
            'end_tm'    =>  $end_tm,
            'aname'     =>  $aname,
            'ad_id'     =>  $ad_id,
            'page'          =>  $page,
            'pagesize'      =>  $pagesize,
            'cur_page_num'  =>  $count,
            'order_type'    =>  $order_type,
            'order'         =>  $order,
            'tatol'         =>  $tatol,
        ));
        $this -> view -> pick('statistics/advertdays');
    }

     //导出数据格式化
    public function exportAppData($list, $type = 0 ){
        $date_str = date('Y-m-d',time());
        $data = array();
        $count = count($list);
        $exposure = $click = $earnings = 0;
        if( !$type ) {
            //媒体
            $filename = "媒体统计_{$date_str}.csv"; //设置文件名
            $str = '媒体ID,媒体名称,平台,曝光量,点击量,点击率,eCPM,预期收益';
            $str = iconv('utf-8','GBK//IGNORE', $str);
            $str = $str."\n";
            foreach ( $list as $k => $val ) {
                $data[$k][]     =   $val['mmid'];
                $data[$k][]     =   iconv('utf-8','GBK//IGNORE', $val['mname']);
                $data[$k][]     =   iconv('utf-8','GBK//IGNORE', $val['platform']);
                $data[$k][]     =   $val['exposure'];
                $data[$k][]     =   $val['click'];
                $data[$k][]     =   Common::click_rate($val['click'],$val['exposure']).'%';
                $data[$k][]     =   Common::ecpm($val['exposure'], $val['earnings']);
                $data[$k][]     =   Common::number_format($val['earnings']);
                $exposure   += $val['exposure'];
                $click      += $val['click'];
                $earnings   += $val['earnings'];
            }
            if(!empty($list)) {
                $data[$count][] = iconv('utf-8','GBK//IGNORE', "总计");
                $data[$count][] = "-";
                $data[$count][] = "-";
                $data[$count][] = $exposure;
                $data[$count][] = $click;
                $data[$count][] = Common::click_rate($click,$exposure);
                $data[$count][] = Common::ecpm($exposure, $earnings); 
                $data[$count][] = Common::number_format($earnings);
            }
        }else {
            //广告位
            $filename = "广告位统计_{$date_str}.csv"; //设置文件名
            $str = '广告ID,平台,曝光量,点击量,点击率,eCPM,预期收益';
            $str = iconv('utf-8','GBK//IGNORE', $str);
            $str = $str."\n";
            foreach ( $list as $k => $val ) {
                $data[$k][]   =   $val['ad_id'];
                $data[$k][]   =   iconv('utf-8','GBK//IGNORE', $val['platform']);
                $data[$k][]   =   $val['exposure'];
                $data[$k][]   =   $val['click'];
                $data[$k][]   =   Common::click_rate($val['click'],$val['exposure']).'%';
                $data[$k][]   =   Common::ecpm($val['exposure'], $val['earnings']);
                $data[$k][]   =   Common::number_format($val['earnings']);
                $exposure   += $val['exposure'];
                $click      += $val['click'];
                $earnings   += $val['earnings'];
            }
            if(!empty($list)) {
                $data[$count][] = iconv('utf-8','GBK//IGNORE', "总计");
                $data[$count][] = "-";
                $data[$count][] = $exposure;
                $data[$count][] = $click;
                $data[$count][] = Common::click_rate($click,$exposure).'%';
                $data[$count][] = Common::ecpm($exposure, $earnings);    
                $data[$count][] = Common::number_format($earnings);
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
            $str = '日期,曝光量,点击量,点击率,eCPM,预期收益';
            $str = iconv('utf-8','GBK//IGNORE', $str);
            $str = $str."\n";
            foreach ( $list as $k => $val ) {
                $data[$k][]   =   iconv('utf-8','GBK//IGNORE', $val['create_date']);
                $data[$k][]   =   $val['exposure'];
                $data[$k][]   =   $val['click'];
                $data[$k][]   =   Common::click_rate($val['click'],$val['exposure']).'%';
                $data[$k][]   =   Common::ecpm($val['exposure'], $val['earnings']);
                $data[$k][]   =   Common::number_format($val['earnings']);
                $exposure   += $val['exposure'];
                $click      += $val['click'];
                $earnings   += $val['earnings'];
            }
        }else {
            //广告位
            $filename = '广告位每日统计数据.csv'; //设置文件名
            $str = '日期,曝光量,点击量,点击率,eCPM,预期收益';
            $str = iconv('utf-8','GBK//IGNORE', $str);
            $str = $str."\n";
            $data = array();
            foreach ( $list as $k => $val ) {
                $data[$k][]   =   iconv('utf-8','GBK//IGNORE', $val['create_date']);
                $data[$k][]   =   $val['exposure'];
                $data[$k][]   =   $val['click'];
                $data[$k][]   =   Common::click_rate($val['click'],$val['exposure']).'%';
                $data[$k][]   =   Common::ecpm($val['exposure'], $val['earnings']);
                $data[$k][]   =   Common::number_format($val['earnings']);
                $exposure   += $val['exposure'];
                $click      += $val['click'];
                $earnings   += $val['earnings'];
            }
        }
        if(!empty($list)) {
            $data[$count][] = iconv('utf-8','GBK//IGNORE', "总计");
            $data[$count][] = $exposure;
            $data[$count][] = $click;
            $data[$count][] = Common::click_rate($click,$exposure);
            $data[$count][] = Common::ecpm($exposure, $earnings);    
            $data[$count][] = Common::number_format($earnings);
        }
        echo $str;
        $this -> export_csv($filename, $data);
    }


    //广告位导出
    public function ad_exportAction() {
        $def_min_date = date("Y-m-d",strtotime("-1 day"));
        $page       =   $this -> request -> get('page', 'int', 1);
        $pagesize   =   $this -> request -> get('pagesize', 'int', 10);
        $mname      =   $this -> request -> get('mname', 'trim');
        $aname      =   $this -> request -> get('aname', 'trim');
        $start_tm   =   $this -> request -> get('start_tm', 'trim',$def_min_date);
        $end_tm     =   $this -> request -> get('end_tm', 'trim',$def_min_date);
        $day        =   $this -> request -> get('day', 'int', 0);
        $list = $this -> get_repository('Statistics') -> get_ad_list($page, $pagesize, array(
            'mname'     =>  $mname,
            'aname'     =>  $aname,
            'start_tm'  =>  $start_tm,
            'end_tm'    =>  $end_tm,
            'day'      =>   $day,
        ));
        foreach ($list as $key => $val) {
            if($val['t_pid']) {
                $temp_parent_info = $this -> get_repository('Template')->get_info($val['t_pid']);
                $list[$key]['temp_parent_name']  = isset($temp_parent_info['name'])?$temp_parent_info['name']:'';
            }
            if($val['t_id']) {
                $temp_sub_info = $this -> get_repository('Template')->get_info($val['t_id']);
                $list[$key]['temp_sub_name']  = isset($temp_sub_info['name'])?$temp_sub_info['name']:'';
            }
        }

        $date_str = date('Y-m-d',time());
        $filename = "广告位统计_{$date_str}.csv"; //设置文件名
        $str = '日期,投放方式,媒体主信息,媒体信息,广告类型,广告位名称,广告位ID,曝光量,点击量,点击率,eCPM,收益'."\n";
        $str = iconv('utf-8','GBK//IGNORE', $str);
        $data = array();
        $count = count($list);
        $exposure = $click = $earnings = 0;
        foreach ( $list as $k => $val ) {
            $i = $k;
            $data[$k][]   = $val['create_date'];
            if($val['type'] == 1) {
                if($val['tf_type'] == 1) {
                    $data[$k][] = "SDK";
                }elseif($val['tf_type'] == 2){
                    if($val['api_type'] == 1) {
                        $data[$k][] = iconv('utf-8','GBK//IGNORE',"通用API");
                    }elseif($val['api_type'] == 2){
                        $data[$k][] = iconv('utf-8','GBK//IGNORE',"互动API");
                    }
                }else{
                    $data[$k][] = iconv('utf-8','GBK//IGNORE',"无");
                }
            }elseif($val['type'] == 2) {
                $data[$k][] = iconv('utf-8','GBK//IGNORE',"商业内容API");
            }

            $data[$k][]    = @iconv('utf-8','GBK//IGNORE', $val['media_name']);
            $data[$k][]    = $val['mname']?@iconv('utf-8','GBK//IGNORE', $val['mname']):"";
            if($val['type']==1 && $val['tf_type'] == 2 && $val['api_type'] == 2){
                $temp_name  = "默认";
            }else{
                $temp_name  = isset($val['temp_parent_name'])?$val['temp_parent_name']."({$val['temp_sub_name']})":'';
            }
            $data[$k][]    = @iconv('utf-8','GBK//IGNORE', $temp_name);
            $data[$k][]    = $val['aname']?@iconv('utf-8','GBK//IGNORE', $val['aname']):'';
            $data[$k][]    = $val['ad_id'];
            $data[$k][]    =   $val['exposure'];
            $data[$k][]    =   $val['click'];
            $data[$k][]    =   Common::click_rate($val['click'],$val['exposure']).'%';
            $data[$k][]    =   Common::ecpm($val['exposure'], $val['earnings']);
            $data[$k][]    =   Common::number_format($val['earnings']);
            $exposure   += $val['exposure'];
            $click      += $val['click'];
            $earnings   += $val['earnings'];
        }
        if(!empty($list)) {
            $data[$count][] = iconv('utf-8','GBK//IGNORE', "总计");
            $data[$count][] = "-";
            $data[$count][] = "-";
            $data[$count][] = "-";
            $data[$count][] = "-";
            $data[$count][] = "-";
            $data[$count][] = "-";
            $data[$count][] = $exposure;
            $data[$count][] = $click;
            $data[$count][] = Common::click_rate($click,$exposure).'%';
            $data[$count][] = Common::ecpm($exposure, $earnings);
            $data[$count][] = Common::number_format($earnings);
        }
        echo $str;
        $this->export_csv($filename, $data);
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