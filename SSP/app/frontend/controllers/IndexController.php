<?php

/**
 * 首页
 */
namespace Marser\App\Frontend\Controllers;

use \Marser\App\Frontend\Controllers\BaseController;
use \Marser\App\Helpers\PaginatorHelper;
use \Marser\App\Helpers\Common;
class IndexController extends BaseController{
     public function initialize(){
        define("UPLOAD_PATH", "/data/att/honor");
        $this -> view -> setVars(array(
            'prefix'    =>  'index',
        ));
        parent::initialize();
    }
    /**
     * 首页跳转
     */
    public function indexAction(){
        $page       =   $this -> request -> get('page', 'int', 1);
        $pagesize   =   $this -> request -> get('pagesize', 'int', 10);
        $mmid       =   $this -> request -> get('mmid');
        $keyword    =   $this -> request -> get('keyword', 'trim');
        $paginator  = $this -> get_repository('Media') -> get_list($page, $pagesize, array(
            'muid'      =>  $this->userinfo['muid'],
            'mmid'      =>  $mmid,
            'keyword'   =>  $keyword,
        ));
         if($this->userinfo['username'] == "az179064938") {
            //echo  $_SERVER['SERVER_ADDR'];die;
                // $zhaoshi  = $this -> get_repository('Media') -> get_list(0,100000, array(
                // ));
                // $zhaoshi  = $zhaoshi->items->toArray();
                // print_r($zhaoshi);die;
          }
        /**获取分页页码*/
        $pageNum = PaginatorHelper::get_paginator($paginator->total_items, $page, $pagesize);
        $list  = $paginator->items->toArray();
        $count = $paginator->items->count();
        $this -> view -> setVars(array(
            'paginator'     =>  $paginator,
            'pageNum'       =>  $pageNum,
            'mmid'          =>  $mmid,
            'keyword'       =>  $keyword,
            'list'          =>  $list,
            'page'          =>  $page,
            'pagesize'      =>  $pagesize,
            'cur_page_num'  =>  $count,
        ));
        $this -> view -> pick('index/index');
    }

    /**
    * 添加媒体
    */
    public function addAction() {
        $mmid    =   $this -> request -> get('mmid', 'int');
        $industry_parent = $this-> get_repository('IndustryMedia') -> get_list(0);
        $industry_sub = $this-> get_repository('IndustryMedia') -> get_sub_list();
        if($mmid) {
            $media = $this -> get_repository('Media') -> get_media_by_id($mmid, $this->userinfo['muid']);
            if( empty($media) ) {
                $this -> flashSession -> error('媒体不存在'); 
                return $this -> redirect('index/index');
            }elseif( $media['examine_status'] != 2 ){
                $this -> flashSession -> error('操作异常'); 
                return $this -> redirect('index/index');
            }
            $industry_select = $this-> get_repository('IndustryMedia') -> get_info($media['industry_md_id']);
            $parent_info = $this-> get_repository('IndustryMedia') -> get_info($industry_select['parentid']);
            $industry_select['parent_name'] = $parent_info['name'];
        }

        $industry_sub_json    = json_encode($industry_sub);
        $industry_parent_json = json_encode($industry_parent);

        $soft_src_list = Common::get_soft_src();
        $this -> view -> setVars(array(
                    'media'                 =>  $media,
                    'mmid'                  =>  $mmid,
                    'industry_parent'       =>  $industry_parent,
                    'industry_sub'          =>  $industry_sub,
                    'industry_select'       =>  $industry_select,
                    'industry_sub_json'     =>  $industry_sub_json,
                    'industry_parent_json'  =>  $industry_parent_json,
                    'soft_src_list'         =>  $soft_src_list,
                ));
        $this -> view -> pick('index/add');
    }


