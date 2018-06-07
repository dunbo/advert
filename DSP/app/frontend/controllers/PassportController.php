<?php

/**
 * 通行证
 * @category PhalconDSP
 * @author haoshisuo 2017-9-13
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
            'title'         =>  $this -> systemConfig -> app -> app_name,
            'assetsVersion' =>  strtotime(date('Y-m-d H', time()) . ":00:00"),
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
            $this -> validator -> add_rule('password', 'required', '请输入密码');
            /** 截获验证异常 */
            if ($error = $this -> validator -> run(array('username'=>$username, 'password'=>$password))) {
                $error = array_values($error);
                $error = $error[0];
                throw new \Exception($error['message'], $error['code']);
            }
            /** 登录处理 */
            RepositoryFactory::get_repository('User') -> login($username, $password);

            return $this -> response -> redirect('materiel/list');
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
        if(RepositoryFactory::get_repository('User') -> login_check()){
            return $this -> response -> redirect("materiel/list");
        }
        return false;
    }
}