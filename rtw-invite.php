<?php
require_once "./header.php";
$conn = connect_to_db();

$apiStartTime = getMicrotime();
$apiUrl = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
$loginIP = getClientIP();
$type = 'rtw_invite';

$headerLocation = ''; //跳转地址
//取当前用户个人信息
$ownInfo = array();
$ownInfo['openid'] = $openid;  //自己的openid
$sql = "select * from dtc_join_user where openid = '$openid';";
$result = mysql_query($sql, $conn);
$joinUserRow = mysql_fetch_assoc($result);
$ownInfo['teamId'] = $joinUserRow['teamId']; //看个人teamId
$ownInfo['srcopenid'] = $joinUserRow['srcopenid'];  //邀请进到活动中来的邀请者openid
//如果自己是内跑成员，则跑团openid一定是自己
if($ownInfo['teamId'] != -1)
{
    $ownInfo['ptOpenid'] = $openid;
    $ownInfo['ptName'] = $joinUserRow['fullname'];
    $ownInfo['isJoin'] = 1;
}
else
{
    //看用户是否加入跑团
    $query = "SELECT src_openid FROM dtc_rtw_join_user WHERE openid = '$openid'";
    $result = mysql_query($query,$conn);
    if(mysql_num_rows($result) != 0 )
    {
        $row = mysql_fetch_assoc($result);
        $ownInfo['ptOpenid'] = $row['src_openid'];
        $ownInfo['isJoin'] = 1; //为1代表已参加跑团
        $ptOpenid = $ownInfo['ptOpenid'];
        $sql1 = "select * from dtc_join_user where openid = '$ptOpenid';";
        $result1 = mysql_query($sql1, $conn);
        $row1 = mysql_fetch_assoc($result1);
        $ownInfo['ptName'] = $row1['fullname'];      
    }else
    {
        $ownInfo['ptOpenid'] = '';
        $ownInfo['ptName'] = '';
        $ownInfo['isJoin'] = 0;  //为0代表未参加跑团
    }
}

//取当前链接中携带的跑团信息
$ptOpenidUrl = '';
$ptNameUrl = '';  //链接里带的跑团名字
if(isset($_GET['ptOpenid']) && $_GET['ptOpenid'] != '')
{
    $ptOpenidUrl = $_GET['ptOpenid'];
    $query = "SELECT * FROM dtc_join_user WHERE openid = '$ptOpenidUrl'";
    $result = mysql_query($query,$conn);
    $row = mysql_fetch_assoc($result);
    $ptNameUrl = $row['fullname'];
}

