<?php
require_once "./header.php";
$conn = connect_to_db();

$apiStartTime = getMicrotime();
$apiUrl = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
$loginIP = getClientIP();
$type = 'rtw-task-open';

$headerLocation = ''; //跳转地址
$sql = "select * from dtc_join_user where openid = '$openid';";
$result = mysql_query($sql, $conn);
$isJoin = 0;
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
            $query = "SELECT id FROM dtc_rtw_join_user WHERE openid ='$openid'";
            $result = mysql_query($query,$conn);
            if(mysql_num_rows($result) == 1){
                $isJoin = 1;
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
        <link rel="stylesheet" href="css/theme-run.css?a=<?php echo rand();?>"/>
        <!--引入自己的Js-->
        <script src="js/responsive.js"></script>
        <!--引用Jquery-->
        <script src="js/jquery-2.1.3.min.js"></script>
        <title>里享</title>
        <style>
            .foot{
                margin-top: 1.5rem;
            }
            .foot-a{
                color:#c54c0f;
            }
            .home-icon{
                display: inline-block;
                width: 2rem;
                height: 2rem;
                float: right;
                margin-right: 1rem;
                margin-top: 1rem;
            }
            .theme-run[name='task-one'] .content .start {
                display: block;
                width: 14rem;
                margin-top: 1.5rem;
            }
        </style>
    </head>
    <body>
    <div class="wrap theme-run" name="task-one">
        <div class="content" >
            <a class="home-icon" href="main.php"><img src="./img/home-icon-1-1.png" width="100%"></a>
            <span class="corner corner-4"> <!--左上角角标--></span>
            <div class="img-area" style="margin: -1rem auto;"><img src="img/shoe.png" width="70%"></div>
            <div class="explain" >
                <h1 style="font-weight: normal">任务说明</h1>
                <span>WHAT’S THE MISSION?</span>
                <div style="background: #fe4819;height: 1px;width: 6.5rem; margin: 0 auto 2.5rem;"></div>
                <p style="text-align: left">
                    1. 自11月25日至12月14日，将不定期上线4个主题任务。<br>
                    2. 每位跑手都可以组建自己的跑团，每项任务的前十名跑团团长都将获得跑鞋一双。<br>
                    3. 集齐4双跑鞋还将有机会竞逐神秘大奖。


                </p>
                <p style="text-align: left">
                    1. FROM NOV. 25 TO DEC. 14, 4 TASKS WILL BE RELEASED PERIODICALLY.<br>
                    2. EACH RUNNER CAN SET UP HIS/HER OWN TEAM. CAPTAINS OF TOP TEN TEAMS OF EACH TASK WILL GET A PAIR OF RUNNING SHOES.<br>
                    3. RUNNERS WHO WIN ALL FOUR PAIRS GET TO COMPETE FOR THE SURPRISE PRIZE.<br>

                </p>
            </div>
            <?php if($isJoin==1){?>
                <a class="start" href="rtw-task-choose.php">进入挑战<br><span>ENTER</span></a>
            <?php }else{?>
                <a class="start" href="rtw-task-choose.php">开启挑战<br><span>ENTER</span></a>
            <?php }?>
            <div class="foot"><a class="foot-a" href="http://r.lovemojito.com/short.php?a=4uphhl">常见问题<br><span class="foot-span">FAQ</span></a><span
                        style="font-size: 1.8rem;color: #c54c0f;margin: 0 0.2rem">/</span><a class="foot-a" href="http://r.lovemojito.com/short.php?a=4uphhl">技术支持<br><span class="foot-span">TECH SUPPORT</span></a></div>
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
