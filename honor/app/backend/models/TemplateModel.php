<?php

namespace Marser\App\Backend\Models;
use \Marser\App\Backend\Models\BaseModel;

class TemplateModel extends BaseModel{

    const TABLE_NAME = 'pu_template';

    public function initialize(){
        parent::initialize();
        $this -> set_table_source(self::TABLE_NAME);
    }

    public function get_list(array $ext=array()){
        if(!empty($ext['id'])){
            $param['conditions'] = "id = {$ext['id']} and  status = 1";
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
         $result = $this -> find(array(
            'columns'       =>  '*',
            'conditions'    =>  'parentid = :parentid: ',
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

    /**
     * 根据id获取信息
     */
    public function get_info($id){
        $params = array(
            'columns' => '*',
            'conditions' => 'id = :id:',
            'bind' => array(
                'id' => $id,
            ),
        );
        $result = $this -> find($params)->toArray();
        return isset($result[0])?$result[0]:'';
    }


}