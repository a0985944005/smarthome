<?php
include "config.php";
                                                                                
$json_string = file_get_contents('php://input');
$json_object = json_decode($json_string);

$channel_access_token = 'g0qv6PfQZkUBDXgLZEYqHEkIzqLpXzuJ1yzFC1pq+yhHWYh8xvRSxsPLTtoXyKV5cls2ftmnaA7KiciG+J4z2OlhE9Qed/mGz7oY4+JaiwGH48dYjgSVsfrRZt58Kwtzehy6UCDuuTlZ7OiZglMNLgdB04t89/1O/w1cDnyilFU=';
$reply_token = $json_object->{"events"}[0]->{"replyToken"};
$message_type = $json_object->{"events"}[0]->{"type"};
$user_id = $type = $json_object->{"events"}[0]->{"source"}->{"userId"};

	if($message_type == "message"){
		$message = $json_object->{"events"}[0]->{"message"}->{"text"};
		$post_data=getmessage($message,$user_id,$reply_token);

	}
	else{
		//postback
		$postback = $json_object->{"events"}[0]->{"postback"}->{"data"};
		$post_data=getpostback($postback,$user_id,$reply_token);
	}


linesend($post_data,$channel_access_token);

//紀錄傳進來的資料
// 開個新檔
// $f = fopen('test.json','W');
// // 續寫檔案
// $f = fopen("test.json","at");

// fwrite($f,$json_string."\n".$message_type."\n"); // 寫入內容
// fclose($f); // 關閉

// // 開啟頁面紀錄時間
// $fl = fopen('igung_log.txt','at');
// fwrite($fl,date("Y-m-d H:i:s")."\n");
// fclose($fl);



function linesend($post_data,$channel_access_token){
	
			$ch = curl_init("https://api.line.me/v2/bot/message/reply");
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			    'Content-Type: application/json; charser=UTF-8',
			    'Authorization: Bearer ' . $channel_access_token
			));
			$result = curl_exec($ch);
			curl_close($ch);
						
								  }


function getsend($action){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch,CURLOPT_URL,"http://192.168.0.134/arduino/".$action);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13");
	$data = curl_exec($ch);
	curl_close($ch);
}

