<?php
require_once "./header.php";
$conn = connect_to_db();

$apiStartTime = getMicrotime();
$apiUrl = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
$loginIP = getClientIP();
$type = 'rank';

$admin = array(
    'o1bHns329rmsuQebWb6ADs-Hs_5A',
    'o1bHns4BRbnHNNu13AujmzGHe6l0',
    'o1bHnszkEjXP0-3JH8_wtO5sLbvs',
    'o1bHns8YAZSqzn-lq20Na7Lz7o8Q',
    'o1bHns75kRBqSEIvyPsyQwSrBCz4'
);

$headerLocation = ''; //跳转地址
$sql = "select * from dtc_join_user where openid = '$openid';";
$result = mysql_query($sql, $conn);
if (is_resource($result) && mysql_num_rows($result) != 0) {
    $joinUserRow = mysql_fetch_assoc($result);
    //进到首页，可简单判断，如果用户teamId不为-1且已确认开启任务且已绑定Nike+则留在本页面；其他情况统一交给join-choose处理
    if ($joinUserRow['teamId'] != -1) {
        if ($joinUserRow['is_confirm'] != 1 || $_SESSION['userInfo']['isBindNike'] != 1) {
            $headerLocation = 'join-choose.html';

        }
    } else {
        $headerLocation = 'join-choose.html';
    }
} else {
    echo "数据同步异常，请联系客服电话18514748838";
    exit();
}

