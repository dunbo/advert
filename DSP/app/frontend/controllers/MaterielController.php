<?php

/**
 * 广告计划控制器
 * @category PhalconDSP
 * @author haoshisuo 2017-9-18
 */

namespace Marser\App\Frontend\Controllers;

use \Marser\App\Frontend\Controllers\BaseController;
use \Marser\App\Frontend\Models\ModelFactory;
use \Marser\App\Helpers\PaginatorHelper;
use \Marser\App\Helpers\Common;

class MaterielController extends BaseController{
    private  $image_size = array(
         'copywriter_img' => array(0,0),
         'brand_img' => array(0,0),
        );

    public function initialize(){
        parent::initialize();
        $this -> view -> setVars(array(
            'prefix' => 'materiel',
        ));
    }

    /**
     * 广告计划列表
     */
    public function listAction(){
        $page           =  intval($this -> request -> get('page', 'trim', 1)); //当前页
        $pagesize       =  intval($this -> request -> get('pagesize', 'trim', 20)); //当前页
        $name           =  $this -> request -> get('name', 'trim'); //广告名称
        $status         =  intval($this -> request -> get('status', 'trim', 0)); //审核状态
        //$switch         =  $this -> request -> get('switch', 'trim'); //广告状态
        $tag            =  intval($this -> request -> get('tag', 'trim')); //统计数据天数
        $begin_tm       =  $this -> request -> get('sh_begin_tm', 'trim'); //开始时间
        $end_tm         =  $this -> request -> get('sh_end_tm', 'trim'); //结束时间
        $def_min_date   =  date("Y-m-d H:i:s ",strtotime('-30 day'));
        $def_max_date   =  date("Y-m-d H:i:s ",time());
        if(empty($tag)){
            if(empty($begin_tm) && empty($end_tm)){
                $tag = 2; //默认统计的数据
            }else{
                $tag = 0;
            }
        }
        $condition = array(
            'auid' => $this ->userinfo['auid'],
            'name' => $name,
        );
        switch ($status) {
            case 1://所有未删除
                $condition['examine_status'] = 0;
                break;
            case 2://启用中
                $condition['switch'] = 1;
                break;
            case 3://暂停中
                $condition['switch'] = 2;
                break;
            case 4://审核中
                $condition['examine_status'] = 1;
                break;
            case 5://审核未通过
                $condition['examine_status'] = 3;
                break;
            case 6://投放结束
                $condition['switch'] = 3;
                break;
            case 7://未开始
                $condition['switch'] = 4;
                break;
        }
        //分页获取列表
        $paginator = $this -> get_repository('Materiel') -> get_list($page, $pagesize, $condition);
        //获取分页页码
        $pageNum = PaginatorHelper::get_paginator($paginator->total_items, $page, $pagesize);
        $list = $paginator->items->toArray();
        $count = $paginator->items->count();
        $industry = ModelFactory::get_model('IndustryModel') -> select()->toArray();
        $hangye = array();
        foreach($industry as $val){
            $hangye[$val['id']] = $val['name'];
        }
        foreach($list as $key => $val){
            if(!empty($val['industry_parentid'])){
                $val['industry_pname'] = $hangye[$val['industry_parentid']];
            }else{
                $val['industry_pname'] = '';
            }
            if(!empty($val['industryid'])){
                $val['industry_name'] = $hangye[$val['industryid']];
            }else{
                $val['industry_name'] = '';
            }
            $list[$key] = $val;
        }
        
        if(is_array($list) && count($list) > 0){
            //广告位样式
            foreach ($list as $key => $val) {
                if($val['t_pid']==4){
                    $parent_template = $this -> get_repository('Template') -> get_info($val['t_pid']);
                    $sub_template    = $this -> get_repository('Template') -> get_info($val['t_id']);
                    $list[$key]['template'] = $parent_template['name'].'-'.$sub_template['name'].'-'.$sub_template['size'];
                }else{
                    $parent_template = $this -> get_repository('Template') -> get_info($val['t_pid']);    
                    $list[$key]['template'] = $parent_template['name'].'-'.$parent_template['size'];
                }
            }
            //关联广告数据表点击量、曝光量等
            $ext  = array(
                'tag'       =>  $tag,
                'begin_tm'  =>  $begin_tm,
                'end_tm'    =>  $end_tm,
            );
            $list = $this -> get_repository('Materiel') -> append_columns($list, $ext);
        }
        $this -> view -> setVars(array(
            'page'              =>  $page,
            'begin_tm'          =>  $begin_tm,
            'end_tm'            =>  $end_tm,
            'def_min_date'      =>  $def_min_date,
            'def_max_date'      =>  $def_max_date,
            'tag'               =>  $tag,
            'pagesize'          =>  $pagesize,
            'paginator'         =>  $paginator,
            'pageNum'           =>  $pageNum,
            'name'              =>  $name,
            'status'            =>  $status,
            'list'              =>  $list,
            'now'               =>  time(),
            'cur_page_num'      =>  $count,
        ));
        $this -> view -> pick('materiel/list');
    }

