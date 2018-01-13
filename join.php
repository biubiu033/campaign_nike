<?php
require_once "./header.php";
$conn = connect_to_db();

$apiStartTime = getMicrotime();
$apiUrl = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
$loginIP = getClientIP();
$pageType = 'join_dtc';

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
                if ($row1['is_input'] < 1)
                {
                    //团队名字没起好，跳到团队起名字页面
                    $headerLocation = 'nominate.php';
                }else{
                    $headerLocation = 'main.php';  //队长进来干什么
                }
            }
        }
        else
        {
            //队员看是否由队长审核通过
            if ($joinUserRow['is_allow'] == 1 && $_SESSION['userInfo']['isBindNike'] == 1)
            {
                //已确认开启且绑定过nike，则跳到main.php
                $headerLocation = 'main.php';
            } elseif($joinUserRow['is_allow'] != -1)
            {
                //其他情况则跳转到等待队长审核页面
                $headerLocation = 'confirm.php';
            }
        }
    } else
    {
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
        'type' => $pageType,
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

$type = isset($_GET['type']) ? $_GET['type'] : 1;


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
    <!-- head 中 -->
    <link rel="stylesheet" href="//cdn.bootcss.com/weui/1.1.1/style/weui.min.css">
    <link rel="stylesheet" href="//cdn.bootcss.com/jquery-weui/1.0.1/css/jquery-weui.min.css">
    <!--    引入自己的css-->
    <link rel="stylesheet" href="css/reset.css?a=456">
    <link rel="stylesheet" href="css/main.css?a=123"/>
    <!--引入自己的Js-->
    <script src="js/responsive.js"></script>

    <!--引用Jquery-->
    <title>里享</title>
    <style>
/*        .join[name="join"] {
            background: url(./img/bg.png) no-repeat;
            background-size: cover;
            min-height: 44.3rem;
            font-family: "Microsoft YaHei", sans-serif;
            box-sizing: border-box;
            padding: 1rem 2.5rem 1rem;
            overflow-x: hidden;
        }*/

/*        .join[name="join"] .title {
            background: url(./img/join-title.png) no-repeat;
            background-size: contain;
            background-position: center;
            margin: 0 auto;
            height: 7rem;
            width: 23rem;
            box-sizing: border-box;
        }*/

/*        .join[name="join"] .join-input {
            background: rgba(255, 255, 255, 0.8);
            border: 2px solid #fa6810;
            box-shadow: 4px 4px #fa6810;
            margin: 0.6rem auto;
            box-sizing: border-box;
            padding: 1rem 2rem 0.5rem;
            box-sizing: border-box;
        }*/
/*
        .join[name="join"] .team {
            margin-bottom: 1rem;
            position: relative;
        }*/

        .join[name="join"] .team input:disabled {
            box-sizing: border-box;
            background-color: #fef1e1;
            color: #000;
            opacity: 1;
        }

       /* .join[name="join"] .team ul {
            display: none;
            position: absolute;
            background: #fa6810;
            color: #fff;
            width: 100%;
            left: 0;
            bottom: -5.2rem;
            padding: 0.5rem 1.5rem;
            box-sizing: border-box;
            z-index: 1;
            font-size: 0.8rem;
        }*/

/*        .join[name="join"] .team ul li {
            padding: 0.2rem 0;
        }*/

 /*       .join[name="join"] .team ul li:not(:last-child) {
            border-bottom: 1px solid #fff;
        }*/

/*        .join[name="join"] h1 {
            text-align: center;
            font-size: 1rem;
            font-weight: bold;
            line-height: 1rem;
            font-style: oblique;
            color: #fa6b0e;
        }*/

/*        .join[name="join"] h1 span {
            font-size: 0.7rem;
            font-family: futurabt;
            font-weight: 100;
        }*/

       /* .join[name="join"] .team-code, .employee-code {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(255, 255, 255, 0.4);
            border: 2px solid #fa6810;
            padding: 0.65rem;
            margin-top: 0.5rem;
            box-sizing: border-box;
        }*/

/*        .join[name="join"] input {
            display: block;
            border: none;
            background: none;
            outline: none;
            font-size: 1.0rem;
            !*text-align: center;*!
            width: 100%;
            box-sizing: border-box;
        }*/

   /*     ::-webkit-input-placeholder { !* WebKit browsers *!
            color: #fa6710;
            opacity: 0.5;
            font-size: 1rem;
        }*/
/*        .input-icon:before {
            content: '';
            !*display: inline-block;*!
            background: url(./img/input-icon.png) center center no-repeat;
            background-size: contain;
            width: 1.5rem;
            height: 1.5rem;
            margin: 0 0.4rem -0.28rem 0;
        }*/

/*        .down-icon:after {
            content: '';
            display: block;
            background: url(./img/icon-down.png) center center no-repeat;
            background-size: contain;
            width: 1rem;
            height: 1rem;
        }

        .join[name="join"] .warn {
            text-align: center;
            color: #fa6b0e;
            font-size: 0.8rem;
            margin-top: 0.2rem;
        }*/

       /* .join[name="join"] .compete {
            display: block;
            background: rgba(255, 255, 255, 0.8);
            font-weight: bold;
            font-size: 1.2rem;
            line-height: 1rem;
            font-family: "Microsoft YaHei", sans-serif;
            margin: 0 auto;
            padding: 0.5rem 0 0.1rem;
            width: 10rem;
            border-radius: 10px;
            border: 2px solid #e1520a;
            box-shadow: 2px 2px #e1520a;
            text-align: center;
            color: #fa6b0e;
            font-style: oblique;
        }*/

 /*       .join[name="join"] .compete span {
            font-size: 0.8rem;
            font-family: futurabt;
            !*   font-weight: 100;*!
        }*/

        .wrong {
            font-size: 0.8rem;
            color: red;
            display: block;
            flex: none;
        }
        .warn-pop{
            background-color:#ffffff;
            display: none;
            margin: 0 auto;
            width: 23rem;
            border-radius: 8px;
            border: 2px solid #f05f06;
            color: #f05f06;
            text-align: center;
            /* height: 6rem; */
            font-size: 1.1rem;
            box-sizing: border-box;
            padding: 1rem;
        }
        .close-bt{
            display: block;
            width: 1.4rem;
            height: 1.4rem;
            background: url(./img/close-btn2.png)no-repeat;
            background-size: contain;
            border-bottom: none;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
<div class="wrap join" name="join">
    <div class="title"></div>
    <div class="join-input">
        <form class="form_join">
            <input name="type" value="<?php echo $type; ?>" style="display: none">
            <?php if ($type == 2) { ?>
                <div class="team">
                    <h1>请选择所在团队<br><span>TEAM</span></h1>
                    <div class="team-code select">
                        <span class="input-icon"></span>
                        <input type="tel" placeholder="请选择所在团队" onfocus="javascript:this.blur()" name="department">
                        <span class="down-icon"></span>
                        <ul class="select_ul">
                            <?php
                            $sql = "select department from dtc_team where type='DTC办公室' ORDER by department";
                            $ret = mysql_query($sql, $conn);
                            while ($row = mysql_fetch_assoc($ret)) {
                                echo "<li>" . $row['department'] . "</li>";
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            <?php } else if ($type == 1) {
                ?>
                <div class="team">
                    <h1>请输入商店号<!--（示例：1234）--><br><span>STORE CODE <!--(Example:1234)--></span></h1>
                    <div class="team-code select">
                        <span class="input-icon"></span>
                        <input placeholder="请输入商店号" name="department" maxlength="4" type="tel">
                    </div>

                </div>
            <?php } else if ($type == 3) { ?>
                <div class="team">
                    <h1>请选择所在的部门<br><span>DIMENSION/FUNCTION</span></h1>
                    <div class="team-code select">
                        <span class="input-icon"></span>
                        <input type="text" placeholder="请选择所在团队" onfocus="javascript:this.blur()" name="department">
                        <span class="down-icon"></span>
                        <ul class="select_ul">
                            <?php
                            $sql = "select department from dtc_team where type='客户服务中心' ORDER by department";
                            $ret = mysql_query($sql, $conn);
                            while ($row = mysql_fetch_assoc($ret)) {
                                echo "<li>" . $row['department'] . "</li>";
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            <?php } ?>
            <div class="team">
                <h1>请输入英文名或中文拼音<br><span>Please input your name</span></h1>
                <div class="team-code">
                    <span class="input-icon"></span>
                    <input type="text" placeholder="请输入英文名或中文拼音" name="name">
                </div>
                <span class="error"></span>
            </div>
            <div class="team">
                <h1>请输入你的员工号<span
                        style="font-weight: bold;">（商店兼职请输入0000）</span><br><span>EMPLOY NUMBER (0000 FOR STORE PT)</span>
                </h1>
                <div class="team-code">
                    <span class="input-icon"></span>
                    <input type="tel" placeholder="请输入您的员工号" name="employ" maxlength="10">
                </div>
                <span class="error"></span>
            </div>
            <div class="team">
                <h1>请输入手机号<br><span>MOBILE NUMBER</span></h1>
                <div class="team-code">
                    <span class="input-icon"></span>
                    <input type="tel" placeholder="输入手机号" name="phone" maxlength="11">
                </div>
                <span class="error"></span>
            </div>
        </form>
    </div>
    <a class="compete">确认<br><span>CONFIRM</span></a>
</div>
<div class="warn-pop">
    <a class="close-bt"></a>
    <p>哈哈哈</p>
</div>
</body>
<script src="js/jquery-2.1.3.min.js"></script>
<!-- body 最后 -->
<script src="//cdn.bootcss.com/jquery/1.11.0/jquery.min.js"></script>
<script src="//cdn.bootcss.com/jquery-weui/1.0.1/js/jquery-weui.min.js"></script>
<script src="js/jquery.bpopup.js"></script>


<script src="js/verify.js"></script>
<script>
    (function () {
        $(".select").on("click", function () {
            $(".select_ul").toggle();

        });
        $(".select_ul li").on("click", function () {
            $("[name='department']").val($(this).html());
        });


        $('form').verify({
            fields: {
                //选中名字字段，设置校验规则
                'input[name="phone"]': {
                    //错误提示信息
                    message: '*不能为空 Invalid Phone Number',
                    //校验规则，返回布尔值，true则成功，false则失败，可在此回调函数中插入您的业务逻辑，最终返回bool值就好，极方便
                    test: function (val) {
                        return val.length > 0;
                    }
                },
                'input[name="department"]': {
                    //错误提示信息
                    message: '*不能为空 Invalid Store Code',
                    //校验规则，返回布尔值，true则成功，false则失败，可在此回调函数中插入您的业务逻辑，最终返回bool值就好，极方便
                    test: function (val) {
//                                var reg = /^\w{9}$/;
                        return val.length > 0;
                    }
                },
                'input[name="employ"]': {
                    //错误提示信息
                    message: '*不能为空 Invalid Employ Number',
                    //校验规则，返回布尔值，true则成功，false则失败，可在此回调函数中插入您的业务逻辑，最终返回bool值就好，极方便
                    test: function (val) {
                        return val.length > 0;
                    }
                },
                'input[name="name"]': {
                    //错误提示信息
                    message: '*请填写有效姓名 Invalid Name',
                    //校验规则，返回布尔值，true则成功，false则失败，可在此回调函数中插入您的业务逻辑，最终返回bool值就好，极方便
                    test: function (val) {
                        var reg = /^[A-z ]+$|^[\u4E00-\u9FA5]+$/;
                        return reg.test(val);
                    }
                }
            },
            // 错误模板，设置装载错误信息的容器，插入上面设置的错误提示信息
            errorTemplate: function (error) {
                return $('<div class="wrong">' + error.message + ' </div>');
            },
            //表单提交按钮，若未设置，则为默认的form.submit;
            submitButton: '.compete',
            success: function () {
                var phone = $('input[name="phone"]').val();
                var formData = $('.form_join').serializeArray();
                $.ajax({
                    url: "./apiJoinDtc.php",
                    type: "GET",
                    data: formData,
                    dataType: "json",
                    async: true,
                    beforeSend: function () {
                        $.showLoading('正在请求中..');
//                    $('.compete')
                    },
                    complete: function () {
                        $.hideLoading();
                    },
                    success: function (data) {

                        if (data.errno === -1) {
//                            $.alert(data.err);
                            $(".warn-pop>p").html(data.err);
                            $('.warn-pop').bPopup({
                                closeClass:'close-bt',
                                positionStyle:'fixed'
                            });
                        } else {
                            window.location.reload();
                        }
                    }
                });

            }

        });


    })();
</script>
<?php include "share_dtc.php"; ?>
</html>

<?php
//记录页面请求日志
$apiEndTime = getMicrotime();
$fetchTime = intval(($apiEndTime - $apiStartTime) * 1000);
$logArr = array(
    'openid' => "$openid",
    'type' => $pageType,
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

?>
