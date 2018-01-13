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
$pageType = 'apiUnUploadUpdate';


if (isset($_POST['slogan']) && !empty($_POST['slogan']) &&
    $_FILES["photo"]["error"] == 0
) {
    $slogan = mysql_real_escape_string($_POST['slogan']);

    $sql3 = "select * from dtc_join_user,dtc_team where dtc_team.teamId=dtc_join_user.teamId and dtc_join_user.openid='$openid';";
    $result3 = mysql_query($sql3, $conn);
    $row3 = mysql_fetch_assoc($result3);
    $teamId = $row3['teamId'];
    $teamName = $row3['teamName'];

    $sql = "SELECT * FROM dtc_theme_run WHERE t_id ='$teamId'";
    $ret = mysql_query($sql, $conn);
    if (!is_resource($ret) || mysql_num_rows($ret) == 1) {
        //保存图片
        $str=random_string(12);
        $tmp_path = $_FILES["photo"]["tmp_name"];
        $fileParts = pathinfo($_FILES["photo"]['name']);
        $img_small = 'Upload/' . $teamId.'_'.$str. '@.' . $fileParts['extension'];
        $target_path = "/data/webroot/www.makeyourruncount.com/campaign_nike/running/dtc/Upload/";
        $targetFile = rtrim($target_path, '/') . '/'. $teamId.'_'.$str. '.' . $fileParts['extension'];
        $targetFileS = rtrim($target_path, '/') . '/' . $teamId.'_'.$str. '@.' . $fileParts['extension'];
        @move_uploaded_file($tmp_path, $targetFile);
        imageCompress($targetFile, $targetFileS, 500, $fileParts['extension']);
        //保存信息
        $sql = "UPDATE dtc_theme_run SET t_name='$teamName',slogan='$slogan',theme_img='$img_small',update_time=now(),votes=0,status=1 
                WHERE t_id = '$teamId'";
        mysql_query($sql, $conn);

        $errcode = 1;
        $errmsg = "信息提交成功";
    }
    else {
        $errcode = -1;
        $errmsg = "队长还未提交过";
    }
} else {
    $errcode = -1;
    $errmsg = "网络错误";
}

$resArr = array("errcode" => $errcode, "errmsg" => $errmsg,'img'=>$targetFile);
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