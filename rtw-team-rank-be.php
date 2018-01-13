<?php
require_once "./header.php";
$conn = connect_to_db();

$apiStartTime = getMicrotime();
$apiUrl = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
$loginIP = getClientIP();
$type = 'rtw_team_rank_be';

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


//进到首页，可简单判断，如果用户teamId不为-1且已确认开启任务且已绑定Nike+则留在本页面；其他情况统一交给join-choose处理
if ($ownInfo['teamId'] != -1)
{    //之前有参加过
    $headerLocation = 'theme-run.php';
} else {//之前没参加过        //如果没绑定
    if( $_SESSION['userInfo']['isBindNike'] != 1)
    {
        $headerLocation = 'rtw-theme-run-be.php';
    }else{//如果绑定了
        //如果rtw_join里没有，则跳去main
        $query = "SELECT id,src_openid FROM dtc_rtw_join_user WHERE openid = '$openid'";
        $result = mysql_query($query, $conn);
        if (mysql_num_rows($result) != 1) {
            $headerLocation = 'rtw-theme-run-be.php';
        }else{
            $srcOpenid = mysql_fetch_assoc($result)['src_openid'];
        }
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
if ($_GET['type']!='1' && $_GET['type']!='2' && $_GET['type']!='3' && $_GET['type']!='4'){
    $tab_type='1';
}else{
    $tab_type=$_GET['type'];
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

$rank = 0;
$num = 0;
$query = "SELECT count(1) num,src_openid FROM dtc_rtw_join_user WHERE src_openid!='0' AND is_count='1' GROUP BY src_openid ORDER BY count(1) DESC ";
//echo $query;
//exit();
$result = mysql_query($query,$conn);
while ($row = mysql_fetch_assoc($result)){
    if($row['src_openid']!='0'){
        $rank++;
        $num = $row['num'];
        if($row['src_openid']==$srcOpenid){
            break;
        }
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
        <link rel="stylesheet" href="//at.alicdn.com/t/font_8ddf6q9x52ucv7vi.css"/>
        <!--引入自己的Js-->
        <script src="js/responsive.js"></script>
        <!--引用Jquery-->
        <script src="js/jquery-2.1.4.js"></script>
        <script src="js/jquery-2.1.3.min.js"></script>
        <script src="js/template.js"></script>
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
                background: none;
                margin: 0;
                padding: 0;
                font-family: "STHeiti SC";
            }

            .rank {
                background: url(./img/bg.png) no-repeat;
                background-size: cover;
                min-height: 43.3rem;
                box-sizing: border-box;
                padding: 2rem 1rem 2rem;
                overflow: hidden;
            }

            .rank .title {
                font-size: 2rem;
                /*font-style: oblique;*/
                text-align: center;
                color: #ffffff;
                width: auto;
            }

            .rank .box {
                padding: 1.5rem 0;
                width: 23.5rem;
                height: 33rem;
                margin:2rem auto 0;
                background: rgba(255, 255, 255, 0.8);
                box-shadow: 6px 10px 0 rgba(250, 106, 6, 0.8);
            }
            .rank .box .list .list-li i {
                height: 1.6rem;
                width: 1rem;
                /*  margin-right: 1rem;*/
            }


            .tab_1{
                display: flex;
                width: 20rem;
                margin: 0 auto 1rem;
                align-items: center;
                justify-content: space-between;
                background: #7b7b7b;
                border-radius: 8px;
                font-size: 1rem;
                color: #ffffff;
                font-weight: bold;
                text-align: center;
            }
            .tab_1{
                display: flex;
                width: 20rem;
                margin: 0 auto 1rem;
                align-items: center;
                justify-content: space-between;
                background: #7b7b7b;
                border-radius: 8px;
                font-size: 1rem;
                color: #ffffff;
                font-weight: bold;
                text-align: center;
            }
            .item_1{
                display: block;
                border-left: 1px solid #ffffff;
                width: 6.7rem;
                height: 2rem;
                padding: 0.2rem 0 0;
                line-height: 2rem;
                font-family: 'fornike365';
            }
            .brl{
                border-top-left-radius:8px;
                border-bottom-left-radius:8px;
            }
            .brr{
                border-top-right-radius:8px;
                border-bottom-right-radius:8px;
            }
            .clb{
                border-left: none;
            }
            .active{
                background: #fa6b0e;
                color: #fff;
            }

            .li-text{
                display: inline-block;
                width: 100%;
            }
            .home{
                display: block;
                width: 2.0rem;
                height: 2.0rem;
                float: right;
                margin-top: -1.0rem;
                /*margin-bottom: 1rem;*/
                margin-left: 0.5rem;
            }
            .tab-title{
                display: none;
            }
            .active-1{
                display: block!important;
            }
            .box-bg{
                padding:1.5rem 1rem;
                margin: 3rem auto 0;
                background: rgba(255,255,255,1);
                border: 2px solid rgba(250, 106, 6, 0.9);
                box-shadow: 5px 5px 0 rgba(250, 106, 6, 0.9);
                box-sizing: border-box;
                width: 75%;
                text-align: center;
            }
            .box-bg p{
                margin: 0.4rem auto ;
                /* font-family: "STHeiti SC"; */
                display: block;
                font-size: 1.35rem;
                text-align: center;
                color: #f76a0e;
                font-weight: bold;
                line-height: 1.4rem;
                font-style: oblique;
            }
            .box-bg p span{
                font-family: 'fornike365';
                font-size: 0.95rem;
                font-weight: 100;
                font-style: oblique;
            }
            .unopen{
                font-family: 'fornike365';
                text-align: center;
                font-size: 1.8rem;
                color: #f76a0e;
                margin-top: 10rem;
            }
        </style>
    </head>
    <body>
    <div class="wrap rank">
        <a class="home" href="rtw-theme-run-be.php"><img src="./img/home-icon-1.png" width="100%"></a>
        <div class="box">
            <div class="tab_1" >
                <a class="item_1 clb brl" data-mid="1"><p class="li-text">TASK1</p></a>
                <a class="item_1 ceshi" data-mid="2"><p class="li-text">TASK2</p></a>
                <a class="item_1  " data-mid="3"><p class="li-text">TASK3</p></a>
                <a class="item_1 brr " data-mid="4"><p class="li-text">TASK4</p></a>
            </div>
            <div class="tab-title" data-t="1">
                <!--                <p class="unopen">尚未开启<br><span style="font-size: 1.6rem">COMING SOON</span></p>-->
                <div class="box-bg">
                    <p>跑团人数<br>
                        <span>TEAM SIZE</span>
                    </p>
                    <p style="font-style: normal;font-size: 1.9rem"><?php echo $num;?></p>
                </div>
                <div class="box-bg">
                    <p>当前跑团排名<br>
                        <span>CURRENT RANKING</span>
                    </p>
                    <p style="font-style: normal;font-size: 1.9rem"><?php echo $rank;?></p>
                </div>
            </div>
            <div class="tab-title " data-t="2">
                <p class="unopen">尚未开启<br><span style="font-size: 1.6rem">COMING SOON</span></p>
                <!--       <div class="box-bg" style="margin: 1.5rem auto 0">
                           <p>跑团人数<br>
                               <span>TEAM SIZE</span>
                           </p>
                           <p style="font-style: normal;font-size: 1.9rem">30</p>
                       </div>
                       <div class="box-bg" style="margin: 1.5rem auto 0">
                           <p>我的连续跑步天数<br>
                               <span>MY CONTINUOUS DAYS OF RUNNING</span>
                           </p>
                           <p style="font-style: normal;font-size: 1.9rem">3</p>
                       </div>
                       <div class="box-bg" style="margin: 1.5rem auto 0">
                           <p>当前跑团排名<br>
                               <span>CURRENT RANKING</span>
                           </p>
                           <p style="font-style: normal;font-size: 1.9rem">130</p>
                       </div>-->
            </div>
            <div class="tab-title " data-t="3">
                <p class="unopen">尚未开启<br><span style="font-size: 1.6rem">COMING SOON</span></p>
                <!--        <div class="box-bg" style="margin: 1.5rem auto 0">
                            <p>跑团最高里程<br>
                                <span>CURRENT RANKING</span>
                            </p>
                            <p style="font-style: normal;font-size: 1.9rem">130</p>
                        </div><div class="box-bg" style="margin: 1.5rem auto 0">
                            <p>当前跑团排名<br>
                                <span>CURRENT RANKING</span>
                            </p>
                            <p style="font-style: normal;font-size: 1.9rem">130</p>
                        </div>-->
            </div>
            <div class="tab-title " data-t="4">
                <p class="unopen">尚未开启<br><span style="font-size: 1.6rem">COMING SOON</span></p>
                <!--                <div class="box-bg" style="margin: 1.5rem auto 0">
                                    <p>我的本周膨胀里程<br>
                                        <span>MY EXPANDED MILEAGE</span>
                                    </p>
                                    <p style="font-style: normal;font-size: 1.9rem">30</p>
                                </div>
                                <div class="box-bg" style="margin: 1.5rem auto 0">
                                    <p>我的连续跑步天数<br>
                                        <span>MY CONTINIUOUS DAYS OF RUNNING</span>
                                    </p>
                                    <p style="font-style: normal;font-size: 1.9rem">3</p>
                                </div>
                                <div class="box-bg" style="margin: 1.5rem auto 0">
                                    <p>当前跑团排名<br>
                                        <span>CURRENT RANKING</span>
                                    </p>
                                    <p style="font-style: normal;font-size: 1.9rem">130</p>
                                </div>-->
            </div>
        </div>
    </div>
    <script>
        $('[data-mid=' + <?php echo $tab_type?> + ']').addClass("active");
        $('[data-t=' + <?php echo $tab_type?> + ']').addClass("active-1");
        $(".tab_1 .item_1").on("click", function () {
            $(this).addClass("active").siblings(".item_1").removeClass("active");
            $('[data-t=' + $(this).attr("data-mid") + ']').addClass("active-1").siblings(".tab-title").removeClass("active-1");
        });
    </script>
    <?php include "share_dtc_rtw.php";?>
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
