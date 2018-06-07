<?php
/**
 * 基础设置模型
 */

namespace Marser\App\Backend\Models;

use \Marser\App\Backend\Models\BaseModel,
    \Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class PuConfigModel extends BaseModel{

    const TABLE_NAME = 'pu_config';

    public function initialize(){
        parent::initialize();
        $this -> set_table_source(self::TABLE_NAME);
    }




    //获取全部基础设置数据
    public function get_all_taglist(array $ext=array()){
         $result = $this -> find(array(
            'columns'       =>  isset($ext['field']) && $ext['field']?$ext['field']:'*',
            'conditions'    =>  'status = :examine_status: and tags!=""',
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

    public function get_info(array $ext=array()){
         $result = $this -> find(array(
            'columns'       =>  isset($ext['field']) && $ext['field']?$ext['field']:'*',
            'conditions'    =>  'config_key = :config_key: and status=1',
            'bind' => array(
                'config_key' => $ext['config_key'],
            )
        ));
        if(!$result){
            throw new \Exception('查询数据失败');
        }
        $list = $result -> toArray();
        return $list;
    }


    /**
     * 更新记录
     * @param array $data
     * @param $aid
     * @return int
     * @throws \Exception
     */
    public function update_record(array $data){
        if(!is_array($data) || count($data) == 0){
            throw new \Exception('参数错误');
        }

        $this -> config_id = '1';
        //$this -> config_key = 'basics_set';
        $result = $this -> iupdate($data);
        if(!$result){
            throw new \Exception('更新失败');
        }
        $affectedRows = $this -> db -> affectedRows();
        return $affectedRows;
    }

    public function update_views($data){
        $phql = "UPDATE " . __CLASS__ . " SET config_value = '".$data['config_value']."' WHERE config_key = :config_key:";
        $result = $this -> getModelsManager() -> executeQuery($phql, array(
            'config_key' => $data['config_key'],
        ));
        if(!$result){
            throw new \Exception('更新失败');
        }
        $affectedRows = $this -> db -> affectedRows();
        return $affectedRows;
    }

}
