<?php
/**
 * Phalcon控制器扩展
 *
 */

namespace Marser\App\Core;

class PhalBaseController extends \Phalcon\Mvc\Controller {

    public function initialize(){
        $this->browser_check();
    }

    /**
     * ajax输出
     * @param $message
     * @param int $code
     * @param array $data
     * @author Marser
     */
    protected function ajax_return($message, $code=1, array $data=array()){
        $result = array(
            'code' => $code,
            'message' => $message,
            'data' => $data,
        );
        //$this -> response -> setContent(json_encode($result));
        $this -> response -> setJsonContent($result);
        $this -> response -> send();
    }

    /**
     * exception日志记录
     * @param \Exception $e
     * @author Marser
     */
    protected function write_exception_log(\Exception $e){
        $logArray = array(
            'file' => $e -> getFile(),
            'line' => $e -> getLine(),
            'code' => $e -> getCode(),
            'message' => $e -> getMessage(),
            'trace' => $e -> getTraceAsString(),
        );
        $this -> logger -> write_log($logArray);
    }


    protected function request_url($url,$post_data){

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // post数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // post的变量
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $output = curl_exec($ch);
        curl_close($ch);
        //打印获得的数据
        return $output;
    }

    //检测浏览器
    public function browser_check()
    {
        $agent=$_SERVER["HTTP_USER_AGENT"];
        if(strpos($agent,'Chrome')!==false || strpos($agent,'Firefox')!==false  ) {
            return true;
        }else {
            $content = <<< EOT
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>安智媒体平台</title>
        <style type="text/css">
        .support{width:500px; margin:80px auto 0; text-align:center}
        .support p{margin-top:20px; font-size:20px; color:#3c8fef;}
        .support img{display:black; width:500px; margin:0 auto}
        </style>
    </head>
    <body>
    <div class="main">
        <div class="support">
            <img src="/admin/static/images/support.png"/>
            <p>推荐使用chrome最新版、火狐最新版来使用本网站</p>
        </div>
    </div>
    </body>
</html>
EOT;
            echo $content;die;
        }
    }
}
