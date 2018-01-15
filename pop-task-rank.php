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
            $headerLocation = 'join-choose.html';
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
//5km
$fiveArr = array();
$query = "SELECT id,`name`,openid,task_id,t_id,t_name,img_one,img_two,create_time,update_time FROM dtc_task_popup WHERE task_id = 1 AND img_one IS NOT NULL 
        AND update_time>'2017-09-27' AND is_confirm = '1' AND  update_time<'2017-09-28' ORDER BY update_time ASC;";
$result = mysql_query($query,$conn);
while ($row = mysql_fetch_assoc($result)){
    $openid = $row['openid'];
    $fiveArr[$openid] = $row;
}

//5km
$tenArr = array();
$query = "SELECT id,`name`,openid,task_id,t_id,t_name,img_one,img_two,create_time,update_time FROM dtc_task_popup WHERE task_id = 2 AND img_one IS NOT NULL 
        AND update_time>'2017-09-27' AND is_confirm = '1' ORDER BY update_time ASC;";
$result = mysql_query($query,$conn);
while ($row = mysql_fetch_assoc($result)){
    $openid = $row['openid'];
    $tenArr[$openid] = $row;
}
//5km
$fullArr = array();
$query = "SELECT id,openid,name,task_id,t_id,t_name,img_one,img_two,create_time,update_time FROM dtc_task_popup WHERE task_id = 3 AND img_one IS NOT NULL 
        AND is_confirm = '1' ORDER BY update_time ASC";
$result = mysql_query($query,$conn);
while ($row = mysql_fetch_assoc($result)){
    $openid = $row['openid'];
    $fullArr[$openid] = $row;
}
//5km
$half = array();
$query = "SELECT id,openid,name,task_id,t_id,t_name,img_one,img_two,create_time,update_time FROM dtc_task_popup WHERE task_id =4 AND img_one IS NOT NULL 
        AND is_confirm = '1' ORDER BY update_time ASC";