    /**
     * 创建广告页面
     */
    public function addAction(){
        $id    =   $this -> request -> get('id', 'int');
        // 媒体行业信息
        $industry = $this -> get_repository('Industry') -> select();
        if( $id ) {
            $materiel = $this -> get_repository('Materiel') -> get_info($id);
            $materiel['tf_area']    = explode(';', $materiel['tf_area']);
            $materiel['tf_mobile']  = explode(',', $materiel['tf_mobile']);
            if( empty($materiel) ) {
                $this -> flashSession -> error('广告位不存在'); 
                return $this -> redirect('materiel/list');
            }
        }        
        $temp_parent_list   =   $this -> get_repository('Template')->get_parent_list();
        $temp_sub_list      =   $this -> get_repository('Template')->get_sub_list();
        //添加时默认时间
        $timestamp   = date("Y-m-d 00:00:00", time());
        $timestamp_7 = date("Y-m-d 00:00:00", (time()+7*86400)); 
        $this -> view -> setVars(array(
            'id'       => $id,
            'materiel' => $materiel, 
            'template' => $template,
            'industry' => $industry,
            'temp_parent_list'  =>  json_encode($temp_parent_list),
            'temp_sub_list'     =>  json_encode($temp_sub_list),  
            'timestamp'     =>  $timestamp,
            'timestamp_7'   =>  $timestamp_7,    
        ));
        $this -> view -> pick('materiel/add');
    }

