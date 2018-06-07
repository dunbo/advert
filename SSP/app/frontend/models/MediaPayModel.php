<?php

namespace Marser\App\Frontend\Models;
use \Marser\App\Frontend\Models\BaseModel;
use	\Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class MediaPayModel extends BaseModel{

    const TABLE_NAME = 'media_pay';

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

    public function insert_record(array $data) {
        if(count($data) == 0) {
            throw new \Exception('参数错误');
        }
        $data['update_time'] = date("Y-m-d H:i:s", time());
        $data['create_time'] = date("Y-m-d H:i:s", time());
        $clone	=	clone $this;
        $result	=	$clone -> create($data);
        if( !$result ) {
            throw new \Exception(implode(',', $this -> getMessages()));
        }
        $id = $clone -> id;
        return $id;
    }

}