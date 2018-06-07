<?php

namespace Marser\App\Frontend\Repositories;

use \Marser\App\Frontend\Repositories\BaseRepository;

class Log extends BaseRepository{

    public function __construct(){
        parent::__construct();
    }

    /**
     * 保存媒体
     * @param array $data
     * @param $mmid
     * @return bool|int
     */
    public function writelog($muid, $actionexp){
        return $this -> get_model('LogModel') -> insert_record($muid, $actionexp);
    }

}