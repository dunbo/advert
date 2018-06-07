<?php

namespace Marser\App\Backend\Repositories;

use \Marser\App\Backend\Repositories\BaseRepository;

class Media extends BaseRepository{

    public function __construct(){
        parent::__construct();
    }

     /**
     * 媒体列表
     * @param int $status
     * @param array $ext
     * @return array
     * @throws \Exception
     */
    public function get_list($page, $pagesize=10, array $ext=array()){
        $list = $this -> get_model('MediaModel') -> get_list($page, $pagesize, $ext);
        return $list;
    }

    /**
     * 获取媒体列表
     * @param int $status
     * @param array $ext
     * @return array
     * @throws \Exception
     */
    public function get_all_list($ext=array()){
        $list = $this -> get_model('MediaModel') -> get_all_list($ext);
        return $list;
    }

    //获取某个媒体主下的媒体列表
     public function get_list_by_muid( $muid, $ext=array() ) {
        return $this -> get_model('MediaModel') -> get_list_by_muid($muid, $ext);
     }

    /**
     * 根据id获取信息
     * @param $tagname
     * @return int
     * @throws \Exception
     */
    public function get_media_by_id($id){
        $rows = $this -> get_model('MediaModel') -> get_media_by_id($id);
        return $rows;
    }

    /**
    * 获取媒体详情
    */
    public function get_media_info($mmid){
        $rows = $this -> get_model('MediaModel') -> get_media_info($mmid);
        return $rows;
    }

    /**
     * 保存媒体
     * @param array $data
     * @param $mmid
     * @return bool|int
     */
    public function save(array $data, $mmid){
        $mmid = intval($mmid);
        if($mmid <= 0){
            /** 添加媒体 */
            return $this -> create($data);
        }else{
            /** 更新媒体 */
            return $this -> update($data, $mmid);
        }
    }

    /**
     * 删除媒体（软删除）
     * @param $mmid
     * @return mixed
     * @throws \Exception
     */
    public function delete($mmid){
        $mmid = intval($mmid);
        if($mmid <= 0){
            throw new \Exception('请选择需要删除的媒体');
        }
        $affectedRows = $this -> get_model('MediaModel') -> update_record(array(
            'status' => 0
        ), $mmid);
        if($affectedRows <= 0){
            throw new \Exception('删除媒体失败');
        }
        return $affectedRows;
    }

    /**
     * 媒体数据入库
     * @param array $data
     * @return int
     * @throws \Exception
     */
    protected function create(array $data){
        /** 判断媒体是否已存在 */
        $isExist = $this -> get_model('MediaModel') -> media_is_exist($data['name']);
        if($isExist && $isExist -> count() > 0){
            throw new \Exception('媒体名称已存在');
        }
        /** 添加媒体 */
        $mmid = $this -> get_model('MediaModel') -> insert_record($data);
        $mmid = intval($mmid);
        if($mmid <= 0){
            throw new \Exception('媒体数据入库失败');
        }
        return $mmid;
    }

    /**
     * 更新媒体数据
     * @param array $data
     * @param $mmid
     * @return mixed
     * @throws \Exception
     */
    protected function update(array $data, $mmid){
        /** 判断媒体是否已存在 */
        // $isExist = $this -> get_model('MediaModel') -> media_is_exist($data['name'], $mmid);
        // if($isExist && $isExist -> count() > 0){
        //     throw new \Exception('媒体名称已存在');
        // }
        /** 更新媒体 */
        $affectedRows = $this -> get_model('MediaModel') -> update_record($data, $mmid);
        if($affectedRows <= 0){
            throw new \Exception('更新媒体失败');
        }
        return $affectedRows;
    }


    public function batch_update_record($ext, $ids){
        $id_arr = explode(',', $ids);
        if(count($id_arr) == 1){
            if( isset($ext['examine_status']) && $ext['examine_status'] == 0 ) {
                $ext['apk_status'] = 0;
                $ext['apk_path'] = '';
            }
            //如果是通过生成appK
            if( isset($ext['examine_status']) && $ext['examine_status'] == 1 ) {
                $media_info = $this -> get_model('MediaModel') -> get_media_by_id($ids);
                if($media_info['examine_status']==0 && $media_info['type']==1 && $media_info['tf_type']==1) {
                    // 媒体类型是android投放类型为SDK的情况 在待审核列表点通过进入待认证
                    $ext['examine_status'] = 3;
                }
                if($ext['examine_status'] == 1 && empty($media_info['appkey'])) {
                    $code_1 = $this->generate_code();
                    $appkey =  md5(md5($code_1.$ids.time()));
                    $ext['appkey'] = $appkey;
                    //如果是h5生成secret
                    if($media_info['type'] == 2) {
                        $code_2 = $this->generate_code();
                        $secret = md5(md5($code_2.$ids.time()));
                        $ext['secret'] = $secret;    
                    }
                }
            }
            $affectedRows = $this -> get_model('MediaModel') -> update_record($ext, $ids);
            $affectedRows = intval($affectedRows);
            return $affectedRows;
        }else if(count($id_arr) > 1){
            $return = 0;
            foreach($id_arr as $id){
                if(isset($ext['examine_status']) && $ext['examine_status'] == 0 ) {
                    $ext['apk_status'] = 0;
                    $ext['apk_path'] = '';
                }
                if(isset($ext['examine_status']) &&  $ext['examine_status'] == 1 ) {
                    $media_info = $this -> get_model('MediaModel') -> get_media_by_id($id);
                    if($media_info['examine_status']==0 && $media_info['type']==1 && $media_info['tf_type']==1) {
                        // 媒体类型是android投放类型为SDK的情况 在待审核列表点通过进入待认证
                        $ext['examine_status'] = 3;
                    }
                    if($ext['examine_status'] == 1 && empty($media_info['appkey'])) {
                        $code_1 = $this->generate_code();
                        $appkey =  md5(md5($code_1.$ids.time()));
                        $ext['appkey'] = $appkey;
                        //如果是h5生成secret
                        if($media_info['type'] == 2) {
                            $code_2 = $this->generate_code();
                            $secret = md5(md5($code_2.$ids.time()));
                            $ext['secret'] = $secret;    
                        }
                    }
                }
                $affectedRows = $this -> get_model('MediaModel') -> update_record($ext, $id);
                $affectedRows = intval($affectedRows);
                $return += $affectedRows;
            }
            return $return;
        }
    }

