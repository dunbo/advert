<?php

namespace Marser\App\Backend\Models;
use \Marser\App\Backend\Models\BaseModel;
use	\Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class MediaActivityModel extends BaseModel{

    const TABLE_NAME = 'media_shield_activity';

    public function initialize(){
        parent::initialize();
        $this -> set_table_source(self::TABLE_NAME);
    }

    /**
     * 获取媒体活动列表
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
       	$builder->columns('a.id,a.mmid,a.muid,a.ad_id,a.area_value,a.probability,a.start_tm,a.end_tm,a.create_tm,b.name');
        $builder->from(array('a' => __CLASS__));
        $builder->leftJoin("\Marser\App\Backend\Models\ActivityModel", "a.aid = b.aid",'b');
		if( isset($ext['muid']) ) {
            $builder->andWhere("a.muid = :muid:", array('muid' => $ext['muid']));
        }
        if( isset($ext['mmid']) ) {
            $builder->andWhere("a.mmid = :mmid:", array('mmid' => $ext['mmid']));
        }
        if( isset($ext['ad_id']) ) {
            $builder->andWhere("a.ad_id = :ad_id:", array('ad_id' => $ext['ad_id']));
        }
        if( isset($ext['srch_type']) && !empty($ext['srch_type'])) {
            $time = time();
            if($ext['srch_type'] == 'exp'){
            	//过期
            	$builder->andWhere(" {$time} > a.end_tm ");
            }elseif( $ext['srch_type'] == 'cur' ) {
            	//进行中
            	$builder->andWhere(" {$time} >= a.start_tm and {$time} < a.end_tm ");
            }elseif( $ext['srch_type'] == 'not' ) {
            	//未开始
            	$builder->andWhere(" {$time} < a.start_tm ");
            }
        }
        $builder->andWhere("a.status = 1");
        $builder->orderBy('a.create_tm desc');
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
    public function get_info($id){
        $builder = $this->getModelsManager()->createBuilder();
        $builder->columns('a.id,a.mmid,a.muid,a.ad_id,a.aid,a.area_value,a.probability,a.start_tm,a.end_tm,a.create_tm,b.name');
        $builder->from(array('a' => __CLASS__));
        $builder->leftJoin("\Marser\App\Backend\Models\ActivityModel", "a.aid = b.aid",'b');
        $builder->andWhere("a.id = :id:", array('id' => $id));
        $result = $builder->getQuery()->execute()->toArray();
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
        $aid = $clone -> id;
        return $aid;
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