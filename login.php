<?php
//define('IS_DEBUG',true);

require_once "../../../php/funcs.php";
include '../../../emoji/emoji.php';

global $CONFIG;
$handler = new RedisSessionHandler('LIXIANG_RUN_SESSION_', $CONFIG['REDIS']['HOST'],
    $CONFIG['REDIS']['PORT']);
session_set_save_handler($handler, true);

if (!session_id())
{
    session_start();
}

if (isset($_GET['code']))
{
    $conn = connect_to_db();
    $code = $_GET['code'];
    $openid = getWeixinOpenid($code, $conn);
    $_SESSION['nikeOpenid'] = $openid;
    if (isset($_SESSION['srcopenid']))
    {
        $src_weixinID = $_SESSION['srcopenid'];
    } else
    {
        $src_weixinID = '';
    }
    $openid = mysql_real_escape_string($openid);
    if ($openid != 'unknown')
    {
        $kmLoginIP = getClientIP();
        $sessionId = session_id();
        $sessionId = 'LIXIANG_RUN_SESSION_' . $sessionId;
        $userAgent = isset($_SERVER["HTTP_USER_AGENT"]) ? mysql_real_escape_string($_SERVER['HTTP_USER_AGENT']) :
            ""; //记录浏览器等
        $insertSql = "INSERT INTO `dtc_join_user` (`openid`,`sessionid`,`srcopenid`, `joinTime`,`ip`, `userAgent`,`updateTime`)";
        $insertSql .= " VALUES ('$openid','$sessionId', '$src_weixinID', NOW(),'$kmLoginIP', '$userAgent', now()) ";
        $insertSql .= " ON DUPLICATE KEY UPDATE sessionid='$sessionId', updateTime = now();";
        mysql_query($insertSql, $conn);
        $userInfo = getWeixinUserInfo($conn, $openid);
        if (isset($userInfo['nickname']))
        {
            $nickname = mysql_real_escape_string($userInfo['nickname']);
        } else
        {
            $nickname = 'nike';
        }
        if (isset($userInfo['headimgurl']))
        {
            $headimgurl = mysql_real_escape_string($userInfo['headimgurl']);
        } else
        {
            $headimgurl = mysql_real_escape_string('http://www.makeyourruncount.com/campaign_nike/running/public/img/lixiang_share.jpg');
        }

        //时间int先算了
        $time = time();
        $sql = "INSERT INTO `yiqipao_member` (`name`, `sessionid`, `headimg`,  `openid`, `created`, `updated`, `regTime`, `regIp`,`userAgent`, `srcopenid`)";
        $sql .= "VALUES ('$nickname', '$sessionId', '$headimgurl', '$openid', $time, $time,$time, '$kmLoginIP','$userAgent', '$src_weixinID')";
        $sql .= "ON DUPLICATE KEY UPDATE name='$nickname', sessionid='$sessionId', headimg='$headimgurl',updated=$time,regIp='$kmLoginIP',userAgent='$userAgent';";
        mysql_query($sql, $conn);

        //这里需要恢复下session字段
        $sql = "select * from yiqipao_member where openid = '$openid'";
        $result = mysql_query($sql, $conn);

        //数据库已有结果，相当于刷新页面
        if (is_resource($result) && mysql_num_rows($result) != 0)
        {
            $row = mysql_fetch_array($result, MYSQL_ASSOC);

            $_SESSION['userInfo']['id'] = $row['id'];
            $_SESSION['userInfo']['sid'] = $row['sid']; //当前参加项目的赞助商
            $_SESSION['userInfo']['mysid'] = $row['mysid']; //我所在的赞助商
            $_SESSION['userInfo']['openid'] = $row['openid'];
            $_SESSION['userInfo']['nikeid'] = $row['nikeid'];
            $_SESSION['userInfo']['name'] = $row['name'];
            $_SESSION['userInfo']['headimg'] = $row['headimg'];
            $_SESSION['userInfo']['created'] = $row['created']; //创建时间
            $_SESSION['userInfo']['regTime'] = $row['regTime']; //注册时间
            $_SESSION['userInfo']['access_token'] = $row['access_token']; //nike+的access_token
            $_SESSION['userInfo']['profile_img_url'] = $row['profile_img_url']; //用户nike+的头像
            $_SESSION['userInfo']['teamId'] = $row['teamId']; //我当前所加入的团队
            $_SESSION['userInfo']['refresh_token_created'] = $row['refresh_token_created']; //refresh_token创建时间
            $_SESSION['userInfo']['tongbuTime'] = $row['tongbuTime']; //数据同步时间   同步到捐助
            $_SESSION['userInfo']['projectId'] = $row['projectId']; //我当前所参加的项目id
            $_SESSION['userInfo']['isBindNike'] = 0;
            if (isset($row['nikeid']) && $row['nikeid'] != '')
            {
                $_SESSION['userInfo']['isBindNike'] = 1; //是否绑定
            }
            $_SESSION['userInfo']['sessionCreated'] = $time; //session创建时间

            $pid = $row['projectId'];
            $teamid = $row['teamId'];

            //获取项目信息数据
            $sql1 = "select * from yiqipao_member_project where openid = '$openid' and pid=$pid order by id desc;";
            $result1 = mysql_query($sql1, $conn);

            //这里跑的里程数需要累加
            $_SESSION['userInfo']['long'] = 0; //我当前所参加项目的里程数
            if (is_resource($result1) && mysql_num_rows($result1) != 0)
            {
                while ($row1 = mysql_fetch_array($result1, MYSQL_ASSOC))
                {
                    $_SESSION['userInfo']['long'] += $row1['long'];
                    if (!isset($_SESSION['userInfo']['uTarget']))
                    {
                        $_SESSION['userInfo']['uTarget'] = $row1['uTarget']; //我当前所参加项目的个人目标
                        $_SESSION['userInfo']['mileage'] = $row1['uTarget']; //我当前所参加项目的个人目标
                    }

                    if (!isset($_SESSION['userInfo']['mpId']))
                    {
                        $_SESSION['userInfo']['mpId'] = $row1['id']; //我当前所参加项目在member_project表中的主键
                    }

                    if (!isset($_SESSION['userInfo']['mpCreated']))
                    {
                        $_SESSION['userInfo']['mpCreated'] = $row1['created']; //参加这个项目的时间
                    }

                }
            }

            $sql2 = "select * from yiqipao_project where id=$pid limit 1;";
            $result2 = mysql_query($sql2, $conn);
            if (is_resource($result2) && mysql_num_rows($result2) != 0)
            {
                $row2 = mysql_fetch_array($result2, MYSQL_ASSOC);
                $_SESSION['userInfo']['projectStart'] = $row2['start']; //当前项目的开始时间
            }

            //获取团队信息数据
            $sql3 = "select * from dtc_team where id=$teamid limit 1;";
            $result3 = mysql_query($sql3, $conn);
            if (is_resource($result3) && mysql_num_rows($result3) != 0)
            {
                $row3 = mysql_fetch_array($result3, MYSQL_ASSOC);
                $_SESSION['userInfo']['teamName'] = $row3['teamName']; //我的团队名
                $_SESSION['userInfo']['teamCode'] = $row3['tcode']; //团队code
            }


        }
    }

    if(isset($_SESSION['redirectUrl']))
    {
        $referer = $_SESSION['redirectUrl'];
    } else if(isset($_GET['referer']))
    {
        $referer = urldecode($_GET['referer']);
    }
    else
    {
        $referer = PATH;
    }
    header("Location:" . $referer);


}

?>