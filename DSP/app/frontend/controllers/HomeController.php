<?php

/**
 * 广告计划控制器
 * @category PhalconDSP
 * @author haoshisuo 2017-9-18
 */

namespace Marser\App\Frontend\Controllers;

use \Marser\App\Frontend\Controllers\BaseController;
use \Marser\App\Frontend\Models\ModelFactory;
use \Marser\App\Helpers\PaginatorHelper;
use \Marser\App\Helpers\Common;

class HomeController extends BaseController{

    public function initialize(){
        parent::initialize();
        $this -> view -> setVars(array(
            'prefix' => 'home',
        ));
    }

    public function indexAction(){
        $tag = $this -> request -> get('tag', 'int', 1); //统计数据天数
        $ext = array('tag' => $tag);
        //用户余额
        $balance = $this -> get_repository('Balance') -> get_balance($this->userinfo['auid']);
       // print_r($balance);
       // echo "调试中";die;
        //广告计划统计
        $ad_count = $this -> get_repository('Materiel') -> get_status_count($this->userinfo['auid']);
        //广告流水统计
        $statistics  = $this -> get_repository('Data') -> get_count($this->userinfo['auid'],$ext);
        //统计图
        $char_date = array();
        $chart_list  = $this -> get_repository('Data') -> get_chart($this->userinfo['auid'],$ext);
        $chart_arr = array();
        foreach ($chart_list as $key => $val) {
            $char_arr['exposure'][]    = intval($val['exposure']);
            $char_arr['click_num'][]   = intval($val['click_num']);
            $char_arr['click_rate'][]  = Common::click_rate($val['click_num'], $val['exposure']);
            $char_arr['total'][]       = intval($val['total'])/100;
            $char_arr['create_date'][] = $val['create_date'];
        }
        $chart_data_json['exposure']    = json_encode($char_arr['exposure']);
        $chart_data_json['click_num']   = json_encode($char_arr['click_num']);
        $chart_data_json['click_rate']  = json_encode($char_arr['click_rate']);
        $chart_data_json['total']       = json_encode($char_arr['total']);
        $chart_data_json['create_date'] = json_encode($char_arr['create_date']);
        $this -> view -> setVars(array(
            'tag'        =>  $tag,
            'balance'    =>  $balance,
            'ad_count'   =>  $ad_count,
            'chart_data_json'  =>  $chart_data_json,
            'statistics' =>  $statistics,
        ));
        $this -> view -> pick('home/index');
    }

}