<?php

namespace Marser\App\Frontend\Repositories;

use \Marser\App\Frontend\Repositories\BaseRepository;

class Statistics extends BaseRepository{

    public function __construct(){
        parent::__construct();
    }

    public function get_chart($muid, $start_tm, $end_tm){
        $list = $this -> get_model('StatisticsModel') -> get_chart($muid, $start_tm, $end_tm);
        return $list;
    }

    /**
     * 获取媒体统计列表或广告位统计列表
     */
    public function get_list($page, $pagesize=10, array $ext=array()){
        $list = $this -> get_model('StatisticsModel') -> get_list($page, $pagesize, $ext);
        return $list;
    }

    /**
     * 获取媒体统计列表或广告位每天统计列表
     */
    public function get_days_list($page, $pagesize=10, array $ext=array()){
        $list = $this -> get_model('StatisticsModel') -> get_days_list($page, $pagesize, $ext);
        return $list;
    }

    /**
     * 获取根据时间分组下的媒体活挂告位数据
     */
    public function get_group_days_list($page, $pagesize=10, array $ext=array()){
        $list = $this -> get_model('StatisticsModel') -> get_group_days_list($page, $pagesize, $ext);
        return $list;
    }
}
