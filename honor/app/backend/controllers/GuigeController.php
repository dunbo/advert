<?php

/**
 * 广告规格控制器
 * @category PhalconHonor
 * @author haoshisuo 2017-10-23
 */

namespace Marser\App\Backend\Controllers;

use \Marser\App\Backend\Controllers\BaseController;
use \Marser\App\Backend\Models\ModelFactory;
use \Marser\App\Helpers\PaginatorHelper;

class GuigeController extends BaseController{

    // 广告规格列表
    public function listAction(){
        $page     = $this -> request -> get('page', 'int', 1);
        $pagesize =   $this -> request -> get('pagesize', 'int', 10);
        $pu_tid = $this -> request -> get('pu_tid', 'trim', 1);
        $status = $this -> request -> get('status', 'trim', 1);

        $paginator = $this -> get_repository('Guige') -> get_list($page, $pagesize, array(
            'pu_tid'      =>  $pu_tid,
            'status'      =>  $status,
        ));
        $pageNum = PaginatorHelper::get_paginator($paginator->total_items, $page, $pagesize);
        $list = $paginator->items-> toArray();
        $count = $paginator->items->count();
        $templates = ModelFactory::get_model('TemplateModel')->get_list();
        $template = ModelFactory::get_model('TemplateModel')->get_list(array('id'=>$pu_tid));
        $this -> view -> setVars(array(
            'paginator'     =>  $paginator,
            'pageNum'       =>  $pageNum,
            'pu_tid'        =>  $pu_tid,
            'status'        =>  $status,
            'list'          =>  $list,
            'template'      =>  $template[0],
            'templates'     =>  $templates,
            'page'          =>  $page,
            'pagesize'      =>  $pagesize,
            'cur_page_num'  =>  $count,
        ));
        $this -> view -> pick('guige/list');
    }

