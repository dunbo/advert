<?php

/**
 * 财务记录模型
 * @category PhalconDSP
 * @author haoshisuo 2017-9-26
 */

namespace Marser\App\Frontend\Models;
use \Marser\App\Frontend\Models\BaseModel,
	\Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class RechargeModel extends BaseModel{

    const TABLE_NAME = 'ad_user_recharge';

    public function initialize(){
        parent::initialize();
        $this->setConnectionService('db2');  
        $this -> setSource(self::TABLE_NAME);
    }

    // 获取充值记录列表
    public function get_list($page, $pagesize=10, array $ext=array()){
        $page = intval($page);
        $page <= 0 && $page = 1;
        $pagesize = intval($pagesize);
        ($pagesize <= 0 || $pagesize > 20) && $pagesize = 10;

        $builder = $this->getModelsManager()->createBuilder();
        $builder->from(array('a' => __CLASS__));
        $builder->columns('a.id,a.recharge_time,a.cash_amount,a.rebate_amount');
        $builder->where('a.auid = :auid:', array('auid' => $ext['auid']));
        $builder->andWhere("a.audit_status = 1");
        $builder->andWhere("a.recharge_status = 1");
    	if(isset($ext['begin_tm'])&&!empty($ext['begin_tm'])){
	    	$builder->andWhere(":begin_tm: <= UNIX_TIMESTAMP(a.recharge_time)", array('begin_tm'=>$ext['begin_tm']));
	    }
	    if(isset($ext['end_tm'])&&!empty($ext['end_tm'])){
	    	$builder->andWhere(":end_tm: >= UNIX_TIMESTAMP(a.recharge_time)", array('end_tm'=>$ext['end_tm']));
	    }
        $builder->orderBy('id desc');
        $paginator = new PaginatorQueryBuilder(array(
            'builder' => $builder,
            'limit' => $pagesize,
            'page' => $page,
        ));
        $result = $paginator->getPaginate();
        return $result;
    }
}