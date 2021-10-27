<?php
require_once("classesPageContainer.php");
require_once("classesBase.php");
require_once("classesHtml.php");
require_once("classesPageFactory.php");
require_once("classesExecute.php");
require_once("f_DB.php");


/*
 * 出退勤専用insert,update
 */
class ShutaikinExecuteSQL extends BaseLogicExecuter
{

	/**
	 * executeSQL
	 * データ操作の実行
	 */
	public function executeSQL()
	{
		$step = $this->prContainer->pbStep;
		$filename = $this->prContainer->pbFileName;
		//DB接続、トランザクション開始
		$con = beginTransaction();
		if($step == STEP_INSERT)//データ登録
		{
			$result = insert_stk($this->prContainer->pbInputContent);
		}
		else if($step == STEP_EDIT)//データ編集
		{
			$result = update_stk($this->prContainer->pbInputContent);
		}
		
		//トランザクションコミットまたはロールバック
		commitTransaction($result,$con);

		$this->PageJump($filename, $_SESSION['userid'], 1, "", "");
	}
}

class CsvImportExecuteSQL extends BaseLogicExecuter
{
    public function executeSQL()
    {
        $filename = $this->prContainer->pbFileName;
        
        foreach($_FILES as $form => $value)
        {
            if ($value['size'] != 0) {
                $file_array = explode('.', $value['name']);
                $extention = $file_array[(count($file_array) - 1)];
                $tempfile = './temp/';
                $tempfile .= "tempfileinsert.txt";
                move_uploaded_file($value['tmp_name'], $tempfile);
            }
        }

        //DB接続、トランザクション開始
        $con = beginTransaction();

        //------------------------//
        //          定数          //
        //------------------------//
        $FilePath = "temp/tempfileinsert.txt";
        
        //------------------------//
        //          変数          //
        //------------------------//
        $countrow = 0;
        $readBody = array();												//読み込み配列
        $userId = "";														//ユーザー名
        $shutime = "";													//出勤時間
        $taitime = "";                                                                                                 //退勤時間
        $shaId = array();                                                                                               //SHAID
        
        //------------------------//
        //        取込処理        //
        //------------------------//
        
        //取込データを読み込み
        $file = fopen($FilePath, "r");
        if($file)
        {
            while($line = fgets($file))
            {
                $strsub = explode(",", trim($line)); //カンマ区切りのデータを取得
                $userId = mb_convert_encoding( $strsub[0], "utf-8", "utf-8");
                $readBody[$countrow]['userId'] = $userId;
                $shutime = mb_convert_encoding($strsub[1], "utf-8", "utf-8");
                $readBody[$countrow]['shutime'] = $shutime;
                $taitime = mb_convert_encoding($strsub[2], "utf-8", "utf-8");
                $readBody[$countrow]['taitime'] = $taitime;
                $countrow++;
            }
        }
        fclose($file);
        
        //------------------------//
        //       チェック処理     //
        //------------------------//
        for($i = 0; $i < count($readBody); $i++) 
        {
            if($readBody[$i]['userId'] == '' || $readBody[$i]['shutime'] == '' || $readBody[$i]['taitime'] == '')            //ブランクのチェック
            {
                $filename = 'CSVIMPORT_1';
                $this->PageJump($filename, $_SESSION['userid'], 1, "error", "");
            }
            else if($readBody[$i]['shutime'] !== date("Y-m-d H:i:s", strtotime($readBody[$i]['shutime'])) || $readBody[$i]['taitime'] !== date("Y-m-d H:i:s", strtotime($readBody[$i]['taitime'])))     //出勤時間と退勤時間のフォーマットは正しいか
            {
                $filename = 'CSVIMPORT_1';
                $this->PageJump($filename, $_SESSION['userid'], 1, "error", "");
            }
            
            for ($i = 0; $i < count($readBody); $i++) 
            {
                //shainmasterからSHAIDを取得
                $sql = "SELECT * FROM shainmaster WHERE SHAUSERID = '" . $readBody[$i]['userId'] . "';";
                $result = $con->query($sql);
                $rownums = $result->num_rows;																					// 検索結果件数取得
                if($rownums == 0)                    //IDは正しいか
                {
                    $filename = 'CSVIMPORT_1';
                    $this->PageJump($filename, $_SESSION['userid'], 1, "error", "");
                }
                
                foreach ($result as $row) 
                {
                    $shaId = $row['SHAID'];
                }
            }

            for ($i = 0; $i < count($readBody); $i++) 
            {
                $shutime = $readBody[$i]['shutime'];
                $taitime = $readBody[$i]['taitime'];
                //timeworkに登録
                $sql = "INSERT INTO timework (SHAID,SHUTIME,TAITIME,AUTOSHUDATE,AUTOTAIDATE,UPDATETIME,UPDATEUSER) "
                        . "VALUE (" . $shaId . ",'" . $shutime . "','" . $taitime . "',NOW(),NOW(),NOW()," . $_SESSION['SHAID'] . ");";
                $result = $con->query($sql);
            }
        }     
        //トランザクションコミットまたはロールバック
	commitTransaction($result,$con);
        
        $filename = 'SHUTAIKIN_1';
        $this->PageJump($filename, $_SESSION['userid'], 1, "", "");
    }
}