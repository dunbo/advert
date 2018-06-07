<?php

/**
 * 财务信息控制器
 * @category PhalconDSP
 * @author haoshisuo 2017-9-14
 */

namespace Marser\App\Frontend\Controllers;

use \Marser\App\Frontend\Controllers\BaseController;
use \Marser\App\Frontend\Models\ModelFactory;
use \Marser\App\Helpers\PaginatorHelper;

class FinanceController extends BaseController{

    public function initialize(){
        parent::initialize();
        $this -> view -> setVars(array(
            'prefix' => 'record',
        ));
    }

    /**
     * 财务记录
     */
    public function recordAction(){
        $page       =  intval($this -> request -> get('page', 'trim', 1)); //当前页
        $pagesize   =  intval($this -> request -> get('pagesize', 'trim', 20)); //当前页
        $type       =  $this -> request -> get('type', 'trim', 'recharge');
        $begin_tm   =  $this -> request -> get('begin_tm', 'trim');
        $end_tm     =  $this -> request -> get('end_tm', 'trim');
        $ext['auid'] = $this -> session -> get('user')['auid'];
        $ext['begin_tm'] = strtotime($begin_tm);
        $ext['end_tm'] = strtotime($end_tm);
        //分页获取列表
        //账户余额
        $balance = ModelFactory::get_model('BalanceModel') -> get_balance($ext['auid']);
        if($type == 'recharge'){
            //充值记录
            $paginator = ModelFactory::get_model('RechargeModel') -> get_list($page, $pagesize, $ext);
        }elseif($type == 'audit'){
            //消费记录
            $paginator = ModelFactory::get_model('AuditModel') -> get_list($page, $pagesize, $ext);
        }
        $count = $paginator->items->count();
        //获取分页页码
        $pageNum = PaginatorHelper::get_paginator($paginator->total_items, $page, $pagesize);
        $results = $paginator->items->toArray();
        $this -> view -> setVars(array(
            'page'      =>  $page,
            'type'      =>  $type,
            'begin_tm'  =>  $begin_tm,
            'end_tm'    =>  $end_tm,
            'pagesize'  =>  $pagesize,
            'paginator' =>  $paginator,
            'pageNum'   =>  $pageNum,
            'results'   =>  $results,
            'balance'   =>  $balance['balance'],
            'cur_page_num'      =>  $count,
        ));
        $this -> view -> pick('finance/record');
    }
}