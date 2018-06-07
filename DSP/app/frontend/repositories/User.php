<?php

/**
 * 用户业务仓库
 * @category PhalconDSP
 * @author haoshisuo 2017-9-13
 */

namespace Marser\App\Frontend\Repositories;

use \Marser\App\Frontend\Repositories\BaseRepository;

class User extends BaseRepository{

    public function __construct(){
        parent::__construct();
    }

    /**
     * 登录态检测
     * @return bool
     */
    public function login_check(){
        if($this -> getDI() -> get('session') -> has('user')){
            if(!empty($this -> getDI() -> get('session') -> get('user')['auid'])){
                return true;
            }
        }
        return false;
    }

    /**
     * 登录处理
     * @param $username
     * @param $password
     * @throws \Exception
     */
    public function login($username, $password){
        /** 获取用户信息 */
        $user = $this -> detail($username);
        if(!$user){
            throw new \Exception('用户名或密码错误');
        }
        $userinfo = $user -> toArray();
        /** 校验密码 */
        if($password != $userinfo['password']){
            throw new \Exception('密码错误，请重新输入');
        }
        /** 设置session */
        $info['auid'] = $userinfo['auid'];
        $info['username'] = $userinfo['username'];
        $info['ad_name'] = $userinfo['ad_name'];
        $info['company_name'] = $userinfo['company_name'];
        $this -> getDI() -> get('session') -> set('user', $info);
    }

    /**
     * 获取用户数据
     * @param string $username
     * @param array $ext
     * @return \Phalcon\Mvc\Model
     * @throws \Exception
     */
    public function detail($username, array $ext=array()){
        $user = $this->get_model('UserModel')->detail($username, $ext);
        if (!$user->auid) {
            throw new \Exception('获取用户信息失败');
        }
        return $user;
    }

    //获取一条信息
    public function get_One($auid){
        $result = $this -> get_model('UserModel') -> get_One($auid);
        return $result->toArray();
    }
}