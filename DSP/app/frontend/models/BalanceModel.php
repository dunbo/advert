<?php

/**
 * 账户余额模型
 * @category PhalconDSP
 * @author haoshisuo 2017-10-13
 */

namespace Marser\App\Frontend\Models;
use \Marser\App\Frontend\Models\BaseModel;

class BalanceModel extends BaseModel{

    const TABLE_NAME = 'ad_user_balance';

    public function initialize(){
        parent::initialize();
        $this -> setConnectionService('db2');  
        $this -> setSource(self::TABLE_NAME);
    }

    //获取账户余额
    public function get_balance($auid){
        $one = $this->find(array(
            'conditions' => "auid = :auid:",
            'bind' => array('auid' => $auid),
            'columns'    => '*',
        ));
        return $one->toArray()[0];
    }
}