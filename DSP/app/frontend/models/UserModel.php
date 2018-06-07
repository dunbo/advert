<?php

/**
 * 用户model
 * @category PhalconDSP
 * @author haoshisuo 2017-9-13
 */

namespace Marser\App\Frontend\Models;
use \Marser\App\Frontend\Models\BaseModel;

class UserModel extends BaseModel{

    const TABLE_NAME = 'ad_user';

    public function initialize(){
        parent::initialize();
        $this -> setSource(self::TABLE_NAME);
    }

    /**
     * 获取用户数据
     * @param $username
     * @param array $ext
     * @return \Phalcon\Mvc\Model
     * @throws \Exception
     */
    public function detail($username, array $ext=array()){
        if(empty($username)){
            throw new \Exception('参数错误');
        }
        $params = array(
            'conditions' => 'username = :username:',
            'bind' => array(
                'username' => $username,
            ),
        );
        if(isset($ext['columns']) && !empty($ext['columns'])){
            $params['columns'] = $ext['columns'];
        }
        $result = $this -> findFirst($params);
        if(!$result){
            throw new \Exception('获取用户信息失败');
        }
        return $result;
    }

    // 根据id查询一条记录
    public function get_One($auid){
        $result = $this -> findFirst(array(
            'conditions' => 'auid = :auid:',
            'bind' => array(
                'auid' => $auid,
            ),
        ));
        if(!$result){
            throw new \Exception('获取用户信息失败');
        }
        return $result;
    }
}