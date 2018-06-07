<?php

/**
 * 广告计划控制器
 * @category PhalconHonor
 * @author haoshisuo 2017-9-22
 */

namespace Marser\App\Backend\Controllers;

use \Marser\App\Backend\Controllers\BaseController;
use \Marser\App\Helpers\PaginatorHelper;
use \Marser\App\Backend\Models\ModelFactory;

class MaterielController extends BaseController{

    // 广告计划列表
    public function listAction(){
        $page           =  $this -> request -> get('page', 'int', 1);
        $pagesize       =   $this -> request -> get('pagesize', 'int', 10);
        $uid            =  $this -> request -> get('uid', 'int');
        $ad_name        =  $this -> request -> get('ad_name', 'trim');
        $materiel_name  =  $this -> request -> get('materiel_name', 'trim');
        $srch_type      =  $this -> request -> get('srch_type', 'trim', 'sh');
        /**分页获取文章列表*/
        $srch_arr = array('del'=>0,'sh'=>1, 'tg'=>2, 'ntg'=>3);
        $arr_srch = array(0=>'del',1=>'sh', 2=>'tg', 3=>'ntg');
        $map = array(
            'ad_name'       => $ad_name,
            'auid'          => $uid,
            'materiel_name' => $materiel_name,
            'srch_type'     => $srch_arr[$srch_type],
        );
        $sess = intval($this -> request -> get('sess', 'trim')); //判断是否获取查询条件
        if(isset($sess) && !empty($sess) && $this -> session -> has('materiel_list')){
            $map = $this -> session -> get('materiel_list');
        }else{
            $this -> session -> set('materiel_list', $map);
        }
        $paginator = $this -> get_repository('Materiel') -> get_list($page, $pagesize, $map);
        $pageNum = PaginatorHelper::get_paginator($paginator->total_items, $page, $pagesize);
        $list = $paginator->items->toArray();
        $industry = ModelFactory::get_model('IndustryModel') -> get_list();
        $hangye = array();
        foreach($industry as $val){
            $hangye[$val['id']] = $val['name'];
        }
        $tag_result = ModelFactory::get_model('TagModel') -> get_list();
        $new_tag_arr = array();
        foreach($tag_result as $val){
            $new_tag_arr[$val['tag_id']] = $val['tag_name'];
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
            if(!empty($val['activetag_sp'])){
                $tmp_activetag_sp = explode(',', $val['activetag_sp']);
                foreach($tmp_activetag_sp as $tmp_val){
                    $val['activetag_sp_name'] .= $new_tag_arr[$tmp_val].',';
                }
                $val['activetag_sp_name'] = substr($val['activetag_sp_name'], 0, -1);
            }else{
                $val['activetag_sp_name'] = '';
            }
            $list[$key] = $val;
            //广告样式
            if($val['t_pid']==4){
                $parent_template = $this -> get_repository('Template') -> get_info($val['t_pid']);
                $sub_template    = $this -> get_repository('Template') -> get_info($val['t_id']);
                $list[$key]['template'] = $parent_template['name'].'-'.$sub_template['name'].'-'.$sub_template['size'];
            }else{
                $parent_template = $this -> get_repository('Template') -> get_info($val['t_pid']);    
                $list[$key]['template'] = $parent_template['name'].'-'.$parent_template['size'];
            }
        }
        $tags = ModelFactory::get_model('TagGroupModel') -> get_all_taglist();
        $count = $paginator->items->count();
        $this -> view -> setVars(array(
            'title'         =>  '广告计划列表',
            'paginator'     =>  $paginator,
            'pageNum'       =>  $pageNum,
            'ad_name'       =>  $map['ad_name'],
            'materiel_name' =>  $map['materiel_name'],
            'list'          =>  $list,
            'page'          =>  $page,
            'pagesize'      =>  $pagesize,
            'cur_page_num'  =>  $count,
            'srch_type'     =>  $arr_srch[$map['srch_type']],
            'uid'           =>  $map['auid'],
            'tags'          =>  $tags,
            'industry'      =>  $industry,
        ));
        $this -> view -> pick('materiel/list');
    }

    //审核通过操作
    public function examinetgAction(){
        try{
            if($this -> request -> isAjax()){
                throw new \Exception('非法请求');
            }
            $ids            =  $this -> request -> get('ids', 'trim');
            $industry_pids  =  $this -> request -> get('industry_pids', 'trim');
            $industryids    =  $this -> request -> get('industryids', 'trim');
            $ext = array();
            $this -> get_repository('Materiel') -> check_has_tags($ids);
            $ext['examine_status'] = 2;
            if(str_replace(',', '', $industry_pids)=='' || str_replace(',', '', $industryids)==''){
                throw new \Exception('审核通过操作必须选择二级行业');
            }
            $ext['ad_industry_parentid'] = $industry_pids;
            $ext['ad_industryid'] = $industryids;
          
            $return = $this -> get_repository('Materiel') -> examine($ext, $ids);
            $this -> writelog("通过了id为{$ids}的广告计划", 'ad_materiel', $return, 'edit');
            $this -> flashSession -> success('审核通过成功');
        }catch(\Exception $e){
            $this -> write_exception_log($e);
            $this -> flashSession -> error($e -> getMessage());
        }
        return $this -> redirect('materiel/list?sess=1');
    }

