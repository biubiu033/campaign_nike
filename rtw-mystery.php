<?php
require_once "./header.php";
$conn = connect_to_db();

$apiStartTime = getMicrotime();
$apiUrl = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
$loginIP = getClientIP();
$type = 'rtw-task-choose';

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
            //$headerLocation = 'join-choose.php';
        }else{
            $fullNmae = $joinUserRow['fullname'];
            $teamId = $joinUserRow['teamId'];
            $query = "INSERT INTO dtc_rtw_join_user (`openid`,`src_openid`,`full_name`,`team_id`,`create_time`) VALUES
                    ('$openid','0','$fullNmae','$teamId',NOW())";
            mysql_query($query,$conn);
        }
    } else
    {
       // $headerLocation = 'join-choose.php';
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
        <!--引入自己的Js-->
        <script src="js/responsive.js"></script>
        <!--引用Jquery-->
        <script src="js/jquery-2.1.3.min.js"></script>
        <script src="js/jquery.bpopup.js"></script>
        <title>里享</title>
        <style>
            .foot{
                margin-top: 4rem;
            }
            .foot-a{
                color:#c54c0f;
            }
            @font-face {
                font-family: 'fornike365';
                src: url('./font/tradegothicfornike365-bdcn.eot');
                src: local('☺'), url('./font/tradegothicfornike365-bdcn.woff') format('woff'), url('./font/tradegothicfornike365-bdcn.ttf') format('truetype'), url('./font/tradegothicfornike365-bdcn.svg') format('svg');
                font-weight: normal;
                font-style: normal;
            }
            body{
                background: #fe8c03 url(./img/bg.png)no-repeat;
                background-size: cover;
                position: relative;
                margin: 0 0;
                font-family: "STHeiti SC";
            }
            .theme-run{
                text-align: center;
            }
            .home-icon{
                display: inline-block;
                width: 2rem;
                height: 2rem;
            }
            .title{
                padding:  1.5rem 1.5rem;
                background: rgba(250, 107, 14, 0.8);
                box-shadow: 0px 5px 0px #ef4004;
            }
            .personal{
                display: flex;
                align-items: center;
                justify-content: flex-start;
            }
            .body{
                color: #fff;
                margin: 0 auto;
            }
            .body p{
                font-family: 'fornike365';
                display: inline-block;
                font-size: 1.8rem;
                text-align: center;
                line-height: 1.5rem;
                letter-spacing: 1px;
            }
            .body span{
                font-size: 1.0rem;
            }
            .rtw-prize{
                border-bottom: 2px solid #e27601;
                box-sizing: border-box;
                padding: 1.6rem 0 1.0rem;
            }
            .rtw-prize p{
                color: #ffffff;
                font-family: 'fornike365';
                letter-spacing: 1px;
                font-size: 1.2rem;
                margin-top: 0.2rem;
            }
            .rtw-prize .task-num{
                background: #ffffff;
                width: 7rem;
                border-radius: 6px;
                padding: 0.25rem;
                margin: 0 auto 0.6rem;
                color: #fe4819!important;
                font-family: 'fornike365';
                font-size: 1rem;

            }
            .confirm-pop{
                width: 85%;
                margin: 0 auto;
                height: 27rem;
            }
            .confirm-btn {
                padding-top: 0.3rem;
                display: block;
                background: rgb(255, 240, 231);
                box-shadow: 1px 2px 0 #b83a0f;
                border: 2px solid #ea5012;
                width: 10rem;
                height: 2.2rem;
                line-height: 1.8rem;
                border-radius: 10px;
                text-align: center;
                color: #fc5219;
                font-size: 1.1rem;
                font-family: 'fornike365';
                /* font-style: oblique; */
                margin: 1.5rem auto 0;
            }
            .confirm-btn span{
                font-size: 0.6rem;
                font-family: 'fornike365';
                font-style: oblique;
                font-weight: 100;
            }
        </style>
    </head>
    <body>
    <div class="wrap theme-run">
        <div class="content">
            <div class="title">
                <div style="text-align:right">
                    <a class="home-icon" href="main.php"><img src="./img/home-icon-1.png" width="100%"></a>
                </div>
                <div class="personal">
                    <div class="body">
                        <p>Nike ID CODE使用期限<br><span>2018年1月1日至2018年4月1日</span></p>
                    </div>
                </div>
            </div>
            <div class="rtw-prize-area">
               <div class="rtw-prize" >
                   <p class="task-num">任务一奖品</p>
                   <p>NIKE LUNARGLIDE 9 ID</p>
                   <p>ID CODE:未得奖</p>
               </div>
                <div class="rtw-prize" >
                    <p class="task-num">任务二奖品</p>
                    <p>NIKE AIR ZOOM PEGASUS 34 SHIELD ID</p>
                    <p>ID CODE:XXXXXXXX</p>
                </div>
                <div class="rtw-prize" >
                    <p class="task-num">任务三奖品</p>
                    <p>NIKE AIR MAX 2017 ID</p>
                    <p>ID CODE:XXXXXXX</p>
                </div>
                <div class="rtw-prize" style="border: none">
                    <p class="task-num">任务四奖品</p>
                    <p>NIKE AIR VAPORMAX FLYKNIT ID</p>
                    <p>ID CODE:XXXXXXXX</p>
                </div>
            </div>
        </div>
        <aside class="joinbefor" id="unopen" style="display:none">
            <!--            <a class="close b-close"></a>-->
            <p>任务尚未开启，敬请期待！<br>
                <span>TASK HASN’T GONE LIVE YET. STAY TUNED!</span>
            </p>
            <a class="confirm-btn ">确认<br><span>CONFIRM</span></a>
        </aside>
        <aside class="confirm-pop" style="display:none">
                <img src="./img/rule-confirm.png" width="100%">
            <a class="confirm-btn b-close">接受</a>
        </aside>
        <script>
            $('.confirm-pop').bPopup({
                positionStyle:'fixed'
            })
        </script>
    </body>
    </html>
<?php include "share_dtc_rtw.php";?>
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
