<?php
require_once("classesPageContainer.php");
require_once("classesBase.php");
require_once("classesHtml.php");
require_once("classesPageFactory.php");
require_once("classesExecute.php");


class ShutaikinPage extends InsertPage
{
	

	/**
	 * 関数名: makeBoxContentMain
	 *   メインの機能提供部分の上部に表示されるHTML文字列を作成する
	 *   機能名の表示など
	 * 
	 * @retrun HTML文字列
	 */
	function makeBoxContentMain()
	{
		$out_column ='';
		//----↓入力項目作成----//

			//出退勤選択画面作成
		$form_array = $this->makeformInsert_stk($this->prContainer->pbInputContent, $out_column, '', "insert", $this->prContainer);
		$form = $form_array[0];
		$makeDatepicker =  $form_array[1];
		
		//----↑入力項目作成----//
		
        //多重送信防止
        if($_SESSION["filename"] == "SHUTAIKIN_1")
        {
            $onsubmit = 'onsubmit="this.onsubmit=function(){return false}"';
        }
        else
        {
            $onsubmit = "";
        }
        
		$send = '<form name ="insert" action="main.php" method="get" enctype="multipart/form-data" '.$onsubmit.'>';
		$this->prInitScript = $makeDatepicker;//メンバ変数に保存
		$html = '<br>';
		$html .= $send;
		$html .= '<div class = "edit_table">';
		$html .= $form;
		$html .= '</div>';
		
        //他画面で操作されていた場合
        if(isset($_SESSION['syutaierror']))
        {
            $step = $_SESSION['syutaierror'];
            unset($_SESSION['syutaierror']);
            if($step == STEP_INSERT)
            {
                $errormsg = "出勤済みです";
            }
            if($step == STEP_EDIT)
            {
                $errormsg = "退勤済みです";
            }
            $html .= "<script>alert('".$errormsg."');</script>";
        }
         
		return $html;
	}

	/**
	 * 関数名: makeBoxContentBottom
	 *   メインの機能提供部分下部のHTML文字列を作成する
	 *   他ページへの遷移ボタンなどを作成
	 * 
	 * @retrun HTML文字列
	 */
	function makeBoxContentBottom()
	{
		$html = '</form>';
		return $html;
	}

		/**
	 * 出退勤選択画面
	 * 出勤、退勤ボタンを作成
	 * datepocker,timepicker jQuery作成
	 * @param int  $post 入力内容
	 * @param  $out_column
	 * @param  $isReadOnly
	 * @param  $formName
	 * @param $form_ini 画面設定値
	 * @param $ParamSet 項目設定値 
	 * 
	 * 戻り値　array
	 */
	function makeformInsert_stk($post, $out_column, $isReadOnly, $formName ,&$Container)
	{
		//------------------------//
		//        初期設定        //
		//------------------------//
		$form_ini = $Container->pbPageSetting;//form_ini画面の設定値												
		$ParamSet = $Container->pbParamSetting;//form_ini項目設定値
		//------------------------//
		//          定数          //
		//------------------------//
		$out_column = explode(',',$out_column);																// 入力チェック(php側)で不可カラム番号配列
		$filename = $Container->pbFileName;																	// ページID
		$columns = $form_ini['page_columns'];
		$columns_array = explode(',',$columns);																// 登録カラム一覧(配列)
		$readonly = $form_ini['readonly'];																	// 読取専用項目
		$readonly_array = explode(',',$readonly);															// 読み取り専用項目(配列)
		//------------------------//
		//          変数          //
		//------------------------//

		$Colum = "";																						// 作成対象フォームのカラム番号
		$insert_str = "";																					// 入力フォームhtml 戻り値
		$isout = false;																						// 作成対象フォームが入力チェック(php側)不可カラムか
		$form_result = array();																				// リストテーブルの繰り返しID配列
		$error ="";
		$datepicker ="";

		//------------------------//
		//          処理          //
		//------------------------//
		$date = date("Y/m/d");	//現在日付
		$time = date("H:i");	//現在時間	

		$insert_str .= "<table name ='form" .$formName. "' id ='" .$formName. "'>";
		//日付、時間入力項目
		$insert_str .= "<td class = 'space'></td>";
		$insert_str .= "<td class ='one'><a class = 'itemname'>日付</a></td>";
		$insert_str .= "<td class = 'two'><input type ='text' name = 'workdate' id = 'workdate' class = 'workdate' value = '$date'  ></td></tr>";
		$insert_str .= "<td class = 'space'></td><td class ='one'><a class = 'itemname'>時間</a></td>";
		$insert_str .= "<td class = 'two'><input type ='text' name = 'worktime' id = 'worktime' class = 'worktime' value = '$time'  >";
		$insert_str .= "<input type ='button' name = 'timebutton' id = 'timebutton' class='timebutton' value ='...'  >";
		$insert_str .= "</td></tr>";
	
		//読取専用
		$disabled = getReadOnly();
		if($disabled == 1)
		{
			$shukkin = "";
			$taikin = "disabled";
		}
		else
		{
			$shukkin = "disabled";
			$taikin = "";
		}
		
		
		/*$insert_str .='<td class = "space"></td><td class ="one"></td>';//@var $form_element_str 戻り値?となるフォームHTML文字列 
				
		$insert_str .= '<td class = "two"><input type ="submit" name = "Comp_insert" id = "form_stkSHUTIME_0" class="shukkin" value = "出勤" '.$shukkin.' >';
		
		
		$insert_str .='<td class = "space"></td><td class ="one"></td>';// @var $form_element_str 戻り値?となるフォームHTML文字列 
		$insert_str .= '<td class = "two"><input type ="submit" name = "Comp_update" id = "form_stkSHUTIME_0" class="shukkin" value = "退勤" '.$taikin.' >';*/
		

		$insert_str .= "</table>";
		
		$insert_str .= '<input type ="submit" name = "Comp_insert" id = "form_stkSHUTIME_0" class="shukkin" value = "出勤" '.$shukkin.' >';
		$insert_str .= '<input type ="submit" name = "Comp_update" id = "form_stkSHUTIME_0" class="shukkin" value = "退勤" '.$taikin.' >';
		
		
		
		//Datetimepickerスクリプト
		$datepicker .= "$('#workdate').datepicker();$('#workdate').datepicker('option', 'showOn', 'button');";
		//timepickerスクリプト
		$datepicker .= "$('#worktime').timepicker();$('#worktime').timepicker('option', 'step', '15');$('#worktime').timepicker('option', 'timeFormat', 'H:i');";
		$datepicker .= "$('#timebutton').on('click', function(){
		$('#worktime').timepicker('show');
	});";
		
		//戻り値
		$form_result[0] = $insert_str;
		$form_result[1] = $datepicker;

		return ($form_result);

	}
}


