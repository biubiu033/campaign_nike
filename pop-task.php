<?php
require_once "./header.php";
$conn = connect_to_db();

$apiStartTime = getMicrotime();
$apiUrl = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"];
$loginIP = getClientIP();
$type = 'task-popup-task';

$headerLocation = ''; //跳转地址
$sql = "select * from dtc_join_user where openid = '$openid';";
$result = mysql_query($sql, $conn);

$isUpload=0;
$imgOne = '';
$imgTwo = '';
if (is_resource($result) && mysql_num_rows($result) != 0)
{
    $joinUserRow = mysql_fetch_assoc($result);
    //进到首页，可简单判断，如果用户teamId不为-1且已确认开启任务且已绑定Nike+则留在本页面；其他情况统一交给join-choose处理
    if ($joinUserRow['teamId'] != -1)
    {
        if ($joinUserRow['is_confirm'] != 1 || $_SESSION['userInfo']['isBindNike'] != 1)
        {
            $headerLocation = 'join-choose.php';
        }else{
            if(isset($_GET['type'])){
                $date=$_GET['type'];
            }else{
                $date=1;
            } //任务默认为1
            $query = "SELECT openid FROM dtc_task_popup WHERE openid = '$openid' AND task_id ='$date'";
            $result = mysql_query($query,$conn);
            if(is_resource($result) && mysql_num_rows($result) == 1){
                $query = "SELECT openid,img_one,img_two FROM dtc_task_popup WHERE openid = '$openid' AND task_id ='$date' AND img_one IS NOT NULL";
                $result = mysql_query($query,$conn);
                if(is_resource($result) && mysql_num_rows($result) == 1){
                    $list =  mysql_fetch_assoc($result);
                    $imgOne = $list['img_one'];
                    $imgTwo = $list['img_two'];
                    $isUpload = 1;
                }
            }else{
                $query = "INSERT INTO dtc_task_popup (`openid`,`task_id`,`create_time`,`update_time`)
                          VALUES ('$openid','$date',NOW(),NOW())";
                $result = mysql_query($query,$conn);
            }
        }
    } else
    {
        $headerLocation = 'join-choose.php';
    }
}
else
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
    $sql1 = 'insert into dtc_api_logs (' . $insertkeysql . ') values (' . $insertvaluesql . ')';
    mysql_query($sql1, $conn);

    header("Location:$headerLocation");

    exit();
}

