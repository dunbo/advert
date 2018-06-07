<?php

/**
 * 首页
 */
namespace Marser\App\Frontend\Controllers;

use \Marser\App\Frontend\Controllers\BaseController,
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
        $id		    =	$this -> request -> get('id');
        $switch	    =	$this -> request -> get('switch', 'int',2);
        $mname  	=	$this -> request -> get('mname', 'trim');
        $aname      =   $this -> request -> get('aname', 'trim');
        $map = array('id' =>  $id, 'muid' =>$this->userinfo['muid'], 'mname'   =>  $mname, 'aname' => $aname,'switch'    =>  $switch, 'status' => 1);
        if($switch == 2) {
            unset($map['switch']);
        }
        $paginator = $this -> get_repository('Position') -> get_list($page, $pagesize, $map);
        $pageNum = PaginatorHelper::get_paginator($paginator->total_items, $page, $pagesize);
        $list  = $paginator->items->toArray();
        $count = $paginator->items->count();
        $this -> view -> setVars(array(
            'paginator'	=>	$paginator,
            'pageNum'	=>	$pageNum,
            'list'		=>	$list,
            'id'		=>	$id,
            'mname'	    =>	$mname,
            'aname'     =>  $aname,
            'switch'	=>	$switch,
            'page'          =>  $page,
            'pagesize'      =>  $pagesize,
            'cur_page_num'  =>  $count,
        ));
        $this -> view -> pick('position/index');
    }

    /**
    * 添加广告位
    */
    public function addAction() {
        $id    =   $this -> request -> get('id', 'int');
        if( $id ) {
            $position = $this -> get_repository('Position') -> get_position_by_id($id,$this->userinfo['muid']);
            if( empty($position) ) {
                $this -> flashSession -> error('广告位不存在'); 
                return $this -> redirect('position/index');
            }elseif( $position['examine_status'] !=2 ){
                $this -> flashSession -> error('操作异常'); 
                return $this -> redirect('position/index');
            } 
        }
        $muid = $this->userinfo['muid'];
        //$templateList   =   $this -> get_repository('Template') -> get_list();
        $mediaList      =   $this -> get_repository('Media') -> get_all_list(array('status' => 1,'muid'=>$muid,'field'=>'mmid,name,type,tf_type,api_type'));
        //$strategyList   =   $this -> get_repository('Strategy') -> get_all_list(array('status' => 1,'muid'=>$muid,'field'=>'strategyid,name'));
        
        $temp_parent_list   =   $this -> get_repository('Template')->get_parent_list();
        $temp_sub_list      =   $this -> get_repository('Template')->get_sub_list();
        //print_r($temp_parent_list);die;
        $this -> view -> setVars(array(
                    'id'                =>  $id,
                    'position'          =>  $position,
                    'mediaList'         =>  $mediaList,
                    //'strategyList'  =>  $strategyList,
                    'temp_parent_list'  =>  json_encode($temp_parent_list),
                    'temp_sub_list'     =>  json_encode($temp_sub_list),    
                ));
        $this -> view -> pick('position/add');
    }


    /**
     * 发布广告位（添加、编辑）
     */
    public function publishAction(){
        try{
            if($this -> request -> isAjax() || !$this -> request -> isPost()){
                throw new \Exception('非法请求');
            }
            $id			=	$this -> request -> getPost('id', 'int');
            $name		=	$this -> request -> getPost('name', 'trim');
            $name       =   $this -> filter  -> sanitize($name, 'remove_xss');
            $mmid		=	$this -> request -> getPost('mmid', 'int');
            $tf_type	=	$this -> request -> getPost('tf_type', 'int');
            $t_pid      =   $this -> request -> getPost('t_pid', 'int');
            $t_id		=	$this -> request -> getPost('t_id', 'int');
            //$strategyid	=	$this -> request -> getPost('strategyid', 'int');
            if( $id ) {
                $position = $this -> get_repository('Position') -> get_position_by_id($id,$this->userinfo['muid']);
                if( empty($position) ) {
                    throw new \Exception('广告位不存在');
                }elseif( $position['examine_status'] !=2 ){
                    throw new \Exception('操作异常');
                } 
            }
            /** 添加验证规则 */
            $this -> validator -> add_rule('name', 'required', '请填写广告名称')
                  -> add_rule('name', 'max_length', '请输入广告名称，不超过20个字', 20);
            $this -> validator -> add_rule('mmid', 'required', '请选择媒体');
            //$this -> validator -> add_rule('tid', 'required', '请选择广告规格');
             /** 截获验证异常 */
            $error = $this -> validator -> run(array(
                        'id'            =>  $id,
                        'name'		    =>	$name,
                        'mmid'		    =>	$mmid,
                        //'tid'		    =>	$tid,
                        //'strategyid'	=>	$strategyid,
            ));
            if($error) {
                $error = array_values($error);
                $error = $error[0];
                throw new \Exception($error['message'], $error['code']);
            }
            $return = $this -> get_repository('Position') -> save(array(
                            'muid'              =>  $this->userinfo['muid'],
                            'name'		        =>	$name,
                            'mmid'		        =>	$mmid,
                            //'tf_type'           =>	$tf_type,
                            't_pid'             =>  $t_pid,
                            't_id'		        =>	$t_id,
                            //'strategyid'	    =>	$strategyid,
                            'switch'            =>  1,
                            'status'            =>  1,
                            'examine_status'    =>  0,
                ), $id);
            //日志
            $log_data = json_encode($_POST);
            if( $id ) {
                $this -> get_repository('Log') -> writelog($this->userinfo['muid'],"编辑了广告位id为{$id}的广告位{$log_data}");
            }else {
                $this -> get_repository('Log') -> writelog($this->userinfo['muid'],"添加了广告位id为{$return}的广告位{$log_data}");
            }
            $this -> flashSession -> success('发布广告位成功');
             return $this -> redirect('position/index');
        }catch(\Exception $e){
            $this -> write_exception_log($e);
            $this -> flashSession -> error($e -> getMessage());
            if($id) {
                return $this -> redirect('position/add?id='.$id);
            }else {
                return $this -> redirect('position/add');
            }
        }
    }


    /**
     * 广告位删除
     */
    public function deleteAction(){
        try{
            $id = intval($this -> request -> get('id', 'int'));
            $position = $this -> get_repository('Position') -> get_position_by_id($id,$this->userinfo['muid']);
            if( empty($position) ) {
                throw new \Exception('删除广告位失败');
            }elseif( $position['examine_status'] == 0 ) {
                throw new \Exception('删除广告位失败');
            }
            $affectedRows = $this -> get_repository('Position') -> delete($id);
            if(!$affectedRows){
                throw new \Exception('删除广告位失败');
            }
            $this -> get_repository('PositionRelaDsp') -> delete($id);
            $this -> flashSession -> success('删除广告位成功');
             $this -> get_repository('Log') -> writelog($this->userinfo['muid'],"删除了广告位id为{$id}的广告位");
        }catch(\Exception $e){
            $this -> write_exception_log($e);
            $this -> flashSession -> error($e -> getMessage());
        }
        return $this -> redirect('position/index');
    }

    /**
    * 获取代码
    */
    public function getCodeAction(){
        $id     = $this -> request -> get('id', 'int');
        $mmid   = $this -> request -> get('mmid', 'int');
        if( !$id || !$mmid ) {
            $this -> ajax_return('参数有误', 0);die;
        }
        //$position = $this -> get_repository('Position') -> get_position_by_id($id,$this->userinfo['muid']);
        $media_info = $this -> get_repository('Media') -> get_media_by_id($mmid, $this->userinfo['muid']);

        $js_code = <<<EOT
        http://u.anzhi.com/ui/xad/geturl/?az_posid={$id}&az_appkey={$media_info['appkey']}&hb=
EOT;
        if( !empty($media_info) ) {
            $this -> ajax_return('', 1, $js_code);die;
        }else {
            $this -> ajax_return('数据有误', 0);die;
        }
    }

    /**
    * 开关
    */
    public function onOffAction(){
        $id     = $this -> request -> get('id', 'int');
        $status = $this -> request -> get('status', 'int');
        if( !$id ) {
            $this -> ajax_return('参数有误', 0);die;
        }
        $position = $this -> get_repository('Position') -> OnOff($id, $status);
        if( !empty($position) ) {
            $titel = $status ? '开启' : '关闭';
            $this -> get_repository('Log') -> writelog($this->userinfo['muid'],"{$titel}了广告位id为{$id}的广告位");
            $this -> ajax_return('', 1);die;
        }else {
            $this -> ajax_return('操作失败', 0);die;
        }
    }

    /**
    * 下载sdk
    */
    public function downloadSDKAction(){
        $id     = $this -> request -> get('id', 'int');
        Header( "Content-type:   application/octet-stream "); 
        Header( "Accept-Ranges:   bytes "); 
        header( "Content-Disposition:   attachment;   filename=test.txt "); 
        header( "Expires:   0 "); 
        header( "Cache-Control:   must-revalidate,   post-check=0,   pre-check=0 "); 
        header( "Pragma:   public "); 
        echo $id;
        echo "勇士未免总冠军是不是";die;
    }

}