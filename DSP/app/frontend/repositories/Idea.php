<?php

/**
 * 广告创意仓库
 * @category PhalconDSP
 * @author haoshisuo 2017-10-16
 */

namespace Marser\App\Frontend\Repositories;

use \Marser\App\Frontend\Repositories\BaseRepository;

class Idea extends BaseRepository{

    public function __construct(){
        parent::__construct();
    }

    /**
     * 获取广告创意列表
     * @param int $page
     * @param int $pagesize
     * @param array $ext
     * @return mixed
     */
    public function get_list($page, $pagesize=10, array $ext=array()){
        $result = $this -> get_model('IdeaModel') -> get_list($page, $pagesize, $ext);
        return $result;
    }

    //获取一条信息
    public function get_One($id){
    	$result = $this -> get_model('IdeaModel') -> get_One($id);
    	return $result->toArray();
    }

    //添加、编辑广告创意操作
    public function save($map, $id=null){
        /*if(!empty($map['prize_name'])){
             $isExist = $this -> get_model('IdeaModel') -> check_exist(array('prize_name'=>$map['prize_name']), $id);
            if($isExist && $isExist -> count() > 0){
                throw new \Exception('奖品名称已存在');
            }
        }*/
    	$id = intval($id);
        if(empty($id)){
            // 新增广告创意
            $lastId = $this -> get_model('IdeaModel') -> insert_record($map);
            return $lastId;
        }else{
            // 更新广告创意
            $affectedRows = $this -> get_model('IdeaModel') -> update_record($map, $id);
            $affectedRows = intval($affectedRows);
            return $affectedRows;
        }
    }

    /**
     * 获取所有广告创意
     * @return mixed
     */
    public function select(){
        $result = $this -> get_model('IdeaModel') -> select();
        return $result->toArray();
    }

    //检测奖品名称是否存在
    public function check_name($map, $id, $isAjax=0){
        $isExist = $this -> get_model('IdeaModel') -> check_exist($map, $id);
        if($isExist && $isExist -> count() > 0){
            if(!empty($isAjax)){
                return 2;
            }else{
                throw new \Exception('奖品名称已存在');
            }
        }
        return 1;
    }
}