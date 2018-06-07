<?php
/**
 * phalcon模板扩展类
 */

namespace Marser\App\Core;

use \Phalcon\Mvc\View\Engine\Volt;

class PhalBaseVolt extends Volt{

    /**
     * 添加扩展函数
     */
    public function initFunction(){
        $compiler = $this->getCompiler();

        /** 添加get_page_url函数 */
        $compiler -> addFunction('get_page_url', function($resolvedArgs, $exprArgs) use ($compiler){
            return '\Marser\App\Helpers\PaginatorHelper::get_page_url(' . $resolvedArgs . ')';
        });
        /** 添加get_page_url函数 */
        $compiler -> addFunction('get_jump_param', function() use ($compiler){
            return '\Marser\App\Helpers\PaginatorHelper::get_jump_param()';
        });
        /** 添加number_format函数 */
        $compiler -> addFunction('number_format', function($resolvedArgs) use ($compiler){
            return '\Marser\App\Helpers\Common::number_format(' . $resolvedArgs . ')';
        });
        /** 添加decimal_format函数 */
        $compiler -> addFunction('decimal_format', function($resolvedArgs) use ($compiler){
            return '\Marser\App\Helpers\Common::decimal_format(' . $resolvedArgs . ')';
        });
        /** 添加click_rate函数 */
        $compiler -> addFunction('click_rate', function($resolvedArgs) use ($compiler){
            return '\Marser\App\Helpers\Common::click_rate('.$resolvedArgs.')';
        });
        /** 添加ecpm函数 */
        $compiler -> addFunction('ecpm', function($resolvedArgs) use ($compiler){
            return '\Marser\App\Helpers\Common::ecpm('.$resolvedArgs.')';
        });
        /** 添加get_platform函数 */
        $compiler -> addFunction('get_platform', function($resolvedArgs) use ($compiler){
            return '\Marser\App\Helpers\Common::get_platform('.$resolvedArgs.')';
        });
        /** 添加str_repeat函数 */
        $compiler -> addFunction('str_repeat', 'str_repeat');

        /** 添加substr_count函数 */
        $compiler -> addFunction('substr_count', 'substr_count');

        /** 添加explode函数 */
        $compiler -> addFunction('explode', 'explode');

        /** 添加array_rand函数 */
        $compiler -> addFunction('array_rand', 'array_rand');
    }


}