    /**
     * 创建、编辑广告逻辑处理
     */
    public function writeAction(){
        try{
            if($this -> request -> isAjax() || !$this -> request -> isPost()){
                throw new \Exception('非法请求');
            }
            $time = time();
            $id             =  intval($this -> request -> get('id', 'trim')); //判断添加还是编辑
            if(!empty($id)){
                $list = $this -> get_repository('Materiel') -> get_info($id);
                if($list['examine_status'] != 3 || $list['auid'] != $this ->userinfo['auid'] ){
                    throw new \Exception('非法操作');
                }
            }

            $api_type       =  $this -> request -> getPost('api_type', 'int', 1); //推广类型
            $tf_type        =  $this -> request -> getPost('tf_type', 'int'); //推广目标

            $name           =  $this -> request -> getPost('name', 'trim'); //广告名称
            $name           =  $this -> filter  -> sanitize($name, 'remove_xss');
            
            $day_budget     =  $this -> request -> getPost('day_budget', 'trim'); //日预算
            $day_budget     =  $this -> filter  -> sanitize($day_budget, 'remove_xss');

            $t_pid          =  $this -> request -> getPost('t_pid', 'int'); //广告样式父级
            $t_id           =  $this -> request -> getPost('t_id', 'int'); //广告样式子级

            //投放类型
            $tf_date_type   =  $this -> request -> getPost('tf_date_type', 'int');

            //投放日期
            $begin_tm       =  $this -> request -> getPost('begin_tm', 'trim'); 
            $begin_tm       =  strtotime($begin_tm);
            $end_tm         =  $this -> request -> getPost('end_tm', 'trim');
            $end_tm         =  strtotime($end_tm);

            $price          =  $this -> request -> getPost('price', 'trim');//单价
            $price          =  $this -> filter  -> sanitize($price, 'remove_xss');
            
            /** 精准投放信息 */
            $tf_area = $this -> request -> getPost('tf_area', 'trim'); //投放地区
            empty($tf_area) && $tf_area = '';
            $area_sp = $this -> request -> getPost('area_sp', 'trim'); //投放地区编号
            $tf_nettype = $this -> request -> getPost('tf_nettype', 'trim'); //网络环境
            empty($tf_nettype) && $tf_nettype = '';
            $tf_type = $this -> request -> getPost('tf_type', 'trim'); //投放平台
            empty($tf_type) && $tf_type = '';
            $tf_mobile = $this -> request -> getPost('tf_mobile', 'trim'); //机型
            empty($tf_mobile) && $tf_mobile = '';
            $media_attr_sp = 0; //媒体属性过滤字段
            //父行业存在则重新赋值
            $industry_parentid = $this -> request -> getPost('industry_parentid', 'trim');
            if(isset($industry_parentid) && !empty($industry_parentid)){
                $media_attr_sp = $industry_parentid;
            }else{
                $industry_parentid = '';
            }
            //子行业存在则父行业必存在
            $industryid = $this -> request -> getPost('industryid', 'trim');
            if(isset($industryid) && !empty($industryid)){
                if(strpos($industryid, ',') === false){
                    $media_attr_sp .= ','.$industryid;
                }else{
                    $media_attr_sp .= $industryid;
                    $industryid = '';
                }
            }else{
                $industryid = '';
            }
            //处理地区编号，过滤重复值
            if(isset($area_sp) && !empty($area_sp)){
                $area_sp = $this -> get_repository('Materiel') -> filter_area_sp($area_sp);
            }else{
                $area_sp = 0;
            }

            //创意图片
            $copywriter_img  =  $this -> request -> getPost('copywriter_img', 'trim');
            $copywriter_img  =  $this -> filter  -> sanitize($copywriter_img, 'remove_xss');
            $brand_img       =  $this -> request -> getPost('brand_img', 'trim');
            $brand_img       =  $this -> filter  -> sanitize($brand_img, 'remove_xss');
            //广告文案
            $copywriter_desc = $this -> request -> getPost('copywriter_desc', 'trim');
            $copywriter_desc =  $this -> filter  -> sanitize($copywriter_desc, 'remove_xss');
            //广告品牌
            $brand_desc =   $this -> request -> getPost('brand_desc', 'trim');
            $brand_desc =   $this -> filter  -> sanitize($brand_desc, 'remove_xss');
            $url        =   $this -> request -> getPost('url', 'trim');
            $url        =   $this -> filter  -> sanitize($url, 'remove_xss');

            $this -> set_img_size($t_pid, $t_id);
            $this -> get_repository('Materiel') -> check_name(array('name'=>$name), $id);
            /** 添加验证规则 */
            $this -> validator -> add_rule('api_type', 'required', '请选择推广类型');
            $this -> validator -> add_rule('tf_type', 'required', '请选择推广目标');

            $this -> validator -> add_rule('name', 'required', '请填写广告名称')->add_rule('name', 'max_length', '广告名称不能超过40个字', 40);
            
            $this -> validator -> add_rule('day_budget', 'required', '请填写日预算')->add_rule('day_budget', 'is_integer', '日预算应填写正整数');
            $this -> validator -> add_rule('price', 'required', '请填写单价')->add_rule('price', 'is_float', '单价格式错误', 1);
            
            $this -> validator -> add_rule('begin_tm', 'required', '请选择投放开始时间');
            if($tf_date_type == 2) {
                $this -> validator -> add_rule('end_tm', 'required', '请选择投放结束时间') -> add_rule('end_tm', 'gequals', '投放结束时间不能小于开始时间', $begin_tm);
            }
            $this -> validator -> add_rule('t_pid', 'required', '请选择广告样式');
            $this -> validator -> add_rule('t_id', 'required', '请选择广告样式');

            if(!$id && empty($_FILES['copywriter_img']['tmp_name'])) {
                throw new \Exception('请上传创意图片');
            }
            if( !$id && $t_id==16 && empty($_FILES['brand_img']['tmp_name']) ) {
                throw new \Exception('请上传品牌图片');
            }
            //落地页
            if($t_id != 17) {
                $this -> validator -> add_rule('url', 'required', '请填写落地页')-> add_rule('url', 'url', '落地页格式有误');
            }
            //创意文案和广告品牌文字校验
            if(in_array($t_id, array(14,15,16))) {
                $this -> validator -> add_rule('copywriter_desc', 'required', '请填写广告文案')->add_rule('copywriter_desc', 'max_length', '广告文案不能超过20个字', 25);
                $this -> validator -> add_rule('brand_desc', 'required', '请填写广告品牌')->add_rule('brand_desc', 'max_length', '广告品牌不能超过20个字', 10);
            } 
            $map = array(
                    'auid'              =>  $this->userinfo['auid'],
                    'api_type'          =>  $api_type,
                    'tf_type'           =>  $tf_type,
                    'name'              =>  $name,
                    'day_budget'        =>  $day_budget*100,
                    't_pid'             =>  $t_pid,
                    't_id'              =>  $t_id,
                    'tf_date_type'      =>  $tf_date_type,
                    'begin_tm'          =>  $begin_tm,
                    'end_tm'            =>  $tf_date_type==2?$end_tm:0,
                    'price'             =>  $price*100,
                    'tf_area'           =>  $tf_area,
                    'area_sp'           =>  $area_sp,
                    'tf_nettype'        =>  $tf_nettype,
                    'tf_mobile'         =>  $tf_mobile,
                    'media_attr_sp'     =>  $media_attr_sp,
                    'activetag_sp'      =>  0,
                    'examine_status'    =>  1,
                    'switch'            =>  1,
                    'industry_parentid' =>  $industry_parentid,
                    'industryid'        =>  $industryid,
                    //创意
                    'copywriter_img'    =>  $copywriter_img,
                    'brand_img'         =>  $brand_img,
                    'copywriter_desc'   =>  $copywriter_desc,
                    'brand_desc'        =>  $brand_desc,
                    'url'               =>  $url,
                    'bh_reason'         =>  0,
                    'bh_explain'        =>  '',
            );
            // /** 截获验证异常 */
            if ($error = $this -> validator -> run($map)) {
                $error = array_values($error);
                $error = $error[0];
                throw new \Exception($error['message'], $error['code']);
            }
            $this->deal_image($map,'copywriter_img');
            $this->deal_image($map,'brand_img');
            $lastId = $this -> get_repository('Materiel') -> save($map, $id);
            $map_json = json_encode($_POST);
            if(empty($id)){
                $this -> writelog("创建了id为{$lastId}的广告计划>>>{$map_json}");
            }else{
                $this -> writelog("修改了id为{$id}的广告计划>>>{$map_json}");
            }
            $this -> flashSession -> success('提交广告计划成功');
        }catch(\Exception $e){
            $this -> write_exception_log($e);
            $this -> flashSession -> error($e -> getMessage());
        }
        if(empty($id)){
            return $this -> redirect('materiel/list');
        }else{
            return $this -> redirect('materiel/list?sess=1');
        }
    }

