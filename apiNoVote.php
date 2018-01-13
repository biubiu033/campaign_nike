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
$pageType = 'apiNoVote';

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = mysql_real_escape_string($_GET['id']);

    $sql = "select count(id) as count from  dtc_theme_vote where openid='$openid'";
    $ret = mysql_query($sql, $conn);
    $row = mysql_fetch_assoc($ret);

    if($row['count']>0 && $row['count']<10){
        //判断是否投票
        $sql = "select * from  dtc_theme_vote where openid='$openid' AND t_id='$id'";
        $ret = mysql_query($sql, $conn);
        if (is_resource($ret) && mysql_num_rows($ret)>0) {
            //插入数据
            $sql = "DELETE FROM dtc_theme_vote  WHERE t_id = '$id' AND openid='$openid'";
            mysql_query($sql, $conn);
            $errcode = 1;
            $errmsg = "取消投票成功";
        }else{
            $errcode = -1;
            $errmsg = "您已经为他投过票了<span>YOU HAVNT VOTED FOR HIM</span>";
        }
    }else{
        $errcode = -1;
        $errmsg = "你已投过十次票<span>YOU DONT HAVE CHANCE</span>";
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