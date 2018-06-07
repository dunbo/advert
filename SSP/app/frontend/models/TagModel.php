<?php
/**
 * 标签模型
 */
namespace Marser\App\Frontend\Models;

use \Marser\App\Frontend\Models\BaseModel,
    \Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class TagModel extends BaseModel{

    const TABLE_NAME = 'pu_tag';

    public function initialize(){
        parent::initialize();
        $this -> set_table_source(self::TABLE_NAME);
    }

    public function get_list($page, $pagesize=10, array $ext=array()){
        $page = intval($page);
        $page <= 0 && $page = 1;
        $pagesize = intval($pagesize);
       	$builder = $this->getModelsManager()->createBuilder();
        $builder->from( __CLASS__);
       	$builder->columns(array('*'));
       	$builder->where('status = :status:', array('status' => 1));

        $builder->orderBy('group_id DESC');
        $paginator = new PaginatorQueryBuilder(array(
            'builder'   =>  $builder,
            'limit'     =>  $pagesize,
            'page'      =>  $page,
        ));
        $result = $paginator->getPaginate();
        return $result;
    }


    public function add_tags_getids($tags){
        $new_tagstr ='';
        $tag_arr = explode(",",$tags);
        foreach($tag_arr as $v){
            if(!empty($v)){
                $new_tagstr.= "'".$v."',";
                $count = $this->get_count($v);
                if($count==0){
                    $clone = clone $this;
                    $clone->insert_record(array('tag_name'=>$v));
                }
            }
        }
        $rs=$this->gettaglist(substr($new_tagstr,0,-1));
        $tag_ids='';
        foreach($rs as $v){
            $tag_ids.=$v['tag_id'].',';
        }
        return substr($tag_ids,0,-1);
    }


    public function add_tags($tags){
        $new_tagstr ='';
        $tag_arr = explode(",",$tags);
        foreach($tag_arr as $v){
            if(!empty($v)){
                $new_tagstr.= "'".$v."',";
                $count = $this->get_count($v);
                if($count==0){
                    $clone = clone $this;
                    $clone->insert_record(array('tag_name'=>$v));
                }
            }
        }
        return $this->gettaglist(substr($new_tagstr,0,-1));
    }


    public function gettaglist($tags){
        $query = new \Phalcon\Mvc\Model\Query("SELECT a.tag_id,a.tag_name FROM Marser\App\Frontend\Models\TagModel a  WHERE a.tag_name in ($tags) and a.status=1", $this->getDI());
        $rs = $query->execute()->toArray();//直接对象
        return $rs;
    }



    /**
     * 插入记录
     * @param array $data
     * @return bool|int
     * @throws \Exception
     */
    public function insert_record(array $data){
        if(!is_array($data) || count($data) == 0){
            throw new \Exception('参数错误');
        }
        $result = $this -> create($data);
        if(!$result){
            throw new \Exception(implode(',', $this -> getMessages()));
        }
        $aid = $this -> group_id;
        return $aid;
    }

    /**
     * 统计数量
     * @param int $status
     * @return mixed
     */
    public function get_count($tagname){
        //$status = intval($status);
        $count = $this -> count(array(
            'conditions' => 'tag_name = :tag_name:',
            'bind' => array(
                'tag_name' => $tagname
            ),
        ));
        return $count;
    }

}
