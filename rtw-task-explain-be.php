<?php
require_once "./header.php";
$conn = connect_to_db();

$apiStartTime = getMicrotime();
$apiUrl = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
$loginIP = getClientIP();
$type = 'rtw_task_explain_be';

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
if ($ownInfo['teamId'] != -1 )
{    //之前有参加过
    $headerLocation = 'theme-run.php';
} else {//之前没参加过
    if( $_SESSION['userInfo']['isBindNike'] != 1 || $ownInfo['isJoin'] !=1)
    {
        $headerLocation = 'rtw-theme-run-be.php';
    }else{//如果绑定了

        //如果rtw_join里没有，则跳去main
        $query = "SELECT id,src_openid FROM dtc_rtw_join_user WHERE openid = '$openid'";
        $result = mysql_query($query, $conn);
        if (mysql_num_rows($result) != 1) {
            $headerLocation = 'rtw-theme-run-be.php.php';
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


if ($_GET['type']!='1' && $_GET['type']!='2' && $_GET['type']!='3' && $_GET['type']!='4'){
    $tab_type='1';
}else{
    $tab_type=$_GET['type'];
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
        <!--引入自己的Js-->
        <script src="js/responsive.js"></script>
        <!--引用Jquery-->
        <script src="js/jquery-2.1.3.min.js"></script>
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
            body{
                background: #fe8c03;
            }
            .theme-run {
                background: url(./img/bg.png) no-repeat;
                background-size: cover;
                position: relative;
                margin: 0 0;
                font-family: "STHeiti SC";
                box-sizing: border-box;
                padding-top: 1rem;
                height: 43rem;
            }
            .theme-run .explain{
                text-align: center;
                box-sizing: border-box;
                padding-top: 1rem;
            }
            .theme-run .explain h1{
                font-style: oblique;
                font-weight: normal;
                color: #ffffff;
            }
            .theme-run .explain span{
                display: block;
                margin-top: -0.3rem;
                margin-bottom: 0.5rem;
                font-style: oblique;
                color: #ffffff;
                font-weight: bold;
                font-size: 0.8rem;
                font-family: 'fornike365';
            }
            .theme-run .explain p{
                text-align: left;
                display: block;
                margin: 1rem 1rem 0 0;
                color: #ffffff;
                font-size: 0.95rem;
                font-family: 'fornike365';
            }
            .theme-run .warn{
                display: inline-block;
                border-radius: 6px;
                background: #ffffff;
                color: #fe4819;
                font-family: 'fornike365';
                width: 6rem;
                text-align: center;
                font-size: 0.9rem;
                padding: 0.3rem 0;
                margin: 1rem 0 0;
            }
            .theme-run .warn-list{
                margin: 1rem auto 0;
                width: 19rem;
                padding-left: 2.5rem;
            }
            .theme-run .warn-list li{
                list-style: disc;
                color: #ffffff;
                font-size: 0.8rem;
                margin-bottom: 0.5rem;
                text-align: left;
                font-family: 'fornike365';
            }
            .theme-run .warn-list li p{
                margin: 0!important;
                font-size: 0.7rem!important;
            }
            .home-icon{
                display: inline-block;
                width: 2rem;
                height: 2rem;
            }
            .btn-style{
                padding-top: 0.3rem;
                display: block;
                background: rgba(255, 255, 255, 0.8);
                box-shadow: 2px 3px 0 #b83a0f;
                border: 2px solid #ea5012;
                width: 10.8rem;
                height: 2.6rem;
                line-height: 1.5rem;
                border-radius: 8px;
                text-align: center;
                color: #fc5219;
                font-size: 1.2rem;
                font-family: 'fornike365';!important;
                font-style: oblique;
                margin: 1.5rem auto 0;
            }
            .btn-style span{
                color: #fc5219!important;

                font-size: 0.7rem!important;
            }
            .share-popup{
                display: none;
                width: 26.6666rem;
                height:40rem;
                padding-top: 0;
            }
        </style>
    </head>
    <body>
    <div class="wrap theme-run">
        <div style="margin: 0 1.5rem;text-align:right"><a class="home-icon" href="rtw-theme-run-be.php"><img src="./img/home-icon-1.png" width="100%"></a></div>
        <div class="explain">
            <h1>任务说明</h1>
            <span>WHAT’S THE TASK？</span>
            <div style="background: #ffffff;height: 1px;width: 6.5rem; margin: 0 auto 1rem;"></div>
            <div style="margin: 0 2rem 0 2rem;text-align: left;">
                <div class="warn">活动规则</div>
                <?php switch ($tab_type){
                    case 1:echo ' <p >
                恭喜你已成为'.$ownInfo['ptName'].'跑团成员，邀请更多好友壮大你们的队伍吧！<br>
                自11月25日9:00起至11月29日21:00，跑团人数最多的前十名跑团团长将成为本轮任务赢家。
            </p>';break;
                    case 2:echo ' <p style="font-size: .8rem">
               自11月30日9:00至12月4日21:00，坚持每天完成2公里。<br>
            活动期间完成任务人数最多的前十名跑团团长将成为本轮任务赢家。<br>
            连续5天完成任务且团队成绩排名前十的跑团成员将获得礼品一份。
                </p>
            ';break;
                    case 3:echo ' <p>
               自12月5日9:00至12月9日21:00，系统将每日对跑团跑步里程进行统计。<br>
                5天内单日跑团里程最高的前十名跑团团长将成为本轮任务赢家。

            </p>';break;
                    case 4:echo '<p>
                自12月10日9:00至12月14日21:00，各跑团里程统计将以1.5倍进行膨胀计入总里程。<br>
                自11月25日至12月14日活动期间，跑团总里程累积前十名的跑团团长将成为本轮任务赢家。

            </p>';break;
                }?>
                <div class="warn" style="margin-top: 2rem">RULES</div>
                <?php switch ($tab_type){
                    case 1:echo '<p style="font-size: 0.9rem;">
                CONGRATULATIONS! YOU ARE NOW A MEMBER OF '.$ownInfo['ptName'].'’S TEAM! INVITE MORE FRIENDS TO EXPAND YOUR TEAM!<BR>
               TASK ONE STARTS FROM 9:00 NOV. 25 TO 21:00 NOV. 29. CAPTAINS OF THE LARGEST TEN TEAMS WILL BE THE WINNERS OF THE TASK.
            </p>';break;
                    case 2:echo ' <p  style="font-size: .8rem">
               FROM 9:00 NOV. 30 TO 21:00 DEC. 4, RUN 2K EACH DAY.<br>
                TEAMS WILL BE RANKED BASED ON THE NUMBER OF MEMBERS WHO COMPLETE THE TASK. CAPTAINS OF TOP TEN TEAMS WILL BE THE WINNERS.<br>
                MEMBERS OF TOP TEN TEAMS WHO COMPLETE THE TASK IN CONSECUTIVE 5 DAYS WILL GET A SMALL PRIZE AS WELL.

                </p>';break;
                    case 3:echo '<p>
               FROM 9:00 DEC. 5 TO 21:00 DEC. 9, THE MILEAGE OF EACH TEAM WILL BE COUNTED EACH DAY.<br>
                TEAMS WILL BE RANKED BASED ON THE HIGHEST SINGLE-DAY MILEAGE DURING THE TASK. CAPTAINS OF TOP TEN TEAMS WILL BE THE WINNERS.

           </p>';break;
                    case 4:echo '  <p>
           FROM 9:00 DEC. 10 TO 21:00 DEC. 14, THE MILEAGE OF EACH TEAM WILL BE EXPANDED TO 1.5 TIMES, THEN BE COUNTED INTO THE TOTAL MILEAGE.<br>
            TEAMS WILL BE RANKED BASED ON THE TOTAL MILEAGE FROM NOV. 25 TO DEC. 14. CAPTAINS OF TOP TEN TEAMS WILL BE THE WINNERS.

         </p> ';break;
                }?>
                <a class="btn-style" id="invite" style="margin-top: 3rem">邀请小伙伴<br><span>INVITE FRIENDS</span></a>
                <a class="btn-style" href="rtw-team-rank-be.php?type=<?php echo $tab_type?>"><?php echo $ownInfo['ptName']?>的跑团<br><span>TEAM OF <?php echo $ownInfo['ptName']?></span></a>
            </div>
        </div>
    </div>
    <div id="share" class="share-popup">
        <img class="b-close" src="./img/share.png" width="100%">
    </div>
    <script>
        $('#invite').on('click',function () {
            $('#share').bPopup({
                positionStyle:'fixed'
            })
        })
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
