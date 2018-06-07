<?php

namespace Marser\App\Backend\Repositories;

use \Marser\App\Backend\Repositories\BaseRepository;

class Activity extends BaseRepository{

    public function __construct(){
        parent::__construct();
    }

    public function get_list($page, $pagesize=10, array $ext=array()){
        $list = $this -> get_model('ActivityModel') -> get_list($page, $pagesize, $ext);
        return $list;
    }

    public function get_activity_info($id){
        $rows = $this -> get_model('ActivityModel') -> get_activity_info($id);
        return $rows;
    }

    public function get_activity_by_name($name){
        return $this -> get_model('ActivityModel') -> get_activity_by_name($name);
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
        $affectedRows = $this -> get_model('ActivityModel') -> update_record(array(
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
        $isExist = $this -> get_model('ActivityModel') -> activity_is_exist($data['name']);
        if($isExist && $isExist -> count() > 0){
            throw new \Exception('活动名称已存在');
        }
        /** 添加活动 */
        $id = $this -> get_model('ActivityModel') -> insert_record($data);
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
        $isExist = $this -> get_model('ActivityModel') -> activity_is_exist($data['name'], $id);
        if($isExist && $isExist -> count() > 0){
            throw new \Exception('活动名称已存在');
        }
        /** 更新活动 */
        $affectedRows = $this -> get_model('ActivityModel') -> update_record($data, $id);
        if($affectedRows <= 0){
            throw new \Exception('更新活动失败');
        }
        return $affectedRows;
    }


    public function batch_update_record($ext, $ids){
        $id_arr = explode(',', $ids);
        if(count($id_arr) == 1){
            $affectedRows = $this -> get_model('ActivityModel') -> update_record($ext, $ids);
            $affectedRows = intval($affectedRows);
            return $affectedRows;
        }else if(count($id_arr) > 1){
            $return = 0;
            foreach($id_arr as $id){
                $affectedRows = $this -> get_model('ActivityModel') -> update_record($ext, $id);
                $affectedRows = intval($affectedRows);
                $return += $affectedRows;
            }
            return $return;
        }
    }

    public function check_activity_prize($ids) {
        $id_arr = explode(',', $ids);
        if(count($id_arr) == 1){
            $activity = $this -> get_model('ActivityModel') -> get_activity_info($ids);
            if( $activity['type'] == 1 || $activity['type'] == 3) {
                $prize_list = $this -> get_model('PrizeModel') -> get_list($ids);
                if( count($prize_list) != 8 ) {
                    throw new \Exception("活动名称为：{$activity['name']}的奖品配置需要配满8个");
                }
                $xingyu= 0;
                foreach ($prize_list as $k => $val) {
                    if( $val['type'] == 5 ) {
                        $xingyu ++;
                    }
                }
                if( $xingyu < 2 ) {
                    throw new \Exception("活动名称为：{$activity['name']}的奖品最少需要2个幸运奖");
                }
            }
        }else if(count($id_arr) > 1){
            foreach($id_arr as $id){
                $activity = $this -> get_model('ActivityModel') -> get_activity_info($id);
                if( $activity['type'] == 1 || $activity['type'] == 3) {
                    $prize_list = $this -> get_model('PrizeModel') -> get_list($id);
                    if( count($prize_list) != 8 ) {
                        throw new \Exception("活动名称为：{$activity['name']}的奖品配置需要配满8个");
                    }
                    $xingyu= 0;
                    foreach ($prize_list as $k => $val) {
                        if( $val['type'] == 5 ) {
                            $xingyu ++;
                        }
                    }
                    if( $xingyu < 2 ) {
                        throw new \Exception("活动名称为：{$activity['name']}的奖品最少需要2个幸运奖");
                    }
                }
            }
        }
    }



}