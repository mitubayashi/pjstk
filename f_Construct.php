<?php

// 定数
const APPLICATION_NAME = "マーキュリーソフト";
const STEP_INSERT = 1;
const STEP_EDIT = 2;
const STEP_DELETE = 3;
const STEP_COMP = 4;
const STEP_PRINT = 5;
const STEP_NONE = 0;

const PAGE_NONE = 0;
const PAGE_ALL = 1;
const PAGE_COUNT_ONLY = 2;


function start(){
//	if ((isset($_SESSION['userName']) == false) || (isset($_SESSION['pre_post'] ) == false))
//	{
//		header("location:".(empty($_SERVER['HTTPS'])? "http://" : "https://").
//							$_SERVER['HTTP_HOST'].dirname($_SERVER["REQUEST_URI"])."/retry.php");
//		exit();
//	}
}
function startJump($post){
//	$judge = false;
//	if (isset($_SESSION['userName']) == false || count($post) == 0)
//	{
//		header("location:".(empty($_SERVER['HTTPS'])? "http://" : "https://").
//							$_SERVER['HTTP_HOST'].dirname($_SERVER["REQUEST_URI"])."/retry.php");
//		exit();
//	}
}

function convertGet2Post()
{

	// GET情報をPOST情報に格納する
	if( isset($_GET) )
	{
		foreach($_GET as $key  =>  $value)
		{
			$_POST[$key] = $value;
		}

	}

}
function convertPost2Get()
{

	// GET情報をPOST情報に格納する
	if( isset($_POST) )
	{
		foreach($_POST as $key  =>  $value)
		{
			$_GET[$key] = $value;
		}

	}

}

/**
 * 関数名: getAutoUpdateValue
 *   自動更新値、デフォルト値を取得するための関数
 *   
 * @param [string] $setting_value	設定値文字列
 * @param [array] $post 送信情報

 * @retrun 採番文字列
 */
function getAutoUpdateValue( $setting_value, $post )
{
	$result = '';

	//@の有無を検索
	$pos = strpos($setting_value,'@');
	if( $pos == false )
	{
		//@がない場合はそのまま
		$result = $setting_value;
		
	}
	else
	{
		//スコープ指定の切り出し
		$scope_temp = substr($setting_value,$pos+1);
		$scope = str_replace ( "'", '', $scope_temp );
		//変数名指定の切り出し
		$valname_temp =  substr($setting_value,0,$pos);
		$valname = str_replace ( "'", '', $valname_temp );

		//値の取得
		$outvalue = '';
		if( $scope === 'session' )
		{
			/*if( array_key_exists ( 'SYAMEI', $_SESSION ) === false)
			{	//未ロードの場合は自社情報読み出し
				loadJisyaMaster();
			}*/
			//session指定なのでsessionから値を取得
			$outvalue = $_SESSION[$valname];
		}
		else if( $scope === "post")
		{
			//post指定なのでpostから取得
			if( array_key_exists ( $valname, $post ) === true)
			{
				$outvalue = $post[$valname];
			}
			else
			{
				$outvalue = $post['form_'.$valname.'_0'];				
			}
		}	
		else if( $scope === "saiban")
		{
			$outvalue = getAutoSaiban( $valname );
		}
		else if( $scope === "system")
		{
			if( $valname === 'date' )
			{
				$outvalue = date("Y/m/d");
			}
			if( $valname === 'datetime' )
			{
				$outvalue = date("Y/m/d H:i:s");
			}
		}
		
		//値の編集
		$result = str_replace ( $valname.'@'.$scope, $outvalue, $setting_value );
	}
	
	// 「|」 がある場合改行
	if(strstr($setting_value,'|') !== false)
	{
		$result = str_replace('|',"\n",$setting_value);
		//$result = nl2br($result);
	}
	
	return $result;
}


/**
 * 関数名: getAutoSaiban
 *   指定の採番をカウントアップし、結果を返す
 * 
 * @retrun 採番文字列
 */
function getAutoSaiban( $saiban_id )
{
	$ukey = $saiban_id.'UKEY';

	if(array_key_exists( $ukey, $_SESSION ) === false )
	{
		//自社マスタロード
		loadJisyaMaster();
	}
	
	//識別子(全社+個人)
	if(array_key_exists( 'PSUKEY', $_SESSION ) === false )
	{
		//※暫定処理。本来はタイムアウトしていなければ必ずある
		$_SESSION['PSUKEY'] = '';
	}
	$ukeyValue = $_SESSION[$ukey] . $_SESSION['PSUKEY'];
	
	//-----------------------------------//
	//     DBアクセス処理(ストアド実行)  //
	//-----------------------------------//
	$con = dbconect();
	//ステートメントを作成
	$stmt = $con->prepare('call SAIBAN(?)');
    //パラメータをバインド
    $stmt->bind_param("s", $ukeyValue);
    //クエリを実行
    $stmt->execute();
    //結果変数をバインド
    $stmt->bind_result($saiban);
    //値取得
    $stmt->fetch();
    //ステートメントを閉じる
    $stmt->close();
	
	return $saiban;
}

/**
 * 関数名: loadJisyaMaster
 *   自社マスタの必要情報をSESSIONに読込む
 * 
 * @retrun なし
 */
