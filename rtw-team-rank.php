<?php

//ini_set("display_errors", "On");
//error_reporting(E_ALL | E_STRICT);

require_once "./header.php";
$conn = connect_to_db();

$apiStartTime = getMicrotime();
$apiUrl = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
$loginIP = getClientIP();
$pagetype = 'team_member';

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
        }else{
            if(isset($_GET['type'])){
                $date=$_GET['type'];
            }else{
                $date=1;
            } //任务默认为1
            $query = "SELECT * FROM dtc_taskone_marathon WHERE openid='$openid' LIMIT 1";
            $result = mysql_query($query,$conn);
            if(is_resource($result) && mysql_num_rows($result) == 0){
//                $headerLocation = 'mls-choose.php';
            }
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

$phpfile = isset($phpfile) ? $phpfile : "campaign_nike/running/dtc/rtw-invite.php?ptOpenid=".$openid;

//task one
$task_one = array();

$query = "SELECT src_openid,count(1) num FROM dtc_rtw_join_user WHERE is_count='1' GROUP BY src_openid ORDER BY count(1) DESC ";
$result = mysql_query($query,$conn);
while ($row = mysql_fetch_assoc($result)){
    $team_openid = $row['src_openid'];
    $team_num = $row['num'];
    if($team_openid!='0'){
        $task_one[$team_openid]['num'] = $team_num;
    }
}

$query = "SELECT a.openid,a.full_name,a.team_id,b.teamName FROM dtc_rtw_join_user a LEFT JOIN dtc_team b 
          ON a.team_id = b.teamId
          WHERE a.src_openid='0' GROUP BY openid";
$result = mysql_query($query,$conn);
while ($row = mysql_fetch_assoc($result)){
    $team_openid = $row['openid'];
    $fullName = $row['full_name'];
    $teamId = $row['team_id'];
    $teamName = $row['teamName'];

    $task_one[$team_openid]['teamOpenid'] = $team_openid;
    $task_one[$team_openid]['fullName'] = $fullName;
    $task_one[$team_openid]['teamId'] = $teamId;
    $task_one[$team_openid]['teamName'] = $teamName;

    if(!isset($task_one[$team_openid]['num'])){
        $task_one[$team_openid]['num'] = 0;
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
        <link rel="stylesheet" href="css/task-rank.css">
        <!--引入自己的Js-->
        <script src="js/responsive.js"></script>
        <!--引用Jquery-->
        <script src="js/jquery-2.1.4.js"></script>
        <script src="js/jquery-2.1.3.min.js"></script>
        <script src="js/template.js"></script>
        <script src="js/jquery.bpopup.js"></script>
        <title>里享</title>
        <style>
            .teamname1{
                font-size: 0.8rem;
                width: 6rem;
                text-decoration: underline;
            }
            .btn-style{
                display: none;
                padding-top: 0.4rem;
                background: rgba(255, 255, 255, 0.8);
                box-shadow: 2px 3px 0 #b83a0f;
                border: 2px solid #ea5012;
                width: 9rem;
                height: 2.2rem;
                line-height: 1.0rem;
                border-radius: 8px;
                text-align: center;
                color: #fc5219;
                font-size: 1.0rem;
                font-family: 'fornike365';
                margin: 1.5rem auto 0;
            }
            .btn-style span{
                color: #fc5219!important;
                font-size: 0.65rem!important;
            }
            .unopen {
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
        <a class="home" href="rtw-task-two.php"><img src="./img/back-icon.png" width="30%"></a>
        <a class="prize" href="main.php"><img src="./img/home-icon-1.png" width="60%"></a>
        <div style="height: 2rem"></div>
        <div class="box">
            <div class="tab_1" >
                <a class="item_1  brl <?php if($date==1){?>active<?php } ?>" data-mid="1"><p class="li-text">TASK1</p></a>
                <a class="item_1 <?php if($date==2){?>active<?php } ?>" data-mid="2"><p class="li-text">TASK2</p></a>
                <a class="item_1 <?php if($date==3){?>active<?php } ?>" data-mid="3"><p class="li-text">TASK3</p></a>
                <a class="item_1 brr <?php if($date==4){?>active<?php } ?> " data-mid="4"><p class="li-text">TASK4</p></a>
            </div>
            <div class="tab-title <?php if($date==1){?>active-1<?php } ?>" data-t="1">
                <div class="tab " >
                    <div class="item" style="width: 15rem;">姓名<br><span class="__span">NAME</span></div>
                    <div class="item" >团队<br><span class="__span">TEAM</span></div>
                    <div class="item" >跑团人数<br><span class="__span">TEAM SIZE</span></div>
                </div>
                <ul class="list" >
                    <?php $count = 0;
                    foreach ($task_one as $item_row) {
//                        if ($item_row['teamId']!='240')
                        $count = $count + 1;
                        ?>
                        <li class="list-li grey-1" id="<?php echo $item_row['id']?>">

                            <div class="_div">
                                <span class="_span" style=" text-align: center;"><?php
                                    switch ($count){
                                        case 1:echo "<img src='img/icon-no1.png'width='50%'>";break;
                                        case 2:echo "<img src='img/icon-no2.png'width='50%'>";break;
                                        case 3:echo "<img src='img/icon-no3.png'width='50%'>";break;
                                        default: echo "NO.".$count;
                                    }
                                    ?></span>
                                <p class="_a" style="width: 6rem;margin-left: 0rem;"><?php echo $item_row['fullName']; ?></p>
                                <p class="teamname" style="margin-left: -2rem" item-id="<?php echo $item_row['teamId'] ?>"><?php echo $item_row['teamName']; ?></p>
                                <p class="distance"><?php echo $item_row['num']; ?></p>
                            </div>
                        </li>
                    <?php } ?>
                </ul>
            </div>
            <div class="tab-title <?php if($date==2){?>active-1<?php } ?>" data-t="2">
                <p class="unopen">尚未开启<br><span style="font-size: 1.6rem">COMING SOON</span></p>
                <!--<div class="tab " >
                    <div class="item" style="width: 13rem;">姓名<br><span class="__span">NAME</span></div>
                    <div class="item" >团队<br><span class="__span">TEAM</span></div>ACTIVE RUNNERS/TEAM SIZE
                    <div class="item" style=" width: 22rem;">活跃人数/团队人数<br><span class="__span">ACTIVE RUNNERS/TEAM SIZE</span></div>
                </div>
                <ul class="list" >
                    <?php /*$count = 0;
                    foreach ($fiveArr as $item_row) {
                        $count = $count + 1;
                        */?>
                        <li class="list-li grey-1" id="<?php /*echo $item_row['id']*/?>">

                            <div class="_div">
                                <span class="_span" style=" text-align: center;"><?php
                /*                                    switch ($count){
                                                        case 1:echo "<img src='img/icon-no1.png'width='50%'>";break;
                                                        case 2:echo "<img src='img/icon-no2.png'width='50%'>";break;
                                                        case 3:echo "<img src='img/icon-no3.png'width='50%'>";break;
                                                        default: echo "NO.".$count;
                                                    }
                                                    */?></span>
                                <p class="_a" style="width: 6rem;margin-left: 0rem;"><?php /*echo $item_row['name']; */?></p>
                                <p class="teamname" style="margin-left: -2rem;"  item-id="<?php /*echo $item_row['t_id'] */?>"><?php /*echo $item_row['t_name']; */?></p>
                                <p class="distance ">10 / 48</p>
                            </div>
                        </li>
                    <?php /*} */?>
                </ul>-->
            </div>
            <div class=" tab-title <?php if($date==3){?>active-1<?php } ?>" data-t="3">
                <p class="unopen">尚未开启<br><span style="font-size: 1.6rem">COMING SOON</span></p>
                <!--  <div class="tab-title active-1" id="teamback">
                      <div class="tab " >
                          <div class="item" style="width: 11rem;">姓名<br><span class="__span">NAME</span></div>
                          <div class="item" >团队<br><span class="__span">TEAM</span></div>
                          <div class="item" style=" width: 21rem;">单日最高里程<br><span class="__span">HIGHEST  SINGLE-DAY MILEAGE</span></div>

                      </div>
                      <ul class="list" >
                          <?php /*$count = 0;
                          foreach ($fiveArr as $item_row) {
                              $count = $count + 1;
                              */?>
                              <li class="list-li grey-1" id="<?php /*echo $item_row['id']*/?>">

                                  <div class="_div">
                                  <span class="_span" style=" text-align: center;"><?php
                /*                                    switch ($count){
                                                        case 1:echo "<img src='img/icon-no1.png'width='50%'>";break;
                                                        case 2:echo "<img src='img/icon-no2.png'width='50%'>";break;
                                                        case 3:echo "<img src='img/icon-no3.png'width='50%'>";break;
                                                        default: echo "NO.".$count;
                                                    }
                                                    */?></span>
                                      <p class="_a" style="width: 6rem;margin-left: 0rem;"><?php /*echo $item_row['name']; */?></p>
                                      <p class="teamname" style="margin-left: -2rem;"  item-id="<?php /*echo $item_row['t_id'] */?>"><?php /*echo $item_row['t_name']; */?></p>
                                      <p class="distance"> 48</p>
                                  </div>
                              </li>
                          <?php /*} */?>
                      </ul>
                  </div>
                  <div class="tab-title " id="myteam">
                      <div class="tab " >
                          <div class="item" >日期<br><span class="__span">DATA</span></div>
                          <div class="item" >当日里程<br><span class="__span">MILEAGE</span></div>
                      </div>
                      <ul class="list" >
                          <?php /*$count = 0;
                          foreach ($fiveArr as $item_row) {
                              $count = $count + 1;
                              */?>
                              <li class="list-li">
                                  <div class="_div" style="justify-content: space-around;">
                                      <p class="distance grey-1"> 48</p>
                                      <p class="distance grey-1"> 48</p>
                                  </div>
                              </li>
                          <?php /*} */?>
                      </ul>
                  </div>-->
            </div>
            <div class="tab-title <?php if($date==4){?>active-1<?php } ?>" data-t="4">
                <p class="unopen">尚未开启<br><span style="font-size: 1.6rem">COMING SOON</span></p>
                <!--                <div class="tab " >
                    <div class="item" style="width: 9rem;">姓名<br><span class="__span">NAME</span></div>
                    <div class="item" style="width: 9rem;">团队<br><span class="__span">TEAM</span></div>
                    <div class="item" style="width: 15rem;">本周膨胀里程<br><span class="__span">EXPANDED MILEAGE</span></div>
                    <div class="item" style=" width: 10rem;">累积里程<br><span class="__span">TOTLE MILEAGE</span></div>
                </div>
                <ul class="list" >
                    <?php /*$count = 0;
                    foreach ($fiveArr as $item_row) {
                        $count = $count + 1;
                        */?>
                        <li class="list-li grey-1" id="<?php /*echo $item_row['id']*/?>">

                            <div class="_div">
                                <span class="_span" style=" text-align: center;"><?php
                /*                                    switch ($count){
                                                        case 1:echo "<img src='img/icon-no1.png'width='50%'>";break;
                                                        case 2:echo "<img src='img/icon-no2.png'width='50%'>";break;
                                                        case 3:echo "<img src='img/icon-no3.png'width='50%'>";break;
                                                        default: echo "NO.".$count;
                                                    }
                                                    */?></span>
                                <p class="_a" style="width: 6rem;margin-left: 0rem;"><?php /*echo $item_row['name']; */?></p>
                                <p class="teamname" style="margin-left: -2rem;"  item-id="<?php /*echo $item_row['t_id'] */?>"><?php /*echo $item_row['t_name']; */?></p>
                                <p class="distance"> 48</p>
                                <p class="distance"> 48</p>
                            </div>
                        </li>
                    <?php /*} */?>
                </ul>-->
            </div>
        </div>
        <!--        <a class="btn-style" id="myteamBtn">我的跑团<br><span>MY TEAM</span></a>-->
        <!--        <a class="btn-style tab-title" id="backBtn">返回<br><span>RETURN</span></a>-->
    </div>
    <script>
        $(".tab_1 .item_1").on("click", function () {
            $(this).addClass("active").siblings(".item_1").removeClass("active");
            $('[data-t=' + $(this).attr("data-mid") + ']').addClass("active-1").siblings(".tab-title").removeClass("active-1");
            //todo:任务三开启时记得打开！！

            /*if ($(this).attr('data-mid')=='3'){
             $('#myteamBtn').addClass('active-1');
             $('.box').css('height','30rem');
             $('.list').css('height','26rem');
             $('#teamback').addClass('active-1');
             $('#myteam').removeClass('active-1');
             }else {
             $('.btn-style').removeClass('active-1');
             $('.box').css('height','33rem');
             $('.list').css('height','29rem');
             }*/
        });
        $('#myteamBtn').on('click',function () {
            $('#myteam').addClass('active-1');

            $('#teamback').removeClass('active-1');
            $(this).removeClass('active-1');
            $('#backBtn').addClass('active-1');
        });
        $('#backBtn').on('click',function () {
            $('#teamback').addClass('active-1');
            $('#myteam').removeClass('active-1');
            $(this).removeClass('active-1');
            $('#myteamBtn').addClass('active-1');
        });
        /*        var wrap=$('.tab-title[data-t=2]');
         console.log(wrap);
         wrap.css('padding-top','13rem');
         wrap.html('<p class="text">尚未开启<br><span>COMING SOON</span></p>');*/
        /*   var wrap=$('.tab-title[data-t=3]');
         console.log(wrap);
         wrap.css('padding-top','13rem');
         wrap.html('<p class="text">尚未开启<br><span>COMING SOON</span></p>');
         var wrap=$('.tab-title[data-t=4]');
         console.log(wrap);
         wrap.css('padding-top','13rem');
         wrap.html('<p class="text">尚未开启<br><span>COMING SOON</span></p>');*/
        /*var wrap=$('.tab-title[data-t=1]');
         console.log(wrap);
         wrap.css('padding-top','13rem');
         wrap.html('<p class="text">尚未开启<br><span>COMING SOON</span></p>');*/
    </script>
    <script id="list_team" type="text/html">
        {{each retArray as row i}}
        <li class="team-member-list-item">
            {{ if row.is_captain==1}}
            <i class="captain"></i>
            {{else}}
            <i></i>
            {{/if}}
            {{ if i==0}}
            <span class="team-member-num">
           <img src='img/icon-no1.png'>
        </span>
            {{else if i==1}}
            <span class="team-member-num">
           <img src='img/icon-no2.png'>
        </span>
            {{else if i==2}}
            <span class="team-member-num">
           <img src='img/icon-no3.png' >
        </span>
            {{else }}
            <span class="team-member-num">
            NO.{{i+1}}
        </span>
            {{/if}}
            <div class="team-member-list-div"><a class="name-2">{{row.fullname}}</a></div>
            <p class="team-member-totle">{{row.longTotal}}</p></li>
        {{/each}}
    </script>
    <aside class="team-member">
        <div class="team-member-box">
            <div class="title"><a class="close"></a>
                <p id = 'pop-title'></p>
                <span id="teamNameEN" style="font-size: 1.2rem;font-family: 'fornike365';"></span>
            </div>
            <div class="tab-title-pop" >
                <p class="_p">团队成员<br><span class="__span">TEAM MEMBER</span></p>
                <p class="_p">公里数<br><span class="__span">KM</span></p>
            </div>
            <ul class="team-member-list" id="popup_teamList">

            </ul>
        </div>
    </aside>
    <script>
        template.helper('formatKm', function(total, type) {
            return sprint
        });
        //遮罩
        $('.list .teamname').on('click', function () {
            var team_name = $(this).find('.teamname').html();
            $('#pop-title').text(team_name);
            //先获取点击的是哪一个队伍的id
            var team_id = $(this).attr('item-id');
            //先从api获取数据
            $.ajax({
                url:"apiTeamList.php?teamID="+team_id,
                async: false,
                dateType: 'Json',
                beforeSend: function () {
//                $.showLoading('正在请求中..');
                },
                complete: function () {
//                $.hideLoading();
                },
                success: function ($data) {
//                var listInfo = $data.retArray;
                    //拼接参数
//                    console.log($data);
                    $('#teamNameEN').html($data.teamNameEN)
                    var _html = template('list_team', $data);
                    $('#popup_teamList').html(_html) ;

                    $('.team-member').bPopup({
                        closeClass: 'close'
                    });
                }
            });


        })

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
    'type' => $pagetype,
    'ip' => $loginIP,
    'url' => $apiUrl,
    'result' => mysql_real_escape_string(json_encode_cn($team_list)),
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