<?php
header('Content-type:application/json;charset=UTF-8');
$spider = curl_init();
@$keyword=$_GET['s'];
$spider_url = "https://api.pcsysoft.com/api/app/video/ver2/video/searchVideoInfo/2/1920?currentPage=1&pageSize=10&searchContent=". urlencode($keyword) ."&entry=1&time=" . getMicroTime();

$mahuaApp_Headers = array(
	'Content-Type: application/json',
	"accessToken:" . '9e28b766e7d7d9a32a170923786bd7ba84373cc706045e3af8791dfd27e1c7e7',
	"X-Auth-Token:" . "mb_token:37818609:367f78fe087f8a3772ffe66dc8801327",
	"X-Client-IP:" . "127.0.0.1",
	'X-Client-NonceStr:' . '77i8BROrmO',
	'X-Client-Sign:' . '4b9fd108309273b8f2ce92f2ac4d25b1058eb479:0',
	'X-Client-TimeStamp:' . getMicroTime(),
	'X-Client-Token:' . "\n",
	'X-Client-Version:' . '2.5.0'
);

$mahuaApp_UserAgent = 'okhttp/3.11.0';

$curlOptArr = array(
	CURLOPT_URL => $spider_url,
	CURLOPT_RETURNTRANSFER => TRUE,
	CURLOPT_SSL_VERIFYPEER => FALSE,
	CURLOPT_HTTPHEADER => $mahuaApp_Headers,
	CURLOPT_USERAGENT => $mahuaApp_UserAgent,
	CURLINFO_HEADER_OUT => true
	/*CURLOPT_SSL_VERIFYSTATUS => FALSE，*/ //Valid in PHP 7.0.7+
);

curl_setopt_array($spider, $curlOptArr);
$result = curl_exec($spider);
if(curl_errno($spider)){
	echo '<br>\nError: ' . curl_error($spider) . "<br>\n";
}

echo $result;

/* for request header debug */
//echo "\n\n" . curl_getinfo($spider, CURLINFO_HEADER_OUT);

curl_close($spider);


function getMicroTime(){
	list($msec, $sec) = explode(' ', microtime());
	$mesc = (int)intval((float)floatval(($msec)) * 1000);
	$sec = (int)intval($sec);
	//return $sec . $mesc;
	return '1548996876745';
}
