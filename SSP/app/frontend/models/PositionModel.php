<?php

namespace Marser\App\Frontend\Models;
use \Marser\App\Frontend\Models\BaseModel;
use	\Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class PositionModel extends BaseModel{

    const TABLE_NAME = 'media_ad_position';

    public function initialize(){
        parent::initialize();
        $this -> set_table_source(self::TABLE_NAME);
    }

    /**
     * 获取广告位列表
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
        $builder->columns('a.id,a.name,a.t_pid,a.t_id,a.examine_status,a.status,a.switch,a.update_tm,a.create_tm,b.name as media_name,b.mmid,b.type,b.tf_type,b.appkey,b.api_type,c.name as tmp_sub_name,c.size,e.name as tmp_parent_name,d.strategyid');
        $builder->from(array('a' => __CLASS__));
        $builder->leftJoin("\Marser\App\Frontend\Models\MediaModel", "a.mmid = b.mmid",'b');
        $builder->leftJoin("\Marser\App\Frontend\Models\TemplateModel", "a.t_id = c.id",'c');
        $builder->leftJoin("\Marser\App\Frontend\Models\StrategyModel", "a.strategyid = d.strategyid",'d');
        $builder->leftJoin("\Marser\App\Frontend\Models\TemplateModel", "a.t_pid = e.id",'e');
        if(isset($ext['id']) && !empty($ext['id'])) {
            $builder->andWhere("a.id = :id:", array('id' => $ext['id']));
        }
        if(isset($ext['muid']) && !empty($ext['muid'])) {
            $builder->andWhere("a.muid = :muid:", array('muid' => $ext['muid']));
        }
        if(isset($ext['switch'])) {
            $builder->andWhere("a.switch = :switch:", array('switch' => $ext['switch']));
        }
        if( isset($ext['aname']) && !empty($ext['aname']) ) {
            $builder->andWhere("a.name like :name:", array('name' => "%{$ext['aname']}%"));
        }
        if( isset($ext['mname']) && !empty($ext['mname']) ) {
            $builder->andWhere("b.name like :name:", array('name' => "%{$ext['mname']}%"));
        }
        $builder->andWhere("b.examine_status = 1 and  a.status = :status:", array('status' => $ext['status']));
        $builder->orderBy('a.id DESC');
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
    // public function get_position_by_id($id, $muid){
    //     if(empty($id) || empty($muid) ){
    //         throw new \Exception('参数错误');
    //     }
    //     $params = array(
    //         'columns' => '*',
    //         'conditions' => 'id = :id: and muid = :muid:',
    //         'bind' => array(
    //             'id'    =>  $id,
    //             'muid'  =>  $muid,
    //         ),
    //     );
    //     $result = $this -> find($params)->toArray();
    //     return $result[0];
    // }


    /**
     * 根据id获取信息
     */
    public function get_position_by_id($id, $muid){
        $builder = $this->getModelsManager()->createBuilder();
        $builder->columns('a.id,a.muid,a.name,a.mmid,a.t_pid,a.t_id,a.strategyid,a.code,a.appkey,a.examine_status,a.status,a.switch,a.update_tm,a.create_tm,a.bh_reason,a.bh_explain,b.tf_type,c.name as tmp_parent_name,d.name as tmp_sub_name,d.size');
        $builder->from(array('a' => __CLASS__));
        $builder->InnerJoin("\Marser\App\Frontend\Models\MediaModel", "a.mmid = b.mmid",'b');
        $builder->leftJoin("\Marser\App\Frontend\Models\TemplateModel", "a.t_pid = c.id",'c');
        $builder->leftJoin("\Marser\App\Frontend\Models\TemplateModel", "a.t_id = d.id",'d');
        $builder->andWhere("a.id = :id: and a.muid = :muid:", array('id' => $id, 'muid' => $muid));
        $result = $builder->getQuery()->execute()->toArray();
       return isset($result[0])?$result[0]:'';
    }

     /**
     * 根据屏蔽策略strategyid获取信息
     */
    public function get_position_by_strategyid($strategyid){
        if(empty($strategyid)){
            throw new \Exception('参数错误');
        }
        $params = array(
            'columns' => '*',
            'conditions' => 'strategyid = :strategyid: and status = 1',
            'bind' => array(
                'strategyid' => $strategyid,
            ),
        );
        $result = $this -> find($params)->toArray();
        return $result;
    }


     /**
     * 广告位数据入库
     * @param array $data
     * @return bool|int
     * @throws \Exception
     */
    public function insert_record(array $data) {
        if(count($data) == 0) {
            throw new \Exception('参数错误');
        }
        $data['update_tm'] = time();
        $data['create_tm'] = time();
        $clone	=	clone $this;
        $result	=	$clone -> create($data);
        if( !$result ) {
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
        $id = intval($id);
        if(count($data) == 0 || $id <= 0) {
            throw new \Exception('参数错误');
        }
        $data['update_tm'] = time();
        $this -> id = $id;
        $result = $this -> iupdate($data);
        if(!$result) {
            throw new \Exception(implode(',', $this -> getMessages()));
        }
        $affectedRows = $this -> db -> affectedRows();
        return $affectedRows;
    }

    /**
     * 广告位是否存在
     * @param null $name
     * @param null $mmid
     */
    public function position_is_exist($name,$muid,$id=0){
        if(empty($name)){
            throw new \Exception('参数错误');
        }
        $params = array();
        $params['conditions'] = "name = :name: and status =1 ";
        //$params['conditions'] = " and muid = :muid:";
        $params['bind']['name'] = $name;
        //$params['bind']['muid'] = $muid;
        $id = intval($id);
        $id > 0 && $params['conditions'] .= " and id != {$id} ";
        $result = $this -> find($params);
        return $result;
    }


}