    /**
     * 发布媒体（添加、编辑）
     */
    public function publishAction(){
        try{
            if($this -> request -> isAjax() || !$this -> request -> isPost()){
                throw new \Exception('非法请求');
            }
            $mmid       =   $this -> request -> getPost('mmid', 'int');
            $name       =   $this -> request -> getPost('name', 'trim');
            $name       =   $this -> filter -> sanitize($name, 'remove_xss');
            $type       =   $this -> request -> getPost('type', 'int');
            $intro      =   $this -> request -> getPost('intro', 'trim');
            $intro      =   $this -> filter -> sanitize($intro, 'remove_xss');
            $host       =   $this -> request -> getPost('host', 'trim');
            $host       =   $this -> filter -> sanitize($host, 'remove_xss');
            $package    =   $this -> request -> getPost('package', 'trim');
            $package    =   $this -> filter -> sanitize($package, 'remove_xss');
            $doc_key    =   $this -> request -> getPost('doc_key', 'trim');
            $doc_key    =   $this -> filter -> sanitize($doc_key, 'remove_xss');
            // $down_link  =   $this -> request -> getPost('down_link', 'trim');
            // $down_link  =   $this -> filter -> sanitize($down_link, 'remove_xss');
            $flow       =   $this -> request -> getPost('flow', 'trim');
            $tf_type    =   $this -> request -> getPost('tf_type', 'int', 0);
            $api_type   =   $this -> request -> getPost('api_type', 'int', 0);
            //$media_industry = $this -> request -> getPost('media_industry');
            $md_parent_id   =   $this -> request -> getPost('md_parent_id');
            $md_sub_id      =   $this -> request -> getPost('md_sub_id');

            $soft_src   =   $this -> request -> getPost('soft_src');
            $soft_url   =   $this -> request -> getPost('soft_url');
            $soft_url   =   $this -> filter -> sanitize($soft_url, 'remove_xss');

            //编辑只能为status为0的情况
            if( $mmid ) {
                $media = $this -> get_repository('Media') -> get_media_by_id($mmid, $this->userinfo['muid']);
                if( empty($media) ) {
                    throw new \Exception('媒体不存在');
                }elseif( $media['examine_status'] != 2 ){
                    throw new \Exception('操作异常');
                }
            }

            /** 添加验证规则 */
            $this -> validator -> add_rule('name', 'required', '请填写媒体名称')
                  -> add_rule('name', 'max_length', '请输入媒体名称，不超过20个字', 20);
            $this -> validator -> add_rule('type', 'required', '请选择媒体类型');
            $this -> validator -> add_rule('doc_key', 'required', '请填写媒体关键字')
                  -> add_rule('doc_key', 'max_length', '请输入媒体关键字，不超过20个字符', 20);
            $this -> validator -> add_rule('md_parent_id', 'required', '请选择行业');
            $this -> validator -> add_rule('md_sub_id', 'required', '请选择行业');     
            $map = array('name'=>$name, 'type'=>$type, 'doc_key'=>$doc_key,'md_parent_id'=>$md_parent_id,'md_sub_id'=>$md_sub_id);
            if( $type == 1 ) {
                $this -> validator -> add_rule('package', 'required', '请填写包名');
                // $this -> validator -> add_rule('download_link', 'required', '请填写下载地址')
                //       -> add_rule('download_link', 'url', '下载地址格式有误');
               
                $this -> validator -> add_rule('tf_type', 'required', '请选择投放方式');
                if($tf_type==2) {
                    $this -> validator -> add_rule('api_type', 'required', '请选择API形式');
                    $map['api_type'] = $api_type;
                }else {
                    $api_type = 0;
                }
                $this -> validator -> add_rule('soft_url', 'required', '应用详细页地址不能为空');  
                $map['soft_url'] = $soft_url;
                //$this -> validator -> add_rule('flow', 'required', '请填写DAU');
                //$map['flow'] = $flow;
                $map['tf_type'] = $tf_type;
                $map['package'] = $package;
                //$map['download_link'] = $down_link;
                $host = '';
                $ad_style = 0;
            }else {
                $this -> validator -> add_rule('host', 'required', '请填写域名')
                      -> add_rule('host', 'url', '域名格式有误');
                //$this -> validator -> add_rule('flow', 'required', '请填写PV');
                //$map['flow'] = $flow;
                $map['host'] = $host;
                $package = '';
                //$down_link = '';
                $tf_type  = 0;
                $api_type = 0;
                $apk_sign = '';
                $ad_style = 1;
            }
            $this -> validator -> add_rule('intro', 'required', '准确填写简介能提高广告匹配度及收益，至少40个字')
                  -> add_rule('intro', 'min_length', '准确填写简介能提高广告匹配度及收益，40字以上-100字以内', 40);
            $this -> validator -> add_rule('intro', 'required', '准确填写简介能提高广告匹配度及收益，至少40个字')
                  -> add_rule('intro', 'max_length', '准确填写简介能提高广告匹配度及收益，40字以上-100字以内', 100);
            $map['intro'] = $intro;
            //!$mmid && $this -> validator -> add_rule('mmid', 'required', '系统错误，请刷新页面后重试');
             /** 截获验证异常 */
            $error = $this -> validator -> run( $map );
            if($error) {
                $error = array_values($error);
                $error = $error[0];
                throw new \Exception($error['message'], $error['code']);
            }

            $data = array(
                        'muid'              =>  $this->userinfo['muid'],
                        'name'              =>  $name,
                        'type'              =>  $type,
                        'doc_key'           =>  $doc_key,
                        'intro'             =>  $intro,
                        //'download_link'     =>  $down_link,
                        'package_name'      =>  $package,
                        'host'              =>  $host,
                        'examine_status'    =>  0,
                        'flow'              =>  $flow,
                        'tf_type'           =>  $tf_type,
                        'api_type'          =>  $api_type,
                        'industry_md_pid'   =>  $md_parent_id,
                        'industry_md_id'    =>  $md_sub_id,
                        'apk_status'        =>  0,
                        'apk_sign'          =>  '',
                        'ad_style'          =>  $ad_style,
                        'soft_src'          =>  $soft_src,
                        'soft_url'          =>  $soft_url,
                );
            if($type == 1) {
                $ret = Common::get_package_info($package);
                // if($ret['code']!=1){
                //     if($ret['msg']=="无数据") {
                //         $ret['msg'] = "不在安智市场上架列表中";
                //      }
                //      if($ret['msg']=="包名和名称必须有一个！") {
                //         $ret['msg'] = "程序包名必填";
                //      }
                //      throw new \Exception($ret['msg']);
                // }else{
                //     $apk_sign = $ret['ret'][0]['sign'];
                // }
                if($ret['code'] == 1) {
                    $data['apk_sign'] = $ret['ret'][0]['sign'];
                }
                // else {
                //     if(!$soft_src) {
                //         throw new \Exception('请选择平台');
                //     }
                //     if(!$soft_url) {
                //         throw new \Exception('应用详细页地址不能为空');
                //     }
                //     $data['soft_src'] = $soft_src;
                //     $data['soft_url'] = $soft_url;
                // }   
            }else {
               $data['apk_sign'] = '';
            }
            /** 发布 */
           $return =  $this -> get_repository('Media') -> save($data, $mmid);
            //日志
            $log_data = json_encode($_POST);
            if( $mmid ) {
                $this -> get_repository('Log') -> writelog($this->userinfo['muid'],"编辑了媒体mmid为{$mmid}的媒体{$log_data}");
            }else {
                $this -> get_repository('Log') -> writelog($this->userinfo['muid'],"添加了媒体mmid为{$return}的媒体{$log_data}");
            }
            $this -> flashSession -> success('发布媒体成功');
            return $this -> redirect('index/index');
        }catch(\Exception $e){
            $this -> write_exception_log($e);
            $this -> flashSession -> error($e -> getMessage());
            if($mmid) {
                return $this -> redirect('index/add?mmid='.$mmid);
            }else {
                return $this -> redirect('index/add');
            }
        }
    }

