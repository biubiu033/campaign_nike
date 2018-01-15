<?php

require_once "./header.php";
$conn = connect_to_db();

$apiStartTime = getMicrotime();
$apiUrl = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
$loginIP = getClientIP();
$type = 'dtc_confirmJoin';

$headerLocation = ''; //跳转地址
$sql = "select * from dtc_join_user where openid = '$openid';";
$result = mysql_query($sql, $conn);
if (is_resource($result) && mysql_num_rows($result) != 0)
{
    $joinUserRow = mysql_fetch_assoc($result);
    //看用户是否已经加入团队并写了相关信息
    $teamId = $joinUserRow['teamId'];
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
                if ($row1['is_input'] < 0 & !isset($_GET['nominate']))
                {
                    //团队名字没起好，且不是从团队页面跳回来的，则跳到团队起名字页面
                    $headerLocation = 'nominate.php';
                }elseif ($joinUserRow['is_confirm'] == 1)
                {
                    //未绑定去绑定
                    if($_SESSION['userInfo']['isBindNike'] != 1)
                    {
                        $headerLocation = 'bind.php';
                    }
                    else
                    {
                        //如果已经确认开启任务则跳到首页
                        $headerLocation = 'main.php';
                    }
                }
            }
        } else
        {
            //队员看是否由队长审核通过
            if ($joinUserRow['is_allow'] == 1 & $_SESSION['userInfo']['isBindNike'] == 1)
            {
                if ($joinUserRow['is_confirm'] == 0)
                {
                    //            $headerLocation = 'confirm.php';
                } else
                {
                    //如果已确认开启，则跳到main.php
                    $headerLocation = 'main.php';
                }
                //已确认开启且绑定过nike，则跳到main.php
                //                $headerLocation = 'main.php';
            } else
            {
                //如果用户确认开启，但是未绑定，则跳到bind.php
                if($joinUserRow['is_confirm'] == 1 & $_SESSION['userInfo']['isBindNike'] != 1)
                {
                    $headerLocation = 'bind.php';
                }
            }
        }
    } else
    {
        //如果是被队长拒绝的话，则跳到confirm.php
        if ($joinUserRow['is_allow'] == -1)
        {
            //            $headerLocation = 'confirm.php';
            $deleteCaptainOpenid = $joinUserRow['delete_captain'];
            $query = "SELECT fullname FROM dtc_join_user WHERE openid = '$deleteCaptainOpenid'";
            $resultDelete = mysql_query($query, $conn);
            $rejectCaptain = mysql_fetch_assoc($resultDelete)['fullname'];
            //            echo $query;
        } else
        {
            $headerLocation = 'join-choose.html';
        }
    }
} else
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
    $sql1 = 'insert into dtc_api_logs (' . $insertkeysql . ') values (' . $insertvaluesql .
        ')';
    mysql_query($sql1, $conn);

    header("Location:$headerLocation");

    exit();
}
//if ($_SESSION['userInfo']['isBindNike'] == 1) {
//    //已经绑定,跳转
//    $agree_url = "./main.php";
//}else{
//    $agree_url = "./bind.php";//没有绑定则跳转至待绑定页面
//}

/****页面数据***/
$ownOpenid = $openid;
$sql3 = "select a.id,a.teamId,a.is_captain,b.teamName,b.department,b.teamNameEN,a.is_allow
          from dtc_join_user a LEFT JOIN dtc_team b 
          ON a.teamId = b.teamId
          WHERE  a.openid='$openid' ";
$result3 = mysql_query($sql3, $conn);
$teamNameEN = '';
while ($row3 = mysql_fetch_assoc($result3))
{
    $teamNameEN .= $row3['teamNameEN'] . ' / ';
    $teamName = $row3['teamName'];
    $isAllow = $row3['is_allow'];
}
$teamNameEN = trim($teamNameEN, ' / ');

//取队长名字，应从新表格取，且可能有多个队长
$query_captain = "SELECT `name` FROM dtc_team_captain WHERE teamId = '$teamId';";
$result_captain = mysql_query($query_captain, $conn);
$captainName = '';
while($row4 = mysql_fetch_assoc($result_captain))
{
    $captainName .= $row4['name']. ' / ';
}
$captainName = trim($captainName, ' / ');

