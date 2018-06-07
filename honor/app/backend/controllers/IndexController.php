<?php
/**
 * index
 */
namespace Marser\App\Backend\Controllers;

use \Marser\App\Backend\Controllers\BaseController;
use \Marser\App\Helpers\PaginatorHelper;

class IndexController extends BaseController{
    private  $image_size = array(
         'identity_front_img' => array(0,0),
         'identity_back_img' => array(0,0),
         'license_img' => array(0,0),
        );

    public function listAction(){
        $page            =   $this -> request -> get('page', 'int', 1);
        $pagesize        =   $this -> request -> get('pagesize', 'int', 10);
        $status          =   $this -> request -> get('status', 'int', 0);
        $media_name      =   $this -> request -> get('media_name', 'trim');
        $md_compnay_name =   $this -> request -> get('md_compnay_name', 'trim');
        $username        =   $this -> request -> get('username', 'trim');
        $paginator = $this -> get_repository('UserMedia') -> get_list($page, $pagesize, array(
            'username'          =>  $username,
            'media_name'        =>  $media_name,
            'md_compnay_name'   =>  $md_compnay_name,
            'status'            =>  $status,
        ));
        //print_r($_GET);die;
        $pageNum = PaginatorHelper::get_paginator($paginator->total_items, $page, $pagesize);
        $list = $paginator->items->toArray();
        $count = $paginator->items->count();
        
        $this -> view -> setVars(array(
            'title'             => '媒体主管理',
            'paginator'         =>  $paginator,
            'pageNum'           =>  $pageNum,
            'username'          =>  $username,
            'media_name'        =>  $media_name,
            'md_compnay_name'   =>  $md_compnay_name,
            'list'              =>  $list,
            'page'              =>  $page,
            'pagesize'          =>  $pagesize,
            'cur_page_num'      =>  $count,
            'status'            =>  $status,
        ));

        $this -> view -> pick('index/list');
    }

    /**
    * 添加媒体主
    */
    public function addAction() {
        $muid    =   $this -> request -> get('muid', 'int');
        if( $muid ) {
            $user = $this -> get_repository('UserMedia') -> get_user_by_muid($muid);
        }
        //获取结算方案
        $plan = $this -> get_repository('Plan') -> get_list();
        $this -> view -> setVars(array(
                    'user'  =>  $user,
                    'muid'  =>  $muid,
                    'plan'  =>  $plan,
                    'title' =>  $muid ? '媒体主管理>编辑媒体主' : '媒体主管理>创建媒体主',
                ));
        $this -> view -> pick('index/add');
    }


    // /**
    //  * 发布媒体主（添加、编辑）
    //  */
    // public function publishAction(){
    //     try{
    //         if($this -> request -> isAjax() || !$this -> request -> isPost()){
    //             throw new \Exception('非法请求');
    //         }
    //         $muid           =   $this -> request -> getPost('muid', 'int');
    //         $media_name     =   $this -> request -> getPost('media_name', 'trim');
    //         $media_name     =   $this -> filter  -> sanitize($media_name, 'remove_xss');
    //         $md_compnay_name=   $this -> request -> getPost('md_compnay_name', 'trim');
    //         $md_compnay_name=   $this -> filter  -> sanitize($md_compnay_name, 'remove_xss');
    //         $intro          =   $this -> request -> getPost('intro', 'trim');
    //         $intro          =   $this -> filter  -> sanitize($intro, 'remove_xss');

    //         $media_type     =   $this -> request -> getPost('media_type', 'int');
            
    //         $identity_name  =   $this -> request -> getPost('identity_name', 'trim');
    //         $identity_name  =   $this -> filter  -> sanitize($identity_name, 'remove_xss');
    //         $identity_num   =   $this -> request -> getPost('identity_num', 'trim');
    //         $identity_num   =   $this -> filter  -> sanitize($identity_num, 'remove_xss');

    //         $company_name   =   $this -> request -> getPost('company_name', 'trim');
    //         $company_name   =   $this -> filter  -> sanitize($company_name, 'remove_xss');
    //         $reg_address    =   $this -> request -> getPost('reg_address', 'trim');
    //         $reg_address    =   $this -> filter  -> sanitize($reg_address, 'remove_xss');
    //         $reg_number     =   $this -> request -> getPost('reg_number', 'trim');
    //         $reg_number     =   $this -> filter  -> sanitize($reg_number, 'remove_xss');
            
