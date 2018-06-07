<?php

namespace Marser\App\Frontend\Models;
use \Marser\App\Frontend\Models\BaseModel;
use	\Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;

class LogModel extends BaseModel{

    const TABLE_NAME = 'media_log';

    public function initialize(){
        parent::initialize();
        $this -> set_table_source(self::TABLE_NAME);
    }

     /**
     * 媒体数据入库
     * @param array $data
     * @return bool|int
     * @throws \Exception
     */
    public function insert_record($muid, $actionexp){
        if( !$muid ) {
            throw new \Exception('参数错误');
        }
        if( empty($actionexp) ) {
        	throw new \Exception('日志内容不能为空');
        }
        $data['muid']		=	$muid;
        $data['actionexp']	=	$actionexp;
        $data['logtime']	=	time();
        $data['fromip']		=	$this->get_client_ip();
        $clone = clone $this;
        $result = $clone -> create($data);
        $id = $clone -> media_log_id;
        return $id;
    }

    public function get_client_ip(){
    	if(!empty($_SERVER["HTTP_CLIENT_IP"])){
			$cip = $_SERVER["HTTP_CLIENT_IP"];
		}elseif(!empty($_SERVER["HTTP_X_FORWARDED_FOR"])){
			$cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
		}elseif(!empty($_SERVER["REMOTE_ADDR"])){
			$cip = $_SERVER["REMOTE_ADDR"];
		}else{
			$cip = "";
		}
		return $cip;
    }



}