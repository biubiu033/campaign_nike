<?php
require_once "./header.php";
$conn = connect_to_db();

$apiStartTime = getMicrotime();
$apiUrl = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
$loginIP = getClientIP();
$type = 'rank-choose';

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
    <!--    引入自己的css-->
    <link rel="stylesheet" href="css/reset.css">
    <link rel="stylesheet" href="css/theme-run-heros.css"/>
    <!--引入自己的Js-->
    <script src="js/responsive.js"></script>
    <!--引用Jquery-->
    <script src="js/jquery-2.1.3.min.js"></script>
    <title>里享</title>
    <style>
        .home{
            display: block;
            width: 2.0rem;
            height: 2.0rem;
            float: left;
            margin-top: -2.0rem;
            margin-bottom: 1rem;
            margin-left: 2rem;
        }
    </style>
</head>
<body>
    <div class="wrap theme-run-heros" style="box-sizing: border-box;padding-top: 5.0rem">
        <a class="home" href="main.php"><img src="./img/home.png" width="100%"></a>
        <a class="chose" href="theme-run-heros.php"><img src="img/rank-chose-btn2.png" width="100%"></a>
        <a class="chose" href="rank.php"><img src="img/rank-chose-btn1.png" width="100%"></a>
        <a class="chose" href="trophy-room.php" style="margin-bottom: 0"><img src="img/rank-chose-btn3.png" width="100%"></a>
    </div>
</body>
<?php include "share_dtc.php";?>
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