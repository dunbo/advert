<?php
/**
 * index
 */
namespace Marser\App\Backend\Controllers;

use \Marser\App\Backend\Controllers\BaseController;
use \Marser\App\Helpers\PaginatorHelper;
use \Marser\App\Helpers\Common;

class FinanceController extends BaseController{
    private  $image_size = array(
         'identity_front_img' 	=> array(0,0),
         'identity_back_img'	=> array(0,0),
         'license_img' 			=> array(0,0),
        );

    public function indexAction(){
        $page            =   $this -> request -> get('page', 'int', 1);
        $pagesize        =   $this -> request -> get('pagesize', 'int', 10);
        $cw_status       =   $this -> request -> get('cw_status', 'int', 0);
        $media_name      =   $this -> request -> get('media_name', 'trim');
        $company_name    =   $this -> request -> get('company_name', 'trim');
        $username        =   $this -> request -> get('username', 'trim');
        $paginator = $this -> get_repository('UserMedia') -> get_finance_list($page, $pagesize, array(
            'username'          =>  $username,
            'media_name'        =>  $media_name,
            'company_name'      =>  $company_name,
            'cw_status'         =>  $cw_status,
        ));
        $pageNum = PaginatorHelper::get_paginator($paginator->total_items, $page, $pagesize);
        $list = $paginator->items->toArray();
        $count = $paginator->items->count();
        //print_r($list);die;
        $this -> view -> setVars(array(
            'title'             => '财务管理',
            'paginator'         =>  $paginator,
            'pageNum'           =>  $pageNum,
            'username'          =>  $username,
            'media_name'        =>  $media_name,
            'company_name'      =>  $company_name,
            'list'              =>  $list,
            'page'              =>  $page,
            'pagesize'          =>  $pagesize,
            'cur_page_num'      =>  $count,
            'cw_status'         =>  $cw_status,
        ));

        $this -> view -> pick('finance/index');
    }

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
                    'title' =>  '财务管理>编辑财务信息',
                ));
        $this -> view -> pick('finance/add');
    }

    /**
     *（添加、编辑）
     */
    public function publishAction(){
        try{
            if($this -> request -> isAjax() || !$this -> request -> isPost()){
                throw new \Exception('非法请求');
            }
            $muid           =   $this -> request -> getPost('muid', 'int');
            if(empty($muid)) {
                throw new \Exception('非法请求');
            }
            $media_type     =   $this -> request -> getPost('media_type', 'int');
            //个人            
            $identity_name  =   $this -> request -> getPost('identity_name', 'trim');
            $identity_name  =   $this -> filter  -> sanitize($identity_name, 'remove_xss');
            $identity_num   =   $this -> request -> getPost('identity_num', 'trim');
            $identity_num   =   $this -> filter  -> sanitize($identity_num, 'remove_xss');
            //公司
            $company_name   =   $this -> request -> getPost('company_name', 'trim');
            $company_name   =   $this -> filter  -> sanitize($company_name, 'remove_xss');
            $reg_address    =   $this -> request -> getPost('reg_address', 'trim');
            $reg_address    =   $this -> filter  -> sanitize($reg_address, 'remove_xss');
            $reg_number     =   $this -> request -> getPost('reg_number', 'trim');
            $reg_number     =   $this -> filter  -> sanitize($reg_number, 'remove_xss');
            //财务信息
            $open_account   =   $this -> request -> getPost('open_account', 'trim');
            $open_account   =   $this -> filter  -> sanitize($open_account, 'remove_xss');
            $open_bank      =   $this -> request -> getPost('open_bank', 'trim');
            $open_bank      =   $this -> filter  -> sanitize($open_bank, 'remove_xss');
            $bank_account   =   $this -> request -> getPost('bank_account', 'trim');
            $bank_account   =   $this -> filter  -> sanitize($bank_account, 'remove_xss');
            $invoice_type   =   $this -> request -> getPost('invoice_type', 'int');
            $tax_rate       =   $this -> request -> getPost('tax_rate', 'int', 0);
            //联系人信息
            $contact_name   =   $this -> request -> getPost('contact_name', 'trim');
            $contact_name   =   $this -> filter  -> sanitize($contact_name, 'remove_xss');
            $email          =   $this -> request -> getPost('email', 'trim');
            $email          =   $this -> filter  -> sanitize($email, 'remove_xss');
            $phone          =   $this -> request -> getPost('phone', 'trim');
            $phone          =   $this -> filter  -> sanitize($phone, 'remove_xss');
            $qq             =   $this -> request -> getPost('qq', 'trim');
            $qq             =   $this -> filter  -> sanitize($qq, 'remove_xss');
            //身份证正反面
            $identity_front_img =   $this -> request -> getPost('identity_front_img', 'trim');
            $identity_back_img  =   $this -> request -> getPost('identity_back_img', 'trim');
            //营业执照
            $license_img        =   $this -> request -> getPost('license_img', 'trim');
            
            /** 添加验证规则 */
            if( $media_type == 1 ) {
                $this -> validator -> add_rule('identity_name', 'required', '请填写姓名');
                $this -> validator -> add_rule('identity_num', 'required', '请填写身份证号');
                $this -> validator -> add_rule('identity_front_img', 'required', '身份证正面必传');
                $this -> validator -> add_rule('identity_back_img', 'required', '身份证反面必传');
                $validator_map['identity_name'] = $identity_name;
                $validator_map['identity_num']  = $identity_num;
                $validator_map['identity_front_img']  = $identity_front_img;
                $validator_map['identity_back_img']   = $identity_back_img;
                $validator_map['invoice_type']  = 0;
                $validator_map['tax_rate']      = 0;
            }else {
                $this -> validator -> add_rule('company_name', 'required', '请填写结算公司名称');
                $this -> validator -> add_rule('reg_address', 'required', '请填写注册地址');
                $this -> validator -> add_rule('reg_number', 'required', '请填写纳税人识别号');
                $this -> validator -> add_rule('license_img', 'required', '营业执照必传');
                $validator_map['company_name']  =   $company_name;
                $validator_map['reg_address']   =   $reg_address;
                $validator_map['reg_number']    =   $reg_number;
                $validator_map['license_img']   =   $license_img;  

                $this -> validator -> add_rule('invoice_type', 'required', '请选择发票类型');
                $validator_map['invoice_type'] = $invoice_type;
                if($invoice_type == 2) {
                    $this -> validator -> add_rule('tax_rate', 'required', '请选择税率');
                    $validator_map['tax_rate'] = $tax_rate;
                }else{
                    $validator_map['tax_rate'] = 0;
                }
            }
            $this -> validator -> add_rule('open_account', 'required', '请填写收款账户');
            $this -> validator -> add_rule('open_bank', 'required', '请填写开户银行');
            $this -> validator -> add_rule('bank_account', 'required', '请填写银行账号');
            
            $validator_map['open_account'] = $open_account;
            $validator_map['open_bank']    = $open_bank;
            $validator_map['bank_account'] = $bank_account;
            
           
             if( $media_type == 1 ) {
                if( $_FILES['identity_front_img']['tmp_name'] ) {
                   $de = $this->deal_image($validator_map,'identity_front_img');
                }
                if( $_FILES['identity_back_img']['tmp_name'] ) {
                    $this->deal_image($validator_map,'identity_back_img');
                }
                $validator_map['license_img']   = '';
                $validator_map['company_name']  = '';
                $validator_map['reg_address']   = '';
                $validator_map['reg_number']    = '';
            }else {
                if( $_FILES['license_img']['tmp_name'] ) {
                   $this->deal_image($validator_map,'license_img');
                }
                $validator_map['identity_front_img'] = '';
                $validator_map['identity_back_img']  = '';
                $validator_map['identity_name']      = '';
                $validator_map['identity_num']       = '';
            }   
            /** 截获验证异常 */
            $error = $this -> validator -> run( $validator_map );
            if($error) {
                $error = array_values($error);
                $error = $error[0];
                throw new \Exception($error['message'], $error['code']);
            }
            /** 发布 */
            $validator_map['media_type']    = $media_type;
            $validator_map['contact_name']  = $contact_name;
            $validator_map['email']         = $email;
            $validator_map['phone']         = $phone;
            $validator_map['qq']            = $qq;
            $validator_map['update_tm']     = time();
            //编辑 
            $return =  $this -> get_repository('UserMedia') -> save($validator_map, $muid); 
            if($return) {
                //审核通过下编辑财务信息通知heifer后台
                $user = $this -> get_repository('UserMedia') -> get_user_by_muid($muid);
                if($user && $user['cw_status'] == 1) {
                    Common::remind_tax_rate_change(array('muid'=>$muid));
                }
                $log_data = json_encode($_POST);
            	$this -> writelog("财务管理中编辑了媒体主muid为{$muid}的媒体主{$log_data}", 'media_user', $return, 'edit');
            }
            $this -> flashSession -> success('编辑成功');
        }catch(\Exception $e) {
            $this -> write_exception_log($e);
            $this -> flashSession -> error($e -> getMessage());
            echo "<script>window.history.go(-1)</script>";die;
        }
        return $this -> redirect('finance/index');
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
                $ext['cw_status'] = 1;
            }else if($type == 'bh'){
                $ext['cw_status']      = 2;
                $ext['bh_reason']   = $bh_reason;
                $ext['bh_explain']  = $bh_explain;
            }
            $return = $this -> get_repository('UserMedia') -> batch_update_record($ext, $ids);
            if($type == 'tg'){
                Common::remind_tax_rate_change(array('muid'=>$ids));
                $this -> writelog("财务管理中通过了媒体主,id为：{$ids}", 'media_user', $ids, 'edit');
            }else if($type == 'bh'){
                $this -> writelog("财务管理中驳回了媒体主,id为：{$ids}", 'media_user', $ids, 'edit');
            }
            $this -> flashSession -> success('更新成功');
        }catch(\Exception $e){
            $this -> write_exception_log($e);
            $this -> flashSession -> error($e -> getMessage());
        }
        return $this -> redirect('finance/index');
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
}
