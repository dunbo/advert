<?php

/**
 * 行业信息仓库
 * @category PhalconDSP
 * @author haoshisuo 2017-9-21
 */

namespace Marser\App\Frontend\Repositories;

use \Marser\App\Frontend\Repositories\BaseRepository;

class Industry extends BaseRepository{

    public function __construct(){
        parent::__construct();
    }

    /**
     * 获取所有行业
     * @return mixed
     */
    public function select(){
        $result = $this -> get_model('IndustryModel') -> select();
        return $result->toArray();
    }
}