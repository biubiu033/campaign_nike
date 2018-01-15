<?php
require_once "./header.php";
$conn = connect_to_db();

$apiStartTime = getMicrotime();
$apiUrl = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
$loginIP = getClientIP();
$type = 'dtc_main';

//这块判断用户情况，如未绑定nike，则看用户是否已经选择分组，如已经选择了分组，则去绑定页面；未选择分组则去最初选择分组页面
if(isset($_SESSION['userInfo']) && $_SESSION['userInfo']['isBindNike']!=1){
    //未绑定,跳转
    $sql = "select count(*) as count from dtc_join_user where openid = '$openid' and teamId != '-1';";
    $result = mysql_query($sql, $conn);
    $row = mysql_fetch_assoc($result);
    if($row['count'] > 0)
    {
        header("Location:bind.php");  
        $pageResult = 'Location:bind.php';         
    }
    else
    {
        header("Location:join-choose.html");
        $pageResult = 'Location:join-choose.html';
    }
    
    //记录页面请求日志
    $apiEndTime = getMicrotime();
    $fetchTime = intval(($apiEndTime - $apiStartTime) * 1000);
    $logArr = array(
        'openid' => "$openid",
        'type' => $type,
        'ip' => $loginIP,
        'url' => $apiUrl,
        'result' => mysql_real_escape_string($pageResult),
        'fetchTime' => $fetchTime,
        'updateTime' => date("Y-m-d H:i:s", time()));
    
    $insertkeysql = $insertvaluesql = $dot = '';
    foreach ($logArr as $insert_key => $insert_value) {
        $insertkeysql .= $dot . $insert_key;
        $insertvaluesql .= $dot . '\'' . $insert_value . '\'';
        $dot = ', ';
    }
    $sql1 = 'insert into dtc_api_logs (' . $insertkeysql . ') values (' . $insertvaluesql . ')';
    mysql_query($sql1, $conn);
    
    exit();
}
else
{
    //看是否选择组
    $sql = "select count(*) as count from dtc_join_user where openid = '$openid' and teamId != '-1';";
    $result = mysql_query($sql, $conn);
    $row = mysql_fetch_assoc($result);
    if($row['count'] > 0)
    {
        //已选择组，看是否参与项目，如未参与则补全
        $sql = "select count(*) as count from yiqipao_member_project where openid = '$openid' and pid = $dtcPid and status > -1;";
        $result = mysql_query($sql, $conn);
        $row = mysql_fetch_assoc($result); 
        if($row['count'] == 0)
        {
            $created = time();
            $sql2 = "select teamId from dtc_join_user where openid = '$openid';";
            $result2 = mysql_query($sql2, $conn);
            $row2 = mysql_fetch_assoc($result2);
            $teamId = $row2['teamId'];
            
            $sql1 = "insert into yiqipao_member_project (sid, mysid, pid, `long`, openid, uTarget, teamId, ip, created) ";
            $sql1 .= "values (8, 0, $dtcPid, 0, '$openid', 10, $teamId, '$loginIP', $created);";
            mysql_query($sql1, $conn);
        }
    }
    else
    {
        //未选择组，去选择项目
        header("Location:join-choose.html");
        $pageResult = 'Location:join-choose.html';
        exit();   
    }    
}

/**************************设置分享参数*****************************/
$signPackage = getSignPackage($conn);

$phpfile = isset($phpfile) ? $phpfile :
    "campaign_nike/running1/dtc/join.php";
$shareDesc = "里享公益家，跑步还能做公益，赶紧加入吧！";

$shareTitle = isset($shareTitle) ? $shareTitle : '跑出里享世界';
$shareImg = isset($shareImg) ? $shareImg : "http://www.makeyourruncount.com/campaign_nike/running/public/img/lixiang_share.jpg";
$unixtime = time();
$key = "hugjmk5AA4giest5weixinTencentCC@#fKM";
$token = md5($openid . $key . $unixtime);
$campaignID = '201707_1_dtcRun';
$shareUrl = addWeixinShareParameters($phpfile, $openid, $campaignID);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="format-detection" content="telephone=no,email=no" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <link rel="stylesheet" href="css/reset.css"/>
    <link rel="stylesheet" href="css/main.css"/>
    <script src="js/responsive.js"></script>
    <title>里享</title>
</head>
<body  style="background: #feaa08;">
<div class="wrap main" name="index">
<svg version="1.1" id="图层_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
   viewBox="0 0 719 1218" style="enable-background:new 0 0 719 1218;" xml:space="preserve">
<style type="text/css">
  .st0{
  fill:#044B94;
  fill-opacity:0.0;
  }
</style>
  <g id="zWjrJE_1_">
    <image style="overflow:visible;" width="719" height="1218" id="zWjrJE" xlink:href="./img/1.png" >
    </image>
  </g>
  <a xlink:href="./theme-run.php">
    <path id="XMLID_30_" class="st0" d="M0,0c0.1,218.1,0.1,436.2,0.2,654.3c132.2-109.8,264.5-219.6,396.7-329.5
    c82.9-68.8,165.8-137.7,248.7-206.5C700.5,72.7,719,71.6,719,0"/>
  </a>
  <a xlink:href="public.php">
    <path id="XMLID_28_" class="st0" d="M0.2,665C239.8,463.4,479.4,261.9,719,60.3c-0.2,97.7-0.4,195.4-0.6,293.1
    c-0.1,54.7,20.5,160.3-10.2,204.9c-16.9,24.5-54.8,45.7-77.5,64.7c-43.7,36.5-87.4,73-131.1,109.5
    c-103.4,86.3-206.8,172.7-310.2,259C126.2,1044.2,63.1,1096.8,0,1149.5C0.1,988,0.1,826.5,0.2,665z"/>
  </a>
  <a xlink:href="./ranking-chose.php">
    <path id="XMLID_29_" class="st0" d="M0,1218c201.9-0.3,403.9-0.6,605.8-0.9c20.1,0,85,11.3,102-0.2c10.8-7.3,7.1-16,7.1-34.9
    c0-113,0-226,0-339c0-97,0-194,0-291C579.8,663.7,444.7,775.4,309.6,887c-81.6,67.4-163.2,134.9-244.8,202.3
    c-15.8,13.1-48.5,30.9-59.2,48.9C-6.2,1158.4,0.2,1193.6,0,1218z"/>
  </a>
</svg>
    <div class="foot"><a>技术支持<br><span>TECH SUPPORT</span></a><span style="font-size: 1.8rem;color: #c54c0f;margin: 0 0.5rem">/</span><a>联系我们<br><span>CONTACT US</span></a></div></div>
<?php include "share_dtc.php"; ?>
</body>
</html>
<?php

//记录页面请求日志
$apiEndTime = getMicrotime();
$fetchTime = intval(($apiEndTime - $apiStartTime) * 1000);
$logArr = array(
    'openid' => "$openid",
    'type' => $type,
    'ip' => $loginIP,
    'url' => $apiUrl,
    'result' => mysql_real_escape_string('main.php'),
    'fetchTime' => $fetchTime,
    'updateTime' => date("Y-m-d H:i:s", time()));

$insertkeysql = $insertvaluesql = $dot = '';
foreach ($logArr as $insert_key => $insert_value) {
    $insertkeysql .= $dot . $insert_key;
    $insertvaluesql .= $dot . '\'' . $insert_value . '\'';
    $dot = ', ';
}
$sql1 = 'insert into dtc_api_logs (' . $insertkeysql . ') values (' . $insertvaluesql . ')';
mysql_query($sql1, $conn);


?>
