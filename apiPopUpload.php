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
$pageType = 'apiPopUpload';



if (isset($_POST['date']) && !empty($_POST['date']) &&!empty($_POST['img'])) {
    $date = $_POST['date'];
    $base64_image_content = $_POST['img'];
    $base64_body = substr(strstr($base64_image_content,','),1);
    $img = base64_decode($base64_body);

    $sql3 = "select * from dtc_join_user,dtc_team where dtc_team.teamId=dtc_join_user.teamId and dtc_join_user.openid='$openid';";
    $result3 = mysql_query($sql3, $conn);
    $row3 = mysql_fetch_assoc($result3);
    $teamId = $row3['teamId'];
    $teamName = $row3['teamName'];
    $fullName = $row3['fullname'];

        //保存图片
        $str=random_string(12);
        $target_path = "/data/webroot/www.makeyourruncount.com/campaign_nike/running/dtc/Upload/";
        $targetFile = rtrim($target_path, '/') . '/'. $teamId.'_'.$str. '.jpg';
        $filePath = "Upload". '/'. $teamId.'_'.$str. '.jpg';

        file_put_contents($targetFile, $img);
        if(isset($_POST['img2'])){
            $base64_image_content2 = $_POST['img2'];
            $base64_body2 = substr(strstr($base64_image_content2,','),1);
            $img2 = base64_decode($base64_body2);
            $str2=random_string(12);
            $targetFile2 = rtrim($target_path, '/') . '/'. $teamId.'_'.$str2. '.jpg';
            $filePath2 = "Upload". '/'. $teamId.'_'.$str2. '.jpg';

            file_put_contents($targetFile2, $img2);
            //保存信息
            $sql = "UPDATE dtc_task_popup SET `name`='$fullName',t_id = '$teamId',t_name = '$teamName',img_one = '$filePath',img_two = '$filePath2',update_time = NOW() WHERE openid = '$openid' AND `task_id`='$date'";
            mysql_query($sql, $conn);
        }else{
            //保存信息
            $sql = "UPDATE dtc_task_popup SET `name`='$fullName',t_id = '$teamId',t_name = '$teamName',img_one = '$filePath',update_time = NOW() WHERE openid = '$openid' AND `task_id`='$date'";
            mysql_query($sql, $conn);
        }
        $errcode = 1;
        $errmsg = "上传成功";
} else {
    $errcode = -1;
    $errmsg = "网络错误";
}


echo $errcode;

//记录页面请求日志
$apiEndTime = getMicrotime();
$fetchTime = intval(($apiEndTime - $apiStartTime) * 1000);
$logArr = array(
    'openid' => "$openid",
    'type' => $pageType,
    'ip' => $loginIP,
    'url' => $apiUrl,
    'result' => $errmsg,
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