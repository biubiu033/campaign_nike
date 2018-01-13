<?php
/**
 * Created by PhpStorm.
 * User: 晶晶
 * Date: 2017/8/3
 * Time: 19:31
 */
require_once "./header.php";
$conn = connect_to_db();

$apiStartTime = getMicrotime();
$apiUrl = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
$loginIP = getClientIP();
$type = 'info';

$user = isset($_GET['openid'])&&!empty($_GET['openid'])?$_GET['openid']:$openid;
$team_sql = "select yiqipao_member.*,dtc_join_user.fullname,dtc_join_user.phone,dtc_join_user.teamId as team_id from yiqipao_member,dtc_join_user WHERE yiqipao_member.openid=dtc_join_user.openid AND yiqipao_member.openid='$user'";
$team_ret = mysql_query($team_sql, $conn);
$userInfo = mysql_fetch_assoc($team_ret);
$teamId = $userInfo['team_id'];

$team_sql = "select teamName from dtc_team WHERE teamId='$teamId' limit 1";
$team_ret = mysql_query($team_sql, $conn);
$row = mysql_fetch_assoc($team_ret);
$userInfo['teamName'] = $row['teamName'];

$captain = array();
$team_sql = "select `name` from dtc_team_captain WHERE teamId='$teamId'";
$team_ret = mysql_query($team_sql, $conn);
while($row = mysql_fetch_assoc($team_ret)){
    $captain[] = $row['name'];
}
$userInfo['captain'] = implode("/",$captain);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
    <title>里享</title>
    <link rel="stylesheet" href="css/reset.css">
    <link rel="stylesheet" href="css/confirm.css?a=13">
    <!--引入自己的css-->
    <!--引入自己的Js-->
    <script src="js/responsive.js"></script>
    <style>

        h2{
            color: #ffffff;
            margin: 1rem;
        }
        .check-pop{
            display: block;
            width: 22rem;
            /* height: 29rem; */
            background: #ffffff;
            border: 3px solid #f05f06;
            box-sizing: border-box;
            padding: 1.5rem;
        }
        .my_info{
            width: 20rem;
            margin: 0 auto;
        }
        .head-img{
            display: block;
            margin: 0 auto 1rem;
            width: 5rem;
            border-radius: 50%;
            border: 2px solid #ffffff;
        }
        .item__1{
            display: flex;
            flex-direction: row;
            justify-content: space-between;
        }
        .__span{
            font-family: 'fornike365';
            letter-spacing: 0;
            font-size: 1.2rem!important;
            margin-left: 0.5rem;
            color: #ffffff;
        }
        .__p{
            font-family: 'fornike365';
            color: #ffffff;
            font-size: 1.2rem;

            margin: 0 0 0.3rem 0rem;
        }
        .list{
            border-bottom: 1px solid #ffffff;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>

<div  class="wrap confirm" name="confirm">
    <div class="title"></div>
    <?php if($openid == $user){ ?>
        <h2>我的信息:</h2>
    <?php }else{ ?>
        <h2>朋友的信息:</h2>
    <?php } ?>

    <div class="my_info">
        <img class="head-img" src="<?php echo $userInfo['headimg']; ?>">
        <div class="item__1"><p class="__p">openid</p><span  class="__span"><?php echo $user; ?></span></div>
        <div class="item__1"><p class="__p">姓名</p><span  class="__span"><?php echo $userInfo['fullname']; ?></span></div>
        <div class="item__1"> <p class="__p">电话号码</p><span  class="__span"><?php echo $userInfo['phone']; ?></span></div>
        <div class="item__1"><p class="__p">所属团队</p><span  class="__span"><?php echo $userInfo['teamName']; ?></span></div>
        <div class="item__1"><p class="__p">队长名称</p><span  class="__span"><?php echo $userInfo['captain']; ?></span></div>
        <div class="item__1"> <p class="__p">是否绑定NRC</p><span  class="__span"><?php echo $userInfo['isBindNike']==1?'是':'否'; ?></span></div>
        <div class="item__1"><p class="__p">绑定NRC账号</p><span  class="__span"><?php echo $userInfo['bindNikeUsername'] ?></span></div>
        <div class="item__1"><p class="__p">绑定NRC时间</p><span  class="__span"><?php echo date("Y-m-d H:i:s",$userInfo['refresh_token_created']); ?></span></div>
        <div class="item__1"><p class="__p">加入里享时间</p><span  class="__span"><?php echo date("Y-m-d H:i:s",$userInfo['regTime']); ?></span></div>

    </div>
</div>

<script src="js/jquery-2.1.3.min.js"></script>
<script src="js/xb_scroll.js"></script>
<script src="js/jquery.bpopup.js"></script>
<script>

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
    'result' => mysql_real_escape_string(json_encode_cn($userInfo)),
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
