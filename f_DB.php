<?php


/////////////////////////////////////////////////////////////////////////////////////
//                                                                                 //
//                                                                                 //
//                             ver 1.1.0 2014/07/03                                //
//                                                                                 //
//                                                                                 //
/////////////////////////////////////////////////////////////////////////////////////




/***************************************************************************
function dbconect()


引数			なし

戻り値	$con	mysql接続済みobjectT
***************************************************************************/

function dbconect(){


//-----------------------------------------------------------//
//                                                           //
//                     DBアクセス処理						 //
//                                                           //
//-----------------------------------------------------------//

	global $con;
	if( $con != null)
	{
		return ($con);
	}
	
	//-----------------------------//
	//   iniファイル読み取り準備   //
	//-----------------------------//
	$db_ini_array = parse_ini_file("./ini/DB.ini",true);																// DB基本情報格納.iniファイル
	
	//-------------------------------//
	//   iniファイル内情報取得処理   //
	//-------------------------------//
	$host = $db_ini_array["database"]["host"];																			// DBサーバーホスト
	$user = $db_ini_array["database"]["user"];																			// DBサーバーユーザー
	$password = $db_ini_array["database"]["userpass"];																	// DBサーバーパスワード
	$database = $db_ini_array["database"]["database"];																	// DB名
	
	//------------------------//
	//     DBアクセス処理      //
	//------------------------//
	
	
	$con = new mysqli($host,$user,$password, $database, "3306") or die('1'.$con->error);					// DB接続
	$con->set_charset("utf8") or die('2'.$con->error);												// cp932を使用する
	
	return ($con);
}

/**
 * 関数名: dbclose
 *   db接続を閉じる(global変数)
 * 
 * @retrun なし
 */
function dbclose()
{
	global $con;
	if( $con != null)
	{
	// DB接続を閉じる
		$con->close();
		$con = null;
	}
}


/************************************************************************************************************
function login($userName,$usserPass)


引数1	$userName				ユーザー名
引数2	$userPass				ユーザーパスワード

戻り値	$result					ログイン結果
************************************************************************************************************/
	
function login($userName,$userPass){
	
	//------------------------//
	//        初期設定        //
	//------------------------//
	
	//require_once("f_DB.php");																							// DB関数呼び出し準備
	$db_ini_array = parse_ini_file("./ini/DB.ini",true);																// DB基本情報格納.iniファイル
	
	//-------------------------------//
	//   iniファイル内情報取得処理   //
	//-------------------------------//
	$host = $db_ini_array["database"]["host"];																			// DBサーバーホスト
	$user = $db_ini_array["database"]["user"];																			// DBサーバーユーザー
	$password = $db_ini_array["database"]["userpass"];																	// DBサーバーパスワード
	$database = $db_ini_array["database"]["database"];																	// DB名
	
	//------------------------//
	//          定数          //
	//------------------------//
	//$Loginsql = "select * from loginuserinfo where USERMEI = '".$userName."' AND USERPASS = '".$userPass."' ;";		// ログインSQL文
	$Loginsql = "select * from shainmaster where SHAUSERID = '".$userName."' AND SHAPASS = '".$userPass."' ;";		// ログインSQL文
	//------------------------//
	//          変数          //
	//------------------------//
	$log_result = false;																								// ログイン判断
	$rownums = 0;																										// 検索結果件数
	
	//------------------------//
	//    ログイン検索処理    //
	//------------------------//
	//$con = dbconect();																									// db接続関数実行
	try	{
			$con = new PDO("mysql:host=$host;dbname=$database;charset=utf8",$user,$password,
			array(PDO::ATTR_EMULATE_PREPARES => false));
		}
		catch(PDOException $e)
		{
			exit('データベース接続失敗。'.$e->getMessage());
		}
	$result = $con->query($Loginsql);																					// クエリ発行
	$rownums = $result->rowCount();

	//------------------------//
	//    ログイン判断処理    //
	//------------------------//
	if ($rownums == 1)
	{
		$log_result = true;																								// ログイン結果true
		//FETCHして各値をセッションに入れる
		$result_row = $result->fetch(PDO::FETCH_ASSOC);
		$_SESSION['SHAID']     = $result_row['SHAID'];
		$_SESSION['SHAUSERID']   = $result_row['SHAUSERID'];
		$_SESSION['HYOJIMEI']   = $result_row['SHAHYOJIMEI'];
		$_SESSION['userid']    = $result_row['SHAID'];		//各所でこれで使っているので入れておく
	}
	return ($log_result);
	
}


/************************************************************************************************************
function limit_date()


引数	なし					ユーザー名

戻り値	$result					有効期限結果
************************************************************************************************************/
	
function limit_date(){
	
	//------------------------//
	//        初期設定        //
	//------------------------//
	require_once("f_DB.php");																						// DB関数呼び出し準備
	
	//------------------------//
	//          定数          //
	//------------------------//
	$date = date_create("NOW");
	$date = date_format($date, "Y-m-d");
	$Loginsql = "select * from systeminfo;";																		// 有効期限SQL文
	
	//------------------------//
	//          変数          //
	//------------------------//
	$limit_result = 0;																								// 有効期限判断
	$rownums = 0;																									// 検索結果件数
	$startdate = "";
	$enddate = "";
	$befor_month = "";
	$message = "";
	$result_limit = array();
	
	//------------------------//
	//    ログイン検索処理    //
	//------------------------//
	$con = dbconect();																								// db接続関数実行
	$result = $con->query($Loginsql) or die($con-> error);														// クエリ発行
	$rownums = $result->num_rows;																					// 検索結果件数取得
	while($result_row = $result->fetch_array(MYSQLI_ASSOC))
	{
		$startdate = $result_row['STARTDATE'];
	}
	
	//------------------------//
	//    ログイン判断処理    //
	//------------------------//
	$enddate = date_create($startdate);
	$enddate = date_add($enddate, date_interval_create_from_date_string('1 year'));
	$enddate = date_sub($enddate, date_interval_create_from_date_string('1 days'));
	$enddate = date_format($enddate, 'Y-m-d');
	$befor_month = date_create($enddate);
	$befor_month = date_format($befor_month, 'Y-m-01');
	$befor_month = date_create($befor_month);
	$befor_month = date_sub($befor_month, date_interval_create_from_date_string('1 month'));
	$befor_month = date_format($befor_month, 'Y-m-d');
	if($enddate >= $date)
	{
		$limit_result = 1;
		if($befor_month <= $date)
		{
			$enddate2 = date_create($enddate);
			$date2 = date_create($date);
			$limit_result = 2;
			$interval = date_diff($date2, $enddate2);
			$message = $interval->format('%a');
		}
	}
	else
	{
		$limit_result = 0;
	}
	$result_limit[0] = $limit_result;
	$result_limit[1] = $message;
	return ($result_limit);
	
}
/************************************************************************************************************
function UserCheck($userID,$userPass)


引数1	$userID						ユーザー名
引数2	$userPass					ユーザーパス

戻り値	$columnName					既に登録されているカラム名
************************************************************************************************************/
	
