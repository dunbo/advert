<?php

/**
 * 广告数据仓库
 * @category PhalconDSP
 * @author haoshisuo 2017-9-19
 */

namespace Marser\App\Frontend\Repositories;

use \Marser\App\Frontend\Repositories\BaseRepository;

class Data extends BaseRepository{

    public function __construct(){
        parent::__construct();
    }

    /**
     * 获取广告数据列表
     * @param int $page
     * @param int $pagesize
     * @param array $ext
     * @return mixed
     */
    public function get_list($page, $pagesize=10, array $ext=array()){
        $result = $this -> get_model('DataModel') -> get_list($page, $pagesize, $ext);
        return $result;
    }

    //广告数据列表导出报表操作
    public function download_csv($results){
        $filename = iconv('utf-8', 'gb2312', '广告统计数据.csv');
        $str = '广告计划名称,广告主名称,时间,曝光量(次),点击量(次),点击率,点击均价(元),总消耗(元)';
        $str = iconv('utf-8','gb2312', $str);
        $str = $str."\n";
        foreach($results as $val){
            $ad_name = iconv('utf-8','gb2312', $val['ad_name']);
            $client_name = iconv('utf-8','gb2312', $val['client_name']);
            $create_date = $val['create_date'];
            $exposure = $val['exposure'];
            $click_num = $val['click_num'];
            $pv = $val['pv'];
            $click_price = $val['click_price']/100;
            $total = $val['total']/100;
            $str .=  $ad_name.",".$client_name.",".$create_date.",".$exposure.",".$click_num.",".$pv.",".$click_price.",".$total.",\n";
        }
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=".$filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $str;exit;
    }

    /**
    * 获取chart
    */
    public function get_chart($auid, $ext=array()) {
        return $this -> get_model('DataModel') -> get_chart($auid, $ext);
    }

    public function get_count($auid, $ext=array()) {
        return $this -> get_model('DataModel') -> get_count($auid, $ext);
    }

    public function get_list_export(array $ext=array()){
        return $this -> get_model('DataModel') -> get_list_export($ext);
    }
}