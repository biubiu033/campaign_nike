<?php
require_once "./header.php";
$conn = connect_to_db();

$apiStartTime = getMicrotime();
$apiUrl = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
$loginIP = getClientIP();
$type = 'rtw_invite';

$headerLocation = ''; //跳转地址
$sql = "select teamId,srcopenid from dtc_join_user where openid = '$openid';";
$result = mysql_query($sql, $conn);
$isJoin = 1;
if (is_resource($result) && mysql_num_rows($result) != 0)
{
    $joinUserRow = mysql_fetch_assoc($result);

    //进到首页，可简单判断，如果用户teamId不为-1且已确认开启任务且已绑定Nike+则留在本页面；其他情况统一交给join-choose处理
    if ($joinUserRow['teamId'] != -1 && $joinUserRow['teamId'] != 240)
    {    //之前有参加过
        $isJoin = -1;//是NIKE员工，以前也注册过了，弹框进入挑战2
        $srcOpenid = '';
        if(!empty($_GET['srcopenid'])){
            $srcOpenid = $_GET['srcopenid'];
            $query = "SELECT id FROM dtc_join_user WHERE openid = '$srcOpenid'";
            $result = mysql_query($query,$conn);
            if($srcOpenid == $openid){
                $isJoin = -2; //自己进自己的跑团，应该去选任务
            }
            if(mysql_num_rows($result) == 1){
                $_SESSION['src_openid'] =  $srcOpenid;
                $query = "SELECT fullname FROM dtc_join_user WHERE openid = '$srcOpenid'";
                $result = mysql_query($query,$conn);
                $srcMember = mysql_fetch_assoc($result);
            }
        }
    }
    else
    {   //之前没参加过
        {//如果绑定了，则把srcopenid给弄进rtw_join
            $srcOpenid = '';
            if(!empty($_GET['srcopenid'])){
                $srcOpenid = $_GET['srcopenid'];
                $query = "SELECT id FROM dtc_join_user WHERE openid = '$srcOpenid'";
                $result = mysql_query($query,$conn);
                if(mysql_num_rows($result) == 1){
                    $_SESSION['src_openid'] =  $srcOpenid;
                    $query = "SELECT src_openid FROM dtc_rtw_join_user WHERE openid = '$openid'";
                    $result = mysql_query($query,$conn);
                    if(mysql_num_rows($result) == 0 ){
                        $isJoin = 0;
                    }elseif (mysql_fetch_assoc($result)['src_openid']==$srcOpenid){
                        $isJoin = 2;
                    }
                    $query = "SELECT fullname FROM dtc_join_user WHERE openid = '$srcOpenid'";
                    $result = mysql_query($query,$conn);
                    $srcMember = mysql_fetch_assoc($result);

                }else{
//                    $headerLocation = 'main.php';
                }

            }else{
//                $headerLocation = 'main.php';
            }
        }
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
        <div class="box" style="padding: 1.2rem 0;font-size: 1.2rem ">
            目前里程数达<?php echo $sum_total; ?>公里<br>
            <p style="font-family: 'fornike365';"><?php echo $sum_total; ?> KM ACHIEVED</p>
            <!--            累计捐献装备<//?php /*echo $donate_count; */?>套<br>
                        <p><//?php /*echo $donate_count; */?> SETS DONATED</p>-->
        </div>
        <div class="box">
            <?php echo emoji_unified_to_html(getSubstr($nickname, 12, '...')); ?>为公益挑战赛助力<?php echo $your_course ?>公里<br>
            <p style="font-family: 'fornike365';"><?php echo emoji_unified_to_html(getSubstr($nickname, 12, '...')); ?> HAVE RUN <?php echo $your_course ?> KM</p>
        </div>
        <!-- <a style="" class="share-btn"></a>-->
        <div style="text-align: center;margin: 0 1.5rem;">
            <a class="share-btn" style="width: 15.8rem;border: none;border-radius: 10px;">加入<?php echo $srcMember['fullname'];?>跑团<br><span>JOIN TEAM <?php echo $srcMember['fullname'];?></span></a>
        </div>
    </div>
</div>
<aside class="joinbefor pop-alert" style="display:none">
    <a class="close" href="rtw-theme-run-be.php"></a>
    <p>你已加入其他跑团<br>
        <span>YOU HAVE JOINED OTHER TEAM</span>
    </p>
</aside>
<aside class="join-success pop-alert" style="display:none">
    <a class="close" href='rtw-theme-run-be.php?srcopenid=<?php echo $srcOpenid;?>'></a>
    <p>加入<?php echo $srcMember['fullname'];?>跑团成功<br>
        <span>YOU HAVE JOINED OTHER <?php echo $srcMember['fullname'];?> TEAM</span>
    </p>
</aside>
<aside class="begin-team pop-alert" style="display:none">
    <a class="close" href="rtw-task-two.php"></a>
    <p>你可发起自己的跑团<br>
        <span>YOU COULD BEGIN TEAM</span>
    </p>
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
        <?php if($isJoin==1){?>
        $('.joinbefor').bPopup({
            modalClose:false,
            positionStyle:'fixed'
        });
        <?php }elseif($isJoin==0){?>
        $('.join-success').bPopup({
            modalClose:false,
            positionStyle:'fixed'
        });
        <?php }elseif($isJoin==2){?>
        window.location.href = 'rtw-theme-run-be.php?srcopenid=<?php echo $srcOpenid;?>';
        <?php }elseif($isJoin==2){?>
        window.location.href = 'rtw-task-two.php';
        <?php }else{?>
        $('.begin-team').bPopup({
            modalClose:false,
            positionStyle:'fixed'
        });
        <?php }?>

    })
</script>
<?php include "share_dtc_rtw.php"; ?>
</html>
