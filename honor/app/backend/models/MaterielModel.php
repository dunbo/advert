<?php

/**
 * 广告计划模型
 * @category PhalconHonor
 * @author haoshisuo 2017-9-25
 */

namespace Marser\App\Backend\Models;
use \Marser\App\Backend\Models\BaseModel;
use	\Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class MaterielModel extends BaseModel{

    const TABLE_NAME = 'ad_materiel';

    public function initialize(){
        parent::initialize();
        $this -> setSource(self::TABLE_NAME);
    }

    //获取广告计划列表
    public function get_list($page, $pagesize=10, array $ext=array()){
        $page = intval($page);
        $page <= 0 && $page = 1;
        $pagesize = intval($pagesize);

       	$builder = $this->getModelsManager()->createBuilder();
        $builder->columns('a.id,a.api_type,a.t_pid,a.t_id,a.name,a.price,a.day_budget,a.begin_tm,a.end_tm,a.idea_id,a.tf_nettype,a.tf_type,a.tf_area,a.tf_mobile,a.industry_parentid,a.industryid,a.activetag_sp,a.ad_industry_parentid,a.ad_industryid,a.copywriter_img,a.brand_img,a.copywriter_desc,a.brand_desc,a.url,b.ad_name,c.name as t_name,c.size');
        $builder->from(array('a' => __CLASS__));
        $builder->leftJoin("\Marser\App\Backend\Models\AdvertiserModel", "a.auid = b.auid",'b');
        $builder->leftJoin("\Marser\App\Backend\Models\TemplateModel", "a.t_id = c.id",'c');
        if( isset($ext['ad_name']) && !empty($ext['ad_name']) ) {
            $builder->andWhere("b.ad_name like :ad_name:", array('ad_name' => "%{$ext['ad_name']}%"));
        }
        if( isset($ext['materiel_name']) && !empty($ext['materiel_name']) ) {
            $builder->andWhere("a.name like :name:", array('name' => "%{$ext['materiel_name']}%"));
        }
        if( isset($ext['srch_type']) ) {
            $builder->andWhere("a.examine_status = :examine_status:", array('examine_status' => $ext['srch_type']));
        }
        if( isset($ext['auid']) && !empty($ext['auid']) ) {
            $builder->andWhere("a.auid = :auid:", array('auid' => $ext['auid']));
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

    //更新记录操作，支持批量
    public function update_record(array $data, $id){
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
     * 检测广告创意是否被关联
     * @param null $name
     * @param null $mmid
     */
    public function check_ad_by_idea($idea_id=0){
        $time = time();
        $params['conditions'] = "examine_status = 2 and switch != 3 and {$time} < end_tm";
        if(!empty($idea_id)){
            $params['conditions'] .= " AND idea_id = :idea_id:";
            $params['bind']['idea_id'] = $idea_id;
        }
        $result = $this -> find($params);
        return $result;
    }

    public function select_tags($ids){
        $result = $this->find(array(
            'columns' => 'activetag_sp',
            'conditions' => "id in ({ids:array})",
            'bind' => array('ids'=>explode(',', $ids)),
        ));
        return $result->toArray();
    }

    // 根据id查询一条记录
    public function get_info($id){
        $id = intval($id);
        $sql = "SELECT A.*,B.`name` as tmp_parent_name,C.`name` as tmp_sub_name,C.size FROM ad_materiel as A LEFT JOIN pu_template as B on A.t_pid = B.id LEFT JOIN pu_template as C on A.t_id = C.id where A.id = {$id}";
        $result = $this->getDI()->get('db')->fetchOne($sql);
        return $result;
    }
}