    //         $scheme         =   $this -> request -> getPost('scheme', 'int');
    //         $open_company   =   $this -> request -> getPost('open_company', 'trim');
    //         $open_company   =   $this -> filter  -> sanitize($open_company, 'remove_xss');
    //         $open_bank      =   $this -> request -> getPost('open_bank', 'trim');
    //         $open_bank      =   $this -> filter  -> sanitize($open_bank, 'remove_xss');
    //         $open_address   =   $this -> request -> getPost('open_address', 'trim');
    //         $open_address   =   $this -> filter  -> sanitize($open_address, 'remove_xss');
    //         $branch_name    =   $this -> request -> getPost('branch_name', 'trim');
    //         $branch_name    =   $this -> filter  -> sanitize($branch_name, 'remove_xss');
    //         $bank_account   =   $this -> request -> getPost('bank_account', 'trim');
    //         $bank_account   =   $this -> filter  -> sanitize($bank_account, 'remove_xss');
    //         $open_licence   =   $this -> request -> getPost('open_licence', 'trim');
    //         $open_licence   =   $this -> filter  -> sanitize($open_licence, 'remove_xss');
    //         $contact_name   =   $this -> request -> getPost('contact_name', 'trim');
    //         $contact_name   =   $this -> filter  -> sanitize($contact_name, 'remove_xss');
    //         $email          =   $this -> request -> getPost('email', 'trim');
    //         $email          =   $this -> filter  -> sanitize($email, 'remove_xss');
    //         $phone          =   $this -> request -> getPost('phone', 'trim');
    //         $phone          =   $this -> filter  -> sanitize($phone, 'remove_xss');
    //         $qq             =   $this -> request -> getPost('qq', 'trim');
    //         $qq             =   $this -> filter  -> sanitize($qq, 'remove_xss');

    //         $identity_front_img =   $this -> request -> getPost('identity_front_img', 'trim');
    //         $identity_back_img  =   $this -> request -> getPost('identity_back_img', 'trim');
    //         $license_img        =   $this -> request -> getPost('license_img', 'trim');
            
    //         $validator_map = array('media_name' => $media_name, 'md_compnay_name' => $md_compnay_name, 'intro' => $intro);
    //         /** 添加验证规则 */
    //         if( !$muid ) {
    //             $this -> validator -> add_rule('media_name', 'required', '请填写媒体主名称');
    //             $validator_map['media_name'] = $media_name;
    //         }
    //         $this -> validator -> add_rule('media_name', 'required', '媒体名称不能为空');
    //         $this -> validator -> add_rule('md_compnay_name', 'required', '公司名称不能为空');
    //         $this -> validator -> add_rule('intro', 'required', '媒体简介不能为空');

    //         if( $media_type == 1 ) {
    //             //$this -> validator -> add_rule('identity_name', 'required', '请填写姓名');
    //             //$this -> validator -> add_rule('identity_num', 'required', '请填写身份证号');
    //             $validator_map['identity_name'] = $identity_name;
    //             $validator_map['identity_num']  = $identity_num;
    //         }else {
    //             $this -> validator -> add_rule('company_name', 'required', '请填写公司全称');
    //             //$this -> validator -> add_rule('reg_address', 'required', '请填写注册地址');
    //             //$this -> validator -> add_rule('reg_number', 'required', '请填写注册号');
    //             $validator_map['company_name']  =   $company_name;
    //             $validator_map['reg_address']   =   $reg_address;
    //             $validator_map['reg_number']    =   $reg_number;           
    //         }

