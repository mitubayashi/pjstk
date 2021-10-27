<?php
  //require_once 'MDB2.php';
	require_once("f_DB.php");	
	session_start();
 
  //DNS 定義
  //$GLOBALS['DNS'] = 'mysql://user01:pass@localhost/Test?charset=utf8';
 
  //パラメーター取得
	/*setcookie("token", '');
	setcookie("token", '', -1);  /* 有効期限は一時間です */
	//setcookie("token", '', -1, "/", null, 1,1);
	/*if(isset($_POST['id']))
	{
		$id = $_POST['id'];
	}*/
	//社員ID取得
	if(isset($_SESSION['userid']))
	{
		$id = $_SESSION['userid'];
		//$password = $_POST['password'];
	}
	
//	if(isset($_SESSION['userPass']))
//	{
//		$password = $_SESSION['userPass'];
//	}
  
  /*if(isset($_POST['auto']))
  {
	  $auto = $_POST['auto'];
  }*/
	if(isset($_SESSION['pre_post']['auto']))
	{
		$auto = $_SESSION['pre_post']['auto'];
	}	
  if(isset($_COOKIE['token']))
  {
	  $cookie_token = $_COOKIE['token'];
  }
 
  //ログイン判定フラグ
  $normal_result = false;
  $auto_result = false;
  
 
  //簡易ログイン
  if (!isset($cookie_token)) {
   if (check_user($id, $password)) {
      $normal_result = true;
    }
  }
 
  //自動ログイン
  if (isset($cookie_token) ) {
    if (check_auto_login($cookie_token)) {
      $auto_result = true;
      $id = $_SESSION['userid'];
    }
  }
 
  //ログイン判定
  if ($normal_result || $auto_result) {
    //ログイン成功
  	
    //セッション ID の振り直し
   session_regenerate_id(true);
    
    //トークン生成処理
    if (($normal_result && $auto == true) || $auto_result) {
 
      //トークンの作成
      $token = get_token();
    
     //トークンの登録
     register_token($id, $token);
 
     //自動ログインのトークンを２週間の有効期限でCookieにセット
    // setCookie("token", $token, time()+60*60*24*14, "/", null, TRUE, TRUE);
	 //setcookie("token", $token);
	//setcookie("token", $token, time()+3600);  /* 有効期限は一時間です */
	//setcookie("token", $token, time()+3600, "/~rasmus/", null, 1);
	setcookie("token", $token);
	setcookie("token", $token, time()+60*60*24*14);  /* 有効期限は一時間です */
	setcookie("token", $token, time()+60*60*24*14, "/~rasmus/", null, 1);
    if ($auto_result) {
      //古いトークンの削除
      delete_old_token($cookie_token);
    }
  }
 
    //リダイレクト
    //header("HTTP/1.1 301 Moved Permanently");
    //header("Location: welcome.php");
  header("location:".(empty($_SERVER['HTTPS'])? "http://" : "https://")
			.$_SERVER['HTTP_HOST'].dirname($_SERVER["REQUEST_URI"])."/main.php");
  } else {
    //ログイン失敗
  	
    //リダイレクト
    //header("HTTP/1.1 301 Moved Permanently");
   // header("Location: login.php");
	  header("location:".(empty($_SERVER['HTTPS'])? "http://" : "https://")
			.$_SERVER['HTTP_HOST'].dirname($_SERVER["REQUEST_URI"])."/login.php");
  }
 
//---------------------------------------------------------------------------//
// ログイン処理
//---------------------------------------------------------------------------//
function check_user($id, $password) {
    
	$judge = false;
	//DB接続
	//$db = MDB2::connect($GLOBALS['DNS'] );
	$con = dbconect();
	//プレースホルダで SQL 作成
	$sql = "SELECT COUNT(*) AS CNT FROM shainmaster WHERE SHAID = $id AND SHAPASS = '$password';";
  
  //パラメーターの型を指定
  //$stmt = $db->prepare($sql, array('text', 'text'));
 
  //パラメーターを渡して SQL 実行
  //$rs = $stmt->execute(array($id, $password));
  
  //while ($row = $rs->fetchRow(MDB2_FETCHMODE_ASSOC)) {
  // $count = $row['cnt'];
  //}
 
  //$db->disconnect();
	$result = $con->query($sql) or ($judge = true);																		// クエリ発行
	if($judge)
	{
		error_log($con->error,0);
		$judge =false;
	}
	$rownums = $result->num_rows;	
	//------------------------//
	//    ログイン判断処理    //
	//------------------------//
	
	if ($rownums == 1) {
	 //ログイン成功
	 return true;
	} else {
	 //ログイン失敗
	return false;
	}
 }
 
 
