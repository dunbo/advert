<?php

/**
 * 基类model
 * @category PhalconDSP
 * @author haoshisuo 2017-9-13
 */

namespace Marser\App\Frontend\Models;

use \Marser\App\Core\PhalBaseModel;

class BaseModel extends PhalBaseModel{

    /**
     * 用户session
     */
    protected $_user;

    public function initialize(){
        parent::initialize();
        $this->_user = $this->getDI()->get('session')->get('user');
    }
}
