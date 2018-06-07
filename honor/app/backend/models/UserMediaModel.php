<?php

namespace Marser\App\Backend\Models;
use \Marser\App\Backend\Models\BaseModel;
use	\Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class UserMediaModel extends BaseModel{

    const TABLE_NAME = 'media_user';

    public function initialize(){
        parent::initialize();
        $this -> set_table_source(self::TABLE_NAME);
    }

    /**
     * 获取媒体主列表
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
        if(isset($ext['username']) && !empty($ext['username'])) {
            $builder->andWhere("username like :username:", array('username' => "%{$ext['username']}%"));
		}
        if(isset($ext['media_name']) && !empty($ext['media_name'])) {
            $builder->andWhere("media_name like :media_name:", array('media_name' => "%{$ext['media_name']}%"));
        }
        if( isset($ext['md_compnay_name']) && !empty($ext['md_compnay_name']) ) {
            $builder->andWhere("md_compnay_name like :md_compnay_name:", array('md_compnay_name' => "%{$ext['md_compnay_name']}%"));
        }
        if( isset($ext['status']) ) {
            $builder->andWhere("status = :status:", array('status' => $ext['status']));
        }
        $builder->orderBy('create_tm DESC');
        $paginator = new PaginatorQueryBuilder(array(
            'builder'   =>  $builder,
            'limit'     =>  $pagesize,
            'page'      =>  $page,
        ));
        $result = $paginator->getPaginate();
        return $result;
    }

    /**
     * 获取财务信息管理列表
     * @param int $page
     * @param array $ext
     * @return mixed
     * @throws \Exception
     */
    public function get_finance_list($page, $pagesize=10, array $ext=array()){
        $page = intval($page);
        $page <= 0 && $page = 1;
        $pagesize = intval($pagesize);
        $builder = $this->getModelsManager()->createBuilder();
        $builder->from( __CLASS__);
        $builder->columns(array('*'));
        if(isset($ext['username']) && !empty($ext['username'])) {
            $builder->andWhere("username like :username:", array('username' => "%{$ext['username']}%"));
        }
        if(isset($ext['media_name']) && !empty($ext['media_name'])) {
            $builder->andWhere("media_name like :media_name:", array('media_name' => "%{$ext['media_name']}%"));
        }
        if( isset($ext['company_name']) && !empty($ext['company_name']) ) {
            $builder->andWhere("company_name like :company_name:", array('company_name' => "%{$ext['company_name']}%"));
        }
        if( isset($ext['cw_status']) ) {
            $builder->andWhere("cw_status = :cw_status:", array('cw_status' => $ext['cw_status']));
            if($ext['cw_status']==0){
                $builder->andWhere("bank_account != ''");
            }
        }
        $builder->orderBy('create_tm DESC');
        $paginator = new PaginatorQueryBuilder(array(
            'builder'   =>  $builder,
            'limit'     =>  $pagesize,
            'page'      =>  $page,
        ));
        $result = $paginator->getPaginate();
        return $result;
    }

    /**
     * 所有有效的媒体主
     * @param array $ext
     * @return array
     * @throws \Exception
     */
    public function get_all_list(array $ext=array()){
         $result = $this -> find(array(
            'columns'   =>  isset($ext['field']) && $ext['field']?$ext['field']:'*',
            'order'     =>  'muid asc',
        ));
        $list = $result -> toArray();
        return $list;
    }


    /**
     * 根据id获取信息
     */
    public function get_user_by_muid($muid,$ext=''){
        if(empty($muid)){
            throw new \Exception('参数错误');
        }
        $params = array(
            'columns' => $ext?$ext:'*',
            'conditions' => 'muid = :muid:',
            'bind' => array(
                'muid' => $muid,
            ),
        );
        $result = $this -> find($params)->toArray();
        return $result[0];
    }

    /**
     * 根据username获取信息
     * @param $tagname
     * @return int
     * @throws \Exception
     */
    public function get_user_by_username($username){
        if(empty($username)){
            throw new \Exception('参数错误');
        }
        $params = array(
            'columns' => '*',
            'conditions' => 'username = :username:',
            'bind' => array(
                'username' => $username,
            ),
        );
        $result = $this -> find($params)->toArray();
        return $result[0];
    }

     /**
     * 媒体数据入库
     * @param array $data
     * @return bool|int
     * @throws \Exception
     */
    public function insert_record(array $data){
        if(count($data) == 0){
            throw new \Exception('参数错误');
        }
        $data['update_tm'] = time();
        $data['create_tm'] = time();
        $clone = clone $this;
        $result = $clone -> create($data);
        if(!$result){
            throw new \Exception(implode(',', $this -> getMessages()));
        }
        $muid = $clone -> muid;
        return $muid;
    }


    /**
     * 标签更新
     * @param array $data
     * @param $muid
     * @return int
     * @throws \Exception
     */
    public function update_record(array $data, $muid) {
        $muid = intval($muid);
        if(count($data) == 0 || $muid <= 0) {
            throw new \Exception('参数错误');
        }
        $data['update_tm'] = time();
        $this -> muid = $muid;
        $result = $this -> iupdate($data);
        if(!$result) {
            throw new \Exception(implode(',', $this -> getMessages()));
        }
        $affectedRows = $this -> db -> affectedRows();
        return $affectedRows;
    }


    /**
     * 媒体是否存在
     * @param null $username
     * @param null $muid
     */
    public function usermeida_is_exist($username, $muid=0){
        if(empty($username)){
            throw new \Exception('参数错误');
        }
        $params = array();
        $params['conditions'] = " username = :username: ";
        $params['bind']['username'] = $username;
        $muid = intval($muid);
        $muid > 0 && $params['conditions'] .= " AND muid != {$muid} ";

        $result = $this -> find($params);
        return $result;
    }


}