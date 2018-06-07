<?php

namespace Marser\App\Backend\Repositories;
use \Marser\App\Backend\Repositories\BaseRepository;

class PositionRelaDsp extends BaseRepository{

    public function __construct(){
        parent::__construct();
    }

     public function get_list_by_ad_pos_id($aid, $ext=array() ) {
     	return $this -> get_model('PositionRelaDspModel') -> get_list_by_ad_pos_id($aid, $ext);
     }

     public function get_dsp_info($aid,$dsp_id) {
        return $this -> get_model('PositionRelaDspModel') -> get_dsp_info($aid, $dsp_id);
     }

    public function get_dsp_info_ext($dsp_id,$dsp_ad_pos_id) {
        return $this -> get_model('PositionRelaDspModel') -> get_dsp_info_ext($dsp_id,$dsp_ad_pos_id);
    }

    /**
     * 数据入库
     * @param array $data
     * @return int
     * @throws \Exception
     */
    public function create(array $data){
        /** 添加媒体 */
        $result = $this -> get_model('PositionRelaDspModel') -> insert_record($data);
        if(!$result){
            throw new \Exception('入库失败');
        }
        return $result;
    }

    public function update(array $data){
        /** 添加媒体 */
        $result = $this -> get_model('PositionRelaDspModel') -> update_record($data);
        if(!$result){
            throw new \Exception('更新失败');
        }
        return $result;
    }
}