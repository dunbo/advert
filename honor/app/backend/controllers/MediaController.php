<?php
/**
 * index
 */
namespace Marser\App\Backend\Controllers;

use \Marser\App\Backend\Controllers\BaseController;
use \Marser\App\Helpers\PaginatorHelper;
use \Marser\App\Backend\Models\ModelFactory;
use \Marser\App\Helpers\Common;

class MediaController extends BaseController{

    public function indexAction(){
        $page            =   $this -> request -> get('page', 'int', 1);
        $pagesize        =   $this -> request -> get('pagesize', 'int', 10);
        $name            =   $this -> request -> get('name', 'trim');
        $company_name    =   $this -> request -> get('company_name', 'trim');
        $username        =   $this -> request -> get('username', 'trim');
        $srch_type       =   $this -> request -> get('srch_type', 'trim', 'sh');

        $status_arr = array('sh'=>0, 'tg'=>1, 'ntg'=>2, 'rz'=>3);
        $paginator = $this -> get_repository('Media') -> get_list($page, $pagesize, array(
            'username'      =>  $username,
            'name'          =>  $name,
            'company_name'  =>  $company_name,
            'srch_type'     =>  $status_arr[$srch_type],
        ));
        $pageNum = PaginatorHelper::get_paginator($paginator->total_items, $page, $pagesize);
        $list  = $paginator->items->toArray();
        // $admin_user_name = $this->session->get('admin')['admin_user_name'];
        // if($admin_user_name == "顿波") {
        //     print_r($list);
        // }
        $count = $paginator->items->count();
        if( !empty($list) ) {
            foreach ($list as $key => $val) {
                if($val['scheme']){
                    $phan_info = $this -> get_repository('Plan') -> get_info($val['scheme']);
                }
                $list[$key]['plan_name'] = isset($phan_info['name'])?$phan_info['name']:'';
                if( $val['industry_parentid'] ) {
                    $idty_parent = $this -> get_repository('Industry') -> get_info($val['industry_parentid']);
                }
                $list[$key]['industry_parentid_name'] = isset($idty_parent['name'])?$idty_parent['name']:'';
                if( $val['industryid'] ) {
                     $idty_sub = $this -> get_repository('Industry') -> get_info($val['industryid']);
                }
                $list[$key]['industry_name'] = isset($idty_sub['name'])?$idty_sub['name']:'';
            }
        }
        $this -> view -> setVars(array(
            'title'         =>  '媒体管理',
            'paginator'     =>  $paginator,
            'pageNum'       =>  $pageNum,
            'username'      =>  $username,
            'name'          =>  $name,
            'company_name'  =>  $company_name,
            'list'          =>  $list,
            'page'          =>  $page,
            'pagesize'      =>  $pagesize,
            'cur_page_num'  =>  $count,
            'srch_type'     =>  $srch_type,
        ));

        $this -> view -> pick('media/index');
    }

