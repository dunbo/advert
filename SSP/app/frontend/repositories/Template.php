<?php

namespace Marser\App\Frontend\Repositories;

use \Marser\App\Frontend\Repositories\BaseRepository;

class Template extends BaseRepository{

    public function __construct(){
        parent::__construct();
    }

     /**
     * 广告规格列表
     * @param int $status
     * @param array $ext
     * @return array
     * @throws \Exception
     */
    public function get_list(){
        $list = $this -> get_model('TemplateModel') -> get_list();
        return $list;
    }

    public function get_parent_list(){
        $list =  $this -> get_model('TemplateModel') -> get_parent_list();
        $data = array();
        foreach ($list as $val) {
            if($val['from_type'] == 1){
                $data[1][] = $val;
            }elseif($val['from_type'] == 2){
                $data[2][] = $val;
            }elseif($val['from_type'] == 3){
                $data[3][] = $val;
            }
        }
        return $data;
    }

    public function get_sub_list(){
         $list = $this -> get_model('TemplateModel') -> get_sub_list();
         return $list;
    }

}