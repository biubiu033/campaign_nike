<?php
require_once "./header.php";
$conn = connect_to_db();

$apiStartTime = getMicrotime();
$apiUrl = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
$loginIP = getClientIP();
$type = 'task-popup-enter';

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
        }else{
            $query = "SELECT * FROM dtc_task_popup WHERE openid='$openid' LIMIT 1";
            $result = mysql_query($query,$conn);
            if(is_resource($result) && mysql_num_rows($result) != 0){
                $openTask = 1;
            }else{
                $openTask = 0;
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

$t_id = 3; //代表1的隐藏任务，马拉松跑

//总的有多少
$sql = "select count(id) as count from dtc_task where task_id = $t_id  AND delete_flag = '0';";
$result = mysql_query($sql, $conn);
$row = mysql_fetch_assoc($result);
$sumLiked = $row['count'] ;
//
$sql = "select count(id) as count from dtc_task where like_openid = '$openid' and task_id = $t_id AND delete_flag = '0';";
$result = mysql_query($sql, $conn);
$row1 = mysql_fetch_assoc($result);
$liked = false;
if ($row1['count'] > 0) {
    $liked = true;
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
 <title>里享</title>
 <style>
  .start{
   display: block!important;
   width: 14rem!important;
   margin-top: 1rem!important;
  }
  .theme-run[name='task-one'] .explain {
   text-align: center;
   margin-top: 0.5rem;
  }
  .img-area{
   margin: -3.0rem auto 0!important;
  }
  .foot{
   margin-top: 1rem;
  }
  .foot-a{
   color:#c54c0f;
  }
 </style>
</head>
<body>
<div class="wrap theme-run" name="task-one">
 <div class="content">
  <span class="corner corner-3"> <!--左上角角标--></span>
  <a class="heart-area" href="main.php">
      <img src="./img/home-icon.png" width="100%">
  </a>
  <div class="img-area" ><img src="img/task-popup-img.png" width="80%"></div>
  <div class="explain">
   <h1>任务说明</h1>
   <span>WHAT’S THE MISSION?</span>
   <div style="background: #fe4819;height: 1px;width: 6.5rem; margin: 0 auto 1.5rem;"></div>
   <div style="height: 15.5rem;overflow-y: scroll;margin: 0 1rem 0 0;text-align: left">
    <p>
        1.活动期间，每晚8点开启次日迷你任务。<br>
        2.运动员根据要求完成任务，同时用NRC记录自己的跑步里程，并按照任务要求，将NRC截图和照片上传至活动页面。<br>
        3.根据图片上传时间，符合任务要求的前20名运动员将成为获胜者，获得相应奖励。<br>

    </p>
    <p>
        DESCRIPTION:  AT 8 P.M., THE MINI TASK FOR THE NEXT DAY WILL BE INITIATED. PARTICIPANTS SHALL FINISH THE TASK AND UPLOAD CORRESPONDING NRC SCREENSHOTSAND PHOTO TO TASK PAGE BEFORE DEADLINE.<br>
        WINNER: FINISH THE TASK AND UPLOAD  VALID SCREENSHOTS ARE THE WINNERS.
    </p>
   </div>


  </div>
  <?php
  if($openTask == '1'){
   ?>
    <a class="start" href="pop-choosedate.php">进入任务<br><span>ENTER</span></a>
  <?php }else{?>
    <a class="start"  href="pop-choosedate.php">开启任务<br><span>ENTER</span></a>
  <?php }?>
  <div class="foot"><a class="foot-a" href="http://r.lovemojito.com/short.php?a=4uphhl">常见问题<br><span class="foot-span">FAQ</span></a><span
       style="font-size: 1.8rem;color: #c54c0f;margin: 0 0.2rem">/</span><a class="foot-a" href="http://r.lovemojito.com/short.php?a=4uphhl">技术支持<br><span class="foot-span">TECH SUPPORT</span></a></div>
 </div>
</div>
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