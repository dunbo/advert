<?php

/**
 * 财务记录模型
 * @category PhalconDSP
 * @author haoshisuo 2017-9-26
 */

namespace Marser\App\Frontend\Models;
use \Marser\App\Frontend\Models\BaseModel,
	\Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class AuditModel extends BaseModel{

    const TABLE_NAME = 'daily_au_financial_audit';

    public function initialize(){
        parent::initialize();
        $this -> setConnectionService('db2');  
        $this -> setSource(self::TABLE_NAME);
    }

    // 获取消费记录列表
    public function get_list($page, $pagesize=10, array $ext=array()){
        $page = intval($page);
        $page <= 0 && $page = 1;
        $pagesize = intval($pagesize);
        ($pagesize <= 0 || $pagesize > 20) && $pagesize = 10;

        $builder = $this->getModelsManager()->createBuilder();
        $builder->from(array('a' => __CLASS__));
        $builder->columns('a.id,a.theday,a.cost');
        $builder->where('a.auid = :auid:', array('auid' => $ext['auid']));
    	if(isset($ext['begin_tm']) && !empty($ext['begin_tm'])){
            $begin_tm = date('Ymd', $ext['begin_tm']);
	    	$builder->andWhere(":begin_tm: <= a.theday", array('begin_tm'=>$begin_tm));
	    }
	    if(isset($ext['end_tm']) && !empty($ext['end_tm'])){
            $end_tm = date('Ymd', $ext['end_tm']);
	    	$builder->andWhere(":end_tm: >= a.theday", array('end_tm'=>$end_tm));
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