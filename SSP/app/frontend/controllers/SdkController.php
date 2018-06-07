<?php

/**
 * 首页
 */
namespace Marser\App\Frontend\Controllers;

use \Marser\App\Frontend\Controllers\BaseController;

class SdkController extends BaseController{
    public function initialize(){
        $this -> view -> setVars(array(
            'prefix'    =>  'sdk',
        ));
        parent::initialize();
    }
    /**
     * sdk首页
     */
    public function indexAction(){
        $this -> view -> pick('sdk/index');
    }

    /**
    * sdk下载
    */
    public function downSdk() {

    }


    /**
    * jsSdk下载
    */
    public function downJsSdk(){
    	
    }

}