<?php
//获取当前时间
function getCurrentTime(){
    date_default_timezone_set("Asia/Shanghai");
    $nowtime  = date('Y-m-d H:i:s', time());
    return $nowtime;
}
/************************* 生成随机数  ************************/
function getRandom($length = 4) {
    return rand(pow(10,($length-1)), pow(10,$length)-1);
}
/************************* 处理php中 array转json 方法中的中文乱码问题  ************************/
function arrayRecursive(&$array, $function, $apply_to_keys_also = false)
{
    static $recursive_counter = 0;
    if (++$recursive_counter > 1000) {
        die('possible deep recursion attack');
    }
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            arrayRecursive($array[$key], $function, $apply_to_keys_also);
        } else {
            $array[$key] = $function($value);
        }
        if ($apply_to_keys_also && is_string($key)) {
            $new_key = $function($key);
            if ($new_key != $key) {
                $array[$new_key] = $array[$key];
                unset($array[$key]);
            }
        }
    }
    $recursive_counter--;
}
/************************* 将数组转成json，同时处理中文乱码问题  ************************/
function toJson($array) {
    arrayRecursive($array, 'urlencode', true);
    $json = json_encode($array);
    return urldecode($json);
}
/************************* 前台请求成功 success  响应  ************************/
function  respondSuccessJson($str) {
    return toJson(array(state=>'success',content=>$str));
}

/************************* 前台请求失败 error  响应  ************************/
function  respondErrorJson($str) {
    return toJson(array(state=>'error',content=>$str));
}

/************************* 去除空格和换行  ************************/
function trimStr($str){
    $qian=array(" ","　","\t","\n","\r");
    return str_replace($qian, '', $str);
}

/************************* 过滤字undefined 和null 符串  ************************/
function filterStr($str){
    $str = str_replace("undefined", "未知", $str);
    $str = str_replace("null", "未知", $str);
    return $str;
}

/************************* 发送短信  ************************/
function send_sms( $text, $mobile){
    $apikey = "3bf6adecf98710a21230d60d2d1e77f1";
    $url="http://yunpian.com/v1/sms/send.json";
    $encoded_text = urlencode("$text");
    $post_string="apikey=$apikey&text=$encoded_text&mobile=$mobile";
    return sock_post($url, $post_string);
}
/************************* 发送短信  ************************/
function tpl_send_sms($apikey, $tpl_id, $tpl_value, $mobile){
    $url="http://yunpian.com/v1/sms/tpl_send.json";
    $encoded_tpl_value = urlencode("$tpl_value");  //tpl_value需整体转义
    $post_string="apikey=$apikey&tpl_id=$tpl_id&tpl_value=$encoded_tpl_value&mobile=$mobile";
    return sock_post($url, $post_string);
}
/************************* 发送短信  ************************/
function sock_post($url,$query){
    $data = "";
    $info=parse_url($url);
    $fp=fsockopen($info["host"],80,$errno,$errstr,30);
    if(!$fp){
        return $data;
    }
    $head="POST ".$info['path']." HTTP/1.0\r\n";
    $head.="Host: ".$info['host']."\r\n";
    $head.="Referer: http://".$info['host'].$info['path']."\r\n";
    $head.="Content-type: application/x-www-form-urlencoded\r\n";
    $head.="Content-Length: ".strlen(trim($query))."\r\n";
    $head.="\r\n";
    $head.=trim($query);
    $write=fputs($fp,$head);
    $header = "";
    while ($str = trim(fgets($fp,4096))) {
        $header.=$str;
    }
    while (!feof($fp)) {
        $data .= fgets($fp,4096);
    }
    return $data;
}