function  getmessage($message,$user_id,$reply_token){

	$action = substr($message, 0,1);
	$member_id = substr($message,6);
	if($action == '@'){
			$sql = "SELECT * FROM `check_key` WHERE `line_key` = '$message'";
			$result = mysql_query($sql);
			$row = mysql_fetch_array($result);
			$sum = mysql_num_rows($result);
			$id = $row['id'];

			if($sum and $row['status']==0){
					//update KEY 使用KEY
					$update = "UPDATE `check_key` SET `status` = '1', `LINE_id` = '$user_id', `time` = CURRENT_TIMESTAMP WHERE `check_key`.`id` = '$id'";
					$resultupdate = mysql_query($update);
					//抓出關聯的帳號
					$sql = "SELECT * FROM `member` WHERE `id` = '$member_id'";
					$result = mysql_query($sql);
					$row= mysql_fetch_array($result);
					$member_account = $row['account'];
					$member_name = $row['name'];
					 //把剛剛註冊的LINE ID 會進去
					$memberupdate = "UPDATE `member_check` SET `LINE_id` = '$user_id', `status` = '1', `time` = CURRENT_TIMESTAMP WHERE `member_check`.`user_id` = '$member_account'" ;
					$memberupdateresult = mysql_query($memberupdate);
					//成功回傳
					$message = $member_name."開通功能成功";
					$post_data = [
													  "replyToken" => $reply_token,
													  "messages" => [
													    [
													      "type" => "text",
													      "text" => $message
													    ]
													  ]
													];			
			}
			else{

				$message ="無效key";
				$post_data = [
													  "replyToken" => $reply_token,
													  "messages" => [
													    [
													      "type" => "text",
													      "text" => $message
													    ]
													  ]
													];						
			}
	}
	else{
		switch($message){

					case "宿舍設備":

						$sql = "SELECT * FROM `electric_device` where id > 2";
 						$result = mysql_query($sql);
 						$i=1;
 						while($list=mysql_fetch_array($result)){  //判斷是否還有資料沒有取完，如果取完，則停止while迴圈。
							
							$list_arr[$i]=$list;
							if($list_arr[$i]['status']=='0'){
								$list_arr[$i]['status']="off";
							}
							else{
								$list_arr[$i]['status']="on";
							}
							$i++;
						}

							//  多個按鈕的回應
 						
						$post_data = [
						  "replyToken" => $reply_token,
						  "messages" => [
						    [
						           "type"=> "template",
						           "altText"=> "This is a buttons template",
						           "template"=> [
						                    "type"=> "buttons",
						                    "thumbnailImageUrl"=> "https://3.bp.blogspot.com/-aCR_j-iAcaU/VgqC-UM__QI/AAAAAAAAHAo/wzVbRUYtBhw/s1600/sy_20111011141208935020.jpg",
						                    "imageAspectRatio"=> "rectangle",
						                    "imageSize"=> "cover",
						                    "imageBackgroundColor"=> "#FFFFFF",
						                    "title"=> "宿舍設備",
						                    "text"=> "請選擇",
						                    "actions"=> [
						                        [
						                          "type"=> "postback",
						                          "label"=> $list_arr[1]['device']."　(". $list_arr[1]['status'].")",
						                          "data"=> "action=onoff&device=3"
						                        ],
						                        [
						                          "type"=> "postback",
						                          "label"=> $list_arr[2]['device']."　(".$list_arr[2]['status'].")",
						                          "data"=> "action=onoff&device=4"
						                        ],
						                        [
						                          "type"=> "postback",
						                          "label"=> $list_arr[3]['device']."　(".$list_arr[3]['status'].")",
						                          "data"=> "action=onoff&device=5"
						                        ],
						                        [
						                          "type"=> "postback",
						                          "label"=> $list_arr[4]['device']."　(".$list_arr[4]['status'].")",
						                          "data"=> "action=onoff&device=6"
						                        ]
						                    ]
						                ]
						          ]
						     ]
						];

					break;

					case "浴室設備":

						$sql = "SELECT * FROM `electric_device` where id < 3";
 						$result = mysql_query($sql);
 						$i=1;
 						while($list=mysql_fetch_array($result)){  //判斷是否還有資料沒有取完，如果取完，則停止while迴圈。
							
							$list_arr[$i]=$list;
							if($list_arr[$i]['status']=='0'){
								$list_arr[$i]['status']="off";
							}
							else{
								$list_arr[$i]['status']="on";
							}
							$i++;
						}

							//  多個按鈕的回應
 						
						$post_data = [
						  "replyToken" => $reply_token,
						  "messages" => [
						    [
						           "type"=> "template",
						           "altText"=> "This is a buttons template",
						           "template"=> [
						                    "type"=> "buttons",
						                    "thumbnailImageUrl"=> "https://3.bp.blogspot.com/-aCR_j-iAcaU/VgqC-UM__QI/AAAAAAAAHAo/wzVbRUYtBhw/s1600/sy_20111011141208935020.jpg",
						                    "imageAspectRatio"=> "rectangle",
						                    "imageSize"=> "cover",
						                    "imageBackgroundColor"=> "#FFFFFF",
						                    "title"=> "浴室設備",
						                    "text"=> "請選擇",
						                    "actions"=> [
						                        [
						                          "type"=> "postback",
						                          "label"=> $list_arr[1]['device']."　(". $list_arr[1]['status'].")",
						                          "data"=> "action=onoff&device=1"
						                        ],
						                        [
						                          "type"=> "postback",
						                          "label"=> $list_arr[2]['device']."　(".$list_arr[2]['status'].")",
						                          "data"=> "action=onoff&device=2"
						                        ]
						                    ]
						                ]
						          ]
						     ]
						];

					break;

					case "宿舍狀態":

						$sql ="SELECT * FROM `sensor_information` ORDER BY `time`DESC limit 1";
						$result = mysql_query($sql);
						$row = mysql_fetch_array($result);

					$message="一氧化碳濃度　：".$row['co']."　ppm\n液化石油氣濃度：".$row['lpg']."　ppm\n煙霧濃度　　　：".$row['smoke']."　ppm\n室內溫度　　　：".$row['temperature']."  度\n"."室內溼度　　　：".$row['humidity']."　  %";
					$post_data = [
					  "replyToken" => $reply_token,
					  "messages" => [
					    [
					      "type" => "text",
					      "text" => $message
					    ]
					  ]
					];

					break;

					default:
							if($message=='130'or$message=='131'or$message=='120'or$message=='121'or$message=='110'or$message=='111'or$message=='100'or$message=='101'){
								getsend($message);
								$post_data = [
												  "replyToken" => $reply_token,
												  "messages" => [
												    [
												      "type" => "text",
												      "text" => $message
												    ]
												  ]
												];		
							}
								else{
									
							$post_data = [
												  "replyToken" => $reply_token,
												  "messages" => [
												    [
												      "type" => "text",
												      "text" => $message
												    ]
												  ]
												];			
							}
					

					break;

				}
		
	}

	return  $post_data;
			
}

