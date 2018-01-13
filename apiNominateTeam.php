<?php
require_once "./header.php";
require_once "../../../php/funcs.php";
header("Content-Type:text/json charset=utf-8");


$apiStartTime = getMicrotime();
$apiUrl = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
$loginIP = getClientIP();
$pageType = 'apiNominateTeam';

if (isset($_GET['openid']) && isset($_GET['token']) && isset($_GET['unixtime']) && isset($_GET['teamName']) && isset($_GET['teamId']) )
{
    $openid = mysql_real_escape_string(trim($_GET['openid']));
    $token = $_GET['token'];
    $unixtime = $_GET['unixtime'];
    $key = 'jyj';
    $checkToken = md5($openid . $key . $unixtime);
    $teamName = $_GET['teamName'];
    $teamId = $_GET['teamId'];
    $timePass = time() - (int)$unixtime;

    if ($timePass > 2660)
    {
        $resultOK = false;
        $resultMsg = "链接已失效";
    } elseif ($token != $checkToken)
    {
        $resultOK = false;
        $resultMsg = "链接不正确";
    } else{
        $query = "SELECT is_captain FROM dtc_join_user WHERE openid = '$openid'";
        $is_captain_res = mysql_query($query,$conn);
        $is_captain_row = mysql_fetch_assoc($is_captain_res);
        $is_captain = $is_captain_row['is_captain'];
        if(isset($is_captain) && $is_captain){
            $resultOK = true;
            $query = "UPDATE dtc_team SET teamName = '$teamName',is_input = is_input+1 WHERE teamId = '$teamId'";
            mysql_query($query,$conn);
        }else{
            $resultOK = false;
            $resultMsg = "权限错误";
        }
    }
}
else{
    $resultOK = false;
    $resultMsg = "链接不正确";
}

//ob_clean(); //清除之前输出//-------------------------------------------------------------------------------

if ($resultOK == false)
{
    $result = array("errcode" => -1, "errmsg" => $resultMsg);
    $kmResult = json_encode_cn($result);
} else
{
    $result = array("errcode" => 0, "errmsg" => 'success');
    $kmResult = json_encode_cn($result);
    //$kmResult = json_encode_cn($userinfo);
}
echo $kmResult;

//记录页面请求日志
$apiEndTime = getMicrotime();
$fetchTime = intval(($apiEndTime - $apiStartTime) * 1000);
$logArr = array(
    'openid' => "$openid",
    'type' => $pageType,
    'ip' => $loginIP,
    'url' => $apiUrl,
    'result' => mysql_real_escape_string($kmResult),
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

/**
 * Created by PhpStorm.
 * User: a7849
 * Date: 2017/6/26
 * Time: 17:55
 */