?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
        <!--    引入自己的css-->
        <link rel="stylesheet" href="http://cdn.bootcss.com/weui/1.1.0/style/weui.min.css">
        <link rel="stylesheet" href="http://cdn.bootcss.com/jquery-weui/1.0.0-rc.0/css/jquery-weui.min.css">
        <link rel="stylesheet" href="css/reset.css">
        <link rel="stylesheet" href="css/task.css">
        <!--引入自己的Js-->
        <script src="js/responsive.js"></script>
        <title>里享</title>
        <style>
            .weui-toast{
                z-index: 99999;
            }
            .Minus{
                display: block;
                width: 1.8rem;
                height: 1.8rem;
                line-height: 0;
                position: absolute;
                top:0.5rem;
                right: 0.5rem;
            }
        </style>
    </head>
    <body>
    <div class="wrap theme-run-heros" style="box-sizing: border-box">
        <a class="home" href="main.php"><img src="./img/home-icon1.png" width="58%"></a>
        <div class="title">
            <img src="img/Task-<?php echo $date?>.png" width="55%">
        </div>
        <div class="content">
            <p class="rule">活动细则<big>/</big>RULES</p>
            <div style="height: 2px;background: #ffffff;width: 8.2rem;margin: 0.2rem auto 1rem"></div>
            <p class="rule-content">
                <?php switch ($date){
                    case 1:echo "1.9月27日当天完成2公里，使用NRC记录跑步里程并截图。<br>
2.拍摄一张本人穿着耐克跑步鞋的脚部特写照片。<br>
3.将NRC截图和照片分别上传至活动页面，最先上传的前20位运动员将成为获胜者。<br><br>
            TASK: FINISH 2 KM ON SEP.27. UPLOAD THE NRC SCREENSHOT AND A CLOSE-UP PHOTO OF YOU WEARING YOUR NIKE RUNNING SHOES  TO TASK PAGE.<br>
WINNER: FIRST 20 UPLOADERS OF BOTH VALID SCREENSHOT AND PHOTO.";
                        break;
                    case 2:echo "
                1.邀请一名小伙伴参与活动，两人里程相加，不多不少达到5公里，使用NRC分别记录两人跑步里程并截图。<br>
2.将两张跑步里程截图上传至活动页面，最先上传、且达到要求的前10组运动员（20位）将成为获胜者。两两组队的运动员，只可发送一次。<br><br>
                  TASK: ASK A FRIEND TO FINISH 5 KM WITH YOU. UPLOAD  2  NRC SCREENSHOTS WHICH ADD UP TO EXACTLY 5KM TO TASK PAGE (ONLY ONE PERSON NEEDS TO SEND THE PICS).<br>
WINNER: FIRST 10 GROUPS UPLOADERS OF BOTH VALID SCREENSHOTS. SAME SCREENSHOTS CAN ONLY BE UPLOADED ONE TIME.";
                        break;
                    case 3:echo "
                       1.9月29日，晚上6点至9点间完成3公里，使用NRC记录跑步里程并截图。<br>
2.在晚上9点前将截图上传至活动页面，最先上传、且达到要求的前20位运动员将成为获胜者。<br><br>
                          TASK: FINISH 3 KM DURING 6 P.M. TO 9 P.M. ON SEP.29. UPLOAD THE NRC SCREENSHOT TO TASK PAGE BEFORE 9 P.M.<br>
WINNER: FIRST 20 UPLOADERS OF A VALID SCREENSHOT BEFORE 9 P.M.";
                        break;
                    case 4:echo "
                1.9月30日，早上8点至12点间完成3公里，使用NRC记录跑步里程并截图。<br>
2.在中午12点前将截图上传至活动页面，最先上传、且达到要求的前20位运动员将成为获胜者。<br><br>
                  TASK: FINISH 3 KM DURING 8 A.M. TO 12 A.M. ON SEP.30. UPLOAD THE NRC SCREENSHOT TO TASK PAGE BEFORE 12 A.M.<br>
WINNER: FIRST 20 UPLOADERS OF A VALID SCREENSHOT BEFORE 12 A.M.
                ";
                        break;
                }?>

            </p>
        </div>
        <?php
        if($date!=1 && $date!=2){
            ?>
            <a class="btn-style" id="<?php if ($isUpload==1){ echo "check";}else{echo "upload";}?>" style="width: 19rem">
                <?php if ($isUpload==1){
                    echo "查看图片<br><span>CHECK THE PICTURE</span>";
                }else{
                    echo"上传图片<br><span>UPLOAD PICTURE</span>" ;
                }?></a>
        <?php } ?>
        <a class="btn-style" href="pop-choosedate.php" style="float: left;width: 8rem;margin-left: 4rem">返回<br><span>RETURN</span></a>
        <a class="btn-style" href="pop-task-rank.php?type=<?php echo $date;?>" style="float: right;width: 8rem;margin-right: 4rem">排行榜<br><span>RANKING</span></a>
    </div>
    <div class="popup-box" id="upload-img">
        <form method="post" enctype="multipart/form-data">
            <a class="close"><img src="./img/close-btn.png" width="60%"></a>
            <div class="title1">请上传图片<p>UPLOAD PICTURE</p></div>
            <label for="photo1">
                <div class="upload-btn"><input type="file" name="photo1" id="photo1" accept="image/*"/></div>
                <div class="upload-img" id="upload-img1" style="height: 100%;line-height: 13rem;"></div>
            </label>
            <?php
            if($date==1 || $date == 2){
                ?>
                <label for="photo2">
                    <div class="upload-btn"><input type="file" name="photo2" id="photo2" accept="image/*"/></div>
                    <div class="upload-img" id="upload-img2" style="height: 100%;line-height: 13rem;"></div>
                </label>
            <?php } ?>

            <a class="upload-submit">确认上传<p>UPLOAD</p></a>
        </form>
    </div>
    <div class="popup_dialog">
        <p class="confirm">是否确认上传图片？<span>UPLOAD THE PICTURE NOW?</span></p>
        <div class="popup_btn">
            <a class="sure ">确&nbsp;&nbsp;&nbsp;定<br><span>CONFIRM</span></a>
            <a class="close">取&nbsp;&nbsp;&nbsp;消<br><span>CANCEL</span></a>
        </div>
    </div>
    <div class="popup-box" id="check-img" style=" padding-top: 5rem;padding-bottom: 2rem">
        <a class="close"><img src="./img/close-btn.png" width="60%"></a>
        <!--            <div class="title1">请上传图片<p>UPLOAD PICTURE</p></div>-->
        <div class="upload-img" style="width:100%;height: 12rem;line-height: 13rem;background: url(<?php echo $imgOne; ?>)no-repeat;background-size: contain;background-position: center">
        </div>
        <?php
        if($date==1 || $date == 2){
            ?>
            <div class="upload-img" style="width:100%;height: 12rem;margin-top:1rem;line-height: 13rem;background: url(<?php echo $imgTwo; ?>)no-repeat;background-size: contain;background-position: center">
            </div>
        <?php } ?>
    </div>

    <!--引用Jquery-->
    <script src="js/jquery-2.1.4.js"></script>
    <script src="js/jquery-2.1.3.min.js"></script>
    <script src="http://cdn.bootcss.com/jquery-weui/1.0.0-rc.0/js/jquery-weui.min.js"></script>
    <script type="text/javascript" src="./js/load-image.all.min.js"></script>
    <script type="text/javascript" src="./js/exif.js"></script>
    <script src="js/jquery.bpopup.js"></script>
    <script>
        //    $.showLoading('正在上传中..');
        $('#check').on('click',function () {
            $('#check-img').bPopup({
                positionStyle:'fixed',
                closeClass:'close'
            })
        })

        $('#upload').on('click',function () {
            $('#upload-img').bPopup({
                positionStyle:'fixed',
                closeClass:'close'
            })
        });

        function rotateImg(img, direction,canvas) {
            //最小与最大旋转方向，图片旋转4次后回到原方向
            var min_step = 0;
            var max_step = 3;
            //img = document.getElementById(pid);
            if (img == null)return;
            //img的高度和宽度不能在img元素隐藏后获取，否则会出错
            var height = img.height;
            var width = img.width;
            //var step = img.getAttribute('step');
            var step = 2;
            if (step == null) {
                step = min_step;
            }
            if (direction == 'right') {
                step++;
                //旋转到原位置，即超过最大值
                step > max_step && (step = min_step);
            } else {
                step--;
                step < min_step && (step = max_step);
            }

            var degree = step * 90 * Math.PI / 180;
            var ctx = canvas.getContext('2d');
            switch (step) {
                case 0:
                    canvas.width = width;
                    canvas.height = height;
                    ctx.drawImage(img, 0, 0);
                    break;
                case 1:
                    canvas.width = height;
                    canvas.height = width;
                    ctx.rotate(degree);
                    ctx.drawImage(img, 0, -height);
                    break;
                case 2:
                    canvas.width = width;
                    canvas.height = height;
                    ctx.rotate(degree);
                    ctx.drawImage(img, -width, -height);
                    break;
                case 3:
                    canvas.width = height;
                    canvas.height = width;
                    ctx.rotate(degree);
                    ctx.drawImage(img, -width, 0);
                    break;
            }
        }

        var fileSize = 0;
        var base64a = null;
        var base64b = null;
        $('#photo1').on('change',function(){
            $('.wrong').hide();
            var _this = $(this);
            var minus="<a class='Minus'><img src='./img/minor.png' style='width: 100%!important;'></a>";
            var imgPre = new Image();
            var btn = _this.parent();
            btn.hide();
            btn.parents("label").addClass("active");
            var upImg = btn.siblings("#upload-img1");

            var file = this.files[0];
            var Orientation = null;
            var that = this;

            var expectWidth = 1000;
            var expectHeight = 1000;

            var max_size = 12 * 1024 * 1024 ;

            if(file.type!='image/png'&&file.type!='image/jpg'&&file.type!='image/jpeg'&&file.type!='image/JPG'&&file.type!='image/JPEG'){
                alert('请上传正确的图片');
            }  else {
                if(file.size > max_size){
                    alert('超过12MB');
                }else {
                    fileSize = file.size;
                    console.log(file);

                    if (this.files && file) {
                        EXIF.getData(file, function() {
                            EXIF.getAllTags(this);
                            Orientation = EXIF.getTag(this, 'Orientation');
                            loadImage(
                                that.files[0],
                                function (img) {
                                    var base64data = img.toDataURL("image/jpg");

                                    if (img.width > img.height) {
                                        expectWidth = 1000;
                                        expectHeight = expectWidth * img.height / img.width;
                                    } else {
                                        expectHeight = 1000;
                                        expectWidth = expectHeight * img.width / img.height;
                                    }
                                    var canvas = document.createElement("canvas");
                                    var ctx = canvas.getContext("2d");
                                    canvas.width = expectWidth;
                                    canvas.height = expectHeight;

                                    var image = new Image();
                                    image.onload = function() {
                                        ctx.drawImage(this, 0, 0, expectWidth, expectHeight);
                                        //修复ios
                                        if (navigator.userAgent.match(/iphone/i))
                                        {
                                            console.log('iphone');
                                            //alert(expectWidth + ',' + expectHeight);
                                            //如果方向角不为1，都需要进行旋转 added by lzk
                                            if (Orientation != "" && Orientation != 1) {
//                            alert('旋转处理');
                                                switch (Orientation) {
                                                    case 6://需要顺时针（向左）90度旋转
//                                    alert('需要顺时针（向左）90度旋转');
                                                        rotateImg(this, 'left', canvas);
                                                        break;
                                                    case 8://需要逆时针（向右）90度旋转
//                                    alert('需要顺时针（向右）90度旋转');
                                                        rotateImg(this, 'right', canvas);
                                                        break;
                                                    case 3://需要180度旋转
//                                    alert('需要180度旋转');
                                                        rotateImg(this, 'right', canvas);//转两次
                                                        rotateImg(this, 'right', canvas);
                                                        break;
                                                }
                                            }
                                        }


                                        //回到原本的程序
                                        base64a = canvas.toDataURL("image/jpeg", 1.0);
//                                        console.log(base64);
                                        imgPre.src = base64a;
                                        imgPre.onload = function() {
                                            btn.siblings("#upload-img1").html(imgPre);
                                            $(upImg).append(minus);
                                            //在此地获取图片长宽比，然后配置img
                                            var getContainer=$('#upload-img1');
                                            var getIMG=$('#upload-img1 img:first');
                                            var fw=getContainer.width();
                                            var fh=getContainer.height();
                                            var boxHW = fh/fw;
                                            var iw=canvas.width;
                                            var ih=canvas.height;
                                            imgHW = ih/iw;
                                            if(imgHW>boxHW){
                                                console.log(1);
                                                getIMG.css('height',fh);
                                                getIMG.css('width',fh/imgHW);
                                            }else {
                                                console.log(2);
                                                getIMG.css('width',fw);
                                                getIMG.css('height',imgHW*fw);
                                            }
                                        }
//                                            prevDiv.attr({'src': base64});/
//                                        $('#imgDown').attr({'src': base64});
                                    };
                                    image.src = base64data;
                                },
                                {
                                    maxWidth: expectWidth,
                                    maxHeight: expectHeight,
                                    canvas: true
                                }
                            );
                        });

                    }
                }
            }
        });
        $('#photo2').on('change',function(){
            $('.wrong').hide();
            var _this = $(this);
            var minus="<a class='Minus'><img src='./img/minor.png' style='width: 100%!important;'></a>";
            var imgPre = new Image();
            var btn = _this.parent();
            btn.hide();
            btn.parents("label").addClass("active");
            var upImg = btn.siblings("#upload-img2");

            var file = this.files[0];
            var Orientation = null;
            var that = this;

            var expectWidth = 1000;
            var expectHeight = 1000;

            var max_size = 12 * 1024 * 1024 ;

            if(file.type!='image/png'&&file.type!='image/jpg'&&file.type!='image/jpeg'&&file.type!='image/JPG'&&file.type!='image/JPEG'){
                alert('请上传正确的图片');
            }  else {
                if(file.size > max_size){
                    alert('超过12MB');
                }else {
                    fileSize = file.size;
                    console.log(file);

                    if (this.files && file) {
                        EXIF.getData(file, function() {
                            EXIF.getAllTags(this);
                            Orientation = EXIF.getTag(this, 'Orientation');
                            loadImage(
                                that.files[0],
                                function (img) {
                                    var base64data = img.toDataURL("image/jpg");

                                    if (img.width > img.height) {
                                        expectWidth = 1000;
                                        expectHeight = expectWidth * img.height / img.width;
                                    } else {
                                        expectHeight = 1000;
                                        expectWidth = expectHeight * img.width / img.height;
                                    }
                                    var canvas = document.createElement("canvas");
                                    var ctx = canvas.getContext("2d");
                                    canvas.width = expectWidth;
                                    canvas.height = expectHeight;

                                    var image = new Image();
                                    image.onload = function() {
                                        ctx.drawImage(this, 0, 0, expectWidth, expectHeight);
                                        //修复ios
                                        if (navigator.userAgent.match(/iphone/i))
                                        {
                                            console.log('iphone');
                                            //alert(expectWidth + ',' + expectHeight);
                                            //如果方向角不为1，都需要进行旋转 added by lzk
                                            if (Orientation != "" && Orientation != 1) {
//                            alert('旋转处理');
                                                switch (Orientation) {
                                                    case 6://需要顺时针（向左）90度旋转
//                                    alert('需要顺时针（向左）90度旋转');
                                                        rotateImg(this, 'left', canvas);
                                                        break;
                                                    case 8://需要逆时针（向右）90度旋转
//                                    alert('需要顺时针（向右）90度旋转');
                                                        rotateImg(this, 'right', canvas);
                                                        break;
                                                    case 3://需要180度旋转
//                                    alert('需要180度旋转');
                                                        rotateImg(this, 'right', canvas);//转两次
                                                        rotateImg(this, 'right', canvas);
                                                        break;
                                                }
                                            }
                                        }


                                        //回到原本的程序
                                        base64b = canvas.toDataURL("image/jpeg", 1.0);
//                                        console.log(base64);
                                        imgPre.src = base64b;
                                        imgPre.onload = function() {
                                            btn.siblings("#upload-img2").html(imgPre);
                                            $(upImg).append(minus);

                                            //在此地获取图片长宽比，然后配置img
                                            var getContainer=$('#upload-img2');
                                            var getIMG=$('#upload-img2 img:first');
                                            var fw=getContainer.width();
                                            var fh=getContainer.height();
                                            var boxHW = fh/fw;
                                            var iw=canvas.width;
                                            var ih=canvas.height;
                                            imgHW = ih/iw;
                                            if(imgHW>boxHW){
                                                console.log(1);
                                                getIMG.css('height',fh);
                                                getIMG.css('width',fh/imgHW);
                                            }else {
                                                console.log(2);
                                                getIMG.css('width',fw);
                                                getIMG.css('height',imgHW*fw);
                                            }
                                        }
//                                            prevDiv.attr({'src': base64});/
//                                        $('#imgDown').attr({'src': base64});
                                    };
                                    image.src = base64data;
                                },
                                {
                                    maxWidth: expectWidth,
                                    maxHeight: expectHeight,
                                    canvas: true
                                }
                            );
                        });

                    }
                }
            }
        });

        $(function() {
            var img = null;
            var img2 = null;
            <?php
            if($date==1 || $date == 2){
            ?>
            $('.upload-submit').click(function(e){
//                alert('活动已关闭，无法上传图片');
//                return false;
                e.preventDefault();
                if(base64a!==null && base64b!==null){
                    img = base64a;
                    img2 = base64b;
                }
                if ( !img&&!img2) {
                    alert("请选择图片后再上传");
                    return false;
                }
                else {
                    $('.popup_dialog').bPopup({
                        positionStyle:'fixed',
                        closeClass:'close',
                        modalClose: false
                    })
                }

            });
            <?php }else{ ?>
            $('.upload-submit').click(function(e){
//                alert('活动已关闭，无法上传图片');
//                return false;
                e.preventDefault();
                if(base64a!==null){
                    img = base64a;
                }
                if ( !img) {
                    alert("请选择图片后再上传");
                    return false;
                }
                else {
                    $('.popup_dialog').bPopup({
                        positionStyle:'fixed',
                        closeClass:'close',
                        modalClose: false
                    })
                }

            });
            <?php } ?>
            $('.sure').on('click',function () {
                console.log(123);
                $.showLoading('正在上传中..');
                //加载
                <?php if($date==1 || $date == 2){ ?>
                $.ajax({
                    url: "apiPopUpload.php",
                    method: 'post',
                    dataType: 'json',
                    data: {
                        "img" : img,
                        "img2" : img2,
                        "date": '<?php echo $date; ?>'
                    },
                    beforeSend: function () {
                        $.showLoading('正在上传中..');
                    },
                    success: function(res) {
                        if(res == '1'){
                            alert('上传成功');
                            $.hideLoading();
                            console.log('success');
//                            location.reload();
                            window.location.href = "pop-task.php?type=3&a=<?php echo rand(); ?>";
                        }
                        else{
                            alert('网络错误');
                            $.hideLoading();
                        }
                    },
                    error: function(){
                        alert('当前网络状况较差，请检查网络后再试!');
                        $.hideLoading();
                        return false;
                    }
                });
                <?php }else{ ?>
                $.ajax({
                    url: "apiPopUpload.php",
                    method: 'post',
                    dataType: 'json',
                    data: {
                        "img" : img,
                        "date": '<?php echo $date; ?>'
                    },
                    beforeSend: function () {
                        $.showLoading('正在上传中..');
                    },
                    success: function(res) {
                        console.log(res);
                        if(res == '1'){
                            alert('上传成功');
                            $.hideLoading();
                            console.log('success');
//                            location.reload();
                            window.location.href = "pop-task.php?type=3&a=<?php echo rand(); ?>";

                        }
                        else{
                            alert('网络错误');
                            $.hideLoading();
                        }
                    },
                    error: function(){
                        alert('当前网络状况较差，请检查网络后再试!');
                        $.hideLoading();
                        return false;
                    }
                });

                <?php } ?>
            })


        });


    </script>
    </body>
    </html>
<?php
//记录页面请求日志

$apiEndTime = getMicrotime();
$fetchTime = intval(($apiEndTime - $apiStartTime) * 1000);
$logArr = array(
    'openid' => "$openid",
    'type' => $type,
    'ip' => $loginIP,
    'url' => $apiUrl,
    'result' => mysql_real_escape_string(json_encode_cn($joinUserRow)),
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