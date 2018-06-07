<?php

/**
 * 广告创意控制器
 * @category PhalconHonor
 * @author haoshisuo 2017-10-19
 */

namespace Marser\App\Backend\Controllers;

use \Marser\App\Backend\Controllers\BaseController;
use \Marser\App\Helpers\PaginatorHelper;
use \Marser\App\Backend\Models\ModelFactory;

class IdeaController extends BaseController{

    // 广告创意列表
    public function listAction(){
        $page           =  $this -> request -> get('page', 'int', 1);
        $pagesize       =   $this -> request -> get('pagesize', 'int', 10);
        $ad_name   =  $this -> request -> get('ad_name', 'trim');
        $prize_name  =  $this -> request -> get('prize_name', 'trim');
        $srch_type      =  $this -> request -> get('srch_type', 'trim', 'sh');
        /**分页获取列表*/
        $srch_arr = array('sh'=>1, 'tg'=>2, 'ntg'=>3);
        $arr_srch = array(1=>'sh', 2=>'tg', 3=>'ntg');
        $map = array(
            'ad_name' => $ad_name,
            'prize_name' => $prize_name,
            'srch_type' => $srch_arr[$srch_type],
        );
        $sess = intval($this -> request -> get('sess', 'trim')); //判断是否获取查询条件
        if(isset($sess) && !empty($sess) && $this -> session -> has('idea_list')){
            $map = $this -> session -> get('idea_list');
        }else{
            $this -> session -> set('idea_list', $map);
        }
        $paginator = $this -> get_repository('Idea') -> get_list($page, $pagesize, $map);
        $pageNum = PaginatorHelper::get_paginator($paginator->total_items, $page, $pagesize);
        $list = $paginator->items->toArray();
        $count = $paginator->items->count();
        $this -> view -> setVars(array(
            'title'         =>  '广告创意列表',
            'paginator'     =>  $paginator,
            'pageNum'       =>  $pageNum,
            'ad_name'  =>  $map['ad_name'],
            'prize_name'    =>  $map['prize_name'],
            'list'          =>  $list,
            'page'          =>  $page,
            'pagesize'      =>  $pagesize,
            'cur_page_num'  =>  $count,
            'srch_type'     =>  $arr_srch[$map['srch_type']],
        ));
        $this -> view -> pick('idea/list');
    }

    //审核通过操作
    public function examinetgAction(){
        try{
            if($this -> request -> isAjax()){
                throw new \Exception('非法请求');
            }
            $ids            =  $this -> request -> get('ids', 'trim');
            $ext = array();
            $ext['examine_status'] = 2;
            $return = $this -> get_repository('Idea') -> examine($ext, $ids);
            $this -> writelog("通过了id为{$ids}的广告创意", 'ad_idea', $return, 'edit');          
            $this -> flashSession -> success('通过广告创意成功');
        }catch(\Exception $e){
            $this -> write_exception_log($e);
            $this -> flashSession -> error($e -> getMessage());
        }
        return $this -> redirect('idea/list?sess=1');
    }

    //审核驳回操作
    public function examinebhAction(){
        try{
            if($this -> request -> isAjax()){
                throw new \Exception('非法请求');
            }
            $ids            =  $this -> request -> get('ids', 'trim');
            $bh_reason      =  $this -> request -> get('bh_reason', 'trim');
            $bh_explain     =  $this -> request -> get('bh_explain', 'trim');
            $ext = array();
            $ext['examine_status'] = 3;
            if(empty($bh_reason)){
                throw new \Exception('请选择驳回理由');
            }
            if(empty($bh_explain)){
                throw new \Exception('请输入驳回说明');
            }
            $this -> get_repository('Idea') -> check_ad_by_idea($ids);
            $ext['bh_reason'] = $bh_reason;
            $ext['bh_explain'] = $bh_explain;
            $return = $this -> get_repository('Idea') -> examine($ext, $ids);
            $this -> writelog("驳回了id为{$ids}的广告创意", 'ad_idea', $return, 'edit');
            $this -> flashSession -> success('驳回广告创意成功');
        }catch(\Exception $e){
            $this -> write_exception_log($e);
            $this -> flashSession -> error($e -> getMessage());
        }
        return $this -> redirect('idea/list?sess=1');
    }

    /**
     * 查看广告创意
     */
    public function editAction(){
        try{
            $idea_id = intval($this -> request -> get('idea_id', 'trim'));
            $from = $this -> request -> get('from', 'trim');
            if(!empty($idea_id)){
                //广告创意信息
                $list = $this -> get_repository('Idea') -> get_One($idea_id);
                $this -> view -> setVars(array(
                    'list' => $list,
                    'from' => $from,
                ));
            }
            $this -> view -> pick('idea/edit');
        }catch(\Exception $e){
            $this -> write_exception_log($e);
            $this -> flashSession -> error($e -> getMessage());
            return $this -> redirect('idea/list');
        }
    }

    /**
     * 修改广告创意逻辑处理
     */
    public function writeAction(){
        try{
            if($this -> request -> isAjax() || !$this -> request -> isPost()){
                throw new \Exception('非法请求');
            }
            $idea_id = intval($this -> request -> get('idea_id', 'trim')); //判断添加还是编辑
            $prize_name = $this -> request -> getPost('prize_name', 'trim'); //奖品名称
            $prize_name = $this -> filter  -> sanitize($prize_name, 'remove_xss');
            $ad_image = $this -> request -> getPost('ad_image', 'trim', ''); //图片1旧值
            $ad_image2 = $this -> request -> getPost('ad_image2', 'trim', ''); //图片2旧值
            $coupon_term = $this -> request -> getPost('coupon_term', 'trim', ''); //优惠券有效期
            $coupon_term = $this -> filter  -> sanitize($coupon_term, 'remove_xss');
            $push_link = $this -> request -> getPost('push_link', 'trim'); //推广链接
            $push_link = $this -> filter  -> sanitize($push_link, 'remove_xss');
            $prize_desc = $this -> request -> getPost('prize_desc', 'trim'); //奖品说明
            $prize_desc = $this -> filter  -> sanitize($prize_desc, 'remove_xss');
            $coupon_step = $this -> request -> getPost('coupon_step', 'trim'); //使用流程
            $coupon_step = $this -> filter  -> sanitize($coupon_step, 'remove_xss');
            $coupon_rule = $this -> request -> getPost('coupon_rule', 'trim'); //使用规则
            $coupon_rule = $this -> filter  -> sanitize($coupon_rule, 'remove_xss');

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
                $ad_image = \Marser\App\Helpers\Common::deal_image('prize_image',640,300,'广告图片1','jpg|png|jpeg|gif');
            }
            if(empty($ad_image2)){
                $ad_image2 = \Marser\App\Helpers\Common::deal_image('prize_image2',0,0,'广告图片2','jpg|png|jpeg|gif');
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
            );
            $lastId = $this -> get_repository('Idea') -> save($map, $idea_id);
            if(empty($idea_id)){
                $this -> writelog("创建了id为{$lastId}的广告创意", 'ad_idea', $lastId, 'add');
            }else{
                $this -> writelog("修改了id为{$idea_id}的广告创意", 'ad_idea', $lastId, 'edit');
            }
            $this -> flashSession -> success('提交广告创意成功');
        }catch(\Exception $e){
            $this -> write_exception_log($e);
            $this -> flashSession -> error($e -> getMessage());
        }
        return $this -> redirect('idea/list?sess=1');
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
            $one = $this -> get_repository('Idea') -> get_One($id);
            $ext['auid'] = $one['auid'];
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
