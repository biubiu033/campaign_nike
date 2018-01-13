<?php
require_once "./header.php";
$conn = connect_to_db();

$apiStartTime = getMicrotime();
$apiUrl = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
$loginIP = getClientIP();
$type = 'trophy-room';

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
$type = 'dtc_trophy_room';

//总的有多少
$sql = "select *  from dtc_trophy where openid = '{$openid}'";
$result = mysql_query($sql, $conn);
$row = mysql_fetch_assoc($result);
$is_get_1km = $row['1km'];
$is_get_10km = $row['10km'];
$is_get_share1 =$row['share1'];
$is_get_3d5km =$row['3d5km'];
$is_get_100km =$row['100km'];
$is_get_sharemany =$row['sharemany'];
$is_get_200km =$row['200km'];
$is_get_team = $row['team_win'];


//$is_get_king = $row['king'];
$is_get_king = $is_get_1km && $is_get_10km && $is_get_share1
&& $is_get_3d5km && $is_get_100km && $is_get_sharemany && $is_get_200km &&$is_get_team? 1 : 0;
//9个奖杯的获得标志，1表示已获得，0表示未获得
?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
        <!--    引入自己的css-->
        <link rel="stylesheet" href="css/reset.css">
        <link rel="stylesheet" href="css/trophy-room.css?=456"/>
        <link rel="stylesheet" href="css/swiper-3.4.2.min.css">
        <!--引入自己的Js-->
        <script src="js/responsive.js"></script>
        <!--引用Jquery-->
        <script src="js/jquery-2.1.3.min.js"></script>
        <title>里享</title>
    </head>
    <body>
    <div class="trophy-room">
        <div class="trophy-item">
            <div class="cup">
                <div class="cup-item"><img src="img/trophy-1km<?php if ($is_get_1km != 1) {
                        echo "-no";
                    } ?>.png" width="51%"></div>
                <div class="cup-item" style="margin: 0 1rem;"><img src="img/trophy-10km<?php if ($is_get_10km != 1) {
                        echo "-no";
                    } ?>.png" width="51%"></div>
                <div class="cup-item"><img src="img/trophy-share1<?php if ($is_get_share1 != 1) {
                        echo "-no";
                    } ?>.png" width="51%"></div>
            </div>
            <div class="cup-name-1">
            </div>
        </div>
        <div class="trophy-item">
            <div class="cup">
                <div class="cup-item"><img src="img/trophy-3d5k<?php if ($is_get_3d5km != 1) {
                        echo "-no";
                    } ?>.png" width="51%"></div>
                <div class="cup-item" style="margin: 0 1rem;"><img src="img/trophy-100km<?php if ($is_get_100km != 1) {
                        echo "-no";
                    } ?>.png" width="51%"></div>
                <div class="cup-item"><img src="img/trophy-sharemany<?php if ($is_get_sharemany != 1) {
                        echo "-no";
                    } ?>.png" width="51%"></div>
            </div>
            <div class="cup-name-2">
            </div>
        </div>
        <div class="trophy-item">
            <div class="cup">
                <div class="cup-item"><img src="img/trophy-200km<?php if ($is_get_200km != 1) {
                        echo "-no";
                    } ?>.png" width="48%"></div>
                <div class="cup-item" style="margin-left: 1.2rem;"><img
                        src="img/trophy-team<?php if ($is_get_team != 1) {
                            echo "-no";
                        } ?>.png" width="48%"></div>
            </div>
            <div class="cup-name-3">
            </div>
        </div>
        <div class="trophy-king">
            <img src="img/trophy-king<?php if ($is_get_king != 1) {
                echo "-no";
            } ?>.png" width="28%">
            <img src="img/trophy-king-name.png" width="24%">
        </div>
       <!-- <a class="share">分享
            <br/>
            <span>SHARE</span>
        </a>-->
        <div style="text-align: center;margin: 0 2.5rem;"><a class="share" href="main.php" style="float: left">返回首页<br><span>RETURN TO HOME PAGE</span></a>
            <a class="share" style="float: right">分享<br><span>SHARE</span></a>
        </div>
    </div>
    <div class="popup-share close">
        <img class="close" src="img/invite-share.png" width="100%">
    </div>
    <script src="js/jquery.bpopup.js"></script>
    <script>
        $(".share").on("click", function () {
            $(".popup-share").bPopup({
                positionStyle: "fixed",
                closeClass: "close"
            });
        });
    </script>
    </body>
    <?php include "share_dtc.php"; ?>
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