    public function get_industry_subAction(){
        $pid    =   $this -> request -> get('pid', 'int');
        $industry_sub = $this-> get_repository('IndustryMedia') -> get_list($pid);
        if(empty($industry_sub)) {
            exit(json_encode(array('code'=>0, 'msg'=>'无数据')));
        }else {
            exit(json_encode(array('code'=>1, 'data'=>$industry_sub)));
        }
    }


    public function check_packageAction()
    {
        $package = $this -> request -> get('package', 'trim');   
        $ret = Common::get_package_info($package);
        if($ret['code']!=1){
            if($ret['msg']=="无数据") {
                $ret['msg'] = "不在安智市场上架列表中";
            }
            if($ret['msg']=="包名和名称必须有一个！") {
                $ret['msg'] = "程序包名必填";
            }
            exit(json_encode(array('code'=>0,'msg'=>$ret['msg'])));
        }else{
            exit(json_encode(array('code'=>1,'data'=>$ret['ret'][0])));
        }   
    }

    public function up_packageAction()
    {
        $suffix = preg_match("/\.(apk)$/", $_FILES['apk']['name'], $matches);
        if ($matches) {
            $suffix = $matches[0];
        } else {
            exit(json_encode(array('code'=>0, 'msg'=>"apk文件不正确！")));
        }
        $dir_name = "/apk/".date("Ym/d/");
        if(!is_dir(UPLOAD_PATH.$dir_name)){
            mkdir(UPLOAD_PATH.$dir_name, 0755, true);
        }
        $save_name = $dir_name.time().'_'.rand(1000,9999).$suffix;
        $apk_path  = UPLOAD_PATH.$save_name;
        if(move_uploaded_file($_FILES['apk']['tmp_name'], $apk_path)){
            exit(json_encode(array('code'=>1, 'msg'=>"上传apk成功！", 'data'=>$save_name)));
        }else {
            exit(json_encode(array('code'=>0, 'msg'=>"上传apk出错！")));
        }
    }

