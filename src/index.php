<?php

function main_handler($event, $context) {
    // print parameters
    // 进门打印传入参数是好习惯
    echo 'event:'.json_encode($event, JSON_PRETTY_PRINT).'
context:'.json_encode($context, JSON_PRETTY_PRINT);

    // echo $event->{'headers'}->{'host'} ; // parameter is object. 传入的参数是object

    // convert parameters to array
    // 转换为数组
    $event = json_decode(json_encode($event), true);
    $context = json_decode(json_encode($context), true);

    // good choice to clean variables
    // SCF中使用全局的变量前最好清空
    unset($_GET);
    unset($_POST);

    // get the path in url
    // 取得链接中非域名部分的path值
    $function_name = $context['function_name'];
    $host_name = $event['headers']['host'];

    // get the queryString
    // 取得链接后?queryString提交的值
    $_GET = $event['queryString'];

    $file_path = __DIR__."/demo.html";
	$fp = fopen($file_path,"r");
	$str = fread($fp,filesize($file_path));
    fclose($fp);
    
    $api_base = "https://api.bilibili.love";
    
    $aid = $_GET['aid'];
    $bvid = $_GET['bvid'];
    $hq = $_GET['hq']? 1 : 0;
    $danmaku = $_GET['danmaku']? 1 : 0;
    echo $api_base."/view?aid=$aid&bvid=$bvid";
    $result = httpGetJSON($api_base."/view?aid=$aid&bvid=$bvid");
    var_dump($result);

    $play_url = "https://player.bilibili.com/player.html?aid=$aid&bvid=$bvid&high_quality=$hq&danmaku=$danmaku";

    $str = str_replace("{{video_title}}", $result->data->title, $str);
    $str = str_replace("{{video_description}}", $result->data->desc, $str);
    $str = str_replace("{{video_poster}}", $result->data->pic, $str);
    $str = str_replace("{{player_url}}", $play_url, $str);   

	return array(
		'isBase64Encoded' => false,
		'statusCode' => 200,
		'headers' => array('Content-Type' => 'text/html; charset=utf-8'),
		'body' => $str
	);
}

  /**  
    * GET Json
    * 
    * @param str $url
    * @return object
     */ 
    function httpGetJSON($url){

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
        //curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);  // 从证书中检查SSL加密算法是否存在
        $output = curl_exec($curl);     //返回api的json对象
        //关闭URL请求
        curl_close($curl);
        return json_decode($output);    //返回json对象
    }
?>
