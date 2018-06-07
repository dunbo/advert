<?php

namespace Marser\App\Frontend\Repositories;

use \Marser\App\Frontend\Repositories\BaseRepository;

class Industry extends BaseRepository{

    public function __construct(){
        parent::__construct();
    }

    public function get_list(array $ext=array()){
        $list = $this -> get_model('IndustryModel') -> get_list($ext);
        return $list;
    }

	public function get_sub_count($id){
		$list = $this -> get_model('IndustryModel') -> get_sub_count($id);
        return $list;
	}

    public function get_info($id){
        $list = $this -> get_model('IndustryModel') -> get_info($id);
        return $list;
    }
}