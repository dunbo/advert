<?php

/**
 * 配置路由规则
 *
 * 实例 ：支持正则
 * $key => array("controller" => "", "action" => "")
 */

return array(
    //  //文章详情路由
    '/:controller/:action/:params' => array(
        'module' => 'frontend',
        'controller' => 1,
        'action' => 2,
        'params' => 3,
    ),
    //后台路由规则
    '/admin/:controller/:action/:params' => array(
        'module' => 'frontend',
        'controller'=>1,
        'action'=>2
    ),
    //404页面路由
    '/404' => array(
        'module' => 'frontend',
        'controller' => 'index',
        'action' => 'notfound',
    ),
    // //默认
    '/default.html' => array(
        'module' => 'frontend',
        'controller' => 'default',
        'action' => 'index',
    ),
    // //关于我们
    '/about.html' => array(
        'module' => 'frontend',
        'controller' => 'default',
        'action'     => 'about',
    ),
    // //常见问题
    '/qa.html' => array(
        'module' => 'frontend',
        'controller' => 'default',
        'action' => 'qa',
    ),
     // 活动
    '/atv.html' => array(
        'module' => 'frontend',
        'controller' => 'default',
        'action' => 'atv',
    ),
);