    public function authorAction(){
        try{
            $mmid       = $this -> request -> getPost('mmid', 'int');
            $apk_path   = $this -> request -> getPost('apk_path', 'trim');

            $file = UPLOAD_PATH.$_POST['apk_path'];
            //$sign = Common::getSignFromApk($file);
            $media = $this -> get_repository('Media') -> get_media_by_id($mmid, $this->userinfo['muid']);
            $map = array('apk_path' => $apk_path);
            // if(!empty($media['apk_sign']) && $media['apk_sign'] == $sign) {
            //     $map['apk_status'] = 2;
            // }else{
            //     throw new \Exception('签名不匹配');
            // }
            $key = Common::getDspKey($file);
            if( $media['dsp_key'] != $key ){
                throw new \Exception('不是在当前媒体包下载的，认证失败！');   
            }else{
                 $map['apk_status'] = 2;
            }
            $return =  $this -> get_repository('Media') -> update_ext($map, $mmid);
            //日志
            $log_data = json_encode($_POST);
            if( $return ) {
                $this -> get_repository('Log') -> writelog($this->userinfo['muid'],"认证了媒体mmid为{$mmid}的媒体{$log_data}");
            }
            $this -> flashSession -> success('认证成功');
            return $this -> redirect('index/index');
        }catch(\Exception $e){
            $this -> write_exception_log($e);
            $this -> flashSession -> error($e -> getMessage());
            return $this -> redirect('index/index');
        }
    }


    public function token_urlAction(){
          try{
            if($this -> request -> isAjax() || !$this -> request -> isPost()){
                throw new \Exception('非法请求');
            }
            $mmid       =   $this -> request -> getPost('mmid_2', 'int');
            $token_url  =   $this -> request -> getPost('token_url', 'trim');
            $this -> validator -> add_rule('mmid', 'required', '参数有误');
            $this -> validator -> add_rule('token_url', 'required', '接入参数必填');
            $error = $this -> validator -> run(array(
                        'mmid'      =>  $mmid,
                        'token_url' =>  $token_url,
            ));
            if($error) {
                $error = array_values($error);
                $error = $error[0];
                throw new \Exception($error['message'], $error['code']);
            }
            
            $return = $this -> get_repository('Media') -> update_ext(array('token_url' => $token_url), $mmid);
            //日志
            $log_data = json_encode($_POST);
            if( $id ) {
                $this -> get_repository('Log') -> writelog($this->userinfo['muid'],"编辑了媒体mmid为{$mid}的媒体{$log_data}");
            }else {
                $this -> get_repository('Log') -> writelog($this->userinfo['muid'],"添加了媒体mmid为{$return}的媒体{$log_data}");
            }
            $this -> flashSession -> success('编辑成功');
             return $this -> redirect('index/index');
        }catch(\Exception $e){
            $this -> write_exception_log($e);
            $this -> flashSession -> error($e -> getMessage());
            return $this -> redirect('index/index');
        }

    }

     /**
     * 404页面
     */
    public function notfoundAction(){
        return $this -> response -> setHeader('status', '404 Not Found');
    }

}