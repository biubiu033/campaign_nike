<?php
/**************************设置分享参数*****************************/
if(!is_resource($conn)){
    $conn = connect_to_db();
}
$signPackage = getSignPackage($conn);

$phpfile = isset($phpfile) ? $phpfile :
    "campaign_nike/running/dtc/invite.php";

//判断该用户是否跑了大于零
$sql = "SELECT sum(`long`) as total FROM yiqipao_member_project where openid='{$openid}' and pid={$dtcPid}";
$result = mysql_query($sql, $conn);

if ($row = mysql_fetch_assoc($result)) {
    if(isset($row['total']) && $row['total'] > 0)
    {
        $total = $row['total'];
        $shareDesc = "我在里享公益挑战赛贡献了$total 公里，正在为山区学校争取运动装备福利!";    
    }
    else
    {
        $shareDesc = "我正在参加里享公益挑战赛，加入我们，为山区学校争取运动装备福利吧!";
    }
    
}else{
    $shareDesc = "我正在参加里享公益挑战赛，加入我们，为山区学校争取运动装备福利吧!";
}

$shareTitle = isset($shareTitle) ? $shareTitle : '跑出里享世界';
$shareImg = isset($shareImg) ? $shareImg : "http://www.makeyourruncount.com/campaign_nike/running/public/img/lixiang_share.jpg";
$unixtime = time();
$key = "hugjmk5AA4giest5weixinTencentCC@#fKM";
$token = md5($openid . $key . $unixtime);
$campaignID = '201707_1_dtcRun';
$shareUrl = addWeixinShareParameters($phpfile, $openid, $campaignID);
?>
<script>	
//微信分享全局对象：
	 window.SHARECONFIG = {
		PKey: 'yiqipao',
		srcWeixinID: '',
		visitWeixinID: '<?php echo $openid; ?>',
		unixtime: '<?php echo $unixtime; ?>',
		token: '<?php echo $token; ?>',
		referUrl: top.location.href,
		logUrl: 'http://www.makeyourruncount.com/share/sharelog_1_3.php', // 日志接口
	 	shareToFriendData: {
	 		title: '<?php echo $shareTitle;?>',
	 		desc: '<?php echo $shareDesc;?>',
	 		link: '<?php echo $shareUrl;?>',
	 		imgUrl: '<?php echo $shareImg;?>',
	 		success: function (res) {
				res.operateType = 'shareToFriend';
				window.Logger(res);
	 		},
		cancel: function (res) {
	 			res.operateType = 'shareToFriend';
	 			window.Logger(res);
	 		},
			fail: function (res) {
	 			res.operateType = 'shareToFriend';
	 			window.Logger(res);
	 		}
	 	},
	 	shareToTimelineData: {
	 		title: '<?php echo $shareDesc;?>',
	 		desc: '<?php echo $shareDesc;?>',
	 		link: '<?php echo $shareUrl;?>',
	 		imgUrl: '<?php echo $shareImg;?>',
	 		success: function (res) {
	 			res.operateType = 'shareToTimeline';
	 			window.Logger(res);
	 		},
	 		cancel: function (res) {
	 			res.operateType = 'shareToTimeline';
	 			window.Logger(res);
	 		},
	 		fail: function (res) {
	 			res.operateType = 'shareToTimeline';
	 			window.Logger(res);
	 		}
	 	},
	 	shareToQQData: {
	 		title: '<?php echo $shareTitle;?>',
	 		desc: '<?php echo $shareDesc;?>',
			link: '<?php echo $shareUrl;?>',
	 		imgUrl: '<?php echo $shareImg;?>',
	 		success: function (res) {
	 			res.operateType = 'shareToQQ';
	 			window.Logger(res);
	 		},
	 		cancel: function (res) {
	 			res.operateType = 'shareToQQ';
	 			window.Logger(res);
	 		},
	 		fail: function (res) {
	 			res.operateType = 'shareToQQ';
	 			window.Logger(res);
	 		}
		},
		shareToWeiboData: {
			title: '<?php echo $shareTitle;?>',
			desc: '<?php echo $shareDesc;?>',
			link: '<?php echo $shareUrl;?>',
			imgUrl: '<?php echo $shareImg;?>',
			success: function (res) {
				res.operateType = 'shareToWeibo';
				window.Logger(res);
			},
			cancel: function (res) {
				res.operateType = 'shareToWeibo';
				window.Logger(res);
			},
			fail: function (res) {
				res.operateType = 'shareToWeibo';
				window.Logger(res);
			}
		}
	};
</script>

<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
  wx.config({
    appId: '<?php echo $signPackage["appId"];?>',
    timestamp: <?php echo $signPackage["timestamp"];?>,
    nonceStr: '<?php echo $signPackage["nonceStr"];?>',
    signature: '<?php echo $signPackage["signature"];?>',
    jsApiList: [
		// 所有要调用的 API 都要加到这个列表中
		'checkJsApi',
		'onMenuShareTimeline',
		'onMenuShareAppMessage',
		'onMenuShareQQ',
		'onMenuShareWeibo'
    ]
  });
</script>
<script type="text/javascript" src="http://www.makeyourruncount.com/share/share_1_3.js"></script>