<?php

/**
 * 广告数据模型
 * @category PhalconHonor
 * @author haoshisuo 2017-9-22
 */

namespace Marser\App\Backend\Models;
use \Marser\App\Backend\Models\BaseModel;
use	\Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class DataModel extends BaseModel{

    const TABLE_NAME = 'ad_statistics';

    public function initialize(){
        parent::initialize();
        $this -> setSource(self::TABLE_NAME);
    }

    // 获取广告数据列表
    public function get_list($page, $pagesize=10, array $ext=array()){
        $page = intval($page);
        $page <= 0 && $page = 1;
        $pagesize = intval($pagesize);
        $time = time();

        $builder = $this->getModelsManager()->createBuilder();
        $builder->from(array('a' => __CLASS__));
        $builder->columns('a.id,a.ad_name,a.client_name,a.create_date,a.exposure,a.pv,a.click_num,a.click_price,a.total');
        if(isset($ext['materiel_name']) && !empty($ext['materiel_name'])){
            $builder->andWhere("a.ad_name like :materiel_name:", array('materiel_name' => "%{$ext['materiel_name']}%"));
        }
        if(isset($ext['ad_name']) && !empty($ext['ad_name'])){
            $builder->andWhere("a.client_name like :ad_name:", array('ad_name' => "%{$ext['ad_name']}%"));
        }
    	if(isset($ext['begin_tm']) && !empty($ext['begin_tm'])){
            $begin_tm = strtotime($ext['begin_tm']);
	    	$builder->andWhere(":begin_tm: <= UNIX_TIMESTAMP(a.create_date)", array('begin_tm'=>$begin_tm));
	    }
	    if(isset($ext['end_tm']) && !empty($ext['end_tm'])){
            $end_tm = strtotime($ext['end_tm']);
	    	$builder->andWhere(":end_tm: >= UNIX_TIMESTAMP(a.create_date)", array('end_tm'=>$end_tm));
	    }

        $paginator = new PaginatorQueryBuilder(array(
            'builder' => $builder,
            'limit' => $pagesize,
            'page' => $page,
        ));
        $result = $paginator->getPaginate();
        return $result;
    }
}