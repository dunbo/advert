<?php
/**
 * 媒体活动管理
 */
namespace Marser\App\Backend\Controllers;

use \Marser\App\Backend\Controllers\BaseController;
use \Marser\App\Helpers\PaginatorHelper;

class MediaActivityController extends BaseController{

    public function indexAction(){
        $page            =   $this -> request -> get('page', 'int', 1);
        $pagesize        =   $this -> request -> get('pagesize', 'int', 10);
        $muid            =   $this -> request -> get('muid', 'int', 0);
        $mmid            =   $this -> request -> get('mmid', 'int', 0);
        $ad_id           =   $this -> request -> get('ad_id', 'int', 0);
        $srch_type       =   $this -> request -> get('srch_type', 'trim','cur');
        //获取所有媒体主
        $media_user = $this -> get_repository('UserMedia')->get_all_list(array('field'=>'muid,username'));
        if( $muid ) {
        	$media_list = $this -> get_repository('Media') -> get_list_by_muid($muid,array('field'=>'mmid,name'));
        }else {
        	$mmid = 0;
        }
        if( $muid && $mmid ) {
        	$media_position = $this -> get_repository('Position') -> get_list_by_mmid($muid,$mmid,array('field'=>'id,name'));
        }else {
        	$ad_id = 0;
        }
        $paginator = $this -> get_repository('MediaActivity') -> get_list($page, $pagesize, array(
            'muid'      =>  $muid,
            'mmid'      =>  $mmid,
            'ad_id'		=>  $ad_id,
            'srch_type'	=>	$srch_type,
        ));
        $pageNum = PaginatorHelper::get_paginator($paginator->total_items, $page, $pagesize);
        $list  = $paginator->items->toArray();
        $count = $paginator->items->count();
        $this -> view -> setVars(array(
            'title'         =>  '媒体活动管理',
            'paginator'     =>  $paginator,
            'pageNum'       =>  $pageNum,
            'muid'          =>  $muid,
            'mmid'          =>  $mmid,
            'ad_id'         =>  $ad_id,
            'page'          =>  $page,
            'pagesize'      =>  $pagesize,
            'cur_page_num'  =>  $count,
            'srch_type'     =>  $srch_type,
            'list'			=>	$list,
            'media_user'	=>	$media_user,
            'media_list'	=>	$media_list,
            'media_position'=>	$media_position,
        ));

        $this -> view -> pick('mediaactivity/index');
    }

 	public function editAction() {
        $id = $this -> request -> get('id', 'int');  
        $rows = $this -> get_repository('MediaActivity') -> get_info($id);
        $rows['area_value'] = explode(';', $rows['area_value']);
        $this -> view -> setVars(array(
            'title'   =>  '编辑',
            'id'      =>  $id,
            'rows'    =>  $rows,
        ));
        $this -> view -> setMainView('mediaactivity/edit');
    }

    public function searchAction(){
        $chars = $this -> request -> get('chars', 'trim');
        $activity = $this -> get_repository('Activity') -> get_activity_by_name($chars);
        $arr = array();
        foreach ($activity as $data) {
            $arr[]=array(
              "id"          => isset($data['aid'])?$data['aid']:'',
              "data"        => isset($data['name'])?$data['name']:'',
              "thumbnail"   => '',
              "description" => '',
            );
        }
        echo json_encode($arr);die;
    }

