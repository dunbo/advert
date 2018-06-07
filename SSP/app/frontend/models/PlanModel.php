<?php

namespace Marser\App\Frontend\Models;
use \Marser\App\Frontend\Models\BaseModel;
use	\Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class PlanModel extends BaseModel{

    const TABLE_NAME = 'media_share_plan';

    public function initialize(){
        $this -> set_table_source(self::TABLE_NAME);
        $this->setConnectionService('db_pay');  
        $this -> db = $this -> getDI() -> get('db_pay');
        self::setup(array(
            'notNullValidations' => false
        ));
    }

    /**
     * 所有媒体
     * @param array $ext
     * @return array
     * @throws \Exception
     */
    public function get_list(array $ext=array()){
         $result = $this -> find(array(
            'columns'       =>  isset($ext['field']) && $ext['field']?$ext['field']:'*',
            'conditions'    =>  'status = :status:',
            'bind' => array(
                'status' => 1
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