function UserCheck($userID,$userPass){
	
	
	//------------------------//
	//        初期設定        //
	//------------------------//
	require_once("f_DB.php");																							// DB関数呼び出し準備
	
	//------------------------//
	//          定数          //
	//------------------------//
	$checksql1 = "select * from loginuserinfo where LUSERNAME ='".$userID."' OR LUSERPASS ='".$userPass."' ;";			// 既登録確認SQL文1
	$checksql2 = "select * from loginuserinfo where LUSERNAME ='".$userID."' ;";										// 既登録確認SQL文2
	$checksql3 = "select * from loginuserinfo where LUSERPASS ='".$userPass."' ;";										// 既登録確認SQL文3
	
	//------------------------//
	//          変数          //
	//------------------------//
	$columnName = ""		;																							// 既に登録されているカラム名宣言
	$rownums = 0;																										// 検索結果件数
	
	//------------------------//
	//      チェック処理      //
	//------------------------//
	$con = dbconect();																									// db接続関数実行
	$result = $con->query($checksql1);																					// クエリ発行
	$rownums = $result->num_rows;																						// 検索結果件数取得
	if($rownums == 0)
	{
		return($columnName);
	}
	else
	{
		$result = $con->query($checksql2);																				// クエリ発行
		$rownums = $result->num_rows;																					// 検索結果件数取得
		if($rownums != 0)
		{
			$columnName .= 'LUSERNAME';
		}
		return($columnName);
	}
	
	
	
}


/************************************************************************************************************
function insertUser()


引数	なし

戻り値	なし
************************************************************************************************************/
	
function insertUser(){
	
	
	//------------------------//
	//        初期設定        //
	//------------------------//
	require_once("f_DB.php");																							// DB関数呼び出し準備
	
	//------------------------//
	//          定数          //
	//------------------------//
	$userID = $_SESSION['insertUser']['uid'];
	$userPass = $_SESSION['insertUser']['pass'];
	$insertsql = "insert into loginuserinfo (LUSERNAME,LUSERPASS) value ('".$userID."','".$userPass."') ;";				// 既登録確認SQL文

	//------------------------//
	//        登録処理        //
	//------------------------//
	$con = dbconect();																									// db接続関数実行
	$con->query($insertsql);																							// クエリ発行
}


/************************************************************************************************************
function selectUser()


引数	なし

戻り値	list			listhtml
************************************************************************************************************/
	
function selectUser(){
	
	
	//------------------------//
	//        初期設定        //
	//------------------------//
	require_once("f_DB.php");																							// DB関数呼び出し準備
	
	if(!isset($_SESSION['listUser']))
	{
		$_SESSION['listUser']['limit'] = ' limit 0,10';
		$_SESSION['listUser']['limitstart'] =0;
		$_SESSION['listUser']['where'] ='';
		$_SESSION['listUser']['orderby'] ='';
	}
	
	//------------------------//
	//          定数          //
	//------------------------//
	$limit = $_SESSION['listUser']['limit'];																			// limit
	$limitstart = $_SESSION['listUser']['limitstart'];																	// limit開始位置
	$where = $_SESSION['listUser']['where'];																			// 条件
	$orderby = $_SESSION['listUser']['orderby'];																		// order by 条件
	$totalSelectsql = "SELECT * from loginuserinfo ".$where." ;";														// 管理者全件取得SQL
	$selectsql = "SELECT * from loginuserinfo ".$where.$orderby.$limit." ;";											// 管理者リスト分取得SQL文
	
	//------------------------//
	//          変数          //
	//------------------------//
	$totalcount = 0;
	$listcount = 0;
	$list_str = "";
	$counter = 1;
	$id ="";
	
	//------------------------//
	//        登録処理        //
	//------------------------//
	$con = dbconect();																									// db接続関数実行
	$result = $con->query($totalSelectsql);																				// クエリ発行
	$totalcount = $result->num_rows;																					// 検索結果件数取得
	$result = $con->query($selectsql);																					// クエリ発行
	$listcount = $result->num_rows;																						// 検索結果件数取得
	if ($totalcount == $limitstart )
	{
		$list_str .= $totalcount."件中 ".($limitstart)."件〜".($limitstart + $listcount)."件 表示中";					// 件数表示作成
	}
	else
	{
		$list_str .= $totalcount."件中 ".($limitstart + 1)."件〜".($limitstart + $listcount)."件 表示中";				// 件数表示作成
	}
	$list_str .= "<table class = 'list' ><thead><tr>";
	$list_str .= "<th>No.</th>";
	$list_str .= "<th>管理者ID</th>";
	$list_str .= "<th>編集</th>";
	$list_str .= "</tr></thead>";
	$list_str .= "<tbody>";
	while($result_row = $result->fetch_array(MYSQLI_ASSOC))
	{
		if(($counter%2) == 1)
		{
        // 2018/08 変更 ↓
			$id = "id = 'stripe_none'";
        // 2018/08 変更 ↑
		}
		else
		{
			$id = "id = 'stripe'";
		}
		$list_str .= "<tr><td ".$id." class = 'td1' >".($limitstart + $counter)."</td>";
		$list_str .= "<td ".$id."class = 'td2' >".$result_row['LUSERNAME']."</td>";
		$list_str .= "<td ".$id." class = 'td3'><input type='submit' name='"
					.$result_row['LUSERID']."_edit' value = '編集'></td></tr>";
		$counter++;
	}
	$list_str .= "</tbody>";
	$list_str .= "</table>";
	$list_str .= "<div class = 'left'>";
	$list_str .= "<input type='submit' name ='back' value ='戻る' class = 'button' style ='height : 30px;' ";
	if($limitstart == 0)
	{
		$list_str .= " disabled='disabled'";
	}
	$list_str .= "></div><div class = 'left'>";
	$list_str .= "<input type='submit' name ='next' value ='進む' class = 'button' style ='height : 30px;' ";
	if(($limitstart + $listcount) == $totalcount)
	{
		$list_str .= " disabled='disabled'";
	}
	$list_str .= "></div>";
	return($list_str);
}

/************************************************************************************************************
function selectID($id)


引数	$id						検索対象ID

戻り値	$result_array			検索結果
************************************************************************************************************/
	
function selectID($id){
	
	
	//------------------------//
	//        初期設定        //
	//------------------------//
	require_once("f_DB.php");																							// DB関数呼び出し準備
	
	//------------------------//
	//          定数          //
	//------------------------//
	$selectidsql = "SELECT * FROM loginuserinfo where LUSERID = ".$id." ;";
	
	//------------------------//
	//          変数          //
	//------------------------//
	$result_array =array();
	
	//------------------------//
	//        検索処理        //
	//------------------------//
	$con = dbconect();																									// db接続関数実行
	$result = $con->query($selectidsql);																				// クエリ発行
	if($result->num_rows == 1)
	{
		$result_array = $result->fetch_array(MYSQLI_ASSOC);
	}
	return($result_array);
}

/************************************************************************************************************
function updateUser()


引数	なし

戻り値	なし
************************************************************************************************************/
	
function updateUser(){
	
	
	//------------------------//
	//        初期設定        //
	//------------------------//
	require_once("f_DB.php");																							// DB関数呼び出し準備
	
	//------------------------//
	//          定数          //
	//------------------------//
	$userID = $_SESSION['editUser']['uid'];
	$userPass = $_SESSION['editUser']['newpass'];
	$id = $_SESSION['listUser']['id'];
	$updatesql = "UPDATE loginuserinfo SET LUSERNAME ='"
				.$userID."', LUSERPASS = '".$userPass."' where LUSERID = ".$id." ;";									// 更新SQL文

	//------------------------//
	//        更新処理        //
	//------------------------//
	$con = dbconect();																									// db接続関数実行
	$con->query($updatesql);																							// クエリ発行
}
/************************************************************************************************************
function deleteUser()


引数	なし

戻り値	なし
************************************************************************************************************/
	
