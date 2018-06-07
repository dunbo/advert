<?php

namespace Marser\App\Frontend\Models;
use \Marser\App\Frontend\Models\BaseModel,
	\Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class StatisticsModel extends BaseModel {

    const TABLE_NAME = 'media_statistics';

    public function initialize() {
        parent::initialize();
        $this -> set_table_source(self::TABLE_NAME);
    }

    /**
    * 获取chart
    */
    public function get_chart($muid, $start_tm, $end_tm){
        $params = array(
            'columns' => 'id,ad_id,mmid,aname,mname,platform,sum(exposure) as exposure,sum(click) as click,sum(earnings) as earnings,create_date',
            'conditions' => 'muid = :muid: and create_date >= :start_tm: and create_date <= :end_tm:',
            'bind' => array(
                'muid'      =>  $muid,
                'start_tm'  =>  $start_tm,
                'end_tm'    =>  $end_tm,
            ),
            'group' =>  'create_date',
            'order' =>  'create_date asc',
        );
        $result = $this -> find($params)->toArray();
        return $result;
    }

    /**
     * 获取媒体统计列表或广告位统计列表
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
       	$builder->columns('id,ad_id,mmid,aname,mname,platform,sum(exposure) as exposure,sum(click) as click,sum(earnings) as earnings,create_date');
       	$builder->from( __CLASS__);
        if (isset($ext['muid']) && !empty($ext['muid'])) {
            $builder->andWhere("muid = :muid:", array('muid' => $ext['muid']));
        }
		if (isset($ext['start_tm']) && !empty($ext['start_tm'])) {
        	$builder->andWhere("create_date >= :start_tm:", array('start_tm' => $ext['start_tm']));
        }
        if (isset($ext['end_tm']) && !empty($ext['end_tm'])) {
        	$builder->andWhere("create_date <= :end_tm:", array('end_tm' => $ext['end_tm']));
        }
        if( $ext['type'] ==1 ) {
            $builder->groupBy('ad_id');
        }else {
            $builder->groupBy('mmid');
        }

        $order = !empty($ext['order'])?"DESC":"ASC";
        if(isset($ext['order_type']) && !empty($ext['order_type'])) {
            if($ext['order_type'] == "exp") {
                $builder->orderBy("exposure {$order}"); 
            }elseif($ext['order_type'] == "click") {
                $builder->orderBy("click {$order}"); 
            }elseif($ext['order_type'] == "ear") {
                $builder->orderBy("earnings {$order}"); 
            }elseif($ext['order_type'] == "rate") {
                $builder->orderBy(" click/exposure  {$order}"); 
            }elseif($ext['order_type'] == 'ecpm') {
                $builder->orderBy(" earnings/exposure  {$order}");
            }
        }else {
            $builder->orderBy('exposure DESC');    
        }
        if( $ext['export'] ) {
             $result = $builder->getQuery()->execute()->toArray();
             return $result;
        }else {
             $paginator = new PaginatorQueryBuilder(array(
                'builder'   =>  $builder,
                'limit'     =>  $pagesize,
                'page'      =>  $page,
            ));
            $result = $paginator->getPaginate();
            return $result;
        }

    }



    /**
     * 获取媒体统计列表或广告位每天统计列表
     * @param int $page
     * @param array $ext
     * @return mixed
     * @throws \Exception
     */
    public function get_days_list($page, $pagesize=10, array $ext=array()) {
        $page = intval($page);
        $page <= 0 && $page = 1;
        $pagesize = intval($pagesize);
        $builder = $this->getModelsManager()->createBuilder();
        $builder->columns('id,ad_id,mmid,mname,platform,sum(exposure) as exposure,sum(click) as click,sum(earnings) as earnings,create_date');
        $builder->from( __CLASS__);
        if( $ext['type'] ==1 ) {
            $builder->andWhere("ad_id = :ad_id:", array('ad_id' => $ext['ad_id']));
        }else {
            $builder->andWhere("mmid = :mmid:", array('mmid' => $ext['mmid']));
        }
        if (isset($ext['muid']) && !empty($ext['muid'])) {
            $builder->andWhere("muid = :muid:", array('muid' => $ext['muid']));
        }
        if (isset($ext['start_tm']) && !empty($ext['start_tm'])) {
            $builder->andWhere("create_date >= :start_tm:", array('start_tm' => $ext['start_tm']));
        }
        if (isset($ext['end_tm']) && !empty($ext['end_tm'])) {
            $builder->andWhere("create_date <= :end_tm:", array('end_tm' => $ext['end_tm']));
        }
        $builder->groupBy('create_date');


       $order = !empty($ext['order'])?"DESC":"ASC";
        if(isset($ext['order_type']) && !empty($ext['order_type'])) {
            if($ext['order_type'] == "exp") {
                $builder->orderBy("exposure {$order}"); 
            }elseif($ext['order_type'] == "click") {
                $builder->orderBy("click {$order}"); 
            }elseif($ext['order_type'] == "ear") {
                $builder->orderBy("earnings {$order}"); 
            }elseif($ext['order_type'] == "rate") {
                $builder->orderBy(" click/exposure  {$order}"); 
            }elseif($ext['order_type'] == 'ecpm') {
                $builder->orderBy(" earnings/exposure  {$order}");
            }
        }else {
            $builder->orderBy('create_date DESC');    
        }
        if( $ext['export'] ) {
             $result = $builder->getQuery()->execute()->toArray();
             return $result;
        }else {
             $paginator = new PaginatorQueryBuilder(array(
                'builder'   =>  $builder,
                'limit'     =>  $pagesize,
                'page'      =>  $page,
            ));
            $result = $paginator->getPaginate();
            return $result;
        }
    }


}