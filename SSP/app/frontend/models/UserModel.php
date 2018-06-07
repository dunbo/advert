<?php

/**
 * 用户model
 */

namespace Marser\App\Frontend\Models;
use \Marser\App\Frontend\Models\BaseModel;

class UserModel extends BaseModel{

    const TABLE_NAME = 'media_user';

    public function initialize(){
        parent::initialize();
        $this -> set_table_source(self::TABLE_NAME);
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
                'username'	=>	$username,
            ),
        );
        if(isset($ext['columns']) && !empty($ext['columns'])){
            $params['columns'] = $ext['columns'];
        }
        $result = $this -> findfirst($params);
        if(!$result){
            throw new \Exception('用户名不存在');
        }
        return $result;
    }


    public function get_user_by_muid($muid, array $ext=array()){
        if(empty($muid)){
            throw new \Exception('参数错误');
        }
        $params = array(
            'conditions' => 'muid = :muid:',
            'bind' => array(
                'muid'  =>  $muid,
            ),
        );
        if(isset($ext['columns']) && !empty($ext['columns'])){
            $params['columns'] = $ext['columns'];
        }
        $result = $this -> find($params)->toArray();
        //return isset($result[0])?$result[0]:array();
        return @$result[0];
    }

    /**
     * 自定义的update事件
     * @param array $data
     * @return array
     */
    protected function before_update(array $data){
        if(empty($data['modify_time'])){
            $data['modify_time'] = date('Y-m-d H:i:s');
        }
        return $data;
    }

    /**
     * 更新用户数据
     * @param array $data
     * @param int $muid
     * @return int
     * @throws \Exception
     */
    public function update_record(array $data, $muid){
        $muid = intval($muid);
        if(count($data) == 0 || $muid <= 0){
            throw new \Exception('参数错误');
        }
        $data = $this -> before_update($data);

        $this -> muid = $muid;
        $result = $this -> iupdate($data);
        if(!$result){
            throw new \Exception('更新失败');
        }
        $affectedRows = $this -> db -> affectedRows();
        return $affectedRows;
    }

    public function insert_record(array $data){
        if(count($data) == 0){
            throw new \Exception('参数错误');
        }
        $data['update_tm'] = time();
        $data['create_tm'] = time();
        $clone = clone $this;
        $result = $clone -> create($data);
        if(!$result){
            throw new \Exception(implode(',', $this -> getMessages()));
        }
        $muid = $clone -> muid;
        return $muid;
    }

    /**
     * 媒体主是否存在
     * @param null $name
     * @param null $mmid
     */
    public function username_is_exist($username, $muid=0){
        if(empty($username)){
            throw new \Exception('参数错误');
        }
        $params = array();
        $params['conditions'] = "username = :username: ";
        $params['bind']['username'] = $username;
        $muid > 0 && $params['conditions'] .= " and muid != {$muid} ";
        $result = $this -> find($params);
        return $result;
    }

}