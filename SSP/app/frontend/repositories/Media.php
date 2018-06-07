<?php

namespace Marser\App\Frontend\Repositories;

use \Marser\App\Frontend\Repositories\BaseRepository;

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

    /**
     * 根据id获取信息
     * @param $tagname
     * @return int
     * @throws \Exception
     */
    public function get_media_by_id($id, $muid){
        $rows = $this -> get_model('MediaModel') -> get_media_by_id($id, $muid);
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
        $isExist = $this -> get_model('MediaModel') -> media_is_exist($data['name'],$data['muid']);
        if($isExist && $isExist -> count() > 0){
            throw new \Exception('媒体名称已存在');
        }
        /** 判断媒体是否已存在 */
        $isExist2 = $this -> get_model('PositionModel') -> position_is_exist($data['name'], 0, 0);
        if($isExist2 && $isExist2 -> count() > 0){
            throw new \Exception('媒体名称已存在');
        }
         /** 判断媒体是否已存在 */
        $isExist3 = $this -> get_model('UserModel') -> username_is_exist($data['name']);
        if($isExist3 && $isExist3 -> count() > 0){
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
        $isExist = $this -> get_model('MediaModel') -> media_is_exist($data['name'], $data['muid'],$mmid);
        if($isExist && $isExist -> count() > 0){
            throw new \Exception('媒体名称已存在');
        }
        /** 判断媒体是否已存在 */
        $isExist2 = $this -> get_model('PositionModel') -> position_is_exist($data['name'], 0, 0);
        if($isExist2 && $isExist2 -> count() > 0){
            throw new \Exception('媒体名称已存在');
        }
         /** 判断媒体是否已存在 */
        $isExist3 = $this -> get_model('UserModel') -> username_is_exist($data['name']);
        if($isExist3 && $isExist3 -> count() > 0){
            throw new \Exception('媒体名称已存在');
        }
        /** 更新媒体 */
        $affectedRows = $this -> get_model('MediaModel') -> update_record($data, $mmid);
        if($affectedRows <= 0){
            throw new \Exception('更新媒体失败');
        }
        return $affectedRows;
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
}