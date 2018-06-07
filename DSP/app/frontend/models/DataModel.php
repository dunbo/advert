<?php

/**
 * 广告数据模型
 * @category PhalconDSP
 * @author haoshisuo 2017-9-15
 */

namespace Marser\App\Frontend\Models;
use \Marser\App\Frontend\Models\BaseModel,
	\Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

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
        $builder->columns('a.id,a.ad_name,a.client_name,a.create_date,a.exposure,a.click_num,a.pv,a.click_price,a.total');
        $builder->where('a.auid = :auid:', array('auid' => $ext['auid']));
        if(isset($ext['materiel_name']) && !empty($ext['materiel_name'])){
            $builder->andWhere("a.ad_name like :materiel_name:", array('materiel_name' => "%{$ext['materiel_name']}%"));
        }
        if(isset($ext['tag']) && !empty($ext['tag'])){
        	if($ext['tag'] == 1){
        		$date = date('Y-m-d', $time);
        		$builder->andWhere("a.create_date = '{$date}'");
        	}elseif($ext['tag'] == 2){
        		$date = date('Y-m-d', $time-86400);
        		$builder->andWhere("a.create_date = '{$date}'");
        	}elseif($ext['tag'] == 7){
        		$date = date('Y-m-d', $time-86400*7);
        		$builder->andWhere("a.create_date >= '{$date}'");
        	}elseif($ext['tag'] == 30){
        		$date = date('Y-m-d', $time-86400*30);
        		$builder->andWhere("a.create_date >= '{$date}'");
        	}
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

    // 广告列表关联广告数据
    public function append_columns($result,$ext){
    	$time = time();
    	$columns = "sum(exposure) as exposure,sum(click_num) as click_num,sum(total) as total";
	    $conditions = "ad_id = :ad_id:";
        $bind = array();
	    //根据传递的参数统计不同的时间段信息
	    if(isset($ext['tag']) && !empty($ext['tag'])){
	    	if($ext['tag'] == 1){
        		$date = date('Y-m-d', $time);
        		$conditions .= " and create_date = '{$date}'";
        	}elseif($ext['tag'] == 2){
        		$date = date('Y-m-d', $time-86400);
        		$conditions .= " and create_date = '{$date}'";
        	}elseif($ext['tag'] == 7){
        		$date = date('Y-m-d', $time-86400*7);
        		$conditions .= " and create_date >= '{$date}'";
        	}elseif($ext['tag'] == 30){
        		$date = date('Y-m-d', $time-86400*30);
        		$conditions .= " and create_date >= '{$date}'";
        	}
	    }
	    if(!empty($ext['begin_tm'])){
            $begin_tm = strtotime($ext['begin_tm']);
	    	$conditions .= " and :begin_tm: <= create_tm";
            $bind['begin_tm'] = $begin_tm;
	    }
	    if(!empty($ext['end_tm'])){
            $end_tm = strtotime($ext['end_tm']);
	    	$conditions .= " and :end_tm: >= create_tm ";
            $bind['end_tm'] = $end_tm;
	    }
        //echo $conditions;die;
    	foreach($result as $key => $data){
            $bind['ad_id'] = $data['id'];
	        $res = $this->find(array(
	        	'columns'     =>  $columns,
	        	'conditions'  =>  $conditions,
	        	'bind'        =>  $bind,
                'group'       =>  'create_date',
	        ));
	        $res_arr = $res->toArray()[0];
	    	$data['exposure'] = $res_arr['exposure'];
	    	$data['click_num'] = $res_arr['click_num'];
	    	$data['total'] = $res_arr['total'];
	    	$result[$key] = $data;
    	}
    	return $result;
    }

    /**
    * 获取chart
    */
    public function get_chart($auid, $ext) {
        if(isset($ext['tag']) && !empty($ext['tag'])){
            $conditions = '';
            $time = time();
            $time_date = date("Y-m-d",$time);
            if($ext['tag'] == 1){
                $date = date('Y-m-d', $time);
                $conditions .= " and create_date = '{$date}'";
            }elseif($ext['tag'] == 2){
                $date = date('Y-m-d', $time-86400);
                $conditions .= " and create_date = '{$date}'";
            }elseif($ext['tag'] == 7){
                $date = date('Y-m-d', $time-86400*7);
                $conditions .= " and  create_date <= '{$time_date}' and create_date >= '{$date}'";
            }elseif($ext['tag'] == 30){
                $date = date('Y-m-d', $time-86400*30);
                $conditions .= " and  create_date <= '{$time_date}' and create_date >= '{$date}'";
            }
        }
        if(isset($ext['tag']) && !empty($ext['tag']) && ($ext['tag'] ==1 || $ext['tag'] ==2) ) {
            $sql = "SELECT * FROM  (SELECT  id,ad_id,sum(exposure) as exposure,sum(click_num) as click_num,sum(total) as total,FROM_UNIXTIME(create_tm, '%h' ) as `create_date` FROM ad_statistics where auid = {$auid} {$conditions} GROUP BY (FROM_UNIXTIME(create_tm, '%H'))) as A  ORDER BY A.`create_date` asc";
            $result = $this->getDI()->get('db')->fetchAll($sql);
            return $result;
        }else {
            $params = array(
                'columns' => 'id,ad_id,sum(exposure) as exposure,sum(click_num) as click_num,avg(click_rate) as click_rate, sum(total) as total,create_date',
                'conditions' => 'auid = :auid:'.$conditions,
                'bind' => array(
                    'auid'      =>  $auid,
                ),
                'group' =>  'create_date',
                'order' =>  'create_date asc',
            );
            $result = $this -> find($params)->toArray();
            return $result;    
        }
    }

    //获取广告主用户统计
    public function get_count($auid, $ext){
        if(isset($ext['tag']) && !empty($ext['tag'])){
            $conditions = '';
            $time = time();
            if($ext['tag'] == 1){
                $date = date('Y-m-d', $time);
                $conditions .= " and create_date = '{$date}'";
            }elseif($ext['tag'] == 2){
                $date = date('Y-m-d', $time-86400);
                $conditions .= " and create_date = '{$date}'";
            }elseif($ext['tag'] == 7){
                $date = date('Y-m-d', $time-86400*7);
                $conditions .= " and create_date >= '{$date}'";
            }elseif($ext['tag'] == 30){
                $date = date('Y-m-d', $time-86400*30);
                $conditions .= " and create_date >= '{$date}'";
            }
        }
        $sql = "SELECT  id,ad_id,sum(exposure) as exposure,sum(click_num) as click_num,avg(click_rate) as click_rate, sum(total) as total,create_date FROM ad_statistics where auid = {$auid} {$conditions} ";
        $result = $this->getDI()->get('db')->fetchOne($sql);
        return $result;
    }


    public function get_list_export(array $ext=array()){
        $time = time();
        $sql =  "select ad_id,ad_name,sum(exposure) as exposure,sum(click_num) as click,avg(click_price) as click_price,sum(total) as total,create_date,avg(click_rate) as click_rate from ad_statistics where ";
        $where = " auid = {$ext['auid']}";
        if(isset($ext['ad_id']) && !empty($ext['ad_id'])){
            $where .= " and ad_id in ({$ext['ad_id']}) ";
        }
        if(isset($ext['tag']) && !empty($ext['tag'])){
            if($ext['tag'] == 1){
                $date = date('Y-m-d', $time);
                $where .= " and create_date = {$date} ";
            }elseif($ext['tag'] == 2){
                $date = date('Y-m-d', $time-86400);
                $where .= " and create_date = {$date} ";
            }elseif($ext['tag'] == 7){
                $date = date('Y-m-d', $time-86400*7);
                $where .= " and create_date >= {$date} ";
            }elseif($ext['tag'] == 30){
                $date = date('Y-m-d', $time-86400*30);
                $where .= " and create_date >= {$date} ";
            }
        }
        if(isset($ext['begin_tm']) && !empty($ext['begin_tm'])){
            $begin_tm = strtotime($ext['begin_tm']);
            $where .= " and create_tm >= {$begin_tm} ";
        }
        if(isset($ext['end_tm']) && !empty($ext['end_tm'])){
            $end_tm = strtotime($ext['end_tm']);
            $where .= " and  {$end_tm}  >= create_tm ";
        }
        $groupBy  = " group by create_date order by create_date asc ";
        $sql = $sql.$where.$groupBy;
        $result = $this->getDI()->get('db')->fetchAll($sql);
        return $result;
    }


}