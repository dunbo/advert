<?php

/**
 * 广告计划仓库
 * @category PhalconHonor
 * @author haoshisuo 2017-9-25
 */

namespace Marser\App\Backend\Repositories;

use \Marser\App\Backend\Repositories\BaseRepository;

class Materiel extends BaseRepository{

    public function __construct(){
        parent::__construct();
    }

    //广告计划列表
    public function get_list($page, $pagesize=10, array $ext=array()){
        $list = $this -> get_model('MaterielModel') -> get_list($page, $pagesize, $ext);
        return $list;
    }
    
     //添加、编辑广告操作
    public function save($map, $id=null){
        $id = intval($id);
        if(empty($id)){
            // 新增广告
            $lastId = $this -> get_model('MaterielModel') -> insert_record($map);
            return $lastId;
        }else{
            // 更新广告
            $affectedRows = $this -> get_model('MaterielModel') -> update_record($map, $id);
            $affectedRows = intval($affectedRows);
            return $affectedRows;
        }
    }

    //批量通过、驳回操作
    public function examine($ext, $ids){
    	$id_arr = explode(',', $ids);
        if(!empty($ext['ad_industry_parentid']) && !empty($ext['ad_industryid'])){
            $industry_pid_arr = explode(',', $ext['ad_industry_parentid']);
            $industryid_arr = explode(',', $ext['ad_industryid']);
        }
    	if(count($id_arr) == 1){
    		$affectedRows = $this -> get_model('MaterielModel') -> update_record($ext, $ids);
    		$affectedRows = intval($affectedRows);
        	return $affectedRows;
    	}else if(count($id_arr) > 1){
            $return = 0;
    		foreach($id_arr as $k => $id){
                if(!empty($ext['ad_industry_parentid']) && !empty($ext['ad_industryid'])){
                    $ext['ad_industry_parentid'] = $industry_pid_arr[$k];
                    $ext['ad_industryid'] = $industryid_arr[$k];
                }
    			$affectedRows = $this -> get_model('MaterielModel') -> update_record($ext, $id);
                $affectedRows = intval($affectedRows);
                $return += $affectedRows;
            }
            return $return;
    	}	
    }

    //审核通过校验是否添加标签
    public function check_has_tags($ids){
        $result = $this -> get_model('MaterielModel') -> select_tags($ids);
        foreach ($result as $val) {
            if(empty($val['activetag_sp'])){
                throw new \Exception('添加标签后才可以审核通过');
            }
        }
    }

    //获取一条信息
    public function get_info($id){
        return $this -> get_model('MaterielModel') -> get_info($id);
    }
}