//---------------------------------------------------------------------------//
//自動ログイン処理
 //--------------------------------------------------------------------------//
 function check_auto_login($token) {
 
  //DB接続
  //$db = MDB2::connect($GLOBALS['DNS'] );
  //プレースホルダで SQL 作成
  //$sql = "SELECT COUNT(*) AS CNT FROM AUTO_LOGIN WHERE TOKEN = ? AND REGISTRATED_TIME >= ?;";
  //パラメーターの型を指定
  //$stmt = $db->prepare($sql, array('text', 'timestamp'));
  //2週間前の日付を取得
  //$date = new DateTime("- 14 days");
  //パラメーターを渡して SQL 実行
	//$rs = $stmt->execute(array($token, $date->format('Y-m-d H:i:s')));
  //while ($row = $rs->fetchRow(MDB2_FETCHMODE_ASSOC)) {
   //$count = $row['cnt'];
  //}
  //$db->disconnect();
	$judge = false;
	//2週間前の日付取得
	$date = date("Y-m-d",strtotime("-2 week"));
	$con = dbconect();
	$sql = "SELECT COUNT(*) AS CNT FROM autologin WHERE TOKEN = '$token' AND RAGISTRATEDTIME >= '$date';";
	$result = $con->query($sql) or ($judge = true);																		// クエリ発行
	if($judge)
	{
		error_log($con->error,0);
		$judge =false;
	}
	$rownums = $result->num_rows;	
	 
	
  if ($rownums ==1) {
    //自動ログイン成功
 
	  
	  $sql = "SELECT * FROM autologin WHERE TOKEN = '$token' AND RAGISTRATEDTIME >= $date;";
	  $result = $con->query($sql) or ($judge = true);																		// クエリ発行
		if($judge)
		{
			error_log($con->error,0);
			$judge =false;
		}
	while($result_row = $result->fetch_array(MYSQLI_ASSOC))
	{
		 //$_SESSION['userid']  = $result_row['SHAID'];
		$shaid  = $result_row['SHAID'];
	}
	
	$Loginsql = "select * from shainmaster where SHAID = '".$shaid."';";
	$result = $con->query($Loginsql) or ($judge = true);																		// クエリ発行
	if($judge)
	{
		error_log($con->error,0);
		$judge =false;
	}
	while($result_row = $result->fetch_array(MYSQLI_ASSOC))
	{
		$_SESSION['SHAID']     = $result_row['SHAID'];
		$_SESSION['SHAUSERID']   = $result_row['SHAUSERID'];
		$_SESSION['HYOJIMEI']   = $result_row['SHAHYOJIMEI'];
		$_SESSION['userid']    = $result_row['SHAID'];		//各所でこれで使っているので入れておく
	}
 
    return true;
 
  } else {
   //自動ログイン失敗
 
   //Cookie のトークンを削除
   setCookie("token", '', -1, "/", null, TRUE, TRUE);
 
    //古くなったトークンを削除
   delete_old_token($token);
 
    return false;
  }
}
 
//---------------------------------------------------------------------------//
//トークンの登録
//---------------------------------------------------------------------------//
 function register_token($id, $token) {
 
	//DB接続
	//$db = MDB2::connect($GLOBALS['DNS'] );
	$judge = false;
	 $date = date("Y/m/d");	//現在日付
	$con = dbconect();
    //プレースホルダで SQL 作成
    $sql = "INSERT INTO autologin ( SHAID, TOKEN, RAGISTRATEDTIME) VALUES ($id,'$token','$date');";
	$result = $con->query($sql) or ($judge = true);
	if($judge)
	{
		error_log($con->error,0);
		$judge =false;
	}
    //パラメーターの型を指定
	//$stmt = $db->prepare($sql, array('text','text','timestamp'));
    //パラメーターを渡して SQL 実行
    //$stmt->execute(array($id, $token,date('Y-m-d H:i:s')));
 
    //$db->disconnect();
 }
 
//---------------------------------------------------------------------------//
//トークンの削除
//---------------------------------------------------------------------------//
function delete_old_token($token) {
    //DB接続
    //$db = MDB2::connect($GLOBALS['DNS'] );
	$judge = false;
	$con = dbconect();
    //プレースホルダで SQL 作成
    $sql = "DELETE  FROM autologin WHERE TOKEN = '$token';";
	
	$result = $con->query($sql) or ($judge = true);																		// クエリ発行
	if($judge)
	{
		error_log($con->error,0);
		$judge =false;
	}
    //パラメーターの型を指定
    //$stmt = $db->prepare($sql, array('text'));
    //パラメーターを渡して SQL 実行
    //$stmt->execute(array($token));
 
    //$db->disconnect();
}
 
//---------------------------------------------------------------------------//
// トークンを作成
//---------------------------------------------------------------------------//
function get_token() {
  $TOKEN_LENGTH = 16;//16*2=32桁
  $bytes = openssl_random_pseudo_bytes($TOKEN_LENGTH);
  return bin2hex($bytes);
  //$auto_login_key = hash('sha256', random_bytes(32));
  //return bin2hex($TOKEN_LENGTH);
}
?>

