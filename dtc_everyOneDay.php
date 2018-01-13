<?php

include ("/var/www/html/www.makeyourruncount.com/php/funcs.php"); //引入微信类
$conn = connect_to_db();
echo date("Y-m-d H:i:s", time()) . "\t开始计算每人每天跑量，并且导出数据---------------------------------------\n\n";
$beginTime = '2017-08-01';
$nowTime = date('Y-m-d');

//读取runlogall信息
$userLog = array();
$query = "SELECT openid,SUM(distance) `sum`,DATE_FORMAT(startTime1,'%Y-%m-%d') `time`,createTime FROM yiqipao_runlog_all 
WHERE donateProject=62 AND teamId>100
GROUP BY openid,DATE_FORMAT(startTime1,'%Y-%m-%d')";

$result = mysql_query($query,$conn);

while ($row = mysql_fetch_assoc($result)){
    $openid = $row['openid'];
    $time = $row['time'];
    if(!isset( $userLog[$openid][$beginTime])){
        $thisBeginTime = $beginTime;
        while($thisBeginTime != $nowTime){
            $userLog[$openid][$thisBeginTime] = 0;
            $thisBeginTime = date('Y-m-d',strtotime($thisBeginTime)+86400);
        }
    }
    if(strtotime($beginTime)<strtotime($time) &&strtotime($nowTime)>strtotime($time)){
        $userLog[$openid][$time] = $row['sum'];
    }
}

//直接读取用户排名信息
$rank = array();
$query = "SELECT 
a.`openid`, sum(a.`long`) as longTotal,a.`teamId` ,
b.fullname,b.department,
c.teamName,c.teamNameEN
FROM yiqipao_member_project a LEFT JOIN dtc_join_user b 
ON a.openid = b.openid
LEFT JOIN dtc_team c
ON b.department = c.department
WHERE a.`pid`= 62 and a.teamId > 100 and a.teamId != 240 group by a.openid ORDER BY sum(a.`long`) DESC, a.id asc";
$result = mysql_query($query,$conn);
while ($row = mysql_fetch_assoc($result)){
    $openid = $row['openid'];
    $rank[$openid] = $row;
    if(isset($userLog[$openid])){
        foreach ($userLog[$openid] AS $key => $value){
            $rank[$openid][$key] = $value;
        }
    }else{
        $thisBeginTime = $beginTime;
        while($thisBeginTime != $nowTime){
            $rank[$openid][$thisBeginTime] = 0;
            $thisBeginTime = date('Y-m-d',strtotime($thisBeginTime)+86400);
        }
    }

}

//print_r($rank);


/** Include PHPExcel */
require_once './lib/PHPExcel.php';


// Create new PHPExcel object
echo date('H:i:s') , " Create new PHPExcel object";
$objPHPExcel = new PHPExcel();

// Set document properties
echo date('H:i:s') , " Set document properties";
$objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
    ->setLastModifiedBy("Maarten Balliauw")
    ->setTitle("Office 2007 XLSX Test Document")
    ->setSubject("Office 2007 XLSX Test Document")
    ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
    ->setKeywords("office 2007 openxml php")
    ->setCategory("Test result file");


// Add some data, we will use printing features
echo date('H:i:s') , " Add some data";
$issetHead = false;
$row = 1;
$column = 97; //chr($column) == 'a';
foreach ($rank AS $key => $value) {
    if(!$issetHead){
        foreach ($value AS $key2 => $value2){
            $objPHPExcel->getActiveSheet()->setCellValue(chr($column++) . $row++, $key);
        }
    }

    foreach ($value AS $key2 => $value2){
        $objPHPExcel->getActiveSheet()->setCellValue(chr($column++) . $row++, $value2);
    }
}

// Set header and footer. When no different headers for odd/even are used, odd header is assumed.
echo date('H:i:s') , " Set header/footer";
$objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader('&L&G&C&HPlease treat this document as confidential!');
$objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle() . '&RPage &P of &N');

// Add a drawing to the header
echo date('H:i:s') , " Add a drawing to the header";
$objDrawing = new PHPExcel_Worksheet_HeaderFooterDrawing();
$objDrawing->setName('PHPExcel logo');
$objDrawing->setPath('./images/phpexcel_logo.gif');
$objDrawing->setHeight(36);
$objPHPExcel->getActiveSheet()->getHeaderFooter()->addImage($objDrawing, PHPExcel_Worksheet_HeaderFooter::IMAGE_HEADER_LEFT);

// Set page orientation and size
echo date('H:i:s') , " Set page orientation and size";
$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

// Rename worksheet
echo date('H:i:s') , " Rename worksheet";
$objPHPExcel->getActiveSheet()->setTitle('Printing');


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);


// Save Excel 2007 file
echo date('H:i:s') , " Write to Excel2007 format";
$callStartTime = microtime(true);

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save(str_replace('.php', '.xlsx', __FILE__));
$callEndTime = microtime(true);
$callTime = $callEndTime - $callStartTime;

echo date('H:i:s') , " File written to " , str_replace('.php', '.xlsx', pathinfo(__FILE__, PATHINFO_BASENAME));
echo 'Call time to write Workbook was ' , sprintf('%.4f',$callTime) , " seconds";
// Echo memory usage
echo date('H:i:s') , ' Current memory usage: ' , (memory_get_usage(true) / 1024 / 1024) , " MB";

// Echo done
echo date('H:i:s') , " Done writing files";
echo 'Files have been created in ' , getcwd();

/**
 * Created by PhpStorm.
 * User: a7849
 * Date: 2017/8/30
 * Time: 10:47
 */