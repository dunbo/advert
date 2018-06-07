<?php

/**
 * 广告数据控制器
 * @category PhalconDSP
 * @author haoshisuo 2017-9-14
 */

namespace Marser\App\Backend\Controllers;

use \Marser\App\Backend\Controllers\BaseController;
use \Marser\App\Helpers\PaginatorHelper;

class DataController extends BaseController{

    public function initialize(){
        parent::initialize();
    }

    /**
     * 广告数据列表
     */
    public function listAction(){
        $page           =  $this -> request -> get('page', 'int', 1); //当前页
        $pagesize       =   $this -> request -> get('pagesize', 'int', 10);
        $materiel_name  =  $this -> request -> get('materiel_name', 'trim'); //广告名称
        $ad_name   =  $this -> request -> get('ad_name', 'trim'); //公司名称
        $begin_tm       =  $this -> request -> get('begin_tm', 'trim');
        $end_tm         =  $this -> request -> get('end_tm', 'trim');
        $ext['materiel_name'] = $materiel_name;
        $ext['ad_name'] = $ad_name;
        $ext['begin_tm'] = $begin_tm;
        $ext['end_tm'] = $end_tm;
        $from = intval($this -> request -> get('from', 'trim')); //判断是否导出报表操作
        if(isset($from) && !empty($from) && $this -> session -> has('data_list')){
            $ext = $this -> session -> get('data_list');
        }else{
            $this -> session -> set('data_list', $ext);
        }
        //分页获取列表
        $paginator = $this -> get_repository('Data') -> get_list($page, $pagesize, $ext);
        //获取分页页码
        $pageNum = PaginatorHelper::get_paginator($paginator->total_items, $page, $pagesize);
        $count = $paginator->items->count();
        $results = $paginator->items->toArray();
        //导出报表
        if(isset($from) && !empty($from) && $this-> session ->has('data_list')){
            $php = $this -> get_repository('Data') -> download_csv($results);
        }
        $this -> view -> setVars(array(
            'title'         =>  '广告数据列表',
            'page'          =>  $page,
            'begin_tm'      =>  $begin_tm,
            'end_tm'        =>  $end_tm,
            'pagesize'      =>  $pagesize,
            'paginator'     =>  $paginator,
            'pageNum'       =>  $pageNum,
            'materiel_name' =>  $materiel_name,
            'ad_name'  =>  $ad_name,
            'list'          =>  $results,
            'cur_page_num'  =>  $count,
        ));
        $this -> view -> pick('data/list');
    }
    
}