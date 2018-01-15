<?php
require_once "./header.php";
$conn = connect_to_db();

$apiStartTime = getMicrotime();
$apiUrl = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
$loginIP = getClientIP();
$type = 'nominate';

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
           /* $teamId = $joinUserRow['teamId'];
            $sql1 = "select * from dtc_team where teamId = $teamId;";
            $result1 = mysql_query($sql1, $conn);
            if (is_resource($result1) && mysql_num_rows($result1) != 0)
            {
                $row1 = mysql_fetch_assoc($result1);
                if ($row1['is_input'] == 1)
                {
                    //团队名字已起好，则跳到confirm.php
//                    $headerLocation = 'confirm.php';
                }
            }*/
        } else
        {
            //非队长，交给join-choose处理
            $headerLocation = 'join-choose.html';
        }
    } else
    {
        //团队未设置，交给join-choose处理
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

$unixtime=time();
$key = 'jyj';
$token = md5($openid . $key . $unixtime);
$update = "UPDATE dtc_join_user SET open_task = 1  where openid = '{$openid}' and open_task !=1 ";
mysql_query($update,$conn);

//判断用户的属性 是否为队长
$sql = "select * from dtc_join_user,dtc_team where dtc_team.teamId=dtc_join_user.teamId and dtc_join_user.openid='$openid';";
$result = mysql_query($sql, $conn);
$row = mysql_fetch_assoc($result);
$is_captain = $row['is_captain'];
$team_id = $row['teamId'];
$is_input =$row['is_input'];
$teamName = $row['teamName']." 队";

if(!$is_captain||!isset($is_captain)||$is_captain==''){
    header('Location:./main.php');
}
?>
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/html">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
    <!--    引入自己的css-->
    <link rel="stylesheet" href="css/reset.css?a=145">
    <link rel="stylesheet" href="css/main.css?a=123"/>
    <!--引入自己的Js-->
    <script src="js/responsive.js"></script>
    <!--引用Jquery-->
    <script src="js/jquery-2.1.3.min.js"></script>
    <script src="js/jquery.bpopup.js"></script>
    <title>里享</title>
</head>
<body>
<div class="wrap nominate" name="nominate">
    <div class="projrct_title "></div>
    <div class="join-input">
        <?php if($is_input < 0){?>
            <form class="form-nominate">
                <h1>欢迎加入【我是跑手】<br>作为团队队长<br>请为你们的团队命名吧
                    <p style="font-size: 1.0rem;line-height: 1rem;letter-spacing: 0">
                        WELCOME TO RUN NIKE DIRECT RUN! AS TEAM CAPTAIN, PLEASE FILL IN YOUR GROUP NAME.
                    </p>
                </h1>
                <div class="team-code">
                    <span class="input-icon"></span>
                    <input class="edit_input" type="text"  name="name" maxlength="10" oninput="get(this.value)">
                    <span style="font-size: 1.2rem;color:#fa6710;font-weight: bold;font-style: oblique">队</span>
                </div>
                <p>5个汉字/10字符以内<br><small style="font-family: 'fornike365';letter-spacing: 0">LIMIT OF 5 CHARACTERS/10 BYTES</small></p>
            </form>
        <?php }else{?>
            <br>
            <h1>欢迎加入【我是跑手】<br>你的团队名称是
                <p style="font-family: 'fornike365';font-size: 1.0rem;line-height: 1rem;letter-spacing: 0;margin-bottom: 0">
                    WELCOME TO RUN NIKE DIRECT RUN!<br>
                    YOUR GROUP NAME IS :
                </p>
                <br><?php echo $teamName;?></h1>
            <br><br>
        <?php }?>
    </div>
    <?php if($is_input < 0){?>
    <a class="submit btn-style">提交<br><span>SUBMIT</span></a>       
    <?php }elseif($is_input == 0){?>
        <a class="edit btn-style">编辑<br><span>EDIT</span></a>
        <a href="./confirm.php?nominate=1" class="btn-style" style="margin-top: 2rem;">进入首页<br><span>ENTER HOME PAGE</span></a>
    <?php }elseif($is_input == 1){?>
     <a href="./confirm.php?nominate=1" class="btn-style">进入首页<br><span>ENTER HOME PAGE</span></a>
    <?php }?>
    <aside class="confirm" style="display: none">
        <div class="box">
            <p>一个队伍只有一次修改队名机会，<br>确定提交吗？<br>
            </p>
            <p style="font-family: 'fornike365';font-size: 0.7rem;font-weight:100;line-height: 1rem;margin-top: 0.7rem">YOU CAN MODIFY YOUR GROUP NAME ONLY ONCE. ARE YOU SURE YOU WANT TO CHANGE IT?</p>
            <div class="btn-box">
                <a class="btn-style sure">确定<br><span>CONFIRM</span></a>
                <a class="btn-style cancel">取消<br><span>CANCEL</span></a>
            </div>
        </div>
    </aside>
    <aside class="wrong-warn" style="display: none">
        <div class="dialog">
            <div class="dialog__hd"><strong class="dialog__title">提示！</strong></div>
            <div class="dialog__bd">请输入5个以内的汉字(不包含符号)或者10个以内的英文字符(不包含符号)</div>
            <div class="dialog__ft"><a  class="dialog__btn ">确定</a></div>
        </div>
    </aside>
</div>
<script>
    var str;
    var flag = true;
    function myStrlen(str){
        var len = 0;
        for (var i=0; i<str.length; i++) {
            var c = str.charCodeAt(i);
            //单字节加1
            if ((c >= 0x0001 && c <= 0x007e) || (0xff60<=c && c<=0xff9f)) {
                len++;
            }
            else {
                len+=2;
            }
        }
        return len;
    }
    function get(s) {
//        alert(str);
        str = s;
    }
    $('.cancel').on('touchstart',function () {
        str = '';
    })
    $('body').on('touchstart','.submit',function () {
        console.log(123);
        var ret=/^[\u4e00-\u9fa5\u0020_a-zA-Z0-9_ ]{1,10}$/;  //必须是汉字
//        var ret1=/^[a-zA-Z]{1,10}$/;        //是字母
//        console.log(ret.test(str));
        if(ret.test(str)){
            var length = myStrlen(str);
            console.log(length);

            if(length<=10 && length>=1){
                $('.confirm').bPopup({
                    closeClass:'cancel'
                })
            }else {
                $('.wrong-warn').bPopup({
                    closeClass:'dialog__btn'
                });
            }
        }else {
            $('.wrong-warn').bPopup({
                closeClass:'dialog__btn'
            });
        }
    })
    $('.edit').on('touchstart',function () {
        $('.join-input').html("<form class='form-nominate'> <h1>欢迎加入【我是跑手】<br>作为团队队长<br>请为你的团队命名吧<p style=font-size:1.0rem;line-height:1rem;letter-spacing:0> WELCOME TO RUN NIKE DIRECT RUN! AS TEAM CAPTAIN, PLEASE FILL IN YOUR GROUP NAME. </p></h1> " +
            "<div class='team-code'> " +
            "<span class='input-icon'></span>"+
            "<input class='edit_input' type='text'  name='name' maxlength='10' oninput='get(this.value)'>"+
            "<span style='font-size: 1.2rem;color:#fa6710;font-weight: bold;font-style: oblique'>队</span>"+
            "</div> <p>5个汉字/10字符以内<br><small style=letter-spacing:0;> LIMIT OF 5 CHARACTERS/10 BYTES</small></p> </form>");
        $('.edit').html('提交<br><span>SUBMIT</span>');
        $('.edit').removeClass('edit');
        $(this).addClass('submit');
        if(flag){
            flag = false;
            return false;

        }
    });
    $('.sure').on('touchstart',function () {
//        console.log(123);
        var ret=/^[\u4e00-\u9fa5\u0020_a-zA-Z0-9_]{1,10}$/;  //必须是汉字或英文
        console.log(ret.test(str));
        if (ret.test(str)){
            var length = myStrlen(str);
            if(length<=10 && length>=1) {
                $.ajax({
                    method: 'GET',
                    url: './apiNominateTeam.php',
                    data: {
                        'teamName': str,
                        'teamId': '<?php echo $team_id;?>',
                        'token': '<?php echo $token;?>',
                        'unixtime': '<?php echo $unixtime;?>',
                        'openid': '<?php echo $openid;?>'
                    },
                    dataType: 'JSON',
                    success: function (data) {
                        if (data['errmsg'] == 'success') {
                            alert('队伍名称成功命名为：' + str);
                            location.href = './nominate.php?a=<?php echo rand();?>';
                        } else {
                            alert('失败');
                        }
//                    location.href='./main.php';
                    },
                    error: function (data) {
                        alert('失败');
                    }

                });
            }
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
foreach ($logArr as $insert_key => $insert_value)
{
    $insertkeysql .= $dot . $insert_key;
    $insertvaluesql .= $dot . '\'' . $insert_value . '\'';
    $dot = ', ';
}
$sql1 = 'insert into dtc_api_logs (' . $insertkeysql . ') values (' . $insertvaluesql .
    ')';
mysql_query($sql1, $conn);

?>
