<?php
/**
 * 基类model
 */

namespace Marser\App\Backend\Models;

use \Marser\App\Core\PhalBaseModel;

class BaseModel extends PhalBaseModel{

    /**
     * 用户session
     */
    protected $_user;

    public function initialize(){
        parent::initialize();
        $this->_user = $this->getDI()->get('session')->get('admin');
    }
}
