<?php
require_once "./header.php";
$conn = connect_to_db();

$apiStartTime = getMicrotime();
$apiUrl = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
$loginIP = getClientIP();
$type = 'rtw_task_choose_be';

$headerLocation = ''; //跳转地址
$ownInfo = array();
$ownInfo['openid'] = $openid;  //自己的openid
$sql = "select * from dtc_join_user where openid = '$openid';";
$result = mysql_query($sql, $conn);
$joinUserRow = mysql_fetch_assoc($result);
$ownInfo['teamId'] = $joinUserRow['teamId']; //看个人teamId
$ownInfo['srcopenid'] = $joinUserRow['srcopenid'];  //邀请进到活动中来的邀请者openid
//如果自己是内跑成员，则跑团openid一定是自己
if($ownInfo['teamId'] != -1)
{
    $ownInfo['ptOpenid'] = $openid;
    $ownInfo['ptName'] = $joinUserRow['fullname'];
    $ownInfo['isJoin'] = 1;
}
else
{
    //看用户是否加入跑团
    $query = "SELECT src_openid FROM dtc_rtw_join_user WHERE openid = '$openid'";
    $result = mysql_query($query,$conn);
    if(mysql_num_rows($result) != 0 )
    {
        $row = mysql_fetch_assoc($result);
        $ownInfo['ptOpenid'] = $row['src_openid'];
        $ownInfo['isJoin'] = 1; //为1代表已参加跑团
        $ptOpenid = $ownInfo['ptOpenid'];
        $sql1 = "select * from dtc_join_user where openid = '$ptOpenid';";
        $result1 = mysql_query($sql1, $conn);
        $row1 = mysql_fetch_assoc($result1);
        $ownInfo['ptName'] = $row1['fullname'];
    }else
    {
        $ownInfo['ptOpenid'] = '';
        $ownInfo['ptName'] = '';
        $ownInfo['isJoin'] = 0;  //为0代表未参加跑团
    }
}

//取当前链接中携带的跑团信息
$ptOpenidUrl = '';
$ptNameUrl = '';  //链接里带的跑团名字
if(isset($_GET['ptOpenid']) && $_GET['ptOpenid'] != '')
{
    $ptOpenidUrl = $_GET['ptOpenid'];
    $query = "SELECT * FROM dtc_join_user WHERE openid = '$ptOpenidUrl'";
    $result = mysql_query($query,$conn);
    $row = mysql_fetch_assoc($result);
    $ptNameUrl = $row['fullname'];
}

$srcOpenid = '';
if(!empty($_GET['srcopenid']))
{
    $srcOpenid = $_GET['srcopenid'];
}


//进到首页，可简单判断，如果用户teamId不为-1且已确认开启任务且已绑定Nike+则留在本页面；其他情况统一交给join-choose处理
if ($ownInfo['teamId'] != -1)
{    //之前有参加过
    $headerLocation = 'theme-run.php';
} else
{//如果没绑定
    if( $_SESSION['userInfo']['isBindNike'] != 1)
    {
        $headerLocation = 'rtw-theme-run-be.php';
    }else{//如果绑定了
        //如果rtw_join里没有，则跳去main
        $query = "SELECT id FROM dtc_rtw_join_user WHERE openid = '$openid'";
        $result = mysql_query($query,$conn);
        if(mysql_num_rows($result)!=1){
            $headerLocation = 'rtw-theme-run-be.php';
        }
    }
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

//设置分享页面
if($ownInfo['isJoin'] == 0)
{
    $phpfile = isset($phpfile) ? $phpfile : "campaign_nike/running/dtc/rtw-invite.php?ptOpenid=".$ptOpenidUrl;
}
else
{
    $phpfile = isset($phpfile) ? $phpfile : "campaign_nike/running/dtc/rtw-invite.php?ptOpenid=".$ownInfo['ptOpenid'];
}

?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
        <!--    引入自己的css-->
        <link rel="stylesheet" href="css/reset.css">
        <link rel="stylesheet" href="css/theme-run.css?a=<?php echo rand();?>"/>
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
            .home-icon{
                display: inline-block;
                width: 2rem;
                height: 2rem;
            }
            .rtw-task-area{
                margin: 2rem 1.0rem 0;
            }
            .rtw-task{
                display: inline-block;
                width: 48%;
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
        </style>
    </head>
    <body>
    <div class="wrap theme-run" name="task-one">
        <div class="content">
            <div style="margin: 1rem 1.5rem 0;text-align:right"><a class="home-icon" href="rtw-theme-run-be.php"><img src="./img/home-icon-1-1.png" width="100%"></a></div>
            <div class="explain" style="margin-top: 0">
                <h1 style="font-weight: normal">任务说明</h1>
                <span>WHAT’S THE MISSION?</span>
                <div style="background: #fe4819;height: 1px;width: 6.5rem; margin: 0 auto 1.5rem;"></div>
                <p style="width: 19.5rem;text-align: left;">
                    1. 自11月25日至12月14日，将不定期上线4个主题任务。<br>
                    2. 帮助你的小伙伴完成任务，你也将有机会获得精美礼品。
                </p>
                <p style="width: 19.5rem;text-align: left;">
                    1. FROM NOV. 25 TO DEC. 14, 4 TASKS WILL BE RELEASED PERIODICALLY.<br>
                    2. HELP YOUR FRIEND COMPLETE THE TASK, AND YOU WILL GET THE CHANCE TO WIN THE PRIZE AS WELL!
                </p>
                <div class="rtw-task-area">
                    <a class="rtw-task" id="task1"><img src="./img/rtw-task1.png" width="100%"></a>
                    <a class="rtw-task" id="task2"><img src="./img/rtw-task2.png" width="100%"></a>
                    <a class="rtw-task" id="task3" style="margin-top: 0.5rem"><img src="./img/rtw-task3.png" width="100%"></a>
                    <a class="rtw-task" id="task4" style="margin-top: 0.5rem"><img src="./img/rtw-task4.png" width="100%"></a>
                </div>
            </div>


        </div>
        <?php include "share_dtc_rtw.php";?>
    </div>
    <aside class="joinbefor" id="unopen" style="display:none">
<!--        <a class="close b-close"></a>-->
        <p>任务尚未开启，敬请期待！<br>
            <span>TASK HASN’T GONE LIVE YET. STAY TUNED!</span>
        </p>
        <a class="confirm-btn b-close">确认<br><span>CONFIRM</span></a>
    </aside>
    <script>
        $('#task1').on('click',function () {
            $('#unopen').bPopup({
             positionStyle:'fixed'
             })
            //window.location.href='rtw-task-explain-be.php?type=1';
        });
        $('#task2').on('click',function () {
            $('#unopen').bPopup({
                positionStyle:'fixed'
            })
             // window.location.href='rtw-task-explain-be.php?type=2';
        });
        $('#task3').on('click',function () {
            $('#unopen').bPopup({
                positionStyle:'fixed'
            });
             // window.location.href='rtw-task-explain-be.php?type=3';
        });
        $('#task4').on('click',function () {
            $('#unopen').bPopup({
                positionStyle:'fixed'
            });
             // window.location.href='rtw-task-explain-be.php?type=4';
        });
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
