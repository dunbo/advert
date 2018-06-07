<?php

namespace Marser\App\Backend\Repositories;

use \Marser\App\Backend\Repositories\BaseRepository;

class MediaActivity extends BaseRepository{

    public function __construct(){
        parent::__construct();
    }

    public function get_list($page, $pagesize=10, array $ext=array()){
        $list = $this -> get_model('MediaActivityModel') -> get_list($page, $pagesize, $ext);
        return $list;
    }

    public function get_info($id){
        $rows = $this -> get_model('MediaActivityModel') -> get_info($id);
        return $rows;
    }

    public function save(array $data, $id){
        $id = intval($id);
        if($id <= 0){
            return $this -> create($data);
        }else{
            return $this -> update($data, $id);
        }
    }

    public function delete($id){
        $id = intval($id);
        if($id <= 0){
            throw new \Exception('请选择需要删除的');
        }
        $affectedRows = $this -> get_model('MediaActivityModel') -> update_record(array(
            'status' => 0
        ), $id);
        if($affectedRows <= 0){
            throw new \Exception('删除失败');
        }
        return $affectedRows;
    }

    /**
     * 活动数据入库
     * @param array $data
     * @return int
     * @throws \Exception
     */
    protected function create(array $data){
        /** 添加活动 */
        $id = $this -> get_model('MediaActivityModel') -> insert_record($data);
        $id = intval($id);
        if($id <= 0){
            throw new \Exception('数据入库失败');
        }
        return $id;
    }

    /**
     * 更新活动数据
     * @param array $data
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    protected function update(array $data, $id){
        /** 更新活动 */
        $affectedRows = $this -> get_model('MediaActivityModel') -> update_record($data, $id);
        if($affectedRows <= 0){
            throw new \Exception('更新失败');
        }
        return $affectedRows;
    }

}