    /**
     * 编辑广告页面
     */
    // public function editAction(){
    //     try{
    //         $id = intval($this -> request -> get('id', 'trim'));
    //         //广告信息
    //         $list = $this -> get_repository('Materiel') -> get_One($id);
    //         if($list['examine_status'] != 3 || $list['auid'] != $this -> session -> get('user')['auid'] ){
    //             throw new \Exception('非法操作');
    //         }
    //         $list['begin_tm'] = date('Y-m-d H:i:s', $list['begin_tm']);
    //         $list['end_tm'] = date('Y-m-d H:i:s', $list['end_tm']);
    //         $list['tf_area'] = explode(';', $list['tf_area']);
    //         $list['tf_mobile'] = explode(',', $list['tf_mobile']);
    //         //媒体行业信息
    //         $industry = $this -> get_repository('Industry') -> select();
    //         //广告模板信息
    //         $template = $this -> get_repository('Template') -> select();
    //         // 广告创意信息
    //         $ad_idea = $this -> get_repository('Idea') -> select();
    //         $this -> view -> setVars(array(
    //             'industry'  =>  $industry,
    //             'template'  =>  $template,
    //             'ad_idea'   =>  $ad_idea,
    //             'list'      =>  $list,
    //         ));
    //         $this -> view -> pick('materiel/edit');
    //     }catch(\Exception $e){
    //         $this -> write_exception_log($e);
    //         $this -> flashSession -> error($e -> getMessage());
    //         return $this -> redirect('materiel/list');
    //     }
    // }
    public function on_offAction(){
        try{
            $id = intval($this -> request -> get('id', 'trim'));
            $list = $this -> get_repository('Materiel') -> get_info($id);
            $auid = $this -> session -> get('user')['auid'];
            if($list['auid'] != $auid ){
               echo json_encode(array('code'=>0, 'msg'=>"非法操作"));die;
            }
            if($list['examine_status'] != 2 || $list['switch'] == 3){
                // //不是已审核通过的或者是已经结束的
                echo json_encode(array('code'=>0, 'msg'=>"非法操作"));die;
            }
            $status = intval($this -> request -> get('status', 'trim'));
            if($status == '1'){
                //继续的不是已暂停的
                if($list['switch'] != 2){
                     echo json_encode(array('code'=>0, 'msg'=>"非法操作"));die;
                }
                //账户余额
                $balance = ModelFactory::get_model('BalanceModel') -> get_balance($auid);
                if($balance['balance']<=0){
                     echo json_encode(array('code'=>0, 'msg'=>"账户余额不足"));die;
                }
            }else if($status == '2' && $list['switch'] != 1){
                //暂停的不是有效的
                 echo json_encode(array('code'=>0, 'msg'=>"非法操作"));die;
            }else if($status == '3' && $list['switch'] != 1){
                //结束的不是有效的
                 echo json_encode(array('code'=>0, 'msg'=>"非法操作"));die;
            }
            $map = array('switch' => $status);
            $this -> get_repository('Materiel') -> save($map, $id);
            $map_json = json_encode($_REQUEST);
            if($status == '1'){
                $this -> writelog("继续了id为{$id}>>>{$map_json}的广告计划");
            }else if($status == '2'){
                $this -> writelog("暂停了id为{$id}>>>{$map_json}的广告计划");
            }else if($status == '3'){
                $this -> writelog("结束了id为{$id}>>>{$map_json}的广告计划");
            }
            echo json_encode(array('code'=>1, 'msg'=>"成功"));die;
        }catch(\Exception $e){
            $this -> write_exception_log($e);
            echo json_encode(array('code'=>0, 'msg'=>$e -> getMessage()));die;
        }
    }


