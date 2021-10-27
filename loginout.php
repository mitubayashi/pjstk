<?php

//require_once 'MDB2.php';
require_once("f_DB.php");
  session_start();
 
  $token = $_COOKIE['token'];
 
  //トークンを削除
 
   //DB接続
  //$db = MDB2::connect('mysql://user01:pass@localhost/Test?charset=utf8');
  //プレースホルダで SQL 作成
  //$sql = "DELETE  FROM AUTO_LOGIN WHERE TOKEN = ?";
  //パラメーターの型を指定
  //$stmt = $db->prepare($sql, array('text'));
  //パラメーターを渡して SQL 実行
  ///$stmt->execute(array($token));
 // $db->disconnect();
	$judge = false;
	$con = dbconect();
    //プレースホルダで SQL 作成
    $sql = "DELETE  FROM autologin WHERE TOKEN = '$token'";
	
	$result = $con->query($sql) or ($judge = true);																		// クエリ発行
	if($judge)
	{
		error_log($con->error,0);
		$judge =false;
	}
   //Cookie のトークンを削除
   //setCookie("token", '', -1, "/", null, TRUE, TRUE);
	setcookie("token", '');
	setcookie("token", '', -1);  /* 有効期限は一時間です */
	setcookie("token", '', -1, "/", null, 1,1);
   //リダイレクト
 // header("HTTP/1.1 301 Moved Permanently");
 // header("Location: login.php");
   header("location:".(empty($_SERVER['HTTPS'])? "http://" : "https://")
			.$_SERVER['HTTP_HOST'].dirname($_SERVER["REQUEST_URI"])."/login.php");

