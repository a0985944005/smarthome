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

// $text = $_GET['tex6t'];
$data 			= file_get_contents('php://input');
$temperature 	= $_POST['temperature'];
$humidity 		= $_POST['humidity'];
$co 			= $_POST['co'];
$lpg 			= $_POST['lpg'];
$smoke 			= $_POST['smoke'];

// $sql = "INSERT INTO `test` (`id`, `text`, `time`) VALUES (NULL, '$data', CURRENT_TIMESTAMP);";
// $result = mysql_query($sql);

if($data!=""){
	$sql_ ="SELECT * FROM `sensor_information`" ;
	$result_ = mysql_query($sql_);
	$num = mysql_num_rows ($result_);
	//資料大於500比清空前100筆資料
	if($num>500){
		$delsql = "DELETE FROM `sensor_information` LIMIT 100";
		$delresult = mysql_query($delsql);

		$sql = "INSERT INTO `sensor_information` (`id`, `co`, `lpg`, `smoke`, `temperature`, `humidity`, `room_id`, `time`) VALUES (NULL, '$co', '$lpg', '$smoke', '$temperature', '$humidity', '530', CURRENT_TIMESTAMP);";
		$result = mysql_query($sql);

	}
	else{
		$sql = "INSERT INTO `sensor_information` (`id`, `co`, `lpg`, `smoke`, `temperature`, `humidity`, `room_id`, `time`) VALUES (NULL, '$co', '$lpg', '$smoke', '$temperature', '$humidity', '530', CURRENT_TIMESTAMP);";
		$result = mysql_query($sql);
	}
	
}


?>