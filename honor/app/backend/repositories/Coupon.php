<?php

namespace Marser\App\Backend\Repositories;

use \Marser\App\Backend\Repositories\BaseRepository;

class Coupon extends BaseRepository{

    public function __construct(){
        parent::__construct();
    }

    public function get_list($aid,$awardid){
        return $this -> get_model('CouponModel') -> get_list($aid, $awardid);
    }

    public function get_count($aid, $id){
        return $this -> get_model('CouponModel') -> get_count($aid, $id);
    }

    public function get_Coupon_info($id){
        $rows = $this -> get_model('CouponModel') -> get_Coupon_info($id);
        return $rows;
    }

    public function save(array $data, $id){
        $id = intval($id);
        if($id <= 0){
            return $this -> create($data);
        }else{
            return $this -> update($data, $id);
        }
    }

 	public function delete($aid,$id){
 		$res = $this -> get_model('CouponModel') -> delete_record($aid, $id);
 		return $res;
 	}


    /**
     * 优惠券数据入库
     * @param array $data
     * @return int
     * @throws \Exception
     */
    protected function create(array $data){
        /** 添加优惠券 */
        $id = $this -> get_model('CouponModel') -> insert_record($data);
        $id = intval($id);
        if($id <= 0){
            throw new \Exception('优惠券数据入库失败');
        }
        return $id;
    }

    /**
     * 更新优惠券数据
     * @param array $data
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    protected function update(array $data, $id){
        /** 更新优惠券 */
        $affectedRows = $this -> get_model('CouponModel') -> update_record($data, $id);
        if($affectedRows <= 0){
            throw new \Exception('更新优惠券失败');
        }
        return $affectedRows;
    }

    public function batch_add($str){
    	$result = $this -> get_model('CouponModel') -> batch_add($str);
        return $result;
    }

}