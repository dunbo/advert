<?php

namespace Marser\App\Frontend\Models;
use \Marser\App\Frontend\Models\BaseModel;
use	\Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class StrategyModel extends BaseModel{

    const TABLE_NAME = 'media_shield_strategy';

    public function initialize(){
        parent::initialize();
        $this -> set_table_source(self::TABLE_NAME);
    }

     /**
     * 获取屏蔽策略列表
     * @param int $page
     * @param array $ext
     * @return mixed
     * @throws \Exception
     */
    public function get_list($page, $pagesize=10, array $ext=array()){
        $page = intval($page);
        $page <= 0 && $page = 1;
        $pagesize = intval($pagesize);
       	$builder = $this->getModelsManager()->createBuilder();
       	$builder->columns(array('*'));
        $builder->from( __CLASS__);
       	$builder->where('status = :status:', array('status' => 1));
        if (isset($ext['muid']) && !empty($ext['muid'])) {
            $builder->andWhere("muid = :muid:", array('muid' => $ext['muid']));
        }
		if( isset($ext['keyword']) && !empty($ext['keyword']) ) {
            $builder->andWhere("name like :name:", array('name' => "%{$ext['keyword']}%"));
        }
        $builder->orderBy('strategyid DESC');
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
    public function get_strategy_by_id($id){
        if(empty($id)){
            throw new \Exception('参数错误');
        }
        $params = array(
            'columns' => '*',
            'conditions' => 'strategyid = :id:',
            'bind' => array(
                'id' => $id,
            ),
        );
        $result = $this -> find($params)->toArray();
        return $result[0];
    }
    /**
     * 有效的媒体
     * @param array $ext
     * @return array
     * @throws \Exception
     */
    public function get_all_list(array $ext=array()){
         $result = $this -> find(array(
            'columns'       =>  isset($ext['field']) && $ext['field']?$ext['field']:'*',
            'conditions'    =>  'muid = :muid: and status = :status:',
            'bind' => array(
                'muid'   => $ext['muid'],
                'status' => $ext['status']
            )
        ));
        if(!$result){
            throw new \Exception('查询数据失败');
        }
        $list = $result -> toArray();
        return $list;
    }


     /**
     * 屏蔽策略数据入库
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
        $id = $clone -> strategyid;
        return $id;
    }

    /**
     * 更新
     * @param array $data
     * @param $tid
     * @return int
     * @throws \Exception
     */
    public function update_record(array $data, $id) {
        $tid = intval($id);
        if(count($data) == 0 || $id <= 0) {
            throw new \Exception('参数错误');
        }
        $data['update_tm'] = time();
        $this -> strategyid = $id;
        $result = $this -> iupdate($data);
        if(!$result) {
            throw new \Exception(implode(',', $this -> getMessages()));
        }
        $affectedRows = $this -> db -> affectedRows();
        return $affectedRows;
    }


}