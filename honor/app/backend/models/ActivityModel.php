<?php

namespace Marser\App\Backend\Models;
use \Marser\App\Backend\Models\BaseModel;
use	\Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class ActivityModel extends BaseModel{

    const TABLE_NAME = 'media_activity';

    public function initialize(){
        parent::initialize();
        $this -> set_table_source(self::TABLE_NAME);
    }

    /**
     * 获取活动列表
     * @param int $page
     * @param array $ext
     * @return mixed
     * @throws \Exception
     */
    public function get_list($page, $pagesize=10, array $ext=array()) {
        $page = intval($page);
        $page <= 0 && $page = 1;
        $pagesize = intval($pagesize);

       	$builder = $this->getModelsManager()->createBuilder();
       	$builder->columns('*');
       	$builder->from( __CLASS__);
        if( isset($ext['name']) && !empty($ext['name'])) {
            $builder->andWhere("name like :name:", array('name' => "%{$ext['name']}%"));
        }
        if( isset($ext['type']) && !empty($ext['type'])) {
            $builder->andWhere("type = :type:", array('type' => $ext['type']));
        }
        if( isset($ext['status']) && !empty($ext['status'])) {
            $builder->andWhere("status = :status:", array('status' => $ext['status']));
        }

        $builder->orderBy('create_tm desc');

        $paginator = new PaginatorQueryBuilder(array(
            'builder'	=>	$builder,
            'limit'		=>	$pagesize,
            'page'		=>	$page,
        ));
        $result = $paginator->getPaginate();
        return $result;
    }

 	/**
     * 根据id获取信息
     */
    public function get_activity_info($id){
        if(empty($id)){
            throw new \Exception('参数错误');
        }
        $params = array(
            'columns' => '*',
            'conditions' => 'aid = :aid:',
            'bind' => array(
                'aid' => $id,
            ),
        );
        $result = $this -> find($params)->toArray();
        return $result[0];
    }

    public function get_activity_by_name($name){
        $params = array(
            'columns'  => 'aid,name',
            'conditions' => "name like :name: and status = 1 and end_tm > :time: ",
            'bind' => array(
                'name' => "%{$name}%",
                'time' =>  time(),
            ),
        );
        $result = $this -> find($params)->toArray();
        return $result;
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
        $aid = $clone -> aid;
        return $aid;
    }


    /**
     * 标签更新
     * @param array $data
     * @param $tid
     * @return int
     * @throws \Exception
     */
    public function update_record(array $data, $aid) {
        if(empty($aid)){
            throw new \Exception('参数错误');
        }
        $data['update_tm'] = time();
        $clone = clone $this;
        $clone -> aid = $aid;
        $result = $clone -> iupdate($data);
        if(!$result){
            throw new \Exception('更新失败');
        }
        $affectedRows = $clone -> db -> affectedRows();
        return $affectedRows;
    }


    /**
     * 活动是否存在
     * @param null $name
     * @param null $mmid
     */
    public function activity_is_exist($name, $aid=0){
        if(empty($name)){
            throw new \Exception('参数错误');
        }
        $params = array();
        $params['conditions'] 	= " name = :name: ";
        $params['bind']['name'] = $name;
        $aid = intval($aid);
        $aid > 0 && $params['conditions'] .= " AND aid != {$aid} ";
        $params['conditions'] .= " AND status != 0 ";	
        $result = $this -> find($params);
        return $result;
    }


}