function deleteUser(){
	
	
	//------------------------//
	//        初期設定        //
	//------------------------//
	require_once("f_DB.php");																							// DB関数呼び出し準備
	
	//------------------------//
	//          定数          //
	//------------------------//
	$id = $_SESSION['result_array']['LUSERID'];
	$deletesql = "DELETE FROM loginuserinfo where LUSERID = ".$id." ;";													// 更新SQL文

	//------------------------//
	//        更新処理        //
	//------------------------//
	$con = dbconect();																									// db接続関数実行
	$con->query($deletesql);																							// クエリ発行
}

/************************************************************************************************************
function existCheck($post,$tablenum,$type)

引数1		$post							登録フォーム入力値
引数2		$main_codeValue					編集ID
引数3		$tablenum						テーブル番号
引数4		$type							1:insert 2:edit 3:delete

戻り値		$errorinfo						既登録確認結果
************************************************************************************************************/
//function existCheck($post,$tablenum,$type){
function existCheck($post,$main_codeValue,$tablenum,$type){
	
	//------------------------//
	//        初期設定        //
	//------------------------//
	$form_ini = parse_ini_file('./ini/form.ini', true);
	require_once ("f_Form.php");
	require_once ("f_DB.php");																							// DB関数呼び出し準備
	require_once ("f_SQL.php");																							// SQL関数呼び出し準備
	
	//------------------------//
	//          定数          //
	//------------------------//
	$filename = $_SESSION['filename'];
	$uniquecolumn = $form_ini[$filename]['uniquecheck'];
	$uniquecolumn_array = explode(',',$uniquecolumn);
	$master_tablenum = $form_ini[$tablenum]['seen_table_num'];
	$master_tablenum_array = explode(',',$master_tablenum);
	//------------------------//
	//          変数          //
	//------------------------//
	$errorinfo = array();
	$errorinfo[0] = "";
	$sql = "";
	$judge = false;
	$codeValue = "";
	$code = "";
	$table_title = "";
	$counter = 1;
	$syorimei = "";
	
	//------------------------//
	//          処理          //
	//------------------------//
	switch($type)
	{
	case 1 :
		$syorimei = "登録";
		break;
	case 2 :
		$syorimei = "編集";
		break;
	case 3 :
		$syorimei = "削除";
		break;
	default :
		break;
	}
	$con = dbconect();																									// db接続関数実行
	if($type == 2)
	{
		$table_title = $form_ini[$tablenum]['table_title'];
		$code = $tablenum.'ID';
		//$post[$code] = $main_codeValue;//編集するID
		//$codeValue = $post[$code];
		$codeValue = $main_codeValue;//編集するID
		$sql = idSelectSQL($codeValue,$tablenum,$code);
		$result = $con->query($sql) or ($judge = true);																	// クエリ発行
		if($judge)
		{
			error_log($con->error,0);
			$judge = false;
		}
		if($result->num_rows == 0 )
		{
			$errorinfo[$counter] = "<div class = 'center'><a class = 'error'>".
									$table_title."情報が削除されているため".
									$syorimei."できません。</a></div><br>";
			$counter++;
		}
		else
		{
			$errorinfo[$counter] = "";
			$counter++;
		}
	}
	for( $j = 0 ; $j < count($uniquecolumn_array) ; $j++)
	{
		if($uniquecolumn_array[$j] == "")
		{
			break;
		}
		$sql = uniqeSelectSQL($post,$tablenum,$uniquecolumn_array[$j]);
		if($sql != '')
		{
			$result = $con->query($sql) or ($judge = true);																// クエリ発行
			if($judge)
			{
				error_log($con->error,0);
				$judge = false;
			}
			if($result->num_rows != 0 )
			{
				$errorinfo[0] .= $uniquecolumn_array[$j].",";
			}
		}
	}
	for($k = 0 ; $k < count($master_tablenum_array) ; $k++ )
	{
		if($master_tablenum == '')
		{
			break;
		}
		$table_title = $form_ini[$master_tablenum_array[$k]]['table_title'];
		$code = $master_tablenum_array[$k].'ID';
		$codeValue = $post[$code];
		$sql = idSelectSQL($codeValue,$master_tablenum_array[$k],$code);
		$result = $con->query($sql) or ($judge = true);																	// クエリ発行
		if($judge)
		{
			error_log($con->error,0);
			$judge = false;
		}
		if($result->num_rows == 0 )
		{
			$errorinfo[$counter] = "<div class = 'center'><a class = 'error'>".
									$table_title."情報が削除されているため".
									$syorimei."できません。</a></div><br>";
			$counter++;
		}
	}
	return ($errorinfo);
}

/************************************************************************************************************
function insert($filename,$post)

引数		$post						入力内容

戻り値		なし
************************************************************************************************************/
function insert($filename, &$post,&$con){
	
	//------------------------//
	//        初期設定        //
	//------------------------//
	$form_ini = parse_ini_file('./ini/form.ini', true);
	require_once ("f_Form.php");
	require_once ("f_DB.php");																							// DB関数呼び出し準備
	require_once ("f_SQL.php");																							// DB関数呼び出し準備
	
	//------------------------//
	//          定数          //
	//------------------------//
	$tablenum = $form_ini[$filename]['use_maintable_num'];
	$list_tablenum = $form_ini[$tablenum]['see_table_num'];
	$list_tablenum_array = explode(',',$list_tablenum);
	$main_table_type = $form_ini[$tablenum]['table_type'];
	//------------------------//
	//          変数          //
	//------------------------//
	$sql = "";
	$judge = false;
	$codeValue = "";
	$code = "";
	$counter = 1;
	$main_CODE =0;
	$over = array();
	
	//------------------------//
	//          処理          //
	//------------------------//
	//$con = dbconect();																									// db接続関数実行
	$sql = InsertSQL($post,$tablenum,"",$filename);
	//$sql = "INSERT INTO loginuserinfos (USERMEI,USERPASS,KENGEN,HYOJIMEI,COLOR,STAMPNAME,PSUKEY,UPDATETIME,UPDATEUSER)VALUES('イシノ','isi',1,'イシノ','#7777DD','yu','1',CURRENT_TIMESTAMP,1);";
	$result = $con->query($sql) or ($judge = true);																		// クエリ発行
	
	if($judge)
	{
		error_log($con->error,0);
		$judge =false;
	}
	////////////////////操作履歴///////////////////////
	addSousarireki($filename, STEP_INSERT, $sql, $con);
	////////////////////操作履歴///////////////////////
	//----------------------//
	//	ヘッダー明細登録	//
	//----------------------//
	$filename_m = $filename . '_M';
	//_Mの設定値がない場合
	if(!isset($form_ini[$filename_m]))
	{
		return $result;
	}
	//紐付けるためのユニークキーを取得
	$ucode_id = strtoupper($tablenum).'UCODE';
	$ucode = $post['form_'.$tablenum.$ucode_id.'_0'];
	$unique_sql = 'SELECT * FROM '.$form_ini[$tablenum]['table_name'].' WHERE '.$ucode_id.'='.$ucode;
	//SQL実行
	$unique_result = $con->query($unique_sql);				// クエリ発行
	//終端までループ
	while($result_row = $unique_result->fetch_array(MYSQLI_ASSOC))
	{
		$post[strtoupper($tablenum).'ID'] = $result_row[strtoupper($tablenum).'ID'];
		break;
	}
	$unique_result->close();
	
	$meisaisql = MeisaiInsertSQL($post,"",$filename);
	if($meisaisql != 0)
	{
		for($i = 0; $i < count($meisaisql); $i++)
		{
			$result = $con->query($meisaisql[$i]) or ($judge = true);	// クエリ発行
			if($judge)
			{
				error_log($con->error,0);
				$judge =false;
			}
		}
	}
	
//	if($main_table_type == 0)
//	{
//		$main_CODE = $con->insert_id;
//		$post[$tablenum.'ID'] = $main_CODE;
//		for( $i = 0 ; $i < count($list_tablenum_array) ; $i++)
//		{
//			if($list_tablenum_array[$i] == "" )
//			{
//				break;
//			}
//			$over =getover($post,$list_tablenum_array[$i]);
//			for( $j = 0; $j < count($over) ; $j++ )
//			{
//				$sql = InsertSQL($post,$list_tablenum_array[$i],$over[$j],$filename);
//				$result = $con->query($sql) or ($judge = true);																// クエリ発行
//				if($judge)
//				{
//					error_log($con->error,0);
//				}
//			}
//		}
//	}
//	
	
	return $result;
}

