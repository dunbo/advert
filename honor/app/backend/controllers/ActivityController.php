<?php
/**
 * 活动管理
 */
namespace Marser\App\Backend\Controllers;

use \Marser\App\Backend\Controllers\BaseController;
use \Marser\App\Helpers\PaginatorHelper;

class ActivityController extends BaseController{
    private  $image_size = array(
             'home_img' => array(0,0),
             'my_prize_button_img' => array(0,0),
             'dram_button_img' => array(0,0),
             'lottery_button_img' => array(0,0),
            );

    public function indexAction(){
        $page            =   $this -> request -> get('page', 'int', 1);
        $pagesize        =   $this -> request -> get('pagesize', 'int', 10);
        $name            =   $this -> request -> get('name', 'trim');
        $type            =   $this -> request -> get('type', 'int');
        $srch_type       =   $this -> request -> get('srch_type', 'trim','sh');
        $status_arr = array( 'tg'=>1,'sh'=>2, 'ntg'=>3,'xj'=>4);
        $paginator = $this -> get_repository('Activity') -> get_list($page, $pagesize, array(
            'name'      =>  $name,
            'type'      =>  $type,
            'status'    =>  $status_arr[$srch_type],
        ));
        $pageNum = PaginatorHelper::get_paginator($paginator->total_items, $page, $pagesize);
        $list  = $paginator->items->toArray();
        foreach ($list as $key => $val) {
            $prize = $this -> get_repository('Prize') -> get_list($val['aid']);
            $list[$key]['prize'] = $prize;
        }
        $count = $paginator->items->count();
        $this -> view -> setVars(array(
            'title'         =>  '活动管理',
            'paginator'     =>  $paginator,
            'pageNum'       =>  $pageNum,
            'name'          =>  $name,
            'type'          =>  $type,
            'list'          =>  $list,
            'page'          =>  $page,
            'pagesize'      =>  $pagesize,
            'cur_page_num'  =>  $count,
            'srch_type'     =>  $srch_type,
        ));

        $this -> view -> pick('activity/index');
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
            $ext = array();
            if($type == 'tg'){
                $ext['status'] = 1;
                //大转盘和九宫格需要配置8个
                $this -> get_repository('Activity') -> check_activity_prize($ids);
            }else if($type == 'bh'){
                $ext['status'] = 3;
            }else if($type == 'xj'){
                $ext['status'] = 4; 
            }else if($type == 'del'){
                $ext['status'] = 0; 
            }else if($type == 'cx'){
                $ext['status'] = 2; 
            }else {
                throw new \Exception('参数有误');
            }
            $return = $this -> get_repository('Activity') -> batch_update_record($ext, $ids);
            if($type == 'tg'){
                $this -> writelog("通过了活动,id为:{$ids}", 'media_activity', $ids, 'edit');
            }else if($type == 'bh'){
                $this -> writelog("驳回了活动,id为:{$ids}", 'media_activity', $ids, 'edit');
            }else if($type == 'xj'){
                $this -> writelog("下架了活动,id为:{$ids}", 'media_activity', $ids, 'edit');
            }else if($type == 'del'){
                $this -> writelog("删除了活动,id为:{$ids}", 'media_activity', $ids, 'edit');
            }else if($type == 'cx'){
                $this -> writelog("撤销了活动,id为:{$ids}", 'media_activity', $ids, 'edit');
            }
            $this -> flashSession -> success('更新成功');
        }catch(\Exception $e){
            $this -> write_exception_log($e);
            $this -> flashSession -> error($e -> getMessage());
        }
        return $this -> redirect('activity/index');
    } 

   /**
    * 查看活动
    */
    public function addAction() {
        try{
            $aid       =   $this -> request -> get('aid', 'int');
            if( $aid ) {
               $activity =  $this -> get_repository('Activity') -> get_activity_info($aid);
               $activity_conf_arr = json_decode($activity['activity_conf'], true);
               $activity['activity_conf'] = $activity_conf_arr;
            }
            $this -> view -> setVars(array(
                        'aid'       =>  $aid,
                        'title'     =>  $aid?'编辑活动':'添加活动',
                        'activity'  =>  $activity,
                    ));
            return $this -> view -> pick('activity/add');
         }catch(\Exception $e) {
            $this -> write_exception_log($e);
            $this -> flashSession -> error($e -> getMessage());
            return $this -> redirect('activity/index');
        }

    }

    /**
     * 发布活动（添加、编辑）
     */
    public function publishAction(){
        try{
            if($this -> request -> isAjax() || !$this -> request -> isPost()){
                throw new \Exception('非法请求');
            }
            $aid            =   $this -> request -> getPost('aid', 'int');
            $name           =   $this -> request -> getPost('name', 'trim');
            $name           =   $this -> filter  -> sanitize($name, 'remove_xss');
            $type           =   $this -> request -> getPost('type', 'trim');
            $draw_num       =   $this -> request -> getPost('draw_num', 'int');
            $intro          =   $this -> request -> getPost('intro', 'trim');
            $intro          =   $this -> filter  -> sanitize($intro, 'remove_xss');
            $start_tm       =   $this -> request -> getPost('start_tm', 'trim');
            $end_tm         =   $this -> request -> getPost('end_tm', 'trim');

            $home_img                = $this -> request -> getPost('home_img_data', 'trim');
            $my_prize_button_img     = $this -> request -> getPost('my_prize_button_img_data', 'trim');
            $activity_back_img       = $this -> request -> getPost('activity_back_img_data', 'trim');
            $draw_button_img         = $this -> request -> getPost('draw_button_img_data', 'trim');
            $draw_button_gray_img    = $this -> request -> getPost('draw_button_gray_img_data', 'trim');
            $activity_intro_title_back_img = $this -> request -> getPost('activity_intro_title_back_img_data', 'trim');
            $activity_intro_back_img = $this -> request -> getPost('activity_intro_back_img_data', 'trim');
            $lottery_button_img      = $this -> request -> getPost('lottery_button_img_data', 'trim');
            $draw_num_back_img       = $this -> request -> getPost('draw_num_back_img_data', 'trim');
            $dial_img                = $this -> request -> getPost('dial_img_data', 'trim');
            $scratch_ticket_img      = $this -> request -> getPost('scratch_ticket_img_data', 'trim');            
            
            
            $start_tm  = strtotime($start_tm);
            $end_tm    = strtotime($end_tm);
            $activity_back_color       =   $this -> request -> getPost('activity_back_color');
            $scratch_ticket_color      =   $this -> request -> getPost('scratch_ticket_color');
            $is_show                   =   $this -> request -> getPost('is_show');

            $this -> validator -> add_rule('type', 'required', '请选择活动类型');
            $this -> validator -> add_rule('name', 'required', '请填写活动名称')
                  -> add_rule('name', 'max_length', '活动名称不超过20个字', 20);
            $this -> validator -> add_rule('draw_num', 'required', '请填写抽奖次数');
            $this -> validator -> add_rule('intro', 'required', '请填写活动说明');
            $this -> validator -> add_rule('start_tm', 'required', '请选择开始时间');
            $this -> validator -> add_rule('end_tm', 'required', '请选择结束时间');
            $map = array(
                    'type'      =>  $type,
                    'name'      =>  $name,
                    'draw_num'  =>  $draw_num,
                    'intro'     =>  $intro,
                    'start_tm'  =>  $start_tm,
                    'end_tm'    =>  $end_tm,
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
            $activity_conf_arr = array(
                    'home_img'                  =>  $home_img,           
                    'my_prize_button_img'       =>  $my_prize_button_img,
                    'activity_back_color'       =>  $activity_back_color,
                    'activity_back_img'         =>  $activity_back_img,
                    'draw_button_img'           =>  $draw_button_img,
                    'draw_button_gray_img'      =>  $draw_button_gray_img,
                    'activity_intro_title_back_img' =>  $activity_intro_title_back_img,
                    'activity_intro_back_img'   =>  $activity_intro_back_img,
                    'lottery_button_img'        =>  $lottery_button_img,
                    'draw_num_back_img'         =>  $draw_num_back_img,
                    'dial_img'                  =>  '',
                    'scratch_ticket_color'      =>  '',
                    'scratch_ticket_img'        =>  '',
                    'is_show'                   =>  0,
                );
            $this->deal_image($activity_conf_arr,'home_img');
            $this->deal_image($activity_conf_arr,'my_prize_button_img');
            $this->deal_image($activity_conf_arr,'activity_back_img');
            $this->deal_image($activity_conf_arr,'draw_button_img');
            $this->deal_image($activity_conf_arr,'draw_button_gray_img');
            $this->deal_image($activity_conf_arr,'activity_intro_title_back_img');
            $this->deal_image($activity_conf_arr,'activity_intro_back_img');
            $this->deal_image($activity_conf_arr,'lottery_button_img');
            $this->deal_image($activity_conf_arr,'draw_num_back_img');
            if($type == 1) {
                $this->deal_image($activity_conf_arr,'dial_img');
            }elseif($type == 2){
                $activity_conf_arr['is_show'] = $is_show;
                $activity_conf_arr['scratch_ticket_color'] = $scratch_ticket_color;
                $this->deal_image($activity_conf_arr,'scratch_ticket_img');
            }
            $activity_conf_json = json_encode($activity_conf_arr);
            $data = array(
                    'name'          =>  $name,
                    'type'          =>  $type,
                    'draw_num'      =>  $draw_num,
                    'intro'         =>  $intro,
                    'start_tm'      =>  $start_tm,
                    'end_tm'        =>  $end_tm,
                    'activity_conf' =>  $activity_conf_json,
                );
            $return = $this -> get_repository('Activity') -> save($data, $aid);
            $log_data = json_encode($_POST);
            if( $aid ) {
                $this -> writelog("编辑了活动aid为{$aid}的活动{$log_data}", 'media_activity', $return, 'edit');
            }else {
                $this -> writelog("添加了活动{$log_data}", 'media_activity', $return, 'add');
            }
            $this -> flashSession -> success('发布活动成功');
        }catch(\Exception $e) {
            $this -> write_exception_log($e);
            $this -> flashSession -> error($e -> getMessage());
        }
        return $this -> redirect('activity/index');
    }

   public function deal_image(&$data,$image_url,$image_name='图片',$expression='jpg|png'){
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
