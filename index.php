<?php
/**
 * Created by PhpStorm.
 * User: a7849
 * Date: 2017/7/30
 * Time: 11:46
 */
require_once "./header.php";
$conn = connect_to_db();

$apiStartTime = getMicrotime();
$apiUrl = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
$loginIP = getClientIP();
$type = 'index';

$headerLocation = ''; //跳转地址
$sql = "select * from dtc_join_user where openid = '$openid';";
$result = mysql_query($sql, $conn);
if (is_resource($result) && mysql_num_rows($result) != 0)
{
    $joinUserRow = mysql_fetch_assoc($result);
    //看用户是否已经加入团队并写了相关信息
    if ($joinUserRow['teamId'] != -1)
    {
        //看用户是否是队长
        if ($joinUserRow['is_captain'] == 1)
        {
            //是队长看团队情况
            $teamId = $joinUserRow['teamId'];
            $sql1 = "select * from dtc_team where teamId = $teamId;";
            $result1 = mysql_query($sql1, $conn);
            if (is_resource($result1) && mysql_num_rows($result1) != 0)
            {
                $row1 = mysql_fetch_assoc($result1);
                if ($row1['is_input'] < 1)
                {
                    //团队名字没起好，跳到团队起名字页面
                    $headerLocation = 'nominate.php';
                }
                else
                {
                    $headerLocation = 'confirm.php';
                }
            }
        } else
        {
            //作为队员，如果已经绑定过且确认参加，则去往首页，否则都跳到确认页面
            if ($joinUserRow['is_confirm'] == 1 && $_SESSION['userInfo']['isBindNike'] == 1)
            {
                //已确认开启且绑定过nike，则跳到main.php
                $headerLocation = 'main.php';
            } else
            {
                //其他情况则跳转到等待队长审核页面
                $headerLocation = 'confirm.php';
            }
        }
    } else
    {
        //teamId=-1且is_allow=-1代表如被队长拒绝的话，如果不是携带is_allow参数（代表confirm页面返回回来）则跳到confirm.php
        if ($joinUserRow['is_allow'] == -1 & !isset($_GET['is_allow']))
        {
            $headerLocation = 'confirm.php';
        }
    }
} else
{
    echo "数据同步异常，请联系客服电话18514748838";
    exit();
}

if ($headerLocation != '')
{
    //记录页面请求日志
    $apiEndTime = getMicrotime();
    $fetchTime = intval(($apiEndTime - $apiStartTime) * 1000);
    $logArr = array(
        'openid' => "$openid",
        'type' => $type,
        'ip' => $loginIP,
        'url' => $apiUrl,
        'result' => mysql_real_escape_string($headerLocation),
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

    header("Location:$headerLocation");

    exit();
}

