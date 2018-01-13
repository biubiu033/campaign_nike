<?php
require_once "./header.php";
$conn = connect_to_db();

$apiStartTime = getMicrotime();
$apiUrl = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
$loginIP = getClientIP();
$type = 'dtc_public';

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
$sql1 = "SELECT sum(`long`) as total FROM yiqipao_member_project where openid='{$openid}' and pid={$dtcPid}";
$ret1 = mysql_query($sql1, $conn);
$row1 = mysql_fetch_assoc($ret1);
$your_course = intval($row1['total']);

$nickname = getWeixinNickname($conn,$openid);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
    <!--    引入自己的css-->
    <link rel="stylesheet" href="css/reset.css">
    <link rel="stylesheet" href="css/public.css?=123"/>
    <link rel="stylesheet" href="//at.alicdn.com/t/font_z9hrayhm8ia4i.css"/>
    <!--引入自己的Js-->
    <script src="js/responsive.js"></script>
    <!--引用Jquery-->
    <script src="js/jquery-2.1.4.js"></script>
    <script src="js/jquery-2.1.3.min.js"></script>
    <script src="js/jquery.bpopup.js"></script>
    <title>里享</title>
    <style>

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
            <p style="font-family: 'fornike365';">I’VE RUN <?php echo $your_course ?> KM</p>
        </div>
       <!-- <a style="" class="share-btn"></a>-->
        <div style="text-align: center;margin: 0 1.5rem;"><a class="share-btn" href="main.php" style="float: left">返回首页<br><span>RETURN TO HOME PAGE</span></a>
            <a class="share-btn" style="float: right">分享<br><span>SHARE</span></a>
        </div>
    </div>
</div>
<div class="popup-share close">
    <img class="close" src="img/invite-share.png"/>
</div>
<div class="dis-pop">
    <img class="dis-pop-close" src="img/public-pop1.png" width="100%">
</div>
</body>
<script>
    $(".pop-img").on("click",function () {
        $(".dis-pop-close").attr("src","img/public-"+$(this).attr('id')+".png");
        $('.dis-pop').bPopup({
            closeClass:'dis-pop-close',
            positionStyle:'fixed'
        });
    });
    $(".pack-show").on("click",function(){
        $(".public-show").show();
        $(".public-hide").hide();
    });
    $(".pack-hide").on("click",function(){
        $(".public-show").hide();
        $(".public-hide").show();

    });
    $(".share-btn").on("click",function(){
        $(".popup-share").bPopup({
            positionStyle:"fixed",
            closeClass:"close"
        });
    });
</script>

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
