<?php

/**
 * 广告创意控制器
 * @category PhalconDSP
 * @author haoshisuo 2017-10-16
 */

namespace Marser\App\Frontend\Controllers;

use \Marser\App\Frontend\Controllers\BaseController;
use \Marser\App\Helpers\PaginatorHelper;
use \Marser\App\Frontend\Models\ModelFactory;

class IdeaController extends BaseController{

    public function initialize(){
        parent::initialize();
        $this -> view -> setVars(array(
            'prefix' => 'idea',
        ));
    }

    /**
     * 广告创意列表
     */
    public function listAction(){
        $page       =  intval($this -> request -> get('page', 'trim')); //当前页
        $prize_name =  $this -> request -> get('prize_name', 'trim'); //奖品名称
        $condition = array(
            'prize_name'    =>  $prize_name,
            'auid'          =>  $this -> session -> get('user')['auid'],
        );
        $sess = intval($this -> request -> get('sess', 'trim')); //判断是否保留之前查询条件
        if(isset($sess) && !empty($sess) && $this -> session -> has('idea_list')){
            $condition = $this -> session -> get('idea_list');
        }else{
            $this -> session -> set('idea_list', $condition);
        }
        //分页获取列表
        $pagesize = 10;
        $paginator = ModelFactory::get_model('IdeaModel') -> get_list($page, $pagesize, $condition);
        //获取分页页码
        $pageNum = PaginatorHelper::get_paginator($paginator->total_items, $page, $pagesize);
        $results = $paginator->items->toArray();
        $this -> view -> setVars(array(
            'page'          =>  $page,
            'pagesize'      =>  $pagesize,
            'paginator'     =>  $paginator,
            'pageNum'       =>  $pageNum,
            'prize_name'    =>  $condition['prize_name'],
            'results'       =>  $results,
        ));
        $this -> view -> pick('idea/list');
    }

    /**
     * 添加、编辑广告创意页面
     */
    public function addAction(){
        try{
            $idea_id = intval($this -> request -> get('idea_id', 'trim'));
            if(!empty($idea_id)){
                //广告创意信息
                $list = $this -> get_repository('Idea') -> get_One($idea_id);
                if($list['examine_status'] != 3 || $list['auid'] != $this -> session -> get('user')['auid'] ){
                    throw new \Exception('非法操作');
                }
                $this -> view -> setVars(array(
                    'list' => $list,
                ));
            }
            $this -> view -> pick('idea/add');
        }catch(\Exception $e){
            $this -> write_exception_log($e);
            $this -> flashSession -> error($e -> getMessage());
            return $this -> redirect('idea/list');
        }
    }

    /**
     * 查看广告创意页面
     */
    public function editAction(){
        try{
            $idea_id = intval($this -> request -> get('idea_id', 'trim'));
            if(!empty($idea_id)){
                //广告创意信息
                $list = $this -> get_repository('Idea') -> get_One($idea_id);
                if($list['examine_status'] != 2 || $list['auid'] != $this -> session -> get('user')['auid'] ){
                    throw new \Exception('非法操作');
                }
                $this -> view -> setVars(array(
                    'list' => $list,
                ));
                $this -> view -> pick('idea/edit');
            }else{
                throw new \Exception('参数错误');
            }
        }catch(\Exception $e){
            $this -> write_exception_log($e);
            $this -> flashSession -> error($e -> getMessage());
            return $this -> redirect('idea/list');
        }
    }