/************************************************************************************************************
function make_post($main_codeValue)

引数		$main_codeValue						メインテーブルのプライマリー番号

戻り値		なし
************************************************************************************************************/
//function make_post($container,$main_codeValue){
function make_post($pbInputContent,$main_codeValue){	
	//------------------------//
	//        初期設定        //
	//------------------------//
	$form_ini = parse_ini_file('./ini/form.ini', true);
	$param_ini = parse_ini_file('./ini/param.ini', true);
	require_once ("f_Form.php");
	require_once ("f_DB.php");																							// DB関数呼び出し準備
	require_once ("f_SQL.php");																							// DB関数呼び出し準備
	
	//------------------------//
	//          定数          //
	//------------------------//
	$filename = $_SESSION['filename'];
	$tablenum = $form_ini[$filename]['use_maintable_num'];
	$table_type = $form_ini[$tablenum]['table_type'];
	$list_tablenum = $form_ini[$tablenum]['see_table_num'];
	$master_tablenum = $form_ini[$tablenum]['seen_table_num'];
	$list_tablenum_array = explode(',',$list_tablenum);
	$master_tablenum_array = explode(',',$master_tablenum);
	$uniqecolumns = $form_ini[$filename]['uniquecheck'];
	$uniqecolumns_array = explode(',',$uniqecolumns);
	//------------------------//
	//          変数          //
	//------------------------//
	$sql = "";
	$judge = false;
	$codeValue = "";
	$code = "";
	$counter = 1;
	$over = array();
	$form_name = '';
	$form_type = '';
	$form_param = array();
	$names_array = array();
	$valus_array = array();
	$counter = 0;
	
	//------------------------//
	//          処理          //
	//------------------------//
	$con = dbconect();																									// db接続関数実行
	$code = $tablenum.'ID';
	//$_SESSION['edit'][$code] = $main_codeValue;//編集するID
	//$container->pbInputContent[$code] = $main_codeValue;//編集するID
	$pbInputContent[$code] = $main_codeValue;//編集するID
	$sql = idSelectSQL($main_codeValue,$tablenum,$code);
	$result = $con->query($sql) or ($judge = true);																		// クエリ発行
	if($judge)
	{
		error_log($con->error,0);
	}
	while($result_row = $result->fetch_array(MYSQLI_ASSOC))
	{
		foreach($result_row as $key => $value)
		{
			// 2018/06/29 追加対応 ↓
			if( $key == "DELETED" )
			{
					// DELETEDは表示しない
					continue;
			}
			// 2018/06/29 追加対応 ↑
			//$form_name = $param_ini[$key]['column_num'];
			$form_name = $tablenum.$key;
			foreach($uniqecolumns_array as $uniqevalue)
			{
				if(strstr($uniqevalue, $form_name) == true)
				{
					//$_SESSION['edit']['uniqe'][$form_name] = $value;
					//$container->pbInputContent['uniqe'][$form_name] = $value;
					$pbInputContent['uniqe'][$form_name] = $value;
				}
			}
			$form_type = $form_ini[$form_name]['form1_type'];
			$form_param = formvalue_return($form_name,$value,$form_type);
			$names_array = explode(',',$form_param[0]);
			$valus_array = explode('#$',$form_param[1]);
			for($i = 0 ; $i < count($valus_array) ; $i++ )
			{
				if($valus_array[$i] != "")
				{	
					//$_SESSION['edit'][$names_array[$i]] = $valus_array[$i];
					//$container->pbInputContent[$names_array[$i]] = $valus_array[$i];
					$pbInputContent[$names_array[$i]] = $valus_array[$i];
				}	
			}
		}
	}
//	if($master_tablenum != '' && $table_type != 1)
	if($master_tablenum != '')
	{
		for($i = 0 ; $i < count($master_tablenum_array) ; $i++ )
		{
			$code = $master_tablenum_array[$i].'ID';
			//$sql = idSelectSQL($_SESSION['edit'][$code],$master_tablenum_array[$i],$code);
			//$sql = idSelectSQL($container->pbInputContent[$code],$master_tablenum_array[$i],$code);
			$sql = idSelectSQL($pbInputContent[$code],$master_tablenum_array[$i],$code);
			$result = $con->query($sql) or ($judge = true);																// クエリ発行
			if($judge)
			{
				error_log($con->error,0);
			}
			while($result_row = $result->fetch_array(MYSQLI_ASSOC))
			{
				foreach($result_row as $key => $value)
				{
					// 2018/06/29 追加対応 ↓
					if( $key == "DELETED" )
					{
							// DELETEDは表示しない
							continue;
					}
					//$form_name = $param_ini[$key]['column_num'];
					$form_name = $tablenum.$key;
					foreach($uniqecolumns_array as $uniqevalue)
					{
						if(strpos($uniqevalue, $form_name) !== false)
						{
							//$_SESSION['edit']['uniqe'][$form_name] = $value;
							//$container->pbInputContent['uniqe'][$form_name] = $value;
							$pbInputContent['uniqe'][$form_name] = $value;
						}
					}
					$form_type = $form_ini[$form_name]['form1_type'];
					$form_param = formvalue_return($form_name,$value,$form_type);
					$names_array = explode(',',$form_param[0]);
					$valus_array = explode('#$',$form_param[1]);
					for($j = 0 ; $j < count($valus_array) ; $j++ )
					{
						if($valus_array[$j] != "")
						{	
							//$_SESSION['edit'][$names_array[$j]] = $valus_array[$j];
							//$container->pbInputContent[$names_array[$j]] = $valus_array[$j];
							$pbInputContent[$names_array[$j]] = $valus_array[$j];
						}
					}
				}
			}
		}
	}
	
	if($list_tablenum != '' && $table_type != 1)
//	if($list_tablenum != '')
	{
		for($i = 0 ; $i < count($list_tablenum_array) ; $i++ )
		{
			$code = $tablenum.'ID';
			$sql = idSelectSQL($main_codeValue,$list_tablenum_array[$i],$code);
			$result = $con->query($sql) or ($judge = true);																// クエリ発行
			if($judge)
			{
				error_log($con->error,0);
			}
			while($result_row = $result->fetch_array(MYSQLI_ASSOC))
			{
				foreach($result_row as $key => $value)
				{
					//$form_name = $param_ini[$key]['column_num'];
					$form_name = $tablenum.$key;
					foreach($uniqecolumns_array as $uniqevalue)
					{
						if(strpos($uniqevalue, $form_name) !== false)
						{
							//$_SESSION['edit']['uniqe'][$form_name] = $value;
							//$container->pbInputContent['uniqe'][$form_name] = $value;
							$pbInputContent['uniqe'][$form_name] = $value;
						}
					}
					$form_type = $form_ini[$form_name]['form1_type'];
					$form_param = formvalue_return($form_name,$value,$form_type);
					$names_array = explode(',',$form_param[0]);
					$valus_array = explode('#$',$form_param[1]);
					for($j = 0 ; $j < count($valus_array) ; $j++ )
					{
						$_SESSION['data'][$list_tablenum_array[$i]][$counter][$names_array[$j]] = $valus_array[$j];
					}
				}
				$counter++;
			}
			$counter = 0;
		}
	}
	
	return $pbInputContent;
}

