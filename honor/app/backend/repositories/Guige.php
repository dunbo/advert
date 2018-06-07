<?php

/**
 * 广告规格仓库
 * @category PhalconHonor
 * @author haoshisuo 2017-10-23
 */

namespace Marser\App\Backend\Repositories;

use \Marser\App\Backend\Repositories\BaseRepository;

class Guige extends BaseRepository{

    public function __construct(){
        parent::__construct();
    }

     /**
     * 广告规格列表
     * @param array $ext
     * @return array
     * @throws \Exception
     */
    public function get_list($page, $pagesize=10, array $ext=array()){
        $result = $this -> get_model('GuigeModel') -> get_list($page, $pagesize, $ext);
        return $result;
    }

    //根据主键查询一条记录
    public function get_One($uid){
        $result = $this -> get_model('GuigeModel') -> get_One($uid);
        return $result;
    }

    //添加、编辑广告规格操作
    public function save($map, $tid = 0){
        $tid = intval($tid);
        if(empty($tid)){
            // 新增广告规格
            $lastId = $this -> get_model('GuigeModel') -> insert_record($map);
            return $lastId;
        }else{
            // 更新广告规格
            $affectedRows = $this -> get_model('GuigeModel') -> update_record($map, $tid);
            $affectedRows = intval($affectedRows);
            return $affectedRows;
        }
    }

}