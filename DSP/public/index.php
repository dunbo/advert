<?php

/**
 * @category PhalconDSP
 * @author haoshisuo 2017-9-12
 */

try {

    $runtime = 'dev';
    define('RUNTIME', $runtime);
    define('ROOT_PATH', dirname(__DIR__));
    //define('UPLOAD_PATH', '/data/att/honor');
    define('UPLOAD_PATH', dirname(__DIR__).'/public/');
    header('X-Powered-By:Marser');

    $config = new \Phalcon\Config\Adapter\Php(ROOT_PATH . "/app/config/system/system_{$runtime}.php");

    /**
     * 引入loader.php
     */
    include ROOT_PATH . '/app/core/loader.php';

    /**
     * 引入services.php
     */
    include ROOT_PATH . '/app/core/services.php';

    /**
     * 处理请求
     */
    $application = new \Phalcon\Mvc\Application($di);

    $application -> registerModules(array(
        'frontend' => array(
            'className' => 'Marser\App\Frontend\FrontendModule',
            'path' => ROOT_PATH . '/app/frontend/FrontendModule.php',
        ),
    ));

    echo $application->handle()->getContent();

}catch (\Exception $e) {
    $log = array(
        'file' => $e -> getFile(),
        'line' => $e -> getLine(),
        'code' => $e -> getCode(),
        'msg' => $e -> getMessage(),
        'trace' => $e -> getTraceAsString(),
    );
print_r($log);die;
    $date = date('Ymd');
    $logger = new \Phalcon\Logger\Adapter\File(ROOT_PATH."/app/cache/logs/error_{$date}.log");
    $logger -> error(json_encode($log));
}