function loadJisyaMaster()
{
	//DB接続
	$con = dbconect();	
	//SQL実行
	$result = $con->query('SELECT * FROM jisyamaster');				// クエリ発行

	//行数ループ
	while($result_row = $result->fetch_array(MYSQLI_ASSOC))
	{
		foreach ( $result_row as $list_key => $list_text )
		{
			//識別
			$_SESSION[$list_key] =$list_text;
		}
		break;
	}
	$result->close();
}

/**
 * 関数名: suggets.iniの設定を使用してDBから値を取得する
 * 
 * @retrun 取得値
 */
function getSuggestValue( $suggest_id, $key )
{
	//DB接続
	$suggest_ini = parse_ini_file('./ini/suggest.ini', true);		// suggest.ini

	//IDからテーブル名など取得
	$select_sql = $suggest_ini[$suggest_id]['select_sql'];
	$key_column  = $suggest_ini[$suggest_id]['key_column'];
	$key_param  = $suggest_ini[$suggest_id]['key_param'];	
	$value_column  = $suggest_ini[$suggest_id]['value_column'];	
	$label_column  = $suggest_ini[$suggest_id]['label_column'];
	$order_by  = $suggest_ini[$suggest_id]['order_by'];
	$limit  = $suggest_ini[$suggest_id]['limit'];
	$add_column_string  = $suggest_ini[$suggest_id]['result_add_column'];
	$add_column  = explode(',',$add_column_string);
	$add_column_count = count($add_column);
	
	//検索条件
	$whereSql = "";
	if( $key != "" )
	{
		$whereSql .= " WHERE " .$key_column.(str_replace("@param", $key, $key_param));
	}
	
	//ORDER BY指定
	if($order_by != "")
	{
		$order_by = " ORDER BY ".$order_by;
	}

	//SQL文
	$sql = $select_sql. $whereSql.$order_by. " LIMIT " .$limit;

	// db接続関数実行
	$con = dbconect();
	$result = $con->query( $sql );																	// クエリ発行
	if(!$result)
	{
		error_log($con->error,0);
		exit();
	}

	$resultArray = array();
	$count = 0;
	while($result_row = $result->fetch_array(MYSQLI_ASSOC))
	{
		$rowArray =array( 'KEY'=>$result_row[$key_column],
						  'VALUE'=>mb_convert_encoding($result_row[$value_column], "UTF-8", "SJIS"),
						  'LABEL'=>mb_convert_encoding($result_row[$label_column], "UTF-8", "SJIS"));
		//追加カラム有無
		if( $add_column_string != '' )
		{
			//追加分のカラムを処理
			for( $i= 0; $i < $add_column_count; $i++ )
			{
				$add_temp = $add_column[ $i ];
				$rowArray[$add_temp] = mb_convert_encoding($result_row[$add_temp], "UTF-8", "SJIS");
			}
			
		}
		//配列に割り当て
		$resultArray[$count] = $rowArray;
		$count++;
	}
	
	return $resultArray;
}

/**
 * 関数名: ユーザー情報を取得する
 * 
 * @retrun ユーザー情報
 */
function loginUserValue($userid)
{
	//DB接続
	$con = dbconect();	
	//SQL実行
	$result = $con->query('SELECT * FROM loginuserinfo where USRID ='.$userid);				// クエリ発行

	//行数ループ
	while($result_row = $result->fetch_array(MYSQLI_ASSOC))
	{
		foreach ( $result_row as $list_key => $list_text )
		{
			//識別
			$value[$list_key] =$list_text;
		}
		break;
	}

	return $value;
}

/**
 * 関数名: ajustFilename
 *			filenameを調整する
 * 
 * @retrun なし
 */
function ajustFilename()
{
	//filename を調整する
	if(strpos($_SESSION['filename'],'PRINT_5') !== false)
	{
		$now_filename = str_replace('PRINT_5', 'INFO_1', $_SESSION['filename']);
		$_SESSION['filename'] = $now_filename;
	}
}

/**
 * 関数名: ユーザー情報を取得する
 * 
 * @retrun ユーザー情報
 */
function loadDBRecord($table,$id)
{
	global $form_ini;
	if( $form_ini != null)
	{
		$form_ini = parse_ini_file('./ini/form.ini', true);
	}

	$values = array();
	//DB接続
	$con = dbconect();	
	//SQL実行
	$result = $con->query('SELECT * FROM '.$form_ini[$table]['table_name'].' WHERE '. strtoupper($table).'ID ='.$id);				// クエリ発行
	//行数ループ
	while($result_row = $result->fetch_array(MYSQLI_ASSOC))
	{
		foreach ( $result_row as $list_key => $list_text )
		{
			$values[$list_key] =$list_text;
		}
		break;
	}

	return $values;
}

/**
 * 出退勤読取専用判定関数
 * 
 */
function getReadOnly()
{
	$shaid = $_SESSION['userid'];//ログインした社員コード
	$sql = "select * from timework where STKID = (select max(STKID) from timework where SHAID = $shaid)";
	$con = dbconect();	
	//SQL実行
	$result = $con->query($sql);				// クエリ発行
	$rownums = $result->num_rows;
	if($rownums == 0 )
	{
		return 1;
	}
	else
	{	
		while($result_row = $result->fetch_array(MYSQLI_ASSOC))
		{
			$taitime = $result_row['TAITIME'];
		}
		if(isset($taitime))
		{
			
			return 1;
		}
		else
		{
			
			return 2;
		}	

	}
	
}
