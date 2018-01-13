<?php
require_once "./header.php";
$conn = connect_to_db();

$apiStartTime = getMicrotime();
$apiUrl = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
$loginIP = getClientIP();
$type = 'task-popup-prize';

$headerLocation = ''; //跳转地址
$sql = "select * from dtc_join_user where openid = '$openid';";
$result = mysql_query($sql, $conn);

$isUpload=0;
$imgOne = '';
$imgTwo = '';
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
            if(isset($_GET['type'])){
                $date=$_GET['type'];
            }else{
                $date=1;
            } //任务默认为1
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
/****页面数据***/
$ownOpenid = $openid;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
    <!--    引入自己的css-->
    <link rel="stylesheet" href="css/reset.css">
    <script src="js/responsive.js"></script>
    <title>里享</title>
<style>
    .prize{
        width: 26.6666666rem;
        margin: 0 auto;
        background:#fc8d01 url("./img/prize-bg.png")no-repeat;
        background-size: cover;
        height: 43.3rem;
        box-sizing: border-box;
        padding: 1rem;
    }
    .back{
        display: block;
        width: 3.5rem;
        height: 3.5rem;
        float: left;
    }
    .home{
        display: block;
        width: 3.5rem;
        height: 3.5rem;
        float: right;
    }
    .btn{
        margin-top: 12.5rem;
        text-align: center;
    }
    .btn a{
        display: inline-block;
        width: 11rem;
        height: 13.7rem;
    }
    .item{
        display: flex;
        justify-content: space-between;
        margin-top: 1rem;
    }
    .mt{
        margin-top: 4.5rem;
    }
</style>
<body style="background-color: #f05f06;">
<div class="prize">
    <a class="back" href="./pop-task-rank.php"><img src="./img/back.png" width="100%"> </a>
    <a class="home" href="./main.php"><img src="./img/home-icon1.png"  width="100%">  </a>
   <div class="btn">
    <div class="item">
        <a href="http://www.makeyourruncount.com/campaign_nike/running/dtc/pop-task-rank.php?type=1"></a>
        <a href="http://www.makeyourruncount.com/campaign_nike/running/dtc/pop-task-rank.php?type=2"></a>
    </div>
    <div class="item">
        <a href="http://www.makeyourruncount.com/campaign_nike/running/dtc/pop-task-rank.php?type=3"></a>
        <a href="http://www.makeyourruncount.com/campaign_nike/running/dtc/pop-task-rank.php?type=4"></a>
    </div>
   </div>
</div>
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
    'result' => mysql_real_escape_string("project total: $sum_total ownTotal: $your_course"),
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