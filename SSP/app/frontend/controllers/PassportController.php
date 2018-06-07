<?php

/**
 * 通行证
 */

namespace Marser\App\Frontend\Controllers;

use \Marser\App\Core\PhalBaseController,
    \Marser\App\Frontend\Repositories\RepositoryFactory;

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

            /** 添加验证规则 */
            $this -> validator -> add_rule('username', 'required', '请输入用户名');
                // -> add_rule('username', 'alpha_dash', '用户名由4-20个英文字符、数字、下划线和横杠组成')
                // -> add_rule('username', 'min_length', '用户名由4-20个英文字符、数字、下划线和横杠组成', 4)
                // -> add_rule('username', 'max_length', '用户名由4-20个英文字符、数字、下划线和横杠组成', 20);
            $this -> validator -> add_rule('password', 'required', '请输入密码');
                // -> add_rule('password', 'min_length', '密码由6-32个字符组成', 6)
                // -> add_rule('password', 'max_length', '密码由6-32个字符组成', 32);
            /** 截获验证异常 */
            if ($error = $this -> validator -> run(array('username'=>$username, 'password'=>$password))) {
                $error = array_values($error);
                $error = $error[0];
                throw new \Exception($error['message'], $error['code']);
            }
            /** 登录处理 */
            RepositoryFactory::get_repository('User') -> login($username, $password);

            return $this -> response -> redirect('index/index');
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
        if( $_SERVER['SERVER_ADDR'] == '127.0.0.1' ) {
            $h_str = 'dev.';
        }
        if( $_SERVER['SERVER_ADDR'] == '172.16.1.250') {
            $d_str = 'dev.';
        }
        $logout_url =  "http://".$h_str."i.anzhi.com/?serviceId=006&serviceVersion=1.0&redirecturi=http://ssp.".$d_str."anzhi.com/index/index";
        //$logout_url = "http://".$h_str."i.anzhi.com/mweb/account/logout";
        session_start();
        session_destroy();
        setcookie('_AZ_COOKIE_', '', time()-31536000, '/', 'anzhi.com');
        setcookie('_AZ_COOKIE_KEY', '', time()-31536000, '/', 'anzhi.com');
        return $this->response->redirect($logout_url, true);
    }

    /**
     * 登录检测处理
     * @return bool
     */
    protected function login_check(){
        if(RepositoryFactory::get_repository('User') -> login_check()){
            return $this -> response -> redirect("index/index");
        }
        return false;
    }
}