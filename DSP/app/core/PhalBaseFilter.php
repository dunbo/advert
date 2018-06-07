<?php

/**
 * Phalcon扩展过滤器
 * @category PhalconDSP
 * @author haoshisuo 2017-9-12
 */

namespace Marser\App\Core;

use \Phalcon\Filter;

class PhalBaseFilter extends Filter{

    /**
     * 自定义初始化函数
     */
    public function init(){
        /** 添加remove_xss过滤器 */
        $this -> add('remove_xss', function($value){
            return \Marser\App\Libs\Filter::remove_xss($value);
        });
    }

}
