<?php

/**
 * 广告创意模型
 * @category PhalconHonor
 * @author haoshisuo 2017-10-19
 */

namespace Marser\App\Backend\Models;
use \Marser\App\Backend\Models\BaseModel;
use	\Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class IdeaModel extends BaseModel{

    const TABLE_NAME = 'ad_idea';

    public function initialize(){
        parent::initialize();
        $this -> setSource(self::TABLE_NAME);
    }

    //获取广告创意列表
    public function get_list($page, $pagesize=10, array $ext=array()){
        $page = intval($page);
        $page <= 0 && $page = 1;
        $pagesize = intval($pagesize);

       	$builder = $this->getModelsManager()->createBuilder();
        $builder->columns('a.id,a.prize_name,a.ad_image,a.ad_image2,a.coupon_term,a.push_link,a.bh_reason,a.bh_explain,b.ad_name');
        $builder->from(array('a' => __CLASS__));
        $builder->leftJoin("\Marser\App\Backend\Models\AdvertiserModel", "a.auid = b.auid",'b');
        $builder->where('status = 1');
        if( isset($ext['ad_name']) && !empty($ext['ad_name']) ) {
            $builder->andWhere("b.ad_name like :ad_name:", array('ad_name' => "%{$ext['ad_name']}%"));
        }
        if( isset($ext['prize_name']) && !empty($ext['prize_name']) ) {
            $builder->andWhere("a.prize_name like :prize_name:", array('prize_name' => "%{$ext['prize_name']}%"));
        }
        if( isset($ext['srch_type']) && !empty($ext['srch_type']) ) {
            $builder->andWhere("a.examine_status = :examine_status:", array('examine_status' => $ext['srch_type']));
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

    // 根据id查询一条记录
    public function get_One($id){
        $result = $this -> find(array(
            'conditions' => 'id = :id:',
            'bind' => array(
                'id' => $id,
            ),
        ));
        if(empty($result[0])){
            throw new \Exception('未知错误');
        }
        return $result[0];
    }

    /**
     * 检测奖品名称是否存在
     * @param null $name
     * @param null $mmid
     */
    public function check_exist($ext, $id=0){
        if(!is_array($ext) || count($ext) == 0){
            throw new \Exception('参数错误');
        }
        $params['conditions'] = 'status = 1';
        if(!empty($ext['auid'])){
            $params['conditions'] .= " AND auid = :auid:";
            $params['bind']['auid'] = $ext['auid'];
        }
        if(!empty($ext['prize_name'])){
            $params['conditions'] .= " AND prize_name = :prize_name:";
            $params['bind']['prize_name'] = $ext['prize_name'];
        }
        $id = intval($id);
        $id > 0 && $params['conditions'] .= " and id != {$id} ";
        $result = $this -> find($params);
        return $result;
    }
}