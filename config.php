<?php
//資料庫設定
//資料庫位置
$db_server = "localhost";
//資料庫名稱
$db_name = "smarthome";
//資料庫管理者帳號
$db_user = "root";
//資料庫管理者密碼
$db_passwd = "12345678";

mysql_pconnect($db_server, $db_user, $db_passwd)or die('Connect to MySQL Server error！<br>Message'.mysql_error());
mysql_select_db($db_name);
$result = mysql_query("SET NAMES UTF8");//資料庫連線採UTF8
session_start(); 
//140.128.88.161
//ming
//h4811678

