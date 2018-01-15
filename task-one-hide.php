<?php
require_once "./header.php";
$conn = connect_to_db();

$apiStartTime = getMicrotime();
$apiUrl = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
$loginIP = getClientIP();
$type = 'task-one-hiden';

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
                $openTask = 1;
            }else{
                $openTask = 0;
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

$t_id = 2; //代表1的隐藏任务，马拉松跑

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
            .foot{
                margin-top: 6rem;
            }
            .foot-a{
                color:#c54c0f;
            }
            .start{
                margin-top:2rem!important;
            }
            ::-webkit-scrollbar {
                border-radius: 30px;
                width: 7px;
                height: 16px;
                background-color: #cccccc;
            }

            ::-webkit-scrollbar-track {
                border-radius: 30px;
                background-color: #cccccc;
            }

            ::-webkit-scrollbar-thumb {
                height: 5px;
                border-radius: 30px;
                background-color: #fa6b0e;
            }
        </style>
    </head>
    <body>
    <div class="wrap theme-run" name="task-one">
        <div class="content">
            <span class="corner corner-2"> <!--左上角角标--></span>
            <div class="heart-area" rel="like">
                <?php if ($liked) { ?>
                    <div class="heart active"
                         style="background-image: url(img/theme-run-heart-active.png)" id="like1"></div>

                <?php } else { ?>
                    <div class="heart"
                         id="like1"></div>
                <?php } ?>
                <p class="liked_sum_count"><?php echo $sumLiked; ?></p>
            </div>
            <div class="img-area"><img src="img/mls-img.png" width="60%"></div>
            <div class="explain">
                <h1>任务说明</h1>
                <span>WHAT’S THE MISSION？</span>
                <div style="background: #fe4819;height: 1px;width: 6.5rem; margin: 0 auto 1rem;"></div>
                <div style="height: 15.5rem;overflow-y: scroll;margin: 0 1rem 0 0;">
                    <p style="margin: 1rem 0 0 1.2rem;">
                        以上海国际马拉松比赛项目分类，
                        根据任务开启至9月4日间累计的跑步里程排名选出获胜个人，
                        为全马获胜者提供马拉松门票和往返交通及住宿，为其他项目获胜者提供马拉松的门票。

                    </p>
                    <p style="font-size: 0.7rem;margin: 1rem 0 0 1.2rem;">
                        RUN FOR YOUR SHANGHAI INTERNATIONAL MARATHON EVENT FROM MISSION STARTS TO SEPTEMBER 4TH.
                        WINNERS OF FULL MARATHON WILL BE PROVIDED WITH FULL ACCOMMODATION AND MARATHON QUOTA.
                        WINNERS OF OTHER EVENTS WILL BE PROVIDED WITH MARATHON QUOTA.

                    </p>
                    <div class="warn">细则/RULES</div>
                    <ul class="warn-list">
                        <li>申请人所在团队必须完成1号挑战。<p>DISQUALIFIED IF YOUR TEAM DO NOT PARTICIPATE IN CHALLENGE 1.</p></li>
                        <li>获奖名单生成后，组委会将与获奖者的店长或直属经理确认其在职情况。<p>COMMITTEE WILL CONFIRM WORKING STATUS WITH WINNER'S HC OR LINE MANAGER AFTER FINAL LIST.</p></li>
                        <li>组委会将会为全马最终获奖者统一安排往返交通及一日住宿。<p> COMMITTEE WILL ARRANGE ONE DAY ACCOMMODATION AND ROUND TRIP FOR WINNERS OF FULL MARATHON.</p></li>
                    </ul>
                </div>
            </div>
            <?php
            if($openTask == '1'){
                ?>
                <div style="text-align: center;margin: 0 1.5rem;"><a class="start" href="main.php" style="float: left">返回首页<br><span>RETURN TO HOME PAGE</span></a>
                    <a class="start" href="mls-choose-result.php" style="float: right">进入任务<br><span>ENTER</span></a>
                </div>
               <!-- <a class="start" href="mls-choose-result.php">进入任务<br><span>ENTER</span></a>-->
            <?php }else{?>
                <div style="text-align: center;margin: 0 1.5rem;"><a class="start" href="main.php" style="float: left">返回首页<br><span>RETURN TO HOME PAGE</span></a>
                    <a class="start" href="mls-choose-result.php" style="float: right">开启任务<br><span>ENTER</span></a>
                </div>
                <!--<a class="start" href="mls-choose.php">开启任务<br><span>START</span></a>-->
            <?php }?>
            <div class="foot">
                <a class="foot-a" href="http://r.lovemojito.com/short.php?a=4uphhl">常见问题<br>
                    <span class="foot-span">FAQ
                    </span>
                </a>
                <span style="font-size: 1.8rem;color: #c54c0f;margin: 0 0.2rem">/</span>
                <a class="foot-a" href="http://r.lovemojito.com/short.php?a=4uphhl">技术支持<br>
                    <span class="foot-span">TECH SUPPORT</span>
                </a>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function () {
            var sumLiked = <?php echo $sumLiked; ?>;
            //开启任务按钮
            //喜欢按钮
            $('.heart').on("click", function () {
                if (!$(this).hasClass("active")) {
                    $.ajax({
                        url: "apiLikeTask.php?task_id=2&like=1",
                        dataType: "json",
                        async: true,
                        beforeSend: function () {
                            $(this).unbind("click");
                        },
                        complete: function () {
                        },
                        success: function ($data) {
                            $('.liked_sum_count').html(sumLiked + 1);
                            sumLiked = sumLiked + 1;
                            $('.heart').addClass("active");
                            setTimeout(function () {
                                $('.heart').css({
                                    "background-image": "url(img/theme-run-heart-active.png)"
                                })
                            }, 400)
                        }
                    });
                } else {
                    $.ajax({
                        url: "apiLikeTask.php?task_id=2&like=0",
                        dataType: "json",
                        async: true,
                        beforeSend: function () {
                            $(this).unbind("click");
                        },
                        complete: function () {
                        },
                        success: function ($data) {
                            $('.liked_sum_count').html(sumLiked - 1);
                            sumLiked = sumLiked - 1;
                            $('.heart').removeClass("active");
                            setTimeout(function () {
                                $('.heart').css({
                                    "background-image": "url(img/theme-run-heart.png)"
                                })
                            }, 400)
                        }
                    });

                }


            });
        });
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
