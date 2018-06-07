<?php
/**
 * Config
 */
namespace Marser\App\Backend\Controllers;

use \Marser\App\Backend\Controllers\BaseController;
use \Marser\App\Helpers\PaginatorHelper;
use \Marser\App\Backend\Models\ModelFactory;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;

class ConfigController extends BaseController{

    /**
     * 行业配置
     */
    public function industryAction(){
        if($this -> request -> isPost()){
            try{
                $set_price = $this -> request -> getPost('set_price', 'trim');
                $diff_price = $this -> request -> getPost('diff_price', 'trim');
                $set_id = $this -> request -> getPost('set_id', 'trim');
                $this -> validator -> add_rule('set_price', 'required', '行业底价不能为空')
                    -> add_rule('set_price', 'is_price', '请填写正确的价格')
                    -> add_rule('diff_price', 'required', '行业底价不能为空')
                    -> add_rule('diff_price', 'is_price', '请填写正确的价格');
                if ($error = $this -> validator -> run(array(
                    'set_price'=>$set_price,
                    'diff_price'=>$diff_price,
                ))) {
                    $error = array_values($error);
                    $error = $error[0];
                    throw new \Exception($error['message'], $error['code']);
                }
                if($set_price>1000000||$diff_price>1000000){
                    throw new \Exception('您输入的金额超过了上限，请重新输入');
                }

                $IndustryModel = ModelFactory::get_model('IndustryModel');
                $data['price']=$set_price*100;
                $data['diff_price']=$diff_price*100;
                $id = $IndustryModel->update_record($data,$set_id);
                if($id < 0){
                    throw new \Exception('编辑行业底价失败');
                }
                $this -> writelog("编辑了id为{$set_id}的行业底价,变更为{$set_price},行业差价,变更为{$diff_price}", 'pu_industry', $id, 'edit');
                $this -> flashSession -> success('编辑成功');
            
            }catch(\Exception $e){
                $this -> write_exception_log($e);

                $this -> flashSession -> error($e -> getMessage());
            }
            return $this -> redirect('config/industry');
        }


        $page       =   $this -> request -> get('page', 'int', 1);
        $pagesize = 10;
        $IndustryModel = ModelFactory::get_model('IndustryModel');
        $list = $IndustryModel->getlistbyjoin();
        $paginator   = new PaginatorModel(
            array(
                "data"  => $list,
                "limit" => $pagesize,
                "page"  => $page
            )
        );
        $paginator_new = $paginator->getPaginate();

        $pageNum = PaginatorHelper::get_paginator($paginator_new->total_items, $page, $pagesize);
        $list = $paginator_new->items;
        $newlist = array();
        foreach ($list as $key => $value) {
            $newlist[$key]=$value->toArray();
        }

        $count = $paginator_new->total_items;

        $this -> view -> setVars(array(
            'paginator'     =>  $paginator_new,
            'pageNum'       =>  $pageNum,
            'list'          =>  $newlist,
            'page'          =>  $page,
            'pagesize'      =>  $pagesize,
            'title'      =>  '行业配置',
            'cur_page_num'  =>  $count
        ));
        $this -> view -> pick('config/industry');
    }


    /**
     * 基础配置
     */
    public function basicsAction(){
        $tab =  $this -> request -> get('tab', 'int', 1);
        $PuConfigModel = ModelFactory::get_model('PuConfigModel');
        if($this -> request -> isPost()){
            try{
                if($tab==2){
                    $config_value = array();
                    foreach ($_POST as $key => $val) {
                        $k_left = substr($key, 0, strlen($key)-2);
                        $k_right = substr($key, -1);
                        $config_value['t_pid_'.$k_right][$k_left] = $val;
                    }
                    $data['config_value'] = json_encode($config_value);
                    $data['config_key'] = 'basics_set_new';
                }else{
                    if($_POST['set_price']>1000000||$_POST['min_price']>1000000){
                        throw new \Exception('您输入的金额超过了最大限制，请重新输入');
                    }
                    $_POST['set_price'] = $_POST['set_price']*100;
                    $_POST['min_price'] = $_POST['min_price']*100;
                    $data['config_value']=json_encode($_POST);
                    $data['config_key'] = 'basics_set';
                }
                $id = $PuConfigModel->update_views($data);
                if($id < 0){
                    throw new \Exception('保存失败');
                }
                $this -> writelog("修改了基础配置,变更为".json_encode($_POST), 'pu_industry', $id, 'edit');
                $this -> flashSession -> success('保存成功');
            
            }catch(\Exception $e){
                $this -> write_exception_log($e);

                $this -> flashSession -> error($e -> getMessage());
            }
            return $this -> redirect('config/basics?tab='.$tab);
        }
        
        if($tab==1){
            $info=$PuConfigModel->get_info(array('config_key'=>'basics_set'));
            $info = json_decode($info[0]['config_value'],true);

            $this -> view -> setVars(array(
                'info'     =>  $info,
            ));
            $this -> view -> pick('config/basics');
        }elseif ($tab==2) {
            $info=$PuConfigModel->get_info(array('config_key'=>'basics_set_new'));
            $info = json_decode($info[0]['config_value'],true);

            $this -> view -> setVars(array(
                'info'     =>  $info,
            ));
            $this -> view -> pick('config/basics_new');
        }
    }

