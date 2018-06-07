<?php

/**
 * 广告数据控制器
 * @category PhalconDSP
 * @author haoshisuo 2017-9-14
 */

namespace Marser\App\Frontend\Controllers;

use \Marser\App\Frontend\Controllers\BaseController;
use \Marser\App\Helpers\PaginatorHelper;

class DataController extends BaseController{

    public function initialize(){
        parent::initialize();
        $this -> view -> setVars(array(
            'prefix' => 'data',
        ));
    }

    /**
     * 广告数据列表
     */
    public function listAction(){
        $page       =  intval($this -> request -> get('page', 'trim', 1)); //当前页
        $pagesize   =  intval($this -> request -> get('pagesize', 'trim', 10)); //当前页
        $materiel_name  =  $this -> request -> get('materiel_name', 'trim'); //广告名称
        $tag            =  intval($this -> request -> get('tag', 'trim'));
        $begin_tm       =  $this -> request -> get('begin_tm', 'trim');
        $end_tm         =  $this -> request -> get('end_tm', 'trim');
        if(empty($tag)){
            if(empty($begin_tm) && empty($end_tm)){
                $tag = 2; //默认统计的数据
            }else{
                $tag = 0;
            }
        }
        $ext['materiel_name'] = $materiel_name;
        $ext['auid'] = $this -> session -> get('user')['auid'];
        $ext['tag'] = $tag;
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
        $results = $paginator->items->toArray();
        $count = $paginator->items->count();
        //导出报表
        if(isset($from) && !empty($from) && $this-> session ->has('data_list')){
            $php = $this -> get_repository('Data') -> download_csv($results);
        }
        $this -> view -> setVars(array(
            'page'          =>  $page,
            'begin_tm'      =>  $ext['begin_tm'],
            'end_tm'        =>  $ext['end_tm'],
            'tag'           =>  $ext['tag'],
            'pagesize'      =>  $pagesize,
            'paginator'     =>  $paginator,
            'pageNum'       =>  $pageNum,
            'materiel_name' =>  $ext['materiel_name'],
            'results'       =>  $results,
            'cur_page_num'  =>  $count,
        ));
        $this -> view -> pick('data/list');
    }
    
}