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

$sql = "select openid from dtc_join_user;";
$result = mysql_query($sql, $conn);
while ($row = mysql_fetch_assoc($result))
{
    $openid = $row['openid'];
    if (!isset($dtcTrophyUsers[$openid]))
    {
        $sql1 = "INSERT INTO dtc_trophy (openid) VALUE ('{$openid}')";
        mysql_query($sql1, $conn);
    }
}


//取跑步数据
$sql1 = "select sum(distance) as total, max(createTime) as createTime, openid from yiqipao_runlog_all where rid > 59600 and status = 0 and donateProject = 62 group by openid;";
echo date("Y-m-d H:i:s", time()) . "\t$sql1\n";
$result1 = mysql_query($sql1, $conn);

//有跑步数据，进一步判断
if (is_resource($result1) && mysql_num_rows($result1) != 0)
{
    //里程达到1公里 10公里 100公里 200公里
    while ($row1 = mysql_fetch_assoc($result1))
    {
        $openid = $row1['openid'];
        $updateStr = '';
        $total = $row1['total'];
        $createTime = $row1['createTime'];
        echo "\t$openid\t当前捐赠里程: $total\t最后捐赠时间: $createTime\n";

        //读取用户信息，注： is_join = 2 代表已经处理过
        $sql = "select * from dtc_trophy where openid = '$openid';";
        //$sql = "select * from dtc_trophy limit 3;";
        $result = mysql_query($sql, $conn);
        $row = mysql_fetch_assoc($result);
        if ($total >= 200)
        {
            if ($row['200km'] == 0)
            {
                $updateStr .= "200km = 1, 200km_time = '$createTime', ";
            }

            if ($row['100km'] == 0)
            {
                $updateStr .= "100km = 1, 100km_time = '$createTime', ";
            }

            if ($row['10km'] == 0)
            {
                $updateStr .= "10km = 1, 10km_time = '$createTime', ";
            }

            if ($row['1km'] == 0)
            {
                $updateStr .= "1km = 1, 1km_time = '$createTime', ";
            }
        } elseif ($total >= 100)
        {
            if ($row['100km'] == 0)
            {
                $updateStr .= "100km = 1, 100km_time = '$createTime', ";
            }

            if ($row['10km'] == 0)
            {
                $updateStr .= "10km = 1, 10km_time = '$createTime', ";
            }

            if ($row['1km'] == 0)
            {
                $updateStr .= "1km = 1, 1km_time = '$createTime', ";
            }

        } elseif ($total >= 10)
        {
            if ($row['10km'] == 0)
            {
                $updateStr .= "10km = 1, 10km_time = '$createTime', ";
            }

            if ($row['1km'] == 0)
            {
                $updateStr .= "1km = 1, 1km_time = '$createTime', ";
            }
        } elseif ($total >= 1)
        {
            if ($row['1km'] == 0)
            {
                $updateStr .= "1km = 1, 1km_time = '$createTime', ";
            }
        }

        if ($updateStr != '')
        {
            $updateStr = rtrim($updateStr, ", ");
            $sql2 = "update dtc_trophy set $updateStr where openid='$openid';";
            mysql_query($sql2, $conn);
            echo date("Y-m-d H:i:s", time()) . "\t$sql2\n";
        }
    }
}

//取分享数据，然后判断
$sql1 = "select count(*) as count, max(updateTime) as createTime, visitWeixinID as openid from yiqipao_pv_log . weixin_share_log where id > 4400 and shareNo = 1 and shareUrl like '%dtc%' group by visitWeixinID;";
echo date("Y-m-d H:i:s", time()) . "\t$sql1\n";
$result1 = mysql_query($sql1, $conn);

//有分享数据，进一步判断
if (is_resource($result1) && mysql_num_rows($result1) != 0)
{
    while ($row1 = mysql_fetch_assoc($result1))
    {
        $openid = $row1['openid'];
        $updateStr = '';
        $count = $row1['count'];
        $createTime = $row1['createTime'];
        echo "\t$openid\t当前分享数量: $count\t最后分享时间: $createTime\n";

        //读取用户信息
        $sql = "select * from dtc_trophy where openid = '$openid';";
        //$sql = "select * from dtc_trophy limit 3;";
        $result = mysql_query($sql, $conn);
        $row = mysql_fetch_assoc($result);
        if ($count >= 10)
        {
            if ($row['sharemany'] == 0)
            {
                $updateStr .= "sharemany = 1, sharemany_time = '$createTime', ";
            }

            if ($row['share1'] == 0)
            {
                $updateStr .= "share1 = 1, share1_time = '$createTime', ";
            }
        } elseif ($count >= 1)
        {
            if ($row['share1'] == 0)
            {
                $updateStr .= "share1 = 1, share1_time = '$createTime', ";
            }
        }

        if ($updateStr != '')
        {
            $updateStr = rtrim($updateStr, ", ");
            $sql2 = "update dtc_trophy set $updateStr where openid='$openid';";
            mysql_query($sql2, $conn);
            echo date("Y-m-d H:i:s", time()) . "\t$sql2\n";
        }
    }
}

//判断连续3天5公里
//判断团队胜利


echo date("Y-m-d H:i:s", time()) . " end---------------------------------------\n";

?>