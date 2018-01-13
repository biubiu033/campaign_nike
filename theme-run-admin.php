<?php
require_once "./header.php";
$conn = connect_to_db();

$apiStartTime = getMicrotime();
$apiUrl = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
$loginIP = getClientIP();
$type = 'theme-run-admin';

$admin = array(
    'o1bHns329rmsuQebWb6ADs-Hs_5A',
    'o1bHns4BRbnHNNu13AujmzGHe6l0',
    'o1bHnszkEjXP0-3JH8_wtO5sLbvs',
    'o1bHns8YAZSqzn-lq20Na7Lz7o8Q',
    'o1bHns75kRBqSEIvyPsyQwSrBCz4',
    'o1bHns32KVIvreU1W8aAoK5KBZvw',
    'o1bHns1mnrp_4wQHhxMTDlBQl0Mk'
);
if( !in_array($openid,$admin)){
    echo '没有权限';
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
    <link rel="stylesheet" href="css/theme-run-heros.css?a=123"/>
    <link rel="stylesheet" href="css/swiper-3.4.2.min.css">
    <link rel="stylesheet" href="//at.alicdn.com/t/font_5j0bahx9isuoko6r.css"/>
    <!--引入自己的Js-->
    <script src="js/responsive.js"></script>
    <!--引用Jquery-->
    <script src="js/jquery-2.1.3.min.js"></script>
    <title>里享</title>
</head>
<body>
<div class="wrap theme-run-heros">
    <div class="header">
        <div class="header-text">
            <h1>挑战1</h1>
            <span>CHALLENGE 1</span>
        </div>
<!--    todo：暂时隐藏    <a class="vote" href="vote.php">投票<br><span class="__span">VOTE</span></a>-->
    </div>
    <div class="box">
        <div class="tab">
            <div class="item" data-mid="1">团队票数排行<i class="iconfont icon-shangxiajiaohuan"></i>
                <p>TEAM RANKINGS</p></div>
<!--            <div class="item" data-mid="2">个人当期里程排行<i class="iconfont icon-shangxiajiaohuan"></i>
                <p>INDIVIDUAL RANKINGS</p></div>-->
        </div>
        <ul data-to="1" class="list active">
            <?php
            $sql = "select * from (select id,(@rowNum:=@rowNum+1) as rank,t_id,t_name,slogan,theme_img,votes,create_time,status from dtc_theme_run,(select (@rowNum :=0)) b WHERE dtc_theme_run.status = 1 order by dtc_theme_run.votes desc ) u";
            $ret = mysql_query($sql, $conn);
            $count = 0;
            while ($row = mysql_fetch_assoc($ret)) {
                $count++;
                ?>
                <li style="height: 30rem">
                    <div class="imfo">
                        <p>NO.<?php echo $row['rank']; ?><span><?php
                                $teamId = $row['t_id'];
                                $query = "SELECT a.teamName,a.teamNameEN,a.department ,b.`name`
                                            FROM dtc_team a LEFT JOIN dtc_team_captain b
                                            ON a.department = b.department 
                                            WHERE a.teamId = '$teamId'; ";
                                $result = mysql_query($query,$conn);

                                while ($capRow = mysql_fetch_assoc($result)){
                                    $team[] = $capRow;
                                }
                                if($row['t_name'] != $team[0]['teamName']){
                                    $upload = "UPLOAD dtc_theme_run SET t_name = '{$team[0]['teamName']}' WHERE t_id = '$teamId";
                                    mysql_query($upload,$conn);
                                }
                                echo $team[0]['teamName'];
                                ?></span></p>
                        <p class="count"><?php echo $row['votes']; ?>票</p>
                    </div>
                    <div class="updataimg">
                        <img  style="height: 15rem" src="<?php echo $row['theme_img']; ?>">
                    </div>
                    <div class="describe" style="height: 2.5rem;overflow: hidden">
                        <?php echo $row['slogan']; ?>
                    </div>
                    <a class="seemore  btn-style" data-mid="<?php echo $count; ?>">查看全文<i class="iconfont icon-arrowDown"></i><br><span>FULL MESSAGE</span></a>
                    <a class="putawy btn-style" style="display: none">收起<i class="iconfont icon-jiantou-copy"></i><br><span>PUT AWAY</span></a>
                    <?php
                        foreach ($team AS $key => $data){
                            $department = $data['department'];
                            $teamNameEn = $data['teamNameEN'];
                            $captain = $data['name'];
                            echo "店号：$department  EN：$teamNameEn  队长：$captain </br>";
                         }
                        $team=array();
                    ?>
                </li>
            <?php } ?>
        </ul>
        <!--<ul data-to="2" class="list">
            <li>
                <div class="imfo">
                    <p>NO.1 <span>雷鸟队</span></p>
                    <p class="count">12票</p>
                </div>
                <img class="updataimg" src="img/theme-run-heros-img.png">
                <div class="describe" style="height: 2.5rem;overflow: hidden">
                    我们的路线是只皮皮虾。
                    随时随地带我们跑起来。
                </div>
                <a class="seemore btn-style" data-mid="3">查看全文<i class="iconfont icon-arrowDown"></i><br><span>FULL MESSAGE</span></a>
                <a class="putawy btn-style" style="display: none">收起<i class="iconfont icon-jiantou-copy"></i><br><span>PUT AWAY</span></a>
            </li>
            <li>
                <div class="imfo">
                    <p>NO.2 <span>火箭队</span></p>
                    <p class="count">96票</p>
                </div>
                <img class="updataimg" src="img/theme-run-heros-img.png">
                <div class="describe" style="height: 2.5rem;overflow: hidden">
                    我们的路线是只皮皮虾。
                    随时随地带我们跑起来。
                    我们的路线是只皮皮虾。
                    随时随地带我们跑起来。
                </div>
                <a class="seemore btn-style" data-mid="3">查看全文<i class="iconfont icon-arrowDown"></i><br><span>FULL MESSAGE</span></a>
                <a class="putawy btn-style" style="display: none">收起<i class="iconfont icon-jiantou-copy"></i><br><span>PUT AWAY</span></a>
            </li>
        </ul>-->
    </div>

</div>
<div class="popup-share" style="display: none;width: 100%;">
    <img style="width: 26.666666rem;height: 43rem;" src="img/pop-lock.png" >
</div>

<script src="js/jquery.bpopup.js"></script>
<script>
   $('.seemore').on("click", function () {
        $(this).parent().children('.describe').css({
            "height": "auto",
            "overflow": "auto"
        });
        $(this).hide();
        $(this).parent().children('.putawy').show();
    });
    $('.putawy').on('click',function () {
        $(this).parent().children('.describe').css({
            "height": "2.5rem",
            "overflow": "hidden"
        });
        $(this).parent().children('.seemore').show();
        $(this).hide();
    });
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
foreach ($logArr as $insert_key => $insert_value) {
    $insertkeysql .= $dot . $insert_key;
    $insertvaluesql .= $dot . '\'' . $insert_value . '\'';
    $dot = ', ';
}
$sql1 = 'insert into dtc_api_logs (' . $insertkeysql . ') values (' . $insertvaluesql . ')';
mysql_query($sql1, $conn);