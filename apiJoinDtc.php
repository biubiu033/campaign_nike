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
$detailInfo = '';
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
    $sql = "SELECT * FROM dtc_team WHERE department = '{$department}' AND type='{$type}'";
    $ret = mysql_query($sql, $conn);
    if (is_resource($ret) && mysql_num_rows($ret))
    {
        $row = mysql_fetch_assoc($ret);
        $teamId = $row['teamId'];
        $teamName = $row['teamName'];
        $teamCode = $row['tcode'];
        $teamNameEn = $row['teamNameEN'];
        //        $department = is_numeric($teamName)?$teamNameEn:$department;

        if ($_GET['type'] == '3')
        {
            /*
            $query = "SELECT * FROM dtc_team_white_list WHERE phone = '{$phone}'";
            $result = mysql_query($query, $conn);
            if(is_resource($result) && mysql_num_rows($result)==0 ){
            $reson = "请输入正确手机号。如确认输入信息无误，请及时与队长联系。<br><span style='font-size: 0.7rem; font-family: 'fornike365' '>Access denied. Please enter the correct mobile number. If the input is correct, please contact your captain.</span>";
            $returnStr = echoAjax($reson, -1);
            }*/
        }
        //更新yiqipao_member
        $update = "UPDATE yiqipao_member SET teamId = '$teamId' where openid = '$openid'";
        mysql_query($update, $conn);
        //更新session
        $_SESSION['userInfo']['teamName'] = $teamName; //我的团队名
        $_SESSION['userInfo']['teamCode'] = $teamCode; //团队code

        $query = "SELECT id FROM dtc_team_captain WHERE name = '$fullname' AND teamId = '$teamId' AND ISNULL(is_join)";
        $result = mysql_query($query, $conn);
        if (is_resource($result) && mysql_num_rows($result) == 0)
        {
            $sql = "UPDATE dtc_join_user
                    SET fullname='{$fullname}',employeeNumber='{$employeeNumber}',phone='{$phone}',department='{$department}',type='{$type}',teamId='{$teamId}', is_allow='0'
                    where openid= '{$openid}' ";
        } elseif (is_resource($result) && mysql_num_rows($result) != 0)
        {
            $sql = "UPDATE dtc_join_user
                    SET fullname='{$fullname}',employeeNumber='{$employeeNumber}',phone='{$phone}',department='{$department}',type='{$type}',teamId='{$teamId}', is_allow='1',is_captain='1', is_join='1'
                    where openid= '{$openid}' ";
            $query = "UPDATE dtc_team_captain SET is_join ='1',join_time=NOW() WHERE name = '$fullname' AND teamId = '$teamId'";
            $result = mysql_query($query, $conn);

        }

        $ret = mysql_query($sql, $conn);

        $returnStr = echoAjax('加入成功', 0);
        $sql1 = "INSERT INTO dtc_trophy (openid) VALUE ('{$openid}')";
        mysql_query($sql1, $conn);

    } else
    {
        //department
        $reson = " 您填写的商店号有误<br><span style='font-size: 0.7rem; font-family: 'fornike365' '>Wrong Store Code</span>";
        $returnStr = echoAjax($reson, -1);
    }
} elseif (isset($_GET['confirm']))
{
    $confirm = $_GET['confirm'];
    if ($confirm)
    {
        $sql = "UPDATE dtc_join_user SET is_confirm=1,confirmTime=now() where openid= '{$openid}' ";
        $ret = mysql_query($sql, $conn);
        //读取用户信息
        $sql = "select * from dtc_join_user where openid = '$openid';";
        $result = mysql_query($sql, $conn);
        $joinUserRow = mysql_fetch_assoc($result);

        //确认参与，需增加yiqipao_member_project信息
        $sql1 = "select * from yiqipao_member_project where openid = '$openid' order by id desc limit 1;";
        $ret1 = mysql_query($sql1, $conn);
        $needAdd = true;
        //如果没有已参加项目，则此处应增加，如有则看是否是对的
        if (is_resource($ret1) && mysql_num_rows($ret1) != 0)
        {
            $row1 = mysql_fetch_assoc($ret1);
            $mpid = $row1['id'];
            //已参加该项目
            if ($row1['pid'] == $dtcPid && $row1['status'] > -1)
            {
                $needAdd = false;
                //团队名不对，则要重新来
                if ($row1['teamId'] != $joinUserRow['teamId'])
                {
                    $currTime = time();
                    $sql2 = "update yiqipao_member_project set status = -1, quitTime = $currTime where id = $mpid limit 1;";
                    mysql_query($sql2, $conn);
                    $needAdd = true;
                }
            } else
            {
                //如果不是退出的项目，则要退出
                if ($row1['status'] > -1)
                {
                    $currTime = time();
                    $sql2 = "update yiqipao_member_project set status = -1, quitTime = $currTime where id = $mpid limit 1;";
                    mysql_query($sql2, $conn);
                }
            }
        }

        //最后收尾，如需增加项目则增加
        if ($needAdd)
        {
            $currTime = time();
            if ($currTime < strtotime("2017-08-04"))
            {
                $currTime = 1501776222;
            }
            $teamId = $joinUserRow['teamId'];
            $sql2 = "insert into yiqipao_member_project (sid, mysid, pid, `long`, openid, uTarget, teamId, ip, created) ";
            $sql2 .= "values (8, 0, $dtcPid, 0, '$openid', 18, $teamId, '$loginIP', $currTime);";
            mysql_query($sql2, $conn);
        }
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

    //审核结束通知用户
    //取用户openid
    $sql1 = "select openid, fullname from dtc_join_user where id='{$uId}';";
    $result1 = mysql_query($sql1, $conn);
    if (is_resource($result1) && mysql_num_rows($result1) != 0)
    {
        $row1 = mysql_fetch_assoc($result1);
        $memberOpenid = $row1['openid'];
        $fullname = $row1['fullname'];

        if ($memberOpenid == 'o1bHnsxUd-54i_GdP7FR_JinSH5o')
        {
            $templateMsg = array();
            $templateMsg['touser'] = $memberOpenid;
            $templateMsg['template_id'] = 'Xzfzn1_mkuM8XW2IO9PSkA6ziElJd-EYCdoUof40hLM';
            $templateMsg['topcolor'] = "#FF0000";
            $templateMsg['data'] = array();
            $templateMsg['data']['first'] = array("value" => "亲爱的${fullname}，感谢您参与并关注里享。\n",
                    'color' => "#000000");
            $templateMsg['data']['keyword1'] = array("value" => "Run, Nike Direct! Run!我是跑手",
                    'color' => "#000000");
            $templateMsg['data']['keyword2'] = array("value" => "队长已经审核你的参与信息！", 'color' =>
                    "#FF0000");
            $templateMsg['data']['remark'] = array("value" => "\n快去看看吧！", 'color' =>
                    "#000000");
            $templateMsg['url'] =
                "http://www.makeyourruncount.com/campaign_nike/running/dtc/join-choose.php";


            $postContent = json_encode_cn($templateMsg);
            $unixtime = time();
            $key = 'hugjmk5AB24giest5weixinkongmingCC@#fKM';
            $token = md5($openid . $key . $unixtime);
            $postUrl = "http://www.makeyourruncount.com/api/sendtemplate.php?openid=$openid&token=$token&unixtime=$unixtime&module=lixiang";
            $sendInfo = postsendKefuDataDTC($postContent, $postUrl);
            $detailInfo = $postContent . "template msg: " . $sendInfo;

            //标记，不要重复发模板消息了
            $sql1 = "update dtc_join_user set is_join='1' where id='{$uId}';";
            mysql_query($sql1, $conn);
        }
    }


    $returnStr = echoAjax('成功', 0);
} else
{
    $err = "参数异常,重新输入";
    $errno = -1;
}

if ($returnStr == '')
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
    'result' => mysql_real_escape_string($returnStr . " " . $detailInfo),
    'fetchTime' => $fetchTime,
    'updateTime' => date("Y-m-d H:i:s", time()));

$insertkeysql = $insertvaluesql = $dot = '';
foreach ($logArr as $insert_key => $insert_value)
{
    $insertkeysql .= $dot . $insert_key;
    $insertvaluesql .= $dot . '\'' . $insert_value . '\'';
    $dot = ', ';
}
$sql1 = 'insert into dtc_api_logs (' . $insertkeysql . ') values (' . $insertvaluesql .
    ')';
mysql_query($sql1, $conn);


function echoAjax($err, $errno)
{
    $retrunAjax = array('err' => $err, 'errno' => $errno);
    return json_encode_cn($retrunAjax);
}

function postsendKefuDataDTC($data, $url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/x-www-form-urlencoded'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $res = curl_exec($ch);
    curl_close($ch);
    return $res;
}

?>
