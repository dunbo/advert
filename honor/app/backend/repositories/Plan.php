<?php

namespace Marser\App\Backend\Repositories;

use \Marser\App\Backend\Repositories\BaseRepository;

class Plan extends BaseRepository{

    public function __construct(){
        parent::__construct();
    }

    public function get_list(){
        $list = $this -> get_model('PlanModel') -> get_list();
        return $list;
    }

    public function get_info($id){
        $list = $this -> get_model('PlanModel') -> get_info($id);
        return $list;
    }

    public function save(array $data, $id){
        $id = intval($id);
        if($id <= 0){
            /** 添加广告位 */
            return $this -> create($data);
        }else{
            /** 更新广告位 */
            return $this -> update($data, $id);
        }
    }

    public function delete($id){
        $id = intval($id);
        if($id <= 0){
            throw new \Exception('请选择需要删除的广告位');
        }
        $affectedRows = $this -> get_model('PlanModel') -> update_record(array(
            'status' => 0
        ), $id);
        if($affectedRows <= 0){
            throw new \Exception('删除广告位失败');
        }
        return $affectedRows;
    }


    protected function create(array $data){
        // /** 判断广告位是否已存在 */
        $isExist = $this -> get_model('PlanModel') -> position_is_exist($data['name']);
        if($isExist && $isExist -> count() > 0){
            throw new \Exception('广告位名称已存在');
        }
        /** 添加广告位 */
        $id = $this -> get_model('PlanModel') -> insert_record($data);
        $id = intval($id);
        if($id <= 0){
            throw new \Exception('广告位数据入库失败');
        }
        return $id;
    }

    protected function update(array $data, $id){
        /** 判断广告位是否已存在 */
        $isExist = $this -> get_model('PlanModel') -> position_is_exist($data['name'], $id);
        if($isExist && $isExist -> count() > 0){
            throw new \Exception('广告位名称已存在');
        }
        /** 更新广告位 */
        $affectedRows = $this -> get_model('PlanModel') -> update_record($data, $id);
        if($affectedRows <= 0){
            throw new \Exception('更新广告位失败');
        }
        return $affectedRows;
    }

    public function batch_update_record($ext, $ids){
        $id_arr = explode(',', $ids);
        if(count($id_arr) == 1){
            $affectedRows = $this -> get_model('PlanModel') -> update_record($ext, $ids);
            $affectedRows = intval($affectedRows);
            return $affectedRows;
        }else if(count($id_arr) > 1){
            $return = 0;
            foreach($id_arr as $id){
                $affectedRows = $this -> get_model('PlanModel') -> update_record($ext, $id);
                $affectedRows = intval($affectedRows);
                $return += $affectedRows;
            }
            return $return;
        }
    }
}