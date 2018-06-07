<?php
/**
 * 后台基类控制器
 */

namespace Marser\App\Backend\Controllers;

use \Marser\App\Core\PhalBaseController,
    \Marser\App\Backend\Repositories\RepositoryFactory;

class BaseController extends PhalBaseController{

    public function initialize(){
        parent::initialize();
        $res = $this -> login_check();
        if($res==true){
            $this -> set_common_vars();
            $this -> auth_check();
        }else{
            header('Location: /admin/passport/index');
        }
    }

    /**
     * 设置模块公共变量
     */
    public function set_common_vars(){


        $popedom_name = $_SERVER['REQUEST_URI'];
        $rs = explode('?',$popedom_name);
        $popedom_name = $rs[0];
        if($popedom_name=='/'){
            return $this -> redirect('dashboard/index');
        }

        $admin= $this -> session -> get('admin');

        $admin_menu= $this -> session -> get('admin_menu');
        $show_popedom_name=$admin_menu['now_menu'];
        if(array_key_exists($popedom_name,$admin['node'])){
            if($show_popedom_name!=$popedom_name)
            {
                $this->session->set('admin_menu',array('now_menu'=>$popedom_name));
                $show_popedom_name=$popedom_name;
            }
        }

        $this->view->popedom_name = $show_popedom_name;

        $menutitle       =   $this -> request -> get('title');

        if(empty($menutitle)){
            if(array_key_exists($popedom_name,$admin['node'])){
                $menutitle =  $admin['node'][$popedom_name];
            }else{
                $menutitle =  'Honor_media';
            }
        }
        $this -> view -> setVars(array(
            'title' => $this -> systemConfig -> app -> app_name,
            'admin' => $this -> session -> get('admin'),
            'menutitle' => $menutitle,
            'assetsVersion' => strtotime(date('Y-m-d H', time()) . ":00:00"),
        ));
    }

    /**
     * 登录检测处理
     * @return bool
     */
    public function login_check(){
        if(!$this -> get_repository('Users') -> login_check()){
            return $this -> redirect("passport/index");
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


    //权限检查
    public function auth_check(){
        $session_arr = $this->session->get('admin');

        // if($session_arr['ua']!=$_SERVER['HTTP_USER_AGENT']||$session_arr['ip']!=$_SERVER['REMOTE_ADDR']){

        //     if($this -> session -> destroy()){
        //         return $this -> response -> redirect('passport/index');
        //     }

        // }

        // $popedom_name = $_SERVER['REQUEST_URI'];///TEST/index
        // $rs = explode('?',$popedom_name);
        // $popedom_name = $rs[0];
        // if($popedom_name=='/admin/dashboard/index'||$popedom_name=='/'){
        //     return;
        // }
        // //$this->view->popedom_name = $show_popedom_name;

        // if(!in_array($popedom_name,$session_arr['popedom'])) {
        //     echo '<div align="center"><font color="red"> <img src="http://518.anzhi.com/Public/images/update.gif"></img>对不起,权限不足!</font>您可以选择  [ <a href="'.$_SERVER['HTTP_REFERER'].'">返回</a> ]</div>';
        //     exit(0);
        // }
    }


    //518日志
    public function writelog($actionexp, $table = '', $value= '',$type=''){
        $acname = $_SERVER['REQUEST_URI'];///TEST/index
        $rs = explode('?',$acname);
        $acname = $rs[0];

        $session_arr = $this->session->get('admin');
        $admin_id = $session_arr['admin_id'];

        $url = $this -> systemConfig -> app -> backend->writelog_url;
        $post_data = array(
            "actionexp"=> $actionexp,
            "table"=> $table,
            "value"=> $value,
            "acname"=> $acname,
            "type"=> $type,
            "admin_id"=> $admin_id,
        );

        $this->request_url($url,$post_data);
        $this -> logger -> write_log(json_encode($post_data));
    }

    protected function dump($arr){
        echo '<pre>';
        print_r($arr);
        exit('</pre>');
    }
}