    //改变广告状态
    public function change_statusAction(){
        try{
            $id = intval($this -> request -> get('id', 'trim'));
            $list = $this -> get_repository('Materiel') -> get_info($id);
            $auid = $this -> session -> get('user')['auid'];
            if($list['auid'] != $auid ){
                throw new \Exception('非法操作');
            }
            if($list['examine_status'] != 2 || $list['switch'] == 3){
                //不是已审核通过的或者是已经结束的
                throw new \Exception('非法操作');
            }
            $status = intval($this -> request -> get('status', 'trim'));
            if($status == '1'){
                //继续的不是已暂停的
                if($list['switch'] != 2){
                    throw new \Exception('非法操作');
                }
                //账户余额
                $balance = ModelFactory::get_model('BalanceModel') -> get_balance($auid);
                if($balance['balance']<=0){
                    throw new \Exception('账户余额不足');
                }
            }else if($status == '2' && $list['switch'] != 1){
                //暂停的不是有效的
                throw new \Exception('非法操作');
            }else if($status == '3' && $list['switch'] != 1){
                //结束的不是有效的
                throw new \Exception('非法操作');
            }
            $map = array('switch' => $status);
            $this -> get_repository('Materiel') -> save($map, $id);
            $map_json = json_encode($_POST);
            if($status == '1'){
                $this -> writelog("继续了id为{$id}>>>{$map_json}的广告计划");
            }else if($status == '2'){
                $this -> writelog("暂停了id为{$id}>>>{$map_json}的广告计划");
            }else if($status == '3'){
                $this -> writelog("结束了id为{$id}>>>{$map_json}的广告计划");
            }
            $this -> flashSession -> success('操作成功');
        }catch(\Exception $e){
            $this -> write_exception_log($e);
            $this -> flashSession -> error($e -> getMessage());
        }
        return $this -> redirect('materiel/list?sess=1');
    }

