<?php
require_once "./header.php";
$conn = connect_to_db();

$apiStartTime = getMicrotime();
$apiUrl = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
$loginIP = getClientIP();
$type = 'theme-run';

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

$apiStartTime = getMicrotime();
$apiUrl = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
$loginIP = getClientIP();
$type = 'dtc_themerun';


//$sql1 = "SELECT open_task FROM dtc_join_user where openid = '{$openid}'";
//$rer1 = mysql_query($sql1,$conn);
//$row1 = mysql_fetch_assoc($rer1);
//////是否已经开启过任务
////if(isset($row1['open_task']) && 1 == $row1['open_task']){
////    header("Location:unUpload.php");
////    exit();
////}
?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
        <!--    引入自己的css-->
        <link rel="stylesheet" href="css/reset.css">
        <link rel="stylesheet" href="css/theme-run.css?a=123"/>
        <link rel="stylesheet" href="css/swiper-3.4.2.min.css">
        <!--引入自己的Js-->
        <script src="js/responsive.js"></script>
        <!--引用Jquery-->
        <script src="js/jquery-2.1.3.min.js"></script>
        <title>里享</title>
    </head>
    <body style="background-color: #f05f06;" >
    <div class="wrap theme-run" name="theme-run">
        <div class="swiper-container">
            <div class="swiper-wrapper">
                <div class="swiper-slide">
                    <div class="task-4">
                     <!--   <a class="learnmore" href="rtw-task-two.php"></a>-->
                    </div>
                </div>
                <div class="swiper-slide">
                    <div class="task-3">
                        <a class="learnmore" href="pop-task-enter.php"></a>
                    </div>
                </div>
                <div class="swiper-slide">
                    <div class="task-1">
                        <a class="learnmore" href="task-one.php"></a>
                    </div>
                </div>
                <div class="swiper-slide">
                    <div class="task-2">
                        <a class="learnmore" href="task-one-hide.php"></a>
                    </div>
                </div>
            </div>
            <!-- 分页器 -->
            <div class="swiper-pagination"></div>
        </div>
        <a class="back" href="main.php">返回首页<br><span>RETURN TO HOME PAGE</span></a>
        <div class="foot" ><a  class="foot-a" href="http://r.lovemojito.com/short.php?a=4uphhl">常见问题<br><span class="foot-span">FAQ</span></a><span
                    style="font-size: 1.8rem;color: #ffffff;margin: 0 0.3rem">/</span><a class="foot-a" href="http://r.lovemojito.com/short.php?a=4uphhl">技术支持<br><span class="foot-span">TECH SUPPORT</span></a></div>
        <!--<a class="foot">相关常见问题<br>
            <span></span>
        </a>-->
    </div>
    <script src="js/swiper-3.4.2.min.js"></script>
    <script src="js/jquery.bpopup.js"></script>
    <script>
        var mySwiper = new Swiper ('.swiper-container', {
            direction: 'horizontal',
            loop: false,
            // 分页器
            pagination: '.swiper-pagination'
        })
    </script>
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