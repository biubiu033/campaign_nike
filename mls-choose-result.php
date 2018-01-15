<?php
require_once "./header.php";
$conn = connect_to_db();

$apiStartTime = getMicrotime();
$apiUrl = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
$loginIP = getClientIP();
$type = 'upload';

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
        }else{
            $query = "SELECT * FROM dtc_taskone_marathon WHERE openid='$openid' LIMIT 1";
            $result = mysql_query($query,$conn);
            if(is_resource($result) && mysql_num_rows($result) != 0){
                $joinStatus = 1;
                $joinRow = mysql_fetch_assoc($result);
            }elseif(isset($_GET['type']) && $_GET['type'] != ''){
                $joinStatus = 0;
            }else{
                $headerLocation = 'mls-choose.php';
            }
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

$missionName = '';
$missionNameEn = '';
$missionNameEn2 = '';
if($joinStatus == 0){
    switch ($_GET['type']){
        case 5:
            $missionName='5K';
            $missionNameEn="&nbsp";
            $missionNameEn2 = "5K";
            $missionNameEn3 = "10"; //名额
            break;
        case 10:
            $missionName='10K';
            $missionNameEn="&nbsp";
            $missionNameEn2 = "10K";
            $missionNameEn3 = "20"; //名额
            break;
        case 21:
            $missionName='半马';
            $missionNameEn='HALF MARATHON';
            $missionNameEn2 = 'HALF MARATHON';
            break;
        case 42:
            $missionName='全马';
            $missionNameEn='FULL MARATHON';
            $missionNameEn2 = 'FULL MARATHON';
            $missionNameEn3 = "20"; //名额
            break;
        default:
            $missionName='错误';
            $missionNameEn='ERROR';
            break;
    }
}else{
    switch ($joinRow['marathon_project']){
        case 5:
            $missionName='5K';
            $missionNameEn="&nbsp";
            $missionNameEn2 = "5KM";
            $missionNameEn3 = "10"; //名额
            break;
        case 10:
            $missionName='10K';
            $missionNameEn="&nbsp";
            $missionNameEn2 = "10KM";
            $missionNameEn3 = "20"; //名额

            break;
        case 21:
            $missionName='半马';
            $missionNameEn='HALF MARATHON';
            $missionNameEn2 = 'HALF MARATHON';
            break;
        case 42:
            $missionName='全马';
            $missionNameEn='FULL MARATHON';
            $missionNameEn2 = 'FULL MARATHON';
            $missionNameEn3 = "20"; //名额
            break;
        default:
            $missionName='错误';
            $missionNameEn='ERROR';
            break;
    }
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
    <link rel="stylesheet" href="//at.alicdn.com/t/font_247495_b94qwp9s1ulq5mi.css"/>
    <!--引入自己的Js-->
    <script src="js/responsive.js"></script>
    <!--引用Jquery-->
    <script src="js/jquery-2.1.3.min.js"></script>
    <title>里享</title>
    <style>
        .btn-disable{
            box-shadow: box-shadow: 0px 3px 0 #5b5b5b;
            border: 2px solid #5b5b5b;
            color: #5b5b5b;
        }
    </style>
</head>
<body style="background: #fe8c03">
<div class="wrap mls-choose">
    <div class="title">
        <?php if($joinStatus==0){?>你选择的项目是<br><span>YOUR SELECTION IS</span><?php } //$join_status = 0?>
        <?php if($joinStatus==1){?>你已成功提交<br><span>YOU HAVE SUCCESSFULLY SUBMITTED</span><?php }?>
    </div>
    <div  class="item-box " >
        <p class="item-name">
            <?php echo $missionName;?><br>
            <span><?php echo $missionNameEn;?></span>
        </p>
        <?php if($joinStatus==0){?><p class="choose-warn mt1" style="line-height: 1.2rem;">
            <?php echo $missionName;?>名额为  <?php echo $missionNameEn3;?>个<br><span style="font-size: 1rem"> <?php echo $missionNameEn3;?> QUOTAS PROVIDED FOR <?php echo $missionNameEn2;?></span>
                </p>
            </div>
                <label class="check mt3"><div class="cell"><input type="checkbox"  ><i class="iconfont icon-gou"></i></div>
            <div class="cell">我所在的跑步队伍已参与1号挑战，并已提交创意路线图。
                <br><span  class="delete-span">MAKE SURE YOUR TEAM HAS PARTICIPATED IN CHALLENGE 1 AND SUBMITTED THE ROUTE.</span>
            </div>
                </label>
        <a id="submit-btn" class="choose-item mt3 btn-disable">提交<br><span>SUBMIT</span></a>
    <?php }?>
    <?php if($joinStatus==1){?>
            <p class="choose-warn mt1" style="line-height: 1.2rem;">
                项目名额申请<br>
                <span style="font-size: 1rem">APPLICATION</span>
            </p>
            </div>
            <div class="check mt3">
                活动结束后将根据<br>
                各项目跑步里程排名选出获胜个人
                <br><span  class="delete-span">WINNERS WILL BE SELECTED BY EACH EVENT RANKING.
                </span>
            </div>
<div style="text-align: center;margin: 0 3rem;"><a class="choose-item mt3" href="main.php" style="display:inline-block;width: 9rem;height: 3.0rem;font-size:1.2rem;line-height: 0.8rem;float: left">返回首页<br><span>RETURN TO HOME PAGE</span></a>
    <a class="choose-item mt3" href="mls-rank.php" style="display:inline-block;width: 9rem;height: 3.0rem;font-size:1.2rem;line-height: 0.8rem;float: right">排行榜<br><span>RANKING</span></a>
</div>
    <?php }?>
</div>
<script src="js/jquery-2.1.3.min.js"></script>
<script>
    var list=$('.check');
    list.find('input').on('click',function () {
        if(this.checked){
            $('.iconfont').css('color','#fa6b0e');
            $('#submit-btn').removeClass('btn-disable');
        }
        else {
            $('.iconfont').css('color','#ffffff');
            $('#submit-btn').addClass('btn-disable');

        }
    });
    $('#submit-btn').on('click',function () {
        if(!$(this).hasClass('btn-disable')){
            $.ajax({
                url:'./apiTaskOneMarathon.php',
                method:'GET',
                dataType:'JSON',
                data:{
                    'type':<?php echo $_GET['type'];?>
                },
                success:function (result) {
                    alert(result.msg);
                    //location.href = 'mls-rank.php';
                    window.location.reload();
                },
                error:function () {
                    alert('网络错误');
                }
            })
        }else {
            alert('请确认您的团队已经参加1号挑战');
        }
    })
</script>
<?php include "share_dtc.php";?>
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