    //广告系列操作
    public function operationAction(){
        $type           =   $this -> request -> get('type', 'trim');
        $ids            =   $this -> request -> get('ids', 'trim');
        $pq_date_type   =   $this -> request -> get('pq_date_type', 'trim');
        $start_date     =   $this -> request -> get('start_date', 'trim');
        $end_date       =   $this -> request -> get('end_date', 'trim');
        $price          =   $this -> request -> getPost('price', 'trim');//单价
        $price          =   $this -> filter  -> sanitize($price, 'remove_xss');
        $switch         =   intval($this -> request -> get('switch', 'trim'));
        try {
            $this -> validator -> add_rule('type', 'required', '操作有误');
            $this -> validator -> add_rule('ids', 'required', '请选择广告');
            $map['ids']  = $ids;
            $map['type'] = $type;
            if($type == 'dt') {
                if($pq_date_type == 1) {
                    //长期投放
                    $this -> validator -> add_rule('start_date', 'required', '请填写开始时间');
                    $map['start_date'] = $start_date;
                }elseif ($pq_date_type == 2) {
                    //指定时间范围
                    $this -> validator -> add_rule('start_date', 'required', '请填写开始时间');
                    $this -> validator -> add_rule('end_date', 'required', '请选择排期结束时间') -> add_rule('end_date', 'gequals', '排期结束时间不能小于开始时间', $start_date);
                    $map['start_date']  = $start_date;
                    $map['end_date']    = $end_date;
                    if( $end_date < date("Y-m-d 23:59:59", time()) ) {
                        throw new \Exception('排期结束时间不能小于今天23:59:59');
                    }
                }
            }elseif ($type == 'pr') {
                $this -> validator -> add_rule('price', 'required', '请填写单价')->add_rule('price', 'is_float', '单价格式错误', 1);
                $map['price'] = $price;
            }elseif ($type == 'st') {
                $this -> validator -> add_rule('switch', 'required', '请先选择');
                $map['switch'] = $switch;
            }
            //print_r($map);die;
            if ($error = $this -> validator -> run($map)) {
                $error = array_values($error);
                $error = $error[0];
                throw new \Exception($error['message'], $error['code']);
            }
            $ext = array(
                'type'          =>  $type,
                'tf_date_type'  =>  $pq_date_type,
                'begin_tm'      =>  strtotime($start_date),
                'end_tm'        =>  $end_date?strtotime($end_date):0,
                'price'         =>  $price*100,
                'switch'        =>  $switch,
            );
            $ret = $this -> get_repository('Materiel') -> batch_opertion($ids, $ext);
            if($ret) {
                $map_json = json_encode($_POST);
                if($type == 'del'){
                    $this -> writelog("删除id为{$id}>>>{$map_json}的广告计划");
                }else if($type == 'dt'){
                    $this -> writelog("修改排期id为{$ids}>>>{$map_json}的广告计划");
                }else if($type == 'pr'){
                    $this -> writelog("修改单价id为{$ids}>>>{$map_json}的广告计划");
                }else if($type == 'st'){
                    $this -> writelog("修改状态id为{$ids}>>>{$map_json}的广告计划"); 
                }
                $this -> flashSession -> success('操作成功');
            }else {
                $this -> flashSession -> success('操作失败');
            }
        } catch (\Exception $e) {
           $this -> write_exception_log($e);
           $this -> flashSession -> error($e -> getMessage()); 
        }
        return $this -> redirect('materiel/list?sess=1');
    }



