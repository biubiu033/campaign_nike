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
$pageType = 'apiLikeTask';

$err = '';
$errno = -1;
$returnStr = ''; //返回参数

if (isset($_GET['task_id']) && $_GET['task_id'] != ''&&isset($_GET['like']) && $_GET['like'] != '') {
    $t_id = mysql_real_escape_string($_GET['task_id']);
    $like = $_GET['like'];
    //先查找
    $sql1 = "select count(*) as count from dtc_task where like_openid = '$openid' and task_id = $t_id AND delete_flag = '0'";
    $result = mysql_query($sql1, $conn);
    $row = mysql_fetch_assoc($result);
    if($like){
        if($row['count'] > 0)
        {
            $err = '已经喜欢过该项目了,不能重复喜欢';
            $errno = -1;
        }else{
            $insert = "insert into dtc_task (task_id,like_openid,create_time) VALUE ($t_id,'{$openid}',now())";
            mysql_query($insert, $conn);
            $err = '成功';
            $errno = 0;
        }
    }else{
        if($row['count'] == 0)
        {
            $err = '未喜欢过该项目，不能取消点赞';
            $errno = -1;
        }else{
            $delete = "UPDATE dtc_task SET delete_flag ='1' WHERE task_id = '$t_id' AND like_openid = '{$openid}'";
            mysql_query($delete, $conn);
            $err = '成功';
            $errno = 0;
        }
    }



} else {

    $err = '参数错误';
    $errno = -1;
}

echoAjax($err,$errno);
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