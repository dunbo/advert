<?php

namespace Marser\App\Backend\Repositories;

use \Marser\App\Backend\Repositories\BaseRepository;

class Prize extends BaseRepository{

    public function __construct(){
        parent::__construct();
    }

    public function get_list($aid){
        $list = $this -> get_model('PrizeModel') -> get_list($aid);
        return $list;
    }

    public function get_list_other($aid, $id){
        $list = $this -> get_model('PrizeModel') -> get_list_other($aid, $id);
        return $list;
    }
    
    public function get_count($aid){
        return $this -> get_model('PrizeModel') -> get_count($aid);
    }

    public function get_prize_info($id){
        $rows = $this -> get_model('PrizeModel') -> get_Prize_info($id);
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
            throw new \Exception('请选择需要删除的活动');
        }
        $affectedRows = $this -> get_model('PrizeModel') -> update_record(array(
            'status' => 0
        ), $id);
        if($affectedRows <= 0){
            throw new \Exception('删除活动失败');
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
        /** 判断活动是否已存在 */
        // $isExist = $this -> get_model('PrizeModel') -> Prize_is_exist($data['name']);
        // if($isExist && $isExist -> count() > 0){
        //     throw new \Exception('活动名称已存在');
        // }
        /** 添加活动 */
        $id = $this -> get_model('PrizeModel') -> insert_record($data);
        $id = intval($id);
        if($id <= 0){
            throw new \Exception('活动数据入库失败');
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
        /** 判断活动是否已存在 */
        // $isExist = $this -> get_model('PrizeModel') -> Prize_is_exist($data['name'], $id);
        // if($isExist && $isExist -> count() > 0){
        //     throw new \Exception('活动名称已存在');
        // }
        /** 更新活动 */
        $affectedRows = $this -> get_model('PrizeModel') -> update_record($data, $id);
        if($affectedRows <= 0){
            throw new \Exception('更新活动失败');
        }
        return $affectedRows;
    }

}