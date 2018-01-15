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
$query = "SELECT * FROM dtc_taskone_marathon WHERE marathon_project = 5 AND full_name IS NOT NULL ORDER BY mile DESC ";
$result = mysql_query($query,$conn);
while ($row = mysql_fetch_assoc($result)){
    $openid = $row['openid'];
    $fiveArr[$openid] = $row;
}
//5km
$tenArr = array();
$query = "SELECT * FROM dtc_taskone_marathon WHERE marathon_project = 10 AND full_name IS NOT NULL ORDER BY mile DESC ";
$result = mysql_query($query,$conn);
while ($row = mysql_fetch_assoc($result)){
    $openid = $row['openid'];
    $tenArr[$openid] = $row;
}
//5km
$fullArr = array();
$query = "SELECT * FROM dtc_taskone_marathon WHERE marathon_project = 42 AND full_name IS NOT NULL ORDER BY mile DESC ";
$result = mysql_query($query,$conn);
while ($row = mysql_fetch_assoc($result)){
    $openid = $row['openid'];
    $fullArr[$openid] = $row;
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
                margin: 0.5rem auto 0;
                background: rgba(255, 255, 255, 0.8);
                box-shadow: 6px 10px 0 rgba(250, 106, 6, 0.8);
            }
            .rank .box .tab {
                display:flex;
                align-items: center;
                justify-content: space-between;
                width: 21rem;
                height: 2rem;
                margin: 0 auto;
                background: none;
                border-radius: 0;
                font-size: 1rem;
                color: #fa6b0e;
                font-weight: bold;
                text-align: center;
                border: 1px solid #fa6b0e;
            }
            .tab-title{
                display: none;
            }
            .rank .box .tab .item {
                border-radius: 0;
                width: 10rem;
                padding: 0.4rem 0 0.2rem;
                line-height: 0.75rem;
                border: 1px solid #fa6b0e;
            }
            .rank .box .list {
                display: block;
                width: 23.9rem;
                padding: 0rem 2.0rem 0.5rem 0.8rem;
                box-sizing: border-box;
                height: 28.4rem;
                margin-top: .5rem;
                overflow-y: scroll;
                overflow-x: hidden;
            }
            .rank .box .list ._span {
                font-family: 'fornike365';
                font-style: oblique;
                font-size: 1.4rem;
                /*margin-left: 0.5rem;*/
                text-align: left;
                display:inline-block;
                width: 2.8rem;
                line-height: 1rem;
                letter-spacing: -1px;
            }
            .__span{
                font-family: 'fornike365';
                font-size: 0.6rem;
                letter-spacing: 0;
            }
            .rank .box .list .list-li {
                padding-top: 1.0rem;
                color: #fa6a0e;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }

            .rank .box .list .list-li i {
                height: 1.6rem;
                width: 1rem;
                /*  margin-right: 1rem;*/
            }

            .rank .box .list .list-li ._div {
                display: flex;
                align-items: center;
                justify-content: space-between;
                width: 20rem;
                height: 3rem;
            }
            .rank .box .list .list-li .a-box{
                display: flex;
                width: 9rem;
                height: 2.9rem;
            }
            .teamname {
                width: 6rem;
                font-size: 0.8rem;
                text-align: left;
                margin-left: 0rem;
                border-bottom: none;
                word-wrap: break-word;
                text-decoration: underline;
            }
            .a-box .belong{
                font-family: 'fornike365';
                font-size: 0.7rem;
                width: 100%;
                font-style: oblique;
            }
            .rank .box .list ._a {
                display: inline-block;
                font-size: 1rem;
                width: 5rem;
                margin-left: -1rem;
                word-wrap: break-word;
            }

            .rank .box .list .distance {
                display: block;
                font-family: 'fornike365';
                font-size: 1.2rem;
                text-align: center;
                width: 4rem;
                word-break: break-all;
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

                border-left: 1px solid #ffffff;
                width: 5rem;
                height: 2rem;
                padding: 0.2rem 0 0;
                line-height: 1rem;
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
            .lh2{
                line-height: 2rem;
            }
            .clb{
                border-left: none;
                line-height: 2rem;
            }
            .active{
                background: #fa6b0e;
                color: #fff;
            }
            .active-1{
                display: block!important;
            }
            .tab_1-p{
                font-size: 0.6rem;
                font-weight: normal;
                font-family: 'fornike365';
            }
            /*team-member pop*/
            .tab-title-pop{
                display: flex;
                margin-left: 4.5rem;
                margin-top: 1rem;
                margin-right: 1rem;
                justify-content: space-between;
                /*  border-bottom: 1px solid #d8ba97;*/
            }
            .tab-title-pop ._p{
                font-size: 1.1rem;
                font-weight: bold;
                color: #fa6a0e;
                text-align: center;
                line-height: 0.8rem;
                border-bottom: 1px solid #f05f06;
            }
            .team-member{
                display: none;
            }
            .team-member-box{
                padding: 1.5rem 1rem;
                box-sizing: border-box;
                width: 21rem;
                height: 34rem;
                margin: 0 auto;
                background: rgba(255,255,255,1);
                border: 2px solid #fa6a0e;
                border-radius: 8px;
                /* box-shadow: 6px 10px 0 rgba(250, 106, 6, 1); */
            }
            .team-member-list{
                display: block;
                margin: 0;
                padding: 0.5rem 2.0rem 0.5rem 0rem;
                height: 23.5rem;
                overflow-y: scroll;
                overflow-x: hidden;
            }
            .team-member-list-item{
                padding-top: 0.6rem;
                color: #fa6a0e;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }
            .team-member-list-item i{
                height: 1.2rem;
                width:1.2rem;
            }
            .captain::before {
                content: '';
                display: inline-block;
                height: 1.2rem;
                width: 1.2rem;
                background: url(./img/captain.png) no-repeat;
                background-size: contain;
                background-position: center;
            }
            .team-member-list-div{
                width: 6rem;
                text-align: left;
            }
            .team-member-num{
                font-family: 'fornike365';
                font-style: oblique;
                font-size: 1.2rem;
                display: block;
                width: 3rem;
                line-height: 2.5rem;
                height: 2.5rem;
                text-align: center;
                margin-left: -0.5rem;
            }
            .team-member-num img{
                width: 50%!important;
            }
            .team-member-list-div .name-2{
                font-size: 1rem;
                /*margin-left: -5rem;*/
                /* border-bottom: 1px solid #fa6b0e;*/
            }
            .team-member-totle{
                font-family: 'fornike365';
                font-size: 1rem;
                width: 5rem;
                text-align: right;
                word-break:break-all;
                margin-right: -1rem;
            }
            .team-member-box .title{
                padding-top: 0 !important;
                font-size: 2rem;
                font-style: oblique;
                text-align: center;
                color: #fa6b0e;
                line-height: 1.5rem;
                height: 4rem;
                width: auto;
                border-bottom: 1px solid #fa6a0e;
            }
            /*.team-member-box .title img{
                display: block;
                margin: 0 auto;
                width: 50%;
            }*/
            .team-member-box .title a.close{
                display: block;
                width: 1.4rem;
                height: 1.4rem;
                background: url("./img/close-btn2.png")no-repeat;
                background-size: contain;
                border-bottom: none;
                position: absolute;
                top: 1rem;
                left: 1rem;

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
                line-height: 1rem;
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
            .lh2{
                line-height: 2rem;
            }
            .clb{
                border-left: none;
                line-height: 2rem;
            }
            .active{
                background: #fa6b0e;
                color: #fff;
            }
            .active-1{
                display: block!important;
            }
            .tab_1-p{
                display: block;
                font-size: 0.6rem;
                font-weight: normal;
                font-family: 'fornike365';
            }
            .li-text{
                display: inline-block;
                width: 100%;
            }
            .choose-item {
                padding-top: 0.6rem;
                box-sizing: border-box;
                display: block;
                background: rgba(255, 255, 255, 0.8);
                box-shadow: 0px 3px 0 #eb5e0c;
                border: 2px solid #ea5012;
                width:9rem;
                height: 3.0rem;
                line-height: 0.8rem;
                border-radius: 10px;
                text-align: center;
                color: #fc5219;
                font-family: 'fornike365';
                font-size: 1.2rem;
                font-style: oblique;
                margin: 10rem auto 0;
            }
            ::-webkit-scrollbar {
                border-radius: 10px;
                width: 16px;
                height: 16px;
                background-color: #cccccc;
            }

            ::-webkit-scrollbar-track {
                border-radius: 10px;
                background-color: #cccccc;
            }

            ::-webkit-scrollbar-thumb {
                height: 5px;
                border-radius: 10px;
                background-color: #fa6b0e;
            }
            .text{
                width: 21rem;
                margin: 0 auto;
                text-align: center;
                line-height: 1.5rem;
                font-size: 1.6rem;
                color: #ffffff;
            }
            .text span{
                font-size: 0.9rem;
            }
            .home{
                display: block;
                width: 2.0rem;
                height: 2.0rem;
                float: left;
                margin-top: -1.0rem;
                /*margin-bottom: 1rem;*/
                margin-left: 0.5rem;
            }
        </style>
    </head>
    <body>
    <div class="wrap rank">
        <a class="home" href="main.php"><img src="./img/home.png" width="100%"></a>
        <div class="title">上马英雄榜</div>
        <!--<div class="tab-title" style="text-align: right;height: 2rem" data-t="2"></div>
         <div class="tab-title active-1" style="text-align: right;height:2rem" data-t="1"></div>-->
        <div class="box">
            <div class="tab_1" >
                <a class="item_1 clb brl active" data-mid="1"><p class="li-text">5K</p></a>
                <a class="item_1 lh2 ceshi" data-mid="2"><p class="li-text">10K</p></a>
                <a class="item_1 brr " data-mid="3"><p class="li-text">全马
                        <span class="tab_1-p"> FULL MARATHON</span></p></a>
            </div>

            <div class="tab-title active-1" data-t="1">
                <div class="tab " >
                    <div class="item" style="width: 8rem">姓名<br><span class="__span">NAME</span></div>
                    <div class="item" style="width:8rem">队名<br><span class="__span">TEAM</span></div>
                    <div class="item" style="width: 9rem">所在商店<br><span class="__span">DEPARTMENT</span></div>
                </div>
                <ul class="list" >
                    <?php $count = 0;
                    foreach ($fiveArr as $item_row) {
                        $count = $count + 1;
                        ?>
                        <li class="list-li" id="<?php echo $item_row['id']?>">
                            <i></i>
                            <div class="_div">
                                <?php echo "&nbsp;&nbsp;&nbsp;"?>
                                <!--                                <span class="_span">--><?php
                                //                                    switch ($count){
                                //                                        case 1:echo "<img src='img/icon-no1.png' width='65%'>";break;
                                //                                        case 2:echo "<img src='img/icon-no2.png' width='65%'>";break;
                                //                                        case 3:echo "<img src='img/icon-no3.png' width='65%'>";break;
                                //                                        default: echo "NO.".$count;
                                //                                    }
                                //                                    ?><!--</span>-->
                                <p class="_a"><?php echo $item_row['full_name']; ?></p>
                                <p class="teamname" item-id="<?php echo $item_row['teamId'] ?>"><?php echo $item_row['team_name']; ?></p>
                                <p class="distance"><?php echo $item_row['department']; ?>
                                </p>
                            </div>
                        </li>
                    <?php } ?>
                </ul>
            </div>
            <div class="tab-title" data-t="2">
                <div class="tab " >
                    <div class="item" style="width: 8rem">姓名<br><span class="__span">NAME</span></div>
                    <div class="item" style="width:8rem">队名<br><span class="__span">TEAM</span></div>
                    <div class="item" style="width: 9rem">所在商店<br><span class="__span">DEPARTMENT</span></div>
                </div>
                <ul class="list" >
                    <?php $count = 0;
                    foreach ($tenArr as $item_row) {
                        $count = $count + 1;
                        ?>
                        <li class="list-li" id="<?php echo $item_row['id'] ?>">
                            <i></i>
                            <div class="_div">
                                <?php echo "&nbsp;&nbsp;&nbsp;"?>
                                <!--                                <span class="_span">--><?php
                                //                                    switch ($count){
                                //                                        case 1:echo "<img src='img/icon-no1.png' width='65%'>";break;
                                //                                        case 2:echo "<img src='img/icon-no2.png' width='65%'>";break;
                                //                                        case 3:echo "<img src='img/icon-no3.png' width='65%'>";break;
                                //                                        default: echo "NO.".$count;
                                //                                    }
                                //                                    ?><!--</span>-->
                                <p class="_a"><?php echo $item_row['full_name']; ?></p>
                                <p class="teamname" item-id="<?php echo $item_row['teamId'] ?>"><?php echo $item_row['team_name']; ?></p>
                                <p class="distance"><?php echo $item_row['department']; ?>
                                </p></div>

                        </li>
                    <?php } ?>
                </ul>
            </div>
            <div class="tab-title" data-t="3">
                <div class="tab " >
                    <div class="item" style="width: 8rem">姓名<br><span class="__span">NAME</span></div>
                    <div class="item" style="width:8rem">队名<br><span class="__span">TEAM</span></div>
                    <div class="item" style="width: 9rem">所在商店<br><span class="__span">DEPARTMENT</span></div>
                </div>
                <ul class="list" >
                    <?php $count = 0;
                    foreach ($fullArr as $item_row) {
                        $count = $count + 1;
                        ?>
                        <li class="list-li" id="<?php echo $item_row['id'] ?>">
                            <i></i>
                            <div class="_div">
                                <?php echo "&nbsp;&nbsp;&nbsp;"?>
                                <!--                                <span class="_span">--><?php
                                //                                    switch ($count){
                                //                                        case 1:echo "<img src='img/icon-no1.png' width='65%'>";break;
                                //                                        case 2:echo "<img src='img/icon-no2.png' width='65%'>";break;
                                //                                        case 3:echo "<img src='img/icon-no3.png' width='65%'>";break;
                                //                                        default: echo "NO.".$count;
                                //                                    }
                                //                                    ?><!--</span>-->
                                <p class="_a"><?php echo $item_row['full_name']; ?></p>
                                <p class="teamname" item-id="<?php echo $item_row['teamId'] ?>" ><?php echo $item_row['team_name']; ?></p>
                                <p class="distance"><?php echo $item_row['department']; ?>
                                </p></div>

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
        /* var wrap=$('.rank');
         wrap.css('padding-top','19rem');
         wrap.html('<p class="text">获奖结果将于26日公布<br><span>WINNERS WILL BE ANNOUNCED ON 26TH AUG.</span></p>');*/
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