if ($headerLocation != '') {
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
    foreach ($logArr as $insert_key => $insert_value) {
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
        <link rel="stylesheet" href="css/public.css"/>
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
                background: rgba(255, 255, 255, 0.8);
                box-shadow: 10px 10px 0 rgba(250, 106, 6, 0.8);

            }

            .teamname {
                width: 8rem;
                font-size: 0.8rem;
                margin-left: -1rem;
                text-align: center;
                border-bottom: none;
            }

            .rank .box .list a.name {
                width: 6rem;
                margin-left: -1.4rem;
                text-align: center;
                font-size: 1rem;
                border-bottom: none;
            }

            .rank .box .list a.name-1 {
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

            .num img {
                width: 50% !important;
            }

            .rank .box .list p {
                font-family: 'fornike365';
                font-size: 1.1rem;
                width: 5rem;
                text-align: right;
                word-break: break-all;
            }

            .tab-title-pop {
                display: flex;
                margin-left: 4.5rem;
                margin-top: 1rem;
                margin-right: 1rem;
                justify-content: space-between;
                /*  border-bottom: 1px solid #d8ba97;*/
            }

            .tab-title-pop ._p {
                font-size: 1.1rem;
                font-weight: bold;
                color: #fa6a0e;
                text-align: center;
                line-height: 0.8rem;
                border-bottom: 1px solid #f05f06;
            }

            .__span {
                font-size: 0.6rem;
                font-family: 'fornike365';
            }

            .team-member-totle span {
                display: block;
                white-space: nowrap;
                text-overflow: ellipsis;
                overflow: hidden;
            }

            .box-1 {
                /*height: 5rem;*/
                background: #ff6b02;
                border: 1px solid #fff;
                border-radius: 20px;
                margin: 2rem 1.5rem 1rem;
                padding: 1rem;
            }

            .box-1 table {
                font-size: 0.8rem;
                color: #fff;
                width: 100%;
                text-align: center;
            }

            .box-1 table th {
                font-size: 1rem;
                font-weight: normal;
                border-bottom: 1px solid #fff;
                padding: 2px;
            }

            .box-1 table td {
                padding: 2px;
            }
        </style>
    </head>
    <body>
    <div class="wrap rank">
        <?php
        $team_sql = "select COUNT(id) as count from dtc_join_user WHERE teamId>-1  AND teamId !='204'";
        $team_ret = mysql_query($team_sql, $conn);
        $team_row = mysql_fetch_assoc($team_ret);
        $num_all = $team_row['count'];

        $team_sql = "select COUNT(id) as count from dtc_join_user WHERE teamId>-1  AND teamId !='204' AND is_allow='1'";
        $team_ret = mysql_query($team_sql, $conn);
        $team_row = mysql_fetch_assoc($team_ret);
        $num_allow = $team_row['count'];

        $team_sql = "select COUNT(id) as count from dtc_join_user WHERE teamId>-1  AND teamId !='204' AND is_allow='0'";
        $team_ret = mysql_query($team_sql, $conn);
        $team_row = mysql_fetch_assoc($team_ret);
        $num_pend = $team_row['count'];

        $team_sql = "select COUNT(id) as count from dtc_join_user WHERE teamId>-1  AND teamId !='204' AND is_confirm=1";
        $team_ret = mysql_query($team_sql, $conn);
        $team_row = mysql_fetch_assoc($team_ret);
        $num_confirm = $team_row['count'];

        $team_sql = "select COUNT(id) as count from dtc_team_captain WHERE teamId>-1  AND teamId !='204' AND is_join=1";
        $team_ret = mysql_query($team_sql, $conn);
        $team_row = mysql_fetch_assoc($team_ret);
        $captain_num= $team_row['count'];

        $team_sql = "select COUNT(id) as count from dtc_team WHERE teamId>-1  AND teamId !='204' AND teamName !='未命名'";
        $team_ret = mysql_query($team_sql, $conn);
        $team_row = mysql_fetch_assoc($team_ret);
        $team_num= floor($team_row['count']/2);
        ?>
        <div class="box-1">
            <table>
                <tr>
                    <th>加入人数</th>
                    <th>审核通过</th>
                    <th>未审核</th>
                </tr>
                <tr>
                    <td><?php echo $num_all; ?></td>
                    <td><?php echo $num_allow; ?></td>
                    <td><?php echo $num_pend; ?></td>
                </tr>
                <tr>
                    <th>确认参与</th>
                    <th>加入队长</th>
                    <th>已取队名</th>
                </tr>
                <tr>
                    <td><?php echo $num_confirm; ?></td>
                    <td><?php echo $captain_num; ?></td>
                    <td><?php echo $team_num; ?></td>
                </tr>
            </table>
        </div>
        <div class="box">
            <div class="tab">
                <div class="item active" style="width: 100%" data-mid="2">团队列表
                    <p>TEAM‘S LIST </p>
                </div>
            </div>

            <div class="tab-title active-1" data-t="2" style="margin-left: 1rem">
                <p>团队名称<br><span class="__span">TEAM NAME</span></p>
                <p>商店名<br><span class="__span">STORE NAME</span></p>
                <p>队长<br><span class="__span">CAPTAIN NAME</span></p>
                <p>审/未<br><span class="__span">NUMBER</span></p>
            </div>
            <ul data-to="2" class="list detail active" style="padding-left: 1rem;">
                <?php
                $team_sql = "select * from dtc_team WHERE teamId>-1  AND teamId !='204' GROUP BY teamId";
                $team_ret = mysql_query($team_sql, $conn);
                $count = 0;
                $team_arr = array();
                while ($team_row = mysql_fetch_assoc($team_ret)) {
                    $count++;
                    $teamId = $team_row['teamId'];
                    $query = "SELECT count(id) as count FROM dtc_join_user WHERE teamId = '$teamId'";
                    $result = mysql_query($query, $conn);
                    $team_count = mysql_fetch_assoc($result);
                    $team_row['all'] = $team_count['count'];
                    $query = "SELECT count(id) as count FROM dtc_join_user WHERE teamId = '$teamId' AND is_allow='1'";
                    $result = mysql_query($query, $conn);
                    $team_count = mysql_fetch_assoc($result);
                    $team_row['join'] = $team_count['count'];
                    $query = "SELECT count(id) as count FROM dtc_join_user WHERE teamId = '$teamId' AND is_allow='0'";
                    $result = mysql_query($query, $conn);
                    $team_count = mysql_fetch_assoc($result);
                    $team_row['allow'] = $team_count['count'];

                    $query = "SELECT count(id) as count FROM dtc_team_captain WHERE teamId = '$teamId' AND is_join=1";
                    $result = mysql_query($query, $conn);
                    $team_count = mysql_fetch_assoc($result);
                    $team_row['captain'] = $team_count['count'];
                    $team_name = array();
                    unset($team_name);
                    $query = "SELECT teamNameEN FROM dtc_team WHERE teamId = '$teamId'";
                    $result = mysql_query($query, $conn);
                    while ($team = mysql_fetch_assoc($result)) {
                        $team_name[] = $team['teamNameEN'];
                    }
                    $team_row['team_nameEN']=implode("/", $team_name);
                    $team_arr[] = $team_row;
                }
                //按人数排序
                $team_arr = arr_sort($team_arr,'all','desc');
                function arr_sort($array,$key,$order="asc"){ //asc是升序 desc是降序
                    $arr_nums=$arr=array();
                    foreach($array as $k=>$v){
                        $arr_nums[$k]=$v[$key];
                    }
                    if($order=='asc'){
                        asort($arr_nums);
                    }else{
                        arsort($arr_nums);
                    }
                    foreach($arr_nums as $k=>$v){
                        $arr[$k]=$array[$k];
                    }
                    return $arr;
                }
                foreach($team_arr as $value){
                    ?>
                    <li item-id="<?php echo $value['teamId']; ?>">
                        <a class="name-1 pop-title" style="margin-left: 0;width: 6rem">
                            <?php echo $value['teamName'] ?>队
                        </a>
                        <p class="team-name-en" style="text-align: center"><?php echo $value['team_nameEN']; ?></p>
                        <p style=" text-align: center;width: 3rem"><?php echo $value['captain']; ?></p>
                        <p style=" text-align: center;width: 3rem"><?php echo $value['join']."/".$value['allow']; ?></p>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <script id="list_team" type="text/html">

        {{each retArray as row i}}
        <li class="team-member-list-item" style="{{if row.is_join !=1}}color:#888{{/if}}">
            <div class="team-member-list-div"><a class="name-2">{{row.fullname}}</a></div>
            <p class="team-member-totle" style="text-align: center;margin-right: 0"><span>{{row.storeEN}}
            </p>
            <p style="text-align: center;width: 2.5rem">{{if row.is_captain==1}}队长{{else}}队员{{/if}}</p>
        </li>
        {{/each}}
    </script>
    <aside class="team-member">
        <div class="team-member-box">
            <div class="title"><a class="close"></a>
                <p id='pop-title'></p>
                <span id="teamNameEN" style="font-size: 1.2rem;font-family: 'fornike365';"></span>

            </div>
            <div class="tab-title-pop" style="margin-left: 1rem;">
                <p class="_p" style="width: 6rem">团队成员<br><span class="__span">TEAM MEMBER</span></p>
                <p class="_p" style="width:5rem;">所属商店<br><span class="__span">STORE</span></p>
                <p class="_p" style="width:2.5rem;">身份<br><span class="__span">IDENTITY</span></p>
            </div>
            <ul class="team-member-list" id="popup_teamList" style="padding:0 1rem">

            </ul>
        </div>
    </aside>
    </body>
    <script>

        template.helper('formatKm', function (total, type) {
            return sprint
        });

        $(".tab .item").on("click", function () {
            $(this).addClass("active").siblings(".item").removeClass("active");
            $('[data-to=' + $(this).attr("data-mid") + ']').addClass("active").siblings(".list").removeClass("active");
            $('[data-t=' + $(this).attr("data-mid") + ']').addClass("active-1").siblings(".tab-title").removeClass("active-1");
        });
        $('.detail li,.teamname').on('click', function () {
            var team_name = $(this).find('.pop-title').html();
            var team_nameEN = $(this).find('.team-name-en').html();

            $('#pop-title').text(team_name);
            $('#teamNameEN').text(team_nameEN);

            //先获取点击的是哪一个队伍的id
            var team_id = $(this).attr('item-id');
            //先从api获取数据
            $.ajax({
                url: "apiAdmin.php?teamID=" + team_id,
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
                    //console.log($data);
                    var _html = template('list_team', $data);
                    $('#popup_teamList').html(_html);

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