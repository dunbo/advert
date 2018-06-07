<?php

/**
 * 媒体行业模型
 * @category PhalconDSP
 * @author haoshisuo 2017-9-21
 */

namespace Marser\App\Frontend\Models;
use \Marser\App\Frontend\Models\BaseModel;

class IndustryModel extends BaseModel{

    const TABLE_NAME = 'pu_industry';

    public function initialize(){
        parent::initialize();
        $this -> setSource(self::TABLE_NAME);
    }

    // 获取所有媒体行业信息
    public function select(){
        $result = $this->find();
    	return $result;
    }

    public function get_info($id){
        if(empty($id)){
            throw new \Exception('参数错误');
        }
        $params = array(
            'columns' => '*',
            'conditions' => 'id = :id:',
            'bind' => array(
                'id' => $id,
            ),
        );
        $result = $this -> find($params)->toArray();
        return $result[0];
    }
}