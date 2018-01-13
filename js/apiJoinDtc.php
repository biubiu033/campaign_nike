<?php

/**
 * Created by PhpStorm.
 * User: Dingjinrong
 * Date: 17/6/8
 * Time: 下午8:34
 */
require_once "./header.php";
require_once "../../../php/funcs.php";
$conn = connect_to_db();
header("Content-Type:text/json charset=utf-8");

$apiStartTime = getMicrotime();
$apiUrl = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
$loginIP = getClientIP();
$pageType = 'apiJoinDtc';
//需要验证接受手机号
//匹配手机号对应的团队信息情况
//将匹配对应关系写入参赛表
//返回成功
$err = "";
$errno = -1;
$returnStr = ''; //返回参数
/*
`fullname` varchar(64) DEFAULT NULL COMMENT '员工姓名',
`employeeNumber` varchar(64) DEFAULT NULL COMMENT '员工编号',
`phone` varchar(32) DEFAULT NULL COMMENT '员工手机号',
`department` varchar(64) DEFAULT NULL COMMENT '所属团队，DTC商店编号或部门名字',
`type` varchar(32) DEFAULT NULL COMMENT '用户类型，DTC商店/DTC办公室/客户服务中心',
`teamId` int(11) NOT NULL DEFAULT '-1' COMMENT '团队编号，用户填完这里后需更新yiqipao_member表的
*
*/
$typeMap[1] = "DTC商店";
$typeMap[2] = "DTC办公室";
$typeMap[3] = "客户服务中心";

if (isset($_GET['name']) && isset($_GET['employ']) && isset($_GET['phone']) &&
    isset($_GET['department']) && isset($_GET['type']))
{
    $fullname = mysql_real_escape_string($_GET['name']);
    $employeeNumber = mysql_real_escape_string($_GET['employ']);
    $phone = mysql_real_escape_string($_GET['phone']);
    $department = mysql_real_escape_string($_GET['department']); //
    $type = mysql_real_escape_string($_GET['type']);
    $type = isset($typeMap[$type]) ? $typeMap[$type] : 'DTC商店';

    //先查找department下的 teamid
    $sql = "SELECT * FROM dtc_team where department = '{$department}' AND type='{$type}'";
    $ret = mysql_query($sql, $conn);
    if (is_resource($ret) && mysql_num_rows($ret))
    {
        $row = mysql_fetch_assoc($ret);
        $teamId = $row['teamId'];
        $teamName = $row['teamName'];
        $teamCode = $row['tcode'];
        $teamNameEn = $row['teamNameEN'];
        //        $department = is_numeric($teamName)?$teamNameEn:$department;

        $query = "SELECT * FROM dtc_team_white_list WHERE phone = '{$phone}'";
        $result = mysql_query($query, $conn);
        if (is_resource($result) && !mysql_num_rows($result) && $_GET['type'] == '3')
        {
            $reson = "根据你输入的信息，你不是有效的 $teamNameEn 员工，如有疑问，请联系你的队长";
            $returnStr = echoAjax($reson, -1);
        } else
        {
            //更新yiqipao_member
            $update = "UPDATE yiqipao_member SET teamId = '$teamId' where openid = '$openid'";
            mysql_query($update, $conn);
            //更新session
            $_SESSION['userInfo']['teamName'] = $teamName; //我的团队名
            $_SESSION['userInfo']['teamCode'] = $teamCode; //团队code

            $sql = "UPDATE dtc_join_user
                    SET fullname='{$fullname}',employeeNumber='{$employeeNumber}',phone='{$phone}',department='{$department}',type='{$type}',teamId='{$teamId}', is_allow='0'
                    where openid= '{$openid}' ";
            $ret = mysql_query($sql, $conn);

            $returnStr = echoAjax('加入成功', 0);
        }

    } else
    {
        //department
        $reson = "您填写的商店号有误";
        $returnStr = echoAjax($reson, -1);
    }
} elseif (isset($_GET['confirm']))
{
    $confirm = $_GET['confirm'];
    if ($confirm)
    {
        $sql = "UPDATE dtc_join_user SET is_confirm=1,confirmTime=now() where openid= '{$openid}' ";
        $ret = mysql_query($sql, $conn);
    } else
    {
        $sql = "UPDATE dtc_join_user a,yiqipao_member b 
                SET a.is_confirm=0,a.teamId = '-1',a.updateTime = NOW() ,b.teamId = '-1',a.department = NULL 
                where a.openid= '{$openid}' AND b.openid = a.openid";
        $ret = mysql_query($sql, $conn);
    }

    //写入dtc_trophy初始化
    $sql1 = "INSERT INTO dtc_trophy (openid) VALUE ('{$openid}')";
    mysql_query($sql1, $conn);
    $returnStr = echoAjax('成功', 0);
} elseif (isset($_GET['allowJoin']) && isset($_GET['uId']))
{
    $allowJoin = $_GET['allowJoin'];
    $uId = $_GET['uId'];
    if ($allowJoin == '1')
    {
        $sql = "UPDATE dtc_join_user SET is_allow='1',updateTime = NOW() where id= '{$uId}' ";
        $ret = mysql_query($sql, $conn);
        //写入dtc_trophy初始化
    } else
    {
        $sql = "UPDATE dtc_join_user a,yiqipao_member b 
                SET a.teamId='-1',a.is_allow='-1',a.updateTime = NOW() ,b.teamId = '-1',a.department = NULL 
                where a.id= '{$uId}' AND b.openid = a.openid";
        $ret = mysql_query($sql, $conn);
    }

    $returnStr = echoAjax('成功', 0);
} else
{
    $err = "参数异常,重新输入";
    $errno = -1;
}

if($returnStr == '')
{
    $returnStr = echoAjax($err, $errno); 
}

echo $returnStr;

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
    return json_encode_cn($retrunAjax);    
}


?>