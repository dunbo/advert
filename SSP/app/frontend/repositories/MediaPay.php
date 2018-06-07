<?php

namespace Marser\App\Frontend\Repositories;

use \Marser\App\Frontend\Repositories\BaseRepository;

class MediaPay extends BaseRepository{

    public function __construct(){
        parent::__construct();
    }

     /**
     * 提现明细列表
     * @param int $status
     * @param array $ext
     * @return array
     * @throws \Exception
     */
    public function get_list($page, $pagesize=10, array $ext=array()){
        $list = $this -> get_model('MediaPayModel') -> get_list($page, $pagesize, $ext);
        return $list;
    }

    /**
     * 财务统计数据入库
     * @param array $data
     * @return int
     * @throws \Exception
     */
    protected function create(array $data){
        $mmid = $this -> get_model('MediaPayModel') -> insert_record($data);
        $mmid = intval($mmid);
        if($mmid <= 0){
            throw new \Exception('提现入库失败');
        }
        return $mmid;
    }

}