    //         $this -> validator -> add_rule('scheme', 'required', '请选择结算方案');
    //         $validator_map['scheme']    =   $scheme;
    //         // //!$mmid && $this -> validator -> add_rule('mmid', 'required', '系统错误，请刷新页面后重试');
    //         /** 截获验证异常 */
    //         $error = $this -> validator -> run( $validator_map );
    //         if($error) {
    //             $error = array_values($error);
    //             $error = $error[0];
    //             throw new \Exception($error['message'], $error['code']);
    //         }
    //         /** 发布 */
    //        $map =  array(
    //                 'media_name'         =>  $media_name,
    //                 'md_compnay_name'    =>  $md_compnay_name,
    //                 'intro'              =>  $intro,
    //                 'media_type'         =>  $media_type,
    //                 'scheme'             =>  $scheme,
    //                 'open_company'       =>  $open_company,
    //                 'open_bank'          =>  $open_bank,
    //                 'open_address'       =>  $open_address,
    //                 'branch_name'        =>  $branch_name,
    //                 'bank_account'       =>  $bank_account,
    //                 'open_licence'       =>  $open_licence,
    //                 'contact_name'       =>  $contact_name,
    //                 'email'              =>  $email,
    //                 'phone'              =>  $phone,
    //                 'qq'                 =>  $qq,
    //         );
    //         if( !$muid ) {
    //             $map['media_name'] = $media_name;
    //         }
    //         if( $media_type == 1 ) {
    //             if( $_FILES['identity_front_img']['tmp_name'] ) {
    //                 $this->deal_image($map,'identity_front_img');
    //             }
    //             if( $_FILES['identity_back_img']['tmp_name'] ) {
    //                 $this->deal_image($map,'identity_back_img');
    //             }
    //             $map['identity_name'] = $identity_name;
    //             $map['identity_num']  = $identity_num;
    //             $map['license_img'] = '';
    //             $map['company_name'] = '';
    //             $map['reg_address']  = '';
    //             $map['reg_number']   = '';
    //         }else {
    //             if( $_FILES['license_img']['tmp_name'] ) {
    //                $this->deal_image($map,'license_img');
    //             }
    //             $map['company_name'] = $company_name;
    //             $map['reg_address']  = $reg_address;
    //             $map['reg_number']   = $reg_number;
    //             $map['identity_front_img'] = '';
    //             $map['identity_back_img']  = '';
    //             $map['identity_name'] = '';
    //             $map['identity_num'] = '';
    //         }
    //         if( $muid ) {
    //             //编辑 
    //             $media_user = $this -> get_repository('UserMedia') -> get_user_by_muid($muid);
    //             //编辑时更新了结算方案的情况
    //             if($media_user['scheme'] != $scheme ) {
    //                 //获取最新的结算方案记录
    //                 $plan_info = $this -> get_repository('PlanUser') -> get_info($muid);
    //                 if( $plan_info ) {
    //                     //更新结束时间
    //                     $end_time = date("Y-m-d 23:59:59", time());
    //                     $plan_user_data = array( 'end_time' => $end_time, 'status' => 0);
    //                     //最的一条结束时间置为当前23:59:59 并置为无效
    //                     $this -> get_repository('PlanUser') -> save($plan_user_data, $plan_info['id']);
    //                     //插入最新方案
    //                     $add_data = array(
    //                             'muid'          =>  $muid,
    //                             'plan_id'       =>  $scheme,
    //                             'create_time'   =>  date('Y-m-d H:i:s', time()),
    //                             'start_time'    =>  date("Y-m-d", strtotime("+1 day")),
    //                             'status'        =>  1,
    //                         );
    //                     $this -> get_repository('PlanUser') -> save($add_data, 0);
    //                 }else {
    //                     $add_data = array(
    //                         'muid'          =>  $muid,
    //                         'plan_id'       =>  $scheme,
    //                         'create_time'   =>  date('Y-m-d H:i:s', time()),
    //                         'start_time'    =>  date("Y-m-d", strtotime("+1 day")),
    //                         'status'        =>  1,
    //                     );
    //                     $this -> get_repository('PlanUser') -> save($add_data, 0);  
    //                 }
    //             }  

    //             $return =  $this -> get_repository('UserMedia') -> save($map, $muid); 
    //         }else {
    //             //新建
    //             $return = $this -> get_repository('UserMedia') -> save($map, 0);
    //             //新增媒体主有结算方案的情况
    //             if( $scheme ) {
    //                 $scheme_data = array(
    //                         'muid'          =>  $return,
    //                         'plan_id'       =>  $scheme,
    //                         'create_time'   =>  date('Y-m-d H:i:s', time()),
    //                         'start_time'    =>  date("Y-m-d", strtotime("+1 day")),
    //                         'status'        =>  1,
    //                     );
    //                 $this -> get_repository('PlanUser') -> save($scheme_data, 0);
    //             }
    //         }
    //         $log_data = json_encode($_POST);
    //         if( $muid ) {
    //             $this -> writelog("编辑了媒体主muid为{$muid}的媒体主{$log_data}", 'media_user', $return, 'edit');
    //         }else {
    //             $this -> writelog("添加了媒体主{$log_data}", 'media_user', $return, 'add');
    //         }
    //         $this -> flashSession -> success('发布媒体主成功');
    //     }catch(\Exception $e) {
    //         $this -> write_exception_log($e);
    //         $this -> flashSession -> error($e -> getMessage());
    //     }
    //     return $this -> redirect('index/list');
    // }

