<?php

/**
 * 通行证
 */

namespace Marser\App\Frontend\Controllers;

use \Marser\App\Core\PhalBaseController,
    \Marser\App\Frontend\Repositories\RepositoryFactory;

class ApplyController extends PhalBaseController{

    public function initialize(){
        parent::initialize();
    }

    public function indexAction(){
    	@$this -> session_begin();
        if( empty($_SESSION['USER_ID']) ) {
            return $this -> response -> redirect('Index/index');
        }
        $user_info =  RepositoryFactory::get_repository('User')->get_user_by_muid($_SESSION['USER_ID']);
        $userinfo  =  array('muid'=>$_SESSION['USER_ID'],'username'=>$_SESSION['USER_NAME']);
        if( !empty($user_info['status']) && $user_info['status'] == 1) {
        	return $this -> response -> redirect('Index/index');
        }
        $this -> view -> setVars(array(
            'prefix'    =>  'index',
            'user_info' =>  $user_info,
            'userinfo'  =>  $userinfo,
            'title' 	=>	$this -> systemConfig -> app -> app_name,
        ));
        $this -> view -> pick('apply/index');
    }

    public function publishAction(){
         try{
            if($this -> request -> isAjax() || !$this -> request -> isPost()){
                throw new \Exception('非法请求');
            }
            $md_compnay_name   	=   $this -> request -> getPost('md_compnay_name', 'trim');
            $md_compnay_name   	=   $this -> filter  -> sanitize($md_compnay_name, 'remove_xss');
            $contact_name   	=   $this -> request -> getPost('contact_name', 'trim');
            $contact_name   	=   $this -> filter  -> sanitize($contact_name, 'remove_xss');
            $email          	=   $this -> request -> getPost('email', 'trim');
            $email          	=   $this -> filter  -> sanitize($email, 'remove_xss');
            $phone          	=   $this -> request -> getPost('phone', 'trim');
            $phone         	 	=   $this -> filter  -> sanitize($phone, 'remove_xss');
            $qq             	=   $this -> request -> getPost('qq', 'trim');
            $qq            		=   $this -> filter  -> sanitize($qq, 'remove_xss');
            $intro          	=   $this -> request -> getPost('intro', 'trim');
            $intro          	=   $this -> filter  -> sanitize($intro, 'remove_xss');

            $this -> validator -> add_rule('md_compnay_name', 'required', '公司名称必填');
            $this -> validator -> add_rule('intro', 'required', '简介必填');
            $validator_map = array('md_compnay_name'=>$md_compnay_name, 'intro'=>$intro);
            /** 截获验证异常 */
            $error = $this -> validator -> run( $validator_map );
            if($error) {
                $error = array_values($error);
                $error = $error[0];
                throw new \Exception($error['message'], $error['code']);
            }
            /** 发布 */
            $this -> session_begin();
            if( empty($_SESSION) ) {
            	throw new \Exception('请先登录');
            }
            $muid 		=	$_SESSION['USER_ID'];
            $username 	=	$_SESSION['USER_NAME'];

            $map =  array(
           			'muid'				=>	$muid,
                    'username'     	 	=>  $username,
                    'password'			=>	'',
                    'md_compnay_name'  	=>  $md_compnay_name,
                    'contact_name' 		=>  $contact_name,
                    'email'        		=>  $email,
                    'phone'  			=>  $phone,
                    'qq'     			=>  $qq,
                    'intro'				=>	$intro,
                    'status'  			=>  0,
            );

            $info = RepositoryFactory::get_repository('User')->get_user_by_muid($muid);
            $log_data = json_encode($_POST);
            if(empty($info)) {
				RepositoryFactory::get_repository('User') -> create($map);
				RepositoryFactory::get_repository('Log') -> writelog($muid,"申请媒体主muid为{$muid}的媒体主{$log_data}");
            }else {
            	if($info['status'] == 2) {
            		RepositoryFactory::get_repository('User') -> update($map, $muid);
            		RepositoryFactory::get_repository('Log') -> writelog($muid,"重新申请媒体主muid为{$muid}的媒体主{$log_data}");
            	}else {
            		throw new \Exception('非法提交');
            	}
            }
            $this -> flashSession -> success('合作申请已发出，请等待商务人员审核');
        }catch(\Exception $e) {
            $this -> write_exception_log($e);
            $this -> flashSession -> error($e -> getMessage());
        }
        return $this -> response -> redirect('apply/index');
    }
 
}