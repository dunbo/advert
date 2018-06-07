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

    //获取平台名称
    public static function get_platform($pid = 0){
        if($pid) {
            $list = self::get_soft_src();
            return isset($list[$pid])?$list[$pid]:'平台不存在';
        }else {
            return "安智市场";
        }
    }

	public static function deal_image($image_url,$image_width=0,$image_height=0,$image_name='图片',$expression='jpg|png|jpeg'){
        if(!$_FILES[$image_url]['tmp_name']){
            throw new \Exception("请上传{$image_name}！");
        }
        // 取得图片后缀
        $suffix = preg_match("/\.({$expression})$/", $_FILES[$image_url]['name'], $matches);
        if ($matches) {
            $suffix = $matches[0];
        } else {
            throw new \Exception("{$image_name}格式不正确！");
        }
        // 判断图片长和宽
        $img_info_arr = getimagesize($_FILES[$image_url]['tmp_name']);
        if (!$img_info_arr) {
            throw new \Exception("{$image_name}格式非法！");
        }
        $width = $img_info_arr[0];
        $height = $img_info_arr[1];
        if($image_width!=0 && $image_height!=0){
            if ($width!=$image_width || $height!=$image_height){
                throw new \Exception("{$image_name}尺寸错误，宽需为{$image_width}px，高需为{$image_height}px");
            }
        }
        $dir_name = "/admin/".date("Ym/d/");
        if(!is_dir(UPLOAD_PATH.$dir_name)){
            mkdir(UPLOAD_PATH.$dir_name, 0755, true);
        }
        $save_name = $dir_name.time().'_'.rand(1000,9999).$suffix;
        $img_path = UPLOAD_PATH.$save_name;
        if(!move_uploaded_file($_FILES[$image_url]['tmp_name'], $img_path)){
            throw new \Exception("上传{$image_name}出错");
        }
        return $save_name;
    }

    //apk签名
    public static function getSignFromApk($file) {
        $descriptorspec = array(
                0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
                1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
                2 => array("pipe", "w") // stderr is a file to write to
                );
        $proc = proc_open(dirname(__DIR__).'/../public/admin/apks/apks '.$file. ' 2>/dev/null|grep META-INF', $descriptorspec, $pipes, realpath(dirname(realpath(__FILE__)).'/../config/gnu'));
        $string = stream_get_contents($pipes[1]);
        $status = proc_get_status($proc);
        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($proc);
        if ($status['exitcode'] >0 ) {
            return false;
        }
        $data = explode("\n", $string);
        $result = array();
        foreach($data as $line) {
            if (empty($line)) continue;
            list($path, $signature, $public_key) = explode("\t", $line);
            if (empty($path)) continue;

            $file = strtoupper(basename($path));
            $result[$file] = array($public_key, $signature);
        }
        ksort($result);
        $public_string = '';
        $private_string = '';
        foreach ($result as $v) {
            $public_string .= $v[0];
            $private_string .= $v[1];
        }
        return self::hashCode($public_string). ','. self::hashCode($private_string);
    }

    public static function hashCode($str) {
        $str = strtoupper($str);
        $hash = 0;
        $multiplier = 1;
        $_offset = 0;
        $_count = strlen($str);
        for ($i = $_offset + $_count - 1; $i >= $_offset; $i--) {
            $hash += ord($str[$i]) * $multiplier;
            $hash = $hash & 0xffffffff;
            $shifted = ($multiplier << 5) & 0xffffffff;
            $multiplier = $shifted - $multiplier;
        }
            if ($hash >= 2147483648) {
                    $hash = $hash - 4294967296;
            }
        return $hash;
    }


    public static function httpGetInfo($url, $vals) {
        $res = curl_init();
        curl_setopt($res, CURLOPT_URL, $url);
        curl_setopt($res, CURLOPT_POST, true);
        curl_setopt($res, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($res, CURLOPT_POSTFIELDS, $vals);
        $result = curl_exec($res);
        $http_code = curl_getinfo($res, CURLINFO_HTTP_CODE);
        $errno = curl_errno($res);
        $error = curl_error($res);
        curl_close($res);
        return $result;
    }

    public static function get_package_info($pkg)
    {
        if($_SERVER['SERVER_ADDR'] == '127.0.0.1'){
            $host = "http://518test.anzhi.com";
        }else {
            $host = "http://518.anzhi.com";//线上地址
        }
        $url = '/index.php/Interface/get_soft_list';
        $data = array(
                'package'   =>  $pkg
        );
        $vals   =   http_build_query($data);
        $res = self::httpGetInfo($host.$url, $vals);
        $last = json_decode($res,true);
        return $last;
    }

    //读取上游DSP的apk文件key
     public static function getDspKey($file) {
        $descriptorspec = array(
                0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
                1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
                2 => array("pipe", "w") // stderr is a file to write to
                );
        $proc = proc_open('unzip -p '.$file. ' appsid.txt', $descriptorspec, $pipes, realpath(dirname(realpath(__FILE__)).'/../config/gnu'));
        $string = stream_get_contents($pipes[1]);
        $status = proc_get_status($proc);
        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        if ($status['exitcode'] >0 ) {
            return false;
        }
        return trim($string);
    }

    public static function get_heifer_url(){
        if($_SERVER['SERVER_ADDR'] == '127.0.0.1' || $_SERVER['SERVER_NAME'] == "honor.dev.anzhi.com"){
            $host = "http://dev.heifer.anzhi.com";
        }else {
            $host = "http://heifer.anzhi.com";//线上地址
        }
        return $host;
    }

    //税率修改通知heifer
    public static function remind_tax_rate_change($map){
        $host= self::get_heifer_url();
        $url = '/http/ad/update/updateMuidInfo.json';
        $data = array(
                'serviceId' =>  '047',
                'auth'      =>  '142605894293bjc9VR9P3Xqv7jFTgh',
                'data'      =>  self::encode(array('muid'=>$map['muid'])),
        );
        $vals   =   http_build_query($data);
        $res    =   self::httpGetInfo($host.$url, $vals);
        $last   =   json_decode($res,true);
        return $last;   
    }

    public static function  encode($data,$encrpty_key="eeUu5p6XElQbYGM26iCIOmo2"){
        if(empty($data)) return array();
        $data = json_encode($data);
        $des =  new \Marser\App\Libs\GoDes($encrpty_key);
        if($data){
            $data = $des->getCodedEncrypt($data);
            return $data;
        }
    }

    //应用详情
    public static function get_soft_src() {
        return array(
                '0'     =>  "安智市场",
                '1'     =>  "百度手机助手",
                '2'     =>  "豌豆荚",
                '3'     =>  "应用宝",
                '4'     =>  "优亿市场",
                '5'     =>  "木蚂蚁",
                '6'     =>  "掌上应用汇",
                '7'     =>  "机锋应用商店",
                '8'     =>  "搜狗市场",
                '9'     =>  "PP助手",
                '10'    =>  "卓易市场",
                '11'    =>  "小米应用市场",
                '12'    =>  "联想乐商店",
                '13'    =>  "OPPO应用商店",
                '14'    =>  "华为应用市场",
                '15'    =>  "魅族flyme",
                '16'    =>  "酷派应用商店",
                '17'    =>  "360手机助手",
                '18'    =>  "金立游戏大厅",
                '19'    =>  "vivo应用商店",
            );
    }
    
}

