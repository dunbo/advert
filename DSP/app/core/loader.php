<?php

/**
 * 注册命文件
 * @category PhalconDSP
 * @author haoshisuo 2017-9-12
 */

$loader = new \Phalcon\Loader();

/**
 * 注册命名空间
 */
$loader -> registerNamespaces(array(
    'Marser' => ROOT_PATH,
    'Marser\App\Core' => ROOT_PATH . '/app/core',
    'Marser\App\Helpers' => ROOT_PATH . '/app/helpers',
    'Marser\App\Libs' => ROOT_PATH . '/app/libs',
    'Marser\App\Frontend\Controllers' => ROOT_PATH . '/app/frontend/controllers',
    'Marser\App\Frontend\Models' => ROOT_PATH . '/app/frontend/models',
    'Marser\App\Frontend\Repositories' => ROOT_PATH . '/app/frontend/repositories',
)) -> register();