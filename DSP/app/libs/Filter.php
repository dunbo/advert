<?php

/**
 * 过滤器
 * @category PhalconDSP
 * @author haoshisuo 2017-9-12
 */

namespace Marser\App\Libs;

class Filter {

    /**
     * 清除xss特殊字符
     * @param $str
     * @return mixed
     */
    public static function remove_xss($str){
        $str = filter_var(trim($str), FILTER_SANITIZE_STRING);
        return $str;
    }
}