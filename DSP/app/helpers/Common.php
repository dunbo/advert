<?php
namespace Marser\App\Helpers;

class Common{
	//格式化人民币
	public static function number_format( $arg ) {
		$arg = $arg/100;
		return number_format($arg, 2);
	}

	//保留两位小数
	public static function decimal_format( $num ) {
		return floor($num*100);
	}

	public static function str_sub($str, $num){
		$str_num =  strlen($str);
		if($str_num > $num) {
			 return mb_substr($str, 0, $num,"utf-8")."...";
		}else{
			return $str;
		}
	}

	//点击率
    public static function click_rate($click=0, $exposure=0){
       if($exposure) {
            $rate =  floatval(sprintf("%.2f", ($click/$exposure)*100 ));
            return $rate;  
       }else{
            return 0;
       }
    }

    //收益除以曝光量乘以1000
    public static function ecpm($exposure=0,$earnings=0){
        $ecpm = $exposure?($earnings/$exposure*10):0;  
        return floatval(sprintf("%.2f", $ecpm));  
    }

    


}

