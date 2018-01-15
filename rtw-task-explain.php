<?php
require_once "./header.php";
$conn = connect_to_db();

$apiStartTime = getMicrotime();
$apiUrl = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
$loginIP = getClientIP();
$type = 'rtw_task_explain';

$headerLocation = ''; //跳转地址
$sql = "select teamId,srcopenid from dtc_join_user where openid = '$openid';";
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

if ($_GET['type']!='1' && $_GET['type']!='2' && $_GET['type']!='3' && $_GET['type']!='4'){
    $tab_type='1';
}else{
    $tab_type=$_GET['type'];
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
            text-align: left;
            box-sizing: border-box;
            padding: 0 0 1rem;
            margin: 0 1.5rem;
            height: 27rem;
            overflow-y: scroll;
        }
        .theme-run  h1{
            font-style: oblique;
            font-weight: normal;
            color: #ffffff;
            text-align: center;
        }
        .theme-run  span{
            display: block;
            margin-top: -0.3rem;
            margin-bottom: 0.5rem;
            font-style: oblique;
            color: #ffffff;
            font-weight: bold;
            font-size: 0.8rem;
            font-family: 'fornike365';
        }
        .theme-run  p{
            text-align: left;
            display: block;
            margin: 1rem auto 0;
            width: 21rem;
            color: #ffffff;
            font-size: 0.95rem;
            font-family: 'fornike365';
        }
        .theme-run .warn{
            text-align: left;
            display: inline-block;
            border-radius: 6px;
            background: #ffffff;
            color: #fe4819;
            font-family: 'fornike365';
            /*width: 5.3rem;*/
            font-size: 0.8rem;
            padding: 0.3rem 0.8rem;
            margin: 1rem 0 0 1rem;
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
            padding-top: 0.4rem;
            display: inline-block;
            background: rgba(255, 255, 255, 0.8);
            box-shadow: 2px 3px 0 #b83a0f;
            border: 2px solid #ea5012;
            width: 9rem;
            height: 2.2rem;
            line-height: 1.3rem;
            border-radius: 8px;
            text-align: center;
            color: #fc5219;
            font-size: 1.0rem;
            font-family: 'fornike365';
            margin: 1.5rem auto 0;
        }
        .btn-style span{
            color: #fc5219!important;
            font-size: 0.65rem!important;
        }
        ::-webkit-scrollbar {
            border-radius: 10px;
            width: 8px;
            height: 16px;
            background-color: #cccccc;
        }
        ::-webkit-scrollbar-track {
            border-radius: 10px;
            background-color: #cccccc;
        }
        ::-webkit-scrollbar-thumb {
            height: 5px;
            border-radius: 10px;
            background-color: #fa6b0e;
        }
    </style>