/******************   裁剪中文字符串   ********************/
function filter_str($str, $len=10, $etc='...')
{
    $restr = '';
    $i = 0;
    $n = 0.0;

    //字符串的字节数
    $strlen = strlen($str);
    while(($n < $len) and ($i < $strlen))
    {
        $temp_str = substr($str, $i, 1);

        //得到字符串中第$i位字符的ASCII码
        $ascnum = ord($temp_str);

        //如果ASCII位高与252
        if($ascnum >= 252)
        {
            //根据UTF-8编码规范，将6个连续的字符计为单个字符
            $restr = $restr.substr($str, $i, 6);
            //实际Byte计为6
            $i = $i + 6;
            //字串长度计1
            $n++;
        }
        elseif($ascnum >= 248)
        {
            $restr = $restr.substr($str, $i, 5);
            $i = $i + 5;
            $n++;
        }
        elseif($ascnum >= 240)
        {
            $restr = $restr.substr($str, $i, 4);
            $i = $i + 4;
            $n++;
        }
        elseif($ascnum >= 224)
        {
            $restr = $restr.substr($str, $i, 3);
            $i = $i + 3 ;
            $n++;
        }
        elseif ($ascnum >= 192)
        {
            $restr = $restr.substr($str, $i, 2);
            $i = $i + 2;
            $n++;
        }

        //如果是大写字母 I除外
        elseif($ascnum>=65 and $ascnum<=90 and $ascnum!=73)
        {
            $restr = $restr.substr($str, $i, 1);
            //实际的Byte数仍计1个
            $i = $i + 1;
            //但考虑整体美观，大写字母计成一个高位字符
            $n++;
        }

        //%,&,@,m,w 字符按1个字符宽
        elseif(!(array_search($ascnum, array(37, 38, 64, 109 ,119)) === FALSE))
        {
            $restr = $restr.substr($str, $i, 1);
            //实际的Byte数仍计1个
            $i = $i + 1;
            //但考虑整体美观，这些字条计成一个高位字符
            $n++;
        }

        //其他情况下，包括小写字母和半角标点符号
        else
        {
            $restr = $restr.substr($str, $i, 1);
            //实际的Byte数计1个
            $i = $i + 1;
            //其余的小写字母和半角标点等与半个高位字符宽
            $n = $n + 0.5;
        }
    }

    //超过长度时在尾处加上省略号
    if($i < $strlen)
    {
        $restr = $restr.$etc;
    }

    return $restr;
}


function getMinImg($src){
    $imgName = substr($src,0,(strlen($src)-4));
    return $imgName.'min.jpg';
}

function getFangImg($src){
    $imgName = substr($src,0,(strlen($src)-4));
    return $imgName.'fang.jpg';
}

/*将base64编码的字符串 转成图片格式存到指定文件夹*/
function getImgurl($img, $save_path, $save_name){

    if(preg_match("/data:image\/[a-zA-Z]{2,4};base64/",$img, $arr)){

        $img = preg_replace("/data:image\/[a-zA-Z]{2,4};base64\,/",'', $img);
        // $img = str_replace('data:image/png;base64,', '', $img);

        $img = str_replace(' ', '+', $img);

        $data = base64_decode($img);

        if(!file_exists($save_path)){
            mkdir($save_path, 0777);
        }

        $filepath = "";
        $filepath =  $save_path;
        $filepath .= $save_name;
        $filepath .= time();
        reSizeImage($data,$filepath.'min.jpg',165,123);
        reSizeImage($data,$filepath.'fang.jpg',200,200);
        $filepath .= ".jpg";
        file_put_contents($filepath, $data);
        //$image_url = $filepath;
        if(substr($filepath,0,3)=='../'){
            $filepath = substr($filepath,3,strlen($filepath));
        }

        return $filepath;
    }else{
        if(substr($img,0,3)=='../'){
            $img = preg_replace("/(min)|(fang)/",'', $img);
            return substr($img,3,strlen($img));
        }else{
            return $img;
        }

        return "";
    }

}

/*  重新切割小图  */
function reSizeImage($data,$filepath,$xx,$yy){
    // $image = "jiequ.jpg"; // 原图

    $imgstream = $data;
    $im = imagecreatefromstring($imgstream);
    $x = imagesx($im);//获取图片的宽
    $y = imagesy($im);//获取图片的高

    // 缩略后的大小
    /* $xx = 165;
     $yy = 123;*/

    $s_bit = $x/$y;
    $d_bit = $xx/$yy;
    if($s_bit>$d_bit){
        //图片宽大于高
        $sx = abs(($x-($y/$yy)*$xx)/2);
        $sy = 0;
        $thumbw = abs($y*$d_bit);
        $thumbh = $y;
    } else {
        //图片高大于等于宽
        $sx = 0;
        $sy = abs(($y-($x/$xx)*$yy)/2);
        $thumbw = $x;
        $thumbh = abs($x/$d_bit);
    }

    if(function_exists("imagecreatetruecolor")) {
        $dim = imagecreatetruecolor( $xx,$yy); // 创建目标图gd2
    } else {
        $dim = imagecreate($xx,$yy); // 创建目标图gd1
    }
    imageCopyreSampled ($dim,$im,0,0,$sx,$sy,$xx,$yy,$thumbw,$thumbh);
    header ("Content-type: image/jpeg");
    imagejpeg ($dim, $filepath, 100);
}



function http_GET($url){

    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
   /* curl_setopt($ch, CURLOPT_USERAGENT, _USERAGENT_);
    curl_setopt($ch, CURLOPT_REFERER, _REFERER_);*/
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);    // https请求 不验证证书和hosts
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    $result = curl_exec($ch);
    curl_close($ch);

    print_r($result);
    return $result;
}


function http_POST($url,$data){
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_USERAGENT, _USERAGENT_);
    curl_setopt($ch, CURLOPT_REFERER, _REFERER_);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);    // https请求 不验证证书和hosts
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}


?>