$srcOpenid = '';
if(!empty($_GET['srcopenid']))
{
    $srcOpenid = $_GET['srcopenid'];
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

/****页面数据***/
$ownOpenid = $openid;
//$teamName = $_SESSION['userInfo']['teamName'];

//总里程从yiqipao_member_project里面找
$sql = "SELECT sum(`long`) as sum_total FROM yiqipao_member_project where pid={$dtcPid}";
$ret = mysql_query($sql, $conn);
$row = mysql_fetch_assoc($ret);
$sum_total = intval($row['sum_total']);
//进度条归一化
$target = 600000;
$rate = $sum_total / $target;
$rate = $rate > 1.0 ? 1.0 : $rate;//最大只到1
//赠送装备映射关系
$donate_count = ceil($sum_total * 0.08);

//你的贡献值
$sql1 = "SELECT sum(`long`) as total FROM yiqipao_member_project where openid='{$srcOpenid}' and pid={$dtcPid}";
$ret1 = mysql_query($sql1, $conn);
$row1 = mysql_fetch_assoc($ret1);
$your_course = intval($row1['total']);

$nickname = getWeixinNickname($conn,$srcOpenid);

//设置分享页面
if($ownInfo['isJoin'] == 0)
{
    $phpfile = isset($phpfile) ? $phpfile : "campaign_nike/running/dtc/rtw-invite.php?ptOpenid=".$ptOpenidUrl;    
}
else
{
    $phpfile = isset($phpfile) ? $phpfile : "campaign_nike/running/dtc/rtw-invite.php?ptOpenid=".$ownInfo['ptOpenid'];
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
    <!--    引入自己的css-->
    <link rel="stylesheet" href="css/reset.css">
    <link rel="stylesheet" href="css/public.css"/>
    <link rel="stylesheet" href="//at.alicdn.com/t/font_z9hrayhm8ia4i.css"/>
    <!--引入自己的Js-->
    <script src="js/responsive.js"></script>
    <!--引用Jquery-->
    <script src="js/jquery-2.1.4.js"></script>
    <script src="js/jquery-2.1.3.min.js"></script>
    <script src="js/jquery.bpopup.js"></script>
    <title>里享</title>
    <style>
        .pop-alert {
            padding: 1rem;
            margin:2rem auto 0;
            background: rgba(255,255,255,1);
            box-shadow: 5px 5px 0 rgba(250, 106, 6, 0.9);
            box-sizing: border-box;
            width: 22rem;
            text-align: center;
        }
        .pop-alert .close{
            display: block;
            background: url("./img/close-btn.png")no-repeat;
            background-size: cover;
            height: 1.8rem;
            width: 1.8rem;
            margin-left: 18rem;
        }
        .pop-alert p{
            margin: 0.4rem auto 1rem;
            /* font-family: "STHeiti SC"; */
            font-family: 'fornike365';
            display: block;
            font-size: 1.35rem;
            text-align: center;
            color: #f76a0e;
            font-weight: bold;
            line-height: 1.4rem;
        }
        .pop-alert p span{
            font-family: 'fornike365';
            font-size: 0.95rem;
            font-weight: 100;
        }
        .confirm-btn {
            padding-top: 0.3rem;
            display: block;
            background: rgba(255, 255, 255, 0.8);
            box-shadow: 1px 2px 0 #b83a0f;
            border: 2px solid #ea5012;
            width: 8rem;
            height: 2.2rem;
            line-height: 1rem;
            border-radius: 10px;
            text-align: center;
            color: #fc5219;
            font-size: 1.0rem;
            font-style: oblique;
            margin: 1.5rem auto 0;
        }
        .confirm-btn span{
            font-size: 0.6rem;
            font-family: 'fornike365';
            font-style: oblique;
            font-weight: 100;
        }
    </style>
</head>
<body>
<div class="wrap public">
    <div class="public-hide">
        <div class="title"></div>
        <div class="title1"></div>
        <div class="title2"></div>
        <div class="title3"></div>
        <a class="pack-btn pack-show"><u>展开全部</u><i class="iconfont icon-arrowDown"></i></a>
    </div>
    <div class="public-show">
        <div class="title"></div>
        <div class="title1"></div>
        <div class="title2"></div>
        <div class="content">
            <img src="img/gy-content.png" width="80%">
        </div>

        <div class="line"></div>
        <div class="title3"></div>
        <a class="pack-btn pack-hide"><u>收起</u><i class="iconfont icon-jiantou-copy"></i></a>
    </div>
    <div class="pack-up">
        <div class="progress">
            <div class="gift">
                <div class="item" style="font-family: 'fornike365';"><p >第一次捐献<br>1ST DONATION</p><img class="pop-img" id="pop1" src="img/gy-gift.png" /><p>100,000公里</p></div>
                <div class="item" style="margin-left: 9%;font-family: 'fornike365';"><p>第二次捐献<br>2ND DONATION</p><img class="pop-img" id="pop2" src="img/gy-gift.png" /><p>300,000公里</p></div>
                <div class="item" style="margin-left: 25%;font-family: 'fornike365';"><p >第三次捐献<br>3RD DONATION</p><img class="pop-img" id="pop3" src="img/gy-gift.png" /><p>600,000公里</p></div>
            </div>
            <div class="load"></div>
            <div class="load load2" style=" width: <?php echo $rate *100;?>%;"></div>
        </div>
        <div class="box" style="padding: 1.2rem 0;font-size: 1.2rem ; font-family: 'fornike365';">
            目前里程数达<?php echo $sum_total; ?>公里<br>
            <p style="font-family: 'fornike365';"><?php echo $sum_total; ?> KM ACHIEVED</p>
            <!--            累计捐献装备<//?php /*echo $donate_count; */?>套<br>
                        <p><//?php /*echo $donate_count; */?> SETS DONATED</p>-->
        </div>
        <div class="box" style=" font-family: 'fornike365';">
            <?php echo emoji_unified_to_html(getSubstr($nickname, 12, '...')); ?>为公益挑战赛助力<?php echo $your_course ?>公里<br>
            <p style="font-family: 'fornike365';"><?php echo emoji_unified_to_html(getSubstr($nickname, 12, '...')); ?> HAS RUN <?php echo $your_course ?> KM</p>
        </div>
        <!-- <a style="" class="share-btn"></a>-->
        <div style="text-align: center;margin: 0 1.5rem; font-family: 'fornike365';">
            <a class="share-btn" style="width: 15.8rem;border: none;border-radius: 10px;">加入<?php echo $ptNameUrl;?>跑团<br><span>JOIN TEAM OF <?php echo $ptNameUrl;?></span></a>
        </div>
    </div>
</div>
<aside class="joinbefor pop-alert" style="display:none">
   <!-- <a class="close" href="rtw-theme-run-be.php"></a>-->
    <p>你已加入其他跑团<br>
        <span>YOU HAVE JOINED OTHER TEAM</span>
    </p>
    <a class="confirm-btn"  href="rtw-theme-run-be.php" >确认<br><span>CONFIRM</span></a>
</aside>
<aside class="join-success pop-alert" style="display:none">
   <!-- <a class="close" href='rtw-theme-run-be.php?ptOpenid=<?php /*echo $ptOpenidUrl;*/?>'></a>-->
    <p>加入<?php echo $ptNameUrl;?>跑团成功<br>
        <span>YOU HAVE JOINED <?php echo $ptNameUrl;?> TEAM</span>
    </p>
    <a class="confirm-btn" href='rtw-theme-run-be.php?ptOpenid=<?php echo $ptOpenidUrl;?>'>确认<br><span>CONFIRM</span></a>

</aside>
<aside class="begin-team pop-alert" style="display:none">
 <!--   <a class="close" href="rtw-task-two.php"></a>-->
    <p>你可进入自己的跑团<br>
        <span>ENTER OWN TEAM</span>
    </p>
    <a class="confirm-btn" href="rtw-task-two.php">确认<br><span>CONFIRM</span></a>
</aside>
</body>
<script>
    $(".pack-show").on("click",function(){
        $(".public-show").show();
        $(".public-hide").hide();
    });
    $(".pack-hide").on("click",function(){
        $(".public-show").hide();
        $(".public-hide").show();

    });
    $('.share-btn').on('click',function () {
        <?php if($ownInfo['isJoin'] == 0){?>
        $('.join-success').bPopup({
            modalClose:false,
            positionStyle:'fixed'
        });        
        <?php }elseif($ownInfo['isJoin'] == 1){?>
        <?php if($ownInfo['ptOpenid'] != $ptOpenidUrl && $ownInfo['ptOpenid'] != $openid){?>
        $('.joinbefor').bPopup({
            modalClose:false,
            positionStyle:'fixed'
	});
        <?php }elseif($ownInfo['ptOpenid'] == $ptOpenidUrl && $ownInfo['ptOpenid'] != $openid){ ?>
        $('.join-success').bPopup({
            modalClose:false,
            positionStyle:'fixed'
        });
        <?php }else{ ?>
        $('.begin-team').bPopup({
            modalClose:false,
            positionStyle:'fixed'
        });        
        <?php }} ?>
    })
</script>
<?php include "share_dtc_rtw.php"; ?>
</html>
