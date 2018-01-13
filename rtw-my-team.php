<?php
require_once "./header.php";
$conn = connect_to_db();

$apiStartTime = getMicrotime();
$apiUrl = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
$loginIP = getClientIP();
$type = 'rtw-task-choose';

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

$query = "SELECT openid FROM dtc_rtw_join_user WHERE src_openid='$openid'";
$result = mysql_query($query,$conn);
$str = '';$str_nickname='';
while ($row = mysql_fetch_assoc($result)){
    $this_openid = $row['openid'];
    $str .= "openid='$this_openid' OR ";
    $str_nickname .= "fromUsername='$this_openid' OR ";
}
$str = trim($str,'OR '); //字段为openid的用
$str_nickname = trim($str_nickname,'OR '); //字段为fromUsername用

$query = "SELECT `openid`,`name`,headimg,isBindNike FROM yiqipao_member WHERE $str";
//        echo $query;
$result = mysql_query($query,$conn);
$my_teammates = array();
while ($row = mysql_fetch_assoc($result)){
    $that_openid = $row['openid'];
    $name = $row['name'];
    $headImg = $row['headimg'];
    $isBindNike = $row['isBindNike'];
    $my_teammates[$that_openid] = array(
        'openid' => $that_openid,
        'name' => $name,
        'headimg' => $headImg,
        'isBindNike' => $isBindNike,
        'subscribe' => 0
    );
}
$query = "SELECT `fromUsername`,`subscribe` FROM weixin_nickname WHERE $str_nickname";
$result = mysql_query($query,$conn);
while ($row = mysql_fetch_assoc($result)) {
    $that_openid = $row['fromUsername'];
    $my_teammates[$that_openid]['subscribe'] = $row['subscribe'];
}

$phpfile = isset($phpfile) ? $phpfile : "campaign_nike/running/dtc/rtw-invite.php?ptOpenid=".$openid;

