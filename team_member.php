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
        'type' => $pagetype,
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
$sql3 = "select a.id,a.teamId,a.is_captain,b.teamName,b.department
          from dtc_join_user a LEFT JOIN dtc_team b 
          ON a.teamId = b.teamId
          WHERE  a.openid='$openid' ";
$result3 = mysql_query($sql3, $conn);
$row3 = mysql_fetch_assoc($result3);
$teamName = $row3['teamName'];
$_SESSION['userInfo']['teamName'] = $teamName;

$my_uid = $row3['id'];
$teamId = $row3['teamId'];
$is_captain =$row3['is_captain'];

//以下构造用户情况
$team_list = array();
//取同一团队不同小组情况
$sql = "select * from dtc_team where teamId = $teamId;";
$ret = mysql_query($sql, $conn);
$teams = array();
while ($row = mysql_fetch_assoc($ret)) {
    $department = $row['department'];
    $teams[$department] = $row;
}

//取同一团队跑步数据
$heroLists = array();
$sql = "SELECT `openid`, sum(`long`) as longTotal, min(created) as created FROM yiqipao_member_project WHERE `pid`={$dtcPid} and teamId=$teamId group by openid ORDER BY sum(`long`) DESC, id asc;";
$query = mysql_query($sql, $conn);
while ($row = mysql_fetch_assoc($query))
{
    $heroOpenid = $row['openid'];
    $heroLists[$heroOpenid] = $row;
}

$headImgList = array();
$query = "select headimg,openid from yiqipao_member where teamId = $teamId";
$result = mysql_query($query, $conn);
while ($row = mysql_fetch_assoc($result)){
    $heroOpenid = $row['openid'];
    $headImgList[$heroOpenid] = $row;
}

//取本团队所有用户信息
$sql = "select * from dtc_join_user where teamId = $teamId;";
$ret = mysql_query($sql, $conn);
while ($row = mysql_fetch_assoc($ret)) {
    $heroOpenid = $row['openid'];
    $department = $row['department'];
    $row['teamNameEN'] = $teams[$department]['teamNameEN'];
    $row['total'] = 0;
    if(isset($heroLists[$heroOpenid]['longTotal']))
    {
        $row['total'] = $heroLists[$heroOpenid]['longTotal'];
    }
    if(isset($headImgList[$heroOpenid]['headimg']))
    {
        $row['headimg'] = $headImgList[$heroOpenid]['headimg'];
    }

    $team_list[] = $row;
}
//根据里程倒序
$team_list = array_sort($team_list, 'total', $type = 'desc');

