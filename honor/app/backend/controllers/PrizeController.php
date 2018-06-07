<?php
/**
 * 活动管理
 */
namespace Marser\App\Backend\Controllers;

use \Marser\App\Backend\Controllers\BaseController;
use \Marser\App\Helpers\PaginatorHelper;

class PrizeController extends BaseController{

   private  $image_size = array(
         'prize_img' => array(0,0),
         'dialog_prize_img' => array(0,0),
         'my_prize_img' => array(0,0),
        );

    public function indexAction() {
        $aid = $this -> request -> get('aid', 'int');
        if( !$aid ) {
            $this -> flashSession -> error('参数有误');
            return $this -> response -> redirect('activity/index');
        }
        $activity =  $this -> get_repository('Activity') -> get_activity_info($aid);
        if( $activity['status'] == 1 && $activity['start_tm'] <= time() ) {
            $activity['author'] = 0;
        }else {
            $activity['author'] = 1;
        } 
        $list = $this -> get_repository('Prize') -> get_list($aid);
        $this -> view -> setVars(array(
            'title'     =>  '奖品',
            'aid'       =>  $aid,
            'list'      =>  $list,
            'activity'  =>  $activity,
        ));
        $this -> view -> pick('prize/index');

    }
    public function editAction() {
        $id = $this -> request -> get('id', 'int');  
        $prize = $this -> get_repository('Prize') -> get_prize_info($id);
        if( $prize['type'] == 1 ) {
            //查询优惠券数
            $coupon_count = $this -> get_repository('Coupon') -> get_count($prize['aid'],$prize['id']);
        }
        $activity =  $this -> get_repository('Activity') -> get_activity_info($prize['aid']);
        if( $activity['status'] == 1 && $activity['start_tm'] <= time() ) {
            $activity['author'] = 0;
        }else {
            $activity['author'] = 1;
        } 
        $this -> view -> setVars(array(
            'title'         =>  '编辑奖品',
            'id'            =>  $id,
            'prize'         =>  $prize,
            'coupon_count'  =>  $coupon_count,
            'activity'      =>  $activity,
        ));
        $this -> view -> setMainView('prize/edit');
    }

    public function coupon_checkAction(){
        if($_FILES['coupon']['tmp_name']){
            $array = array('csv');
            $ytypes = $_FILES['coupon']['name'];
            $info = pathinfo($ytypes);
            $type =  $info['extension'];//获取文件件扩展名
            if(!in_array($type,$array)){
                $return = array(
                    'code' => 0,
                    'msg' => "上传格式错误",
                );
                exit(json_encode($return));                     
            }           
            $data = file_get_contents($_FILES['coupon']['tmp_name']);
            //判断是否是utf-8编辑
            if(mb_check_encoding($data,"utf-8") != true){
                $data = iconv("gbk","utf-8", $data);
            }
            $data = str_replace("\r\n","\n",$data); 
            $data_arr = explode("\n", $data);
            $newarr = array();
            $n = 0;
            $str = '';
            foreach($data_arr as $k=>$v){
                if($k == 0){
                    continue;
                }
                if( !trim($v) ) {
                    continue;
                }
                $num = $v;
                if(isset($newarr[$num])){
                    $str .= "重复数据：".$num."\n";
                }else{
                    $newarr[$num] = 1;
                    $n++;
                }
            }
            if($str != ''){
                $return = array(
                    'code' => 0,
                    'msg' => $str?$str:'获取不到优惠券！',
                );
                exit(json_encode($return));     
            }else{
                $return = array(
                    'code'  =>  1,
                    'count' =>  $n,
                );
                exit(json_encode($return));     
            }
        }       
    }



