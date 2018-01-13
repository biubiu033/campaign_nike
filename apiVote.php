<?php
/**
 * Created by PhpStorm.
 * User: Dingjinrong
 * Date: 17/6/7
 * Time: 下午4:59
 */
require_once "header.php";
$conn = connect_to_db();

$apiStartTime = getMicrotime();
$apiUrl = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
$loginIP = getClientIP();
$pageType = 'apiVote';


if (isset($_GET['id']) && !empty($_GET['id'])
) {
    $id = mysql_real_escape_string($_GET['id']);

    $sql = "select count(id) as count from  dtc_theme_vote where openid='$openid'";
    $ret = mysql_query($sql, $conn);
    $row = mysql_fetch_assoc($ret);
    if($row['count']<10){
        //判断重复投票
        $sql = "select * from  dtc_theme_vote where openid='$openid' AND t_id='$id'";
        $ret = mysql_query($sql, $conn);
        if (!is_resource($ret) || !mysql_num_rows($ret)) {
            //插入数据
            $sql = "INSERT INTO dtc_theme_vote (t_id,openid,status,create_time) VALUES ('$id','$openid',0,now())";
            mysql_query($sql, $conn);

            //判断票数是否到10,更新票数
            $sql = "select count(id) as count from  dtc_theme_vote where openid='$openid'";
            $ret = mysql_query($sql, $conn);
            $row = mysql_fetch_assoc($ret);
            if($row['count']>=10){
                $sql = "update dtc_theme_vote set status=1,enable_time=now() where openid='$openid'";
                mysql_query($sql, $conn);
                $sql = "select * from  dtc_theme_vote where openid='$openid' AND status=1";
                $ret = mysql_query($sql, $conn);
                while($row = mysql_fetch_assoc($ret)){
                    //更新票数
                    $teamId = $row['t_id'];
                    $sql_up = "update dtc_theme_run set votes=votes+1 where t_id='$teamId'";
                    mysql_query($sql_up, $conn);
                }
            }
            $errcode = 1;
            $errmsg = "投票成功";
        }else{
            $errcode = -1;
            $errmsg = "您已经为他投过票了<span>YOU HAD VOTED FOR HIM</span>";
        }
    }else{
        $errcode = -1;
        $errmsg = "投票次数用完<span>RUN OUT OF VOTES</span>";
    }
} else {
    $errcode = -1;
    $errmsg = "参数错误<span>PARAMETER ERROR</span>";
}

$resArr = array("errcode" => $errcode, "errmsg" => $errmsg);
$resJson = json_encode_cn($resArr);
echo $resJson;

//记录页面请求日志
$apiEndTime = getMicrotime();
$fetchTime = intval(($apiEndTime - $apiStartTime) * 1000);
$logArr = array(
    'openid' => "$openid",
    'type' => $pageType,
    'ip' => $loginIP,
    'url' => $apiUrl,
    'result' => mysql_real_escape_string($resJson),
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