?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
        <link rel="stylesheet" href="//cdn.bootcss.com/weui/1.1.1/style/weui.min.css">
        <link rel="stylesheet" href="//cdn.bootcss.com/jquery-weui/1.0.1/css/jquery-weui.min.css">
        <!--    引入自己的css-->
        <link rel="stylesheet" href="css/reset.css">
        <link rel="stylesheet" href="//at.alicdn.com/t/font_8ddf6q9x52ucv7vi.css"/>
        <!--引入自己的Js-->
        <script src="js/responsive.js"></script>
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
                padding: 1.5rem 1rem 2rem;
                overflow: hidden;
            }

            .rank .title {
                font-size: 2rem;
                font-style: oblique;
                text-align: center;
                color: #ffffff;
                width: auto;
            }

            .rank .box {
                padding: 1.5rem 0;
                width: 23.5rem;
                height: 30rem;
                margin: 0 auto;
                background: rgba(255, 255, 255, 0.8);
                box-shadow: 6px 10px 0 rgba(250, 106, 6, 0.8);
            }
            .check-pop{
                display: none;
                width: 22rem;
                /* height: 29rem; */
                background: #ffffff;
                border: 3px solid #f05f06;
                box-sizing: border-box;
                padding: 1.5rem;
            }
            .head-img{
                display: block;
                margin: 0 auto;
                width: 8rem;
                border-radius: 50%;
                border: 3px solid #f05f06;
            }
            .delete-pop {
                display: none;
                position: absolute;
                height: 43.3rem;
                top: 0;
                bottom: 0;
                left: 0;
                right: 0;
                background-color: rgba(0, 0, 0, .5);
            }

            .delete-box {
                padding: 1.5rem 1.5rem;
                box-sizing: border-box;
                width: 22rem;
                margin: 6rem auto 0;
                background: rgba(255, 255, 255, 1);
                box-shadow: 6px 10px 0 rgba(250, 106, 6, 1);
                color: #f76a0e;
            }

            .delete-box ._ul {
                margin: 0.8rem 0;
                font-size: 1.1rem;
            }

            .delete-box ._ul ._li {
                margin-top: 0.5rem;
            }

            .delete-box p {
                font-size: 1.2rem;
                line-height: 1.3rem;
            }

            .delete-box img {
                width: 1rem;
            }

            input[type="radio"] {
                box-sizing: border-box;
                padding: 0;
                background: #f05f06;
                border: none;
                border-radius: 3px;
                outline: none;
                width: 1rem;
                height: 1rem;
                margin-right: 0.7rem;
            }
            .iconfont{
                color: #ffffff;
            }
            .other-reason {
                background: #e5e5e5;
                width: 100%;
                height: 5rem;
                margin-bottom: 1.0rem;
                border: none;
                outline: none;
                padding: 0.5rem;
                box-sizing: border-box;
            }

            .btn-area {
                margin: 0 1rem;
                display: flex;
                justify-content: space-between;
            }
            .unset {
                background-color: #cdcdcd !important;
                box-shadow: 4px 4px #b5b5b5 !important;
            }
            .btn-area button,.manage-text {
                display: inline-block;
                border-radius: 8px;
                font-family: "STHeiti SC";
                width: 6.8rem;
                height: 2.4rem;
                line-height: 0.9rem;
                padding-top: 0.35rem;
                text-align: center;
                background-color: #f76a0f;
                box-shadow: 3px 3px #d46b29;
                color: #ffffff;
                font-size: 1.2rem;
                font-style: oblique;
                letter-spacing: 4px;
            }
            .manage-text{
                font-size: 0.8rem;
                height: 1.6rem;
                width: 6.0rem;
                line-height: 0.7rem;
                letter-spacing: 1px;
                margin-bottom: 1rem;
            }
            .manage{
                display: inline-block;
                width: 8rem;
                height: 3rem;
            }
            .rank .box .tab {
                display:flex;
                align-items: center;
                justify-content: space-between;
                width: 21rem;
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
                line-height: 0.9rem;
                border: 1px solid #fa6b0e;
            }
            .rank .box .list {
                display: block;
                /* margin-top: 0.5rem; */
                width: 24.3rem;
                padding: 0rem 2.0rem 0.5rem 0.8rem;
                box-sizing: border-box;
                height: 26.5rem;
                overflow-y: scroll;
                overflow-x: hidden;
            }
            .rank .box .list ._span {
                font-family: 'fornike365';
                font-style: oblique;
                font-size: 1.7rem;
                margin-left: 0.5rem;
                text-align: left;
                display: block;
                width: 4rem;
                letter-spacing: -1px;
            }
            .__span{
                font-family: 'fornike365';
                font-size: 0.6rem;
                letter-spacing: 0;
            }
            .__p{
                font-family: 'fornike365';
                color: #f05f06;
                font-size: 1.2rem;
                margin: 1rem 0 1rem 1rem;
            }
            .delete-span{
                font-family: 'fornike365';
                font-size: 0.6rem;
                line-height: 0.9rem;
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
                width: 2rem;
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
                width: 11rem;
                height: 2.9rem;
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                justify-content: center;
            }
            .a-box .belong{
                font-family: 'fornike365';
                font-size: 0.7rem;
                width: 100%;
                font-style: oblique;
            }
            .newlist{
                margin-left: 1rem;
                width: 100%;
            }
            .name-1{
                font-size: 1.1rem;
            }
            .name-2{
                display: inline-block;
                font-family: 'fornike365';
                font-size: 1.0rem;
            }
            .checkbtn{
                float: right;
                display: inline-block;
                background: #f05f06;
                color: #ffffff;
                text-align: center;
                width: 2.8rem;
                height: 1.5rem;
                line-height: 1.5rem;
                font-weight: bold;
                font-size: 0.85rem;
                border-radius: 8px;
            }
            .captain::before {
                content: '';
                display: inline-block;
                height: 1.4rem;
                width: 1.4rem;
                background: url(./img/captain.png) no-repeat;
                background-size: contain;
                background-position: center;
            }

            .rank .box .list ._a {
                font-size: 1rem;
                margin-left: 0;
                font-weight: bold;
            }

            .rank .box .list .distance {
                display: block;
                font-family: 'fornike365';
                font-size: 1.5rem;
                margin-right: -1rem;
                text-align: center;
                width: 4rem;
                word-break: break-all;
            }

            .rank .box .list .delete{
                display: inline-block;
                background: url("./img/delete.png")no-repeat;
                background-size: contain;
                width: 2rem;
                height: 1.2rem;
            }

            .tab_1{
                display: flex;
                width: 20rem;
                margin: 0 auto 1rem;
                align-items: center;
                justify-content: space-between;
                background: #fff;
                border-radius: 8px;
                font-size: 1rem;
                color: #fa6b0e;
                font-weight: bold;
                text-align: center;
            }
            .item_1{
                border-radius: 8px;
                width: 10rem;
                padding: 0.2rem 0 0;
                line-height: 1rem;
            }
            .active{
                display: block;
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
            .home{
                display: block;
                width: 2.0rem;
                height: 2.0rem;
                float: left;
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

        </style>
    </head>
    <body>
    <div class="wrap rank">
        <a class="home" href="main.php"><img src="./img/home.png" width="100%"></a>
        <div class="title"><?php echo $teamName.'队'; ?></div>
        <div class="tab-title" style="text-align: right;" data-t="2">
            <?php if ($is_captain>0) {?>
                <a class="manage manage-btn"><p class="manage-text">管理成员<br><span class="__span" style="font-size: 0.5rem">MANAGE TEAM MEMBER</span></p></a>
            <?php }?>
        </div>
        <div class="tab-title active-1" style="text-align: right;height: 3rem" data-t="1"></div>
        <div class="box">
            <?php if ($is_captain>0) {//是否是队长 ?>
                <div class="tab_1" >
                    <div class="item_1 active" data-mid="1">待审核
                        <p class="tab_1-p"> TO BE CONFIRMED</p></div>
                    <div class="item_1" data-mid="2">全部成员
                        <p class="tab_1-p">ALL TEAM MEMBER</p></div>
                </div>
            <?php } ?>
            <div class="tab-title <?php if ($is_captain>0) { echo "active-1";}?>" data-t="1">
                <div class="tab " >
                    <div class="item" style="width: 10rem; font-family: 'fornike365';">姓名/NAME</div>
                    <div class="item" style="width: 16rem; font-family: 'fornike365';">来自/FROM</div>
                </div>
                <ul class="list" >
                    <?php
                    foreach ($team_list as $key => $item_row) {
                        if(!$item_row['is_captain'] && $item_row['is_allow'] == '0'){
                            ?>
                            <li class="list-li newlist">
                                <p class="name-1"><?php echo $item_row['fullname']; ?></p>
                                <div style="width:12.2rem" > <p class="name-2"><?php echo is_numeric($item_row['department'])==1?$item_row['teamNameEN']:$item_row['department']; ?></p>
                                    <a class="checkbtn" check-list-key = "<?php echo $key ?>">审核</a></div>
                            </li>
                        <?php }} ?>
                </ul>
            </div>
            <div class="<?php if ($is_captain>0) {echo "tab-title";}?>" data-t="2">
                <div class="tab " >
                    <div class="item" style="width: 8rem">队内排名<br><span class="__span">RANKING</span></div>
                    <div class="item" style="width: 13rem">名称<br><span class="__span">NAME</span></div>
                    <div class="item" style="width: 8rem">公里数<br><span class="__span">KM</span></div>
                </div>
                <ul class="list"  style="height: 26rem;">
                    <?php $count = 0;
                    foreach ($team_list as $item_row) {
                        if($item_row['is_captain'] || $item_row['is_allow'] == '1'){
                            $count = $count + 1;
                            ?>
                            <li class="list-li" id="<?php echo $item_row['id'] ?>">
                                <?php if ($item_row['is_captain']) {//是否是队长 ?>
                                    <i class="captain"></i>
                                <?php } elseif($is_captain>0 ) {  // 且要是被批准的?>
                                    <i item-id="<?php echo $item_row['id'] ?>" class="team-icon"></i>
                                <?php } else{?>
                                    <i></i>
                                <?php } ?>
                                <div class="_div"><span class="_span"><?php
                                        switch ($count){
                                            case 1:echo "<img src='img/icon-no1.png'width='50%'>";break;
                                            case 2:echo "<img src='img/icon-no2.png'width='50%'>";break;
                                            case 3:echo "<img src='img/icon-no3.png'width='50%'>";break;
                                            default: echo "NO.".$count;
                                        }
                                        ?></span>
                                    <div class="a-box"><a class="_a"><?php echo $item_row['fullname']; ?></a><p class="belong">来自/FROM：<?php  echo $item_row['teamNameEN']; ?></p></div>
                                    <p class="distance"><?php echo round($item_row['total']); ?>
                                    </p></div>

                            </li>
                        <?php }} ?>


                </ul>
            </div>
        </div>
    </div>
    <aside class="check-pop">
        <img class="head-img" src="http://www.makeyourruncount.com/campaign_nike/running/public/img/lixiang_share.jpg">
        <p class="__p">姓名<span  class="__span">/NAME:</span><text id="realName">OLOVIA</text></p>
        <p class="__p">来自<span  class="__span">/FROM</span>:<text id = "procurement">PROCUREMENT<text></p>
        <p class="__p">员工号<span  class="__span">/EMPLOYEE NUMBER:</span><text id = "workNum">1234567<text></p>
        <p class="__p">电话号码<span  class="__span">/TEL:</span><text id = "phone">18450055555</text></p>
        <div class="btn-area" style="margin-top: 2rem">
            <button class="confirm">同意<br><span class="__span">CONFIRM</span></button>
            <button class="reject">拒绝<br><span class="__span">REJECT</span></button>
        </div>
    </aside>
    <aside class="delete-pop">
        <div class="delete-box">
            <p>将选中的队员从队伍中删除，其跑步成绩也将被一并被清除且不可恢复，请您确保已知晓，并选择删除理由：
                <br><span class="delete-span" style="font-size: 0.9rem" >IF YOU DELETE YOUR TEAM MEMBER, THE PERFORMANCE WILL ALSO BE DELETED AND CANNOT BE RETRIEVED. PLEASE SELECT THE REASON BELOW:</span>
            </p>
            <ul class="_ul">
                <li class="_li"><label><input type="radio" class="iconfont "  name="re">队员离职<span  class="delete-span">（NO LONGER WITH THE COMPANY）</span></label></li>
                <li class="_li"><label><input type="radio" class="iconfont "  name="re">非本队人员<span  class="delete-span">（NOT BELONG TO THE TEAM）</span></label></li>
                <li class="_li"><label><input class="reason iconfont" name="re" type="radio">其他请填写<span  class="delete-span">（OTHERS -PLEASE INDICATE YOUR REASON）</span></label></li>
            </ul>
            <textarea class="other-reason"></textarea>
            <div class="btn-area" >
                <button class="sure unset">确认<br><span class="__span">CONFIRM</span></button>
                <button class="cancel">取消<br><span class="__span">CANCEL</span></button>
            </div>
        </div>
    </aside>
    <!--引用Jquery-->
    <script src="js/jquery-2.1.4.js"></script>
    <script src="js/jquery-2.1.3.min.js"></script>
    <script src="//cdn.bootcss.com/jquery-weui/1.0.1/js/jquery-weui.min.js"></script>
    <script src="js/jquery.bpopup.js"></script>
    <script>
        var teamList = '<?php
            echo json_encode($team_list,JSON_UNESCAPED_UNICODE);
            ?>';
        teamList = eval('(' + teamList + ')');


    </script>
    <script>
        $(".tab_1 .item_1").on("click", function () {
            $(this).addClass("active").siblings(".item_1").removeClass("active");
            $('[data-to=' + $(this).attr("data-mid") + ']').addClass("active").siblings(".list").removeClass("active");
            $('[data-t=' + $(this).attr("data-mid") + ']').addClass("active-1").siblings(".tab-title").removeClass("active-1");
        });
        var uId = -1;
        var checkListKey = -1;
        $('.checkbtn').on('click',function () {
            checkListKey = $(this).attr('check-list-key');
            $('.head-img').attr('src',teamList[checkListKey]['headimg']);
            $('#realName').html(teamList[checkListKey]['fullname']);
            $('#procurement').html(teamList[checkListKey]['department']);
            $('#workNum').html(teamList[checkListKey]['employeeNumber']);
            $('#phone').html(teamList[checkListKey]['phone']);
            uId = teamList[checkListKey]['id'];
            $('.check-pop').bPopup({
                positionStyle:'fixed'
            })
        });
        $('.confirm').on('click',function () {
            if(uId != -1){
                $.ajax({
                    url: "./apiJoinDtc.php",
                    data:{
                        'allowJoin':1,
                        'uId':uId
                    },
                    type: "GET",
                    async: true,
                    beforeSend: function () {
                        $.showLoading('正在请求中..');
                        $('.check-pop').css('z-index','1999');
//                    $('.compete')
                    },
                    complete: function () {
                        $.hideLoading();
                    },
                    success: function ($data) {
                        window.location.reload();
                    }
                })
            }
        });
        $('.reject').on('click',function () {
            if(uId != -1) {
                $.ajax({
                    url: "./apiJoinDtc.php",
                    data: {
                        'allowJoin': -1,
                        'uId': uId
                    },
                    type: "GET",
                    async: true,
                    beforeSend: function () {
                        $.showLoading('正在请求中..');
                        $('.check-pop').css('z-index','1999');

//                    $('.compete')
                    },
                    complete: function () {
                        $.hideLoading();
                    },
                    success: function ($data) {
                        window.location.reload();
                    }
                })
            }
        });

        $('.manage').on('click',function () {
            $('.team-icon').addClass('delete');
            $('.manage').html("<p class='manage-text'>完成<br><span class='__span' style='font-size: 0.5rem'>FINISH</span></p>")
            $('.manage-btn').removeClass('manage');
            $('.manage-btn').addClass('finish');
        });
        $('body').on('click','.finish',function () {
            $('.team-icon').removeClass('delete');
            $('.manage-btn').addClass('manage');
            $('.manage').html("<p class='manage-text'>管理成员<br><span class='__span' style='font-size: 0.5rem'>MANAGE TEAM MEMBER</span></p>")
            $('.manage-btn').removeClass('finish ');

        });
        var u_id = -1;
        $('body').on('touchstart','.delete',function (){
            $('.delete-pop').fadeIn(500);
            u_id = $(this).attr('item-id');
            var list=$('.delete-pop .delete-box ul li');
            list.find('input').on('click',function () {
                var $reason=$('.other-reason').val();
                if(this.checked){
                    $(this).addClass('icon-gou');
                    $(this).parents().siblings('li').find('input').removeClass('icon-gou');
                    if(($(this).hasClass('reason'))&&$reason==''){
                        $('.other-reason').attr("disabled",false);
                        $('.btn-area button.sure').addClass('unset');
                    }
                    else {
                        $('.other-reason').val('');
                        $('.other-reason').attr("disabled",true);
                        $('.btn-area button.sure').removeClass('unset');
                    }

                }
                var checks=$('.delete-pop .delete-box ul li label input:checked');
                if (checks.size()==0){
                    $('.btn-area button.sure').addClass('unset');
                }

            });
        });
        $('.cancel').on('click',function (){
            $('.delete-pop').fadeOut(500);
        });
        $('.other-reason').on('keydown',function () {
            if ($('.other-reason').val()==''){
                $('.btn-area button.sure').addClass('unset');
            }else {
                $('.btn-area button.sure').removeClass('unset');
            }
        });
        $('.sure').on('click', function ()
        {
            if (!($(this).hasClass('unset'))) {
                var reason = $('.delete-pop .delete-box ul li label input:checked').parent().text();
                if($('.other-reason').val() != ''){
                    reason = reason+ ':' +$('.other-reason').val();
                }
                if (!($(this).hasClass('unset'))) {
                    $.ajax({
                        url: './apiDeletePlayer.php',
                        method: 'GET',
                        data: {'u_id': u_id, 'reason': reason},
                        dataType: 'JSON',
                        success: function () {
                            alert('成功');
                            window.location.reload();
                        },
                        error: function () {
                            alert('失败');
                            window.location.reload();
                        }
                    });
                    //去向的链接
                }
            }
        })
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
