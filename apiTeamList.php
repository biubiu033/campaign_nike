<?php
/**
 * Created by PhpStorm.
 * User: Dingjinrong
 * Date: 17/6/28
 * Time: 上午11:32
 */
require_once "./header.php";
require_once "../../../php/funcs.php";
$conn = connect_to_db();
header("Content-Type:text/json charset=utf-8");

$apiStartTime = getMicrotime();
$apiUrl = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
$loginIP = getClientIP();
$pageType = 'apiTeamList';


$teamId = $_GET['teamID'];
$err = '';
$errno = -1;
$returnStr = ''; //返回参数

$retArray = array();

$userList = array();
$query = "SELECT `fullname`,`openid`,is_captain FROM dtc_join_user ";
$result = mysql_query($query, $conn);
while ($row = mysql_fetch_assoc($result)){
    $t_openid = $row['openid'];
    $userList[$t_openid]['fullname'] = $row['fullname'];
    $userList[$t_openid]['is_captain'] = $row['is_captain'];

}

$list = array();

$sql = "SELECT `openid`, sum(`long`) as longTotal, min(created) as created,`teamId` FROM yiqipao_member_project WHERE `pid`= {$dtcPid} AND teamId = '$teamId' group by openid ORDER BY sum(`long`) DESC, id asc;";
$ret = mysql_query($sql, $conn);
$count = 0;

$ret = mysql_query($sql, $conn);
while ($row = mysql_fetch_assoc($ret)) {
    $t_id = $row['teamId'];
    $t_openid = $row['openid'];
    $row['fullname'] = $userList[$t_openid]['fullname'];
    $row['is_captain'] = $userList[$t_openid]['is_captain'];
    $row['longTotal']=round($row['longTotal'], 0);
    $list[] = $row;
}

/**
 * 获取TeamNameEN
 */
$sql3 = "SELECT teamNameEN FROM dtc_team WHERE teamId = '$teamId'";
$result3 = mysql_query($sql3, $conn);
$teamNameEN = '';
while ($row3 = mysql_fetch_assoc($result3))
{
    $teamNameEN .= $row3['teamNameEN'] . ' / ';
}
$teamNameEN = trim($teamNameEN, ' / ');
$err = $sql;
$errno = 1;
$retArray = $list;
$retuen_json = json_encode_cn(compact('err', 'errno','teamNameEN', 'retArray'));
echo $retuen_json;

//记录页面请求日志
$apiEndTime = getMicrotime();
$fetchTime = intval(($apiEndTime - $apiStartTime) * 1000);
$logArr = array(
    'openid' => "$openid",
    'type' => $pageType,
    'ip' => $loginIP,
    'url' => $apiUrl,
    'result' => mysql_real_escape_string($retuen_json),
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


exit();

?>