    /**
     * 热门标签配置
     */
    public function tagAction(){
        $page       =   $this -> request -> get('page', 'int', 1);
        $pagesize = 5;
        $TagGroupModel = ModelFactory::get_model('TagGroupModel');

        $paginator = $TagGroupModel->get_list($page, $pagesize);

        //$paginator = $this -> get_repository('Tag') -> get_list($page, $pagesize);

        $pageNum = PaginatorHelper::get_paginator($paginator->total_items, $page, $pagesize);
        $list = $paginator->items->toArray();//total_pages
        $new_arr = $list;
        foreach ($list as $key => $val) {
            $tmpstr='';
                foreach(json_decode($val['tags'],true) as $v){
                    $tmpstr.=$v['tag_name'].',';
                }
            $new_arr[$key][new_tags]=substr($tmpstr,0,-1);
        }
        $count = $paginator->items->count();

        $this -> view -> setVars(array(
            'paginator'     =>  $paginator,
            'pageNum'       =>  $pageNum,
            'list'          =>  $new_arr,
            'page'          =>  $page,
            'pagesize'      =>  $pagesize,
            'title'      =>  '热门标签配置',
            'cur_page_num'  =>  $count
        ));
        $this -> view -> pick('config/tag');
    }


    /**
     * 新增标签
     */
    public function savetagroupAction(){
        try{
            if($this -> request -> isAjax() || !$this -> request -> isPost()){
                throw new \Exception('非法请求');
            }
            $set_id = $this -> request -> getPost('set_id', 'trim');
            $tag_group_name = $this -> request -> getPost('tag_group_name', 'trim');

                $this -> validator -> add_rule('tag_group_name', 'max_length', '标签组名由0-20个字符组成', 20);
                if ($error = $this -> validator -> run(array(
                    'tag_group_name'=>$tag_group_name,
                ))) {
                    $error = array_values($error);
                    $error = $error[0];
                    throw new \Exception($error['message'], $error['code']);
                }

            $tag_group_rank = $this -> request -> getPost('tag_group_rank', 'trim');
            $tags = $this -> request -> getPost('tags', 'trim');
            $TagModel = ModelFactory::get_model('TagModel');
            $tags_arr = $TagModel->add_tags($tags);

            $TagGroupModel = ModelFactory::get_model('TagGroupModel');
            $data['name']=$tag_group_name;
            $data['rank']=$tag_group_rank;
            $data['tags']=json_encode($tags_arr);

            if($set_id==0){
                $id = $TagGroupModel->insert_record($data);
                if($id <= 0){
                    throw new \Exception('新增标签组失败');
                }
                $this -> writelog("新增了id为{$id}的标签组", 'pu_tag_group', $id, 'add');
                $this -> flashSession -> success('新增标签组成功');
            }else{
               $id = $TagGroupModel->update_record($data,$set_id);
                // if($id <= 0){
                //     throw new \Exception('编辑标签组失败');
               // }
                $this -> writelog("编辑了id为{$set_id}的标签组", 'pu_tag_group', $id, 'edit');
                $this -> flashSession -> success('编辑标签组成功');
            }


        }catch(\Exception $e){
            $this -> write_exception_log($e);

            $this -> flashSession -> error($e -> getMessage());
        }
        return $this -> redirect('config/tag');
    }
}