/************************************************************************************************************
function make_headerpost($main_codeValue)

引数1		$filename							ページ名
引数2		$main_codeValue						編集ID
引数	3		$use_code							mmh(見積明細の場合)
 * 
 * 明細編集時、値を設定
戻り値		なし
************************************************************************************************************/
function make_headerpost($filename,$main_codeValue,$use_code)
{
	//------------------------//
	//        初期設定        //
	//------------------------//
	$form_ini = parse_ini_file('./ini/form.ini', true);
	require_once ("f_Form.php");
	require_once ("f_DB.php");																							// DB関数呼び出し準備
	require_once ("f_SQL.php");																							// DB関数呼び出し準備
	
	//------------------------//
	//          定数          //
	//------------------------//
	$tablenum = $form_ini[$filename]['use_maintable_num'];//mmm
	$columns = $form_ini[$filename]['page_columns'];//mmmSEQ,mmmHINMEI,mmmTANKA,mmmSURYO,mmmTANNI,mmmKINGAKU
	$columns_array = explode(',',$columns);
	
	//------------------------//
	//          変数          //
	//------------------------//
	$count = 0;
	$judge = false;
	
	//------------------------//
	//          処理          //
	//------------------------//
	$con = dbconect();																									// db接続関数実行
	$code = strtoupper( $use_code ).'ID';//mmhCODE
	
	$sql = idSelectSQL($main_codeValue,$tablenum,$code);
	$result = $con->query($sql) or ($judge = true);																		// クエリ発行
	if($judge)
	{
		error_log($con->error,0);
	}
	
	$post = array();
	while($result_row = $result->fetch_array(MYSQLI_ASSOC))
	{
		foreach($result_row as $key => $value)
		{
			$form_name = $tablenum.$key;
			$form_type = $form_ini[$form_name]['form1_type'];
			$form_param = formvalue_return($form_name,$value,$form_type);
			$names_array = explode(',',$form_param[0]);
			$valus_array = explode('#$',$form_param[1]);
			for($j = 0 ; $j < count($valus_array) ; $j++ )
			{
				//if($valus_array[$j] != "")
				//{	
					$post[$names_array[$j].'_'.$count] = $valus_array[$j];
				//}
			}
		}
		
		$count++;
	}
	
	return($post);
}
/************************************************************************************************************
function update($filename,$post,$con)

引数		$post								入力内容

戻り値		なし
************************************************************************************************************/
function update($filename, $post,&$con){
	//------------------------//
	//        初期設定        //
	//------------------------//
	$form_ini = parse_ini_file('./ini/form.ini', true);
	require_once ("f_Form.php");
	require_once ("f_DB.php");																							// DB関数呼び出し準備
	require_once ("f_SQL.php");																							// DB関数呼び出し準備
	
	//------------------------//
	//          定数          //
	//------------------------//
	$tablenum = $form_ini[$filename]['use_maintable_num'];
	$list_tablenum = $form_ini[$tablenum]['see_table_num'];
	$list_tablenum_array = explode(',',$list_tablenum);
	$main_table_type = $form_ini[$tablenum]['table_type'];
	//------------------------//
	//          変数          //
	//------------------------//
	$sql = "";
	$judge = false;
	$codeValue = "";
	$code = "";
	$counter = 1;
	$main_CODE =0;
	$over = array();
	$delete =array();
	$delete_param = array();
	$delete_path = "";
	$delete_CODE = "";
	
	//------------------------//
	//          処理          //
	//------------------------//
	
	//出退勤前情報取得
	$content = loadDBRecord("stk",$post["edit_list_id"]);
	
	
	//$con = dbconect();																									// db接続関数実行
	$sql = UpdateSQL($post,$tablenum,"",$filename);
	$result = $con->query($sql) or ($judge = true);																		// クエリ発行
	if($judge)
	{
		error_log($con->error,0);
	}
	////////////////////操作履歴///////////////////////
	//addSousarireki($filename, STEP_EDIT, $sql, $con);
	Sousarireki($filename, STEP_EDIT, $sql, $con,$post,$content);
	////////////////////操作履歴///////////////////////
	//----------------------//
	//	ヘッダー明細登録		//
	//----------------------//
	$headersql = MeisaiUpdateSQL($post,$tablenum,"",$filename);
	if($headersql != 0)
	{	
		for($i = 0; $i < count($headersql); $i++)
		{
			$result = $con->query($headersql[$i]) or ($judge = true);	// クエリ発行
			if($judge)
			{
				error_log($con->error,0);
				$judge =false;
			}
		}
	}
	if($main_table_type == 0)
	{
		for( $i = 0 ; $i < count($list_tablenum_array) ; $i++)
		{
			if(isset($post['delete'.$list_tablenum_array[$i]]))
			{
				$delete = $post['delete'.$list_tablenum_array[$i]];
				for($j = 0 ; $j < count($delete) ; $j++)
				{
					$delete_param = explode(':',$delete[$j]);
					$delete_path = $delete_param[0];
					$delete_CODE = $delete_param[1];
					$tablenum = $list_tablenum_array[$i];
					$code = $tablenum.'ID';
					if(file_exists($delete_path))
					{
						unlink($delete_path);
					}
					$sql = DeleteSQL($delete_CODE,$tablenum,$code,$filename);
					$result = $con->query($sql) or ($judge = true);																// クエリ発行
					if($judge)
					{
						error_log($con->error,0);
					}
				}
			}
		}
		for( $i = 0 ; $i < count($list_tablenum_array) ; $i++)
		{
			if($list_tablenum_array[$i] == "" )
			{
				break;
			}
			$over =getover($post,$list_tablenum_array[$i]);
			for( $j = 0; $j < count($over) ; $j++ )
			{
				$sql = InsertSQL($post,$list_tablenum_array[$i],$over[$j],$filename);
				$result = $con->query($sql) or ($judge = true);																// クエリ発行
				if($judge)
				{
					error_log($con->error,0);
				}
			}
		}
	}
	
	//戻り値 true false
	return $result;
}




