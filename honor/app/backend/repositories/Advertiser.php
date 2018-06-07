<?php

/**
 * 广告主仓库
 * @category PhalconHonor
 * @author haoshisuo 2017-9-22
 */

namespace Marser\App\Backend\Repositories;

use \Marser\App\Backend\Repositories\BaseRepository;

class Advertiser extends BaseRepository{

    public function __construct(){
        parent::__construct();
    }

     /**
     * 广告主列表
     * @param int $status
     * @param array $ext
     * @return array
     * @throws \Exception
     */
    public function get_list($page, $pagesize=10, array $ext=array()){
        $result = $this -> get_model('AdvertiserModel') -> get_list($page, $pagesize, $ext);
        return $result;
    }

    //根据主键查询一条记录
    public function get_One($uid){
        $result = $this -> get_model('AdvertiserModel') -> get_One($uid);
        return $result;
    }

    //添加、编辑广告主操作
    public function save($map, $id = 0){
        $isExist = $this -> get_model('AdvertiserModel') -> check_exist(array('ad_name'=>$map['ad_name']), $id);
        if($isExist && $isExist -> count() > 0){
            throw new \Exception('广告主名称已存在');
        }
        $isExist2 = $this -> get_model('AdvertiserModel') -> check_exist(array('username'=>$map['username']), $id);
        if($isExist2 && $isExist2 -> count() > 0){
            throw new \Exception('用户名已存在');
        }
        $isExist3 = $this -> get_model('AdvertiserModel') -> check_exist(array('company_name'=>$map['company_name']), $id);
        if($isExist3 && $isExist3 -> count() > 0){
            throw new \Exception('公司名称已存在');
        }
        $id = intval($id);
        if(empty($id)){
            // 新增广告主
            $lastId = $this -> get_model('AdvertiserModel') -> insert_record($map);
            return $lastId;
        }else{
            // 更新广告主
            $affectedRows = $this -> get_model('AdvertiserModel') -> update_record($map, $id);
            $affectedRows = intval($affectedRows);
            return $affectedRows;
        }
    }

    //检测公司名称是否存在
    public function check_exist($map, $auid, $isAjax=0){
        $isExist = $this -> get_model('AdvertiserModel') -> check_exist($map, $auid);
        if($isExist && $isExist -> count() > 0){
            if(!empty($isAjax)){
                return 2;
            }else{
                throw new \Exception('公司名称已存在');
            }
        }
        return 1;
    }

}