<?php
require_once "./header.php";
$conn = connect_to_db();

$apiStartTime = getMicrotime();
$apiUrl = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
$loginIP = getClientIP();
$type = 'dtc_notBindNike';

if (isset($_SESSION['userInfo']) && $_SESSION['userInfo']['isBindNike'] == 1) {
    //已经绑定,跳转
    header("Location:main.php");
    exit();
}
//var_dump($_SESSION['userInfo']);

?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
        <title>里享</title>
        <link rel="stylesheet" href="css/reset.css">
        <!--引入自己的css-->
        <link rel="stylesheet" href="css/confirm.css">
        <!--引入自己的Js-->
        <script src="js/responsive.js"></script>
    </head>
    <body>
    <div class="bind">
        <img src="img/bind-title.png" width="100%" class="bind-title" >
        <p>
            谁说普通人不能成为超级跑手？<br>
            只要有一双脚，就能跑出你的名号！<br>
            各种跑步新玩法！每期一次奇趣挑战！<br>
            敢来试试吗？<br>
            <span>
                WHO SAID ORDINARY PEOPLE CAN’T BE SUPER RUNNERS?<br>
                RUN! DTC RUN! RUN FOR YOUR OWN GLORY!<br>
                DIFFERENT RUNNING THEMES AND FUN CHALLENGES!<br>
                GIVE IT A TRY!<br>
            </span>
        </p>
        <!--        <a class="bind-btn" href="http://www.makeyourruncount.com/campaign_nike/running/?c=user&a=bind&historyUrl=-->
        <?php //echo urlencode("http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?><!--"></a>-->
        <a class="bind-btn"
           href="http://www.makeyourruncount.com/campaign_nike/running/?c=user&a=bind&historyUrl=dtcbind"></a>
    </div>
    </body>
    <?php include 'share_dtc.php'; ?>
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
    'result' => mysql_real_escape_string('bind'),
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