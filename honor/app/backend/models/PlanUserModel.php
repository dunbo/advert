<?php

namespace Marser\App\Backend\Models;
use \Marser\App\Backend\Models\BaseModel;
use	\Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class PlanUserModel extends BaseModel{

    const TABLE_NAME = 'media_user_share_plan';

    public function initialize(){
        $this -> set_table_source(self::TABLE_NAME);
        $this->setConnectionService('db2');  
        $this -> db = $this -> getDI() -> get('db2');
        self::setup(array(
            'notNullValidations' => false
        ));
    }

    public function get_info($muid){
        $result = $this -> find(array(
            'columns'       =>  '*',
            'conditions'    =>  'muid = :muid: and status = :status:',
            'bind' => array(
                'muid'      =>  $muid, 
                'status'    =>  1,
            ),
            'order' =>  'id desc',
        ));
        if(!$result){
            throw new \Exception('查询数据失败');
        }
        $list = $result -> toArray();
        return isset($list[0])?$list[0]:'';
    }

     /**
     * 媒体数据入库
     * @param array $data
     * @return bool|int
     * @throws \Exception
     */
    public function insert_record(array $data){
        if(count($data) == 0){
            throw new \Exception('参数错误');
        }
        $data['create_tm'] = time();
        $clone = clone $this;
        $result = $clone -> create($data);
        if(!$result){
            throw new \Exception(implode(',', $this -> getMessages()));
        }
        $id = $clone -> id;
        return $id;
    }


    /**
     * 标签更新
     * @param array $data
     * @param $tid
     * @return int
     * @throws \Exception
     */
    public function update_record(array $data, $id) {
        if(empty($id)){
            throw new \Exception('参数错误');
        }
        $data['update_tm'] = time();
        $clone = clone $this;
        $clone -> id = $id;
        $result = $clone -> iupdate($data);
        if(!$result){
            throw new \Exception('更新失败');
        }
        $affectedRows = $clone -> db -> affectedRows();
        return $affectedRows;
    }


}