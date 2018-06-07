<?php

namespace Marser\App\Backend\Repositories;

use \Marser\App\Backend\Repositories\BaseRepository;

class UserMedia extends BaseRepository{

    public function __construct(){
        parent::__construct();
    }

     /**
     * 媒体主列表
     * @param int $status
     * @param array $ext
     * @return array
     * @throws \Exception
     */
    public function get_list($page, $pagesize=10, array $ext=array()){
        $list = $this -> get_model('UserMediaModel') -> get_list($page, $pagesize, $ext);
        return $list;
    }

    /**
     * 媒体主财务信息列表
     * @param int $status
     * @param array $ext
     * @return array
     * @throws \Exception
     */
    public function get_finance_list($page, $pagesize=10, array $ext=array()){
        $list = $this -> get_model('UserMediaModel') -> get_finance_list($page, $pagesize, $ext);
        return $list;
    }

    public function get_all_list( $ext=array() ){
        $list = $this -> get_model('UserMediaModel') -> get_all_list($ext);
        return $list;
    }

    /**
     * 根据muid获取信息
     * @param $tagname
     * @return int
     * @throws \Exception
     */
    public function get_user_by_muid($muid,$ext=''){
        $rows = $this -> get_model('UserMediaModel') -> get_user_by_muid($muid,$ext);
        return $rows;
    }

    /**
     * 根据username获取信息
     * @param $tagname
     * @return int
     * @throws \Exception
     */
    public function get_user_by_username($username){
        $rows = $this -> get_model('UserMediaModel') -> get_user_by_username($username);
        return $rows;
    }

    /**
     * 保存媒体主
     * @param array $data
     * @param $muid
     * @return bool|int
     */
    public function save(array $data, $muid){
        //print_r($muid);die;
        $muid = intval($muid);
        if($muid <= 0){
            /** 添加媒体主 */
            return $this -> create($data);
        }else{
            /** 更新媒体主 */
            return $this -> update($data, $muid);
        }
    }

    /**
     * 删除媒体主（软删除）
     * @param $muid
     * @return mixed
     * @throws \Exception
     */
    public function delete($muid){
        $muid = intval($muid);
        if($muid <= 0){
            throw new \Exception('请选择需要删除的媒体主');
        }
        $affectedRows = $this -> get_model('UserMediaModel') -> update_record(array(
            'status' => 0
        ), $muid);
        if($affectedRows <= 0){
            throw new \Exception('删除媒体主失败');
        }
        return $affectedRows;
    }

    /**
     * 媒体主数据入库
     * @param array $data
     * @return int
     * @throws \Exception
     */
    protected function create(array $data){
        /** 判断媒体主是否已存在 */
        $isExist = $this -> get_model('UserMediaModel') -> usermeida_is_exist($data['username']);
        if($isExist && $isExist -> count() > 0){
            throw new \Exception('登录名称已存在');
        }
        /** 添加媒体主 */
        $muid = $this -> get_model('UserMediaModel') -> insert_record($data);
        $muid = intval($muid);
        if($muid <= 0){
            throw new \Exception('媒体主数据入库失败');
        }
        return $muid;
    }

    /**
     * 更新媒体主数据
     * @param array $data
     * @param $muid
     * @return mailparse_determine_best_xfer_encoding(fp)
     * @throws \Exception
     */
    protected function update(array $data, $muid){
        /** 判断媒体主是否已存在 */
        // $isExist = $this -> get_model('UserMediaModel') -> usermeida_is_exist($data['username'], $muid);
        // if($isExist && $isExist -> count() > 0){
        //     throw new \Exception('登录名称已存在');
        // }
        /** 更新媒体主 */
        $affectedRows = $this -> get_model('UserMediaModel') -> update_record($data, $muid);
        if($affectedRows <= 0){
            throw new \Exception('更新媒体主失败');
        }
        return $affectedRows;
    }


     public function batch_update_record($ext, $ids){
        $id_arr = explode(',', $ids);
        if(count($id_arr) == 1){
            $affectedRows = $this -> get_model('UserMediaModel') -> update_record($ext, $ids);
            $affectedRows = intval($affectedRows);
            return $affectedRows;
        }else if(count($id_arr) > 1){
            $return = 0;
            foreach($id_arr as $id){
                $affectedRows = $this -> get_model('UserMediaModel') -> update_record($ext, $id);
                $affectedRows = intval($affectedRows);
                $return += $affectedRows;
            }
            return $return;
        }
    }

}