/************************************************************************************************************
function make_csv($post)

引数		$post							入力内容

戻り値		$path							csvファイルパス
************************************************************************************************************/
function make_csv($post){
	
	//------------------------//
	//        初期設定        //
	//------------------------//
	$form_ini = parse_ini_file('./ini/form.ini', true);
	$param_ini = parse_ini_file('./ini/param.ini', true);
	require_once ("f_Form.php");
	require_once ("f_DB.php");																							// DB関数呼び出し準備
	require_once ("f_SQL.php");																							// DB関数呼び出し準備
	require_once ("f_File.php");																						// DB関数呼び出し準備
	
	//------------------------//
	//          定数          //
	//------------------------//
	$filename = $_SESSION['filename'];
	$tablenum = $form_ini[$filename]['use_maintable_num'];
	//------------------------//
	//          変数          //
	//------------------------//
	$sql = array();
	$isonce = true;
	$csv = "";
	$where_csv = "";
	$header_csv = "";
	$value_csv = "";
	$header = "";
	$where = "";
	$path = "";
	$judge = false;
	
	//------------------------//
	//          処理          //
	//------------------------//
	$con = dbconect();																									// db接続関数実行
	
	
	
	if($filename == 'HENKYAKUINFO_2')
	{
		$post['form_405_0'] = '0';																				// 入出荷中のみ表示のため
		$sql = hannyuusyutuSQL($post);
		$sql = SQLsetOrderby($post,$filename,$sql);
	}
	else if($filename == 'SYUKKAINFO_2')
	{
		$_SESSION['list']['form_405_0'] = '0';																				// 入出荷中のみ表示のため
		$sql = hannyuusyutuSQL($_SESSION['list']);
		$sql = SQLsetOrderby($post,$filename,$sql);
	}
	else if($filename == 'GENBALIST_2' || $filename == 'SIZAILIST_2' || $filename == 'ZAIKOINFO_2')
	{
		$sql = itemListSQL($post);
		$sql = SQLsetOrderby($post,$filename,$sql);
	}
	else
	{
		$sql = joinSelectSQL($post,$tablenum, $filename, $form_ini);
		$sql = SQLsetOrderby($post,$filename,$sql);
	}
	
	
	
	
	$result = $con->query($sql[0]) or ($judge = true);																	// クエリ発行
	if($judge)
	{
		error_log($con->error,0);
	}
	while($result_row = $result->fetch_array(MYSQLI_ASSOC))
	{
		foreach($result_row as $key => $value)
		{
                        // 2018/06/29 追加対応 ↓
                        if($key == 'DELETED')
                        {
                                continue;
                        }
                        // 2018/06/29 追加対応 ↑
			if($isonce == true)
			{
				if($key != 'SYUKKASUM' && $key != 'HENKYAKUSUM' && $key != 'ZAIKO')
				{
					$header = $param_ini[$key]['link_name'];
					$header_csv .= $header.",";
					$where = key_value($key,$post);
				}
				else
				{
					if($key == 'SYUKKASUM')
					{
						$header = "出荷数";
					}
					if($key == 'HENKYAKUSUM')
					{
						$header = "返却数";
					}
					if($key == 'ZAIKO')
					{
						$header = "土場在庫数";
					}
					$header_csv .= $header.",";
					$where = "";
				}
				$where_csv .= $header." = ".$where.",";
			}
			$columnnum = 0;
			if(isset($param_ini[$key]['column_num']))
			{
				$columnnum = $param_ini[$key]['column_num'];
			}
			if($columnnum != 0 )
			{
				$type = $form_ini[$columnnum]['form1_type'];
				$format = $form_ini[$columnnum]['format'];
				$value = format_change($format,$value,$type);
			}
			$value = mb_convert_encoding($value, "sjis-win", "cp932");
			$value_csv .= $value.",";
		}
		$value_csv = substr($value_csv,0,-1);
		if($isonce == true)
		{
			$header_csv = substr($header_csv,0,-1);
			$where_csv = substr($where_csv,0,-1);
			$csv .= $where_csv."\r\n".$header_csv."\r\n".$value_csv."\r\n";
		}
		else
		{
			$csv .= $value_csv."\r\n";
		}
		$value_csv = "";
		$header_csv = "";
		$isonce = false;
		
	}
	$path = csv_write($csv);
	return($path);
}

/************************************************************************************************************
function delete($filename,$post,$data)

引数1		$post								入力内容
引数2		$data								登録ファイル内容

戻り値	なし
************************************************************************************************************/
function delete($filename,$post,$data,&$con){
	
	//------------------------//
	//        初期設定        //
	//------------------------//
	$form_ini = parse_ini_file('./ini/form.ini', true);
	require_once ("f_Form.php");
	require_once ("f_DB.php");																							// DB関数呼び出し準備
	require_once ("f_SQL.php");																							// DB関数呼び出し準備
	
	//------------------------//
	//          定数          //
	//------------------------//
//	$filename = $_SESSION['filename'];
	$tablenum = $form_ini[$filename]['use_maintable_num'];
	$list_tablenum = $form_ini[$tablenum]['see_table_num'];
	$list_tablenum_array = explode(',',$list_tablenum);
	$main_table_type = $form_ini[$tablenum]['table_type'];
	//------------------------//
	//          変数          //
	//------------------------//
	$sql = "";
	$judge = false;
	$codeValue = "";
	$code = "";
	$counter = 1;
	$main_CODE =0;
	$over = array();
	$delete =array();
	$delete_param = array();
	$delete_path = "";
	$delete_CODE = "";
	$list_insert ="";
	$list_insert_array = array();
	$content = "";
	//------------------------//
	//          処理          //
	//------------------------//
	//$con = dbconect();																									// db接続関数実行
	$code = $tablenum.'ID';
	$delete_CODE = $post[$code];
	$sql = DeleteSQL($delete_CODE,$tablenum,$code,$filename);
	for($i = 0; $i < count($sql); $i++)
	{
		$result = $con->query($sql[$i]) or ($judge = true);																		// クエリ発行
		if($judge)
		{
			error_log($con->error,0);
		}
	}
	////////////////////操作履歴///////////////////////
	//addSousarireki($filename, STEP_DELETE, $sql[0], $con);
	Sousarireki($filename, STEP_DELETE, $sql, $con,$post,$content);
	////////////////////操作履歴///////////////////////
	
	$delete_path = "";
	$delete_CODE = "";
	if($main_table_type == 0 && $list_tablenum != '')
	{
		for( $i = 0 ; $i < count($list_tablenum_array) ; $i++)
		{
			$list_insert = $form_ini[$list_tablenum_array[$i]]['page_num'];
			$list_insert_array = explode(',',$list_insert);
			$code = $list_tablenum_array[$i].'ID';
			for($j = 0; $j < count($list_insert_array) ; $j++)
			{
				if(isset($data[$list_tablenum_array[$i]]))
				{
					for($k = 0 ; $k < count($data[$list_tablenum_array[$i]]) ; $k++)
					{
						foreach($data[$list_tablenum_array[$i]][$k] as $key => $value)
						{
							if($key == '')
							{
								// 空アレイの場合
							}
							else if(strstr($key,$list_insert_array[$j]) == true )
							{
								$delete_path = $value;
								$delete_CODE = $data[$list_tablenum_array[$i]][$k][$code];
								break;
							}
						}
						if($delete_path != '' && $delete_CODE != '')
						{
							if(file_exists($delete_path))
							{ 
								unlink($delete_path );
							}
							$sql = DeleteSQL($delete_CODE,$list_tablenum_array[$i],$code,$filename);
							$result = $con->query($sql) or ($judge = true);												// クエリ発行
							if($judge)
							{
								error_log($con->error,0);
							}
							$delete_path = "";
							$delete_CODE = "";
						}
					}
				}
			}
		}
	}
	
	return $result;
}

