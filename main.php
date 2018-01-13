<?php
require_once "./header.php";
$conn = connect_to_db();

$apiStartTime = getMicrotime();
$apiUrl = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
$loginIP = getClientIP();
$type = 'main';


$headerLocation = ''; //跳转地址
$sql = "select * from dtc_join_user where openid = '$openid';";
$result = mysql_query($sql, $conn);
if (is_resource($result) && mysql_num_rows($result) != 0)
{
    $joinUserRow = mysql_fetch_assoc($result);
    //进到首页，可简单判断，如果用户teamId不为-1且已确认开启任务且已绑定Nike+则留在本页面；其他情况统一交给join-choose处理
    if ($joinUserRow['teamId'] != -1)
    {                
        if ($joinUserRow['is_confirm'] != 1 || $_SESSION['userInfo']['isBindNike'] != 1)
        {

            $headerLocation = 'join-choose.php';
            
        } 
    } else
    {
        $headerLocation = 'join-choose.php';    
    }
}
else
{
    echo "数据同步异常，请联系客服电话18514748838";
    exit();
}

if ($headerLocation != '')
{
    //记录页面请求日志
    $apiEndTime = getMicrotime();
    $fetchTime = intval(($apiEndTime - $apiStartTime) * 1000);
    $logArr = array(
        'openid' => "$openid",
        'type' => $type,
        'ip' => $loginIP,
        'url' => $apiUrl,
        'result' => mysql_real_escape_string($headerLocation),
        'fetchTime' => $fetchTime,
        'updateTime' => date("Y-m-d H:i:s", time()));

    $insertkeysql = $insertvaluesql = $dot = '';
    foreach ($logArr as $insert_key => $insert_value)
    {
        $insertkeysql .= $dot . $insert_key;
        $insertvaluesql .= $dot . '\'' . $insert_value . '\'';
        $dot = ', ';
    }
    $sql1 = 'insert into dtc_api_logs (' . $insertkeysql . ') values (' . $insertvaluesql . ')';
    mysql_query($sql1, $conn);

    header("Location:$headerLocation");

    exit();
}

$apiStartTime = getMicrotime();
$apiUrl = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
$loginIP = getClientIP();
$type = 'dtc_main';


