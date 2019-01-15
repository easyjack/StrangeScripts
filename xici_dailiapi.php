<?php

header("Content-type:text/html; charset=utf-8");
if(isset($_GET['p'])){
	$page=$_GET['p'];
}else
	$page=1;
$spider_url = "https://www.xicidaili.com/nt/";

for ($i = 1; $i <= $page; $i++) {
    getData($spider_url . $i);
}

function getData($url) {
    $user_agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36';
    $spider = curl_init();
    curl_setopt($spider, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($spider, CURLOPT_USERAGENT, $user_agent);
    curl_setopt($spider, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($spider, CURLOPT_URL, $url);
    $result = curl_exec($spider);
    curl_close($spider);
    preg_match_all('/<tr class="odd">(.+?)<\/table>/su', $result, $matchArrray);
    $result = $matchArrray[1][0];
    $result = preg_replace('/\s||class="country"/', '', $result);
    preg_match_all('/<td>([A-Z0-9\.])*?<\/td>/siu', $result, $matchArrray);
//var_dump($matchArrray);
    $result = array();
    $string = "";
    for ($i = 0; $i < count($matchArrray[0]); $i++) {
        $matchArrray[0][$i] = preg_replace('/<td>|<\/td>/', '', $matchArrray[0][$i]);
        if ($i == 0 || $i % 3 == 0) {
            $string = "";
            $string .= $matchArrray[0][$i];
        } else if ($i % 3 == 1) {
            $string .= ":" . $matchArrray[0][$i];
        } else if ($i % 3 == 2) {
            $string .= "@" . $matchArrray[0][$i];
            array_push($result, $string);
        }
    }
    foreach ($result as $item) {
        echo $item . "\n";
    }
}
