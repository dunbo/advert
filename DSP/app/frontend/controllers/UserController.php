<?php

/**
 * 用户信息
 * @category PhalconDSP
 * @author haoshisuo 2017-9-13
 */

namespace Marser\App\Frontend\Controllers;

use \Marser\App\Frontend\Controllers\BaseController;

class UserController extends BaseController{

    public function initialize(){
        parent::initialize();
        $this -> view -> setVars(array(
            'prefix' => 'user',
        ));
    }

    /**
     * 客户信息页面
     */
    public function indexAction(){
        $auid = $this -> session -> get('user')['auid'];
        $info = $this -> get_repository('User') -> get_One($auid);
        $this -> view -> setVars(array(
            'info' => $info,
        ));
        $this -> view -> pick('user/index');
    }
}