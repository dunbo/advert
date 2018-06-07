<?php

/**
 * 广告创意仓库
 * @category PhalconHonor
 * @author haoshisuo 2017-10-19
 */

namespace Marser\App\Backend\Repositories;

use \Marser\App\Backend\Repositories\BaseRepository;

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

    //批量审核操作
    public function examine($ext, $ids){
        $id_arr = explode(',', $ids);
        if(count($id_arr) == 1){
            $affectedRows = $this -> get_model('IdeaModel') -> update_record($ext, $ids);
            $affectedRows = intval($affectedRows);
            return $affectedRows;
        }else if(count($id_arr) > 1){
            $return = 0;
            foreach($id_arr as $id){
                $affectedRows = $this -> get_model('IdeaModel') -> update_record($ext, $id);
                $affectedRows = intval($affectedRows);
                $return += $affectedRows;
            }
            return $return;
        }   
    }

    //检测广告创意是否被关联
    public function check_ad_by_idea($idea_id=0){
        $isExist = $this -> get_model('MaterielModel') -> check_ad_by_idea($idea_id);
        if($isExist && $isExist -> count() > 0){
            throw new \Exception('该创意已关联未开始或进行中的广告计划');
        }
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