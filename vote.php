<?php
require_once "./header.php";
$conn = connect_to_db();

$apiStartTime = getMicrotime();
$apiUrl = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
$loginIP = getClientIP();
$type = 'vote';

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

$headerLocation = "theme-run-heros.php";

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

$sql = "select count(id) as count from  dtc_theme_vote where openid='$openid'";
$ret = mysql_query($sql, $conn);
$row = mysql_fetch_assoc($ret);
$vote = 10 - $row['count'];
?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
        <!--    引入自己的css-->
        <link rel="stylesheet" href="http://cdn.bootcss.com/weui/1.1.0/style/weui.min.css">
        <link rel="stylesheet" href="http://cdn.bootcss.com/jquery-weui/1.0.0-rc.0/css/jquery-weui.min.css">
        <link rel="stylesheet" href="css/reset.css">
        <link rel="stylesheet" href="css/theme-run-heros.css"/>
        <link rel="stylesheet" href="http://at.alicdn.com/t/font_nxf8c0l8q90y66r.css"/>
        <!--引入自己的Js-->
        <script src="js/responsive.js"></script>
        <!--引用Jquery-->
        <script src="js/jquery-2.1.3.min.js"></script>
        <script src="http://cdn.bootcss.com/jquery-weui/1.0.0-rc.0/js/jquery-weui.min.js"></script>
        <title>里享</title>
        <style>
            .weui-dialog, .weui-toast {
                z-index: 10000;
            }
            .popup_dialog {
                display: none;
            }

            .popup_dialog p {
                color: #fa6b0e;
                font-size: 2.4rem;
                font-weight: bold;
                line-height: 2rem;
                text-align: center;
            }

            .popup_dialog p span {
                display: block;
                font-size: 1rem;
                font-family: 'fornike365';
            }

            .popup_dialog .popup_btn {
                display: flex;
                align-items: center;
                justify-content: center;
                margin-top: 2.5rem;
            }

            .popup_dialog .popup_btn a {
                display: block;
                background: #fff;
                font-weight: bold;
                font-size: 1.4rem;
                line-height: 1.2rem;
                padding: 0.7rem 0 0.3rem;
                width: 8rem;
                border-radius: 15px;
                border: 2px solid #fa6b0e;
                box-shadow: 2px 3px #fa6b0e;
                text-align: center;
                color: #fa6b0e;
                font-style: oblique;
                margin: 0 1rem;
            }

            .popup_dialog .popup_btn span {
                font-size: 1rem;
                font-family: 'fornike365';
            }
            .popup{
                display: none;
                border: 2px solid #fa6b0e;
                box-shadow: 3px 3px #fa6b0e;
                background: rgba(255,255,255,0.9);
                text-align: center;
                width: 15rem;
                height: 13rem;
                border-radius: 10px;
            }
            .popup i {
                font-size: 6rem;
                color: #fa6b0e;
            }
            .popup p {
                margin-top: -1.5rem;
                color: #fa6b0e;
                font-size: 1.4rem;
                font-family: 'fornike365';
            }

            .popup p span {
                display: block;
                font-size: 0.8rem;
                line-height: 0.8rem;

            }
        </style>
    </head>
    <body style="background-color: #fa6b0e;">
    <div class="wrap theme-run-heros">
        <div class="explain header">
            <p>点击图片查看详情并投票<br>
            </p>
            <p class="en">
                CLICK THE PHOTOS FOR DETAILS AND VOTE FOR<br>
                YOUR FAVORITE RUNNING ROUTE
            </p>
            <h2>您还需投<span class="num"><?php echo $vote; ?></span>票（投满10票才有效）<br>
            </h2>
            <p class="en"><?php echo $vote; ?> VOTES LEFT (YOU ARE REQUIRED TO VOTE FOR<br>
                10 ROUTES BEFORE SUBMISSION)</p>
            <a class="vote" href="main.php" style="height: 2.5rem;float:none">首页<br><span class="__span">HOME PAGE</span></a>
        </div>
        <div class="vote-box">
            <?php
            $sql = "select * from  dtc_theme_run where status=1";
            $ret = mysql_query($sql, $conn);
            $count = 0;
            while ($row = mysql_fetch_assoc($ret)) {
                $count++;
                $t_id = $row['t_id'];
                $flag = 0;
                $sql_vote = "select * from  dtc_theme_vote where openid='$openid' AND t_id='$t_id'";
                $ret_vote = mysql_query($sql_vote, $conn);
                if (is_resource($ret_vote) && mysql_num_rows($ret_vote)) {
                    $flag = 1;
                }else{
                    $flag = 0;
                }
                ?>
                <div class="vote-items" flag = "<?php echo $flag;?>"  data-mid="<?php echo $count; ?>">
                    <div class="team-name">
                        <span><?php echo $row['t_name']; ?></span>
                        <span <?php if ($flag){ ?>class="chose-icon" <?php } ?>></span>
                    </div>
                    <div class="small-img">
                        <img src="<?php echo $row['theme_img']; ?>">
                    </div>

                    <p class="describe-1">
                        <?php echo $row['slogan']; ?>
                    </p>
                </div>
            <?php } ?>
        </div>
    </div>
    <?php
    $sql = "select * from  dtc_theme_run where status=1";
    $ret = mysql_query($sql, $conn);
    $count = 0;
    while ($row = mysql_fetch_assoc($ret)) {
        $count++;
        ?>
        <aside class="pop-detail" data-to="pop-<?php echo $count; ?>">
            <div class="imfo" style="text-align: center">
                <p><span><?php echo $row['t_name']; ?></span></p>
            </div>
            <div class="updataimg">
                <img style="width: 100%;height: auto" src="<?php echo $row['theme_img']; ?>">
            </div>
            <div class="describe" style="height: 2.5rem;overflow: hidden">
                <?php echo $row['slogan']; ?>
            </div>
            <a class="seemore btn-style">查看全文<i class="iconfont icon-arrowDown"></i><br><span>FULL MESSAGE</span></a>
            <a class="putawy btn-style" style="display: none">收起<i class="iconfont icon-jiantou-copy"></i><br><span>PUT AWAY</span></a>
            <a class="vote-btn" id="<?php echo $row['t_id']; ?>">投TA一票<br>
                <span>I WANT TO VOTE</span>
            </a>
        </aside>
    <?php } ?>
    <div class="popup_dialog">
        <p class="confirm">确定为他投票<span>PLEASE CONFIRM YOUR VOTE</span></p>
        <div class="popup_btn">
            <a class="sure">确&nbsp;&nbsp;&nbsp;定<br><span>CONFIRM</span></a>
            <a class="close">取&nbsp;&nbsp;&nbsp;消<br><span>CANCEL</span></a>
        </div>
    </div>
    <div class="popup popup_success">
        <i class="iconfont icon-gou"></i>
        <p class="success_msg">投票完成<span>VOTE COMPLETED</span></p>
    </div>
    <div class="popup popup_fail">
        <i class="iconfont icon-cha"></i>
        <p>投票未完成，请重试<span>VOTE UNCOMPLETED,<br>PLEASE TRY AGAIN</span></p>
    </div>
