<?php
/**
 * Created by PhpStorm.
 * User: Dingjinrong
 * Date: 17/6/7
 * Time: 下午4:59
 */
ini_set('memory_limit','1024M');

require_once "header.php";
$conn = connect_to_db();

$apiStartTime = getMicrotime();
$apiUrl = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
$loginIP = getClientIP();
$pageType = 'apiUnUpload';

//echo $_FILES;

if (isset($_POST['date']) && !empty($_POST['date']) &&$_FILES["photo2"]["error"] == 0) {
    $date = $_POST['date'];

    $sql3 = "select * from dtc_join_user,dtc_team where dtc_team.teamId=dtc_join_user.teamId and dtc_join_user.openid='$openid';";
    $result3 = mysql_query($sql3, $conn);
    $row3 = mysql_fetch_assoc($result3);
    $teamId = $row3['teamId'];
    $teamName = $row3['teamName'];

    $sql = "SELECT * FROM dtc_theme_run WHERE t_id ='$teamId'";
    $ret = mysql_query($sql, $conn);
    if (!is_resource($ret) || mysql_num_rows($ret) == 0) {
        //保存图片
        $str=random_string(12);
        $tmp_path = $_FILES["photo2"]["tmp_name"];
        $fileParts = pathinfo($_FILES["photo2"]['name']);
//        $img_small = 'Upload/' . $teamId.'_'.$str. '@.' . $fileParts['extension'];
        $target_path = "/data/webroot/www.makeyourruncount.com/campaign_nike/running/dtc/Upload/";
        $targetFile = rtrim($target_path, '/') . '/'. $teamId.'_'.$str. '.' . $fileParts['extension'];
        $filePath = "/campaign_nike/running/dtc/Upload". '/'. $teamId.'_'.$str. '.' . $fileParts['extension'];
//        $targetFileS = rtrim($target_path, '/') . '/' . $teamId.'_'.$str. '@.' . $fileParts['extension'];
        @move_uploaded_file($tmp_path, $targetFile);
//        imageCompress($targetFile, $targetFileS, 500, $fileParts['extension']);
        //保存信息
        $sql = "UPDATE dtc_task_popup SET t_id = '$teamId',t_name = '$teamName',img_two = '$filePath',update_time = NOW() WHERE openid = '$openid' AND task_id = '$date'";
        mysql_query($sql, $conn);

        $errcode = 1;
        $errmsg = "上传成功";
    } else {
        $errcode = -1;
        $errmsg = "已经提交过了";
    }
} else {
    $errcode = -1;
    $errmsg = "网络错误";
}

$resArr = array("errcode" => $errcode, "errmsg" => $errmsg);
$resJson = json_encode_cn($resArr);
echo $resJson;

//记录页面请求日志
$apiEndTime = getMicrotime();
$fetchTime = intval(($apiEndTime - $apiStartTime) * 1000);
$logArr = array(
    'openid' => "$openid",
    'type' => $pageType,
    'ip' => $loginIP,
    'url' => $apiUrl,
    'result' => mysql_real_escape_string($resJson),
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



function imageCompress($imgsrc, $imgdst, $new_width, $fileType)
{
    list($width,$height,$type)=getimagesize($imgsrc);
    /*$new_width = ($width>600?600:$width)*0.9;*/
    $new_height =($new_width / $width) * $height;
    switch($type){
        case 2:
            header('Content-Type:image/jpeg');
            $image_wp=imagecreatetruecolor($new_width, $new_height);
            $image = imagecreatefromjpeg($imgsrc);
            imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            imagejpeg($image_wp, $imgdst,75);
            imagedestroy($image_wp);
            break;
        case 3:
            header('Content-Type:image/png');
            $image_wp=imagecreatetruecolor($new_width, $new_height);
            $image = imagecreatefrompng($imgsrc);
            imagecopyresampled($image_wp, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            imagejpeg($image_wp, $imgdst,75);
            imagedestroy($image_wp);
            break;
    }
    header( 'Content-Type:text/html;charset=utf-8 ');
}


?>