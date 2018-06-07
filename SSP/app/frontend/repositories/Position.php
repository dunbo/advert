<?php

namespace Marser\App\Frontend\Repositories;

use \Marser\App\Frontend\Repositories\BaseRepository;

class Position extends BaseRepository{

    public function __construct(){
        parent::__construct();
    }

     /**
     * 广告位
     * @param int $status
     * @param array $ext
     * @return array
     * @throws \Exception
     */
    public function get_list($page, $pagesize=10, array $ext=array()){
        $list = $this -> get_model('PositionModel') -> get_list($page, $pagesize, $ext);
        return $list;
    }


     /**
     * 根据id获取信息
     * @param $tagname
     * @return int
     * @throws \Exception
     */
    public function get_position_by_id($id,$muid){
        $rows = $this -> get_model('PositionModel') -> get_position_by_id($id,$muid);
        return $rows;
    }

    /**
     * 根据屏蔽策略strategyid获取信息
     */
    public function get_position_by_strategyid($strategyid){
        $list = $this -> get_model('PositionModel') -> get_position_by_strategyid($strategyid);
        return $list;
    }
    /**
     * 保存广告位
     * @param array $data
     * @param $id
     * @return bool|int
     */
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

    /**
     * 删除广告位（软删除）
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public function delete($id){
        $id = intval($id);
        if($id <= 0){
            throw new \Exception('请选择需要删除的广告位');
        }
        $affectedRows = $this -> get_model('PositionModel') -> update_record(array(
            'status' => 0
        ), $id);
        if($affectedRows <= 0){
            throw new \Exception('删除广告位失败');
        }
        return $affectedRows;
    }

    /**
     * 广告位开关
     * @param $id
     * @param $status
     * @return mixed
     * @throws \Exception
     */
    public function OnOff($id, $status) {
    	$id = intval($id);
    	if($id <= 0) {
    		throw new \Exception("请选择要操作的广告位");
    	}
    	$affectedRows = $this -> get_model('PositionModel') -> update_record(array(
    		'switch' => $status
    	), $id);
    	if($affectedRows <= 0) {
    		throw new \Exception("修改广告位开关失败");
    	}
    	return $affectedRows;
    }


    /**
     * 广告位数据入库
     * @param array $data
     * @return int
     * @throws \Exception
     */
    protected function create(array $data){
        // /** 判断广告位是否已存在 */
        $isExist = $this -> get_model('PositionModel') -> position_is_exist($data['name'],$data['muid']);
        if($isExist && $isExist -> count() > 0){
            throw new \Exception('广告位名称已存在');
        }
        /** 判断媒体是否已存在 */
        $isExist2 = $this -> get_model('MediaModel') -> media_is_exist($data['name'],0,0);
        if($isExist2 && $isExist2 -> count() > 0){
            throw new \Exception('广告位名称已存在');
        }
         /** 判断媒体是否已存在 */
        $isExist3 = $this -> get_model('UserModel') -> username_is_exist($data['name']);
        if($isExist3 && $isExist3 -> count() > 0){
            throw new \Exception('广告位名称已存在');
        }
        /** 添加广告位 */
        $id = $this -> get_model('PositionModel') -> insert_record($data);
        $id = intval($id);
        if($id <= 0){
            throw new \Exception('广告位数据入库失败');
        }
        return $id;
    }

    /**
     * 更新广告位数据
     * @param array $data
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    protected function update(array $data, $id){
        /** 判断广告位是否已存在 */
        $isExist = $this -> get_model('PositionModel') -> position_is_exist($data['name'],$data['muid'],$id);
        if($isExist && $isExist -> count() > 0){
            throw new \Exception('广告位名称已存在');
        }
        /** 判断媒体是否已存在 */
        $isExist2 = $this -> get_model('MediaModel') -> media_is_exist($data['name'],0,0);
        if($isExist2 && $isExist2 -> count() > 0){
            throw new \Exception('广告位名称已存在');
        }
        /** 判断媒体是否已存在 */
        $isExist3 = $this -> get_model('UserModel') -> username_is_exist($data['name']);
        if($isExist3 && $isExist3 -> count() > 0){
            throw new \Exception('广告位名称已存在');
        }
        /** 更新广告位 */
        $affectedRows = $this -> get_model('PositionModel') -> update_record($data, $id);
        if($affectedRows <= 0){
            throw new \Exception('更新广告位失败');
        }
        return $affectedRows;
    }

}