function getpostback($postback,$user_id,$reply_token) {

		$data = explode("&",$postback);
		$action = explode("=",$data[0]);
		$device = explode("=",$data[1]);


			switch ($action[1]) {

				case 'onoff'://開關電器

				//看目前設備狀態
					$sql = "SELECT * FROM `electric_device` WHERE `id` = '$device[1]'";
					$result = mysql_query($sql);
					$onoff = mysql_fetch_array($result);
					if($onoff['status']=="0"){$onoff['status']="1";}else{$onoff['status']="0";}


					//POST DATA
					$device_action=$onoff['pin'].$onoff['status'];
					getsend($device_action);
					//
					$status = $onoff['status'];
					//更改設備狀態
					$sql_ = "UPDATE `electric_device` SET `status` = '$status' WHERE `id` = '$device[1]';";
					$result_ = mysql_query($sql_);

						if($onoff['status']=='0'){$onoff['status']="off";}else{$onoff['status']="on";}

					$text = $onoff['device']."　(".$onoff['status'].")";
			  		  // mqttsend('servermqtt',$text);



 				$post_data = [
					  "replyToken" => $reply_token,
					  "messages" => [
					    [
					      "type" => "text",
					      "text" => $text
					      					    ]
					  ]
					];


					break;
				case 'reserve' :

					//看目前設備狀態
						$sql = "SELECT * FROM `device_status` WHERE `id` = '$device[1]'";
						$result = mysql_query($sql);
						$onoff = mysql_fetch_array($result);
						if($onoff[2]=="off")
								{
						//更改設備狀態
							$onoff[2]="reserve";
							
							$sql_ = "UPDATE `device_status` SET `status` = '$onoff[2]' WHERE `device_status`.`id` = '$device[1]';";
							$result_ = mysql_query($sql_);
							$text = $onoff['1']."　(".$onoff[2].")";
						    mqttsend('servermqtt',$text);
						    $post_data = [
								  "replyToken" => $reply_token,
								  "messages" => [
								    [
								      "type" => "text",
								      "text" => "已預約".$onoff['1']
								    ]
								  ]
								];

						    }
						else
							{
								
								$post_data = [
								  "replyToken" => $reply_token,
								  "messages" => [
								    [
								      "type" => "text",
								      "text" => "此洗衣機預約中"
								    ]
								  ]
								];
						    }
					


 				
					break;
				
				default:
					# code...
					break;
			}
return $post_data;
}
		



?>