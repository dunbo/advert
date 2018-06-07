<?php

/**
 * 用户业务仓库
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
            if(!empty($this -> getDI() -> get('session') -> get('user')['muid'])){
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
        $userinfo = $this -> detail($username, array('columns'=>'muid,username,password'));
        if(!$userinfo){
            throw new \Exception('用户名或密码错误');
        }
        /** 校验密码 */
        if( $password != $userinfo['password'] ){
            throw new \Exception('用户名或密码错误，请重新输入');
        }
        /** 设置session */
        unset($userinfo['password']);
        $this -> getDI() -> get('session') -> set('user', $userinfo);
    }

    /**
     * 重置密码
     * @param $oldpwd
     * @param $newpwd
     * @return mixed
     * @throws \Exception
     */
    public function update_password($oldpwd, $newpwd){
        /** 校验旧密码是否正确 */
        $user = $this -> detail($this -> getDI() -> get('session') -> get('user')['username']);
        if(!$user){
            throw new \Exception('密码错误');
        }
        $userinfo = $user -> toArray();
        if(!$this -> getDI() -> get('security') -> checkHash($oldpwd, $userinfo['password'])){
            throw new \Exception('密码错误，请重新输入');
        }
        /** 密码更新 */
        $password = $this -> getDI() -> get('security') -> hash($newpwd);
        $affectedRows = $this -> get_model('UserModel') -> update_record(array(
            'password' => $password,
        ), $this -> getDI() -> get('session') -> get('user')['muid']);
        if(!$affectedRows){
            throw new \Exception('修改密码失败，请重试');
        }
        return $affectedRows;
    }

    /**
     * 更新个人信息
     * @param array $data
     * @param null $muid
     * @return bool
     * @throws \Exception
     */
    public function update(array $data, $muid){
        $muid = intval($muid);
        if($muid <= 0){
            throw new \Exception('参数错误');
        }
        $affectedRows = $this -> get_model('UserModel') -> update_record($data, $muid);
        if(!$affectedRows){
            throw new \Exception('修改失败');
        }
        return true;
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
        if (!$user->muid) {
            throw new \Exception('获取用户信息失败');
        }
        return $user->toArray();
    }

    public function get_user_by_muid($muid, array $ext=array()){
        $user = $this->get_model('UserModel')->get_user_by_muid($muid, $ext);
        return $user;
    }

        /**
     * 媒体主数据入库
     * @param array $data
     * @return int
     * @throws \Exception
     */
    public function create(array $data){
        /** 添加媒体主 */
        $muid = $this -> get_model('UserModel') -> insert_record($data);
        $muid = intval($muid);
        if($muid <= 0){
            throw new \Exception('申请失败');
        }
        return $muid;
    }
}