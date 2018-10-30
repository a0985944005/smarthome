<?php 
include "config.php";
// if($_SESSION['id'])//判斷SESSION是不是空直
//     {
//         echo "<script>";
//         echo "location.href='https://www.google.com.tw/';";
//         echo "</script>";
//     }   

    $sql = "SELECT * FROM `check_key` ORDER BY `id` DESC limit 1 ";
    $result = mysql_query($sql);
    $row = mysql_fetch_array($result);
    $linekey = $row['line_key'];
    
    ?>
<!DOCTYPE html>
<html>
  <head>
    <!-- Required meta tags always come first -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <script  src="https://code.jquery.com/jquery-3.3.1.js"></script>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.rawgit.com/twbs/bootstrap/v4-dev/dist/css/bootstrap.css">
    <style>
      body{
        font-family: '微軟正黑體' !important;
      }
      label{
        font-weight: bold;
      }
    </style>
  </head>
  <body>

    <div class="container">
      <h1 align="center" ><br /><strong>以無線網路技術控制宿舍設備</strong></h1>
      <h2 align="center" ><br /><strong>-- 註冊成功!! --</strong></h2>
      <h2 align="center" ><br /><strong>請您加入好友後輸入:<?=$linekey?></strong></h2>
      <div align="center"><img  style="width: 60%;" src="https://qr-official.line.me/M/QCrDNjbrVU.png"></div>
 
    </div>
    <!-- jQuery first, then Bootstrap JS. -->
    <script src="https://cdn.bootcss.com/jquery/1.12.3/jquery.min.js"></script>
    <script src="https://cdn.rawgit.com/twbs/bootstrap/v4-dev/dist/js/bootstrap.js"></script>
  </body>
</html>