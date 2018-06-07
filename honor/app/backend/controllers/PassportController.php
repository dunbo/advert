<?php

/**
 * 通行证
 */

namespace Marser\App\Backend\Controllers;

use \Marser\App\Core\PhalBaseController,
    \Marser\App\Backend\Repositories\RepositoryFactory;

class PassportController extends PhalBaseController{

    public function initialize(){
        parent::initialize();
    }

    /**
     * 登录页
     */
    public function indexAction(){
        $this -> login_check();
        $this -> view -> setVars(array(
            'title' => $this -> systemConfig -> app -> app_name,
            'assetsVersion' => strtotime(date('Y-m-d H', time()) . ":00:00"),
        ));
        $this -> view -> setMainView('passport/login');
    }

    /**
     * 登录处理
     * @throws \Exception
     */
    public function loginAction(){
        $this -> login_check();
        try {
            if($this -> request -> isAjax() || !$this -> request -> isPost()){
                throw new \Exception('非法请求');
            }
            $username = $this -> request -> getPost('username', 'trim');
            $password = $this -> request -> getPost('password', 'trim');


            $url = $this -> systemConfig -> app -> backend->login_url;
            $post_data = array(
                "account"=> $username,
                "password"=> $password,
            );

            $res = $this->request_url($url,$post_data);
            //print_r($session_arr); //放到session里  todo


            if($res==-1){
                throw new \Exception('用户名或密码错误，请重新输入');
            }else{
                $session_arr = json_decode($res,true);
                $session_arr['admin']['ua']=$_SERVER['HTTP_USER_AGENT'];
                $session_arr['admin']['ip']=$_SERVER['REMOTE_ADDR'];
                $this->session->set('admin', $session_arr['admin']);
                //$rs = $this->session->get('admin');
                //var_dump($rs);
                //exit;
                return $this -> response -> redirect('dashboard/index');
            }
        }catch(\Exception $e){
            $this -> write_exception_log($e);

            $this -> flashSession -> error($e -> getMessage());

            return $this -> response -> redirect('passport/index');
        }
    }

    /**
     * 注销登录
     */
    public function logoutAction(){
        if($this -> session -> destroy()){
            return $this -> response -> redirect('passport/index');
        }
    }

    /**
     * 登录检测处理
     * @return bool
     */
    protected function login_check(){
        if(RepositoryFactory::get_repository('Users') -> login_check()){
            return $this -> response -> redirect("dashboard/index");
        }
        return false;
    }
}