<!--    <div class="popup-share" style="display: none;width: 100%;">-->
<!--        <img style="width: 26.666666rem;height: 43rem;" src="img/pop-lock.png">-->
<!--    </div>-->
    <script src="js/jquery.bpopup.js"></script>
    <script>
/*//        $('.popup-share').bPopup({
//            positionStyle:'fixed',
//            opacity: 0.9
//        });
        var flag;
        $('.vote-items').on("click", function () {
            flag = $(this).attr('flag');

            if(flag == 0){
                console.log(flag);
                $('.vote-btn').html('投TA一票<br> <span>I WANT TO VOTE</span>');
                $('.confirm').html('确定为他投票<span>PLEASE CONFIRM YOUR VOTE</span>');
            }else {
                $('.vote-btn').html('取消投票<br> <span>I WANT TO CANCEL</span>');
                $('.confirm').html('确定取消投票<span>PLEASE CONFIRM YOUR CANCEL VOTE</span>');
            }
            $('.describe').css({
                "height": "2.5rem",
                "overflow": "hidden"
            });
            $('.seemore').show();
            $('[data-to=' + "pop-" + $(this).attr("data-mid") + ']').bPopup({
                positionStyle: "absolute"
            });
        });
        $('.seemore').on("click", function () {
            $('.describe').css({
                "height": "auto",
                "overflow": "auto"
            });
            $('.seemore').hide();
            $('.putawy').show();
        });
        $('.putawy').on('click',function () {
            $('.describe').css({
                "height": "2.5rem",
                "overflow": "hidden"
            });
            $('.seemore').show();
            $('.putawy').hide();
        });
        $(".vote-btn").on("click", function () {
            var id = this.id;
            //换弹窗样式
            $(".popup_dialog").bPopup({
                positionStyle: "fixed",
                closeClass: "close"
            });
            $(".sure").unbind("click");
            $(".sure").on("click", function () {
                if(flag == 0){
                    $.ajax({
                        url: './apiVote.php',
                        type: 'get',
                        data: {
                            id: id
                        },
                        dataType: 'JSON', //返回值类型，一般设置为json、application/json  这里要用大写  不然会取不到返回的数据
                        success: function (msg) {
                            //加载
                            $(".popup_dialog").bPopup().close();
                            if(msg.errcode==1){
                                $('.success_msg').html(msg.errmsg+"<span>VOTE COMPLETED</span>");
                                $(".popup_success").bPopup({
                                    positionStyle: "fixed",
                                    modalClose:false
                                });
                                $(".popup,.b-modal").on("click",function(){
                                    window.location.href = "./vote.php";
                                });
                            }else{
                                $(".popup_fail p").html(msg.errmsg);
                                $(".popup_fail").bPopup({
                                    positionStyle: "fixed",
                                    closeClass:"popup"
                                });
                            }

                        },
                        error: function (data, status, e) {
                            $(".popup_dialog").bPopup().close();
                            $(".popup_fail").bPopup({
                                positionStyle: "fixed",
                                closeClass:"popup"
                            });
                        }
                    });
                }else {
                    $.ajax({
                        url: './apiNoVote.php',
                        type: 'get',
                        data: {
                            id: id
                        },
                        dataType: 'JSON', //返回值类型，一般设置为json、application/json  这里要用大写  不然会取不到返回的数据
                        success: function (msg) {
                            //加载
                            $(".popup_dialog").bPopup().close();
                            if(msg.errcode==1){
                                $('.success_msg').html( msg.errmsg+"<span> COMPLETED</span>");
                                $(".popup_success").bPopup({
                                    positionStyle: "fixed",
                                    modalClose:false
                                });
                                $(".popup,.b-modal").on("click",function(){
                                    window.location.href = "./vote.php";
                                });
                            }else{
                                $(".popup_fail p").html(msg.errmsg);
                                $(".popup_fail").bPopup({
                                    positionStyle: "fixed",
                                    closeClass:"popup"
                                });
                            }

                        },
                        error: function (data, status, e) {
                            $(".popup_dialog").bPopup().close();
                            $(".popup_fail").bPopup({
                                positionStyle: "fixed",
                                closeClass:"popup"
                            });
                        }
                    });
                }
            });
        });*/
    </script>
    <?php include "share_dtc.php"; ?>
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