/************************************************************************************************************
function existID($id)


引数	$id						検索対象ID

戻り値	$result_array			検索結果
************************************************************************************************************/
	
function existID($id){
	
	
	//------------------------//
	//        初期設定        //
	//------------------------//
	require_once("f_DB.php");						// DB関数呼び出し準備
	
	$form_ini = parse_ini_file('./ini/form.ini', true);
	
	//------------------------//
	//          定数          //
	//------------------------//
	$filename = $_SESSION['filename'];
	$tablenum = $form_ini[$filename]['use_maintable_num'];
	//$tablenum = $form_ini['use_maintable_num'];
	$tablename = $form_ini[$tablenum]['table_name'];
	//$tablename = $form_ini['table_name'];
	$selectidsql = "SELECT * FROM ".$tablename." where ".$tablenum."ID = ".$id." ;";
	
	//------------------------//
	//          変数          //
	//------------------------//
	$result_array =array();
	
	//------------------------//
	//        検索処理        //
	//------------------------//
	$con = dbconect();																									// db接続関数実行
	$result = $con->query($selectidsql);																				// クエリ発行
	if($result->num_rows == 1)
	{
		$result_array = $result->fetch_array(MYSQLI_ASSOC);
	}
	return($result_array);
}


/************************************************************************************************************
function deleterireki()

引数1		$sql						検索SQL

戻り値		$list_html					モーダルに表示リストhtml
************************************************************************************************************/
function deleterireki(){
	
	//------------------------//
	//        初期設定        //
	//------------------------//
	require_once("f_DB.php");																							// DB関数呼び出し準備
	require_once("f_File.php");																							// DB関数呼び出し準備
	$form_ini = parse_ini_file('./ini/form.ini', true);
	
	//------------------------//
	//          定数          //
	//------------------------//
	
	//------------------------//
	//          変数          //
	//------------------------//
	$date = date_create("NOW");
	$date = date_sub($date, date_interval_create_from_date_string('1 year'));
	$DATE = date_format($date, "Y-m-d");
//	$DATETIME = date_format($date, 'Y-m-d H:i:s');
	$DATETIME = $DATE." 00:00:00";
	$judge = false;
	//------------------------//
	//        検索処理        //
	//------------------------//
	$con = dbconect();																									// db接続関数実行
	$sql = "";
	$sql = "DELETE FROM genbainfo WHERE ENDDATE < '".$DATE."' ;";
	$result = $con->query($sql) or ($judge = true);																		// クエリ発行
	if($judge)
	{
		error_log($con->error,0);
		$judge = false;
	}
	$sql = "DELETE FROM saiinfo WHERE SAIUPDATE < '".$DATETIME."' ;";
	$result = $con->query($sql) or ($judge = true);																		// クエリ発行
	if($judge)
	{
		error_log($con->error,0);
		$judge = false;
	}
	$sql = "DELETE FROM rirekiinfo WHERE CREATEDATE < '".$DATE."' ;";
	$result = $con->query($sql) or ($judge = true);																		// クエリ発行
	if($judge)
	{
		error_log($con->error,0);
		$judge = false;
	}
	deletedate_change();
}


/************************************************************************************************************
function makeListRow()

  
 戻り値	list_html					リストhtml
************************************************************************************************************/
function mekeListRow()
{
//	//チェックボックス
//	if($isCheckBox == 1)
//	{
//		$list_html .="<td ".$id. "class = 'center'><input type = 'checkbox' name ='check_".$result_row[$main_table.'ID']."' id = 'check_".$result_row[$main_table.'ID']."'";
//		if(isset($post['check_'.$result_row[$main_table.'ID']]))
//		{
//			$list_html .= " checked ";
//		}
//		$list_html .=' onclick="this.blur();this.focus();" onchange="check_out(this.id)" ></td>';
//	}
	//No.表示
	if($isNo == 1)
	{
		$list_html .="<td ".$id." class = 'sequence'><a class='body'>".
						($limitstart + $counter)."</a></td>";
	}

	//実データ列
	for($i = 0 ; $i < count($columns_array) ; $i++)
	{
		//何度も見るので設定値を最初に絞る
		$column_setting = 	$form_ini[$columns_array[$i]];

		//設定ファイルから設定値を取得
		$field_name = $column_setting['column'];
		$format     = $column_setting['format'];
		$type       = $column_setting['form1_type'];
		$valigin    = $column_setting['list_align'];
		$value = $result_row[$field_name];

		//リンク指定の有無
		if(count($herf_link_array) > $i)
		{
			//リンク指定あり？
			if($herf_link_array[$i]=='1' && isset($column_setting['link_to']))
			{
				$link_to = $column_setting['link_to'];
				$link_key = $column_setting['link_key'];

				//リンクありの場合、値を<a href >で囲む
				$value = "<a href='pagejump.php?".$link_to."_button=&edit_list_id=".$result_row[$link_key]."'>".$value."</a>";
			}
		}

		//列幅指定
		$td_width = "";
		if(count($column_width_array) > $i)
		{
			//列幅の固定？
			if($column_width_array[$i]=='1')
			{
				//固定であるなら、サイズ指定を使用して幅を設定
				$width = $column_setting['form1_size'] * 4;
				$td_width = " style='width:".$width."px;'";
			}
		}

		//フォーマット指定
		if($format != 0)
		{
			$value = format_change($format, $value, $type);
		}

		//数値の場合は右寄せ
		switch($valigin)
		{
		case 1:
			$class = "class = 'center' ";
			break;
		case 2:
			$class = "class = 'right' ";
			break;
		default:
			$class = "";		
		}

		//書き込み
		$list_html .="<td ".$id." ".$class.$td_width." ><a class ='body'>".$value."</a></td>";
	}

	//編集ボタン
	if($isEdit == 1)
	{
		$table_id = mb_strtoupper($main_table);
		$list_html .= "<td ".$id." class='edit' valign='top'><input type='submit' name='edit_".
						$result_row[$table_id.'ID']."' value = '編集' ".$disabled."></td>";
	}
}