    //审核驳回操作
    public function examinebhAction(){
        try{
            if($this -> request -> isAjax()){
                throw new \Exception('非法请求');
            }
            $ids            =  $this -> request -> get('ids', 'trim');
            $bh_reason      =  $this -> request -> get('bh_reason', 'trim');
            $bh_explain     =  $this -> request -> get('bh_explain', 'trim');
            $ext = array();
            $ext['examine_status'] = 3;
            $ext['bh_reason'] = $bh_reason;
            $ext['bh_explain'] = $bh_explain;
            $return = $this -> get_repository('Materiel') -> examine($ext, $ids);
            $this -> writelog("驳回了id为{$ids}的广告计划", 'ad_materiel', $return, 'edit');
            $this -> flashSession -> success('驳回成功');
        }catch(\Exception $e){
            $this -> write_exception_log($e);
            $this -> flashSession -> error($e -> getMessage());
        }
        return $this -> redirect('materiel/list?sess=1');
    }

    //添加标签操作
    public function examinetagAction(){
        try{
            if($this -> request -> isAjax()){
                throw new \Exception('非法请求');
            }
            $page           =  $this -> request -> get('page', 'int', 1);
            $ids            =  $this -> request -> get('ids', 'trim');
            $industry_pids  =  $this -> request -> get('industry_pids', 'trim');
            $industryids    =  $this -> request -> get('industryids', 'trim');
            $activetag_sp   =  $this -> request -> get('activetag_sp', 'trim');
            $sp_activetag   =  $this -> request -> get('sp_activetag', 'trim');
            $tag_arr = array();
            if(!empty($activetag_sp)){
                foreach($activetag_sp as $val){
                    if(!in_array($val, $tag_arr)){
                        $tag_arr[] = $val;
                    }
                }
            }
            if(!empty($sp_activetag)){
                foreach($sp_activetag as $val){
                    if(!in_array($val, $tag_arr)){
                        $tag_arr[] = $val;
                    }
                }
            }
            $tag_arr = implode(',', $tag_arr);
            $ext = array();
            if(empty($tag_arr)){
                throw new \Exception('请选择标签');
            }
            $ext['activetag_sp'] = $tag_arr;
            $return = $this -> get_repository('Materiel') -> examine($ext, $ids);
            $this -> writelog("给id为{$ids}的广告计划添加了标签", 'ad_materiel', $return, 'edit');
            $this -> flashSession -> success('添加标签成功');
        }catch(\Exception $e){
            $this -> write_exception_log($e);
            $this -> flashSession -> error($e -> getMessage());
        }
        return $this -> redirect('materiel/list?sess=1&page='.$page);
    }

    //ajax请求获取所有标签
    public function tagsAction(){
        try{
            if(!$this -> request -> isAjax()){
                throw new \Exception('非法请求');
            }
            $tag_name = $this -> request -> get('tag_name', 'trim');
            $ext['tag_name'] = $tag_name;
            $tag_result = ModelFactory::get_model('TagModel') -> get_list($ext);
            $this -> ajax_return('查询成功', 1, $tag_result);
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

    public function editAction(){
        $id       =   $this -> request -> get('id', 'int');
        $industry = $this -> get_repository('Industry') -> get_list();
        $materiel = $this -> get_repository('Materiel') -> get_info($id);
        $materiel['tf_area']    = explode(';', $materiel['tf_area']);
        $materiel['tf_mobile']  = explode(',', $materiel['tf_mobile']);
        $temp_parent_list   =   $this -> get_repository('Template')->get_parent_list();
        $temp_sub_list      =   $this -> get_repository('Template')->get_sub_list();
        $tags = ModelFactory::get_model('TagGroupModel') -> get_all_taglist();
        //添加时默认时间
        $timestamp   = date("Y-m-d 00:00:00", time());
        $timestamp_7 = date("Y-m-d 00:00:00", (time()+7*86400)); 
        $tag_result  = ModelFactory::get_model('TagModel') -> get_list();
        $new_tag_arr = array();
        foreach($tag_result as $val){
            $new_tag_arr[$val['tag_id']] = $val['tag_name'];
        }
         if(!empty($materiel['activetag_sp'])){
            $tmp_activetag_sp = explode(',', $materiel['activetag_sp']);
            foreach($tmp_activetag_sp as $tmp_val){
                $materiel['activetag_sp_name'] .= $new_tag_arr[$tmp_val].',';
            }
            $materiel['activetag_sp_name'] = substr($materiel['activetag_sp_name'], 0, -1);
        }else{
            $materiel['activetag_sp_name'] = '';
        }
        //print_r($materiel);die;
        $this -> view -> setVars(array(
            'id'       => $id,
            'materiel' => $materiel, 
            'template' => $template,
            'industry' => $industry,
            'tags'     => $tags,
            'temp_parent_list'  =>  json_encode($temp_parent_list),
            'temp_sub_list'     =>  json_encode($temp_sub_list),  
            'timestamp'     =>  $timestamp,
            'timestamp_7'   =>  $timestamp_7,    
        ));
        $this -> view -> pick('materiel/edit');
    }

    public function uploadimageAction(){
        $id    =   $this -> request -> get('id', 'int');
        $img   =   $this -> request -> get('img', 'trim');
        $img   =   str_replace("_cur", '',  $img);
        $map   =   array();
        try {
            $this->deal_image($map,$img);
            $lastId = $this -> get_repository('Materiel') -> save($map, $id);
            if($lastId) {
                 $map_json = json_encode($_GET);
                 $this -> writelog("修改了id为{$id}的广告计划的创意图片{$map_json}");
                 echo json_encode(array('code' =>1, 'msg'=>"上传成功"));die;
            }else {
                 echo json_encode(array('code' =>0, 'msg'=>"上传成功"));die;
            }
           
        } catch (\Exception $e) {
            echo json_encode(array('code' =>0, 'msg'=>$e -> getMessage()));die;
        }
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
            $width  = $img_info_arr[0];
            $height = $img_info_arr[1];
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


}