      /**
     * 发布奖品（添加、编辑）
     */
    public function publishAction(){
        try{
            if($this -> request -> isAjax() || !$this -> request -> isPost()){
                throw new \Exception('非法请求');
            }
            $id         =   $this -> request -> getPost('id', 'int');
            $aid        =   $this -> request -> getPost('aid', 'int');

            $type       =   $this -> request -> getPost('type', 'int');

            $prize_img        = $this -> request -> getPost('prize_img_data','trim','');
            $dialog_prize_img = $this -> request -> getPost('dialog_prize_img_data','trim','');
            $my_prize_img     = $this -> request -> getPost('my_prize_img_data','trim','');

            $name        =   $this -> request -> getPost('name', 'trim');
            $name        =   $this -> filter  -> sanitize($name, 'remove_xss');
            $award_intro =   $this -> request -> getPost('award_intro', 'trim');
            $award_intro =   $this -> filter  -> sanitize($award_intro, 'remove_xss');
            $num         =   $this -> request -> getPost('num', 'int');
            $probability =   $this -> request -> getPost('probability', 'trim');
            $url         =   $this -> request -> getPost('url', 'trim');
            $url         =   $this -> filter  -> sanitize($url, 'remove_xss');
            $prize_intro =   $this -> request -> getPost('prize_intro', 'trim');
            $prize_intro =   $this -> filter  -> sanitize($prize_intro, 'remove_xss');

            if( !$aid ) {
                throw new \Exception('参数有误');
            }
            //验证奖品数
            $activity =  $this -> get_repository('Activity') -> get_activity_info($aid);
            if( !$id && ($activity['type'] == 1 || $activity['type'] == 3) ) {
                $count = $this -> get_repository('Prize') -> get_count($aid);
                if( $count >= 8 ) {
                    $act_name = $activity['type']==1?'大转盘':'九宫格';
                    throw new \Exception("配置的{$act_name}奖品数不能超过8个");
                }
            }
            $this -> validator -> add_rule('type', 'required', '请选择活动类型');
            if( $type == 1 ) {
                $this -> validator -> add_rule('name', 'required', '请填写活动名称');
                $this -> validator -> add_rule('award_intro', 'required', '请填写中奖文字说明');
                $this -> validator -> add_rule('num', 'required', '请填写奖品数量');
                $this -> validator -> add_rule('probability', 'required', '请填写中奖概率');
                $url && $this -> validator -> add_rule('url', 'url', '立即领取跳转地址格式不对');
                $this -> validator -> add_rule('prize_intro', 'required', '请填写活动说明');
                $data_arr = array(
                        'prize_img'          =>  $prize_img,           
                        'dialog_prize_img'   =>  $dialog_prize_img,
                        'my_prize_img'       =>  $my_prize_img,
                        'type'               =>  $type,
                        'name'               =>  $name,
                        'award_intro'        =>  $award_intro,
                        'num'                =>  $num,
                        'prize_num'          =>  $num,
                        'probability'        =>  $probability,
                        'url'                =>  $url,
                        'prize_intro'        =>  $prize_intro,
                    );
            }elseif( $type == 2 || $type == 3 ) {
                $this -> validator -> add_rule('name', 'required', '请填写活动名称');
                $this -> validator -> add_rule('award_intro', 'required', '请填写中奖文字说明');
                $this -> validator -> add_rule('num', 'required', '请填写奖品数量');
                $this -> validator -> add_rule('probability', 'required', '请填写中奖概率');
                $url && $this -> validator -> add_rule('url', 'url', '立即领取跳转地址格式不对');
                $this -> validator -> add_rule('prize_intro', 'required', '请填写活动说明');
                $data_arr = array(
                        'prize_img'          =>  $prize_img,           
                        'dialog_prize_img'   =>  $dialog_prize_img,
                        'my_prize_img'       =>  $my_prize_img,
                        'type'               =>  $type,
                        'name'               =>  $name,
                        'award_intro'        =>  $award_intro,
                        'num'                =>  $num,
                        'prize_num'          =>  $num,
                        'probability'        =>  $probability,
                        'url'                =>  $url,
                        'prize_intro'        =>  $prize_intro,
                    );
            }elseif( $type == 4 ) {
                $this -> validator -> add_rule('name', 'required', '请填写活动名称');
                $this -> validator -> add_rule('award_intro', 'required', '请填写中奖文字说明');
                $this -> validator -> add_rule('num', 'required', '请填写奖品数量');
                $this -> validator -> add_rule('probability', 'required', '请填写中奖概率');
                $data_arr = array(
                        'prize_img'          =>  $prize_img,           
                        'dialog_prize_img'   =>  $dialog_prize_img,
                        'my_prize_img'       =>  $my_prize_img,
                        'type'               =>  $type,
                        'name'               =>  $name,
                        'award_intro'        =>  $award_intro,
                        'num'                =>  $num,
                        'prize_num'          =>  $num,
                        'probability'        =>  $probability,
                        'url'                =>  '',
                        'prize_intro'        =>  '',
                    );
            }elseif( $type == 5 ) {
                $this -> validator -> add_rule('name', 'required', '请填写活动名称');
                $this -> validator -> add_rule('probability', 'required', '请填写中奖概率');
                $data_arr = array(
                        'prize_img'          =>  $prize_img,           
                        'dialog_prize_img'   =>  $dialog_prize_img,
                        'my_prize_img'       =>  $my_prize_img,
                        'type'               =>  $type,
                        'name'               =>  $name,
                        'award_intro'        =>  '',
                        'num'                =>  0,
                        'prize_num'          =>  0,
                        'probability'        =>  $probability,
                        'url'                =>  '',
                        'prize_intro'        =>  '',
                    );
            }
             /** 截获验证异常 */
            $error = $this -> validator -> run( $data_arr );
            if($error) {
                $error = array_values($error);
                $error = $error[0];
                throw new \Exception($error['message'], $error['code']);
            }
            //验证概率
            $num2   =   explode("/", $probability);
            $pos    =   strpos($probability, "/");
            if($probability && $pos == false) {
                throw new \Exception('概率格式错误');
            }else if($probability != 0 && (!is_numeric($num2[0]) || !is_numeric($num2[1]))){
                throw new \Exception('概率格式出错');
            }else if($probability == '') {
                $probability = 0;
            }
            $prize_other = $this -> get_repository('prize') -> get_list_other($aid, $id);
            $probability_num = 0;
            foreach($prize_other as $v) {
                if(empty($v['probability'])){
                    continue;
                }
                $num = explode("/",$v['probability']);
                $calculate = ($num[0]/$num[1]);
                $probability_num = $probability_num + $calculate;
            }
            $calculate2 = ($num2[0]/$num2[1]);
            $probability_num = $probability_num + $calculate2;
            if($probability_num  > 1){
                throw new \Exception('概率不能大于1');
            }

            $this->deal_image($data_arr,'prize_img');
            $this->deal_image($data_arr,'dialog_prize_img');
            $this->deal_image($data_arr,'my_prize_img');
            $coupon_arr = $this->deal_csv('coupon');

            //上传文件路径
            if($_FILES['coupon']['tmp_name']){
                $dir_name = "/csv/".date("Ym/d/");
                if(!is_dir(UPLOAD_PATH.$dir_name)){
                    mkdir(UPLOAD_PATH.$dir_name, 0755, true);
                }
                $save_name = $dir_name.time().'_'.rand(1000,9999).'.csv';
                $csv_path = UPLOAD_PATH.$save_name;
                if(!move_uploaded_file($_FILES['coupon']['tmp_name'], $csv_path)){
                    throw new \Exception("上传{$image_name}出错");
                }
                $data_arr['coupon_path'] = $save_name;
            }

            $time = time();
            if( $id ) {
                $data_arr['aid']       =  $aid;
                $data_arr['update_tm'] =  $time;
            }else {
                $data_arr['aid']       =  $aid;
                $data_arr['update_tm'] = $time;
                $data_arr['create_tm'] = $time;
            }
            $return = $this -> get_repository('prize') -> save($data_arr, $id);
            $awardid = $id?$id:$return;
            $key = "honor_gift_aid:{$aid}:pid:{$awardid}";
            if($coupon_arr) {
                if( $id ) {
                    //删除之前的优惠卷码
                    $this -> get_repository('Coupon') -> delete($aid, $id);
                    //删除缓存
                    $this->redis->remove($key);
                }
                $str = '';
                foreach ($coupon_arr as $k => $val) {
                    $str .= "({$aid},{$awardid},'{$val}',{$time}),";
                }
                $str = rtrim($str, ',');
                $this -> get_repository('Coupon') -> batch_add($str);
                //加入队列
                $coupon_list =  $this -> get_repository('Coupon') -> get_list($aid,$awardid);
                if( !empty($coupon_list) ) {
                    foreach ($coupon_list as $k => $v) {
                          $this->redis->rpush($key, $v['coupon_code']);                 
                    }
                }
            }
            $this -> flashSession -> success('发布奖品成功');
            $log_data = json_encode($_POST);
            if( $id ) {
                $this -> writelog("编辑了奖品id为{$id}的奖品{$log_data}", 'media_prize', $return, 'edit');
                echo "<script>window.parent.location.reload();window.parent.close_win()</script>";die;
            }else {
                $this -> writelog("添加了奖品id为{$return}的奖品{$log_data}", 'media_prize', $return, 'add');
            }
        }catch(\Exception $e) {
            $this -> write_exception_log($e);
            $this -> flashSession -> error($e -> getMessage());
            if( $id ) {
                echo "<script>window.parent.location.reload();window.parent.close_win()</script>";die;
            }
        }
        return $this -> redirect('prize/index?aid='.$aid);
    }

    public function deleteAction() {
        $id = $this -> request -> get('id', 'int');  
        $aid = $this -> request -> get('aid', 'int');
        $data['status'] = 0;
        try{
            $return = $this -> get_repository('prize') -> save($data, $id);
            $this -> flashSession -> success('删除奖品成功');
            $this -> writelog("删除了奖品,id为:{$id}", 'media_prize', $id, 'edit');
        }catch(\Exception $e) {
            $this -> flashSession -> error($e -> getMessage());
        }
        return $this -> redirect('prize/index?aid='.$aid);
    }

   public function deal_image(&$data,$image_url,$image_name='图片',$expression='jpg|png|gif'){
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

    public function deal_csv($key,$csv_name='优惠券',$expression= array('csv')) {
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
                if($k == 0) {
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



}
