<?php

namespace Marser\App\Backend\Models;
use \Marser\App\Backend\Models\BaseModel;
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
        $builder->columns('a.id,a.name,a.t_pid,a.t_id,a.examine_status,a.status,a.strategyid,a.switch,a.examine_status,a.update_tm,a.create_tm,b.name as media_name,b.type,b.tf_type,b.mmid,b.api_type,c.name as tmp_sub_name,c.size,e.name as tmp_parent_name');
        $builder->from(array('a' => __CLASS__));
        $builder->leftJoin("\Marser\App\Backend\Models\MediaModel", "a.mmid = b.mmid",'b');
        $builder->leftJoin("\Marser\App\Backend\Models\TemplateModel", "a.t_id = c.id",'c');
        $builder->leftJoin("\Marser\App\Backend\Models\TemplateModel", "a.t_pid = e.id",'e');

        if( isset($ext['muid']) && !empty($ext['muid']) ) {
             $builder->andWhere("a.muid = :muid:", array('muid' => $ext['muid']));
        }
        if( isset($ext['ad_name']) && !empty($ext['ad_name']) ) {
            $builder->andWhere("a.name like :name:", array('name' => "%{$ext['ad_name']}%"));
        }
        if( isset($ext['md_name']) && !empty($ext['md_name']) ) {
            $builder->andWhere("b.name like :name:", array('name' => "%{$ext['md_name']}%"));
        }
        if( isset($ext['srch_type']) ) {
            $builder->andWhere("a.examine_status = :examine_status:", array('examine_status' => $ext['srch_type']));
        }
        if( isset($ext['status']) && !empty($ext['status']) ) {
            $builder->andWhere("b.examine_status = 1 and a.status = :status:", array('status' => $ext['status']));
        }
        $builder->orderBy('a.id DESC');

        $paginator = new PaginatorQueryBuilder(array(
            'builder'	=>	$builder,
            'limit'		=>	$pagesize,
            'page'		=>	$page,
        ));
        $result = $paginator->getPaginate();
        return $result;
    }

    //获取某个媒体下的广告位
    public function get_list_by_mmid( $muid, $mmid, $ext=array() ) {
        $result = $this -> find(array(
            'columns'     =>  isset($ext['field']) && $ext['field']?$ext['field']:'*',
            'conditions'  =>  'muid = :muid: and mmid = :mmid: and examine_status = :examine_status: and status = :status:',
            'bind' => array(
                'muid'           => $muid,
                'mmid'           => $mmid,
                'examine_status' => 1,
                'status'         => 1,
            ),
            'order' => 'id desc',
        ));
        $list = $result -> toArray();
        return $list;
    }

    /**
     * 根据id获取信息
     */
    // public function get_position_by_id($id){
    //     if(empty($id)){
    //         throw new \Exception('参数错误');
    //     }
    //     $params = array(
    //         'columns' => '*',
    //         'conditions' => 'id = :id:',
    //         'bind' => array(
    //             'id' => $id,
    //         ),
    //     );
    //     $result = $this -> find($params)->toArray();
    //     return $result[0];
    // }

    /**
     * 根据id获取信息
     */
    public function get_position_by_id($id){
        $builder = $this->getModelsManager()->createBuilder();
        $builder->columns('a.id,a.muid,a.name,a.mmid,a.t_pid,a.t_id,a.strategyid,a.code,a.appkey,a.examine_status,a.status,a.switch,a.update_tm,a.create_tm,a.bh_reason,a.bh_explain,b.type,b.tf_type,b.ad_style,c.name as tmp_parent_name,d.name as tmp_sub_name,d.size');
        $builder->from(array('a' => __CLASS__));
        $builder->InnerJoin("\Marser\App\Backend\Models\MediaModel", "a.mmid = b.mmid",'b');
        $builder->leftJoin("\Marser\App\Backend\Models\TemplateModel", "a.t_pid = c.id",'c');
        $builder->leftJoin("\Marser\App\Backend\Models\TemplateModel", "a.t_id = d.id",'d');
        $builder->andWhere("a.id = :id:", array('id' => $id));
        $result = $builder->getQuery()->execute()->toArray();
       return isset($result[0])?$result[0]:'';
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
     * 广告位是否存在
     * @param null $name
     * @param null $mmid
     */
    public function position_is_exist($name, $id=0){
        if(empty($name)){
            throw new \Exception('参数错误');
        }
        $params = array();
        $params['conditions'] = " name = :name: and status =1 ";
        $params['bind']['name'] = $name;
        $id = intval($id);
        $id > 0 && $params['conditions'] .= " AND id != {$id} ";

        $result = $this -> find($params);
        return $result;
    }


    public function batch_update_record(array $data, $ids){
        if(count($data) == 0 || !$ids) {
            throw new \Exception('参数错误');
        }
        $sql = "update \Marser\App\Backend\Models\PositionModel set examine_status = ?1 "." where id in ( ?2 )";
        $result = $this->modelsManager->executeQuery($sql,array(1=>$data['status'], 2=>$ids));
        if ($result->success() === false) {
             throw new \Exception('更新失败');
        }else {
            return $result->success();
        }
    }

    public function get_detail($id){
        $sql  = "SELECT A.muid,A.name as aname,B.tf_type,B.name as mname, C.name as tname,C.size, D.name as sname ";
        $sql .= "FROM \Marser\App\Backend\Models\PositionModel as A LEFT JOIN \Marser\App\Backend\Models\MediaModel as B ON A.mmid = B.mmid LEFT JOIN \Marser\App\Backend\Models\TemplateModel as C ON A.t_id = C.id LEFT JOIN \Marser\App\Backend\Models\StrategyModel as D ON A.strategyid = D.strategyid WHERE A.id = ?1";
        $result = $this->modelsManager->executeQuery($sql,array(1=>$id))->toArray();
        return $result[0];
    }

    //批量导出
    public function batch_export($ids) {
        $sql = "SELECT a.id,a.name,a.examine_status,a.status,a.strategyid,a.switch,a.examine_status,a.update_tm,a.create_tm,b.name as media_name,b.mmid,b.type,b.package_name,b.flow,b.download_link,b.host,b.doc_key,b.intro,b.industry_parentid,b.industryid,b.tf_type,b.api_type,c.name as t_name,c.size,d.dsp_id FROM media_ad_position as a LEFT JOIN media_list as b ON a.mmid = b.mmid LEFT JOIN pu_template as c on a.t_id = c.id LEFT JOIN media_ad_rela as d on a.id = d.ad_pos_id LEFT JOIN media_list as e on a.mmid = b.mmid WHERE b.type = 1 AND a.examine_status = 0 AND a.id in ({$ids}) and a.`status` = 1  GROUP BY a.id";
        $result = $this->getDI()->get('db')->fetchAll($sql);
        return $result;
    }

    //已关联第三方dsp的处于待审核的广告位
    public function get_position_rela_dsp($name){
        $sql = "SELECT a.id,a.`name` FROM media_ad_position as a LEFT JOIN  media_ad_rela as b  on a.id = b.ad_pos_id  INNER JOIN media_list as c on a.mmid = c.mmid where a.`name` LIKE '%{$name}%' and  a.examine_status = 0 and a.`status` = 1 and c.type = 1 and ISNULL(b.ad_pos_id)";
        $result = $this->getDI()->get('db')->fetchAll($sql);
        return $result; 
    }

}