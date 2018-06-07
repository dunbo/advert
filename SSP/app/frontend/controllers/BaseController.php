<?php

/**
 * 前台基类控制器
 */

namespace Marser\App\Frontend\Controllers;

use \Marser\App\Core\PhalBaseController,
    \Marser\App\Frontend\Repositories\RepositoryFactory;

class BaseController extends PhalBaseController{
    public $userinfo;
    public function initialize(){
        // ini_set('display_errors',1);        
        // ini_set('display_startup_errors',1);
        // error_reporting(-1);
        parent::initialize();
        $this -> login_check();
        $this -> set_common_vars();
    }

    /**
     * 设置模块公共变量
     */
    public function set_common_vars(){
        $userinfo = array(
                'muid'      =>  $_SESSION['USER_ID'],
                'username'  =>  $_SESSION['USER_NAME'],
        );
        $this->userinfo  =  $userinfo;
        $this -> view -> setVars(array(
            'title' => $this -> systemConfig -> app -> app_name,
            'userinfo' => $userinfo,
        ));
        //检查用户是否通过审核
        $userinfo = $this -> get_repository('User')->get_user_by_muid($_SESSION['USER_ID']);
        if( empty($userinfo) || $userinfo['status'] != 1) {
            $this->response->redirect( 'apply/index' );  
            // $this->dispatcher->forward(array(
            //     "controller" => "apply",
            //     "action"     => "index",
            // ));
        }
    }

    /**
     * 获取业务对象
     * @param $repositoryName
     * @return object
     * @throws \Exception
     */
    protected function get_repository($repositoryName){
        return RepositoryFactory::get_repository($repositoryName);
    }

    /**
     * 页面跳转
     * @param null $url
     */
    protected function redirect($url=NULL){
        empty($url) && $url = $this -> request -> getHeader('HTTP_REFERER');
        $this -> response -> redirect($url);
    }

    /**
     * 登录检测处理
     * @return bool
     */
    public function login_check(){
        $this -> session_begin();
        $this -> user_loging_new();
        if(!isset($_SESSION['USER_UID']) || $_SESSION['USER_ID'] == 13176){
            if( $_SERVER['SERVER_ADDR'] == '127.0.0.1') {
                $h_str = 'dev.';
            }
            $center_url = "http://".$h_str."i.anzhi.com?serviceId=006&serviceVersion=1.0&redirecturi=";
            $login_url = $center_url."http://ssp.dev.anzhi.com";//.$build_query;
            header("Location:{$login_url}");
        }else {
            return true;
        }
    }

    function user_loging_new(){
        if(!isset($_SESSION['USER_UID']) || $_SESSION['USER_ID'] == 13176){
            if (!empty($_COOKIE['_AZ_COOKIE_'])) {
                $ucenter = new \Marser\App\Libs\GoUcenter('www');
                $cookie_data = $ucenter->parse_uc_cookie();
                $user = $ucenter->token_userinfo();
                if (@$_SESSION['USER_ID'] != $cookie_data['pid']) {
                    if (isset($user['USER_ID']) && $user['USER_ID']!=13176 && isset($user['USER_NAME'])) {
                        $_SESSION['USER_ID'] = $user['USER_ID'];
                        $_SESSION['USER_UID'] = $user['USER_UID'];
                        $_SESSION['USER_NAME'] = $user['USER_NAME'];
                        $_SESSION['EMAIL'] = $user['EMAIL'];
                        $_SESSION['MOBILE'] = $user['MOBILE'];
                    }
                }else{
                    if (isset($cookie_data['pid']) && $user['pid']!=13176 && isset($cookie_data['loginAccount'])) {
                        $_SESSION['USER_ID'] = $cookie_data['pid'];
                        $_SESSION['USER_UID'] = $cookie_data['uid'];
                        $_SESSION['USER_NAME'] = $cookie_data['loginAccount'];  
                        $_SESSION['EMAIL'] = $user['EMAIL'];
                        $_SESSION['MOBILE'] = $user['MOBILE'];
                    }               
                }
            }
        }   
        //setcookie('_AZ_COOKIE_', '', time()-31536000, '/', 'anzhi.com');
        //setcookie('_AZ_COOKIE_KEY', '', time()-31536000, '/', 'anzhi.com');
    }

}