$result = mysql_query($query,$conn);
while ($row = mysql_fetch_assoc($result)){
    $openid = $row['openid'];
    $half[$openid] = $row;
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
        </style>
    </head>
    <body>
    <div class="wrap rank">
        <a class="home" href="main.php"><img src="./img/home-icon1.png" width="100%"></a>
        <a class="prize" href="pop-prize.php"><img src="./img/prize.png" width="100%"></a>
        <div class="title">英雄榜</div>
        <div class="box">
            <div class="tab_1" >
                <a class="item_1  brl <?php if($date==1){?>active<?php } ?>" data-mid="1"><p class="li-text">TASK1</p></a>
                <a class="item_1 <?php if($date==2){?>active<?php } ?>" data-mid="2"><p class="li-text">TASK2</p></a>
                <a class="item_1 <?php if($date==3){?>active<?php } ?>" data-mid="3"><p class="li-text">TASK3</p></a>
                <a class="item_1 brr <?php if($date==4){?>active<?php } ?> " data-mid="4"><p class="li-text">TASK4</p></a>
            </div>
            <div class="tab-title <?php if($date==1){?>active-1<?php } ?>" data-t="1">
                <div class="tab " >
                    <div class="item" >姓名<br><span class="__span">NAME</span></div>
                    <div class="item" >队名<br><span class="__span">TEAM</span></div>
                </div>
                <ul class="list" >
                    <?php $count = 0;
                    foreach ($fiveArr as $item_row) {
                        $count = $count + 1;
                        ?>
                        <li class="list-li grey-1" id="<?php echo $item_row['id']?>">

                            <div class="_div">
                                <span class="_span"><?php
                                    switch ($count){
                                        case 1:echo "<img src='img/icon-no1.png'width='50%'>";break;
                                        case 2:echo "<img src='img/icon-no2.png'width='50%'>";break;
                                        case 3:echo "<img src='img/icon-no3.png'width='50%'>";break;
                                        default: echo "NO.".$count;
                                    }
                                    ?></span>
                                <p class="_a" style="width: 8rem"><?php echo $item_row['name']; ?></p>
                                <p class="teamname"  item-id="<?php echo $item_row['t_id'] ?>"><?php echo $item_row['t_name']; ?></p>
                            </div>
                        </li>
                    <?php } ?>
                </ul>
            </div>
            <div class="tab-title <?php if($date==2){?>active-1<?php } ?>" data-t="2">
                <div class="tab " >
                    <div class="item" >姓名<br><span class="__span">NAME</span></div>
                    <div class="item" >队名<br><span class="__span">TEAM</span></div>
                </div>
                <ul class="list" >
                    <?php $count = 0;
                    foreach ($tenArr as $item_row) {
                        $count = $count + 1;
                        ?>
                        <li class="list-li grey-1" id="<?php echo $item_row['id'] ?>">
                            <div class="_div">
                                <span class="_span"><?php
                                    switch ($count){
                                        case 1:echo "<img src='img/icon-no1.png'width='50%'>";break;
                                        case 2:echo "<img src='img/icon-no2.png'width='50%'>";break;
                                        case 3:echo "<img src='img/icon-no3.png'width='50%'>";break;
                                        default: echo "NO.".$count;
                                    }
                                    ?></span>
                                <p class="_a" style="width: 8rem"><?php echo $item_row['name']; ?></p>
                                <p class="teamname"  item-id="<?php echo $item_row['t_id'] ?>"><?php echo $item_row['t_name']; ?></p>
                            </div>

                        </li>
                    <?php } ?>
                </ul>
            </div>
            <div class="tab-title <?php if($date==3){?>active-1<?php } ?>" data-t="3">
                <div class="tab " >
                    <div class="item" >姓名<br><span class="__span">NAME</span></div>
                    <div class="item" >队名<br><span class="__span">TEAM</span></div>
                </div>
                <ul class="list" >
                    <?php $count = 0;
                    foreach ($fullArr as $item_row) {
                        $count = $count + 1;
                        ?>
                        <li class="list-li grey-1" id="<?php echo $item_row['id'] ?>">
                            <div class="_div">
                                                                <span class="_span"><?php
                                                                    switch ($count){
                                                                        case 1:echo "<img src='img/icon-no1.png' width='50%'>";break;
                                                                        case 2:echo "<img src='img/icon-no2.png' width='50%'>";break;
                                                                        case 3:echo "<img src='img/icon-no3.png' width='50%'>";break;
                                                                        default: echo "NO.".$count;
                                                                    }
                                                                    ?></span>
                                <p class="_a" style="width: 8rem"><?php echo $item_row['name']; ?></p>
                                <p class="teamname" item-id="<?php echo $item_row['t_id'] ?>" ><?php echo $item_row['t_name']; ?></p>

                            </div>

                        </li>
                    <?php } ?>
                </ul>
            </div>
            <div class="tab-title <?php if($date==4){?>active-1<?php } ?>" data-t="4">
                <div class="tab " >
                    <div class="item" >姓名<br><span class="__span">NAME</span></div>
                    <div class="item" >队名<br><span class="__span">TEAM</span></div>
                </div>
                <ul class="list" >
                    <?php $count = 0;
                    foreach ($half as $item_row) {
                        $count = $count + 1;
                        ?>
                        <li class="list-li grey-1" id="<?php echo $item_row['id'] ?>">
                            <div class="_div">
                                                                <span class="_span"><?php
                                                                    switch ($count){
                                                                        case 1:echo "<img src='img/icon-no1.png' width='50%'>";break;
                                                                        case 2:echo "<img src='img/icon-no2.png' width='50%'>";break;
                                                                        case 3:echo "<img src='img/icon-no3.png' width='50%'>";break;
                                                                        default: echo "NO.".$count;
                                                                    }
                                                                    ?></span>
                                <p class="_a" style="width: 8rem"><?php echo $item_row['name']; ?></p>
                                <p class="teamname" item-id="<?php echo $item_row['t_id'] ?>" ><?php echo $item_row['t_name']; ?></p>

                            </div>

                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </div>
    <script>
        $(".tab_1 .item_1").on("click", function () {
            $(this).addClass("active").siblings(".item_1").removeClass("active");
            $('[data-t=' + $(this).attr("data-mid") + ']').addClass("active-1").siblings(".tab-title").removeClass("active-1");
        });
       /*  var wrap=$('.tab-title[data-t=2]');
         console.log(wrap);
         wrap.css('padding-top','13rem');
         wrap.html('<p class="text">排行榜尚未开启<br><span>COMING SOON</span></p>');*/
     /*   var wrap=$('.tab-title[data-t=3]');
        console.log(wrap);
        wrap.css('padding-top','13rem');
        wrap.html('<p class="text">排行榜尚未开启<br><span>COMING SOON</span></p>');
        var wrap=$('.tab-title[data-t=4]');
        console.log(wrap);
        wrap.css('padding-top','13rem');
        wrap.html('<p class="text">排行榜尚未开启<br><span>COMING SOON</span></p>');*/
        /*var wrap=$('.tab-title[data-t=1]');
        console.log(wrap);
        wrap.css('padding-top','13rem');
        wrap.html('<p class="text">排行榜尚未开启<br><span>COMING SOON</span></p>');*/
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
        //遮罩
        var wrap=$('.rank');
        //        wrap.css('padding-top','19rem');
        //        wrap.html('<p class="text">获奖结果将于9月8日公布<br><span>WINNERS WILL BE ANNOUNCED ON 8TH SEP.</span></p>' +
        //            '<a class="choose-item" href="main.php" >返回首页<br><span style="font-family:fornike365;font-size:0.8rem;">RETURN TO HOME PAGE</span></a>');
    </script>

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