    /**
     * 发布媒体主（添加、编辑）
     */
    public function publishAction(){
        try{
            if($this -> request -> isAjax() || !$this -> request -> isPost()){
                throw new \Exception('非法请求');
            }
            $muid           =   $this -> request -> getPost('muid', 'int');
            $media_name     =   $this -> request -> getPost('media_name', 'trim');
            $media_name     =   $this -> filter  -> sanitize($media_name, 'remove_xss');
            $md_compnay_name=   $this -> request -> getPost('md_compnay_name', 'trim');
            $md_compnay_name=   $this -> filter  -> sanitize($md_compnay_name, 'remove_xss');
            $intro          =   $this -> request -> getPost('intro', 'trim');
            $intro          =   $this -> filter  -> sanitize($intro, 'remove_xss');

            $media_type     =   $this -> request -> getPost('media_type', 'int');
            
            $scheme         =   $this -> request -> getPost('scheme', 'int');
            
            $contact_name   =   $this -> request -> getPost('contact_name', 'trim');
            $contact_name   =   $this -> filter  -> sanitize($contact_name, 'remove_xss');
            $email          =   $this -> request -> getPost('email', 'trim');
            $email          =   $this -> filter  -> sanitize($email, 'remove_xss');
            $phone          =   $this -> request -> getPost('phone', 'trim');
            $phone          =   $this -> filter  -> sanitize($phone, 'remove_xss');
            $qq             =   $this -> request -> getPost('qq', 'trim');
            $qq             =   $this -> filter  -> sanitize($qq, 'remove_xss');

            $validator_map = array('media_name' => $media_name, 'md_compnay_name' => $md_compnay_name, 'intro' => $intro);
            /** 添加验证规则 */
            if( !$muid ) {
                $this -> validator -> add_rule('media_name', 'required', '请填写媒体主名称');
                $validator_map['media_name'] = $media_name;
            }
            $this -> validator -> add_rule('media_name', 'required', '媒体名称不能为空');
            $this -> validator -> add_rule('md_compnay_name', 'required', '公司名称不能为空');
            $this -> validator -> add_rule('intro', 'required', '媒体简介不能为空');

            $this -> validator -> add_rule('scheme', 'required', '请选择结算方案');
            $validator_map['scheme']    =   $scheme;
            /** 截获验证异常 */
            $error = $this -> validator -> run( $validator_map );
            if($error) {
                $error = array_values($error);
                $error = $error[0];
                throw new \Exception($error['message'], $error['code']);
            }
            /** 发布 */
           $map =  array(
                    'media_name'         =>  $media_name,
                    'md_compnay_name'    =>  $md_compnay_name,
                    'intro'              =>  $intro,
                    'media_type'         =>  $media_type,
                    'scheme'             =>  $scheme,
                    'contact_name'       =>  $contact_name,
                    'email'              =>  $email,
                    'phone'              =>  $phone,
                    'qq'                 =>  $qq,
            );
            if( !$muid ) {
                $map['media_name'] = $media_name;
            }
            if( $muid ) {
                //编辑 
                $media_user = $this -> get_repository('UserMedia') -> get_user_by_muid($muid);
                //编辑时更新了结算方案的情况
                if($media_user['scheme'] != $scheme ) {
                    //获取最新的结算方案记录
                    $plan_info = $this -> get_repository('PlanUser') -> get_info($muid);
                    if( $plan_info ) {
                        //更新结束时间
                        $end_time = date("Y-m-d 23:59:59", time());
                        $plan_user_data = array( 'end_time' => $end_time, 'status' => 0);
                        //最的一条结束时间置为当前23:59:59 并置为无效
                        $this -> get_repository('PlanUser') -> save($plan_user_data, $plan_info['id']);
                        //插入最新方案
                        $add_data = array(
                                'muid'          =>  $muid,
                                'plan_id'       =>  $scheme,
                                'create_time'   =>  date('Y-m-d H:i:s', time()),
                                'start_time'    =>  date("Y-m-d", strtotime("+1 day")),
                                'status'        =>  1,
                            );
                        $this -> get_repository('PlanUser') -> save($add_data, 0);
                    }else {
                        $add_data = array(
                            'muid'          =>  $muid,
                            'plan_id'       =>  $scheme,
                            'create_time'   =>  date('Y-m-d H:i:s', time()),
                            'start_time'    =>  date("Y-m-d", strtotime("+1 day")),
                            'status'        =>  1,
                        );
                        $this -> get_repository('PlanUser') -> save($add_data, 0);  
                    }
                }  

                $return =  $this -> get_repository('UserMedia') -> save($map, $muid); 
            }else {
                //新建
                $return = $this -> get_repository('UserMedia') -> save($map, 0);
                //新增媒体主有结算方案的情况
                if( $scheme ) {
                    $scheme_data = array(
                            'muid'          =>  $return,
                            'plan_id'       =>  $scheme,
                            'create_time'   =>  date('Y-m-d H:i:s', time()),
                            'start_time'    =>  date("Y-m-d", strtotime("+1 day")),
                            'status'        =>  1,
                        );
                    $this -> get_repository('PlanUser') -> save($scheme_data, 0);
                }
            }
            $log_data = json_encode($_POST);
            if( $muid ) {
                $this -> writelog("编辑了媒体主muid为{$muid}的媒体主{$log_data}", 'media_user', $return, 'edit');
            }else {
                $this -> writelog("添加了媒体主{$log_data}", 'media_user', $return, 'add');
            }
            $this -> flashSession -> success('发布媒体主成功');
        }catch(\Exception $e) {
            $this -> write_exception_log($e);
            $this -> flashSession -> error($e -> getMessage());
        }
        return $this -> redirect('index/list');
    }


