<?php
/**
 * Created by PhpStorm.
 * User: Dingjinrong
 * Date: 17/6/28
 * Time: 上午10:29
 * 用途:用于删除队员
 */
require_once "./header.php";
require_once "../../../php/funcs.php";
$conn = connect_to_db();
header("Content-Type:text/json charset=utf-8");

$apiStartTime = getMicrotime();
$apiUrl = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
$loginIP = getClientIP();
$pageType = 'apiDeletePlayer';
//需要验证接受手机号
//匹配手机号对应的团队信息情况
//将匹配对应关系写入参赛表
//返回成功
$err = "";
$errno = -1;
$returnStr = ''; //返回参数

//必须是队长才可以删除
$sql3 = "select is_captain,dtc_team.teamId from dtc_join_user,dtc_team where dtc_team.teamId=dtc_join_user.teamId and dtc_join_user.openid='$openid';";
$result3 = mysql_query($sql3, $conn);
$row3 = mysql_fetch_assoc($result3);
$is_captain = $row3['is_captain'];
$deal_teamId = $row3['teamId'];

if (isset($_GET['u_id']) && isset($_GET['reason']) && $is_captain) {
    $u_id = mysql_real_escape_string($_GET['u_id']);
    $reason = mysql_real_escape_string($_GET['reason']);
    //查出uid 对应的openid
    $sql = "select * FROM dtc_join_user where id = '$u_id'";

    $ret = mysql_query($sql,$conn);
    if(is_resource($ret) && mysql_num_rows($ret)){
        $row = mysql_fetch_assoc($ret);
        $u_openid = $row['openid'];
    }else{
        echoAjax('id 出错', -1);
    }

    //更新 teamId
    $update = "UPDATE dtc_join_user a,yiqipao_member b 
            SET a.teamId ='-1', a.delete_reason='$reason',a.delete_captain = '$openid',a.delete_time=NOW(),a.is_allow='0' ,b.teamId = '-1' 
    WHERE a.id = '$u_id' AND a.teamId = '$deal_teamId' AND a.openid = b.openid ";

    mysql_query($update,$conn);
    // 更新memberid


    $err = '删除成功';
    $errno = 1;
}
else {
    $err = '访问受限';
    $errno = -1;
}


echoAjax($err, $errno);
//记录页面请求日志
//记录页面请求日志
$apiEndTime = getMicrotime();
$fetchTime = intval(($apiEndTime - $apiStartTime) * 1000);
$logArr = array(
    'openid' => "$openid",
    'type' => $pageType,
    'ip' => $loginIP,
    'url' => $apiUrl,
    'result' => mysql_real_escape_string($returnStr),
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




function echoAjax($err, $errno)
{

    $retrunAjax = array('err' => $err, 'errno' => $errno);
    echo json_encode_cn($retrunAjax);
    exit();
}


?>
