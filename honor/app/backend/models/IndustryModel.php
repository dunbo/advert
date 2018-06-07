<?php

namespace Marser\App\Backend\Models;
use \Marser\App\Backend\Models\BaseModel;

class IndustryModel extends BaseModel{

    const TABLE_NAME = 'pu_industry';

    public function initialize(){
        parent::initialize();
        $this -> set_table_source(self::TABLE_NAME);
    }

    public function get_list(array $ext=array()){
       	$result = $this -> find(array(
            'columns'       =>  isset($ext['field']) && $ext['field']?$ext['field']:'*',
        ));
        if(!$result){
            throw new \Exception('查询数据失败');
        }
        $list = $result -> toArray();
        return $list;
    }


    public function getlistbyjoin(){
        $query = new \Phalcon\Mvc\Model\Query("SELECT a.id,a.name,a.parentid,b.name AS parentname,a.price,a.diff_price   FROM Marser\App\Backend\Models\IndustryModel a LEFT JOIN Marser\App\Backend\Models\IndustryModel b ON a.parentid=b.id", $this->getDI());

        $rs = $query->execute();//直接对象
        return $rs;
    }

    /**
     * 更新记录
     * @param array $data
     * @param $aid
     * @return int
     * @throws \Exception
     */
    public function update_record(array $data, $aid){
        $aid = intval($aid);
        if($aid <= 0 || !is_array($data) || count($data) == 0){
            throw new \Exception('参数错误');
        }

        $this -> id = $aid;
        $result = $this -> iupdate($data);
        if(!$result){
            throw new \Exception('更新失败');
        }
        $affectedRows = $this -> db -> affectedRows();
        return $affectedRows;
    }

    //获取所有记录
    public function select(){
        $result = $this->find();
        return $result->toArray();
    }
    
    public function get_info($id){
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

}
