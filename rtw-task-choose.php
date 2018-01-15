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
            $headerLocation = 'join-choose.html';
        }else{
            $fullNmae = $joinUserRow['fullname'];
            $teamId = $joinUserRow['teamId'];
            $query = "INSERT INTO dtc_rtw_join_user (`openid`,`src_openid`,`full_name`,`team_id`,`create_time`) VALUES
                    ('$openid','0','$fullNmae','$teamId',NOW())";
            mysql_query($query,$conn);
        }
    } else
    {
        $headerLocation = 'join-choose.html';
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
            .rtw-task-area{
                margin: 3.5rem 0.5rem 0 1rem;
            }
            .rtw-task{
                display: inline-block;
                width: 48%;
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
                font-size: 2.2rem;
                 text-align: center;
                 line-height: 1.5rem;
            }
            .body span{
                font-size: 1.0rem;
            }
            .prize{
                padding-top: 0.6rem;
                box-sizing: border-box;
                display: inline-block;
                background: rgba(255, 255, 255, 0.8);
                box-shadow: 0px 3px 0 #eb5e0c;
                border: 2px solid #ea5012;
                width: 10rem;
                height: 3rem;
                line-height: 0.9rem;
                border-radius: 10px;
                text-align: center;
                color: #fc5219;
                font-family: 'fornike365';
                font-size: 1.1rem;
                font-weight: bold;
                margin: 2rem auto 0;
            }
            .prize span{
                font-size: 0.7rem;
                font-family: 'fornike365';
                font-style: oblique;
                font-weight: 100;
            }
            .joinbefor{
                padding: 1.5rem;
                margin:2rem auto 0;
                background: rgba(255,255,255,0.9);
                box-shadow: 5px 5px 0 rgba(250, 106, 6, 0.9);
                box-sizing: border-box;
                width: 22rem;
                text-align: center;
            }
            .joinbefor .close{
                display: block;
                background: url("./img/close-btn.png")no-repeat;
                background-size: cover;
                height: 1.5rem;
                width: 1.5rem;
                margin-left: 18rem;
            }
            .joinbefor p{
                margin: 0.4rem auto 1rem;
                display: block;
                font-size: 1.35rem;
                text-align: center;
                color: #f76a0e;
                font-weight: bold;
                line-height: 1.4rem;
            }
            .joinbefor p span{
                font-family: 'fornike365';
                font-size: 0.95rem;
                font-weight: 100;
            }
            .confirm-btn {
                padding-top: 0.3rem;
                display: block;
                background: rgba(255, 255, 255, 0.8);
                box-shadow: 1px 2px 0 #b83a0f;
                border: 2px solid #ea5012;
                width: 8rem;
                height: 2.2rem;
                line-height: 1rem;
                border-radius: 10px;
                text-align: center;
                color: #fc5219;
                font-size: 1.0rem;
                font-style: oblique;
                margin: 1.5rem auto 0;
            }
            .confirm-btn span{
                font-size: 0.6rem;
                font-family: 'fornike365';
                font-style: oblique;
                font-weight: 100;
            }
            #superise{
                margin-right: 0.7rem;
                margin-left: 0.7rem;
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
                        <p>完成任务 赢取跑鞋<br><span>COMPLETE THE TASKS TO WIN THE RUNNING SHOES</span></p>
                    </div>
                </div>
            </div>
                <div class="rtw-task-area">
                    <a class="rtw-task" id="task1"><img src="./img/rtw-task1-1.png" width="100%"></a>
                    <a class="rtw-task" id="task2"><img src="./img/rtw-task2-1.png" width="100%"></a>
                    <a class="rtw-task" id="task3" style="margin-top: 1rem"><img src="./img/rtw-task3-1.png" width="100%"></a>
                    <a class="rtw-task" id="task4" style="margin-top: 1rem"><img src="./img/rtw-task4-1.png" width="100%"></a>
                </div>
        </div>
        <a class="prize" id="superise">神秘奖品<br><span>SUPERISE PRIZE</span></a>
        <a class="prize" href="rtw-mystery.php">我的奖品<br><span>MY PRIZE</span></a>
        <aside class="joinbefor" id="unopen" style="display:none">
<!--            <a class="close b-close"></a>-->
            <p>任务尚未开启，敬请期待！<br>
                <span>TASK HASN’T GONE LIVE YET. STAY TUNED!</span>
            </p>
            <a class="confirm-btn b-close">确认<br><span>CONFIRM</span></a>
        </aside>
        <aside class="joinbefor" id="openprize" style="display:none">
<!--            <a class="close b-close"></a>-->
            <p>集齐四双跑鞋，召唤神秘大奖<br>
                <span>GET ALL FOUR PAIRS AND UNLOCK THE GRAND PRIZE!</span>
            </p>
            <img src="./img/prize-box.png" width="45%">
            <div style="height: 2rem"></div>
            <a class="confirm-btn b-close">确认<br><span>CONFIRM</span></a>
        </aside>
      <script>
          $('#task1').on('click',function () {
              /*$('#unopen').bPopup({
                  positionStyle:'fixed'
              });*/
              window.location.href='rtw-task-explain.php?type=1';
          });
          $('#task2').on('click',function () {
              /*$('#unopen').bPopup({
                  positionStyle:'fixed'
              })*/
              window.location.href='rtw-task-explain.php?type=2';
          });
          $('#task3').on('click',function () {
             /* $('#unopen').bPopup({
                  positionStyle:'fixed'
              })*/
              window.location.href='rtw-task-explain.php?type=3';
          });
          $('#task4').on('click',function () {
              /*$('#unopen').bPopup({
                  positionStyle:'fixed'
              })*/
              window.location.href='rtw-task-explain.php?type=4';
          });
          $('#superise').on('click',function () {
              $('#openprize').bPopup({
                  positionStyle:'fixed'
              })
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
