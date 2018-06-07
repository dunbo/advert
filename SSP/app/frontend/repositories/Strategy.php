<?php

namespace Marser\App\Frontend\Repositories;

use \Marser\App\Frontend\Repositories\BaseRepository;

class Strategy extends BaseRepository{

    public function __construct(){
        parent::__construct();
    }

     /**
     * 屏蔽策略
     * @param int $status
     * @param array $ext
     * @return array
     * @throws \Exception
     */
    public function get_list($page, $pagesize=10, array $ext=array()){
        $list = $this -> get_model('StrategyModel') -> get_list($page, $pagesize, $ext);
        return $list;
    }

    /**
     * 获取屏蔽策略列表
     * @param int $status
     * @param array $ext
     * @return array
     * @throws \Exception
     */
    public function get_all_list($ext=array()){
        $list = $this -> get_model('StrategyModel') -> get_all_list($ext);
        return $list;
    }

    /**
     * 根据id获取信息
     */
    public function get_strategy_by_id($id){
        $rows = $this -> get_model('StrategyModel') -> get_strategy_by_id($id);
        return $rows;
    }

    /**
     * 保存屏蔽策略
     * @param array $data
     * @param $id
     * @return bool|int
     */
    public function save(array $data, $id){
        $id = intval($id);
        if($id <= 0){
            /** 添加屏蔽策略 */
            return $this -> create($data);
        }else{
            /** 更新屏蔽策略 */
            return $this -> update($data, $id);
        }
    }

    /**
     * 删除屏蔽策略（软删除）
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public function delete($id){
        $id = intval($id);
        if($id <= 0){
            throw new \Exception('请选择需要删除的屏蔽策略');
        }
        $affectedRows = $this -> get_model('StrategyModel') -> update_record(array(
            'status' => 0
        ), $id);
        if($affectedRows <= 0){
            throw new \Exception('删除屏蔽策略失败');
        }
        return $affectedRows;
    }

    /**
     * 屏蔽策略数据入库
     * @param array $data
     * @return int
     * @throws \Exception
     */
    protected function create(array $data){
        /** 判断屏蔽策略是否已存在 */
//     $isExist = $this -> get_model('StrategyModel') -> tag_is_exist($data['name'], $data['slug']);
//     if($isExist && $isExist -> count() > 0){
//         throw new \Exception('屏蔽策略名称或缩略名已存在');
//      }
        /** 添加屏蔽策略 */
        $id = $this -> get_model('StrategyModel') -> insert_record($data);
        $id = intval($id);
        if($id <= 0){
            throw new \Exception('屏蔽策略数据入库失败');
        }
        return $id;
    }

    /**
     * 更新屏蔽策略数据
     * @param array $data
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    protected function update(array $data, $id){
        /** 判断屏蔽策略是否已存在 */
        //$isExist = $this -> get_model('StrategyModel') -> tag_is_exist($data['name'], $data['slug'], $id);
//         if($isExist && $isExist -> count() > 0){
//             throw new \Exception('屏蔽策略名称或缩略名已存在');
//         }
        /** 更新屏蔽策略 */
        $affectedRows = $this -> get_model('StrategyModel') -> update_record($data, $id);
        if($affectedRows <= 0){
            throw new \Exception('更新屏蔽策略失败');
        }
        return $affectedRows;
    }

}