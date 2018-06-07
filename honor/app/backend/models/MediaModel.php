<?php

namespace Marser\App\Backend\Models;
use \Marser\App\Backend\Models\BaseModel;
use	\Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class MediaModel extends BaseModel{

    const TABLE_NAME = 'media_list';

    public function initialize(){
        parent::initialize();
        $this -> set_table_source(self::TABLE_NAME);
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
        $builder->columns('a.mmid,a.muid,a.doc_key,a.type,a.tf_type,a.api_type,a.name,a.package_name,a.host,a.download_link,a.industry_parentid,a.industryid,a.appkey,a.examine_status,a.update_tm,a.create_tm,a.flow,a.apk_status,a.apk_sign,a.apk_path,a.dsp_path,a.dsp_key,a.soft_src,a.soft_url,b.username,b.media_name,b.company_name,b.scheme');
        $builder->from(array('a' => __CLASS__));
        $builder->leftJoin("\Marser\App\Backend\Models\UserMediaModel", "a.muid = b.muid",'b');
        if(isset($ext['username']) && !empty($ext['username'])) {
            $builder->andWhere("b.username = :username:", array('username' => "{$ext['username']}"));
        }
        if(isset($ext['company_name']) && !empty($ext['company_name'])) {
            $builder->andWhere("b.md_compnay_name like :company_name:", array('company_name' => "%{$ext['company_name']}%"));
        }
        if(isset($ext['name']) && !empty($ext['name']) ) {
            $builder->andWhere("a.name like :name:", array('name' => "%{$ext['name']}%"));
        }
        if( isset($ext['srch_type']) ) {
            $builder->andWhere("a.examine_status = :examine_status:", array('examine_status' => $ext['srch_type']));
        } 
        $builder->orderBy('a.mmid DESC');
        $paginator = new PaginatorQueryBuilder(array(
            'builder'	=>	$builder,
            'limit'		=>	$pagesize,
            'page'		=>	$page,
        ));
        $result = $paginator->getPaginate();
        return $result;
    }

    /**
     * 所有媒体
     * @param array $ext
     * @return array
     * @throws \Exception
     */
    public function get_all_list(array $ext=array()){
         $result = $this -> find(array(
            'columns'       =>  isset($ext['field']) && $ext['field']?$ext['field']:'*',
            'conditions'    =>  'examine_status = :examine_status:',
            'bind' => array(
                'examine_status' => 1
            )
        ));
        if(!$result){
            throw new \Exception('查询数据失败');
        }
        $list = $result -> toArray();
        return $list;
    }

    //获取某个媒体主下的媒体列表
    public function get_list_by_muid( $muid, $ext=array() ) {
        $result = $this -> find(array(
            'columns'     =>  isset($ext['field']) && $ext['field']?$ext['field']:'*',
            'conditions'  =>  'muid = :muid: and examine_status = :examine_status:',
            'bind' => array(
                'muid'           =>  $muid,
                'examine_status' => 1,
            ),
            'order' => 'mmid desc',
        ));
        $list = $result -> toArray();
        return $list;
    }

    /**
     * 根据id获取信息
     */
    public function get_media_by_id($mmid){
        if(empty($mmid)){
            throw new \Exception('参数错误');
        }
        $params = array(
            'columns' => '*',
            'conditions' => 'mmid = :mmid:',
            'bind' => array(
                'mmid' => $mmid,
            ),
        );
        $result = $this -> find($params)->toArray();
        return $result[0];
    }

    /**
    * 获取媒体详情
    */
    public function get_media_info($mmid){
        if(empty($mmid)){
            throw new \Exception('参数错误');
        }
        $builder = $this->getModelsManager()->createBuilder();
        $builder->columns('a.mmid,a.muid,a.doc_key,a.anzhi_tag,a.type,a.flow,a.level,a.name,a.package_name,a.host,a.download_link,a.tf_type,a.api_type,a.industry_parentid,a.industryid,a.appkey,a.intro,a.examine_status,a.update_tm,a.create_tm,a.token_url,a.apk_sign,a.soft_src,a.soft_url,a.secret,b.username,b.media_name,b.company_name,b.scheme');
        $builder->from(array('a' => __CLASS__));
        $builder->leftJoin("\Marser\App\Backend\Models\UserMediaModel", "a.muid = b.muid",'b');
        $builder->andWhere("a.mmid = :mmid:", array('mmid' => $mmid));
        $result = $builder->getQuery()->execute()->toArray();
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
        $mmid = $clone -> mmid;
        return $mmid;
    }


    /**
     * 标签更新
     * @param array $data
     * @param $tid
     * @return int
     * @throws \Exception
     */
    public function update_record(array $data, $mmid) {
        if(empty($mmid)){
            throw new \Exception('参数错误');
        }
        $data['update_tm'] = time();
        $clone = clone $this;
        $clone -> mmid = $mmid;
        $result = $clone -> iupdate($data);
        if(!$result){
            throw new \Exception('更新失败');
        }
        $affectedRows = $clone -> db -> affectedRows();
        return $affectedRows;
    }


    /**
     * 媒体是否存在
     * @param null $name
     * @param null $mmid
     */
    public function media_is_exist($name, $mmid=0){
        if(empty($name)){
            throw new \Exception('参数错误');
        }
        $params = array();
        $params['conditions'] = " name = :name: ";
        $params['bind']['name'] = $name;
        $mmid = intval($mmid);
        $mmid > 0 && $params['conditions'] .= " AND mmid != {$mmid} ";

        $result = $this -> find($params);
        return $result;
    }

    //批量导出
    public function batch_export($ids) {
        $sql = "SELECT a.*,b.username,b.media_name,b.company_name,b.scheme FROM media_list as a LEFT JOIN media_user as b ON a.muid=b.muid WHERE mmid in ({$ids}) ORDER BY mmid ASC";
        $result = $this->getDI()->get('db')->fetchAll($sql);
        return $result;
    }

}