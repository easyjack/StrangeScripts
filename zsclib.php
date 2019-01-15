<?php

/*
 * ZSC library Study room monitor
 * Power By WILO
 * Start at 2017-12-23
 * 注意是否有读写权限
 */

header("content-type:text/html; charset=utf-8");
header('Cache-control: private, must-revalidate');
?>
<style type="text/css">
    body{
        text-align:center;
        font-size:1.1em;
        background-color:#dadada;
    }
</style>
<?php

define("SendKey", "*******");
define("StartTime", "7:00");
define("EndTime", "20:00");
date_default_timezone_set('PRC');
$WANaddress = "127.0.0.1";

$GlobalSwitch = TRUE;

echo "<h1>System for <i style=\"color:orange\">辣鸡鲈鱼</i> to snatch the library room</h1>";

if (strtotime(date("H:i")) < strtotime(StartTime) || strtotime(date("H:i")) > strtotime(EndTime)) {
    $GlobalSwitch = FALSE;
}

//读取当天推送配置
$conContent = file_get_contents("zsclib.conf");
$pregCount = preg_match("/\d{4}[-](\d{2}[-]?){2}/", $conContent, $pregArray);
if ($pregCount == 1) {
    //配置作用于当天
    if ($pregArray[0] == date("Y-m-d")) {
        //取出配置值
        $conSwitch = (bool) (int) str_replace($pregArray, "", $conContent);
        if (!$conSwitch) {
            $GlobalSwitch = FALSE;
        }
    }
}

//改变当天推送设置
if (isset($_REQUEST['action'])) {
    if ($_REQUEST['action'] == "off" || $_REQUEST['action'] == "on") {
        changeState($_REQUEST['action'], $WANaddress);
    } else if ($_REQUEST["action"] == "push") {
        //提前终止推送页面，减少资源浪费
        if (!$GlobalSwitch) {
            die("<i style=\"color:red\">Push System Stop</i>");
        } else {
            echo "<i style=\"color:red\">Message will be pushed at soon</i><br />";
        }
    }
}

//Main content
echo "<h4>";

$webContent = file_get_contents('http://210.38.224.60/m/weixin/wdetail.php?id=0000475130');       //Get content
//$content = file_get_html('http://210.38.224.60/m/weixin/wdetail.php?id=0000475130');

$contentr = preg_replace("/[\t\n\r]+/", "", $webContent);

//获取描述
preg_match_all('/<p class="weui_media_desc">([^<>]+)/', $webContent, $desc);
//print_r($content);
//print_r($desc[1]);

//获取馆藏可借
preg_match_all('/<li class="weui_media_info_meta">([^<>]+)/', $webContent, $info);
//print_r($content);
//print_r($info[1]);
?>
<div>
    <p style="background-color: #ffff99; padding: 0.5em;margin:0rem; margin-left: 1.5em; margin-right: 1.5em; border-radius: 0.5em;">
        1.监控系统默认只在<?php echo StartTime."-".EndTime ?>期间自动运行<br />
        2.当天是否推送取决于当天是否执行停用操作<br />
        3.服务器设置为5分钟触发一次推送<br />
        4.当余量与上一次推送相同时，不推送
    </p>
</div>
<?php

//输出调试
echo "debug message:<br />";
echo "<p>" . $desc[1][1] . "<br />";
echo $info[1][0] . "</p>";
echo "<br />";
echo "<p>" . $desc[1][2] . "<br/>";
echo $info[1][1] . "</p>";

//截取四楼研修室
preg_match_all('/可借\(([^<>]+)\)/', $info[1][1], $info);
//print_r($info);

$count = $info[1][0];
echo "<br /><br />分析结果: 余 " . $count."间";
echo "<br />当前推送状态: ";
if($GlobalSwitch)
    echo "<i style=\"color:blue\">Running</i>";
else
    echo "<i style=\"color:red\">Stop</i>";
//var_dump((int)$count);
echo "</h4>";
?>
<button style="font-size: 1.5em; color:white; width: 8em; height: 4em; border: 1px; border-radius: 0.5em;background-color: #66ccff" onclick="changeState()">
    <?php 
    if(isset($conSwitch) && !$conSwitch) 
        echo "启用当天推送"; 
    else 
        echo "停用当天推送"; 
    ?>
</button>
<script>
    function changeState(){
        window.location.href=window.location.href + "?action=<?php 
    if(isset($conSwitch) && !$conSwitch) 
        echo "on"; 
    else 
        echo"off"; 
    ?>";
    }
</script>
<h5 style="border-top-style:dashed; border-width: 0.1em; margin:0.5em; margin-left: 1.5em; margin-right: 1.5em;">Power By WILO</h5>
<?php

if (isset($_REQUEST['action']) && $_REQUEST["action"] == "push" && isset($count) ) {
    
    //与上一次推送余量对比
    $lastCount = (int) file_get_contents("zsclib.count");
    if ($lastCount == $count) {
        die("余量数目未发生变化，不推送");
    } else {
        echo("触发推送<br />");
        $countTmp = $count - $lastCount;
        $WCM_title = "研修室空余监控";
        $WCM_desp = "新的弹药ready\n\n余 {$count}间\n\n变化 {$countTmp}间\n\n" . date("Y/m/d H:i")
                . "\n\n更改系统当日推送设置:\n\n"
                . "http://{$WANaddress}/zsclib.php";
        //pushMsg
        $WCM_url = "https://pushbear.ftqq.com/sub?sendkey="
                . SendKey
                . "&text=" . urlencode($WCM_title)
                . "&desp=" . urlencode($WCM_desp);
        //var_dump($WCM_desp);
        var_dump(json_decode(https_request($WCM_url)));
    }
    file_put_contents("zsclib.count", $count); 
}


function https_request($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

function changeState($option, $WANaddress){
    switch ($option){
        case "on":
            $conContent = date("Y-m-d") . "\n1";
            file_put_contents("zsclib.conf", $conContent);
            break;
        case "off":
            $conContent = date("Y-m-d") . "\n0";
            file_put_contents("zsclib.conf", $conContent);
            break;
    }
    $WCM_title = "研修室余量当天推送状态变更";
    $WCM_desp = "当天推送状态已变更为" . $option
            . "\n\n变更人 {$_SERVER['REMOTE_ADDR']}  ". date("Y/m/d H:i")
            . "\n\n更改系统当日推送设置:\n\n"
            . "http://{$WANaddress}/zsclib.php";
    //pushMsg
    $WCM_url = "https://pushbear.ftqq.com/sub?sendkey="
            . SendKey
            . "&text=" . urlencode($WCM_title)
            . "&desp=" . urlencode($WCM_desp);
    //var_dump($WCM_desp);
    var_dump(json_decode(https_request($WCM_url)));
    Header("HTTP/1.1 303 See Other");
    Header("Location:http://{$WANaddress}{$_SERVER['PHP_SELF']}");
}