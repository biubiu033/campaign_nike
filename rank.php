<?php
require_once "./header.php";
$conn = connect_to_db();

$apiStartTime = getMicrotime();
$apiUrl = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
$loginIP = getClientIP();
$pageType = 'rank';

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
?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
        <!--    引入自己的css-->
        <link rel="stylesheet" href="css/reset.css">
        <link rel="stylesheet" href="css/public.css?a=123"/>
        <link rel="stylesheet" href="//at.alicdn.com/t/font_5j0bahx9isuoko6r.css"/>
        <!--引入自己的Js-->
        <script src="js/responsive.js"></script>
        <!--引用Jquery-->
        <script src="js/jquery-2.1.3.min.js"></script>
        <script src="js/template.js"></script>
        <script src="js/jquery.bpopup.js"></script>
        <title>里享</title>
        <style>

            .rank .box {
                padding: 1.5rem 1.5rem 1.5rem 0.8rem;
                height: 27rem;
                margin: 0.6rem 1rem 1rem;
                background: rgba(255,255,255,0.8);
                box-shadow: 10px 10px 0 rgba(250, 106, 6, 0.8);

            }
            .teamname{
                width: 8rem;
                font-size: 0.8rem;
                margin-left: -1rem;
                text-align: center;
                border-bottom: none;
            }
            .rank .box .list a.name{
                width: 6rem;
                /*margin-left: -1.4rem;*/
                text-align: center;
                font-size: 1rem;
                border-bottom: none;
            }
            .rank .box .list a.name-1{
                margin-left: -1rem;
                width: 9rem;
                text-align: left;
                text-decoration: underline;
            }
            .rank .box .list .num {
                font-family: 'fornike365';
                font-style: oblique;
                font-size: 1.3rem;
                display: block;
                width: 4rem;
                height: 2.5rem;
                line-height: 2.5rem;
                text-align: center;
            }
            .num img{
                width: 50%!important;
            }
            .rank .box .list p{
                font-family: 'fornike365';
                font-size: 1.1rem;
                width: 5rem;
                text-align: right;
                word-break:break-all;
            }
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
            .__span{
                font-size: 0.6rem;
                font-family: 'fornike365';
            }
            .home{
                display: block;
                width: 2.0rem;
                height: 2.0rem;
                float: left;
                margin-top: 1.0rem;
            }
        </style>
    </head>
    <body style="background-color: #f05f06;">
    <div class="wrap rank">
        <a class="home" href="main.php"><img src="./img/home.png" width="100%"></a>
        <div class="title"></div>
        <div class="box">
            <div class="tab">
                <div class="item active" data-mid="1">个人里程排行<i class="iconfont icon-shangxiajiaohuan"></i>
                    <p>INDIVIDUAL’S MILEAGE RANKING</p></div>
                <div class="item" data-mid="2">团队里程排行<i class="iconfont icon-shangxiajiaohuan"></i>
                    <p>TEAM‘S MILEAGE RANKING </p></div>
            </div>
            <div class="tab-title active-1" data-t="1">
                <p>姓名<br><span class="__span">NAME</span></p>
                <p>团队<br><span class="__span">TEAM</span></p>
                <p>公里数<br><span class="__span">KM</span></p>
            </div>
            <div class="tab-title " data-t="2">
                <p>团队<br><span class="__span">TEAM</span></p>
                <p>日人均（公里）<br><span class="__span">KM/HEAD/DAY</span></p>
            </div>
            <ul data-to="1" class="list active">
                <?php
                $teamList = array();
                $query = "SELECT `teamName`,`teamId` FROM dtc_team where teamId != 240;";
                $result = mysql_query($query, $conn);
                while ($row = mysql_fetch_assoc($result)){
                    $t_id = $row['teamId'];
                    $teamList[$t_id]['teamName'] = $row['teamName']."队";
                    $teamList[$t_id]['total'] = 0;  //总公里数
                    $teamList[$t_id]['totalDays'] = 0;  //总人天数
                    $teamList[$t_id]['memberNum'] = 0; //团队人数
                    $teamList[$t_id]['avg'] = 0; //日人均数

                }
                $userList = array();
                $query = "SELECT `fullname`,`openid` FROM dtc_join_user;";
                $result = mysql_query($query, $conn);
                while ($row = mysql_fetch_assoc($result)){
                    $t_openid = $row['openid'];
                    $userList[$t_openid] = $row['fullname'];
                }
                //            print_r($userList);
                $yMemberList = array();
                $sql = "SELECT `openid`, sum(`long`) as longTotal, min(created) as created,`teamId` FROM yiqipao_member_project WHERE `pid`= {$dtcPid} and teamId > 100 and teamId != 240 group by openid ORDER BY sum(`long`) DESC, id asc;";
                $ret = mysql_query($sql, $conn);
                $count = 0;

                while ($row = mysql_fetch_assoc($ret)) {
                    $t_id = $row['teamId'];
                    $t_openid = $row['openid'];
                    $row['teamName'] = $teamList[$t_id]['teamName'];
                    $row['fullname'] = $userList[$t_openid];
                    $yMemberList[] =$row;
//                print_r($row);
                    $count++;
                    if($count<101){
                        ?>
                        <li>
                    <span class="num"><?php
                        switch ($count){
                            case 1:echo "<img src='img/icon-no1.png' >";break;
                            case 2:echo "<img src='img/icon-no2.png' >";break;
                            case 3:echo "<img src='img/icon-no3.png'>";break;
                            default: echo "NO.".$count;
                        }
                        ?></span>
                            <a class="name"><?php echo  $row['fullname'];
                                ?></a>
                            <a class="teamname" item-id="<?php echo $row['teamId'] ?>" style="text-decoration: underline;"><text class="pop-title"><?php echo $row['teamName'];
                                    ?></text></a>
                            <p><?php  echo round($row['longTotal'], 0);
                                ?></p></li>

                    <?php }}?>
            </ul>
            <ul data-to="2" class="list detail">
                <?php
                $count = 0;
                foreach ($yMemberList AS $key => $value){
                    $t_id = $value['teamId'];
                    //计算参与项目经过了多少天
                    //$days = (float)(time()-$value['created']) / 86400;
                    $date1 = date("Y-m-d", time());
                    $date2 = date("Y-m-d", $value['created']-86400);
                    $days = DateDiff($date1, $date2, 'd');
                    $teamList[$t_id]['total']+=$value['longTotal'];
                    $teamList[$t_id]['totalDays']+=$days;
                    $teamList[$t_id]['avg'] = round($teamList[$t_id]['total'] / $teamList[$t_id]['totalDays'], 2);
                    $teamList[$t_id]['memberNum'] ++;
                }

                $teamList = array_sort($teamList, 'avg', $type = 'desc');
                foreach ($teamList AS $key => $value) {
                    $count++;
                    ?>
                    <li item-id="<?php echo $key; ?>">
                    <span class="num"><?php
                        switch ($count){
                            case 1:echo "<img src='img/icon-no1.png'>";break;
                            case 2:echo "<img src='img/icon-no2.png' >";break;
                            case 3:echo "<img src='img/icon-no3.png' >";break;
                            default: echo "NO.".$count;
                        }
                        ?></span>
                        <a class="name-1 pop-title"><?php echo $value['teamName'];
                            ?></a>
                        <p style=" text-align: left;"><?php  echo round($value['avg'],2);
                            ?></p></li>
                <?php } ?>
            </ul>
        </div>
    </div>
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
    </body>
    <script>

        template.helper('formatKm', function(total, type) {
            return sprint
        });

        $(".tab .item").on("click", function () {
            $(this).addClass("active").siblings(".item").removeClass("active");
            $('[data-to=' + $(this).attr("data-mid") + ']').addClass("active").siblings(".list").removeClass("active");
            $('[data-t=' + $(this).attr("data-mid") + ']').addClass("active-1").siblings(".tab-title").removeClass("active-1");
        });
        $('.detail li,.teamname').on('click', function () {
            var team_name = $(this).find('.pop-title').html();
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
    'result' => mysql_real_escape_string(json_encode_cn($teamList)),
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