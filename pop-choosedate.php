<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
    <!--    引入自己的css-->
    <link rel="stylesheet" href="css/reset.css">
    <!--引入自己的Js-->
    <script src="js/responsive.js"></script>
    <!--引用Jquery-->
    <script src="js/jquery-2.1.3.min.js"></script>
    <title>里享</title>
    <style>
        @font-face {
            font-family: 'fornike365';
            src: url('./font/tradegothicfornike365-bdcn.eot');
            src: local('☺'), url('./font/tradegothicfornike365-bdcn.woff') format('woff'), url('./font/tradegothicfornike365-bdcn.ttf') format('truetype'), url('./font/tradegothicfornike365-bdcn.svg') format('svg');
            font-weight: normal;
            font-style: normal;
        }
        body{
            background: #fe8c03;
        }
        .theme-run-heros {
            background:url(./img/bg.png) no-repeat;
            background-size: cover;
            background-position: center;
            min-height: 43.0rem;
        }
        .item{
            text-align: center;
            height: 10.3rem;
            box-sizing: border-box;
            padding-top: 1.5rem;
            margin-bottom: 0.6rem;
        }
        .op8{
            background: rgba(252, 86, 20, 0.8);
        }
        .op3{
            background: rgba(252, 86, 20, 0.3);
        }
        .btn-style {
            padding-top: 0.5rem;
            box-sizing: border-box;
            display: block;
            background: rgba(255, 255, 255, 0.8);
            box-shadow: 0 3px 0 #eb5e0c;
            border: 2px solid #ea5012;
            width: 14rem;
            height: 3.2rem;
            line-height: 1.1rem;
            border-radius: 10px;
            text-align: center;
            color: #fc5219;
            font-family: 'fornike365';
            font-size: 1.2rem;
            font-weight: bold;
            font-style: oblique;
            margin: 0.5rem auto 0;
        }
        .comingsoon{
            background: rgba(255, 255, 255, 0.3);
        }
        .btn-style span{
            font-size: 1rem;
            font-family: 'fornike365';
            font-style: oblique;
            font-weight: 100;
        }
        .home{
           position: absolute;
            top: 1.5rem;
            left: 1.5rem;
        }
    </style>
</head>
<body>
<div class="wrap theme-run-heros" >
    <a class="home" href="main.php"><img src="./img/home-icon1.png" width="58%"></a>
   <div class="item op3">
       <img src="./img/Task-1.png?a=123" width="40%">
    <?php if (time()<1506431673){
        echo "<a class='btn-style comingsoon'>未开启<br><span>COMIMG SOON</span></a>";
    } elseif(time()>=1506431673&&time()<=1506527999){
        echo "<a class='btn-style ' href='pop-task.php?type=1'>进入任务<br><span>ENTER</span></a>";
    }else{
        echo "<a class='btn-style' href='pop-task.php?type=1'>已结束<br><span>COMPLETE</span></a>";
    }?>
   </div>
    <div class="item op8">
        <img src="./img/Task-2.png?a=456" width="40%">
       <?php if (time()<1506515048){
            echo "<a class='btn-style comingsoon'>未开启<br><span>COMIMG SOON</span></a>";
        } elseif(time()>=1506515048&&time()<=1506614399){
            echo "<a class='btn-style ' href='pop-task.php?type=2'>进入任务<br><span>ENTER</span></a>";
        }else{
            echo "<a class='btn-style' href='pop-task.php?type=2'>已结束<br><span>COMPLETE</span></a>";
        }?>
    </div>
    <div class="item op3">
        <img src="./img/Task-3.png?a=789" width="40%">
       <?php if (time()<1506600000){
            echo "<a class='btn-style comingsoon'>未开启<br><span>COMIMG SOON</span></a>";
        } elseif(time()>=1506600000&&time()<=1506690000){
            echo "<a class='btn-style ' href='pop-task.php?type=3'>进入任务<br><span>ENTER</span></a>";
        }else{
            echo "<a class='btn-style' href='pop-task.php?type=3'>已结束<br><span>COMPLETE</span></a>";
        }?>
    </div>
    <div class="item op8" style="margin-bottom: 0">
        <img src="./img/Task-4.png?a=489" width="40%">
        <?php if (time()<1506686986){
            echo "<a class='btn-style comingsoon' >未开启<br><span>COMIMG SOON</span></a>";
        } elseif(time()>=1506686986&&time()<=1506744000){
            echo "<a class='btn-style ' href='pop-task.php?type=4'>进入任务<br><span>ENTER</span></a>";
        }else{
            echo "<a class='btn-style' href='pop-task.php?type=4'>已结束<br><span>COMPLETE</span></a>";
        }?>
    </div>
</div>
<script>
    $('.comingsoon').attr('disabled','disabled');
</script>
</body>
</html>