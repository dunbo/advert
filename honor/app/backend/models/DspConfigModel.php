<?php

namespace Marser\App\Backend\Models;
use \Marser\App\Backend\Models\BaseModel;
use	\Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class DspConfigModel extends BaseModel{

    const TABLE_NAME = 'media_dsp_config';

    public function initialize(){
        $this -> set_table_source(self::TABLE_NAME);
        $this->setConnectionService('db2');  
        $this -> db = $this -> getDI() -> get('db2');
        self::setup(array(
            'notNullValidations' => false
        ));
    }


	 public function get_dsp_by_name($name){
        $params = array(
            'columns'  => 'id,dsp_name',
            'conditions' => "dsp_name like :name: and status = 1",
            'bind' => array(
                'name' => "%{$name}%",
            ),
        );
        $result = $this -> find($params)->toArray();
        return $result;
    }

    public function get_info($id){
        $result = $this -> find(array(
            'columns'       =>  '*',
            'conditions'    =>  'id = :id:',
            'bind' => array(
                'id'      =>  $id, 
            ),
        ));
        if(!$result){
            throw new \Exception('查询数据失败');
        }
        $list = $result -> toArray();
        return isset($list[0])?$list[0]:'';
    }

}