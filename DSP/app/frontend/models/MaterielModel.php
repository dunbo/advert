<?php

/**
 * 广告计划模型
 * @category PhalconDSP
 * @author haoshisuo 2017-9-18
 */

namespace Marser\App\Frontend\Models;

use \Marser\App\Frontend\Models\BaseModel,
	\Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class MaterielModel extends BaseModel{

    const TABLE_NAME = 'ad_materiel';

    public function initialize(){
        parent::initialize();
        $this -> setSource(self::TABLE_NAME);
    }

    /**
     * 获取广告计划列表
     * @param int $page
     * @param array $ext
     * @return mixed
     * @throws \Exception
     */
    public function get_list($page, $pagesize=10, array $ext=array()){
        $page = intval($page);
        $page <= 0 && $page = 1;
        $pagesize = intval($pagesize);
        //($pagesize <= 0 || $pagesize > 20) && $pagesize = 10;
        $time = time();

        $builder = $this->getModelsManager()->createBuilder();
        $builder->columns('a.id,a.name,a.t_pid,a.t_id,a.day_budget,a.examine_status,a.switch,a.begin_tm,a.end_tm,a.idea_id,a.tf_nettype,a.tf_type,a.tf_area,a.tf_mobile,a.industry_parentid,a.industryid,a.price,a.api_type,a.tf_date_type,b.prize_name');
        $builder->from(array('a' => __CLASS__));
        $builder->leftJoin("\Marser\App\Frontend\Models\IdeaModel", "a.idea_id = b.id",'b');
        $builder->where('a.auid = :auid:', array('auid' => $ext['auid']));
        $builder->orderBy("a.id desc");
        if( isset($ext['examine_status']) ){
            if($ext['examine_status'] == 0) {
                $builder->andWhere("a.examine_status != :examine_status:", array('examine_status' => 0));
            }else{
                $builder->andWhere("a.examine_status = :examine_status:", array('examine_status' => $ext['examine_status']));
            }
        }
        if(isset($ext['switch']) && !empty($ext['switch'])){
            if($ext['switch'] == 4){
                $builder->andWhere("a.switch = 1 and a.examine_status=2");
                $builder->andWhere("a.begin_tm >= :begin_tm:", array('begin_tm' => $time));
            }
            if($ext['switch'] == 1){ //状态有效并且在有效时间范围内
                $builder->andWhere("a.switch = 1 and a.examine_status=2");
                $builder->andWhere(" (a.tf_date_type=1 and a.begin_tm < :begin_tm: ) or (a.tf_date_type=2 and a.begin_tm < :begin_tm: and a.end_tm > :begin_tm: ) ", array('begin_tm' => $time));
            }
            if($ext['switch'] == 2){//状态暂停并且在有效时间范围内
                $builder->andWhere("a.switch = 2 and a.examine_status=2");
                $builder->andWhere(" (a.tf_date_type=1 and a.begin_tm < :begin_tm: ) or (a.tf_date_type=2 and a.begin_tm < :begin_tm: and a.end_tm > :begin_tm: ) ", array('begin_tm' => $time));
            }
            if($ext['switch'] == 3){//状态有效并且时间不在范围内
                $builder->andWhere("a.switch=1 and a.examine_status=2 and (a.tf_date_type=2 and a.begin_tm < :begin_tm:) or (a.tf_date_type=2 and a.end_tm < :begin_tm: ) ", array('begin_tm' => $time));
            }
        }
        if(isset($ext['name']) && !empty($ext['name'])){
            $builder->andWhere("a.name like :name:", array('name' => "%{$ext['name']}%"));
        }
        $orderBy  = "(a.examine_status=1) desc,";
        $orderBy .= "(a.examine_status=3) desc,";
        $orderBy .= "a.examine_status=2 and a.switch = 1 and ((a.tf_date_type=1 and a.begin_tm < {$time} ) or (a.tf_date_type=2 and a.begin_tm < {$time} and a.end_tm > {$time} ))  desc,";
        $orderBy .= "a.examine_status=2 and a.switch =2  and ((a.tf_date_type=1 and a.begin_tm < {$time} ) or (a.tf_date_type=2 and a.begin_tm < {$time} and a.end_tm > {$time} ))  desc,";
        $orderBy .= "a.switch=1 and a.examine_status=2 and a.begin_tm >= {$time} desc,";
        $orderBy .= "a.switch=1 and a.examine_status=2 and  (a.tf_date_type=2 and a.end_tm < {$time} ) desc,";
        $orderBy .= "a.id desc";
        $builder->orderBy($orderBy);
        if( isset($ext['export']) ) {
             $result = $builder->getQuery()->execute()->toArray();
             return $result;
        }else {
            $paginator = new PaginatorQueryBuilder(array(
                'builder' => $builder,
                'limit' => $pagesize,
                'page' => $page,
            ));
            $result = $paginator->getPaginate();
            return $result;
        }
       
    }

    // 插入广告计划
    public function insert_record(array $data){
        if(!is_array($data) || count($data) == 0){
            throw new \Exception('参数错误');
        }
        $data['create_tm'] = time();
        $data['update_tm'] = time();
        $clone = clone $this;
        $result = $clone -> create($data);
        if(!$result){
            throw new \Exception(implode(',', $this -> getMessages()));
        }
        $id = $clone -> id;
        return $id;
    }

    /**
     * 更新记录
     * @param array $data
     * @param $aid
     * @return int
     * @throws \Exception
     */
    public function update_record(array $data, $id){
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

    //循环更新调用此方法iupdate存在循环更新只更细第一条存在bug
    public function  update_record_ext(array $data, $id){
        $id = intval($id);
        if(count($data) == 0 || $id <= 0) {
            throw new \Exception('参数错误');
        }
        $data['update_tm'] = time();
        $map = '';
        foreach ($data as $k => $val) {
            if(gettype($val) == 'string') {
                $map .= $k."='".$val."',";
            }else {
                $map .= $k."=".$val.",";
            }
        }
        $map = trim($map, ',');
        $sql = "update \Marser\App\Frontend\Models\MaterielModel set {$map} where id in ( ?1 )";
        $result = $this->modelsManager->executeQuery($sql,array(1=>$id));
        if ($result->success() === false) {
             throw new \Exception('更新失败');
        }else {
            return $result->success();
        }

    }


    // 根据id查询一条记录
    public function get_info($id){
        $id = intval($id);
        $sql = "SELECT A.*,B.`name` as tmp_parent_name,C.`name` as tmp_sub_name,C.size FROM ad_materiel as A LEFT JOIN pu_template as B on A.t_pid = B.id LEFT JOIN pu_template as C on A.t_id = C.id where A.id = {$id}";
        $result = $this->getDI()->get('db')->fetchOne($sql);
        return $result;
    }

    /**
     * 检测广告计划名称是否存在
     * @param null $name
     * @param null $mmid
     */
    public function check_exist($ext, $id=0){
        if(!is_array($ext) || count($ext) == 0){
            throw new \Exception('参数错误');
        }
        $params['conditions'] = '2>1';
        if(!empty($ext['name'])){
            $params['conditions'] .= " AND name = :name:";
            $params['bind']['name'] = $ext['name'];
        }
        $id = intval($id);
        $id > 0 && $params['conditions'] .= " and id != {$id} ";
        $result = $this -> find($params);
        return $result;
    }

    //广告计划统计
    public function get_status_count($auid){
        $time = time();
        $sql_sh  = "SELECT count(*) as count FROM ad_materiel where auid = {$auid} and examine_status=1";
        $sql_ntg = "SELECT count(*) as count FROM ad_materiel where auid = {$auid} and examine_status=3";
        $sql_exe = "SELECT count(*) as count FROM ad_materiel where auid = {$auid} and examine_status =2 and switch=1 and ((tf_date_type=1 and {$time} >= begin_tm ) or (tf_date_type=2 and begin_tm<{$time} and {$time} < end_tm))";  
        $ret_sh  = $this->getDI()->get('db')->fetchOne($sql_sh);
        $ret_ntg = $this->getDI()->get('db')->fetchOne($sql_ntg);
        $ret_exe = $this->getDI()->get('db')->fetchOne($sql_exe);
        $result = array(
            '1'=> $ret_sh['count'], //待审核
            '2'=> $ret_ntg['count'],//未通过
            '3'=> $ret_exe['count'],//投放中
        );
        return $result;
    }

    //批量提取
    public function batch_data($ids){
        $sql = "select * from ad_materiel where id in({$ids}) ";
        return $this->getDI()->get('db')->fetchAll($sql);
    }
    
}