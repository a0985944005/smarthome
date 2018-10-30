<?PHP
include "config.php";
$name=$_POST['name'];
$sex=$_POST['sex'];
$account=$_POST['account'];
$password=$_POST['password'];
$ckpassword=$_POST['ckpassword'];
$email=$_POST['email'];
$phone=$_POST['phone'];
$room=$_POST['room'];
$account_same = 0;
$_SESSION['id'] = 0;

$sql = "SELECT `account` FROM `member`";
$result = mysql_query($sql);


while ($row = mysql_fetch_array($result))
{
 if($row['account'] == $account){
 	$account_same = $account_same+1;
 }
}
if(md5($password) == md5($ckpassword) and $account_same == 0){
	$passwordmd5 = md5($password);
	//新增到會員表單
	$sql = "INSERT INTO `member` (`id`, `name`, `phone`, `email`, `account`, `password`, `sex`, `room_id`, `class`, `time`) VALUES (NULL, '$name', '$phone', '$email', '$account', '$passwordmd5', '$sex', '$room', '0', CURRENT_TIMESTAMP)";
	$result = mysql_query($sql);
	//取得剛剛新增的ID
	$sql = "SELECT * FROM `member` order by `id` DESC limit 1 ";
    $result = mysql_query($sql);
    $row = mysql_fetch_array($result);
    $keyid = $row['id'];

	//新增到會員狀態表單
	$sql = "INSERT INTO `member_check` (`id`, `user_id`, `LINE_id`, `status`, `time`) VALUES (NULL,'$account', '', '0', CURRENT_TIMESTAMP);";
	$result = mysql_query($sql);

	//新增到KEY表單
	$line_key = line_key(5);
	$line_key.=$keyid;
	$sql = "INSERT INTO `check_key` (`id`, `line_key`, `status`, `LINE_id`, `time`) VALUES (NULL, '$line_key', '0', '', CURRENT_TIMESTAMP);";
	$result = mysql_query($sql);

	$_SESSION['id'] = 1;
	echo "<script type = 'text/javascript'>";
	echo "alert('註冊成功！！');";
	echo "location.href='scuess.php';";
	echo "</script>";
}
elseif($account_same != 0){
	echo "<script type = 'text/javascript'>";
	echo "alert('此帳號已使用過!');";
	echo "location.href='registered.html';";
	echo "</script>";
}
elseif (md5($password) != md5($ckpassword)) {
	echo "<script type = 'text/javascript'>";
	echo "alert('確認密碼與密碼不同!');";
	echo "location.href='registered.html';";
	echo "</script>";
	
}


function line_key($sum){
	$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

	$password = '@';
	for ( $i = 0; $i < $sum; $i++ ) {
		// 这里提供两种字符获取方式
		// 第一种是使用 substr 截取$chars中的任意一位字符；
		// 第二种是取字符数组 $chars 的任意元素
		// $password .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		$password .= $chars[ mt_rand(0, strlen($chars) - 1) ];
	}
	return $password;
}			


?>