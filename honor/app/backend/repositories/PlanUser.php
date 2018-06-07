<?php

namespace Marser\App\Backend\Repositories;

use \Marser\App\Backend\Repositories\BaseRepository;

class PlanUser extends BaseRepository{

    public function __construct(){
        parent::__construct();
    }

    function get_info($muid){
        $rows = $this -> get_model('PlanUserModel') ->get_info($muid);
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
            throw new \Exception('请选择需要删除');
        }
        $affectedRows = $this -> get_model('PlanUserModel') -> update_record(array(
            'status' => 0
        ), $id);
        if($affectedRows <= 0){
            throw new \Exception('删除失败');
        }
        return $affectedRows;
    }

    protected function create(array $data){
        $id = $this -> get_model('PlanUserModel') -> insert_record($data);
        $id = intval($id);
        if($id <= 0){
            throw new \Exception('入库失败');
        }
        return $id;
    }

    protected function update(array $data, $id){
        $affectedRows = $this -> get_model('PlanUserModel') -> update_record($data, $id);
        if($affectedRows <= 0){
            throw new \Exception('更新败');
        }
        return $affectedRows;
    }

}