<?php

/**
 * 系统配置--线上环境
 */

return array(
    'app' => array(
        //项目名称
        'app_name' => 'HonorAdmin',

        //版本号
        'version' => '1.0',

        //根命名空间
        'root_namespace' => 'Marser',

        //前台配置
        'frontend' => array(
            //模块在URL中的pathinfo路径名
            'module_pathinfo' => '/',

            //控制器路径
            'controllers' => ROOT_PATH . '/app/frontend/controllers/',

            //视图路径
            'views' => ROOT_PATH . '/app/frontend/views/',

            //是否实时编译模板
            'is_compiled' => true,

            //模板路径
            'compiled_path' => ROOT_PATH . '/app/cache/compiled/frontend/',

            //前台静态资源URL
            'assets_url' => '/home/',
        ),

        //后台配置
        'backend' => array(
            //模块在URL中的pathinfo路径名
            'module_pathinfo' => '/admin/',

            //控制器路径
            'controllers' => ROOT_PATH . '/app/backend/controllers/',

            //视图路径
            'views' => ROOT_PATH . '/app/backend/views/',

            //是否实时编译模板
            'is_compiled' => true,

            //模板路径
            'compiled_path' => ROOT_PATH . '/app/cache/compiled/backend/',

            //后台静态资源URL
            'assets_url' => '/admin/',

            //'login_url' => 'http://houtai.localhost/index.php/InterfaceHonor/login_interface',//518登陆接口地址
            'login_url' => 'http://518test.anzhi.com/index.php/InterfaceHonor/login_interface',//518登陆接口地址

            //'writelog_url' => 'http://houtai.localhost/index.php/InterfaceHonor/writelog_interface',//518日志接口地址
            'writelog_url' => 'http://518test.anzhi.com/index.php/InterfaceHonor/writelog_interface',//518日志接口地址
        ),

        //类库路径
        'libs' => ROOT_PATH . '/app/libs/',

        //日志根目录
        'log_path' => ROOT_PATH . '/app/cache/logs/',

        //缓存路径
        'cache_path' => ROOT_PATH . '/app/cache/data/',
    ),
    
    //数据库表配置
    'database' => array(
        //数据库连接信息
        'db' => array(
            //'host' => '172.16.1.19',
            'host' => 'honorm.mysql.lan.anzhi.com',
            'port' => 3306,
            'username' => 'honUser',
            'password' => 'hON@)1L88L1A',
            'dbname' => 'honor',
            'charset' => 'utf8',
        ),

        //表前缀
        'prefix' => '',
    ),

    'db2' => array(
        //数据库连接信息
        'db' => array(
            //'host' => '172.16.1.18',
            'host' => 'honorpaym.mysql.lan.anzhi.com',
            'port' => 3306,
            'username' => 'honor_read',
            'password' => 'BL4O80nl',
            'dbname' => 'honor_pay',
            'charset' => 'utf8',
        ),

        //表前缀
        'prefix' => '',
    ),

);
