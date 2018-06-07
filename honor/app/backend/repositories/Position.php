<?php

namespace Marser\App\Backend\Repositories;

use \Marser\App\Backend\Repositories\BaseRepository;

class Position extends BaseRepository{

    public function __construct(){
        parent::__construct();
    }

     /**
     * 广告位
     * @param int $status
     * @param array $ext
     * @return array
     * @throws \Exception
     */
    public function get_list($page, $pagesize=10, array $ext=array()){
        $list = $this -> get_model('PositionModel') -> get_list($page, $pagesize, $ext);
        return $list;
    }

    //获取某个媒体下的广告位
    public function get_list_by_mmid($muid, $mmid, $ext=array() ) {
        return $this -> get_model('PositionModel') -> get_list_by_mmid($muid, $mmid, $ext);
    }

     /**
     * 根据id获取信息
     * @param $tagname
     * @return int
     * @throws \Exception
     */
    public function get_position_by_id($id){
        $rows = $this -> get_model('PositionModel') -> get_position_by_id($id);
        return $rows;
    }

    /**
     * 保存广告位
     * @param array $data
     * @param $id
     * @return bool|int
     */
    public function save(array $data, $id){
        $id = intval($id);
        if($id <= 0){
            /** 添加广告位 */
            return  $this -> create($data);
        }else{
            /** 更新广告位 */
            return $this -> update($data, $id);
        }
    }

    /**
     * 删除广告位（软删除）
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public function delete($id){
        $id = intval($id);
        if($id <= 0){
            throw new \Exception('请选择需要删除的广告位');
        }
        $affectedRows = $this -> get_model('PositionModel') -> update_record(array(
            'status' => 0
        ), $id);
        if($affectedRows <= 0){
            throw new \Exception('删除广告位失败');
        }
        return $affectedRows;
    }

    /**
     * 广告位开关
     * @param $id
     * @param $status
     * @return mixed
     * @throws \Exception
     */
    public function OnOff($id, $status) {
    	$id = intval($id);
    	if($id <= 0) {
    		throw new \Exception("请选择要操作的广告位");
    	}
    	$affectedRows = $this -> get_model('PositionModel') -> update_record(array(
    		'switch' => $status
    	), $id);
    	if($affectedRows <= 0) {
    		throw new \Exception("修改广告位开关失败");
    	}
    	return $affectedRows;
    }


    /**
     * 广告位数据入库
     * @param array $data
     * @return int
     * @throws \Exception
     */
    protected function create(array $data){
        // /** 判断广告位是否已存在 */
        $isExist = $this -> get_model('PositionModel') -> position_is_exist($data['name']);
        if($isExist && $isExist -> count() > 0){
            throw new \Exception('广告位名称已存在');
        }
        /** 添加广告位 */
        $id = $this -> get_model('PositionModel') -> insert_record($data);
        $id = intval($id);
        if($id <= 0){
            throw new \Exception('广告位数据入库失败');
        }
        return $id;
    }

    /**
     * 更新广告位数据
     * @param array $data
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    protected function update(array $data, $id){
        // /** 判断广告位是否已存在 */
        // $isExist = $this -> get_model('PositionModel') -> position_is_exist($data['name'], $id);
        // if($isExist && $isExist -> count() > 0){
        //     throw new \Exception('广告位名称已存在');
        // }
        /** 更新广告位 */
        $affectedRows = $this -> get_model('PositionModel') -> update_record($data, $id);
        if($affectedRows <= 0){
            throw new \Exception('更新广告位失败');
        }
        return $affectedRows;
    }

    public function batch_update_record($ext, $ids){
        $id_arr = explode(',', $ids);
        if(count($id_arr) == 1){
            $affectedRows = $this -> get_model('PositionModel') -> update_record($ext, $ids);
            $affectedRows = intval($affectedRows);
            $info  =  $this ->  get_position_by_id($ids);
            //通过广告位时是h5类型商业内容API的增加appid和dspid
            if($ext['type'] == 'tg' && $info['type'] == 2 && $info['ad_style'] == 1) {
                $appid = $this -> get_model('MediaAppidDspModel')->insert_record(array('create_tm'=>time()));
                $dspid = $this -> get_model('MediaAppidDspModel')->insert_record(array('create_tm'=>time()));
                $data = array(
                    'ad_pos_id'     =>  $ids,
                    'dsp_ad_pos_id' =>  $dspid,
                    'dsp_id'        =>  2,
                    'dsp_appid'     =>  $appid,
                    'update_tm'     =>  time(),  
                );
               $this -> get_model('PositionRelaDspModel')->insert_record($data);
            }
            return $affectedRows;
        }else if(count($id_arr) > 1){
            $return = 0;
            foreach($id_arr as $id){
                $affectedRows = $this -> get_model('PositionModel') -> update_record($ext, $id);
                $affectedRows = intval($affectedRows);
                $return += $affectedRows;
                $info  =  $this ->  get_position_by_id($id);
                //h5类型商业内容API的增加appid和dspid
                if($ext['type'] == 'tg' && $info['type'] == 2 && $info['ad_style'] == 1) {
                    $appid = $this -> get_model('MediaAppidDspModel')->insert_record(array('create_tm'=>time()));
                    $dspid = $this -> get_model('MediaAppidDspModel')->insert_record(array('create_tm'=>time()));
                    $data = array(
                        'ad_pos_id'     =>  $id,
                        'dsp_ad_pos_id' =>  $dspid,
                        'dsp_id'        =>  2,
                        'dsp_appid'     =>  $appid,
                        'update_tm'     =>  time(),  
                    );
                   $this -> get_model('PositionRelaDspModel')->insert_record($data);
                }
            }
            return $return;
        }
    }

    public function get_detail($id){
        $rows = $this -> get_model('PositionModel') -> get_detail($id);
        return $rows;
    }

    public function batch_export($ids){
        $result = $this -> get_model('PositionModel') -> batch_export($ids);
        return $result;
    }

    public function get_position_rela_dsp($name){
        $result = $this -> get_model('PositionModel') -> get_position_rela_dsp($name);
        return $result; 
    }
    

}