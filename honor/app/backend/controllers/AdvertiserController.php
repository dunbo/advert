<?php

/**
 * 广告主控制器
 * @category PhalconHonor
 * @author haoshisuo 2017-9-22
 */

namespace Marser\App\Backend\Controllers;

use \Marser\App\Backend\Controllers\BaseController;
use \Marser\App\Helpers\PaginatorHelper;

class AdvertiserController extends BaseController{

    // 广告主列表
    public function listAction(){
        $page           =  $this -> request -> get('page', 'int', 1);
        $pagesize       =  $this -> request -> get('pagesize', 'int', 10);
        $ad_name        =  $this -> request -> get('ad_name', 'trim');
        $company_name   =  $this -> request -> get('company_name', 'trim');
        $username       =  $this -> request -> get('username', 'trim');
        $map = array(
            'username'      =>  $username,
            'ad_name'       =>  $ad_name,
            'company_name'  =>  $company_name,
        );
        $sess = intval($this -> request -> get('sess', 'trim')); //判断是否获取查询条件
        if(isset($sess) && !empty($sess) && $this -> session -> has('advertiser_list')){
            $map = $this -> session -> get('advertiser_list');
        }else{
            $this -> session -> set('advertiser_list', $map);
        }
        $paginator = $this -> get_repository('Advertiser') -> get_list($page, $pagesize, $map);
        
        $pageNum = PaginatorHelper::get_paginator($paginator->total_items, $page, $pagesize);
        $list = $paginator->items-> toArray();
        $count = $paginator->items->count();

        $this -> view -> setVars(array(
            'title'         =>  '广告主列表',
            'paginator'     =>  $paginator,
            'pageNum'       =>  $pageNum,
            'username'      =>  $map['username'],
            'ad_name'       =>  $map['ad_name'],
            'company_name'  =>  $map['company_name'],
            'list'          =>  $list,
            'page'          =>  $page,
            'pagesize'      =>  $pagesize,
            'cur_page_num'  =>  $count,
        ));
        $this -> view -> pick('advertiser/list');
    }

    /**
    * 添加、编辑广告主页面
    */
    public function addAction() {
        $uid = $this -> request -> get('uid', 'int');
        $info = array();
        if(!empty($uid)) {
            $info = $this -> get_repository('Advertiser') -> get_One($uid);
        }
        $this -> view -> setVars(array(
            'info'  =>  $info,
            'uid'   =>  $uid,
            'title' =>  $uid ? '编辑广告主' : '创建广告主',
        ));
        $this -> view -> pick('advertiser/add');
    }