    /**
     * 广告规格（添加、编辑）数据库操作
     */
    public function writeAction(){
        try{
            if($this -> request -> isAjax() || !$this -> request -> isPost()){
                throw new \Exception('非法请求');
            }
            $tid = $this -> request -> getPost('tid', 'int');
            $pu_tid = $this -> request -> getPost('pu_tid', 'int');
            //$size = $this -> request -> getPost('size', 'trim');
            $describe = $this -> request -> getPost('describe', 'trim');
            $describe = $this -> filter  -> sanitize($describe, 'remove_xss');
            $probability = intval($this -> request -> getPost('probability', 'trim'));

            $dynamic_img = $this -> request -> getPost('dynamic_img', 'trim', '');
            $static_img = $this -> request -> getPost('static_img', 'trim', '');
            $img_inset_static_320   =   $this -> request -> getPost('img_inset_static_320', 'trim', '');
            $img_inset_dynamic_320  =   $this -> request -> getPost('img_inset_dynamic_320', 'trim', '');

            // ini_set('display_errors',1);        
            // ini_set('display_startup_errors',1);
            // error_reporting(-1);
            ini_set('max_execution_time', 0);
            ini_set('memory_limit','256M');

            /** 添加验证规则 */
            $this -> validator -> add_rule('describe', 'required', '请填写文字描述')
            -> add_rule('describe', 'max_length', '文字描述不能超过30个字符', 30);
            $this -> validator -> add_rule('probability', 'required', '请填写概率')
            -> add_rule('probability', 'is_integer', '概率应为整数');
            /** 截获验证异常 */
            if ($error = $this -> validator -> run(array(
                'describe' => $describe,
                'probability' => $probability,
            ))) {
                $error = array_values($error);
                $error = $error[0];
                throw new \Exception($error['message'], $error['code']);
            }
            if($pu_tid == 1) {
                $size_1 = array(320,250);
                $size_2 = array(600,500);
            }elseif($pu_tid == 2){
                $size = array(640,960);
            }elseif($pu_tid == 3){
                $size = array(640,100);
            }elseif($pu_tid == 4){
                $size = array(150,150);
            }elseif($pu_tid == 5){
                $size = array(150,150);
            }elseif($pu_tid == 6){
                $size = array(225,140);
            }elseif($pu_tid == 7){
                $size = array(700,280);
            }
            $img_arr = array();
            if( $tid ) {
                $ad_temp = $this -> get_repository('Guige') -> get_One($tid);
                $img_arr = json_decode($ad_temp['img_json'], true);
                $img_arr = $img_arr?$img_arr:array();
            }
            if( $pu_tid == 1 ) {
                     //静态图
                    if(empty($tid) || !empty($_FILES['img_inset_static_320']['tmp_name'])){
                        $static_img_320 = $this->deal_image('img_inset_static_320',$size_1[0],$size_1[1],'广告静态图片320*250','png|jpg|jpeg');
                        $img_arr['static_320'] =  $static_img_320;
                    }
                    if(empty($tid) || !empty($_FILES['img_inset_static_600']['tmp_name'])){
                        $static_img = $this->deal_image('img_inset_static_600',$size_2s[0],$size_2[1],'广告静态图片600*500','png|jpg|jpeg');
                        $img_arr['static_600'] = $static_img;
                    }
                    //动态图
                    if(empty($tid) || !empty($_FILES['img_inset_dynamic_320']['tmp_name'])){
                        $dynamic_img_320 = $this->deal_image('img_inset_dynamic_320',$size_1[0],$size_1[1],'广告动态图片320*250','gif');
                        $img_arr['dynamic_320'] =  $dynamic_img_320;
                    }
                    if(empty($tid) || !empty($_FILES['img_inset_dynamic_600']['tmp_name'])){
                        $dynamic_img = $this->deal_image('img_inset_dynamic_600',$size_2[0],$size_2[1],'广告动态图片600*500','gif');
                        $img_arr['dynamic_600'] = $dynamic_img;
                    }
            }else {
                if(empty($tid) || !empty($_FILES['img_static']['tmp_name'])){
                    $static_img = $this->deal_image('img_static',$size[0],$size[1],'广告静态图片','png|jpg|jpeg');
                    $static_arr  = explode('.', $static_img);
                    $suffix      = array_pop($static_arr);
                    if( $pu_tid == 2) {
                        $static_img_path =  UPLOAD_PATH.$static_img;
                         $info = $this->get_sava_info($suffix);
                        $image = new \Imagick($static_img_path);  
                        $image = $image->coalesceImages();  
                        foreach ($image as $frame) {  
                            $frame->thumbnailImage(320,480);  
                        }  
                        $image = $image->optimizeImageLayers();  
                        $image->writeImages($info['img_path'], true);
                        $img_arr['static_320'] = $info['save_name'];
                        $img_arr['static_640'] = $static_img;  
                    }elseif ($pu_tid == 3) {
                        $static_img_path =  UPLOAD_PATH.$static_img;
                        $img_size_arr = array(
                            array(480,75),
                            array(320,50),
                            array(240,38),
                        );
                        $image = new \Imagick($static_img_path);  
                        $image = $image->coalesceImages();
                        foreach ($img_size_arr as $val) {
                            $info = $this->get_sava_info($suffix);
                            foreach ($image as $frame) {  
                                $frame->thumbnailImage($val[0],$val[1]);  
                            }  
                            $image = $image->optimizeImageLayers();  
                            $image->writeImages($info['img_path'], true);
                            $img_arr['static_'.$val[0]] = $info['save_name'];
                        }
                        $img_arr['static_640'] = $static_img;
                    }
                }
                if(empty($tid) || !empty($_FILES['img_dynamic']['tmp_name'])){
                    $dynamic_img = $this->deal_image('img_dynamic',$size[0],$size[1],'广告动态图片','gif');
                    $dynamic_arr  = explode('.', $dynamic_img);
                    $suffix      = array_pop($dynamic_arr);
                    if( $pu_tid == 2) {
                       $dynamic_img_path =  UPLOAD_PATH.$dynamic_img;
                       $info = $this->get_sava_info($suffix);
                       $image = new \Imagick($dynamic_img_path);  
                       $image = $image->coalesceImages();  
                       foreach ($image as $frame) {  
                            $frame->thumbnailImage(320,480);  
                        }  
                        $image = $image->optimizeImageLayers();  
                        $image->writeImages($info['img_path'], true);
                        $img_arr['dynamic_320'] = $info['save_name'];
                        $img_arr['dynamic_640'] = $dynamic_img;  
                    }elseif ($pu_tid == 3) {
                        $dynamic_img_path =  UPLOAD_PATH.$dynamic_img;
                        $image = new \Imagick($dynamic_img_path);
                        $image = $image->coalesceImages(); 
                        $img_size_arr = array(
                            array(480,75),
                            array(320,50),
                            array(240,38),
                        );
                        foreach ($img_size_arr as $val) {
                            $info = $this->get_sava_info($suffix);
                            foreach ($image as $frame) {  
                                $frame->thumbnailImage($val[0],$val[1]);  
                            } 
                            $image = $image->optimizeImageLayers();  
                            $image->writeImages($info['img_path'], true);
                            $img_arr['dynamic_'.$val[0]] = $info['save_name'];
                        }
                        $img_arr['dynamic_640'] = $dynamic_img;
                    }
                }               
            }
            $img_json = $img_arr?json_encode($img_arr):'';
            /** 发布 */
            $return = $this -> get_repository('Guige') -> save(array(
                    'pu_tid'          =>  $pu_tid,
                    'dynamic_img'     =>  $dynamic_img,
                    'static_img'      =>  $static_img,
                    'describe'        =>  $describe,
                    'probability'     =>  $probability,
                    'img_json'        =>  $img_json,
            ), $tid);
            if(isset($tid) && !empty($tid)){
                $this -> writelog("编辑了id为{$tid}的广告规格", 'ad_template', $return, 'edit');
            }else{
                $this -> writelog('创建了广告规格', 'ad_template', $return, 'add');
            }
            $this -> flashSession -> success('提交广告规格成功');
        }catch(\Exception $e) {
            $this -> write_exception_log($e);
            $this -> flashSession -> error($e -> getMessage());
        }
        return $this -> redirect('guige/list?pu_tid='.$pu_tid);
    }

