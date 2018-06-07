<?php

/**
 * 屏蔽策略管理
 */
namespace Marser\App\Frontend\Controllers;

use \Marser\App\Frontend\Controllers\BaseController;
use \Marser\App\Helpers\PaginatorHelper;
use \Marser\App\Frontend\Models\ModelFactory;

class StrategyController extends BaseController{
    public function initialize(){
        $this -> view -> setVars(array(
            'prefix'    =>  'strategy',
        ));
        parent::initialize();
    }
    /**
     * 首页跳转
     */
    public function indexAction(){
        $page		=	$this -> request -> get('page', 'int', 1);
        $pagesize   =   $this -> request -> get('pagesize', 'int', 10);
        $keyword	=	$this -> request -> get('keyword', 'trim');
       
        /**分页获取屏蔽策略列表*/
        $paginator	=	$this -> get_repository('Strategy') -> get_list($page, $pagesize, array(
            'keyword'	=>	$keyword,
            'muid'      =>  $this->userinfo['muid'],
        ));

        /**获取分页页码*/
        $pageNum = PaginatorHelper::get_paginator($paginator->total_items, $page, $pagesize);
        /**获取屏蔽策略所属的分类ID*/
        $list  = $paginator->items->toArray();
        $count = $paginator->items->count();

        $this -> view -> setVars(array(
            'paginator'	=>	$paginator,
            'pageNum'	=>	$pageNum,
            'list'		=>	$list,
            'keyword'	=>	$keyword,
            'page'          =>  $page,
            'pagesize'      =>  $pagesize,
            'cur_page_num'  =>  $count,
        ));
        $this -> view -> pick('strategy/index');
    }

    /**
    * 编辑屏蔽策略
    */
    public function addAction() {
        $id   =  $this -> request -> get('id', 'int');
        if($id) {
            $rows = $this-> get_repository('Strategy') -> get_strategy_by_id($id);
        }
        $industry_edit = @explode(',', $rows['industry_json']);
        $tag_edit = @json_decode($rows['tag_json'], true);

        $industry = $this-> get_repository('Industry') -> get_list();
        
        $industry_arr = array();
        $industry_total_num = $industry_select_num = 0;
        foreach ($industry as $k => $v) {
            if( $v['parentid']  == 0 ) {
                $industry_arr[$k]['id']          = $v['id'];
                $industry_arr[$k]['parent_name'] = $v['name'];
                 $i = 0;
               foreach ($industry as $kk => $vv) {
                    if( $v['id'] == $vv['parentid'] ) {
                        $industry_arr[$k]['sub'][$kk]['id']         =   $vv['id'];
                        $industry_arr[$k]['sub'][$kk]['sub_name']   =   $vv['name'];
                        //检查节点是否被选中
                        if( in_array($vv['id'], $industry_edit) ) {
                            $industry_arr[$k]['sub'][$kk]['selected'] = 1;
                            $industry_select_num ++;
                            $i ++;
                        }else {
                            $industry_arr[$k]['sub'][$kk]['selected'] = 0;
                        }
                        $industry_total_num ++;
                    }
                }
                //编辑检查节点是否被选中
                $num = 0;
                foreach ($industry as $kkk => $vvv) {
                    if( $vvv['parentid'] == $v['id'] ) {
                        $num ++;
                    }
                }
                if( ($num - $i) > 0 ) {
                    $industry_arr[$k]['selected_left'] = 1; 
                }else {
                    $industry_arr[$k]['selected_left'] = 0;
                }
                if( in_array( $v['id'], $industry_edit) ) {
                    $industry_arr[$k]['selected'] = 1; 
                }else {
                    $industry_arr[$k]['selected'] = 0; 
                } 
            }
        }

        $tags = ModelFactory::get_model('TagGroupModel') -> get_all_taglist();
        $tags_arr = array();
        $tags_total_num = $tags_select_num= 0;
        foreach ($tags as $key => $val) {
            $tags_arr[$key]['group_id'] =   $val['group_id'];
            $tags_arr[$key]['name']     =   $val['name'];
            $tags_arr[$key]['rank']     =   $val['rank'];
            $tags_arr[$key]['status']   =   $val['status'];
            $sub_tags   =  json_decode($val['tags'], true);
            $j = 0;
            foreach ($sub_tags as $kk => $vv) {
                if( in_array($vv['tag_id'], $tag_edit[$val['group_id']]) ) {
                    $sub_tags[$kk]['selected'] = 1;
                    $tags_select_num ++;
                    $j ++;
                }else {
                    $sub_tags[$kk]['selected'] = 0;
                }
            }

            $num = count($sub_tags);
            if( ($num - $j) > 0 ) {
                $tags_arr[$key]['selected_left'] = 1; 
            }else {
                $tags_arr[$key]['selected_left'] = 0;
            }
            if( !empty($tag_edit[$val['group_id']]) ) {
                $tags_arr[$key]['selected'] = 1;
            }else {
                $tags_arr[$key]['selected'] = 0;
            }
            $tags_arr[$key]['tags'] = $sub_tags;
            $tags_total_num += count($sub_tags);
        }

        $this -> view -> setVars(array(
                    'id'            =>  $id,
                    'rows'          =>  $rows,
                    'industry'      =>  $industry_arr,
                    'list'          =>  $tags_arr,
                    'perfix'        =>  'strategy_add',
                    'industry_sy_num'      =>   $industry_total_num - $industry_select_num,
                    'industry_select_num'  =>   $industry_select_num,
                    'tags_sy_num'          =>   $tags_total_num - $tags_select_num,
                    'tags_select_num'      =>   $tags_select_num,
                ));
        $this -> view -> pick('strategy/add');
    }