class WorkInfoPage extends ListPage
{
	/**
	 * 関数名: makeBoxContentMain
	 *   メインの機能提供部分の上部に表示されるHTML文字列を作成する
	 *   機能名の表示など
	 * 
	 * @retrun HTML文字列
	 */
	function makeBoxContentMain()
	{
		//デフォルトで検索条件を設定
		if(isset($this->prContainer->pbListId))
		{
			$value = loadDBRecord('sha', $this->prContainer->pbListId);
			$this->prContainer->pbInputContent['form_shaSHAHYOJIMEI_0'] = $value['SHAHYOJIMEI'];
		}	
		if(!isset($_SESSION['list']))
		{
			$_SESSION['list'] = array();
		}
		//検索フォーム作成,日付フォーム作成
		$formStrArray = $this->makeformSearch_setV2( $this->prContainer->pbInputContent, 'form' );
		$form = $formStrArray[0];			//0はフォーム用HTML
		$this->prInitScript = $formStrArray[1];	//1は構築用スクリプト
		
		//検索SQL
		$sql = array();
		$sql = joinSelectSQL($this->prContainer->pbInputContent, $this->prMainTable, $this->prContainer->pbFileName, $this->prContainer->pbFormIni);
		$sql = SQLsetOrderby($this->prContainer->pbInputContent, $this->prContainer->pbFileName, $sql);
		$limit = $this->prContainer->pbInputContent['list']['limit'];				// limit
		$limit_start = $this->prContainer->pbInputContent['list']['limitstart'];	// limit開始位置
		
		//リスト表示HTML作成
		$pagemove = intval( $this->prContainer->pbPageSetting['isPageMove'] );
		$list =  $this->makeListV2($sql, $_SESSION['list'], $limit, $limit_start, $pagemove);
		
		$checkList = $_SESSION['check_column'];
		
		//出力HTML作成
		$html ='<div class = "pad" >';
		$html .='<form name ="form" action="main.php" method="get"onsubmit = "return check(\''.$checkList.'\');">';
		$html .='<table><tr><td><fieldset><legend>検索条件</legend>';
		$html .= $form;								//検索項目表示
		$html .='</fieldset></td><td valign="bottom"><input type="submit" name="serch" value = "表示" class="free" ></td></tr></table>';
		$html .= $list;
		$html .= '</form>';
		
		return $html;
		
	}
}

class csvImport extends InsertPage{
    
    function makeBoxContentMain()
    {
        $html = '<form name ="fileinsert" action="main.php" method="post" enctype="multipart/form-data" 
				onsubmit = "return check();">';
        $html .= '<div style="margin-top:1%;margin-left:12%" >';
        $html .= '<input type="file" name="sansyo" value="" />';
        $html .= '<br>';
        $html .= '<p>勤務時間の取込情報は、<br>ユーザー名,出勤時間,退勤時間の形式でCSVを作成してください。</p>';
        
        $content = $this->prContainer->pbInputContent;
        if($content == "error")
        {
            $html .= '<a class = "error">CSVを取り込めませんでした。</a>';
        }
        
        return $html;
    }
    
    function makeBoxContentBottom() 
    {
        $html = '<div class = "center"><input type="submit" name = "Comp" value = "登録" class="free" style="margin-top:2%;margin-left:-15%" >';
        $html .= '</form>';
        
        return $html;
    }
}