?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
        <title>里享</title>
        <link rel="stylesheet" href="css/reset.css">
        <!--引入自己的css-->
        <link rel="stylesheet" href="css/confirm.css?a=13">
        <!--引入自己的Js-->
        <script src="js/responsive.js"></script>
        <style>
            .__span{
                font-family: 'fornike365';
                font-size: 0.6rem;
                letter-spacing: 0;
            }
            .__p{
                font-family: 'fornike365';
                color: #FFFFFF;
                font-size: 1.3rem;
                font-style: normal;
                margin: 2rem 0 0;
            }
            .bt-back{
                display: block;
                background: rgba(255,255,255,0.9);
                /* background-size: contain; */
                height: 3.5rem;
                border-radius: 12px;
                border: 2px solid #ea5012;
                box-shadow: 3px 2px 1px #ea5012;
                line-height: 1rem;
                width: 12rem;
                font-weight: bold;
                font-size: 1.3rem;
                text-align: center;
                color: #fa6b0e;
                font-style: oblique;
                box-sizing: border-box;
                padding-top: 0.7rem;
            }
        </style>
    </head>
    <body>
    <div class="wrap confirm" name="confirm">
        <div class="title"></div>
        <?php

        if ($isAllow == 1)
        {

            ?>
            <div class="confirm-word">
                <div class="applyJoin">
                    你已加入<br><span>YOU HAVE JOINED TEAM </span>
                </div>
                <div class="teamName">
                    <div style="margin: 0.8rem 2rem;;border-bottom: 1px solid white;">
                        <?php

                        echo substr($teamName,-1)=='队'?$teamName:$teamName.' 队';


                        ?>
                    </div>
                    <!--            <p class="__p" style="margin:0 0;">来自<span  class="__span"></span>:PROCUREMENT</p>-->

                    <p class="__p" style="margin:0 0;">  <?php

                        echo $teamNameEN;

                        ?></p>
                </div>
                <div class="participateWord">
                    开启“我是跑手”挑战赛<br><span>TO KICK START“ RUN,NIKE DIRECT! RUN!”</span>
                </div>
            </div>
            <div class="confirm-bt">
                <a class="confirm-bt-sure">确定<br><span>CONFIRM</span></a>
                <a class="confirm-bt-cancel" href="">取消<br><span>CANCEL</span></a>
            </div>
            <?php

        }

        ?>
        <?php

        if ($isAllow == 0)
        {

            ?>
            <div class="confirm-word">
                <div class="applyJoin">
                    你已申请加入<br><span>YOU HAVE APPLIED FOR </span>
                </div>
                <div class="teamName">
                    <div style="margin: 0.8rem 2rem;;border-bottom: 1px solid white;">
                        <?php

                        echo mb_substr($teamName,-1)=='队'?$teamName:$teamName.' 队';

                        ?>
                    </div>
                    <p class="__p" style="margin:0 0;">  <?php

                        echo $teamNameEN;

                        ?></p>

                </div>
                <div class="participateWord">
                    请等待队长审核<br><span style="font-size: 0.8rem">PLEASE WAIT FOR CONFIRMATION</span><br>
                    <p class="__p">如长时间未得回应，请联系队长：<br><span  style="font-size: 0.8rem">IF YOU HAVE NOT RECEIVED  RESPONSE, PLEASE CONTACT CAPTAIN:</span><br><br><?php

                        echo $captainName;

                        ?></p>
                </div>
            </div>
            <?php

        }

        ?>
        <?php

        if ($isAllow == -1)
        {

        ?>
        <div class="confirm-word">

            <div class="participateWord" style="line-height: 1.8rem;margin-top: 5rem;width: 18rem">
                你的申请已被拒绝<br>
                请返回申请其他队伍<br><span style="font-size: 0.8rem;display: block;line-height: 1rem;">YOUR APPLICATION HAS BEEN REJECTED, PLEASE CHOOSE THE RIGHT TEAM.</span><br>
                <p class="__p" >如有疑问，请联系队长：<br><span  style="font-size: 0.8rem;display: block;line-height: 1rem;">ANY QUESTION PLEASE CONTACT CAPTAIN:</span><br><?php

                    echo $rejectCaptain;

                    ?></p>
            </div>
        </div>
        <div class="confirm-bt" style="margin-top: 2rem;">
            <a class="bt-back" href="join-choose.html?is_allow=-1">返回<br><span>BACK</span></a>
        </div>
    </div>
    <?php

    }

    ?>

    <!--参赛须知弹窗-->
    <aside class="popup-disclaimer" style="margin-top: 2rem;">
        <div class="popup-word">
            <div class="popup-title">参跑须知
                <div class="line"></div>
                <span>DISCLAIMER</span>
            </div>
            <div class="details">
                <div class="panel1" id="demo">
                    <div class="_panel-box" style="margin-top: 1rem">
                        <div class="item">1.本人自愿参加由耐克商业（中国）有限公司（ “主办方”）Nike Direct发起的我是跑手活动（“活动”)。<br>
                            <span>I am voluntarily participating in the Run, Nike Direct! Run!（“Event”） initiated by the Nike Direct team of Nike Commercial (China) Co. Ltd.(“NIKE”).</span>　
                        </div>
                        <div class="item">2.本人确保身体健康、不是先天性心脏病和风湿性心脏病患者/高血压和脑血管疾病患者/心肌炎和其它心脏病患者/冠状动脉病患者和严重心率不齐者/糖尿病患者等其他不适合运动的疾病患者，在身体上和精神上都具备参加活动的能力。<br>
                            <span>I declare that I have no health conditions that would prevent me from participating in physical activities. I am aware that participant having congenital heart and rheumatic heart disease, hypertension and cerebrovascular disease, myocarditis and other heart disease, coronary artery disease and serious arrhythmia person, diabetes or other diseases may not be suitable for taking part in Event that requires sports activities. I am physically and psychologically capable of participating in the Campaign.</span>
                        </div>
                        <div class="item">
                            3.本人全面理解参加活动可能出现的风险。本人愿意自行承担因参加活动而可能存在的所有风险，且在法律允许的范围内，本人代表本人及本人的继承人、信托人、承保人、继任人和受让人在此全面、永久地免除主办方、主办方的关联公司和子公司及其各自的管理人员、董事、股东、员工、代理人、代表、志愿者、工作人员等因本人遭受的与活动有关的人身伤害或财产损失而提起的一切索赔或诉讼中的责任，无论该等事件是否由免责方的疏忽引起的还是另有原因（包括但不限于因过度劳累、脱水、中暑、自己跌倒或任何第三方造成的事故）。<br>
                            <span> I am fully aware of the potential injuries that may result from participating the Event. I hereby voluntarily acknowledge and accept, at my own cost, to assume full responsibility for my participation in the Event. I, for myself and on behalf of my heirs, estate, insurers, successors and assigns, hereby fully and forever release and discharge NIKE, and the affiliates and subsidiaries of NIKE, their respective officers, directors, shareholders, employees, agents, distributors, representatives, contractors, successors, assigns, and insurers, volunteers, and staff used in connection with the Event from any and all claims or causes of action I may have for damages for personal or bodily injury, disability, death, loss or damage to person or property relating in any way to the Event, whether arising from the negligence of any or all of the Released Parties or otherwise( including but not limited to exhaustion, dehydration, sunstroke, tripping over or accidents caused by a third party), to the fullest extent permitted by law.
                        </span>
                        </div>
                        <div class="item">4.本人已认真阅读且全面理解并完全同意以上声明书的内容。本人自愿签署本声明书，未受到任何引诱或胁迫。<br>
                            <span>I fully understand and accept all terms of this disclaimer. I sign this Disclaimer freely and voluntarily, without any inducement or coercion.</span>
                        </div>
                    </div>
                </div>
            </div>
            <span>
            <a class="agree-btn disagree" href="#" >
                不同意<br>
                <span>NOT AGREE</span>
            </a>
            <a class="agree-btn agree" href="#" >
                我同意<br>
                <span>I AGREE</span>
            </a>
        </span>
        </div>
    </aside>

    <script src="js/jquery-2.1.3.min.js"></script>
    <script src="js/xb_scroll.js"></script>
    <script src="js/jquery.bpopup.js"></script>
    <script>
        $('.confirm-bt-sure').click(function () {
            $('.popup-disclaimer').bPopup({
                positionStyle: 'fixed',
                closeClass: "close"
            });
        });
        $('.agree').click(function () {

            $.ajax({
                url: "./apiJoinDtc.php?confirm=1",
                type: "GET",
                async: true,
                beforeSend: function () {
//                $.showLoading('正在请求中..');
                },
                complete: function () {
//                $.hideLoading();
                },
                success: function ($data) {
                    window.location.reload();
                }
            });
        })
        $('.disagree,.confirm-bt-cancel').click(function () {

            $.ajax({
                url: "./apiJoinDtc.php?confirm=0",
                type: "GET",
                async: true,
                beforeSend: function () {
//                $.showLoading('正在请求中..');
                },
                complete: function () {
//                $.hideLoading();
                },
                success: function ($data) {
                    window.location.reload();
                }
            });
        })

    </script>
    <?php

    include "share_dtc.php";

    ?>

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