<?php

//define('IS_DEBUG',true);

require_once "../../../php/funcs.php";
include '../../../emoji/emoji.php';

global $CONFIG;
$handler = new RedisSessionHandler('LIXIANG_RUN_SESSION_', $CONFIG['REDIS']['HOST'],
    $CONFIG['REDIS']['PORT']);
session_set_save_handler($handler, true);

require_once "../../../php/session_common.php";

$dtcPid = 62;
$openid = ''; //微信授权
if (!session_id())
{
    session_start();
}

$isWeixinBrowser = isWeixinBrowser();
if ($isWeixinBrowser == 1)
{
    if (isset($_SESSION['nikeOpenid']) && !empty($_SESSION['nikeOpenid']) && $_SESSION['nikeOpenid'] !=
        'unknown' && isset($_SESSION['userInfo']))
    {
        $openid = $_SESSION['nikeOpenid'];
        $conn = connect_to_db();
        $sql = "select count(*) as count from dtc_join_user where openid = '$openid';";
        $result = mysql_query($sql, $conn);
        $row = mysql_fetch_assoc($result);
        if ($row['count'] == 0)
        {
            $_SESSION['redirectUrl'] = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $redirectUrl = "http://www.makeyourruncount.com/campaign_nike/running/dtc/login.php?referer=" .
                urlencode("http://" . $_SERVER['HTTP_HOST'] .
                #$redirectUrl = "http://www.makeyourruncount.com/api/redirect1.php?referer=" . urlencode("http://" . $_SERVER['HTTP_HOST'] .
                $_SERVER['REQUEST_URI']);
            $baseUrl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $CONFIG['appid'] .
                "&response_type=code&scope=snsapi_base";
            $finalUrl = $baseUrl . "&redirect_uri=$redirectUrl&state=nike#wechat_redirect";
            //echo $finalUrl;
            header("Location:$finalUrl");
            exit();
        }

    } else
    {
        $_SESSION['redirectUrl'] = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $redirectUrl = "http://www.makeyourruncount.com/campaign_nike/running/dtc/login.php?referer=" .
            urlencode("http://" . $_SERVER['HTTP_HOST'] .
            #$redirectUrl = "http://www.makeyourruncount.com/api/redirect1.php?referer=" . urlencode("http://" . $_SERVER['HTTP_HOST'] .
            $_SERVER['REQUEST_URI']);
        $baseUrl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $CONFIG['appid'] .
            "&response_type=code&scope=snsapi_base";
        $finalUrl = $baseUrl . "&redirect_uri=$redirectUrl&state=nike#wechat_redirect";
        //echo $finalUrl;
        header("Location:$finalUrl");
        exit();
    }


} else
{
//        echo "<script>alert('请从微信手机端访问，参与活动哦～');</script>";
//        header("Location: http://www.makeyourruncount.com/");
//        exit();
    //    $openid = 'o1bHns2YFBNQ48ef54RihzPsLYWQ123'; //LHX
    //$openid = 'o1bHns1Tu-IQG7Tt-ouqSF1BfDXk';  //Chenyun
    //$openid = 'oeHn0jnvVktdzBM7Z3dOgU_ttNp8'; //Andy
    //  $openid = 'o1bHns-HEFAC5lopFSxtGz1Zvbr0';  //joanne
//      $openid = 'o1bHns-Z80QKhcb7gX7cTacK36so';  //adam
//        $openid = 'o1bHns5rEUTlWxGO53hBvLq3s_vw'; //Jr
    //    $openid="o1bHnszkEjXP0-3JH8_wtO5sLbvs"; //cjj
    //    $openid = 'o1bHns329rmsuQebWb6ADs-Hs_5A'; //crda
    $openid = 'o1bHns4BRbnHNNu13AujmzGHe6l0';  //JYJ
    $_SESSION['nikeOpenid'] = $openid;
    //echo $openid; die;
//    echo "请使用微信浏览器进入，谢谢";
//    exit();
    //$_SESSION['status'] = 'find';
}


//注：这里更新下session，确保NRC绑定正确
$conn = connect_to_db();
$sql = "select * from yiqipao_member where openid = '$openid';";
$result = mysql_query($sql, $conn);
$row = mysql_fetch_assoc($result);
if (isset($_SESSION['userInfo']))
{
    $_SESSION['userInfo']['isBindNike'] = $row['isBindNike'];
} else
{
    $_SESSION['userInfo'] = array();
    $_SESSION['userInfo']['isBindNike'] = $row['isBindNike'];
}

if($_SESSION['userInfo']['isBindNike'] == 1 && time() > ($row['refresh_token_created'] + 3600 * 24 * 365))
{
	$_SESSION['userInfo']['isBindNike'] = 0;
}
//$_SESSION['userInfo']['isBindNike'] = 0;

$prefix = "http://www.makeyourruncount.com/";
$a = $prefix . "campaign_nike/running/nikecms/store/upload/";
$title = "【以爱之名，热血奔跑】你的每一公里，每一次分享，都将转化为一个更健康的自己，一个更融合的社区，以及让需要帮助的人实现梦想的可能！";
$url = $prefix . "pc/";
$weibo = "http://service.weibo.com/share/share.php?url=$url&title=$title&pic=" .
    $url . "img/swipe-banner01.jpg||" . $url . "img/qrcode.jpg";
$qq = "http://share.v.t.qq.com/index.php?c=share&a=index&appkey=&pic=" . $url .
    "img/swipe-banner01.jpg||" . $url . "img/qrcode.jpg&title=$title&url=$url";

function getMicrotime()
{
    list($usec, $sec) = explode(' ', microtime());
    return ((float)$usec + (float)$sec);
}


function arrays_sort($arr, $keys, $type = 'asc')
{
    $keysvalue = $new_array = array();
    foreach ($arr as $k => $v)
    {
        $keysvalue[$k] = $v[$keys];
    }
    if ($type == 'asc')
    {
        asort($keysvalue);
    } else
    {
        arsort($keysvalue);
    }
    reset($keysvalue);
    foreach ($keysvalue as $k => $v)
    {
        $new_array[] = $arr[$k];
    }
    return $new_array;
}

?>
