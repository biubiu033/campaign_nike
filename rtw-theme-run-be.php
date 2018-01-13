<?php
require_once "./header.php";
$conn = connect_to_db();

$apiStartTime = getMicrotime();
$apiUrl = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
$loginIP = getClientIP();
$type = 'rtw_theme_run_be';

$member_bind = 1;
$headerLocation = ''; //跳转地址
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


$isJoin = 0;//一次弹框，提示加入成功
//进到首页，可简单判断，如果用户teamId不为-1且已确认开启任务且已绑定Nike+则留在本页面；
if ($ownInfo['teamId'] != -1)
{    //之前有参加过
    $headerLocation = 'theme-run.php';
} else
{   //之前没参加过
    if( $_SESSION['userInfo']['isBindNike'] != 1)
    {
        //$headerLocation = 'bind.php';
        $member_bind = 0;
    }
    else{
        //请关注
        $query = "SELECT subscribe FROM weixin_nickname WHERE fromUsername='$openid'";
        $result = mysql_query($query,$conn);
        if(mysql_fetch_assoc($result)['subscribe'] == 0){
            $member_bind = -2; //没关注
        }
    }

    //如果绑定了，且关注了则把srcopenid给弄进rtw_join
    if($ownInfo['isJoin'] == 0){
        $query = "SELECT id FROM dtc_join_user WHERE openid = '$ptOpenidUrl'";
        $result = mysql_query($query,$conn);
        if(mysql_num_rows($result) == 1){
            //插入
            $query = "INSERT INTO dtc_rtw_join_user (`openid`,`src_openid`,`create_time`) VALUES
                    ('$openid','$ptOpenidUrl',NOW())";
            mysql_query($query,$conn);
            //fix member_project
            //确认参与，需增加yiqipao_member_project信息
            $sql1 = "select * from yiqipao_member_project where openid = '$openid' order by id desc limit 1;";
            $ret1 = mysql_query($sql1, $conn);
            $needAdd = true;
            //如果没有已参加项目，则此处应增加，如有则看是否是对的
            if (is_resource($ret1) && mysql_num_rows($ret1) != 0)
            {
                $row1 = mysql_fetch_assoc($ret1);
                $mpid = $row1['id'];
                //已参加该项目
                if ($row1['pid'] == $dtcPid && $row1['status'] > -1)
                {
                    $needAdd = false;
                    //团队名不对，则要重新来
                    if ($row1['teamId'] != $joinUserRow['teamId'])
                    {
                        $currTime = time();
                        $sql2 = "update yiqipao_member_project set status = -1, quitTime = $currTime where id = $mpid limit 1;";
                        mysql_query($sql2, $conn);
                        $needAdd = true;
                    }
                } else
                {
                    //如果不是退出的项目，则要退出
                    if ($row1['status'] > -1)
                    {
                        $currTime = time();
                        $sql2 = "update yiqipao_member_project set status = -1, quitTime = $currTime where id = $mpid limit 1;";
                        mysql_query($sql2, $conn);
                    }
                }
            }

            //最后收尾，如需增加项目则增加
            if ($needAdd)
            {
                $currTime = time();
                if ($currTime < strtotime("2017-08-04"))
                {
                    $currTime = 1501776222;
                }
                $teamId = $joinUserRow['teamId'];
                $sql2 = "insert into yiqipao_member_project (sid, mysid, pid, `long`, openid, uTarget, teamId, ip, created) ";
                $sql2 .= "values (8, 0, $dtcPid, 0, '$openid', 18, $teamId, '$loginIP', $currTime);";
                mysql_query($sql2, $conn);
            }
        }
        else{
            $member_bind = -1;//src_openid有错误
        }
    }
    else{
        $isJoin = 1;
    }

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
        <link rel="stylesheet" href="css/theme-run.css?a=123"/>
        <link rel="stylesheet" href="css/swiper-3.4.2.min.css">
        <!--引入自己的Js-->
        <script src="js/responsive.js"></script>
        <!--引用Jquery-->
        <script src="js/jquery-2.1.3.min.js"></script>
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
    <body style="background-color: #e9541c;" >
    <div class="wrap theme-run" name="theme-run">
        <div class="swiper-container">
            <div class="swiper-wrapper">
                <div class="swiper-slide">
                    <div class="taskshoe">
                    </div>
                </div>
            </div>
            <!-- 分页器 -->
            <div class="swiper-pagination"></div>
        </div>
        <a class="back" href="rtw-task-choose-be.php">活动详情<br><span>LEARN MORE</span></a>
    </div>
    <aside class="bind-pop pop-alert" style="display:none">
<!--        <a class="close" ></a>-->
        <p>请完成NRC账号绑定
            <br><span>Please bind your NRC account</span>
        </p>
        <a class="confirm-btn" href="http://www.makeyourruncount.com/campaign_nike/running/?c=user&a=bind&historyUrl=dtcbindNew">确认<br><span>CONFIRM</span></a>
    </aside>
    <aside class="follow-pop pop-alert" style="display:none">
        <!--        <a class="close" ></a>-->
        <p>请完成关注里享公众号
            <br><span>Please follow "lixiang" on WeChat Public Account.</span>
        </p>
        <img style="width: 11rem" src="https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQG18jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyWFZHSUpEbE5iRFAxMDAwME0wM0cAAgSehJZYAwQAAAAA">
    </aside>
    <aside class="invite-error pop-alert" style="display:none">
        <a class="close"></a>
        <p>邀请人有误
            <br><span>INVITE ERROR</span>
        </p>
    </aside>
    <aside class="success-pop pop-alert" style="display:none">
        <!--        <a class="close" ></a>-->
        <p>成功加入活动
            <br><span>SUCCESS</span>
        </p>
        <a class="confirm-btn b-close">确认<br><span>CONFIRM</span></a>
    </aside>
    <script src="js/swiper-3.4.2.min.js"></script>
    <script src="js/jquery.bpopup.js"></script>
    <script>
        <?php
        if($member_bind==1 && $isJoin == 0){
        echo "$('.success-pop').bPopup({
            positionStyle:'fixed'
        });";
        }elseif ($member_bind==0){
            echo "$('.bind-pop').bPopup({
            modalClose:false,
            positionStyle:'fixed'
        });";
        }elseif($member_bind==-2){
            echo "$('.follow-pop').bPopup({
            modalClose:false,
            positionStyle:'fixed'
        });";
        }elseif($member_bind==-1){
            echo "$('.invite-error').bPopup({
            modalClose:false,
            positionStyle:'fixed'
        });";
        }
        ?>
        var mySwiper = new Swiper ('.swiper-container', {
            direction: 'horizontal',
            loop: false,
            // 分页器
        })
    </script>
    </body>
    <?php include "share_dtc_rtw.php";?>
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