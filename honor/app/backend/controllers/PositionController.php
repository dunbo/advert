<?php

/**
 * 首页
 */
namespace Marser\App\Backend\Controllers;

use \Marser\App\Backend\Controllers\BaseController,
    \Marser\App\Helpers\PaginatorHelper;

class PositionController extends BaseController{
    public function initialize(){
        $this -> view -> setVars(array(
            'prefix'    =>  'position',
        ));
        parent::initialize();
    }
    /**
     * 首页跳转
     */
    public function indexAction(){
        $page	    =	$this -> request -> get('page', 'int', 1);
        $pagesize   =   $this -> request -> get('pagesize', 'int', 10);
        $ad_name	=	$this -> request -> get('ad_name', 'trim');
        $md_name	=	$this -> request -> get('md_name', 'trim');
        $username   =   $this -> request -> get('username', 'trim');
        $srch_type  =   $this -> request -> get('srch_type', 'trim', 'sh');
        /**分页获取文章列表*/
        $status_arr = array('sh'=>0, 'tg'=>1, 'ntg'=>2);
        if( $username ) {
            $user = $this -> get_repository('UserMedia') -> get_user_by_username($username);
            $muid = !empty($user)?$user['muid']:0;
        }else {
            $muid = 0;
        }
        $map = array(
                'ad_name'   =>  $ad_name, 
                'md_name'   =>  $md_name, 
                'status'    =>  1,
                'srch_type' => $status_arr[$srch_type],
                'muid'      =>  $muid,
            );
        $paginator  =   $this -> get_repository('Position') -> get_list($page, $pagesize, $map);
        $pageNum    =   PaginatorHelper::get_paginator($paginator->total_items, $page, $pagesize);
        $list       =   $paginator->items->toArray();
        $count      =   $paginator->items->count();
        foreach ($list as $k => $val) {
            $dsp_list = $this -> get_repository('PositionRelaDsp') -> get_list_by_ad_pos_id($val['id']);
            foreach ($dsp_list as $kk => $vv) {
                $dsp_info = $this -> get_repository('DspConfig') -> get_info($vv['dsp_id']);
                $dsp_list[$kk]['dsp_name'] = isset($dsp_info['dsp_name'])?$dsp_info['dsp_name']:'';
            }
            $list[$k]['dsp_id_arr'] = $dsp_list;
        }
        $this -> view -> setVars(array(
            'title'         =>  '广告位管理',
            'paginator'     =>  $paginator,
            'pageNum'       =>  $pageNum,
            'ad_name'       =>  $ad_name,
            'md_name'       =>  $md_name,
            'username'      =>  $username,
            'list'          =>  $list,
            'page'          =>  $page,
            'pagesize'      =>  $pagesize,
            'cur_page_num'  =>  $count,
            'srch_type'     =>  $srch_type,
        ));

        $this -> view -> pick('position/index');
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
            }else if($type == 'bh'){
                $ext['examine_status'] = 2;
                $ext['bh_reason'] = $bh_reason;
                $ext['bh_explain'] = $bh_explain;
            }else if($type == 'tag'){
                $ext['activetag_sp'] = $activetag_sp;
            }
            $ext['type'] = $type;
            $return = $this -> get_repository('Position') -> batch_update_record($ext, $ids);
            if($type == 'tg'){
                $this -> writelog("通过了广告位,id为：{$ids}", 'media_ad_position', $ids, 'edit');
            }else if($type == 'bh'){
                $this -> writelog("驳回了广告位,id为：{$ids}", 'media_ad_position', $ids, 'edit');
            }
            $this -> flashSession -> success('更新成功');
        }catch(\Exception $e){
            $this -> write_exception_log($e);
            $this -> flashSession -> error($e -> getMessage());
        }
        return $this -> redirect('position/index');
    } 


    /**
    * 查看广告位 
    */
    public function detailAction() {
        $id         =   $this -> request -> get('id', 'int');
        $srch_type  =   $this -> request -> get('srch_type', 'trim','sh');
        $position = $this -> get_repository('Position') -> get_position_by_id($id);
        $muid = $position['muid'];
         $temp_parent_list  =   $this -> get_repository('Template')->get_parent_list();
        $temp_sub_list      =   $this -> get_repository('Template')->get_sub_list();
        $mediaList          =   $this -> get_repository('Media') -> get_list_by_muid($muid, array('mmid,name'));
        //$strategyList   =   $this -> get_repository('Strategy') -> get_all_list(array('status' => 1,'muid'=>$muid,'field'=>'strategyid,name'));
        $this -> view -> setVars(array(
                    'id'            =>  $id,
                    'position'      =>  $position,
                    'mediaList'     =>  $mediaList,
                    //'strategyList'  =>  $strategyList,
                    'srch_type'     =>  $srch_type,
                    'temp_parent_list'  =>  json_encode($temp_parent_list),
                    'temp_sub_list'     =>  json_encode($temp_sub_list),    
                ));
        $this -> view -> pick('position/detail');
    }

    /**
     * 编辑广告位
     */
    public function publishAction(){
        try{
            if($this -> request -> isAjax() || !$this -> request -> isPost()){
                throw new \Exception('非法请求');
            }
            $srch_type  =   $this -> request -> getPost('srch_type', 'trim');
            $id         =   $this -> request -> getPost('id', 'int');
            $mmid       =   $this -> request -> getPost('mmid', 'int');
            $tf_type    =   $this -> request -> getPost('tf_type', 'int');
            $t_pid      =   $this -> request -> getPost('t_pid', 'int');
            $t_id       =   $this -> request -> getPost('t_id', 'int');
            //$strategyid =   $this -> request -> getPost('strategyid', 'int');
            if( !$id ) {
                throw new \Exception('广告位不存在');
            }
            $this -> validator -> add_rule('mmid', 'required', '请选择媒体');
            //$this -> validator -> add_rule('tid', 'required', '请选择广告规格');
             /** 截获验证异常 */
            $error = $this -> validator -> run(array(
                        'mmid'          =>  $mmid,
                        //'tid'           =>  $tid,
            ));
            if($error) {
                $error = array_values($error);
                $error = $error[0];
                throw new \Exception($error['message'], $error['code']);
            }
            /** 发布文章 */
            $map = array(
                            'mmid'              =>  $mmid,
                            'tf_type'           =>  $tf_type,
                            't_pid'             =>  $t_pid,
                            't_id'              =>  $t_id,
                            //'strategyid'        =>  $strategyid,
                );
            //已通过的广告位不能编辑投放类型
            if( $srch_type == 'tg' ) {
                unset($map['tf_type']);
            }
            $return = $this -> get_repository('Position') -> save($map, $id);
            $log_data = json_encode($_POST);
            $this -> writelog("编辑了广告位id为{$id}的广告位{$log_data}", 'media_ad_position', $return, 'edit');
            $this -> flashSession -> success('发布广告位成功');
        }catch(\Exception $e){
            $this -> write_exception_log($e);
            $this -> flashSession -> error($e -> getMessage());
        }
        return $this -> redirect('position/index?srch_type='.$srch_type);
    }

    /**
    * 查看屏蔽策略 
    */
    public function StrategyAction() {
        try{
            $id   =  $this -> request -> get('id', 'int');
            $rows = $this-> get_repository('Strategy') -> get_strategy_by_id($id);
            if(empty($rows)) {
                throw new \Exception('该屏蔽策略不存在');
            }
            $industry_edit = @explode(',', $rows['industry_json']);
            $tag_edit = @json_decode($rows['tag_json'], true);

            $industry = $this-> get_repository('Industry') -> get_list();
            
            $industry_arr = array();
            $industry_total_num = $industry_select_num = 0;
            foreach ($industry as $k => $v) {
                if( $v['parentid']  == 0 ) {
                    $industry_arr[$k]['id']          = $v['id'];
                    $industry_arr[$k]['parent_name'] = $v['name'];
                     $i = 0;
                   foreach ($industry as $kk => $vv) {
                        if( $v['id'] == $vv['parentid'] ) {
                            $industry_arr[$k]['sub'][$kk]['id']         =   $vv['id'];
                            $industry_arr[$k]['sub'][$kk]['sub_name']   =   $vv['name'];
                            //检查节点是否被选中
                            if( in_array($vv['id'], $industry_edit) ) {
                                $industry_arr[$k]['sub'][$kk]['selected'] = 1;
                                $industry_select_num ++;
                                $i ++;
                            }else {
                                $industry_arr[$k]['sub'][$kk]['selected'] = 0;
                            }
                            $industry_total_num ++;
                        }
                    }
                    //编辑检查节点是否被选中
                    $num = 0;
                    foreach ($industry as $kkk => $vvv) {
                        if( $vvv['parentid'] == $v['id'] ) {
                            $num ++;
                        }
                    }
                    if( ($num - $i) > 0 ) {
                        $industry_arr[$k]['selected_left'] = 1; 
                    }else {
                        $industry_arr[$k]['selected_left'] = 0;
                    }
                    if( in_array( $v['id'], $industry_edit) ) {
                        $industry_arr[$k]['selected'] = 1; 
                    }else {
                        $industry_arr[$k]['selected'] = 0; 
                    } 
                }
            }

            $tags = \Marser\App\Backend\Models\ModelFactory::get_model('TagGroupModel') -> get_all_taglist();
            $tags_arr = array();
            $tags_total_num = $tags_select_num= 0;
            foreach ($tags as $key => $val) {
                $tags_arr[$key]['group_id'] =   $val['group_id'];
                $tags_arr[$key]['name']     =   $val['name'];
                $tags_arr[$key]['rank']     =   $val['rank'];
                $tags_arr[$key]['status']   =   $val['status'];
                $sub_tags   =  json_decode($val['tags'], true);
                $j = 0;
                foreach ($sub_tags as $kk => $vv) {
                    if( @in_array($vv['tag_id'], $tag_edit[$val['group_id']]) ) {
                        $sub_tags[$kk]['selected'] = 1;
                        $tags_select_num ++;
                        $j ++;
                    }else {
                        $sub_tags[$kk]['selected'] = 0;
                    }
                }
                $num = count($sub_tags);
                if( ($num - $j) > 0 ) {
                    $tags_arr[$key]['selected_left'] = 1; 
                }else {
                    $tags_arr[$key]['selected_left'] = 0;
                }
                if( !empty($tag_edit[$val['group_id']]) ) {
                    $tags_arr[$key]['selected'] = 1;
                }else {
                    $tags_arr[$key]['selected'] = 0;
                }
                $tags_arr[$key]['tags'] = $sub_tags;
                $tags_total_num += count($sub_tags);
            }
            $this -> view -> setVars(array(
                        'id'            =>  $id,
                        'rows'          =>  $rows,
                        'industry'      =>  $industry_arr,
                        'list'          =>  $tags_arr,
                        'perfix'        =>  'strategy_add',
                        'industry_sy_num'      =>   $industry_total_num - $industry_select_num,
                        'industry_select_num'  =>   $industry_select_num,
                        'tags_sy_num'          =>   $tags_total_num - $tags_select_num,
                        'tags_select_num'      =>   $tags_select_num,
                    ));
            $this -> view -> pick('position/strategy');
        }catch(\Exception $e){
            $this -> write_exception_log($e);
            $this -> flashSession -> error($e -> getMessage());
            $this -> redirect('position/index');
        }
    }

    /**
    * 导出广告位 
    */
    public function exportAction() {
         $ids    =  $this -> request -> get('ids');
         $list   =  $this -> get_repository('Position') -> batch_export($ids);
         //$admin_user_name = $this->session->get('admin')['admin_user_name'];
          if( !empty($list) ) {
            foreach ($list as $key => $val) {
                if( $val['industry_parentid'] ) {
                    $idty_parent = $this -> get_repository('Industry') -> get_info($val['industry_parentid']);
                }
                $list[$key]['industry_parentid_name'] = isset($idty_parent['name'])?$idty_parent['name']:'';
                if( $val['industryid'] ) {
                     $idty_sub = $this -> get_repository('Industry') -> get_info($val['industryid']);
                }
                $list[$key]['industry_name'] = isset($idty_sub['name'])?$idty_sub['name']:'';
            }
            //广告位
            $filename = '广告位.csv'; //设置文件名
            $str = ',,,,,,,,,,广告位申请表,,,,,,,'."\n";
            $str .= '序号,产品名称,所属行业,账号,系统平台（安卓/IOS）,包下载链接,程序主包名,应用关键词,应用简介（40字以上）,广告位名称,广告位类型,广告位尺寸,奖励视屏入口位置说明（如死亡复活，商店等）,日活,PV,客户预期ecpm,特殊要求及备注,广告位ID（Anzhi）,appid,广告位ID,第三方Appkey'."\n";
            foreach ( $list as $k => $val ) {
                $number         =   $k + 1;
                $media_name     =   str_replace(',', '，',  $val['media_name']);
                $industry       =   $val['industry_parentid_name'].' | '.$val['industry_name'];
                $account        =   '';
                if($val['type'] == 1) {
                    $platform = 'Android';
                    $download_link  = $val['download_link'];
                    $package_name   =   $val['package_name']; 
                }elseif($val['type'] == 2) {
                    $platform = 'H5';
                    $download_link  =  $val['host'];
                    $package_name   =  ''; 
                }else {
                    $platform = '';
                    $download_link = '';
                    $package_name   =  ''; 
                }
                $doc_key        =   str_replace(',', '，',$val['doc_key']);
                $intro          =   str_replace(array("\r\n","\n",','),array('','','，'),$val['intro']);
                $name           =   str_replace(',', '，',$val['name']);
                
                if($val['type'] == 1) {
                    if($val['tf_type'] == 1) {
                        $tf_type = 'SDK';
                    }elseif($val['tf_type'] == 2) {
                        if($val['api_type'] == 1) {
                            $tf_type = '通用广告API';
                        }elseif($val['api_type'] == 2) {
                            $tf_type = '互动广告API';
                        }else {
                            $tf_type='';
                        }
                    }else {
                        $tf_type = '类型有误';
                    }
                }elseif($val['type'] == 2) {
                    $tf_type='商业内容API';
                }else {
                    $tf_type='';
                }

                $size           =   str_replace(',', '，',  $val['size']);
                $video_intro    =   '';
                $DAU            =   $val['type']==1?$val['flow']:'';
                $PV             =   $val['type']==2?$val['flow']:'';
                $ecpm           =   '';
                $ts_intro       =   '';
                $ad_id          =   $val['id'];
                $str  .= $number.",".$media_name.",".$industry.",".$account.",".$platform.",".$download_link.",".$package_name.",".$doc_key.",".$intro.",".$name.",".$tf_type.",".$size.",".$video_intro.",".$DAU.",".$PV.",".$ecpm.",".$ts_intro.",".$ad_id."\n";
            }
            $str = iconv('utf-8','GBK//IGNORE', $str);
            $this -> export_csv($filename, $str);
         }else {
            $this -> flashSession -> error('只能导出广告位类型是SDK类型且未关联第三方DSP广告位ID');
            $this -> redirect('position/index');
         }
    }

    public function importAction()
    {
        if( $_POST ) {
            try{
                $dsp_id   = $this -> request -> getPost('dsp_id', 'int');
                $dsp_info = $this -> get_repository('DspConfig') -> get_info($dsp_id);
                if(!$dsp_id) {
                    throw new \Exception("导入前请先选择DSP"); 
                }
                $data_arr = $this->deal_csv('dsp');
                if(!empty($data_arr) ) {
                    foreach ($data_arr as $v) {
                        $arr = explode(',',$v);
                        if( !$arr[17] || !$arr[18] || !$arr[19] ) {
                            throw new \Exception("广告位名称为：{$arr[9]}的数据有误"); 
                            break;
                        }
                        if( strpos($arr[17], 'E+') ){
                              throw new \Exception("广告位名称为：{$arr[9]}的数据中广告位ID数据中不能含有科学计数法"); 
                            break;
                        }
                        if( strpos($arr[18], 'E+') ){
                              throw new \Exception("广告位名称为：{$arr[9]}的数据中appid数据中不能含有科学计数法"); 
                            break;
                        }
                        if( strpos($arr[19], 'E+') ){
                              throw new \Exception("广告位名称为：{$arr[9]}的数据中广告位ID数据中不能含有科学计数法"); 
                            break;
                        }
                        if($dsp_info['dsp_name']=="飞扬" && !trim($arr[20]) ) {
                            throw new \Exception("广告位名称为：{$arr[9]}的第三方appkey不能为空"); 
                            break;
                        }
                        $up_data[] = array(
                            'ad_pos_id'     =>  trim($arr[17]),
                            'dsp_appid'     =>  trim($arr[18]), 
                            'dsp_ad_pos_id' =>  trim($arr[19]), 
                            'dsp_id'        =>  $dsp_id,
                            'dsp_appkey'    =>  isset($arr[20])?trim($arr[20]):'',
                        );
                    }
                    foreach ($up_data as $val) {
                        $dsp_info = $this -> get_repository('PositionRelaDsp') -> get_dsp_info($val['ad_pos_id'],$val['dsp_id']);
                        if( !empty($dsp_info) ) {
                             $return = $this -> get_repository('PositionRelaDsp') -> update($val);
                        }else {
                             //检查第三方广告位id是否已经关了了安智广告位
                             $is_ext_dsp  =  $dsp_info = $this -> get_repository('PositionRelaDsp') -> get_dsp_info_ext($val['dsp_id'], $val['dsp_ad_pos_id']);
                             if($is_ext_dsp) {
                                throw new \Exception("第三方广告位ID为：{$val['dsp_ad_pos_id']}的已经关联过了"); 
                                break;
                             }else {
                                $return = $this -> get_repository('PositionRelaDsp') -> create($val);
                             }
                        }
                        $this -> writelog("导入给广告位ID为{$val['ad_pos_id']}关联第三方DSP广告位ID为{$val['dsp_id']}", 'media_ad_position', $return, 'edit');
                    }
                    $this -> flashSession -> success('导入成功');
                    echo "<script>window.parent.location.reload();window.parent.close_win()</script>";die;
                }else {
                    throw new \Exception('无数据');                    
                }   
            }catch(\Exception $e) {
                $this -> write_exception_log($e);
                $this -> flashSession -> error($e -> getMessage());
                echo "<script>window.parent.location.reload();window.parent.close_win()</script>";die;
            }
        }else {
            $this -> view -> setVars(array(
                'title'         =>  '导入DSP',
            ));
            $this -> view -> setMainView('Position/import');
        }
    }

    public function deal_csv($key,$csv_name='DSP广告位',$expression= array('csv')) {
       if($_FILES[$key]['tmp_name']){
            $ytypes  =   $_FILES[$key]['name'];
            $info    =   pathinfo($ytypes);
            $type    =   $info['extension'];//获取文件件扩展名
            if( !in_array($type, $expression) ) {
                throw new \Exception("{$csv_name}上传格式错误！");
            }
            $data_file  =   file_get_contents($_FILES[$key]['tmp_name']);
            //判断是否是utf-8编辑
            if(mb_check_encoding($data_file,"utf-8") != true) {
                $data_file  =   iconv("gbk","utf-8", $data_file);
            }
            $data_file  =   str_replace("\r\n","\n",$data_file);    
            $data_arr   =   explode("\n", $data_file);
            $data_arr   =   array_unique($data_arr);
            //礼包文件
            $newarr = array();
            $d_str = '';
            $str = '';
            $count = 0;
            foreach($data_arr as $k=>$v) {
                if($k == 0 || $k == 1) {
                    continue;
                }
                if( !trim($v) ) {
                    continue;
                }
                $newarr[] = $v;
            }
            if( !empty($newarr) ) {
                return array_unique($newarr);
            }else {
                throw new \Exception("上传{$csv_name}不能为空！");
            }
        }else {
            return false;
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

     public function searchAction(){
        $chars = $this -> request -> get('chars', 'trim');
        $activity = $this -> get_repository('DspConfig') -> get_dsp_by_name($chars);
        $arr = array();
        foreach ($activity as $data) {
            $arr[]=array(
              "id"          => isset($data['id'])?$data['id']:'',
              "data"        => isset($data['dsp_name'])?$data['dsp_name']:'',
              "thumbnail"   => '',
              "description" => '',
            );
        }
        echo json_encode($arr);die;
    }

    public function moveAction()
    {
        if( $_POST ) {
            try{
                $id_rela = $this -> request -> getPost('id_rela', 'int');
                $id      = $this -> request -> getPost('id', 'int');
                if(!$id || !$id_rela) {
                    throw new \Exception("请先选择广告"); 
                }
                $dsp_list = $this -> get_repository('PositionRelaDsp') -> get_list_by_ad_pos_id($id_rela);
                $time = time();
                foreach ($dsp_list as $k => $v) {
                    $data =  array(
                        'ad_pos_id'     => $id,
                        'dsp_ad_pos_id' => $v['dsp_ad_pos_id'],
                        'dsp_id'        => $v['dsp_id'],
                        'dsp_appid'     => $v['dsp_appid'],
                        'dsp_appkey'    => $v['dsp_appkey'],
                        'update_tm'     => $time,
                    );
                    $return = $this -> get_repository('PositionRelaDsp') -> create($data);
                }
                if($return) {
                    $this -> flashSession -> success('转移成功');
                    $this -> writelog("将广告位id位{$id_rela}DSP参数转移给广告位ID为{$id}广告位", 'media_ad_rela', $return, 'edit');
                    echo "<script>window.parent.location.reload();window.parent.close_win()</script>";die;
                }else{
                    throw new \Exception('转移失败');
                }
            }catch(\Exception $e) {
                $this -> write_exception_log($e);
                $this -> flashSession -> error($e -> getMessage());
                echo "<script>window.parent.location.reload();window.parent.close_win()</script>";die;
            }
        }else {
            $id       = $this -> request -> get('id', 'int');
            $position = $this -> get_repository('Position') -> get_position_by_id($id);
            $this -> view -> setVars(array(
                'title'         =>  'DSP参数转移',
                'position'      =>  $position,
            ));
            $this -> view -> setMainView('Position/move');
        }
    } 

    public function ad_searchAction(){
        $chars = $this -> request -> get('chars', 'trim');
        $position = $this -> get_repository('Position') -> get_position_rela_dsp($chars);
        $arr = array();
        foreach ($position as $data) {
            $arr[]=array(
              "id"          => isset($data['id'])?$data['id']:'',
              "data"        => isset($data['name'])?$data['name']:'',
              "thumbnail"   => '',
              "description" => '', 
            );
        }
        echo json_encode($arr);die;
    }

}