    /**
     * 广告主（添加、编辑）数据库操作
     */
    public function writeAction(){
        try{
            if($this -> request -> isAjax() || !$this -> request -> isPost()){
                throw new \Exception('非法请求');
            }
            $uid                =  $this -> request -> getPost('uid', 'int');
            $username           =  $this -> request -> getPost('username', 'trim');
            $username           =   $this -> filter  -> sanitize($username, 'remove_xss');
            $password           =  $this -> request -> getPost('password', 'trim');
            $password           =   $this -> filter  -> sanitize($password, 'remove_xss');
            $ad_name            =  $this -> request -> getPost('ad_name', 'trim');
            $ad_name            =   $this -> filter  -> sanitize($ad_name, 'remove_xss');
            $company_web        =  $this -> request -> getPost('company_web', 'trim');
            $company_web        =   $this -> filter  -> sanitize($company_web, 'remove_xss');
            $company_name       =  $this -> request -> getPost('company_name', 'trim');
            $company_name       =   $this -> filter  -> sanitize($company_name, 'remove_xss');
            $register_address   =  $this -> request -> getPost('register_address', 'trim');
            $register_address   =   $this -> filter  -> sanitize($register_address, 'remove_xss');
            $business_img       =  $this -> request -> getPost('business_img', 'trim', '');
            $register_num       =  $this -> request -> getPost('register_num', 'trim');
            $contact_name       =  $this -> request -> getPost('contact_name', 'trim');
            $contact_name       =   $this -> filter  -> sanitize($contact_name, 'remove_xss');
            $contact_email      =  $this -> request -> getPost('contact_email', 'trim');
            $contact_email      =   $this -> filter  -> sanitize($contact_email, 'remove_xss');
            $contact_phone      =  $this -> request -> getPost('contact_phone', 'trim');
            $contact_qq         =  $this -> request -> getPost('contact_qq', 'trim');
            /** 添加验证规则 */
            $this -> validator -> add_rule('ad_name', 'required', '请填写广告主名称')
            -> add_rule('ad_name', 'max_length', '广告主名称不能超过20个字符', 20);
            $this -> validator -> add_rule('username', 'required', '请填写登录名')
            -> add_rule('username', 'max_length', '登录名不能超过20个字符', 20);
            $this -> validator -> add_rule('password', 'required', '请填写密码')
            -> add_rule('password', 'max_length', '密码不能超过20个字符', 20);
            $this -> validator -> add_rule('company_web', 'required', '请填写公司网址')
            -> add_rule('company_web', 'max_length', '公司网址不能超过40个字符', 40)
            -> add_rule('company_web', 'url', '公司网址格式错误');
            $this -> validator -> add_rule('company_name', 'required', '请填写公司名称')
            -> add_rule('company_name', 'max_length', '公司名称不能超过40个字符', 40);
            !empty($register_num) && $this -> validator -> add_rule('register_num', 'alpha_numeric', '注册号格式错误');
            !empty($contact_email) && $this -> validator -> add_rule('contact_email', 'email', '联系人邮箱格式错误');
            !empty($contact_phone) && $this -> validator -> add_rule('contact_phone', 'telephone', '联系人手机号格式错误');
            !empty($contact_qq) && $this -> validator -> add_rule('contact_qq', 'qq_num', '联系人qq号格式错误');
            /** 截获验证异常 */
            if ($error = $this -> validator -> run(array(
                'ad_name'       =>  $ad_name,
                'username'      =>  $username,
                'password'      =>  $password,
                'company_web'   =>  $company_web,
                'company_name'  =>  $company_name,
                'register_num'  =>  $register_num,
                'contact_email' =>  $contact_email,
                'contact_phone' =>  $contact_phone,
                'contact_qq'    =>  $contact_qq,
            ))) {
                $error = array_values($error);
                $error = $error[0];
                throw new \Exception($error['message'], $error['code']);
            }
            //图片处理
            if(!empty($_FILES['yingye_img']['tmp_name'])){
                $business_img = \Marser\App\Helpers\Common::deal_image('yingye_img',0,0,'营业照');
            }
            /** 发布 */
            $return = $this -> get_repository('Advertiser') -> save(array(
                    'username'          =>  $username,
                    'password'          =>  $password,
                    'ad_name'           =>  $ad_name,
                    'company_web'       =>  $company_web,
                    'company_name'      =>  $company_name,
                    'register_address'  =>  $register_address,
                    'register_num'      =>  $register_num,
                    'contact_name'      =>  $contact_name,
                    'contact_email'     =>  $contact_email,
                    'contact_phone'     =>  $contact_phone,
                    'contact_qq'        =>  $contact_qq,
                    'business_img'      =>  $business_img,
            ), $uid);
            if(isset($uid) && !empty($uid)){
                $this -> writelog("编辑了id为{$uid}的广告主信息", 'ad_user', $return, 'edit');
            }else{
                $this -> writelog('创建了广告主信息', 'ad_user', $return, 'add');
            }
            $this -> flashSession -> success('提交广告主成功');
        }catch(\Exception $e) {
            $this -> write_exception_log($e);
            $this -> flashSession -> error($e -> getMessage());
            echo "<script>window.history.go(-1)</script>";die;
        }
        if(isset($uid) && !empty($uid)){
            return $this -> redirect('advertiser/list?sess=1');
        }else{
            return $this -> redirect('advertiser/list');
        }
    }

    //检查公司名称是否存在
    public function check_existAction(){
        try{
            if(!$this -> request -> isAjax()){
                throw new \Exception('非法请求');
            }
            $auid = $this -> request -> get('auid', 'trim');
            $company_name = $this -> request -> get('company_name', 'trim');
            $ext['company_name'] = $company_name;
            $return = $this -> get_repository('Advertiser') -> check_exist($ext, $auid, 1);
            $this -> ajax_return('返回成功', $return);
        }catch(\Exception $e){
            $this -> write_exception_log($e);
            if( $this -> request -> isAjax() ) {
                $this -> ajax_return($e -> getMessage(), 0);
            }else {
                $this -> flashSession -> error($e -> getMessage());
                return $this -> redirect('advertiser/list');
            }
        }
        $this -> view -> disable();
    }
}
