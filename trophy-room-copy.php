<?php
    $is_get_1km=1;
    $is_get_10km=1;
    $is_get_share1=1;
    $is_get_3d5k=0;
    $is_get_100km=0;
    $is_get_sharemany=0;
    $is_get_200km=0;
    $is_get_team=0;
    $is_get_king=0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
    <!--    引入自己的css-->
    <link rel="stylesheet" href="css/reset.css">
    <link rel="stylesheet" href="css/trophy-room.css"/>
    <link rel="stylesheet" href="css/swiper-3.4.2.min.css">
    <!--引入自己的Js-->
    <script src="js/responsive.js"></script>
    <!--引用Jquery-->
    <script src="js/jquery-2.1.3.min.js"></script>
    <title>里享</title>
</head>
<body>
    <div class="trophy-room">
        <div class="trophy-item">
            <div class="cup">
                <div class="cup-item"><img src="img/trophy-1km<?php if ($$is_get_1km!=1){echo "-no";} ?>.png" width="51%"></div>
                <div class="cup-item" style="margin: 0 1rem;"><img src="img/trophy-10km<?php if ($$is_get_10km!=1){echo "-no";} ?>.png" width="51%"></div>
                <div class="cup-item"><img src="img/trophy-share1<?php if ( $is_get_share1!=1){echo "-no";} ?>.png" width="51%"></div>
            </div>
            <div class="cup-name-1">
<!--                <p>第一个1公里<br><span>MY FIRST 1 KM ACHIEVED</span></p>
                <p>10公里达成<br><span>MY FIRST 10 KM ACHIEVED</span></p>
                <p>第一次分享<br><span>MY FIRST POST</span></p>-->
            </div>
        </div>
        <div class="trophy-item">
            <div class="cup">
                <div class="cup-item"><img src="img/trophy-3d5k<?php if ($is_get_3d5k!=1){echo "-no";} ?>.png" width="51%"></div>
                <div class="cup-item" style="margin: 0 1rem;"><img src="img/trophy-100km<?php if ( $is_get_100km!=1){echo "-no";} ?>.png" width="51%"></div>
                <div class="cup-item"><img src="img/trophy-sharemany<?php if ($is_get_sharemany!=1){echo "-no";} ?>.png" width="51%"></div>
            </div>
            <div class="cup-name-2">
<!--                <p>连续3天5公里<br><span>5 KM ACHIEVED FOR <br>3 CONSECUTIVE DAYS</span></p>
                <p>100公里达成<br><span>MY FIRST 10 KM ACHIEVED</span></p>
                <p>分享达人<br><span>MY FIRST GURU</span></p>-->
            </div>
        </div>
        <div class="trophy-item">
            <div class="cup">
                <div class="cup-item"><img src="img/trophy-200km<?php if ($is_get_200km!=1){echo "-no";} ?>.png" width="48%"></div>
                <div class="cup-item" style="margin-left: 1.2rem;"><img src="img/trophy-team<?php if ($is_get_team!=1){echo "-no";} ?>.png" width="48%"></div>
            </div>
            <div class="cup-name-3">
<!--                <p>200公里达成<br><span>200 KM ACHIEVED</span></p>
                <p>团队胜利<br><span>TEAM VICTORY</span></p>-->
            </div>
        </div>
        <div class="trophy-king">
            <img src="img/trophy-king<?php if ($is_get_king!=1){echo "-no";} ?>.png" width="28%">
            <img src="img/trophy-king-name.png" width="24%">
        </div>
        <a class="share">分享好友</a>
    </div>
</body>
</html>