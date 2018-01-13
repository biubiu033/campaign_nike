<?php
/**
 * Created by PhpStorm.
 * User: Dingjinrong
 * Date: 17/6/16
 * Time: 下午6:33
 */
require_once "./header.php";
require_once "../../../php/funcs.php";
$conn = connect_to_db();
header("Content-Type:text/json charset=utf-8");


$apiStartTime = getMicrotime();
$apiUrl = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
$loginIP = getClientIP();
$pageType = 'apiTaskOneMarathon';

$msg = '';
$errCode = '';
$returnStr = ''; //返回参数

if (isset($_GET['type']) && $_GET['type'] != '') {
    $type = mysql_real_escape_string($_GET['type']);
    //先查找
    $sql1 = "select * from dtc_taskone_marathon where openid = '$openid'";
    $result = mysql_query($sql1, $conn);
    if(is_resource($result) && mysql_num_rows($result) == 1)
    {
        $msg = '已经选择过项目了';
        $errCode = '-1';
    }else{
        $insert = "insert into dtc_taskone_marathon (`openid`,`marathon_project`,create_time) VALUE ('$openid','{$type}',now())";
        mysql_query($insert, $conn);
        $msg = '项目选择成功';
        $errCode = '1';
    }

} else {
    $msg = '参数错误';
    $errCode = '-2';
}

$return = array('msg' => $msg, 'errCode' => $errCode);
echo json_encode_cn($return);

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





?>