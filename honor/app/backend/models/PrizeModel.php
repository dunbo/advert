<?php

namespace Marser\App\Backend\Models;
use \Marser\App\Backend\Models\BaseModel;
use	\Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class PrizeModel extends BaseModel{

    const TABLE_NAME = 'media_prize';

    public function initialize(){
        parent::initialize();
        $this -> set_table_source(self::TABLE_NAME);
    }

    /**
     * 获取奖品列表
     * @param int $page
     * @param array $ext
     * @return mixed
     * @throws \Exception
     */
    public function get_list($aid) {
        $params = array(
            'columns' => '*',
            'conditions' => 'aid = :aid: and status != 0 ',
            'bind' => array(
                'aid' => $aid,
            ),
            'order' =>  'id asc',
        );
        $result = $this -> find($params)->toArray();
        return $result;
    }

    public function get_list_other($aid, $id){
        $params = array(
            'columns' => '*',
        );
        if( $id ) {
           $params['conditions'] = 'aid = :aid: and id != :id: and status != 0 ';
           $params['bind'] = array('aid' => $aid,'id' => $id);
        }else {
           $params['conditions'] = 'aid = :aid: and status != 0 ';
           $params['bind'] = array('aid' => $aid);
        }
        $result = $this -> find($params)->toArray();
        return $result;
    }

    public function get_count( $aid ){
        $params = array(
            'columns' => 'count(*) count',
            'conditions' => 'aid = :aid: and status != 0 ',
            'bind' => array(
                'aid' => $aid,
            ),
        );
        $result = $this -> find($params)->toArray();
        return isset($result[0])?$result[0]['count']:0;
    }

 	/**
     * 根据id获取信息
     */
    public function get_prize_info($id){
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
        $data['update_tm'] = time();
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


    /**
     * 奖品是否存在
     * @param null $name
     * @param null $mmid
     */
    public function Prize_is_exist($name, $id=0){
        if(empty($name)){
            throw new \Exception('参数错误');
        }
        $params = array();
        $params['conditions'] 	= " name = :name: ";
        $params['bind']['name'] = $name;
        $id = intval($id);
        $id > 0 && $params['conditions'] .= " AND id != {$id} ";
        $params['conditions'] .= " AND status != 0 ";	
        $result = $this -> find($params);
        return $result;
    }


}