    //检查广告计划名是否存在
    public function check_nameAction(){
        try{
            if(!$this -> request -> isAjax()){
                throw new \Exception('非法请求');
            }
            $id = $this -> request -> get('id', 'trim');
            $name = $this -> request -> get('name', 'trim');
            $ext['name'] = $name;
            $return = $this -> get_repository('Materiel') -> check_name($ext, $id, 1);
            $this -> ajax_return('返回成功', $return);
        }catch(\Exception $e){
            $this -> write_exception_log($e);
            if( $this -> request -> isAjax() ) {
                $this -> ajax_return($e -> getMessage(), 0);
            }else {
                $this -> flashSession -> error($e -> getMessage());
                return $this -> redirect('materiel/list');
            }
        }
        $this -> view -> disable();
    }

    public function exportAction(){
        $name      =  $this -> request -> get('name', 'trim'); //广告名称
        $status    =  intval($this -> request -> get('status', 'trim', 0)); //审核状态
        //$tag       =  intval($this -> request -> get('tag', 'trim')); //统计数据天数
        $begin_tm  =  $this -> request -> get('sh_begin_tm', 'trim'); //开始时间
        $end_tm    =  $this -> request -> get('sh_end_tm', 'trim'); 

        $condition = array(
            'auid'   => $this ->userinfo['auid'],
            'name'   => $name,
            'export' => 1,
        );
        switch ($status) {
            case 1://所有未删除
                $condition['examine_status'] = 0;
                break;
            case 2://启用中
                $condition['switch'] = 1;
                break;
            case 3://暂停中
                $condition['switch'] = 2;
                break;
            case 4://审核中
                $condition['examine_status'] = 1;
                break;
            case 5://审核未通过
                $condition['examine_status'] = 3;
                break;
            case 6://投放结束
                $condition['switch'] = 3;
                break;
            case 7://未开始
                $condition['switch'] = 4;
                break;
        }
        //分页获取列表
        $list = $this -> get_repository('Materiel') -> get_list(0, 0, $condition); 
        $id_arr = array();
        foreach ($list as $k => $val) {
            $id_arr[] = $val['id'];
        }
        $ids = implode($id_arr, ',');
        if(empty($tag)){
            if(empty($begin_tm) && empty($end_tm)){
                $tag = 2; //默认统计的数据
            }else{
                $tag = 0;
            }
        }
        $ext  = array(
            'auid'      =>  $this->userinfo['auid'],
            'ad_id'     =>  $ids,
            'tag'       =>  $tag,
            'begin_tm'  =>  $begin_tm,
            'end_tm'    =>  $end_tm,
        );
        $result = $this -> get_repository('Data') -> get_list_export($ext); 
        //导出csv
        $date_str = date('Y-m-d',time());
        $data = array();
        $count = count($result);
        $exposure = $click = $click_price =$total = 0;
        //媒体
        $filename = "广告计划统计_{$date_str}.csv"; //设置文件名
        $str = '日期,广告计划名称,曝光量,点击量,点击率,点击均价,消耗金额';
        $str = iconv('utf-8','GBK//IGNORE', $str);
        $str = $str."\n";
        foreach ( $result as $k => $val ) {
            $data[$k][]     =   $val['create_date'];
            $data[$k][]     =   iconv('utf-8','GBK//IGNORE', $val['ad_name']);
            $data[$k][]     =   $val['exposure'];
            $data[$k][]     =   $val['click'];
            $data[$k][]     =   $val['click_rate'].'%';
            $data[$k][]     =   Common::number_format($val['click_price']);
            $data[$k][]     =   Common::number_format($val['total']);
            $exposure    += $val['exposure'];
            $click       += $val['click'];
            //$click_rate  += $val['click_rate'];
            $click_price += $val['click_price'];
            $total       += $val['total'];
        }
        if(!empty($result)) {
            $data[$count][] = iconv('utf-8','GBK//IGNORE', "总计");
            $data[$count][] = "-";
            $data[$count][] = $exposure;
            $data[$count][] = $click;
            $data[$count][] = "-";
            $data[$count][] = "-";
            //$data[$count][] = Common::click_rate($click,$exposure);
            //$data[$count][] = Common::number_format($click_price/$count);
            $data[$count][] = Common::number_format($total);
        }
        echo $str;
        $this -> export_csv($filename, $data);
    }

