<?php

namespace Marser\App\Frontend\Models;
use \Marser\App\Frontend\Models\BaseModel;

class PositionRelaDspModel extends BaseModel{

    const TABLE_NAME = 'media_ad_rela';

    public function initialize(){
        parent::initialize();
        $this -> set_table_source(self::TABLE_NAME);
    }

    /**
     * 删除
     * @param array $data
     * @param $tid
     * @return int
     * @throws \Exception
     */
    public function delete_record($id) {
        $id = intval($id);
        if($id <= 0) {
            throw new \Exception('参数错误');
        }
        $sql = "delete FROM media_ad_rela where ad_pos_id = {$id}";
        $result = $this->getDI()->get('db')->query($sql);
        return $result;
    }

}