    public function delAction(){
        try{
            $tid = $this -> request -> get('tid', 'int');
            $pu_tid = $this -> request -> get('pu_tid', 'int');
            $return = $this -> get_repository('Guige') -> save(array(
                'status' => 0,
            ), $tid);
            $this -> writelog("删除了id为{$tid}广告规格", 'ad_template', $return, 'edit');
            $this -> flashSession -> success('删除广告规格成功');
        }catch(\Exception $e) {
            $this -> write_exception_log($e);
            $this -> flashSession -> error($e -> getMessage());
        }
        return $this -> redirect('guige/list?pu_tid='.$pu_tid);
    }


    public static function deal_image($image_url,$image_width=0,$image_height=0,$image_name='图片',$expression='jpg|png|jpeg|gif'){
        if(!$_FILES[$image_url]['tmp_name']){
            throw new \Exception("请上传{$image_name}！");
        }
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
        $width = $img_info_arr[0];
        $height = $img_info_arr[1];
        if($image_width!=0 && $image_height!=0){
            if ($width!=$image_width || $height!=$image_height){
                throw new \Exception("{$image_name}尺寸错误，宽需为{$image_width}px，高需为{$image_height}px");
            }
        }
        $dir_name = "/admin/".date("Ym/d/").$image_width;
        if(!is_dir(UPLOAD_PATH.$dir_name)){
            mkdir(UPLOAD_PATH.$dir_name, 0755, true);
        }
        $save_name = $dir_name.time().'_'.rand(1000,9999).$suffix;
        $img_path = UPLOAD_PATH.$save_name;
        if(!move_uploaded_file($_FILES[$image_url]['tmp_name'], $img_path)){
            throw new \Exception("上传{$image_name}出错");
        }
        return $save_name;
    }

    public static function get_sava_info($suffix) {
        $dir_name = "/admin/".date("Ym/d/");
        if(!is_dir(UPLOAD_PATH.$dir_name)){
            mkdir(UPLOAD_PATH.$dir_name, 0755, true);
        }
        $save_name = $dir_name.time().'_'.rand(1000,9999).'.'.$suffix;
        $img_path = UPLOAD_PATH.$save_name;
        return array('save_name'=>$save_name, 'img_path'=>$img_path);
    }

}
