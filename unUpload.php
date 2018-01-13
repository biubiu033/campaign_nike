<?php
require_once "./header.php";
$conn = connect_to_db();

$apiStartTime = getMicrotime();
$apiUrl = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
$loginIP = getClientIP();
$type = 'unUpload';

$headerLocation = ''; //跳转地址
$sql = "select * from dtc_join_user where openid = '$openid';";
$result = mysql_query($sql, $conn);
if (is_resource($result) && mysql_num_rows($result) != 0)
{
    $joinUserRow = mysql_fetch_assoc($result);
    //看用户是否已经加入团队并写了相关信息
    if ($joinUserRow['teamId'] != -1)
    {
        //看用户是否是队长
        if ($joinUserRow['is_captain'] == 1)
        {
            //是队长看团队情况
            $teamId = $joinUserRow['teamId'];
            $sql1 = "select * from dtc_team where teamId = $teamId;";
            $result1 = mysql_query($sql1, $conn);
            if (is_resource($result1) && mysql_num_rows($result1) != 0)
            {
                $row1 = mysql_fetch_assoc($result1);
                if ($row1['is_input'] < 0)
                {
                    //团队名字没起好，跳到团队起名字页面
                    $headerLocation = 'nominate.php';
                }
            }
        } else
        {
            //队员看是否由队长审核通过
            if ($joinUserRow['is_allow'] == 1 && $_SESSION['userInfo']['isBindNike'] == 1)
            {
                //已确认开启且绑定过nike，则跳到main.php
//                $headerLocation = 'main.php';
            } else
            {
                //其他情况则跳转到等待队长审核页面
                $headerLocation = 'confirm.php';
            }
        }
    } else
    {
        //如果是被队长拒绝的话，则跳到confirm.php
        if ($joinUserRow['is_allow'] == -1)
        {
            $headerLocation = 'confirm.php';  //todo：如果是被队长拒绝了，就再也不能注册了
        }else{
            $headerLocation = 'join-choose.php';
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

$update = "UPDATE dtc_join_user SET open_task = 1  where openid = '{$openid}' and open_task !=1 ";
mysql_query($update,$conn);
//判断用户的属性 是否为队长
$sql3 = "select * from dtc_join_user,dtc_team where dtc_team.teamId=dtc_join_user.teamId and dtc_join_user.openid='$openid';";
$result3 = mysql_query($sql3, $conn);
$row3 = mysql_fetch_assoc($result3);
$is_captain = $row3['is_captain'];
$teamid = $row3['teamId'];
$teamName = $row3['teamName'];
//判断该队活动照是否上传
$sql = "select count(id) as count from  dtc_theme_run where t_id = $teamid";
$ret = mysql_query($sql, $conn);
$row = mysql_fetch_assoc($ret);
$is_upload = false;
if ($row['count'] > 0) {
    $is_upload = true;//已经投票
    header("Location:upload.php");
    exit();
} else {

}


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
    <link rel="stylesheet" href="css/public.css"/>
    <link rel="stylesheet" href="//at.alicdn.com/t/font_9oyfwnufd6q7iudi.css"/>
    <!--引入自己的Js-->
    <script src="js/responsive.js"></script>
    <!--引用Jquery-->
    <script src="js/jquery-2.1.4.js"></script>
    <script src="js/jquery-2.1.3.min.js"></script>
    <script src="js/jquery.bpopup.js"></script>
    <script src="js/script.js"></script>
    <title>里享</title>
    <style>
        .weui-dialog, .weui-toast{
            z-index: 10000;
        }
    </style>
</head>
<body>
<div class="wrap upload">
    <div class="title">
        <img src="img/upload-title.png">
        <div class="personal">
            <div class="body">
                <span><?php echo $teamName; ?></span>
            </div>
        </div>
    </div>
    <div class="un-upload">
        <div class="content">
            你的队长还未上传创意路线及背后故事，<br>快快让他组织大家活动起来。<br>
            <p>HURRY UP! YOUR CAPTAIN HASN’T UPLOADED ANY CREATIVE IDEAS OR STORIES. THE CLOCK IS TICKING!</p>
        </div>
       <!-- <?php /*if ($is_captain) { */?>
            <a class="upload-btn"></a>
        --><?php /*} */?>
    </div>
    <div style="display:block;height: 4rem;"></div>
    <div class="foot-btn">
        <a href="vote.php">投票区<p>VOTING</p></a>
        <div class="line"></div>
        <a href="rank.php">英雄榜<p>RANKING</p></a>
    </div>
</div>

<div class="popup-box" >
    <form method="post" enctype="multipart/form-data">
        <i class="iconfont icon-svg26 close"></i>
        <div class="title">请上传图片<p>UPLOAD PHOTOS</p></div>
        <label for="photo">
            <div class="upload-btn"><input type="file" name="photo" id="photo"/></div>
            <div class="upload-img"></div>
        </label>
        <a class="upload-photo">上传跑步路线拼图<p>UPLOAD ASSEMBLED PHOTO OF RUNNING ROUTE </p></a>
        <textarea rows="5" placeholder="请描述你们团队的跑步路线及其创意来源Please describe your running route and its creative sources" name="slogan"></textarea>
        <a class="upload-submit">提交<p>SUBMIT</p></a>
    </form>
</div>

</body>
<script src="http://cdn.bootcss.com/jquery-weui/1.0.0-rc.0/js/jquery-weui.min.js"></script>
<script src="js/ajaxfileupload.js"></script>
<script src="js/verify.js"></script>
<script>
    $(".upload-photo").on("click", function () {
        $(".upload-btn").click();
    });
    $(".upload-btn").on("click", function () {
        $(".popup-box").bPopup({
            positionStyle: "fixed",
            closeClass: "close"
        });
    });
    $('form').verify({
        fields: {
            //选中名字字段，设置校验规则
            'textarea[name="slogan"]': {
                //错误提示信息
                message: '*请填写参赛宣言',
                //校验规则，返回布尔值，true则成功，false则失败，可在此回调函数中插入您的业务逻辑，最终返回bool值就好，极方便
                test: function (val) {
                    return val.length > 0;
                }
            },
            'input[name="photo"]': {
                //错误提示信息
                message: '*请选择参赛照片',
                //校验规则，返回布尔值，true则成功，false则失败，可在此回调函数中插入您的业务逻辑，最终返回bool值就好，极方便
                test: function (val) {
                    return val.length > 0;
                }
            }
        },
        // 错误模板，设置装载错误信息的容器，插入上面设置的错误提示信息
        errorTemplate: function (error) {
            return $('<div class="wrong">' + error.message + ' </div>');
        },
        //表单提交按钮，若未设置，则为默认的form.submit;
        submitButton: '.upload-submit',
        success: function () {
            $.confirm("确定上传？",function(){
                $.showLoading('正在上传中..');
                $('.popup-box').css('z-index','1999');
                //加载
                $.ajaxFileUpload({
                    url: './apiunUpload.php',
                    type: 'post',
                    data: {
                        slogan: $('textarea[name="slogan"]').val()
                    },
                    cache: false,
                    secureuri: false, //一般设置为false
                    fileElementId: 'photo', // 上传文件的id、name属性名
                    dataType: 'JSON', //返回值类型，一般设置为json、application/json  这里要用大写  不然会取不到返回的数据
                    success: function (data, status) {
                        $.hideLoading();
                        var msg = eval("(" + data + ")");
                        //加载
                        if (msg.errcode == "1") {
                            $.alert(msg.errmsg,function(){
                                window.location.href = "./unUpload.php?<?php echo rand(0, 1) ?>";
                            });
                        }else{
                            $.alert(msg.errmsg);
                        }
                    },
                    error: function (data, status, e) {
                        $.alert("请求失败");
                    }
                });
            });
        }
    });
</script>
<?php include "share_dtc.php";?>
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