    /**
    * 查看媒体
    */
    public function detailAction() {
        $mmid       =   $this -> request -> get('mmid', 'int');
        $srch_type  =   $this -> request -> get('srch_type', 'trim','sh');
        if( !$mmid ) {
           $this -> flashSession -> error('参数有误');
            return $this -> redirect('media/index');
        }
        $industry = $this -> get_repository('Industry') -> get_list();
        $media = $this -> get_repository('Media') -> get_media_info($mmid);
        $soft_src_list = Common::get_soft_src();
        $this -> view -> setVars(array(
                    'industry'      =>  $industry,
                    'media'         =>  $media,
                    'mmid'          =>  $mmid,
                    'srch_type'     =>  $srch_type,
                    'soft_src_list' =>  $soft_src_list,
                    'title'     =>  '媒体详情',
                ));
        $this -> view -> pick('media/detail');
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
            $ids = $this -> request -> get('ids', 'trim');
            $bh_reason = $this -> request -> get('bh_reason', 'trim');
            $bh_explain = $this -> request -> get('bh_explain', 'trim');
            $activetag_sp = $this -> request -> get('activetag_sp', 'trim');
            if(!empty($activetag_sp)){
                $activetag_sp = array_unique($activetag_sp);
                $activetag_sp = implode(',', $activetag_sp);
            }else{
                $activetag_sp = 0;
            }
            $ext = array();
            if($type == 'tg'){
                $ext['examine_status'] = 1;
                $this -> get_repository('Media') -> check_media_data($ids);
            }else if($type == 'sh'){
                $ext['examine_status'] = 0;
            }else if($type == 'bh'){
                $ext['examine_status'] = 2;
                $ext['bh_reason'] = $bh_reason;
                $ext['bh_explain'] = $bh_explain;
            }else if($type == 'tag'){
                $ext['activetag_sp'] = $activetag_sp;
            }
            $return = $this -> get_repository('Media') -> batch_update_record($ext, $ids);
            $this -> flashSession -> success('更新成功');
            if($type == 'tg'){
                $this -> writelog("通过了媒体,id为:{$ids}", 'media_list', $ids, 'edit');
                $id_arr = explode(',', $ids);
                if(count($id_arr) == 1) {
                    $rows = $this -> get_repository('Media') -> get_media_by_id($ids);
                    if($rows['type']==1 && $rows['tf_type']==1) {
                        return $this -> redirect('media/index?srch_type=rz');
                    }else {
                        return $this -> redirect('media/index?srch_type=tg');
                    }
                }else {
                    return $this -> redirect('media/index?srch_type=tg');    
                }
            }else if($type == 'bh'){
                $this -> writelog("驳回了媒体,id为:{$ids}", 'media_list', $ids, 'edit');
                return $this -> redirect('media/index?srch_type=ntg');
            }else if($type == 'sh') {
                $this -> writelog("驳回了媒体,id为:{$ids}", 'media_list', $ids, 'edit');
                return $this -> redirect('media/index?srch_type=sh');
            }
            
        }catch(\Exception $e){
            $this -> write_exception_log($e);
            $this -> flashSession -> error($e -> getMessage());
            $de =  $this -> request -> getHeader('REFERER');
            header("Location:{$de}");die;
        }
        
    } 


    /**
     * 发布媒体（添加、编辑）
     */
    public function publishAction(){
        try{
            if($this -> request -> isAjax() || !$this -> request -> isPost()){
                throw new \Exception('非法请求');
            }
            $srch_type      =   $this -> request -> getPost('srch_type', 'trim');
            $mmid           =   $this -> request -> getPost('mmid', 'int');
            //$name           =   $this -> request -> getPost('name', 'trim');
            $type           =   $this -> request -> getPost('type', 'trim');
            $industry_pid   =   $this -> request -> getPost('industry_parentid', 'int');
            $industryid     =   $this -> request -> getPost('industryid', 'int');
            $level          =   $this -> request -> getPost('level', 'int');
            $package_name   =   $this -> request -> getPost('package_name', 'trim');
            $package_name   =   $this -> filter  -> sanitize($package_name, 'remove_xss');
            // $down_link      =   $this -> request -> getPost('download_link', 'trim');
            // $download_link  =   $this -> filter  -> sanitize($down_link, 'remove_xss');
            $host           =   $this -> request -> getPost('host', 'trim');
            $host           =   $this -> filter  -> sanitize($host, 'remove_xss');
            $doc_key        =   $this -> request -> getPost('doc_key', 'trim');
            $doc_key        =   $this -> filter  -> sanitize($doc_key, 'remove_xss');
            $anzhi_tag      =   $this -> request -> getPost('anzhi_tag', 'trim');
            $anzhi_tag      =   $this -> filter  -> sanitize($anzhi_tag, 'remove_xss');
            $intro          =   $this -> request -> getPost('intro', 'trim');
            $intro          =   $this -> filter  -> sanitize($intro, 'remove_xss');
            $flow           =   $this -> request -> getPost('flow', 'trim');
            $token_url      =   $this -> request -> getPost('token_url', 'trim');
            $token_url      =   $this -> request -> getPost('token_url', 'remove_xss');
            $doc_key        =   $a=str_replace("，", ",", $doc_key);
            $anzhi_tag      =   $a=str_replace("，", ",", $anzhi_tag);
            $soft_src       =   $this -> request -> getPost('soft_src');
            $soft_url       =   $this -> request -> getPost('soft_url', 'trim');
            $soft_url       =   $this -> filter -> sanitize($soft_url, 'remove_xss');
//print_r($industryid);die;
            // $this -> validator -> add_rule('name', 'required', '请填写媒体名称')
            //       -> add_rule('name', 'max_length', '请输入媒体名称，不超过10个字', 10);
            $this -> validator -> add_rule('industry_parentid', 'required', '请选择一级行业');
            $this -> validator -> add_rule('industryid', 'required', '请选择二级行业');
            $this -> validator -> add_rule('level', 'required', '请选择媒体等级');
            $this -> validator -> add_rule('doc_key', 'required', '请填写媒体关键字');
            $map = array('level'=>$level,'industry_parentid'=>$industry_pid,'industryid'=>$industryid,'type'=>$type, 'doc_key'=>$doc_key);
            $map['flow'] = $flow;
            if( $type == 1 ) {
                $this -> validator -> add_rule('package_name', 'required', '请填写包名');
                // $this -> validator -> add_rule('download_link', 'required', '请填写下载地址')
                //       -> add_rule('download_link', 'url', '下载地址格式有误');
                $map['package_name']    =   $package_name;
                //$map['download_link']   =   $down_link;
                $map['soft_src']        =   $soft_src;
                $map['soft_url']        =   $soft_url;
            }else {
                $this -> validator -> add_rule('host', 'required', '请填写域名')
                      -> add_rule('host', 'url', '域名格式有误');
                // $this -> validator -> add_rule('token_url', 'required', '请填写回调地址')
                //       -> add_rule('token_url', 'url', '回调地址格式有误');
                $map['host'] = $host; 
                $map['token_url']   =   $token_url;
            }
            $this -> validator -> add_rule('anzhi_tag', 'required', '请填写安智关键字');
            $this -> validator -> add_rule('intro', 'required', '请填写媒体简介');
            $this -> validator -> add_rule('intro', 'required', '准确填写简介能提高广告匹配度及收益，40字以上-100字以内')
                  -> add_rule('intro', 'min_length', '准确填写简介能提高广告匹配度及收益，40字以上-100字以内', 40);
            $this -> validator -> add_rule('intro', 'required', '准确填写简介能提高广告匹配度及收益，至少40个字')
                  -> add_rule('intro', 'max_length', '准确填写简介能提高广告匹配度及收益，40字以上-100字以内', 100);
            
            $map['anzhi_tag']   =   $anzhi_tag;
            $map['intro']       =   $intro;

            /** 截获验证异常 */
            $error = $this -> validator -> run( $map );
            if($error) {
                $error = array_values($error);
                $error = $error[0];
                throw new \Exception($error['message'], $error['code']);
            }
            $anzhi_key = implode(',', array($doc_key, $anzhi_tag));
            $tag_id_str = ModelFactory::get_model('TagModel') -> add_tags_getids($anzhi_key);
            $map['tag_id_str']          =   $tag_id_str;

            $return = $this -> get_repository('Media') -> save($map, $mmid);
            $log_data = json_encode($_POST);
            if( $muid ) {
                $this -> writelog("编辑了媒体mmid为{$mmid}的媒体{$log_data}", 'media_list', $return, 'edit');
            }else {
                $this -> writelog("添加了媒体{$log_data}", 'media_list', $return, 'add');
            }
            $this -> flashSession -> success('发布媒体成功');
        }catch(\Exception $e) {
            $this -> write_exception_log($e);
            $this -> flashSession -> error($e -> getMessage());
        }
        return $this -> redirect('media/index?srch_type='.$srch_type);
    }

    /**
     * 操作状态
     */
    public function apk_statusAction(){
        try{
            $srch_type = $this -> request -> get('srch_type', 'trim');
            $status    = $this -> request -> get('status', 'trim');
            $ids       = $this -> request -> get('ids', 'trim');

            $status_arr = array('ntj'=>2, 'tg'=>1, 'bh'=>3);
            if(!array_key_exists($status, $status_arr)) {
                throw new \Exception('非法请求');    
            }
            if($srch_type != 'rz') {
                throw new \Exception('非法请求');    
            }
            $map = array('apk_status' => $status_arr[$status]); 
            $return = $this -> get_repository('Media') -> batch_update_record($map, $ids);

            if($status == 'ntj') {
                $this -> writelog("提交了认证,id为:{$ids}", 'media_list', $ids, 'edit');
            }elseif($status == 'bh') {
                $this -> writelog("驳回了认证,id为:{$ids}", 'media_list', $ids, 'edit');
            }elseif($status == 'tg') {
                $this -> writelog("认证通过了,id为:{$ids}", 'media_list', $ids, 'edit');
            }

            $this -> flashSession -> success('更新成功');
        }catch(\Exception $e){
            $this -> write_exception_log($e);
            $this -> flashSession -> error($e -> getMessage());
        }
        return $this -> redirect('media/index?srch_type=rz');
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
            $media = $this -> get_repository('Media') -> get_media_by_id($mmid);
            $map = array('apk_path' => $apk_path);
            // if(!empty($media['apk_sign']) && $media['apk_sign'] == $sign){
            //     $map['apk_status'] = 2;
            // }else{
            //     throw new \Exception('签名不匹配');
            // }
            $key = Common::getDspKey($file);
            if( $media['dsp_key'] != $key ){
                throw new \Exception('不是在当前媒体包下载的，认证失败！');   
            }else {
                $map['apk_status'] = 2;
            }
            $return =  $this -> get_repository('Media') -> update_ext($map, $mmid);
            //日志
            if( $return ) {
                $this -> writelog("认证了媒体mmid为{$mmid}的媒体", 'media_list', $mmid, 'edit');
            }
            $this -> flashSession -> success('认证成功');
            return $this -> redirect('media/index?srch_type=rz');
        }catch(\Exception $e){
            $this -> write_exception_log($e);
            $this -> flashSession -> error($e -> getMessage());
            return $this -> redirect('media/index?srch_type=rz');
        }
    }

    public function up_pkg_dspAction(){
       try{
            $mmid   = $this -> request -> getPost('mmid', 'int');
            $suffix = preg_match("/\.(apk)$/", $_FILES['dsp']['name'], $matches);
            if(!$mmid) {
                throw new \Exception('参数有误！');
            }
            if ($matches) {
                $suffix = $matches[0];
            } else {
                throw new \Exception('apk文件不正确！');
            }
            $dir_name = "/dsp/".date("Ym/d/");
            if(!is_dir(UPLOAD_PATH.$dir_name)){
                mkdir(UPLOAD_PATH.$dir_name, 0755, true);
            }
            $save_name = $dir_name.time().'_'.rand(1000,9999).$suffix;
            $dsp_path  = UPLOAD_PATH.$save_name;
            if(move_uploaded_file($_FILES['dsp']['tmp_name'], $dsp_path)){
                $key = Common::getDspKey($dsp_path); 
                if( !$key ) {
                    throw new \Exception('上传的DSP空包信息不完整');
                }   
                $data = array(
                    'dsp_path'  =>  $save_name,
                    'dsp_key'   =>  $key,
                );
               $return =  $this -> get_repository('Media') -> update_ext($data, $mmid);
                if( $return ) {
                    $this -> writelog("上传了dsp空包的媒体mmid为{$mmid}的媒体", 'media_list', $mmid, 'edit');
                    $this -> flashSession -> success('上传DSP空包成功');
                    return $this -> redirect('media/index?srch_type=sh');
                }else{
                    throw new \Exception('上传失败');
                }
            }else{
                 throw new \Exception('上传上游DSP失败');
            }
         }catch(\Exception $e){
            $this -> write_exception_log($e);
            $this -> flashSession -> error($e -> getMessage());
            return $this -> redirect('media/index?srch_type=sh');
        }
    }


    /**
    * 导出广告位 
    */
    public function exportAction() {
         $ids    =  $this -> request -> get('ids');
         $list   =  $this -> get_repository('Media') -> batch_export($ids);
         if( !empty($list) ) {
            foreach ($list as $key => $val) {
                if($val['scheme']){
                    $phan_info = $this -> get_repository('Plan') -> get_info($val['scheme']);
                }
                $list[$key]['plan_name'] = isset($phan_info['name'])?$phan_info['name']:'';
                if( $val['industry_parentid'] ) {
                    $idty_parent = $this -> get_repository('Industry') -> get_info($val['industry_parentid']);
                }
                $list[$key]['industry_parentid_name'] = isset($idty_parent['name'])?$idty_parent['name']:'';
                if( $val['industryid'] ) {
                     $idty_sub = $this -> get_repository('Industry') -> get_info($val['industryid']);
                }
                $list[$key]['industry_name'] = isset($idty_sub['name'])?$idty_sub['name']:'';
            }
        }
        if( !empty($list) ) {
            //广告位
            $filename = '媒体列表.csv'; //设置文件名
            $str = '媒体ID,媒体名称,媒体主名称,行业,平台,投放方式,量级(PV或DAU),包名,网站域名,媒体关键字,结算方案,创建时间,空包下载地址,应用平台,应用详情页'."\n";
            foreach ( $list as $k => $val ) {
                $mmid       = $val['mmid'];
                $media_name = $val['name'];
                $username   = $val['media_name'];
                $industry   = $val['industry_parentid_name']?$val['industry_parentid_name'].'/'.$val['industry_name']:'';
                if($val['type'] == 1) {
                    $platform = 'Android';
                }elseif ($val['type'] == 2) {
                    $platform = 'H5';
                }else {
                    $platform = '类型有误';
                }
                if($val['tf_type'] == 1) {
                    $tf_type = "SDK";
                }elseif ($val['tf_type'] == 2) {
                    if($val['api_type'] == 1) {
                        $tf_type = "通用广告API";
                    }elseif ($val['api_type'] == 2) {
                        $tf_type = "互动广告API";
                    }
                }else {
                    $tf_type = "类型有误";
                }
                $flow           =   $val['flow'];
                $package_name   =   $val['package_name'];
                $host           =   $val['host'];
                //$download_link  =   $val['download_link'];
                $doc_key        =   $val['doc_key'];
                $plan_name      =   $val['plan_name'];
                $create_tm      =   date('Y-m-d H:i:s', $val['create_tm']);
                $pkg_link       =   'http://honor.anzhi.com/admin/apks/anzhikong-debug.apk';
                
                if($val['type'] == 1 ) {
                    $soft_src       = Common::get_platform($val['soft_src']);
                    if($val['soft_url']) {
                        $soft_url = $val['soft_url'];
                    }else{
                        $soft_url = "http://www.anzhi.com/pkg/".$val['package_name'];
                    }
                }else{
                    $soft_src       =   '';
                    $soft_url       =   '';
                }

                $str  .= $mmid.",".$media_name.",".$username.",".$industry.",".$platform.",".$tf_type.",".$flow.",".$package_name.",".$host.",".$doc_key.",".$plan_name.",".$create_tm.",".$pkg_link.",".$soft_src.",".$soft_url."\n";
            }
            $str = iconv('utf-8','GBK//IGNORE', $str);
            $this -> export_csv($filename, $str);
         }else {
            $this -> flashSession -> error('请选择媒体');
            $this -> redirect('position/index');
         }
    }

    //输出格式
    function export_csv($filename, $data)
    {
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=".$filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $data;die;
    }

}
