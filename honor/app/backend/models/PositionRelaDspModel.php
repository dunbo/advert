<?php

namespace Marser\App\Backend\Models;
use \Marser\App\Backend\Models\BaseModel;
use	\Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class PositionRelaDspModel extends BaseModel{

    const TABLE_NAME = 'media_ad_rela';

    public function initialize(){
        parent::initialize();
        $this -> set_table_source(self::TABLE_NAME);
    }

    public function get_list_by_ad_pos_id($aid, $ext=array() ) {
        $result = $this -> find(array(
            'columns'     =>  isset($ext['field']) && $ext['field']?$ext['field']:'*',
            'conditions'  =>  'ad_pos_id = :ad_pos_id:',
            'bind' => array(
                'ad_pos_id'           =>  $aid,
            ),
        ));
        $list = $result -> toArray();
        return $list;
    }


    public function get_dsp_info($aid,$dsp_id) {
        $result = $this -> find(array(
            'columns'     =>  '*',
            'conditions'  =>  'ad_pos_id = :ad_pos_id: and dsp_id = :dsp_id:',
            'bind' => array(
                'ad_pos_id'           =>  $aid,
                'dsp_id'              =>  $dsp_id,
            ),
        ));
        $list = $result -> toArray();
        return $list;
    }


    public function get_dsp_info_ext($dsp_id,$dsp_ad_pos_id) {
        $result = $this -> find(array(
            'columns'     =>  '*',
            'conditions'  =>  'dsp_id = :dsp_id: and dsp_ad_pos_id = :dsp_ad_pos_id:',
            'bind' => array(
                'dsp_id'           =>  $dsp_id,
                'dsp_ad_pos_id'    =>  $dsp_ad_pos_id,
            ),
        ));
        $list = $result -> toArray();
        return $list;
    }

     /**
     *入库
     * @param array $data
     * @return bool|int
     * @throws \Exception
     */
    public function insert_record(array $data){
        $data['update_tm'] = time();
        if(count($data) == 0){
            throw new \Exception('参数错误');
        }
        $clone = clone $this;
        $result = $clone -> create($data);
        if(!$result){
            throw new \Exception(implode(',', $this -> getMessages()));
        }
        return true;
    }

    public function update_record(array $data) {
        $time = time();
        $sql = "update media_ad_rela set dsp_appid = '{$data['dsp_appid']}',dsp_ad_pos_id = {$data['dsp_ad_pos_id']},dsp_appkey='{$data['dsp_appkey']}', update_tm={$time} where ad_pos_id = {$data['ad_pos_id']} and dsp_id = {$data['dsp_id']} ";
        $result = $this->getDI()->get('db')->execute($sql);
        if ($result) {
            return true;
        }else {
            return false;
        }
    }



}