    public function del_checkAction(){
        $id = $_GET['id'];
        $time = time();
        $info = $this -> get_repository('Materiel') -> get_info($id);
        if($info['examine_status'] == 1) {
            echo json_encode(array('code'=>0,'msg'=>"id为{$info['id']}广告处于审核中，不支持删除"));die;
        }
        if($info['examine_status'] == 2 && $info['switch'] == 1 && ( ($info['tf_date_type']==1 && $info['begin_tm']<$time) || ($info['tf_date_type']==2 && $info['begin_tm'] <$time && $info['end_tm']>$time)   ) ) {
            echo json_encode(array('code'=>0,'msg'=>"id为{$info['id']}广告处于投放中，不支持删除"));die;
        }

        echo json_encode(array('code'=>1,'msg'=>"ok"));die;
    }

     public function deal_image(&$data,$image_url,$image_name='图片',$expression='jpg|png|gif'){
        if($_FILES[$image_url]['tmp_name']){
            // 取得图片后缀
            $suffix = preg_match("/\.({$expression})$/", $_FILES[$image_url]['name'], $matches);
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
            // $width  = $img_info_arr[0];
            // $height = $img_info_arr[1];
            // $image_width = $this->image_size[$image_url][0];
            // $image_height = $this->image_size[$image_url][1];
            // if($image_width!=0 && $image_height!=0){
            //     if ($width!=$image_width || $height!=$image_height){
            //         throw new \Exception("{$image_name}尺寸错误，宽需为{$image_width}px，高需为{$image_height}px");
            //     }
            // }
            $dir_name = "/dsp/".date("Ym/d/");
            if(!is_dir(UPLOAD_PATH.$dir_name)){
                mkdir(UPLOAD_PATH.$dir_name, 0755, true);
            }
            $save_name = $dir_name.time().'_'.rand(1000,9999).$suffix;
            $img_path  = UPLOAD_PATH.$save_name;
            if(!move_uploaded_file($_FILES[$image_url]['tmp_name'], $img_path)){
                throw new \Exception("上传{$image_name}出错");
            }
            $data[$image_url] = $save_name;
            return true;
        }else {
            return false;
        }
    }

    private function set_img_size($pid, $tid){
        if(!$pid || !$tid) {
            return false;
        }
        if($pid == 1) {
            $this->image_size['copywriter_img'] = array(600,100);
        }else if($pid == 3) {
            $this->image_size['copywriter_img'] = array(640,960);
        }elseif ($pid == 2) {
            $this->image_size['copywriter_img'] = array(600,500);
        }elseif ($pid == 4) {
            if($tid == 14) {
                $this->image_size['copywriter_img'] = array(1280,720);
            }elseif ($tid == 15) {
                $this->image_size['copywriter_img'] = array(1280,720);
            }elseif ($tid == 16) {
                $this->image_size['copywriter_img'] = array(1200,800);
                $this->image_size['brand_img']      = array(300,300);
            }elseif ($tid == 17) {
                $this->image_size['copywriter_img'] = array(1280,720);
            }
        }
        return true;
    }

    function export_csv($filename, $data){
        $fp = fopen('php://output', 'w');
        foreach ($data as $val) {
            fputcsv($fp, $val);
        }
        fclose($fp);
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=".$filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');die;
    }
}