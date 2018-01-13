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
$query = "SELECT `name`,`department`,`is_join` FROM dtc_team_captain WHERE teamId = '$teamId'";
$result = mysql_query($query, $conn);
while ($row = mysql_fetch_assoc($result)){
    $t_openid = trim(strtolower($row['name']));
    $userList[$t_openid] = array('fullname' => $row['name'],'is_join'=>$row['is_join'],'is_captain'=>1,'department'=>$row['department']);
}
$query = "SELECT `fullname`,`openid`,`is_captain`,`department` FROM dtc_join_user WHERE teamId = '$teamId'";
$result = mysql_query($query, $conn);
while ($row = mysql_fetch_assoc($result)){
    $t_openid = trim(strtolower($row['fullname']));
    $userList[$t_openid] = array('fullname' => $row['fullname'],'is_join'=>1,'is_captain'=>$row['is_captain'],'department'=>$row['department']);
}


$list = array();

foreach($userList as $value){
    $department = $value['department'];
    $sql = "SELECT * from dtc_team WHERE teamId='$teamId' AND department = '$department'";
    $ret = mysql_query($sql, $conn);
    if($row = mysql_fetch_assoc($ret)) {
        $value['store'] = $row['teamNameCN'];
        $value['storeEN'] = $row['teamNameEN'];
    }
    $list[] = $value;
}

$list = arr_sort($list,'is_captain','desc');


$err = '成功';
$errno = 1;
$retArray = $list;
$retuen_json = json_encode_cn(compact('err', 'errno', 'retArray'));
echo $retuen_json;


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