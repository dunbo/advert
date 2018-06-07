<?php

/**
 * 广告规格模型
 * @category PhalconHonor
 * @author haoshisuo 2017-10-23
 */

namespace Marser\App\Backend\Models;
use \Marser\App\Backend\Models\BaseModel;
use	\Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class GuigeModel extends BaseModel{

    const TABLE_NAME = 'ad_template';

    public function initialize(){
        parent::initialize();
        $this -> setSource(self::TABLE_NAME);
    }

    /**
     * 获取广告规格列表
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
       	$builder->columns('*');
        if(isset($ext['status'])) {
            $builder->andWhere("status = :status:", array('status' => "{$ext['status']}"));
		}
        if(isset($ext['pu_tid']) && !empty($ext['pu_tid'])) {
            $builder->andWhere("pu_tid = :pu_tid:", array('pu_tid' => "{$ext['pu_tid']}"));
        }
        $builder->orderBy('tid DESC');
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
    public function get_One($tid){
        if(empty($tid)){
            throw new \Exception('参数错误');
        }
        $params = array(
            'conditions' => 'tid = :tid:',
            'bind' => array(
                'tid' => $tid,
            ),
        );
        $result = $this -> findFirst($params) -> toArray();
        if(!$result){
            throw new \Exception('获取信息失败');
        }
        return $result;
    }
    
    // 新增广告规格
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
        $tid = $clone -> tid;
        return $tid;
    }

    /**
     * 更新记录
     * @param array $data
     * @param $aid
     * @return int
     * @throws \Exception
     */
    public function update_record(array $data, $tid){
        $tid = intval($tid);
        if($tid <= 0 || !is_array($data) || count($data) == 0){
            throw new \Exception('参数错误');
        }
        $data['update_tm'] = time();
        $this -> tid = $tid;
        $result = $this -> iupdate($data);
        if(!$result){
            throw new \Exception('更新失败');
        }
        $affectedRows = $this -> db -> affectedRows();
        return $affectedRows;
    }
}