<?php

//ini_set("display_errors", "On");
//error_reporting(E_ALL | E_STRICT);

require_once "./header.php";
$conn = connect_to_db();

$apiStartTime = getMicrotime();
$apiUrl = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
$loginIP = getClientIP();
$pagetype = 'team_member';

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
        }else{
            if(isset($_GET['type'])){
                $date=$_GET['type'];
            }else{
                $date=1;
            } //任务默认为1
            $query = "SELECT * FROM dtc_taskone_marathon WHERE openid='$openid' LIMIT 1";
            $result = mysql_query($query,$conn);
            if(is_resource($result) && mysql_num_rows($result) == 0){
//                $headerLocation = 'mls-choose.php';
            }
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

$phpfile = isset($phpfile) ? $phpfile : "campaign_nike/running/dtc/rtw-invite.php?ptOpenid=".$openid;

?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
        <!--    引入自己的css-->
        <link rel="stylesheet" href="css/reset.css">
        <link rel="stylesheet" href="//at.alicdn.com/t/font_8ddf6q9x52ucv7vi.css"/>
        <!--引入自己的Js-->
        <script src="js/responsive.js"></script>
        <!--引用Jquery-->
        <script src="js/jquery-2.1.4.js"></script>
        <script src="js/jquery-2.1.3.min.js"></script>
        <script src="js/template.js"></script>
        <script src="js/jquery.bpopup.js"></script>
        <title>里享</title>
        <style>
            @font-face {
                font-family: 'fornike365';
                src: url('./font/tradegothicfornike365-bdcn.eot');
                src: local('☺'), url('./font/tradegothicfornike365-bdcn.woff') format('woff'), url('./font/tradegothicfornike365-bdcn.ttf') format('truetype'), url('./font/tradegothicfornike365-bdcn.svg') format('svg');
                font-weight: normal;
                font-style: normal;
            }
            .rank {
                background: url(./img/bg.png) no-repeat;
                background-size: cover;
                min-height: 43.3rem;
                box-sizing: border-box;
                padding: 1.3rem 1rem 2rem;
                overflow: hidden;
            }

            .rank .box {
                padding: 1.5rem 0;
                width: 23.5rem;
                height: 33rem;
                margin: 0.5rem auto 0;
                background: rgba(255, 255, 255, 0.8);
                border: 2px solid #fa6b0e;
                box-shadow: 0 10px 0 rgba(250, 106, 6, 0.8);
            }
            .tab-title{
                display: none;
            }
            .item_1{
                display: block;
                border-left: 1px solid #ffffff;
                width: 50%;
                height: 2.2rem;
                padding: 0.2rem 0 0;
                line-height: 1.1rem;
                font-family: 'fornike365';
                font-weight: lighter;
                letter-spacing: 1px;
            }
            .tab_1{
                display: flex;
                width: 22rem;
                margin: 0 auto 1rem;
                align-items: center;
                justify-content: space-between;
                background: #7b7b7b;
                border-radius: 8px;
                font-size: 1rem;
                color: #ffffff;
                font-weight: bold;
                text-align: center;
            }
            .brl{
                border-top-left-radius:8px;
                border-bottom-left-radius:8px;
            }
            .brr{
                border-top-right-radius:8px;
                border-bottom-right-radius:8px;
            }
            .active{
                background: #fa6b0e;
                color: #fff;
            }
            .active-1{
                display: block!important;
            }
            .home{
                display: block;
                width: 3.2rem;
                height: 3.2rem;
                position: absolute;
                left: 1rem;
                top:1rem;
            }
            .prize{
                display: block;
                width: 3.2rem;
                height: 3.2rem;
                position: absolute;
                top: 1rem;
                right:1rem;
            }
            .li-text{
                display: inline-block;
                width: 100%;
            }
            .li-text span{
                font-size: 0.8rem;
            }
        </style>
    </head>
    <body>
    <div class="wrap rank">
        <a class="home" href="rtw-task-choose.php"><img src="./img/back-icon.png" width="30%"></a>
        <a class="prize" href="main.php"><img src="./img/home-icon-1.png" width="60%"></a>
        <div style="height: 2rem"></div>
        <div class="box">
            <div class="tab_1" >
                <a class="item_1  brl <?php if($date==1){?>active<?php } ?>" data-mid="1"><p class="li-text">男子<br><span>MEN</span></p></a>
                <a class="item_1 brr  <?php if($date==2){?>active<?php } ?>" data-mid="2"><p class="li-text">女子<br><span>WOMAN</span></p></a>
            </div>
            <div class="tab-title <?php if($date==1){?>active-1<?php } ?>" data-t="1">
                <img src="img/mystery-1.png" width="100%">
            </div>
            <div class="tab-title <?php if($date==2){?>active-1<?php } ?>" data-t="2">
                <img src="img/mystery-2.png" width="100%">
            </div>
        </div>
    </div>
    <script>
        $(".tab_1 .item_1").on("click", function () {
            $(this).addClass("active").siblings(".item_1").removeClass("active");
            $('[data-t=' + $(this).attr("data-mid") + ']').addClass("active-1").siblings(".tab-title").removeClass("active-1");
        });
    </script>

    <?php include "share_dtc_rtw.php";?>
    </body>
    </html>
<?php
//记录页面请求日志

$apiEndTime = getMicrotime();
$fetchTime = intval(($apiEndTime - $apiStartTime) * 1000);
$logArr = array(
    'openid' => "$openid",
    'type' => $pagetype,
    'ip' => $loginIP,
    'url' => $apiUrl,
    'result' => mysql_real_escape_string(json_encode_cn($team_list)),
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