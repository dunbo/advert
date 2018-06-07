<?php

namespace Marser\App\Frontend\Models;
use \Marser\App\Frontend\Models\BaseModel;
use	\Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class SettlementModel extends BaseModel{

    const TABLE_NAME = 'media_settlement_month';

    public function initialize(){
        $this -> set_table_source_pay(self::TABLE_NAME);
        $this->setConnectionService('db_pay');  
        $this -> db = $this -> getDI() -> get('db_pay');
        self::setup(array(
            'notNullValidations' => false
        ));
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
        $builder->orderBy('id DESC');
        $paginator = new PaginatorQueryBuilder(array(
            'builder'   =>  $builder,
            'limit'     =>  $pagesize,
            'page'      =>  $page,
        ));
        $result = $paginator->getPaginate();
        return $result;
    }

    //账户余额
    public function get_account_mount($muid){
         $result = $this -> find(array(
            'columns'       =>  'sum(expected_profits_total) as mount',
            'conditions'    =>  ' muid = :muid: and audit_status in (:audit_status:)',
            'bind' => array(
            	'muid'			=>	$muid,
                'audit_status' 	=> '1,2',
            )
        ));
        if(!$result){
            throw new \Exception('查询数据失败');
        }
        $list = $result -> toArray();
        return $list[0];
    }

     //可提现金额以及结算账单数
    public function get_amount_in_cash($muid){
         $result = $this -> find(array(
            'columns'       =>  'sum(settlement_amount_no_tax) as mount, count(*) as count',
            'conditions'    =>  ' muid = :muid: and audit_status in (:audit_status:)',
            'bind' => array(
                'muid'          =>  $muid,
                'audit_status'  => '3',
            )
        ));
        if(!$result){
            throw new \Exception('查询数据失败');
        }
        $list = $result -> toArray();
        return $list[0];
    }





  




}