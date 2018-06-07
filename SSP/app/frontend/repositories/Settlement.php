<?php

namespace Marser\App\Frontend\Repositories;

use \Marser\App\Frontend\Repositories\BaseRepository;

class Settlement extends BaseRepository{

    public function __construct(){
        parent::__construct();
    }

     /**
     * 财务统计列表
     * @param int $status
     * @param array $ext
     * @return array
     * @throws \Exception
     */
    public function get_list($page, $pagesize=10, array $ext=array()){
        $list = $this -> get_model('SettlementModel') -> get_list($page, $pagesize, $ext);
        return $list;
    }
    //账户余额
    public function get_account_mount($muid){
        return $this -> get_model('SettlementModel') -> get_account_mount($muid);
    }
    //可提现金额以及结算账单数
    public function get_amount_in_cash($muid){
        return $this -> get_model('SettlementModel') -> get_amount_in_cash($muid);
        
    }
}