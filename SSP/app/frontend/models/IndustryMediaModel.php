<?php

namespace Marser\App\Frontend\Models;
use \Marser\App\Frontend\Models\BaseModel;

class IndustryMediaModel extends BaseModel{

    const TABLE_NAME = 'media_industry';

    public function initialize(){
        parent::initialize();
        $this -> set_table_source(self::TABLE_NAME);
    }

    public function get_list($pid, array $ext=array()){
       $result = $this -> find(array(
            'columns'       =>  isset($ext['field']) && $ext['field']?$ext['field']:'*',
            'conditions'    =>  'parentid = :parentid:',
            'bind' => array(
                'parentid' => $pid
            )
        ));
        if(!$result){
            throw new \Exception('查询数据失败');
        }
        $list = $result -> toArray();
        return $list;
    }

    public function get_sub_list(array $ext=array()){
       $result = $this -> find(array(
            'columns'       =>  isset($ext['field']) && $ext['field']?$ext['field']:'*',
            'conditions'    =>  'parentid != :parentid:',
            'bind' => array(
                'parentid' => 0
            )
        ));
        if(!$result){
            throw new \Exception('查询数据失败');
        }
        $list = $result -> toArray();
        return $list;
    }

    public function get_sub_count($id)
    {
        $result = $this -> find(array(
            'columns'       =>  'count(*) as count',
            'conditions'    =>  'parentid = :parentid:',
            'bind' => array(
                'parentid' => $id
            )
        ));
        if(!$result){
            throw new \Exception('查询数据失败');
        }
        $list = $result -> toArray();
        return $list[0];
    }

    public function get_info($id){
        if(empty($id)){
            throw new \Exception('参数错误');
        }
        $params = array(
            'columns' => '*',
            'conditions' => 'id = :id:',
            'bind' => array(
                'id' => $id,
            ),
        );
        $result = $this -> find($params)->toArray();
        return $result[0];
    }
}