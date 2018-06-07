<?php
/**
 * 控制面板
 */

namespace Marser\App\Backend\Controllers;

use \Marser\App\Backend\Controllers\BaseController,
    \Marser\App\Libs\ServerNeedle;

class DashboardController extends BaseController{

    public function initialize(){
        parent::initialize();
    }

    /**
     * 控制台页面
     */
    public function indexAction(){
        //echo '<h4>欢迎您！</h4>';
    }

    public function testAction(){
        $this->view->title = 'I am test';
        echo 'I am test';
    
    }
}
