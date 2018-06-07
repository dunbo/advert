<?php

namespace Marser\App\Backend\Models;
use \Marser\App\Backend\Models\BaseModel,
	\Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class StatisticsModel extends BaseModel {

    const TABLE_NAME = 'media_statistics';

    public function initialize() {
        parent::initialize();
        $this -> set_table_source(self::TABLE_NAME);
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
       	$builder->columns('id,ad_id,mmid,mname,aname,platform,amount_income,divide_into_proportion,sum(exposure) as exposure,sum(click) as click,sum(earnings) as earnings,create_date');
       	$builder->from( __CLASS__);
        if( $ext['type'] ==1 ) {
            if( isset($ext['mname']) && !empty($ext['mname'])) {
                $builder->andWhere("mname like :mname:", array('mname' => "%{$ext['mname']}%"));
            }
            if( isset($ext['aname']) && !empty($ext['aname'])) {
                $builder->andWhere("aname like :aname:", array('aname' => "%{$ext['aname']}%"));
            }
        }else {
            if( isset($ext['mname']) && !empty($ext['mname'])) {
                $builder->andWhere("mname like :mname:", array('mname' => "%{$ext['mname']}%"));
            }
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

    public function get_list_count(array $ext=array()) {
        $builder = $this->getModelsManager()->createBuilder();
        $builder->columns('sum(exposure) as exposure,sum(click) as click,sum(earnings) as earnings');
        $builder->from( __CLASS__);
        if( isset($ext['mname']) && !empty($ext['mname'])) {
            $builder->andWhere("mname like :mname:", array('mname' => "%{$ext['mname']}%"));
        }
        if( isset($ext['aname']) && !empty($ext['aname'])) {
            $builder->andWhere("aname like :aname:", array('aname' => "%{$ext['aname']}%"));
        }
        if (isset($ext['start_tm']) && !empty($ext['start_tm'])) {
            $builder->andWhere("create_date >= :start_tm:", array('start_tm' => $ext['start_tm']));
        }
        if (isset($ext['end_tm']) && !empty($ext['end_tm'])) {
            $builder->andWhere("create_date <= :end_tm:", array('end_tm' => $ext['end_tm']));
        }
        $result = $builder->getQuery()->execute()->toArray();
        return $result[0];
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
        $builder->columns('id,ad_id,mmid,mname,aname,platform,amount_income,divide_into_proportion,sum(exposure) as exposure,sum(click) as click,sum(earnings) as earnings,create_date');
        $builder->from( __CLASS__);
        if( $ext['type'] ==1 ) {
            $builder->andWhere("ad_id = :ad_id:", array('ad_id' => $ext['ad_id']));
            if( isset($ext['aname']) && !empty($ext['aname'])) {
                $builder->andWhere("aname like :aname:", array('aname' => "%{$ext['aname']}%"));
            }
        }else {
            $builder->andWhere("mmid = :mmid:", array('mmid' => $ext['mmid']));
            if( isset($ext['mname']) && !empty($ext['mname'])) {
                $builder->andWhere("mname like :mname:", array('mname' => "%{$ext['mname']}%"));
            }
        }
        if (isset($ext['start_tm']) && !empty($ext['start_tm'])) {
            $builder->andWhere("create_date >= :start_tm:", array('start_tm' => $ext['start_tm']));
        }elseif (isset($ext['end_tm']) && !empty($ext['end_tm'])) {
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

    public function get_days_list_count(array $ext=array()) {
        $builder = $this->getModelsManager()->createBuilder();
        $builder->columns('sum(exposure) as exposure,sum(click) as click,sum(earnings) as earnings');
        $builder->from( __CLASS__);
        if( isset($ext['mmid']) && !empty($ext['mmid'])) {
            $builder->andWhere("mmid = :mmid:", array('mmid' => $ext['mmid']));
        }
        if( isset($ext['ad_id']) && !empty($ext['ad_id'])) {
             $builder->andWhere("ad_id = :ad_id:", array('ad_id' => $ext['ad_id']));
        }
        if( isset($ext['mname']) && !empty($ext['mname'])) {
            $builder->andWhere("mname like :mname:", array('mname' => "%{$ext['mname']}%"));
        }
        if( isset($ext['aname']) && !empty($ext['aname'])) {
            $builder->andWhere("aname like :aname:", array('aname' => "%{$ext['aname']}%"));
        }
        if (isset($ext['start_tm']) && !empty($ext['start_tm'])) {
            $builder->andWhere("create_date >= :start_tm:", array('start_tm' => $ext['start_tm']));
        }
        if (isset($ext['end_tm']) && !empty($ext['end_tm'])) {
            $builder->andWhere("create_date <= :end_tm:", array('end_tm' => $ext['end_tm']));
        }
        $result = $builder->getQuery()->execute()->toArray();
        return $result[0];
    }


    /**
     * 获取媒体统计列表或广告位统计列表
     * @param int $page
     * @param array $ext
     * @return mixed
     * @throws \Exception
     */
    public function get_ad_list($page, $pagesize=10, array $ext=array()) {
        $page = intval($page);
        $page <= 0 && $page = 1;
        $pagesize = intval($pagesize);

        $builder = $this->getModelsManager()->createBuilder();
        $builder->columns('a.id,a.ad_id,a.mmid,a.mname,a.aname,a.platform,a.amount_income,a.divide_into_proportion,sum(a.exposure) as exposure,sum(a.click) as click,sum(a.earnings) as earnings,a.create_date,c.media_name,c.company_name,b.type,b.tf_type,b.api_type,d.t_pid,d.t_id');
        $builder->from(array('a' => __CLASS__));
        $builder->leftJoin("\Marser\App\Backend\Models\MediaModel", "a.mmid = b.mmid",'b');

        $builder->leftJoin("\Marser\App\Backend\Models\UserMediaModel", "b.muid = c.muid",'c');

        $builder->leftJoin("\Marser\App\Backend\Models\PositionModel", "a.ad_id = d.id",'d');

        if( isset($ext['mname']) && !empty($ext['mname'])) {
                $builder->andWhere("a.mname like :mname:", array('mname' => "%{$ext['mname']}%"));
        }
        if( isset($ext['aname']) && !empty($ext['aname'])) {
                $builder->andWhere("a.aname like :aname:", array('aname' => "%{$ext['aname']}%"));
        }
        if (isset($ext['start_tm']) && !empty($ext['start_tm'])) {
            $builder->andWhere("a.create_date >= :start_tm:", array('start_tm' => $ext['start_tm']));
        }
        if (isset($ext['end_tm']) && !empty($ext['end_tm'])) {
            $builder->andWhere("a.create_date <= :end_tm:", array('end_tm' => $ext['end_tm']));
        }
        if(isset($ext['day']) && !empty($ext['day'])) {
            $builder->groupBy('a.create_date');
            $builder->orderBy('a.exposure DESC');
        }else {
            $builder->groupBy('a.ad_id');
            $order = !empty($ext['order'])?"DESC":"ASC";
            if(isset($ext['order_type']) && !empty($ext['order_type'])) {
                if($ext['order_type'] == "exp") {
                    $builder->orderBy("a.exposure {$order}"); 
                }elseif($ext['order_type'] == "click") {
                    $builder->orderBy("a.click {$order}"); 
                }elseif($ext['order_type'] == "ear") {
                    $builder->orderBy("a.earnings {$order}"); 
                }elseif($ext['order_type'] == "rate") {
                    $builder->orderBy(" a.click/a.exposure  {$order}"); 
                }elseif($ext['order_type'] == 'ecpm') {
                    $builder->orderBy(" a.earnings/a.exposure  {$order}");
                }
            }else {
                 $builder->orderBy('a.exposure DESC');
            }
        }
        $result = $builder->getQuery()->execute()->toArray();
        return $result;
    }

}