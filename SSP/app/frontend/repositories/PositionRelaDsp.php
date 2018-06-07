<?php

namespace Marser\App\Frontend\Repositories;

use \Marser\App\Frontend\Repositories\BaseRepository;

class PositionRelaDsp extends BaseRepository{

    public function __construct(){
        parent::__construct();
    }

    /**
     * 删除
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public function delete($id){
        $id = intval($id);
        if($id <= 0){
            throw new \Exception('请选择需要删除的广告位');
        }
        $affectedRows = $this -> get_model('PositionRelaDspModel') -> delete_record($id);
        return $affectedRows;
    }

  

}