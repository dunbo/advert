<?php

namespace Marser\App\Backend\Models;
use \Marser\App\Backend\Models\BaseModel;

class CouponModel extends BaseModel{

    const TABLE_NAME = 'media_coupon';

    public function initialize(){
        parent::initialize();
        $this -> set_table_source(self::TABLE_NAME);
    }

    public function get_list($aid,$awardid) {
        $params = array(
            'columns' => 'id,coupon_code',
            'conditions' => 'aid = :aid: and awardid = :awardid:',
            'bind' => array(
                'aid'        =>  $aid,
                'awardid'    =>  $awardid,
            ),
        );
        $result = $this -> find($params)->toArray();
        return $result;
    }

    /**
     * 获取奖品列表
     * @param int $page
     * @param array $ext
     * @return mixed
     * @throws \Exception
     */
    public function get_count($aid, $id) {
        $params = array(
            'columns' => 'count(*) as count',
            'conditions' => 'aid = :aid: and awardid = :id:',
            'bind' => array(
                'aid'   =>  $aid,
                'id'    =>  $id,
            ),
        );
        $result = $this -> find($params)->toArray();
        return isset($result[0])?$result[0]['count']:0;
    }

 	/**
     * 根据id获取信息
     */
    public function get_prize_info($id){
        if(empty($id)){
            throw new \Exception('参数错误');
        }
        $params = array(
            'columns' => '*',
            'conditions' => 'id = :id:',
            'bind' => array(
                'id' => $id,
            ),
        );
        $result = $this -> find($params)->toArray();
        return $result[0];
    }

     /**
     * 优惠券数据入库
     * @param array $data
     * @return bool|int
     * @throws \Exception
     */
    public function insert_record(array $data){
        if(count($data) == 0){
            throw new \Exception('参数错误');
        }
        $data['create_tm'] = time();
        $clone = clone $this;
        $result = $clone -> create($data);
        if(!$result){
            throw new \Exception(implode(',', $this -> getMessages()));
        }
        $id = $clone -> id;
        return $id;
    }


    /**
     * 标签更新
     * @param array $data
     * @param $tid
     * @return int
     * @throws \Exception
     */
    public function update_record(array $data, $id) {
        if(empty($id)){
            throw new \Exception('参数错误');
        }
        $clone = clone $this;
        $clone -> id = $id;
        $result = $clone -> iupdate($data);
        if(!$result){
            throw new \Exception('更新失败');
        }
        $affectedRows = $clone -> db -> affectedRows();
        return $affectedRows;
    }

    public function delete_record($aid, $id){
        if(empty($aid) || empty($id)){
            throw new \Exception('参数错误');
        }
        $status = $this->modelsManager->executeQuery(
            "DELETE FROM \Marser\App\Backend\Models\CouponModel WHERE aid = :aid: and awardid = :id:",
            array(
                'aid'   =>  $aid,
                "id"    =>  $id,
            )
        );
        return $status->success();
    }

    public function batch_add($str){
        $sql = "INSERT INTO media_coupon(aid,awardid,coupon_code,create_tm) values ".$str;
        $result = $this->getDI()->get('db')->execute($sql);
        if ($result) {
            return true;
        }else {
            return false;
        }
    }

}