/************************************************************************************************************
function unsetSessionParam($session)

引数1	$session					

セッション情報など初期化
戻り値	無し
************************************************************************************************************/
function unsetSessionParam()
{
		//$step = $_SESSION['step'];
		//$edit_list_id = $_SESSION['edit_list_id'];
		$_SESSION = array();
		/*if($step == 5)
		{	
			$_SESSION = array();
			$_SESSION['filename'] = 'ANKENINFO_1';
			$_SESSION['list'] = array();
			$_SESSION['list']['id'] = $edit_list_id;
			$_SESSION['step'] = 2;
		}
		else
		{
			$_SESSION = array();
		}*/
		
		$_GET = null;

}
/************************************************************************************************************
function uniqueControl($session)

引数1	$value					

見積情報登録時 案件コード空白、未登録の場合
戻り値	無し
************************************************************************************************************/
function uniqueControl($value)
{
	$tablename = 'ankeninfo';
	
	//------------------------//
	//          処理          //
	//------------------------//
	
	$con = dbconect();
	$sql ="select * from $tablename where ANKID = $value" ;
	$result = $con->query($sql);
	$rownums = $result->num_rows;								//検索結果件数
	
	//案件コード空白、未登録だった場合
	if($rownums == 0)
	{
		//Auto_incrementの次回値取得
		$sql ="show table status like 'ankeninfo'";
		$row = $con->query($sql);
		$autorow = mysqli_fetch_object($row);
		$next_id = $autorow->Auto_increment;
		
		if($value != '')
		{	
			$insert_sql = "INSERT INTO $tablename (ANKID,ANKUCODE) VALUE ($value,'0000000');";
			$result = $con->query($insert_sql) or ($judge = true);								// クエリ発行
			if($judge)
			{
				error_log($con->error,0);
				$judge =false;
			}
			$_SESSION['step'] = '5';
			$_SESSION['edit_list_id'] = $value;//案件コード
			
		}
		else
		{
			//案件識別コードが空白の場合
			$value = $next_id;
			$insert_sql = "INSERT INTO $tablename (ANKID,ANKUCODE) VALUE ($next_id,'0000000');";
			$result = $con->query($insert_sql) or ($judge = true);								// クエリ発行
			if($judge)
			{
				error_log($con->error,0);
				$judge =false;
			}
			$_SESSION['step'] = '5';
			$_SESSION['edit_list_id'] = $value;//案件コード
		}
	}
	
	
	return ($value);
}

/*
 *指定のページへ呼ばせる関数
 *  
 */
function addSousarireki( $filename, $id, $sql, &$con )
{
	global $form_ini;
	//$judge = false;
	if( $form_ini === null)
	{
		$form_ini = parse_ini_file('./ini/form.ini', true);
	}

	$rireki_file_id = 'SOUSALIST_1';
	$sousa = 'ログイン';
	if( $id === STEP_INSERT )
	{
		$sousa = '追加';
	}
	if( $id === STEP_EDIT )
	{
		$sousa = '更新';
	}
	if( $id === STEP_DELETE )
	{
		$sousa = '削除';
	}
	//ステートメントを作成
	$stmt = $con->prepare('INSERT INTO sousarireki(GAMEN,SOUSA,SYOUSAI,UPDATEUSER) VALUES(?,?,?,?)');
    //パラメータをバインド
    $stmt->bind_param('sssi', $form_ini[$filename]['title'], $sousa, $sql, $_SESSION['userid']);
    //クエリを実行
    $stmt->execute();
    //ステートメントを閉じる
    $stmt->close();
	
	//return $judge;
}

/*
 * トランザクション開始関数
 * 
 */
function beginTransaction()
{
		//DB接続
		$con = dbconect();
		//トランザクション開始
		$con -> begin_transaction();
	
	return $con;
}
/**
 * 
 * トランザクションコミット
 */
function commitTransaction($judge,&$con)
{
	if($judge === true )
	{
		//トランザクションコミット
		$con->commit();
	}	
	else
	{
		//失敗時ロールバック
		$con->rollBack();
	}
	
}

/**
 * 出退勤選択専用登録
 * 
 * @param $post 入力内容
 * 
 */
function insert_stk($post)
{
	
	//------------------------//
	//        初期設定        //
	//------------------------//
	$form_ini = parse_ini_file('./ini/form.ini', true);
	require_once ("f_Form.php");
	require_once ("f_DB.php");																							// DB関数呼び出し準備
	require_once ("f_SQL.php");																							// DB関数呼び出し準備
	
	//------------------------//
	//          定数          //
	//------------------------//
	$filename = $_SESSION['filename'];
	$tablenum = $form_ini[$filename]['use_maintable_num'];
	$judge = false;
	//------------------------//
	//          処理          //
	//------------------------//
	$con = dbconect();																									// db接続関数実行
	$sql = InsertSQL_stk($post,$tablenum,"");
	$result = $con->query($sql) or ($judge = true);																		// クエリ発行
	if($judge)
	{
		error_log($con->error,0);
		$judge =false;
	}

	return $result;
}

/**
 * 出退勤選択専用退勤押し時
 * @param $post 更新内容
 * 
 * 
 */
function update_stk($post)
{
	//------------------------//
	//        初期設定        //
	//------------------------//
	$form_ini = parse_ini_file('./ini/form.ini', true);
	require_once ("f_Form.php");
	require_once ("f_DB.php");																							// DB関数呼び出し準備
	require_once ("f_SQL.php");																							// DB関数呼び出し準備
	
	//------------------------//
	//          定数          //
	//------------------------//
	$filename = $_SESSION['filename'];
	$tablenum = $form_ini[$filename]['use_maintable_num'];
	//------------------------//
	//          処理          //
	//------------------------//
	$con = dbconect();
	//社員確認
	$shain = $_SESSION['userid'];
	//更新データ検索
	$stkid = "select max(stkid) from timework where shaid = '$shain'";
	$stkresult = $con->query($stkid) or ($judge = true);																	// クエリ発行// db接続関数実行
	while($stkresult_row = $stkresult->fetch_array(MYSQLI_ASSOC))
	{
		$stkid = $stkresult_row['max(stkid)'];
	}		
	//更新処理
	$sql = UpdateSQL_stk($post,$tablenum,$stkid);
	$result = $con->query($sql) or ($judge = true);																		// クエリ発行
	if($judge)
	{
		error_log($con->error,0);
	}
	
	return $result;
}

/*
 *指定のページへ呼ばせる関数
 *  
 */
function Sousarireki( $filename, $id, $sql, &$con,$post ,$content)
{
	global $form_ini;
	//$judge = false;
	if( $form_ini === null)
	{
		$form_ini = parse_ini_file('./ini/form.ini', true);
	}

	$rireki_file_id = 'SOUSALIST_1';
	$date = date('Y/m/d');
	$user = mb_convert_encoding($_SESSION['HYOJIMEI'], "UTF-8","SJIS");
	$sousa = 'ログイン';
	if( $id === STEP_INSERT )
	{
		$sousa = '追加';
	}
	if( $id === STEP_EDIT )
	{
		$sousa = '更新';
		$naiyou = "[出勤変更前]" ;
		$naiyou .= $content['SHUTIME'];
		$naiyou .= "[出勤変更後]" ;
		$naiyou .= $post['form_stkSHUTIME_0'];
		$naiyou .= "[退勤変更前]" ;
		$naiyou .= $content['TAITIME'];
		$naiyou .= "[退勤変更後]" ;
		$naiyou .= $post['form_stkTAITIME_0'];
	}
	if( $id === STEP_DELETE )
	{
		$sousa = '削除';
		
		$naiyou = "[出勤]" ;
		$naiyou .= $post['form_stkSHUTIME_0'];
		$naiyou .= "[退勤]" ;
		$naiyou .= $post['form_stkTAITIME_0'];
	}
	
	$sql = "INSERT INTO srireki(SDATE,TNAME,GAMEN,NAIYOU) VALUES('$date' ,'$user','$sousa','$naiyou')";
	$result = $con->query($sql) or ($judge = true);																		// クエリ発行
	if($judge)
	{
		error_log($con->error,0);
	}
	//return $judge;
}