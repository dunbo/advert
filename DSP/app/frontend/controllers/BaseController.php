<?php

/**
 * 前台基类控制器
 * @category PhalconDSP
 * @author haoshisuo 2017-9-13
 */

namespace Marser\App\Frontend\Controllers;

use \Marser\App\Core\PhalBaseController;
use \Marser\App\Frontend\Models\ModelFactory;
use \Marser\App\Frontend\Repositories\RepositoryFactory;

class BaseController extends PhalBaseController{
    public $userinfo; 
    public function initialize(){
        parent::initialize();
        $this->userinfo  = $this -> session -> get('user');
        $this -> login_check();
        $this -> set_common_vars();
    }

    /**
     * 设置模块公共变量
     */
    public function set_common_vars(){
        $this -> view -> setVars(array(
            'prefix' => 'materiel',
            'userinfo' => $this->userinfo,
            'assetsVersion' => strtotime(date('Y-m-d H', time()) . ":00:00"),
        ));
    }

    /**
     * 登录检测处理
     * @return bool
     */
    public function login_check(){
        if(!$this -> get_repository('User') -> login_check()){
            header('Location: /passport/index');die;
        }
        return true;
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

    protected function dump($arr){
        echo '<pre>';
        print_r($arr);
        exit('</pre>');
    }

    //操作日志
    protected function writelog($actionexp = ''){
        $fromip = $_SERVER['REMOTE_ADDR'];
        $auid = $this -> session -> get('user')['auid'];
        $ext = array(
            "auid"=> $auid,
            "actionexp"=> $actionexp,
            "logtime"=> time(),
            "fromip"=> $fromip,
        );
        $lastId = ModelFactory::get_model('LogModel') -> insert_record($ext);
        return $lastId;
    }
}