$sql = "select *  from dtc_trophy where openid = '{$openid}'";
$result = mysql_query($sql, $conn);
$row_trophy = mysql_fetch_assoc($result);
$is_get_king = $row_trophy['king'];
$is_show_king = $row_trophy['king_show'];
$is_get_1km = $row_trophy['1km'];
$is_get_10km = $row_trophy['10km'];
$is_get_share1 =$row_trophy['share1'];
$is_get_3d5km =$row_trophy['3d5km'];
$is_get_100km =$row_trophy['100km'];
$is_get_sharemany =$row_trophy['sharemany'];
$is_get_200km =$row_trophy['200km'];
$is_get_team = $row_trophy['team_win'];
if($is_get_1km && $is_get_10km && $is_get_share1 && $is_get_3d5km && $is_get_100km && $is_get_sharemany && $is_get_200km &&$is_get_team) {
    $query = "UPDATE dtc_trophy SET king = '1' where openid = '$openid'";
    $result = mysql_query($query, $conn);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="format-detection" content="telephone=no,email=no"/>
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <link rel="stylesheet" href="css/reset.css"/>
    <link rel="stylesheet" href="css/main.css?a=123"/>
    <script src="js/jquery-2.1.3.min.js"></script>
    <script src="js/responsive.js"></script>
    <title>里享</title>
    <style>
        body {
            background: url(./img/bg.png) no-repeat;
            background-size: cover;
            background-color: #fb710e;
            /*min-height: 43.3rem;*/
            overflow-x: hidden;
        }
        .main-content{
            width: 100%;
            overflow: hidden;
           /* background-color: rgba(252, 177, 5, 0.5);*/
        }
        .main * {
            box-sizing: border-box;
        }

        .main {
            transform: rotate(-10deg) translate(-9rem, 2rem);
            -ms-transform:rotate(-10deg) translate(-9rem, 2rem);/* IE 9 */
            -moz-transform: rotate(-10deg) translate(-9rem, 2rem) ;/* Firefox */
            -webkit-transform: rotate(-10deg) translate(-9rem, 2rem); /* Safari 和 Chrome */
            -o-transform: rotate(-10deg) translate(-9rem, 2rem); /* Opera */
            width: 100%;
            padding-bottom: 4rem;
        }

        .main img {
            width: 100%;
            /* transform: rotate(40deg);*/
        }

        .main .item {
            display: block;
            width: 170%;
            padding: 3.666rem 13rem;
            text-align: center;
        }
        .foot{
            z-index: 9999;
            width: 100%;
            text-align: center;
            margin-top: 0;
          /*  background-color: rgba(252, 177, 5, 0.5);*/
        }
        .teammember {
            z-index: 999;
            background-color: #ff5500;
            box-shadow: 4px 4px #9c2000;
            position: absolute;
            top: 26px;
            left: 17.5rem;
            border-radius: 5px;
            color: #ffffff;
            text-align: center;
            width: 6rem;
            padding: 0.5rem 0.7rem 0.2rem;
            font-size: 1rem;
            line-height: 1rem;
        }
        .teammember span{
            font-family: 'fornike365';
            font-size: 0.7rem;
        }
    </style>
</head>
<body>

<div class="wrap main-content">
    <a class="teammember" href="team_member.php">团队成员><br><span>TEAM MEMBER</span></a>
    <div class="main">

        <a href="./public.php"  class="item" style="margin: 7rem 0 1rem;padding: 1.5rem 12.5rem .5rem 14rem;background: #fc5c13 ;"><img
                src="img/main-2.png"></a>
        <a  href="./theme-run.php" class="item" style="background: #fc5c13;margin: 0 0 1rem;padding: 1rem 18.5rem 1rem 18rem;"><img src="img/main-1.png"></a>
        <a href="./ranking-chose.php" class="item" style="background: #fc5c13;margin: 0 0 1rem;padding: 1rem 18rem 1rem 16rem;"><img src="img/main-3.png"></a>
    </div>
</div>
<div class="foot" ><a class="foot-a" href="http://r.lovemojito.com/short.php?a=4uphhl">常见问题<br><span class="foot-span">FAQ</span></a><span
        style="font-size: 1.8rem;color: #c54c0f;margin: 0 0.2rem">/</span><a class="foot-a" href="http://r.lovemojito.com/short.php?a=4uphhl">技术支持<br><span class="foot-span">TECH SUPPORT</span></a></div>
<!--获奖弹框提示-->
<aside class="celebrate 1km" style="display:none">
    <a class="close b-close"></a>
    <p>恭喜你完成第一个1公里跑<br>
        <span>CONGRATULATIONS! YOU’VE FINISHED 1 KM RUNNING</span>
    </p>
    <div class="img-bg"><img src="img/trophy-1km.png" width="40%;"></div>
    <h1>获得1KM奖杯1个<br><span>1 KM TROPHY COLLECTED</span></h1>
    <p style="margin-top: 1rem">继续加油<br>
        <span>KEEP GOING</span><br>
        更多的荣誉在等你<br>
        <span>MORE TROPHIES TO COME</span>
    </p>
</aside>
<aside class="celebrate 10km" style="display:none">
    <a class="close b-close"></a>
    <p>恭喜你完成第一个10公里跑<br>
        <span>CONGRATULATIONS! YOU’VE FINISHED 10 KM RUNNING</span>
    </p>
    <div class="img-bg"><img src="img/trophy-10km.png" width="40%"></div>
    <h1>获得10KM奖杯1个<br><span>10 KM TROPHY COLLECTED</span></h1>
    <p style="margin-top: 1rem">继续加油<br>
        <span>KEEP GOING</span><br>
        更多的荣誉在等你<br>
        <span>MORE TROPHIES TO COME</span>
    </p>
</aside>
<aside class="celebrate share1" style="display:none">
    <a class="close b-close"></a>
    <p>恭喜你完成第一次分享<br>
        <span>CONGRATULATIONS! YOU’VE POSTED 1ST POST</span>
    </p>
    <div class="img-bg"><img src="img/trophy-share1.png" width=40%"></div>
    <h1>获得首次分享奖杯1个<br><span>FIRST POST TROPHY COLLECTED</span></h1>
    <p style="margin-top: 1rem">继续加油<br>
        <span>KEEP GOING</span><br>
        更多的荣誉在等你<br>
        <span>MORE TROPHIES TO COME</span>
    </p>
</aside>
<aside class="celebrate 3d5k" style="display:none">
    <a class="close b-close"></a>
    <p>恭喜你完成连续3天5公里跑<br>
        <span>CONGRATULATIONS! YOU’VE FINISHED 5KM RUNNING IN 3 CONSECUTIVE DAYS</span>
    </p>
    <div class="img-bg"><img src="img/trophy-3d5k.png" width="40%"></div>
    <h1>获得3DAYS 5KM奖杯1个<br><span>3DAYS 5KM TROPHY COLLECTED</span></h1>
    <p style="margin-top: 1rem">继续加油<br>
        <span>KEEP GOING</span><br>
        更多的荣誉在等你<br>
        <span>MORE TROPHIES TO COME</span>
    </p>
</aside>
<aside class="celebrate 100km" style="display:none">
    <a class="close b-close"></a>
    <p>恭喜你完成第一个100公里跑<br>
        <span>CONGRATULATIONS! YOU’VE FINISHED 100 KM RUNNING</span>
    </p>
    <div class="img-bg"><img src="img/trophy-100km.png" width="40%"></div>
    <h1>获得100KM奖杯1个<br><span>100 KM TROPHY COLLECTED</span></h1>
    <p style="margin-top: 1rem">继续加油<br>
        <span>KEEP GOING</span><br>
        更多的荣誉在等你<br>
        <span>MORE TROPHIES TO COME</span>
    </p>
</aside>
<aside class="celebrate sharemany" style="display:none">
    <a class="close b-close"></a>
    <p>恭喜你完成十次分享<br>
        <span>CONGRATULATIONS! YOU’VE POSTED 10TH POST</span>
    </p>
    <div class="img-bg"><img src="img/trophy-sharemany.png" width="40%"></div>
    <h1>获得分享达人奖杯1个<br><span> POST GURU TROPHY COLLECTED</span></h1>
    <p style="margin-top: 1rem">继续加油<br>
        <span>KEEP GOING</span><br>
        更多的荣誉在等你<br>
        <span>MORE TROPHIES TO COME</span>
    </p>
</aside>
<aside class="celebrate 200km" style="display:none">
    <a class="close b-close"></a>
    <p>恭喜你完成第一个200公里跑<br>
        <span>CONGRATULATIONS! YOU’VE FINISHED  200KM RUNNING</span>
    </p>
    <div class="img-bg"><img src="img/trophy-200km.png" width="40%"></div>
    <h1>获得200KM奖杯1个<br><span>200 KM TROPHY COLLECTED</span></h1>
    <p style="margin-top: 1rem">继续加油<br>
        <span>KEEP GOING</span><br>
        更多的荣誉在等你<br>
        <span>MORE TROPHIES TO COME</span>
    </p>
</aside>
<aside class="celebrate team-win" style="display:none">
    <a class="close b-close"></a>
    <p>恭喜你的团队取得一次胜利<br>
        <span>CONGRATULATIONS! YOUR TEAM WON IN ONE THEME RUN CHALLENGE</span>
    </p>
    <div class="img-bg"><img src="img/trophy-200km.png" width="40%"></div>
    <h1>获得团队胜利奖杯1个<br><span>TEAM VICTORY TROPHY COLLECTED</span></h1>
    <p style="margin-top: 1rem">继续加油<br>
        <span>KEEP GOING</span><br>
        更多的荣誉在等你<br>
        <span>MORE TROPHIES TO COME</span>
    </p>
</aside>
<aside class="celebrate king" style="display:none">
    <a class="close b-close"></a>
    <p>恭喜你集齐所有奖杯<br>
        <span>CONGRATULATIONS! YOU COLLECTED ALL TROPHIES</span>
    </p>
    <div class="img-bg"><img src="img/trophy-king.png" width="53%"></div>
    <h1>获得跑步达人奖杯一个<br><span>RUNNING GURU TROPHY COLLECTED</span></h1>
    <p style="margin-top: 1rem">继续加油<br>
        <span>KEEP GOING</span><br>
        更多的荣誉在等你<br>
        <span>MORE TROPHIES TO COME</span>
    </p>
</aside>
<?php include "share_dtc.php"; ?>
<script src="js/jquery.bpopup.js"></script>
<script type="text/javascript">
    <?php
    if($is_get_1km && $is_get_10km && $is_get_share1 && $is_get_3d5km && $is_get_100km && $is_get_sharemany && $is_get_200km &&$is_get_team &&!$is_show_king){
    $query = "UPDATE dtc_trophy SET king_show = '1' where openid = '{$openid}'";
    mysql_query($query, $conn);
    ?>
    $('.king').bPopup();
    <?php }elseif($row_trophy['team_win'] && !$row_trophy['team_win_show']){
    $query = "UPDATE dtc_trophy SET team_win_show = '1' where openid = '{$openid}'";
    mysql_query($query, $conn);
    ?>
    $('.team-win').bPopup();
    <?php }elseif($row_trophy['200km'] && !$row_trophy['200km_show']){
        $query = "UPDATE dtc_trophy SET 200km_show = '1' where openid = '{$openid}'";
        mysql_query($query, $conn);
    ?>
    $('.200km').bPopup();
    <?php }elseif($row_trophy['sharemany'] && !$row_trophy['sharemany_show']){
    $query = "UPDATE dtc_trophy SET sharemany_show = '1' where openid = '{$openid}'";
    mysql_query($query, $conn);
    ?>$('.sharemany').bPopup();
    <?php }elseif($row_trophy['100km'] && !$row_trophy['100km_show']){
    $query = "UPDATE dtc_trophy SET 100km_show = '1' where openid = '{$openid}'";
    mysql_query($query, $conn);
    ?>$('.100km').bPopup();
    <?php }elseif($row_trophy['3d5km'] && !$row_trophy['3d5km_show']){
    $query = "UPDATE dtc_trophy SET 3d5km_show = '1' where openid = '{$openid}'";
    mysql_query($query, $conn);
    ?>$('.3d5k').bPopup();
    <?php }elseif($row_trophy['share1'] && !$row_trophy['share1_show']){
    $query = "UPDATE dtc_trophy SET share1_show = '1' where openid = '{$openid}'";
    mysql_query($query, $conn);
    ?>$('.share1').bPopup();
    <?php }elseif($row_trophy['10km'] && !$row_trophy['10km_show']){
    $query = "UPDATE dtc_trophy SET 10km_show = '1' where openid = '{$openid}'";
    mysql_query($query, $conn);
    ?>$('.10km').bPopup();
    <?php }elseif($row_trophy['1km'] && !$row_trophy['1km_show']){
    $query = "UPDATE dtc_trophy SET 1km_show = '1' where openid = '{$openid}'";
    mysql_query($query, $conn);
    ?>$('.1km').bPopup();
    <?php }?>

</script>
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
    'result' => mysql_real_escape_string(json_encode_cn($joinUserRow)),
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