    //检查媒体数据是否完整
    public function check_media_data($ids){
         $id_arr = explode(',', $ids);
         if( count($id_arr) == 1 ) {
              $rows = $this -> get_model('MediaModel') -> get_media_by_id($ids);
               if( !$rows['name'] || !$rows['doc_key'] || !$rows['intro'] || !$rows['anzhi_tag'] || !$rows['level'] || !$rows['industry_parentid'] || !$rows['industryid'] ) {
                    throw new \Exception('媒体名称:“'.$rows['name'].'”信息不完整，不能通过审核');
               }    
               if( $rows['type'] == 1 ) {
                   if( !$rows['package_name'] ) {
                        throw new \Exception('媒体名称:“'.$rows['name'].'”信息不完整，不能通过审核');
                    }
               }else {
                    // if( !$rows['host'] || !$rows['token_url'] ) {
                    //     throw new \Exception('媒体名称:“'.$rows['name'].'”信息不完整，不能通过审核');
                    // }
               }
               if($rows['examine_status']==2 && $rows['type'] == 1 && $rows['tf_type'] == 1 && $rows['apk_status'] !=1) {
                    throw new \Exception('媒体名称:“'.$rows['name'].'”认证未通过，不能通过审核');
               } 
         }elseif( count($id_arr) > 1 ) {
            foreach($id_arr as $id){
                $rows = $this -> get_model('MediaModel') -> get_media_by_id($id);
                 if( !$rows['name'] || !$rows['doc_key'] || !$rows['intro'] || !$rows['anzhi_tag'] || !$rows['level'] || !$rows['industry_parentid'] || !$rows['industryid'] ) {
                    throw new \Exception('媒体名称:“'.$rows['name'].'”信息不完整，不能通过审核');
                    return false;
               }    
               if( $rows['type'] == 1 ) {
                   if( !$rows['package_name'] ) {
                        throw new \Exception('媒体名称:“'.$rows['name'].'”信息不完整，不能通过审核');
                        return false;
                    }
               }else {
                    // if( !$rows['host'] || !$rows['token_url'] ) {
                    //     throw new \Exception('媒体名称:“'.$rows['name'].'”信息不完整，不能通过审核');
                    //     return false;
                    // }
               } 
               if($rows['examine_status']==2 && $rows['type'] == 1 && $rows['tf_type'] == 1 && $rows['apk_status'] !=1) {
                    throw new \Exception('媒体名称:“'.$rows['name'].'”认证未通过，不能通过审核');
               } 
            }
         }
         return true;
    }

    public function batch_update_apk($ext, $ids){
        $id_arr = explode(',', $ids);
        if(count($id_arr) == 1){
            $affectedRows = $this -> get_model('MediaModel') -> update_record($ext, $ids);
            $affectedRows = intval($affectedRows);
            return $affectedRows;
        }else if(count($id_arr) > 1){
            $return = 0;
            foreach($id_arr as $id){
                $affectedRows = $this -> get_model('MediaModel') -> update_record($ext, $id);
                $affectedRows = intval($affectedRows);
                $return += $affectedRows;
            }
            return $return;
        }
    }

        /**
     * 更新媒体数据
     * @param array $data
     * @param $mmid
     * @return mixed
     * @throws \Exception
     */
    public function update_ext(array $data, $mmid){
        $affectedRows = $this -> get_model('MediaModel') -> update_record($data, $mmid);
        if($affectedRows <= 0){
            throw new \Exception('更新媒体失败');
        }
        return $affectedRows;
    }

    public function batch_export($ids){
        $result = $this -> get_model('MediaModel') -> batch_export($ids);
        return $result;
    }
    
    public function generate_code($length = 4) {
        return rand(pow(10,($length-1)), pow(10,$length)-1);
    }
}