</head>
<body>
<div class="wrap theme-run">
    <div style="margin: 0 1.5rem;text-align:right"><a class="home-icon" href="main.php"><img src="./img/home-icon-1.png" width="100%"></a></div>
        <h1>任务说明</h1>
        <span style="text-align: center">WHAT’S THE TASK？</span>
        <div style="background: #ffffff;height: 1px;width: 6.5rem; margin: 0 auto 1rem;"></div>
    <div class="explain">
            <?php switch ($tab_type){
                case 1:echo ' <div class="warn">活动规则 <big>|</big> RULES</div>
                <p>
                自11月25日9:00至11月29日21:00，组建自己的跑团，邀请小伙伴加入。<br>
                系统将按跑团人数对团队进行排名，排名前十的跑团团长将获得NIKE LUNARGLIDE 9 ID跑鞋一双。
            </p>
            <p style="font-size: 0.9rem;">
              FROM 9:00 NOV. 25 TO 21:00 NOV. 29, EACH RUNNER CAN SET UP HIS/HER OWN TEAM, BE THE CAPTAIN AND INVITE FRIENDS TO JOIN THE TEAM. <br>
                CAPTAINS OF THE LARGEST TEN TEAMS WILL GET A PAIR OF NIKE LUNARGLIDE 9 ID .
            </p>
            <div class="warn" style="margin-top: 2rem;">如何邀请 <big>|</big> HOW TO INVITE?</div>
            <p >
               点击“我的跑团”，邀请小伙伴，根据页面提示<br>发送邀请。
            </p>
            <p style="font-size: 0.9rem;">
              CLICK “MY TEAM” AND FOLLOW THE GUIDANCE TO INVITE YOUR FRIENDS TO JOIN THE TEAM.
            </p>
            <div class="warn" style="margin-top: 2rem">注意事项 <big>|</big> TIPS</div>
            <p>已被邀请的用户无法再次加入你的团队。<br>
                受邀团员需关注“里享”公众号，下载NRC APP，绑定NIKE+账号。
               </p>
                <p style="font-size: 0.9rem;">
             RUNNERS WHO HAVE ALREADY BEEN INVITED CAN’T JOIN OTHER TEAMS.<br>
            INVITED RUNNERS NEED TO FOLLOW THE WECHAT ACCOUNT OF “LIXIANG”, DOWNLOAD NRC APP AND BIND NIKE+ ACCOUNT.
            </p>
            ';break;
                case 2:echo ' <div class="warn">活动规则 <big>|</big> RULES</div>
                <p >
             自11月30日9:00至12月4日21:00 ，可持续邀请小伙伴，加入自己的跑团。成员需每日完成2公里，并用NRC记录。<br>
            系统将按照连续5天完成任务的人数进行排名，排名前十的跑团团长将获得NIKE AIR ZOOM PEGASUS 34 SHIELD ID一双。 <br>
            连续5天完成任务且团队成绩排名前十的跑团成员将获得礼品一份。 

            </p>
            <p style="font-size: 0.9rem;">
               FROM 9:00 NOV. 30 TO 21:00 DEC. 4, CAPTAINS CAN CONTINUE TO INVITE FRIENDS TO JOIN THE TEAM. EACH MEMBER OF EACH TEAM SHALL RUN AT LEAST 2K EACH DAY AND RECORD IT WITH NRC.<br>
                TEAMS WILL BE RANKED BASED ON THE NUMBER OF MEMBERS WHO COMPLETE THE TASK. CAPTAINS OF TOP TEN TEAMS WILL WIN A PAIR OF NIKE AIR ZOOM PEGASUS 34 SHIELD ID. <br>
                MEMBERS OF TOP TEN TEAMS WHO COMPLETE THE TASK IN CONSECUTIVE 5 DAYS WILL GET A SMALL PRIZE AS WELL.

            </p>
             <div class="warn" style="margin-top: 2rem;">如何邀请 <big>|</big> HOW TO INVITE?</div>
            <p >
               点击“我的跑团”，邀请小伙伴，根据页面提示<br>发送邀请。
            </p>
            <p style="font-size: 0.9rem;">
              CLICK “MY TEAM” AND FOLLOW THE GUIDANCE TO INVITE YOUR FRIENDS TO JOIN THE TEAM.
            </p>
            <div class="warn" style="margin-top: 2rem">注意事项 <big>|</big> TIPS</div>
            <p>已被邀请的用户无法再次加入你的团队。<br>
                受邀团员需关注“里享”公众号，下载NRC APP，绑定NIKE+账号。
               </p>
                <p style="font-size: 0.9rem;">
             RUNNERS WHO HAVE ALREADY BEEN INVITED CAN’T JOIN OTHER TEAMS.<br>
            INVITED RUNNERS NEED TO FOLLOW THE WECHAT ACCOUNT OF “LIXIANG”, DOWNLOAD NRC APP AND BIND NIKE+ ACCOUNT.
            </p>
            ';break;
                case 3:echo ' <div class="warn">活动规则 <big>|</big> RULES</div>
                <p >
                 自12月5日9:00至12月9日21:00 ，可持续邀请小伙伴，加入自己的跑团，并用NRC记录跑步里程。<br>
                系统将每日对跑团总里程进行排名，5天内单日跑团里程最高的前十名跑团团长将获得NIKE AIR MAX 2017 ID一双。

            </p>
            <p style="font-size: 0.9rem;">
              FROM 9:00 DEC. 5 TO 21:00 DEC. 9, CAPTAINS CAN CONTINUE TO INVITE FRIENDS TO JOIN THE TEAM AND RECORD THE MILEAGE WITH NRC. <br>
            TEAMS WILL BE RANKED BASED ON THE HIGHEST SINGLE-DAY MILEAGE DURING THE TASK. CAPTAINS OF TOP TEN TEAMS WILL GET A PAIR OF NIKE AIR MAX 2017 ID.

            </p>
         <div class="warn" style="margin-top: 2rem;">如何邀请 <big>|</big> HOW TO INVITE?</div>
            <p >
               点击“我的跑团”，邀请小伙伴，根据页面提示<br>发送邀请。
            </p>
            <p style="font-size: 0.9rem;">
              CLICK “MY TEAM” AND FOLLOW THE GUIDANCE TO INVITE YOUR FRIENDS TO JOIN THE TEAM.
            </p>
            <div class="warn" style="margin-top: 2rem">注意事项 <big>|</big> TIPS</div>
            <p>已被邀请的用户无法再次加入你的团队。<br>
                受邀团员需关注“里享”公众号，下载NRC APP，绑定NIKE+账号。
               </p>
                <p style="font-size: 0.9rem;">
             RUNNERS WHO HAVE ALREADY BEEN INVITED CAN’T JOIN OTHER TEAMS.<br>
            INVITED RUNNERS NEED TO FOLLOW THE WECHAT ACCOUNT OF “LIXIANG”, DOWNLOAD NRC APP AND BIND NIKE+ ACCOUNT.
            </p>
            ';break;
                case 4:echo ' <div class="warn">活动规则 <big>|</big> RULES</div>
             <p>
                自12月10日9:00至12月14日21:00 ，可持续邀请小伙伴，加入自己的跑团，并用NRC记录跑步里程。<br>
                当日里程数将以1.5倍膨胀，并计入总里程。总里程最高的前十名跑团团长将获得NIKE VAPORMAX FLYKNIT SE ID一双。

            </p>
            <p style="font-size: 0.9rem;">
                FROM 9:00 DEC. 10 TO 21:00 DEC. 14, CAPTAINS CAN CONTINUE TO INVITE FRIENDS TO JOIN THE TEAM AND RECORD THE MILEAGE WITH NRC. <br>
THE MILEAGE OF EACH TEAM WILL BE EXPANDED TO 1.5 TIMES, THEN BE COUNTED INTO THE TOTAL MILEAGE. TEAMS WILL BE RANKED BASED ON THE TOTAL MILEAGE. CAPTAINS OF TOP TEN TEAMS WILL GET A PAIR OF NIKE VAPORMAX FLYKNIT SE ID.

            </p>
         <div class="warn" style="margin-top: 2rem;">如何邀请 <big>|</big> HOW TO INVITE?</div>
            <p >
               点击“我的跑团”，邀请小伙伴，根据页面提示<br>发送邀请。
            </p>
            <p style="font-size: 0.9rem;">
              CLICK “MY TEAM” AND FOLLOW THE GUIDANCE TO INVITE YOUR FRIENDS TO JOIN THE TEAM.
            </p>
            <div class="warn" style="margin-top: 2rem">注意事项 <big>|</big> TIPS</div>
            <p>已被邀请的用户无法再次加入你的团队。<br>
                受邀团员需关注“里享”公众号，下载NRC APP，绑定NIKE+账号。
               </p>
                <p style="font-size: 0.9rem;">
             RUNNERS WHO HAVE ALREADY BEEN INVITED CAN’T JOIN OTHER TEAMS.<br>
            INVITED RUNNERS NEED TO FOLLOW THE WECHAT ACCOUNT OF “LIXIANG”, DOWNLOAD NRC APP AND BIND NIKE+ ACCOUNT.
            </p>
            ';break;
            }?>
    </div>
    <div style="margin: 1rem 2.5rem 0">
        <a class="btn-style"  href="rtw-my-team.php">我的跑团<br><span>MY TEAM</span></a>
        <a class="btn-style" href="rtw-team-rank.php?type=<?php echo $tab_type?>" style="float: right">排行榜<br><span>RANKING</span></a>
    </div>
</div>
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