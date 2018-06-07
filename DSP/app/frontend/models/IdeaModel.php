<?php

/**
 * 广告创意模型
 * @category PhalconDSP
 * @author haoshisuo 2017-10-16
 */

namespace Marser\App\Frontend\Models;

use \Marser\App\Frontend\Models\BaseModel,
	\Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class IdeaModel extends BaseModel{

    const TABLE_NAME = 'ad_idea';

    public function initialize(){
        parent::initialize();
        $this -> setSource(self::TABLE_NAME);
    }

    /**
     * 获取广告创意列表
     * @param int $page
     * @param array $ext
     * @return mixed
     * @throws \Exception
     */
    public function get_list($page, $pagesize=10, array $ext=array()){
        $page = intval($page);
        $page <= 0 && $page = 1;
        $pagesize = intval($pagesize);
        //($pagesize <= 0 || $pagesize > 20) && $pagesize = 10;

        $builder = $this->getModelsManager()->createBuilder();
        $builder->from(__CLASS__);
        $builder->columns('id,prize_name,ad_image,ad_image2,coupon_term,push_link,examine_status');
        $builder->where('status = 1');
        $builder->andWhere('auid = :auid:', array('auid' => $ext['auid']));
        if(isset($ext['prize_name']) && !empty($ext['prize_name'])){
            $builder->andWhere("prize_name like :prize_name:", array('prize_name' => "%{$ext['prize_name']}%"));
        }

        $paginator = new PaginatorQueryBuilder(array(
            'builder' => $builder,
            'limit' => $pagesize,
            'page' => $page,
        ));
        $result = $paginator->getPaginate();
        return $result;
    }

    // 插入广告创意
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
        $id = $clone -> id;
        return $id;
    }

    /**
     * 更新记录
     * @param array $data
     * @param $aid
     * @return int
     * @throws \Exception
     */
    public function update_record(array $data, $id){
        $id = intval($id);
        if($id <= 0 || !is_array($data) || count($data) == 0){
            throw new \Exception('参数错误');
        }
        $data['update_tm'] = time();
        $this -> id = $id;
        $result = $this -> iupdate($data);
        if(!$result){
            throw new \Exception('更新失败');
        }
        $affectedRows = $this -> db -> affectedRows();
        return $affectedRows;
    }

    // 根据id查询一条记录
    public function get_One($id){
        $result = $this -> find(array(
            'conditions' => 'id = :id:',
            'bind' => array(
                'id' => $id,
            ),
        ));
        if(empty($result[0])){
            throw new \Exception('未知错误');
        }
        return $result[0];
    }

    /**
     * 检测奖品名称是否存在
     * @param null $name
     * @param null $mmid
     */
    public function check_exist($ext, $id=0){
        if(!is_array($ext) || count($ext) == 0){
            throw new \Exception('参数错误');
        }
        $params['conditions'] = 'status = 1';
        if(!empty($ext['auid'])){
            $params['conditions'] .= " AND auid = :auid:";
            $params['bind']['auid'] = $ext['auid'];
        }
        if(!empty($ext['prize_name'])){
            $params['conditions'] .= " AND prize_name = :prize_name:";
            $params['bind']['prize_name'] = $ext['prize_name'];
        }
        $id = intval($id);
        $id > 0 && $params['conditions'] .= " and id != {$id} ";
        $result = $this -> find($params);
        return $result;
    }

    //获取所有广告创意
    public function select(){
        $result = $this->find(array(
            'columns'     =>  'id,prize_name',
            'conditions'  =>  'examine_status = 2 and status = 1 and auid = '.$this->_user['auid'],
        ));
        return $result;
    }
}