?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
        <!--    引入自己的css-->
        <link rel="stylesheet" href="css/reset.css">
        <!--引入自己的Js-->
        <script src="js/responsive.js"></script>
        <!--引用Jquery-->
        <script src="js/jquery-2.1.3.min.js"></script>
        <script src="js/jquery.bpopup.js"></script>
        <title>里享</title>
        <style>
            @font-face {
                font-family: 'fornike365';
                src: url('./font/tradegothicfornike365-bdcn.eot');
                src: local('☺'), url('./font/tradegothicfornike365-bdcn.woff') format('woff'), url('./font/tradegothicfornike365-bdcn.ttf') format('truetype'), url('./font/tradegothicfornike365-bdcn.svg') format('svg');
                font-weight: normal;
                font-style: normal;
            }

            body {
                background: #fe8c03;
            }

            .theme-run {
                background: url(./img/bg.png) no-repeat;
                background-size: cover;
                position: relative;
                margin: 0 0;
                font-family: "STHeiti SC";
                box-sizing: border-box;
                padding-top: 1rem;
                height: 43rem;
            }

            .theme-run .explain {
                text-align: left;
                box-sizing: border-box;
                padding: 0 0 1rem;
                margin: 1.5rem;
                height: 27rem;
                overflow-y: scroll;
            }

            .theme-run h1 {
                font-weight: normal;
                color: #ffffff;
                text-align: center;
                letter-spacing: 5px;
            }

            .theme-run span {
                display: block;
                margin-top: -0.3rem;
                margin-bottom: 0.5rem;
                font-style: oblique;
                color: #ffffff;
                font-weight: bold;
                font-size: 1rem;
                font-family: 'fornike365';
            }

            .theme-run p {
                text-align: left;
                display: block;
                margin: 1rem auto 0;
                width: 21rem;
                color: #ffffff;
                font-size: 0.95rem;
                font-family: 'fornike365';
            }
            .home-icon {
                display: inline-block;
                width: 2rem;
                height: 2rem;
            }

            .btn-style {
                padding-top: 0.4rem;
                display:block;
                background: rgba(255, 255, 255, 0.8);
                box-shadow: 2px 3px 0 #b83a0f;
                border: 2px solid #ea5012;
                width: 13rem;
                height: 2.2rem;
                line-height: 1.3rem;
                border-radius: 8px;
                text-align: center;
                color: #fc5219;
                font-size: 1.0rem;
                font-family: 'fornike365';
                margin: 1.5rem auto 0;
            }

            .btn-style span {
                color: #fc5219 !important;
                font-size: 0.65rem !important;
            }

            ::-webkit-scrollbar {
                border-radius: 10px;
                width: 8px;
                height: 16px;
                background-color: #cccccc;
            }

            ::-webkit-scrollbar-track {
                border-radius: 10px;
                background-color: #cccccc;
            }

            ::-webkit-scrollbar-thumb {
                height: 5px;
                border-radius: 10px;
                background-color: #fa6b0e;
            }
            .share-popup{
                display: none;
                width: 26.6666rem;
                height:40rem;
                padding-top: 0;
            }
            .avator{
                display: inline-block;
                width: 32%;
                text-align: center;
                margin-top: 0.6rem;
                position: relative;
            }
            .avator-img{
                width: 65%;
                border: 3px solid #f87401;
                border-radius: 50%;
            }
            .avator span{
                margin-top: 0.2rem;
            }
            .joinbefor{
                padding: 1.5rem;
                margin:2rem auto 0;
                background: rgba(255,255,255,0.9);
                box-shadow: 5px 5px 0 rgba(250, 106, 6, 0.9);
                box-sizing: border-box;
                width: 22rem;
                text-align: center;
            }
            .joinbefor .close{
                display: block;
                background: url("./img/close-btn.png")no-repeat;
                background-size: cover;
                height: 1.5rem;
                width: 1.5rem;
                margin-left: 18rem;
            }
            .joinbefor p{
                margin: 0.4rem auto 1rem;
                display: block;
                font-size: 1.35rem;
                text-align: center;
                color: #f76a0e;
                font-weight: bold;
                line-height: 1.4rem;
            }
            .joinbefor p span{
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
    <div class="wrap theme-run">
        <div style="margin: 0 1.5rem;text-align:right"><a class="home-icon" href="main.php"><img src="./img/home-icon-1.png" width="100%"></a></div>
        <h1>我的跑团</h1>
        <span style="text-align: center;margin-top: 0.5rem;font-size: 1rem">MY TEAM</span>
        <div class="explain">
            <?php
            foreach ($my_teammates AS $value){
                $headImg = $value['headimg'];
                $name = $value['name'];
                echo "";
                if($value['isBindNike'] ==1 && $value['subscribe'] ==1){
                    echo " <div class=\"avator\">
                        <img src=\"$headImg\" class=\"avator-img\">
                        <span>$name</span></div>";
                }elseif($value['isBindNike'] ==0){
                    echo " <div class=\"avator\" id='unbind'><img src=\"./img/mask.png\" width=\"65%\" style=\"
                        position: absolute;
                        width: 71%;
                        border-radius: 50%;\">
                        <img src=\"$headImg\" class=\"avator-img\">
                        <span>$name</span></div>";
                }elseif($value['subscribe'] ==0){
                    echo " <div class=\"avator\" id='unsubscribe'><img src=\"./img/mask.png\" width=\"65%\" style=\"
                        position: absolute;
                        width: 71%;
                        border-radius: 50%;\">
                        <img src=\"$headImg\" class=\"avator-img\">
                        <span>$name</span></div>";
                }
            }
            ?>
        </div>
        <div style="margin: 1rem 2.5rem 0">
            <a class="btn-style" id="invite">邀请小伙伴<br><span>INVITE FRIENDS</span></a>
        </div>
    </div>
    <div id="share" class="share-popup">
        <img class="b-close" src="./img/share.png" width="100%">
    </div>
    <aside class="joinbefor" id="unbind-pop" style="display:none">
        <a class="close b-close"></a>
        <p>跑手未绑定NRC账号<br>
            <span>The runner hasn't binded NRC account.</span>
        </p>
        <!--        <a class="confirm-btn b-close">确认<br><span>CONFIRM</span></a>-->
    </aside>
    <aside class="joinbefor" id="unsubscribe-pop" style="display:none">
        <a class="close b-close"></a>
        <p>跑手未关注里享公众号<br>
            <span>The runner hasn't followed "lixiang" on WeChat.</span>
        </p>
        <!--        <a class="confirm-btn b-close">确认<br><span>CONFIRM</span></a>-->
    </aside>
    <script>
        $('#invite').on('click',function () {
            $('#share').bPopup({
                positionStyle:'fixed'
            })
        })
        $('#unbind').on('click',function () {
            $('#unbind-pop').bPopup({
                positionStyle:'fixed'
            })
        });
        $('#unsubscribe').on('click',function () {
            $('#unsubscribe-pop').bPopup({
                positionStyle:'fixed'
            })
        });
    </script>
    </body>
    </html>
<?php include "share_dtc_rtw.php";?>
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