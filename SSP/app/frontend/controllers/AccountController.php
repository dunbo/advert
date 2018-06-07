<?php

namespace Marser\App\Frontend\Controllers;

use \Marser\App\Frontend\Controllers\BaseController;
use \Marser\App\Helpers\PaginatorHelper;
use \Marser\App\Helpers\Common;

class AccountController extends BaseController{
    public function initialize(){
        define("UPLOAD_PATH", "/data/att/honor");
        parent::initialize();
    }
    private  $image_size = array(
        'identity_front_img'  => array(0,0),
        'identity_back_img'   => array(0,0),
        'license_img'         => array(0,0),
    );

    /**
     * 账户信息
     */
    public function indexAction(){
        $muid = $this->userinfo['muid'];
        $userInfo = $this -> get_repository('User')->get_user_by_muid($muid);
        //财务信息不完整
        $this -> view -> setVars(array(
            'prefix'    =>  'account_user',
            'user'      =>  $userInfo,
        ));
        $this -> view -> pick('account/index');
    }	

    public function financeAction(){
        $page       =   $this -> request -> get('page', 'int', 1);
        $pagesize   =   $this -> request -> get('pagesize', 'int', 10);
        $lt         =   $this -> request -> get('lt','trim', 'sr');
        
        $muid       = $this->userinfo['muid'];
        //账户余额
        $amount     = $this -> get_repository('Settlement') -> get_account_mount($muid);
        //可提现金额
        $tx_amount  = $this -> get_repository('Settlement') -> get_amount_in_cash($muid);
        //媒体主名称
        $media_name = $this -> get_repository('User') -> get_user_by_muid($muid,array('columns'=>'media_name'));
        $media_name =  $media_name['media_name'];
        if($lt == 'sr') {
            //收入列表
            $paginator = $this -> get_repository('Settlement') -> get_list($page, $pagesize,array(
                'muid'       =>  $muid,
            ));
            $pageNum = PaginatorHelper::get_paginator($paginator->total_items, $page, $pagesize);
            $list = $paginator->items->toArray();
            $count = $paginator->items->count();
            
            $this -> view -> setVars(array(
                'paginator'     =>  $paginator,
                'pageNum'       =>  $pageNum,
                'list'          =>  $list,
                'page'          =>  $page,
                'pagesize'      =>  $pagesize,
                'cur_page_num'  =>  $count,
                'prefix'        =>  'account_finance',
                'media_name'    =>  $media_name,
                'mount'         =>  $amount['mount']?$amount['mount']:0,
                'tx_amount'     =>  $tx_amount['mount']?$tx_amount['mount']:0,
                'bill'          =>  $tx_amount['count']?$tx_amount['count']:0,
            ));
            $this -> view -> pick('account/finance');
        }else {
            $paginator = $this -> get_repository('MediaPay') -> get_list($page, $pagesize,array(
                'muid'       =>  $muid,
            ));
            $pageNum = PaginatorHelper::get_paginator($paginator->total_items, $page, $pagesize);
            $list = $paginator->items->toArray();
            $count = $paginator->items->count();
            $this -> view -> setVars(array(
                'paginator'     =>  $paginator,
                'pageNum'       =>  $pageNum,
                'list'          =>  $list,
                'page'          =>  $page,
                'pagesize'      =>  $pagesize,
                'cur_page_num'  =>  $count,
                'prefix'        =>  'account_finance',
                'media_name'    =>  $media_name,
                'mount'         =>  $amount['mount']?$amount['mount']:0,
                'tx_amount'     =>  $tx_amount['mount']?$tx_amount['mount']:0, 
                'bill'          =>  $tx_amount['count']?$tx_amount['count']:0,
            ));
            $this -> view -> pick('account/flow');
        }
        
    }