    /**
     * 404页面
     */
    public function notfoundAction(){
        return $this -> response -> setHeader('status', '404 Not Found');
    }

    public function deal_image(&$data,$image_url,$image_name='图片',$expression='jpg|png'){
        if($_FILES[$image_url]['tmp_name']){
            // 取得图片后缀
            $suffix = preg_match("/\.({$expression})$/", strtolower($_FILES[$image_url]['name']), $matches);
            if ($matches) {
                $suffix = $matches[0];
            } else {
                throw new \Exception("{$image_name}格式不正确！");
            }
            // 判断图片长和宽
            $img_info_arr = getimagesize($_FILES[$image_url]['tmp_name']);
            if (!$img_info_arr) {
                throw new \Exception("{$image_name}格式非法！");
            }
            $width  = $img_info_arr[0];
            $height = $img_info_arr[1];
            $image_width = $this->image_size[$image_url][0];
            $image_height = $this->image_size[$image_url][1];
            if($image_width!=0 && $image_height!=0){
                if ($width!=$image_width || $height!=$image_height){
                    throw new \Exception("{$image_name}尺寸错误，宽需为{$image_width}px，高需为{$image_height}px");
                }
            }
            $dir_name = "/media/".date("Ym/d/");
            if(!is_dir(UPLOAD_PATH.$dir_name)){
                mkdir(UPLOAD_PATH.$dir_name, 0755, true);
            }
            $save_name = $dir_name.time().'_'.rand(1000,9999).$suffix;
            $img_path = UPLOAD_PATH.$save_name;
            if(!move_uploaded_file($_FILES[$image_url]['tmp_name'], $img_path)){
                throw new \Exception("上传{$image_name}出错");
            }
            $data[$image_url] = $save_name;
            return true;
        }else {
            return false;
        }
    }

    /**
     * 操作状态
     */
    public function operationAction(){
        try{
            if($this -> request -> isAjax()){
                throw new \Exception('非法请求');
            }
            $type = $this -> request -> get('type', 'trim');
            $ids  = $this -> request -> get('ids', 'trim');
            $bh_reason  = $this -> request -> get('bh_reason', 'trim');
            $bh_explain = $this -> request -> get('bh_explain', 'trim');
           
            $ext = array();
            if($type == 'tg'){
                //审核通过检查数据完成性
                $user = $this -> get_repository('UserMedia') -> get_user_by_muid($ids);
                if(!$user['media_name']) {
                    throw new \Exception('媒体主名称不能为空');
                }
                if(!$user['md_compnay_name']) {
                    throw new \Exception('公司名称不能为空');
                }
                if(!$user['intro']) {
                    throw new \Exception('媒体简介不能为空');
                }
                if(!$user['scheme']) {
                    throw new \Exception('请选择结算方案');
                }
                $ext['status'] = 1;
            }else if($type == 'bh'){
                $ext['status']      = 2;
                $ext['bh_reason']   = $bh_reason;
                $ext['bh_explain']  = $bh_explain;
            }
            $return = $this -> get_repository('UserMedia') -> batch_update_record($ext, $ids);
            if($type == 'tg'){
                $this -> writelog("通过了媒体主,id为：{$ids}", 'media_user', $ids, 'edit');
            }else if($type == 'bh'){
                $this -> writelog("驳回了媒体主,id为：{$ids}", 'media_user', $ids, 'edit');
            }
            $this -> flashSession -> success('更新成功');
        }catch(\Exception $e){
            $this -> write_exception_log($e);
            $this -> flashSession -> error($e -> getMessage());
        }
        return $this -> redirect('index/list');
    }
}
