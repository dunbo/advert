<?php

namespace Marser\App\Backend\Repositories;

use \Marser\App\Backend\Repositories\BaseRepository;

class Industry extends BaseRepository{

    public function __construct(){
        parent::__construct();
    }

    public function get_list(array $ext=array()){
        $list = $this -> get_model('IndustryModel') -> get_list($ext);
        return $list;
    }

    public function get_info($id){
        $list = $this -> get_model('IndustryModel') -> get_info($id);
        return $list;
    }
}