    //提现
    public function put_forwardAction(){
        try {
            $muid = $this->userinfo['muid'];
            $map = array(
                'muid'  =>  $muid,
            );
            //申请提现 
            $ret = Common::put_forward($map);
            if($ret['code'] == 200){
                $this -> flashSession -> success($ret['msg']); 
                $log_data = json_encode($_POST);
                $this -> get_repository('Log') -> writelog($this->userinfo['muid'],"提现了金额为{$tx_mount}{$log_data}"); 
            }else{
                throw new \Exception($ret['msg']);
            }        
        } catch (\Exception $e) {
            $this -> write_exception_log($e);
            $this -> flashSession -> error($e -> getMessage());
        }
        return $this -> redirect('account/finance?lt=tx');
    }

    //下载账单
    public function downAction(){
        $id   = $this -> request -> get('id', 'int');
        $type = $this -> request -> get('type', 'int');
        $muid = $this->userinfo['muid'];
        $media_type = $this -> get_repository('User') -> get_user_by_muid($muid,array('columns'=>'media_type'));
        $media_type = $media_type['media_type'];
        try {
            if(!$id) {
                throw new \Exception('操作有误');
            }
            if($type == 0) {
                $filename = "结算函";
            }elseif($type == 1){
                $filename = "月度账单";
            }else {
                 throw new \Exception('操作有误');
            }
            $map = array(
                'muid'      =>  $muid,
                'id'        =>  $id,
                'type'      =>  $type,
                'muidType'  =>  $media_type,
            );
            //申请提现 
            $ret = Common::bill_download($map);
        } catch (\Exception $e) {
            $this -> write_exception_log($e);
            $this -> flashSession -> error($e -> getMessage());
        }
        return $this -> redirect('account/finance?lt=tx');
    }

    /**
     * 发布媒体主（添加、编辑）
     */
    public function publishAction(){
        try{
            if($this -> request -> isAjax() || !$this -> request -> isPost()){
                throw new \Exception('非法请求');
            }
            $muid           =   $this->userinfo['muid'];
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
            // $open_address   =   $this -> request -> getPost('open_address', 'trim');
            // $open_address   =   $this -> filter  -> sanitize($open_address, 'remove_xss');
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
            
            //检查是否是已通过状态
            $user_info = $this -> get_repository('User')->get_user_by_muid($muid);
            if($user_info['status'] == 1 && $user_info['cw_status'] == 1) {
                throw new \Exception('审核已通过，如需修改请联系商务');
            }
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
            //$this -> validator -> add_rule('open_address', 'required', '请填写开户地');
            $this -> validator -> add_rule('open_bank', 'required', '请填写收款银行');
            $this -> validator -> add_rule('bank_account', 'required', '请填写银行账号');
            
            $validator_map['open_account'] = $open_account;
            //$validator_map['open_address'] = $open_address;
            $validator_map['open_bank']    = $open_bank;
            $validator_map['bank_account'] = $bank_account;
            
           
             if( $media_type == 1 ) {
                if( $_FILES['identity_front_img']['tmp_name'] ) {
                    $this->deal_image($validator_map,'identity_front_img');
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
            $validator_map['media_type']    = $media_type;
            $validator_map['contact_name']  = $contact_name;
            $validator_map['email']         = $email;
            $validator_map['phone']         = $phone;
            $validator_map['qq']            = $qq;
            $validator_map['update_tm']     = time();
            $validator_map['bh_reason']     =  '';
            $validator_map['bh_explain']    =  '';
            $validator_map['cw_status']     =  0;
            //编辑 
            $return =  $this -> get_repository('User') -> update($validator_map, $muid); 
            if($return) {
                //通知heifer后台
                //Common::remind_tax_rate_change(array('muid'=>$muid));
                $log_data = json_encode($_POST);
                $this -> get_repository('Log') -> writelog($this->userinfo['muid'],"编辑了媒体主muid为{$muid}的媒体主{$log_data}"); 
            }
            $this -> flashSession -> success('编辑媒体主成功');
        }catch(\Exception $e) {
            $this -> write_exception_log($e);
            $this -> flashSession -> error($e -> getMessage());
            echo "<script>window.history.go(-1)</script>";die;
        }
        return $this -> redirect('account/index');
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