<?php

namespace Marser\App\Backend\Repositories;

use \Marser\App\Backend\Repositories\BaseRepository;

class MediaAppidDsp extends BaseRepository{

    public function __construct(){
        parent::__construct();
    }
    protected function create(array $data){
        $id = $this -> get_model('MediaAppidDspModel') -> insert_record($data);
        $id = intval($id);
        if($id <= 0){
            throw new \Exception('数据异常');
        }
        return $id;
    }
 
}