    /**
     * 创建、编辑广告创意逻辑处理
     */
    public function writeAction(){
        try{
            if($this -> request -> isAjax() || !$this -> request -> isPost()){
                throw new \Exception('非法请求');
            }
            $idea_id = intval($this -> request -> get('idea_id', 'trim')); //判断添加还是编辑
            if(!empty($idea_id)){
                //广告创意信息
                $list = $this -> get_repository('Idea') -> get_One($idea_id);
                if($list['examine_status'] != 3 || $list['auid'] != $this -> session -> get('user')['auid'] ){
                    throw new \Exception('非法操作');
                }
            }
            $prize_name = $this -> request -> getPost('prize_name', 'trim'); //奖品名称
            $prize_name        =   $this -> filter  -> sanitize($prize_name, 'remove_xss');
            $ad_image = $this -> request -> getPost('ad_image', 'trim', ''); //图片1旧值
            $ad_image2 = $this -> request -> getPost('ad_image2', 'trim', ''); //图片2旧值
            $coupon_term = $this -> request -> getPost('coupon_term', 'trim', ''); //优惠券有效期
            $push_link = $this -> request -> getPost('push_link', 'trim'); //推广链接
            $push_link        =   $this -> filter  -> sanitize($push_link, 'remove_xss');
            $prize_desc = $this -> request -> getPost('prize_desc', 'trim'); //奖品说明
            $prize_desc        =   $this -> filter  -> sanitize($prize_desc, 'remove_xss');
            $coupon_step = $this -> request -> getPost('coupon_step', 'trim'); //使用流程
            $coupon_step        =   $this -> filter  -> sanitize($coupon_step, 'remove_xss');
            $coupon_rule = $this -> request -> getPost('coupon_rule', 'trim'); //使用规则
            $coupon_rule        =   $this -> filter  -> sanitize($coupon_rule, 'remove_xss');

            /** 添加验证规则 */
            $this -> validator -> add_rule('prize_name', 'required', '请填写奖品名称')
            -> add_rule('prize_name', 'max_length', '奖品名称不能超过30个字符', 30)
            -> add_rule('prize_name', 'min_length', '奖品名称不能少于3个字符', 3);
            $this -> validator -> add_rule('push_link', 'required', '请填写推广链接')
            -> add_rule('push_link', 'url', '推广链接格式错误');
            $this -> validator -> add_rule('prize_desc', 'required', '请填写奖品说明');
            $this -> validator -> add_rule('coupon_step', 'required', '请填写使用流程');
            $this -> validator -> add_rule('coupon_rule', 'required', '请填写使用规则');
            /** 截获验证异常 */
            if ($error = $this -> validator -> run(array(
                'prize_name' => $prize_name,
                'push_link' => $push_link,
                'prize_desc' => $prize_desc,
                'coupon_step' => $coupon_step,
                'coupon_rule' => $coupon_rule,
            ))) {
                $error = array_values($error);
                $error = $error[0];
                throw new \Exception($error['message'], $error['code']);
            }
            if(empty($ad_image)){
                $ad_image = PaginatorHelper::deal_image('prize_image',640,300,'广告图片1','jpg|png|jpeg|gif');
            }
            if(empty($ad_image2)){
                $ad_image2 = PaginatorHelper::deal_image('prize_image2',0,0,'广告图片2','jpg|png|jpeg|gif');
            }
            if(!empty($coupon_term)){
                $coupon_term = strtotime($coupon_term);
            }
            $map = array(
                'prize_name'    =>  $prize_name,
                'ad_image'      =>  $ad_image,
                'ad_image2'     =>  $ad_image2,
                'coupon_term'   =>  $coupon_term,
                'push_link'     =>  $push_link,
                'prize_desc'    =>  $prize_desc,
                'coupon_step'   =>  $coupon_step,
                'coupon_rule'   =>  $coupon_rule,
                'examine_status'=>  1,
                'auid'          =>  $this -> session -> get('user')['auid'],
            );
            $lastId = $this -> get_repository('Idea') -> save($map, $idea_id);
            $map_json = json_encode($_POST);
            if(empty($idea_id)){
                $this -> writelog("创建了id为{$lastId}的广告创意>>>{$map_json}");
            }else{
                $this -> writelog("修改了id为{$idea_id}的广告创意>>>{$map_json}");
            }
            $this -> flashSession -> success('提交广告创意成功');
        }catch(\Exception $e){
            $this -> write_exception_log($e);
            $this -> flashSession -> error($e -> getMessage());
        }
        if(empty($idea_id)){
            return $this -> redirect('idea/list');
        }else{
            return $this -> redirect('idea/list?sess=1');
        }
    }

    //检查奖品名称是否存在
    public function check_nameAction(){
        try{
            if(!$this -> request -> isAjax()){
                throw new \Exception('非法请求');
            }
            $id = $this -> request -> get('id', 'trim');
            $prize_name = $this -> request -> get('prize_name', 'trim');
            $ext['prize_name'] = $prize_name;
            $ext['auid'] = $this -> session -> get('user')['auid'];
            $return = $this -> get_repository('Idea') -> check_name($ext, $id, 1);
            $this -> ajax_return('返回成功', $return);
        }catch(\Exception $e){
            $this -> write_exception_log($e);
            if( $this -> request -> isAjax() ) {
                $this -> ajax_return($e -> getMessage(), 0);
            }else {
                $this -> flashSession -> error($e -> getMessage());
                return $this -> redirect('idea/list');
            }
        }
        $this -> view -> disable();
    }
}