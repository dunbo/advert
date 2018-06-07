<?php

/**
 * 广告计划仓库
 * @category PhalconDSP
 * @author haoshisuo 2017-9-15
 */

namespace Marser\App\Frontend\Repositories;

use \Marser\App\Frontend\Repositories\BaseRepository;

class Materiel extends BaseRepository{

    public function __construct(){
        parent::__construct();
    }

    /**
     * 获取广告计划列表
     * @param int $page
     * @param int $pagesize
     * @param array $ext
     * @return mixed
     */
    public function get_list($page, $pagesize=10, array $ext=array()){
        $result = $this -> get_model('MaterielModel') -> get_list($page, $pagesize, $ext);
        return $result;
    }

    //获取一条信息
    public function get_info($id){
    	return $this -> get_model('MaterielModel') -> get_info($id);
    }

    //关联广告数据表曝光量等字段
    public function append_columns($result,$ext){
    	$results = $this -> get_model('DataModel') -> append_columns($result,$ext);
        return $results;
    }

    //添加、编辑广告操作
    public function save($map, $id=null){
    	$id = intval($id);
        if(empty($id)){
            // 新增广告
            $lastId = $this -> get_model('MaterielModel') -> insert_record($map);
            return $lastId;
        }else{
            // 更新广告
            $affectedRows = $this -> get_model('MaterielModel') -> update_record($map, $id);
            $affectedRows = intval($affectedRows);
            return $affectedRows;
        }
    }

    //检测广告计划名称是否存在
    public function check_name($map, $id, $isAjax=0){
        $isExist = $this -> get_model('MaterielModel') -> check_exist(array('name'=>$map['name']), $id);
        if($isExist && $isExist -> count() > 0){
            if(!empty($isAjax)){
                return 2;
            }else{
                throw new \Exception('广告计划名称已存在');
            }
        }
        return 1;
    }

    //过滤地区重复编号
    public function filter_area_sp($area_sp){
        //11,130000,130100;11,130000,130200;1,110000,;1,120000,120100
        $area_arr = explode(';', $area_sp); //初始数组
        $area_arr = array_unique($area_arr);
        $final_arr = array(); //最终数组
        $tmp_arr = array(); //临时数组
        foreach($area_arr as $key => $val){
            $val = explode(',', $val);
            //只选了省，没选市，省编号直接通过
            if(empty($val[2])){
                $final_arr[] = $val[1];
                continue;
            }
            //省编号为键，长度和市编号为值
            $tmp_arr[$val[1]]['len'] = $val[0];
            $tmp_arr[$val[1]][] = $val[2];
        }
        foreach($final_arr as $zhi){
            if(empty($tmp_arr[$zhi])){
                continue;
            }
            unset($tmp_arr[$zhi]);
        }
        if(!empty($tmp_arr)){
            foreach($tmp_arr as $k => $v){
                if(count($v) == ($v['len']+1)){ //市编号数量等于省长度，省编号直接通过
                    $final_arr[] = $k;
                    continue;
                }
                foreach ($v as $kk => $vv) {
                    if($kk === 'len'){
                        continue;
                    }
                    $final_arr[] = $vv; //市编号通过
                }
            }
        }
        return implode(',', $final_arr);
    }

    //广告计划统计
    public function get_status_count($auid) {
        return $this -> get_model('MaterielModel') -> get_status_count($auid);
    }

    //id串
    public function batch_opertion($ids, $ext){
        $data = $this -> get_model('MaterielModel') -> batch_data($ids);
        foreach ($data as $key => $val) {
            switch ($ext['type']) {
                case 'del':
                    if($val['examine_status'] == 1) {
                        throw new \Exception("id为{$val['id']}广告计划处于审核中，不可【删除】");
                    }
                    $time = time();
                    if($val['examine_status'] == 2 && $val['switch'] == 1 && ( ($val['tf_date_type']==1 && $val['begin_tm']<$time) || ($val['tf_date_type']==2 && $val['begin_tm'] <$time && $val['end_tm']>$time)   ) ) {
                        throw new \Exception("id为{$val['id']}广告计划处于投放中，不可【删除】");
                    }
                    break;
                case 'dt':
                     if($val['examine_status'] == 1) {
                        throw new \Exception("id为{$val['id']}广告计划处于审核中,不可【修改排期】");
                    }
                    break;
                case 'pr':
                    if($val['examine_status']==1) {
                        throw new \Exception("id为{$val['id']}广告计划审核中,不可【修改单价】");
                    }
                    if( !$val['ad_industryid'] ){
                        throw new \Exception("id为{$val['id']}广告计划后台管理人员未选择行业");
                    }
                    //暂时关闭行业底价验证
                    // $industry  =  $this -> get_model('IndustryModel') -> get_info($val['ad_industryid']);
                    // if($ext['price'] < $industry['price']) {
                    //      throw new \Exception("id为{$val['id']}的广告单价不可低于行业底价");
                    // }
                    break;
            }
        }
        foreach ($data as $kk => $vv) {
            switch ($ext['type']) {
                case 'del'://删除
                    $this -> get_model('MaterielModel') -> update_record_ext(array('examine_status'=>0), $vv['id']);
                    break;
                case 'dt'://排期
                    $this -> get_model('MaterielModel') -> update_record_ext(array('tf_date_type'=>$ext['tf_date_type'],'begin_tm'=>$ext['begin_tm'],'end_tm'=>$ext['end_tm']), $vv['id']);
                    break;
                case 'pr'://单价
                    $this -> get_model('MaterielModel') -> update_record_ext(array('price'=>$ext['price']), $vv['id']);
                    break;
                case 'st'://状态
                    $this -> get_model('MaterielModel') -> update_record_ext(array('switch'=>$ext['switch']),$vv['id']);
                    break;
                default:
                    throw new \Exception("操作有误");
                    break;
            }
        }
        return true;
    }


}