<?php

/**
 * 广告位形式模型
 */

namespace Marser\App\Frontend\Models;
use \Marser\App\Frontend\Models\BaseModel;

class TemplateModel extends BaseModel{

    const TABLE_NAME = 'pu_template';

    public function initialize(){
        parent::initialize();
        $this -> setSource(self::TABLE_NAME);
    }

    /**
     * 广告规格列表
     * @param array $ext
     * @return array
     * @throws \Exception
     */
    public function get_list(array $ext=array()){
        if(!empty($ext['id'])){
            $param['conditions'] = "id = {$ext['id']} ";
        }else {
            $param['conditions'] = "status = 1";
        }
        $result = $this -> find($param);
        if(!$result){
            throw new \Exception('查询数据失败');
        }
        $list = $result -> toArray();
        return $list;
    }

    public function get_parent_list(){
        $conditions = 'parentid = :parentid: and status = 1';
         $result = $this -> find(array(
            'columns'       =>  '*',
            'conditions'    =>  $conditions,
            'bind' => array(
                'parentid'   => 0,
            )
        ));
        if(!$result){
            throw new \Exception('查询数据失败');
        }
        $list = $result -> toArray();
        return $list;
    }

    public function get_sub_list(){
        $result = $this -> find(array(
            'columns'       =>  '*',
            'conditions'    =>  'parentid != :parentid: and status = 1',
            'bind' => array(
                'parentid'   => 0,
            )
        ));
        if(!$result){
            throw new \Exception('查询数据失败');
        }
        $list = $result -> toArray();
        return $list;
    }

    public function get_info($id){
        $result = $this -> find(array(
            'columns'       =>  '*',
            'conditions'    =>  'id = :id: and status = 1',
            'bind' => array(
                'id' => $id,
            )
        ));
        $list = $result -> toArray();
        return isset($list[0])?$list[0]:'';
    }
}