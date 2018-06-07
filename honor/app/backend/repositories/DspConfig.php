<?php

namespace Marser\App\Backend\Repositories;

use \Marser\App\Backend\Repositories\BaseRepository;

class DspConfig extends BaseRepository{

    public function __construct(){
        parent::__construct();
    }

    public function get_dsp_by_name($name){
        $list = $this -> get_model('DspConfigModel') -> get_dsp_by_name($name);
        return $list;
    }
    
    public function get_info($id){
        $list = $this -> get_model('DspConfigModel') -> get_info($id);
        return $list;
    }

}