<?php

/**
 * 基础model
 */

namespace Marser\App\Frontend\Models;

use \Marser\App\Core\PhalBaseModel;

class BaseModel extends PhalBaseModel{

   /**
     *初始化session
     */
    protected $_user;

    public function initialize(){
        parent::initialize();
        $this->_user = $this->getDI()->get('session')->get('user');
    }
}
