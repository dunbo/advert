<?php

namespace Marser\App\Frontend\Repositories;

use \Marser\App\Frontend\Repositories\BaseRepository;

class Balance extends BaseRepository{

    public function __construct(){
        parent::__construct();
    }

    public function get_balance($auid){
        return $this -> get_model('BalanceModel') -> get_balance($auid);
    	
    }

}