    /**
     * 发布媒体活动（添加、编辑）
     */
    public function publishAction(){
        try{
            if($this -> request -> isAjax() || !$this -> request -> isPost()){
                throw new \Exception('非法请求');
            }
            $id      	=   $this -> request -> getPost('id', 'int');
            $muid       =   $this -> request -> getPost('muid_data', 'int');
            $mmid       =   $this -> request -> getPost('mmid_data', 'int');
            $ad_id      =   $this -> request -> getPost('ad_id_data', 'int');
            $aid        =   $this -> request -> getPost('aid', 'int');
            $probability=   $this -> request -> getPost('probability', 'int');
            $area_num   =   $this -> request -> getPost('area_num');
            $area_value =   $this -> request -> getPost('area_value');
            $start_tm   =   $this -> request -> getPost('start_tm');
            $end_tm   	=   $this -> request -> getPost('end_tm');

            $start_tm  = strtotime($start_tm);
            $end_tm    = strtotime($end_tm);

            $this -> validator -> add_rule('aid', 'required', '请填写活动');
            $this -> validator -> add_rule('probability', 'required', '请填写概率');
            $this -> validator -> add_rule('start_tm', 'required', '请选择开始时间');
            $this -> validator -> add_rule('end_tm', 'required', '请选择结束时间');
            $map = array(
            		'aid'			=>	$aid,
                    'probability'   =>  $probability,
                    'start_tm' 	 	=>  $start_tm,
                    'end_tm'     	=>  $end_tm,
                );
             /** 截获验证异常 */
            $error = $this -> validator -> run( $map );
            if($error) {
                $error = array_values($error);
                $error = $error[0];
                throw new \Exception($error['message'], $error['code']);
            }
            if( $start_tm > $end_tm ) {
                throw new \Exception('开始时间不能大于结束时间');
            }
            //处理地区编号，过滤重复值
            if(isset($area_num) && !empty($area_num)){
                $area_sp = $this -> filter_area_sp($area_num);
            }else{
                $area_sp = 0;
            }
            $data = array(
                    'muid'          =>  $muid,
                    'mmid'          =>  $mmid,
                    'ad_id'      	=>  $ad_id,
                    'aid'         	=>  $aid,
                    'probability'   =>  $probability,
                    'area_value'    =>  $area_value,
                    'area_sp'       =>  $area_sp,
                    'start_tm' 		=>  $start_tm,
                    'end_tm'        =>  $end_tm,
                );
            $return = $this -> get_repository('MediaActivity') -> save($data, $id);
            $this -> flashSession -> success('发布成功');
            $log_data = json_encode($_POST);
            if( $id ) {
                $this -> writelog("编辑了媒体活动管理id为{$id}。{$log_data}", 'media_shield_activity', $return, 'edit');
            	 echo "<script>window.parent.location.reload();window.parent.close_win()</script>";die;
            }else {
                $this -> writelog("添加了媒体活动管理{$log_data}", 'media_shield_activity', $return, 'add');
            }
        }catch(\Exception $e) {
            $this -> write_exception_log($e);
            $this -> flashSession -> error($e -> getMessage());
            if( $id ) {
                echo "<script>window.parent.location.reload();window.parent.close_win()</script>";die;
            }
        }
        return $this -> redirect("mediaactivity/index?muid={$muid}&mmid={$mmid}&ad_id={$ad_id}");
    }

	public function deleteAction() {
        $id = $this -> request -> get('id', 'int');
        $muid       =   $this -> request -> get('muid', 'int',0);
        $mmid       =   $this -> request -> get('mmid', 'int',0);
        $ad_id      =   $this -> request -> get('ad_id', 'int',0);
        try{
        	$data['status'] = 0;
            $return = $this -> get_repository('MediaActivity') -> save($data, $id);
            $this -> flashSession -> success('删除成功');
            $this -> writelog("删除了媒体活动,id为:{$id}", 'media_shield_activity', $id, 'edit');
        }catch(\Exception $e) {
            $this -> flashSession -> error($e -> getMessage());
        }
        return $this -> redirect("mediaactivity/index?muid={$muid}&mmid={$mmid}&ad_id={$ad_id}");
    }


    //过滤地区重复编号
    public function filter_area_sp($area_sp){
        //11,130000,130100;11,130000,130200;1,110000,;1,120000,120100
        $area_arr = explode(';', $area_sp); //初始数组
        $area_arr = array_unique($area_arr);
        $final_arr = array(); //最终数组
        $tmp_arr = array(); //临时数组
        foreach($area_arr as $key => $val){
            $val = explode(',', $val);
            //只选了省，没选市，省编号直接通过
            if(empty($val[2])){
                $final_arr[] = $val[1];
                continue;
            }
            //省编号为键，长度和市编号为值
            $tmp_arr[$val[1]]['len'] = $val[0];
            $tmp_arr[$val[1]][] = $val[2];
        }
        foreach($final_arr as $zhi){
            if(empty($tmp_arr[$zhi])){
                continue;
            }
            unset($tmp_arr[$zhi]);
        }
        if(!empty($tmp_arr)){
            foreach($tmp_arr as $k => $v){
                if(count($v) == ($v['len']+1)){ //市编号数量等于省长度，省编号直接通过
                    $final_arr[] = $k;
                    continue;
                }
                foreach ($v as $kk => $vv) {
                    if($kk === 'len'){
                        continue;
                    }
                    $final_arr[] = $vv; //市编号通过
                }
            }
        }
        return implode(',', $final_arr);
    }
}