    /**
     * 发布屏蔽策略（添加、编辑）
     */
    public function publishAction(){
        try{
            if($this -> request -> isAjax() || !$this -> request -> isPost()){
                throw new \Exception('非法请求');
            }
            $id			     =	$this -> request -> getPost('id', 'int');
            $name		     =	$this -> request -> getPost('name', 'trim');
            $shield_industry =  $this -> request -> getPost('shield_industry', 'int');
            $shield_tag      =  $this -> request -> getPost('shield_tag', 'int');
            $shield_url      =  $this -> request -> getPost('shield_url', 'int');
            $industry_json   =  $this -> request -> getPost('industry', 'trim');
            $url             =  $this -> request -> getPost('url', 'trim');
            $tag_parent_json =  $this -> request -> getPost('tag_parent', 'trim'); 
            $tag_sub_json    =  $this -> request -> getPost('tag_sub', 'trim');
           
           
            /** 添加验证规则 */
            $this -> validator -> add_rule('name', 'required', '请填写屏蔽策略名称')
                 -> add_rule('name', 'max_length', '请输入屏蔽策略名称，不超过20个字', 20);

             /** 截获验证异常 */
            $error = $this -> validator -> run(array(
						'name'	=>	$name
            ));
            if($error) {
                $error = array_values($error);
                $error = $error[0];
                throw new \Exception($error['message'], $error['code']);
            }

            $map = array(
                    'name'              =>  $name,
                    'shield_industry'   =>  $shield_industry,
                    'shield_tag'        =>   $shield_tag,
                    'status'            =>  1,
                    'muid'              =>  $this->userinfo['muid'],
                );

            if( $shield_url ) {
                $url_crc = sprintf("%u",crc32($url));
                $map['url']     = $url;
                $map['url_crc'] = $url_crc;
            }else {
                $map['url']     = '';
                $map['url_crc'] = '';
            }

            if( $shield_industry == 1 ) {
                $industry_arr = json_decode($industry_json, true);
                $parent_industry = $industry = array();
                foreach ($industry_arr as $k => $v) {
                    if( ($k%2) == 0  ) {
                        $parent_industry[] = $v;//行业一级
                    }else {
                        $industry[] = $v; //行业二级
                    }
                }

                $industry_db_ids = array();
                //获取父级下的数量
                foreach ($parent_industry as $k => $val) {
                    $tmp_count = $this-> get_repository('Industry') -> get_sub_count($val);
                    print_r($tmp_count);
                    $cur_sub_num = count($industry[$k]);
                    if( $tmp_count['count'] > $cur_sub_num ) {
                        //写子id
                        foreach ($industry[$k] as $vvv) {
                           $industry_db_ids[] = $vvv;
                        }
                    }else{
                        //写父id
                        $industry_db_ids[] = $val;
                    }
                }
                $industry_db_ids = $industry_db_ids?implode(',', $industry_db_ids):'';

                $result = array();
                foreach( $industry as $k=>$v ){
                    foreach ($v as $kk => $val) {
                        $result[] = $val;
                    }
                }
                $result = array_unique($result);
                $industry_id_arr    =   array_merge($parent_industry,$result);
                $industry_id_str    =   implode(',', array_unique($industry_id_arr));
                $map['industry']       =  $industry_db_ids;
                $map['industry_json']  =  $industry_id_str;
            }else {
                $map['industry']        =   '';
                $map['industry_json']   =   '';
            }
            if( $shield_tag == 1 ) {
                $tag_parent_arr =   json_decode($tag_parent_json, true);
                $tag_sub_arr    =   json_decode($tag_sub_json, true);
                $tag_result_arr = array();
                foreach ($tag_parent_arr as $k => $val) {
                    $tag_result_arr[$val] = $tag_sub_arr[$k];
                }
                $tag_result_json    =   !empty($tag_result_arr)?json_encode($tag_result_arr):''; 
                $tag_sub            =   array_reduce($tag_sub_arr, 'array_merge', array());
                $tag_sub_str        =   implode(',', array_unique($tag_sub));
                $map['tag']         =  $tag_sub_str;
                $map['tag_json']    =  $tag_result_json;
            }else {
                $map['tag']         =   '';
                $map['tag_json']    =   '';
            }

            /** 发布屏蔽策略 */
            $return = $this -> get_repository('Strategy') -> save($map, $id);
            //日志
            $log_data = json_encode($_POST);
            if( $id ) {
                $this -> get_repository('Log') -> writelog($this->userinfo['muid'],"编辑了屏蔽策略id为{$id}。{$log_data}");
            }else {
                $this -> get_repository('Log') -> writelog($this->userinfo['muid'],"添加了屏蔽策略id为{$return}。{$log_data}");
            }
            $this -> flashSession -> success('发布屏蔽策略成功');
        }catch(\Exception $e){
            $this -> write_exception_log($e);
            $this -> flashSession -> error($e -> getMessage());
        }

        return $this -> redirect('Strategy/index');
    }


    /**
     * 屏蔽策略删除
     */
    public function deleteAction(){
        try{
            $strategyid = intval($this -> request -> get('strategyid', 'int'));
            //查看广告位上是否有该屏蔽策略关联
            $position_list = $this -> get_repository('Position') -> get_position_by_strategyid($strategyid);
            if( !empty($position_list) ) {
                 throw new \Exception('请先解除现有广告位与该屏蔽策略的关联');
            }
            $affectedRows = $this -> get_repository('Strategy') -> delete($strategyid);
            if(!$affectedRows){
                throw new \Exception('删除屏蔽策略失败');
            }
            $this -> flashSession -> success('删除屏蔽策略成功');
            $this -> get_repository('Log') -> writelog($this->userinfo['muid'],"删除了屏蔽策略strategyid为{$strategyid}");
            return $this -> redirect('Strategy/index');
        }catch(\Exception $e){
            $this -> write_exception_log($e);
            $this -> flashSession -> error($e -> getMessage());
            return $this -> redirect('Strategy/index');
        }
        
    }

}