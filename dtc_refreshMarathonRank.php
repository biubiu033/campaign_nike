<?php
//include ("/var/www/html/www.makeyourruncount.com/php/funcs.php"); //引入微信类
//$conn = connect_to_db();
$conn = mysql_connect('localhost', 'root', 'aCmblvhreD0m');
mysql_select_db('lixiang',$conn);
$query = "Set Names 'utf8mb4'";
mysql_query($query,$conn);
echo date("Y-m-d H:i:s", time()) . "\t开始计算3d5k---------------------------------------\n\n";

//$openid = 'o1bHnsxUd-54i_GdP7FR_JinSH5o'; //crd
//$openid = 'o1bHns8YAZSqzn-lq20Na7Lz7o8Q'; //niko
//$openid = 'o1bHns75kRBqSEIvyPsyQwSrBCz4';  //luna

//判断连续3天5公里
$dtc_taskone_marathon = array();

$query = "select openid from dtc_taskone_marathon";

$result = mysql_query($query, $conn);
while ($row = mysql_fetch_assoc($result)){
    $openid = $row['openid'];
    $dtc_taskone_marathon[$openid] = $openid;
}

$query = "SELECT openid,sum(distance) AS sum,teamId FROM yiqipao_runlog_all 
WHERE rid >59600 AND `status`=0 AND donateProject = 62 AND endTime >'2017-08-01' AND endTime < '2017-08-26'
GROUP BY openid
ORDER BY sum DESC";
$count = 0;
$result = mysql_query($query,$conn);
while ($row = mysql_fetch_assoc($result)){
    $openid = $row['openid'];

    if(isset($dtc_taskone_marathon[$openid])){
        $sum = $row['sum'];
        $teamId = $row['teamId'];
        $query = "SELECT teamName FROM dtc_team WHERE teamId = '$teamId' LIMIT 1";
        $teamName = mysql_fetch_assoc(mysql_query($query,$conn))['teamName'];
        $query = "SELECT fullname,teamNameEn FROM dtc_join_user WHERE  openid = '$openid'";
        $fullName = mysql_fetch_assoc(mysql_query($query,$conn))['fullname'];
        $teamNameEn = mysql_fetch_assoc(mysql_query($query,$conn))['teamNameEn'];

        $update = "UPDATE dtc_taskone_marathon_copy SET full_name='$fullName',teamId='$teamId',mile='$sum',team_name='$teamName',department='$teamNameEn',update_time=now() WHERE openid ='$openid'";
        echo $update."\n";
        echo $count++."\n";
        mysql_query($update,$conn);
    }
}


echo date("Y-m-d H:i:s", time()) . " end---------------------------------------\n";
/**
 * Created by PhpStorm.
 * User: a7849
 * Date: 2017/8/14
 * Time: 15:16
 */