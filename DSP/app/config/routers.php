<?php

/**
 * 配置路由规则
 * @category PhalconDSP
 * @author haoshisuo 2017-9-12
 *
 * 实例 ：支持正则
 * $key => array("controller" => "", "action" => "")
 */

return array(
    //前台路由规则
    '/' => array(
    	'module' => 'frontend',
        "controller" => "Home",  
        "action"     => "index",  
    )
);