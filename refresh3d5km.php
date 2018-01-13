<?php

include ("/var/www/html/www.makeyourruncount.com/php/funcs.php"); //引入微信类
$conn = connect_to_db();
echo date("Y-m-d H:i:s", time()) . "\t开始计算荣誉殿堂---------------------------------------\n\n";

$openid = 'o1bHnsxUd-54i_GdP7FR_JinSH5o'; //crd
//$openid = 'o1bHns8YAZSqzn-lq20Na7Lz7o8Q'; //niko
//$openid = 'o1bHns75kRBqSEIvyPsyQwSrBCz4';  //luna


$dtcPid = 62;
$dtcTrophyUsers = array();
$joinUsers = array();
//先补全下dtc_trophy表
$sql = "select openid from dtc_trophy;";
$result = mysql_query($sql, $conn);
while ($row = mysql_fetch_assoc($result))
{
    $openid = $row['openid'];
    $dtcTrophyUsers[$openid] = $openid;
}

//$sql = "select openid from dtc_join_user;";
//$result = mysql_query($sql, $conn);
//while ($row = mysql_fetch_assoc($result))
//{
//    $openid = $row['openid'];
//    if (!isset($dtcTrophyUsers[$openid]))
//    {
//        $sql1 = "INSERT INTO dtc_trophy (openid) VALUE ('{$openid}')";
//        mysql_query($sql1, $conn);
//    }
//}
$count = 0;
$lastOpenid = '';
$lastTime = '';
//判断连续3天5公里
$query = "SELECT openid,DATE_FORMAT(`endTime`,'%Y-%m-%d') endTime FROM yiqipao_runlog_all WHERE rid > 59600 and status = 0 and donateProject = 62 AND distance>=5 ORDER BY openid ,endTime DESC";
//查询到的数据根据openid排序，根据endTime倒序
$result = mysql_query($query,$conn);
while ($row = mysql_fetch_assoc($result)){
    if($row['openid'] != $lastOpenid){  //如果不等于上一个openid，就重置
        $count = 1;
        $lastTime = strtotime($row['endTime']);
        $lastOpenid = $row['openid'];
    }else{   //如果等于上一个openid，有两个情况，
        if(strtotime($row['endTime']) == $lastTime-3600*24){ //天数连续，则计数，并判断是否等于3
            $count = $count+1;
            if($count == 3){
                echo  $row['openid']."\r\n";
            }
            $lastTime = strtotime($row['endTime']);
            $lastOpenid = $row['openid'];
        }else{                          //否则就重置
            $count = 1;
            $lastTime = strtotime($row['endTime']);
            $lastOpenid = $row['openid'];
        }
    }
}

//判断团队胜利


echo date("Y-m-d H:i:s", time()) . " end---------------------------------------\n";

?>