<?php

/**
 * 通行证
 */

namespace Marser\App\Frontend\Controllers;

use \Marser\App\Core\PhalBaseController,
    \Marser\App\Frontend\Repositories\RepositoryFactory;

class DefaultController extends PhalBaseController{

    public function initialize(){
        parent::initialize();
        @$this -> session_begin();
        $this -> view -> setVars(array(
            'login_url'    =>  array(
		    		'login'		=>	'http://dev.i.anzhi.com/?serviceId=006&serviceVersion=1.0&redirecturi=http://ssp.anzhi.com/index/index',
		    		'register'	=>	'http://dev.i.anzhi.com/web/account/register?serviceId=006&serviceVersion=1.0&redirecturi=http://ssp.anzhi.com/index/index',
		    	),
           	'is_login'	=>	isset($_SESSION['USER_ID'])&&$_SESSION['USER_ID']?1:0,
        ));
    }

    public function indexAction(){
    	$this -> view -> setMainView('default/index');
    }

    public function aboutAction(){
    	$this -> view -> setMainView('default/about');	
    }

    public function qaAction(){
    	$this -> view -> setMainView('qa/about');	
    }

    public function atvAction(){
        $this -> view -> setMainView('default/atc');   
    }
}