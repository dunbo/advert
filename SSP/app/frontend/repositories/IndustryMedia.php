<?php

namespace Marser\App\Frontend\Repositories;

use \Marser\App\Frontend\Repositories\BaseRepository;

class IndustryMedia extends BaseRepository{

    public function __construct(){
        parent::__construct();
    }

    public function get_list($pid, array $ext=array()){
        $list = $this -> get_model('IndustryMediaModel') -> get_list($pid, $ext);
        return $list;
    }

    public function get_sub_list(array $ext=array()){
        $list = $this -> get_model('IndustryMediaModel') -> get_sub_list($ext);
        return $list;
    }

	public function get_sub_count($id){
		$list = $this -> get_model('IndustryMediaModel') -> get_sub_count($id);
        return $list;
	}

    public function get_info($id){
        $list = $this -> get_model('IndustryMediaModel') -> get_info($id);
        return $list;
    }
}