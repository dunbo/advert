<?php

namespace Marser\App\Frontend\Models;
use \Marser\App\Frontend\Models\BaseModel;
use	\Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class MediaModel extends BaseModel{

    const TABLE_NAME = 'media_list';

    public function initialize(){
        parent::initialize();
        $this -> set_table_source(self::TABLE_NAME);
    }

    /**
     * 获取媒体列表
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
        $builder->from( __CLASS__);
       	$builder->columns(array('*'));
        if(isset($ext['muid']) && !empty($ext['muid'])) {
            $builder->andWhere("muid = :muid:", array('muid' => $ext['muid']));
		}
        if(isset($ext['mmid']) && !empty($ext['mmid'])) {
            $builder->andWhere("mmid = :mmid:", array('mmid' => $ext['mmid']));
        }
        if( isset($ext['keyword']) && !empty($ext['keyword']) ) {
            $builder->andWhere("name like :name:", array('name' => "%{$ext['keyword']}%"));
        }
        $builder->orderBy('mmid DESC');
        $paginator = new PaginatorQueryBuilder(array(
            'builder'   =>  $builder,
            'limit'     =>  $pagesize,
            'page'      =>  $page,
        ));
        $result = $paginator->getPaginate();
        return $result;
    }

    /**
     * 所有媒体
     * @param array $ext
     * @return array
     * @throws \Exception
     */
    public function get_all_list(array $ext=array()){
         $result = $this -> find(array(
            'columns'       =>  isset($ext['field']) && $ext['field']?$ext['field']:'*',
            'conditions'    =>  'muid = :muid: and examine_status = :examine_status:',
            'bind' => array(
                'muid'              =>  $ext['muid'],
                'examine_status'    =>  1,
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
    public function get_media_by_id($mmid, $muid){
        if(empty($mmid) || empty($muid) ){
            throw new \Exception('参数错误');
        }
        $params = array(
            'columns' => '*',
            'conditions' => 'mmid = :mmid: and muid = :muid:',
            'bind' => array(
                'mmid'  =>  $mmid,
                'muid'  =>  $muid,
            ),
        );
        $result = $this -> find($params)->toArray();
        return isset($result[0])?$result[0]:array();
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
        $mmid = $clone -> mmid;
        return $mmid;
    }


    /**
     * 标签更新
     * @param array $data
     * @param $tid
     * @return int
     * @throws \Exception
     */
    public function update_record(array $data, $mmid) {
        $mmid = intval($mmid);
        if(count($data) == 0 || $mmid <= 0) {
            throw new \Exception('参数错误');
        }
        $data['update_tm'] = time();
        $this -> mmid = $mmid;
        $result = $this -> iupdate($data);
        if(!$result) {
            throw new \Exception(implode(',', $this -> getMessages()));
        }
        $affectedRows = $this -> db -> affectedRows();
        return $affectedRows;
    }


    /**
     * 媒体是否存在
     * @param null $name
     * @param null $mmid
     */
    public function media_is_exist($name,$muid,$mmid=0){
        if(empty($name)){
            throw new \Exception('参数错误');
        }
        $params = array();
        $params['conditions'] = " name = :name: ";
        //$params['conditions'] = " and muid = :muid:";
        $params['bind']['name'] = $name;
        //$params['bind']['muid'] = $muid;
        $mmid = intval($mmid);
        $mmid > 0 && $params['conditions'] .= " and mmid != {$mmid} ";
        $result = $this -> find($params);
        return $result;
    }


}