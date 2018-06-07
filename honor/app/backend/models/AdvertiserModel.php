<?php

/**
 * 广告主模型
 * @category PhalconHonor
 * @author haoshisuo 2017-9-22
 */

namespace Marser\App\Backend\Models;
use \Marser\App\Backend\Models\BaseModel;
use	\Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class AdvertiserModel extends BaseModel{

    const TABLE_NAME = 'ad_user';

    public function initialize(){
        parent::initialize();
        $this -> setSource(self::TABLE_NAME);
    }

    /**
     * 获取广告主列表
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
        if(isset($ext['ad_name']) && !empty($ext['ad_name'])) {
            $builder->andWhere("ad_name like :ad_name:", array('ad_name' => "%{$ext['ad_name']}%"));
        }
        if( isset($ext['company_name']) && !empty($ext['company_name']) ) {
            $builder->andWhere("company_name like :company_name:", array('company_name' => "%{$ext['company_name']}%"));
        }
        $builder->orderBy('auid DESC');
        $paginator = new PaginatorQueryBuilder(array(
            'builder'   =>  $builder,
            'limit'     =>  $pagesize,
            'page'      =>  $page,
        ));
        $result = $paginator->getPaginate();
        return $result;
    }

    /**
     * 根据id获取单条记录
     */
    public function get_One($auid){
        if(empty($auid)){
            throw new \Exception('参数错误');
        }
        $params = array(
            'conditions' => 'auid = :auid:',
            'bind' => array(
                'auid' => $auid,
            ),
        );
        $result = $this -> findFirst($params) -> toArray();
        if(!$result){
            throw new \Exception('获取信息失败');
        }
        return $result;
    }
    
    // 新增广告主
    public function insert_record(array $data){
        if(!is_array($data) || count($data) == 0){
            throw new \Exception('参数错误');
        }
        $data['create_tm'] = time();
        $data['update_tm'] = time();
        $clone = clone $this;
        $result = $clone -> create($data);
        if(!$result){
            throw new \Exception(implode(',', $this -> getMessages()));
        }
        $id = $clone -> auid;
        return $id;
    }

    /**
     * 更新记录
     * @param array $data
     * @param $aid
     * @return int
     * @throws \Exception
     */
    public function update_record(array $data, $auid){
        $auid = intval($auid);
        if($auid <= 0 || !is_array($data) || count($data) == 0){
            throw new \Exception('参数错误');
        }
        $data['update_tm'] = time();
        $this -> auid = $auid;
        $result = $this -> iupdate($data);
        if(!$result){
            throw new \Exception('更新失败');
        }
        $affectedRows = $this -> db -> affectedRows();
        return $affectedRows;
    }

    /**
     * 检测广告主或登录名是否存在
     * @param null $name
     * @param null $mmid
     */
    public function check_exist($ext, $auid=0){
        if(!is_array($ext) || count($ext) == 0){
            throw new \Exception('参数错误');
        }
        $params['conditions'] = '2>1';
        if(!empty($ext['ad_name'])){
            $params['conditions'] .= " and ad_name = :ad_name:";
            $params['bind']['ad_name'] = $ext['ad_name'];
        }
        if(!empty($ext['username'])){
            $params['conditions'] .= " and username = :username:";
            $params['bind']['username'] = $ext['username'];
        }
        if(!empty($ext['company_name'])){
            $params['conditions'] .= " and company_name = :company_name:";
            $params['bind']['company_name'] = $ext['company_name'];
        }
        $auid = intval($auid);
        $auid > 0 && $params['conditions'] .= " and auid != {$auid} ";

        $result = $this -> find($params);
        return $result;
    }
}