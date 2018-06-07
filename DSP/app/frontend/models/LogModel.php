<?php

/**
 * 广告日志模型
 * @category PhalconDSP
 * @author haoshisuo 2017-10-22
 */

namespace Marser\App\Frontend\Models;

use \Marser\App\Frontend\Models\BaseModel;

class LogModel extends BaseModel{

    const TABLE_NAME = 'ad_log';

    public function initialize(){
        parent::initialize();
        $this -> setSource(self::TABLE_NAME);
    }

    // 插入广告主操作日志
    public function insert_record(array $data){
        if(!is_array($data) || count($data) == 0){
            throw new \Exception('参数错误');
        }
        $clone = clone $this;
        $result = $clone -> create($data);
        if(!$result){
            throw new \Exception(implode(',', $this -> getMessages()));
        }
        $id = $clone -> ad_log_id;
        return $id;
    }

}