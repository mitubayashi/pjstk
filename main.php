<?php
		// セッション開始
		session_start();
		require_once("f_Construct.php");
		require_once("f_Button.php");
		require_once("classesHtmlCustom.php");
		require_once("classesExecuterCustom.php");
		
		//変数
		$execute = "";
		$list_id = "";
		$step = 0;
		$form_ini = parse_ini_file('./ini/form.ini', true);
		$number = 0;
		//遷移画面の記録
		if(isset($_SESSION['history'][$number]))
		{
			$number = count($_SESSION['history']);
			
		}
			
		if(!isset($_SESSION['userid']))
		{
			header("location:".(empty($_SERVER['HTTPS'])? "http://" : "https://")
			.$_SERVER['HTTP_HOST'].dirname($_SERVER["REQUEST_URI"])."/error.php");
			
			exit();
			//login("s","s");
		}
		
		//$_POSTを$_GETに移植する
		convertPost2Get();
		
		$keyarray = array_keys($_GET);
		
		if(isset($_SESSION['step']))
		{
			$step = $_SESSION['step'];
			unset($_SESSION['step']);
		}
		
		foreach($keyarray as $key)//ページ遷移
		{
			if(strstr($key, 'TOP_5') != false)
			{
				$_SESSION['step'] = 0;
				$pre_url = explode('_',$key);
				$_SESSION['filename'] = $pre_url[0]."_".$pre_url[1];
			}
			if(strstr($key, '_button') != false )//新規作成、編集判定
			{
				$pre_url = explode('_',$key);
				if($pre_url[1] == 1)
				{
					if(isset($_GET['edit_list_id']))//リストページから編集へ
					{
						//編集ID所持
						$list_id = $_GET['edit_list_id'];
						$_SESSION['list'] = array();
						$step = STEP_EDIT;
						
					}
					else if(isset($_SESSION['list']['id']))
					{
						$list_id = $_SESSION['list']['id'];
						$step = STEP_EDIT;
					}
					else
					{
						//$_SESSION['step'] = 1;//新規作成へ
						$step = STEP_INSERT;
					}
					//一部機能でのstepすり替え
					if( $pre_url[0] === 'NYUKIN' )
					{
						$step = STEP_EDIT;
					}

				}
				else if($pre_url[1] == 2)
				{
					//リスト画面作成
					$step = STEP_NONE;
				}
				else if($pre_url[1] == 3)//編集のみ
				{
					if(isset($_GET['edit_list_id']))
					{
						$list_id = $_GET['edit_list_id'];
					}
					else {
						$_SESSION['list'] = array();
						$_SESSION['list']['id'] = 1;					
					}
					$step = STEP_EDIT;
				}
				else if($pre_url[1] == 6)
				{
					if(isset($_GET['edit_list_id']))
					{
						$list_id = $_GET['edit_list_id'];
					}
				}
				else if($pre_url[1] == 9)
				{
					if(isset($_GET['edit_list_id']))
					{
						$list_id = $_GET['edit_list_id'];
						$step = STEP_INSERT;
					}
				}
				
				$_SESSION['filename'] = $pre_url[0]."_".$pre_url[1];
				break;
			}
			else if($key == 'insert')//データ登録
			{
				$step = STEP_INSERT;
				$_SESSION['filename'] = $keyarray[0];
				ajustFilename();	//PRINT_5⇒INFO_1にアジャスト
			}
			else if($key == 'kousinn')//データ更新
			{
				$step = STEP_EDIT;
				ajustFilename();	//PRINT_5⇒INFO_1にアジャスト
			}
			else if (strstr($key, 'serch'))//データ検索時
			{
				//$_SESSION['step'] = 0;
				$step = STEP_NONE;
				$filename_array = explode('_',$_SESSION['filename']);
				$_SESSION['filename'] = $filename_array[0].'_'.'2';
				$_SESSION['list'] = $_GET;
			}
			else if($key == 'cancel')//一覧へ戻る
			{
				if(isset($_SESSION['upload']) == true)
				{
					foreach($_SESSION['upload'] as $delete => $file)
					{
						unlink($file);
					}
				}
				unset($_SESSION['files']);
				//リスト画面作成
				//$_SESSION['step'] = 0;
				$step = STEP_NONE;
				$filename = $_SESSION['filename'];
				$pre_url = explode('_',$filename);
				$_SESSION['filename'] = $pre_url[0]."_"."2";
			}
			else if($key == 'delete')//データ削除時
			{
				$step = STEP_DELETE;
				$_GET['step'] = $step;
				$_SESSION['edit'] = $_GET;
				ajustFilename();	//PRINT_5⇒INFO_1にアジャスト
			}
			else if(strstr($key, 'edit_'))//編集ボタン押し時
			{
				$idarray = explode('_',$key);
				//入金処理  入金確認時
				if($idarray[2] == "Comp")
				{
					$list_id = $idarray[1];
					$step = STEP_EDIT;

					$filename_array = explode('_',$_SESSION['filename']);
					$_SESSION['filename'] = $filename_array[0].'_'.'1';
					break;
				}
				else if($idarray[2] == "Del")
				{
					$list_id = $idarray[1];
					$step = STEP_DELETE;

					$filename_array = explode('_',$_SESSION['filename']);
					$_SESSION['filename'] = $filename_array[0].'_'.'1';
					break;
				}
				
			}
			else if(strstr($key, 'print'))//印刷画面
			{
				if( $keyarray[0] ==='MITSUMORIINFO_1' || $_SESSION['filename'] == 'MITSUMORIINFO_1')
				{
					$_SESSION['filename'] = 'MITSUMORIPRINT_5';
				}
				else if($keyarray[0] ==='SEIKYUINFO_1' || $_SESSION['filename'] == "SEIKYUINFO_1")
				{
					$_SESSION['filename'] = 'SEIKYUPRINT_5';
				}	
				//$_SESSION['step'] = 5;
				$step = STEP_PRINT;
			}
			else if(strstr($key, 'Comp_'))//データ処理
			{
				$idarray = explode('_',$key);
				$execute = $idarray[0];
				if(isset($idarray[1]))
				{
					if($idarray[1] == 'insert')
					{
						$step = STEP_INSERT;
					}
					else if($idarray[1] == 'update')
					{
						$step = STEP_EDIT;
					}	
				}
				else
				{
					$step = $_GET['step'];
				}	
				
			}
			else if($key == 'Comp')
			{
				$execute = $key;
				$step = $_GET['step'];
			}	
        }

		//$filename決定
		if(isset($_SESSION['filename']))
		{
			$filename = $_SESSION['filename'];
			
			/*if( $filename !== "USERMASTER_1" && isPermission($filename) === false)	{
				$_SESSION['filename'] = 'TOP_5';
				
				 header("location:".(empty($_SERVER['HTTPS'])? "http://" : "https://")
			   .$_SERVER['HTTP_HOST'].dirname($_SERVER["REQUEST_URI"])."/main.php?TOP_5");
				exit();	
			}*/	
		}
        else
        {
			$_SESSION['filename'] = 'SHUTAIKIN_1';
			$filename = 'SHUTAIKIN_1';
			$step = STEP_INSERT;
		}
		
		
		//FactoryにPageを作ってもらう
		$factory = PageFactory::getInstance();
		
		//フォーム設定情報の読込み
		$container = new PageContainer( $factory->pbFormIni );
		//指定IDの情報をメンバ変数に
		$container->ReadPage( $filename, $list_id, $step );
		//$container->ReadPage($filename);
		
		$executer = $factory->createExecuter( $filename, $container );
		
		if($executer == null)
		{	
			//ページ判定
			$page = $factory->createPage( $filename, $container );
			
			//html上部作成
			$page->executePreHtmlFunc();
			
			//作ったPageにHTMLを吐かせる
			$page->echoAllHtml();
		}
		else
		{
			//データ処理
			$executer->executeSQL();
		}
		
		//画面遷移記録
		$_SESSION['history'][$number] = $filename;
		//dbを閉じる
		dbclose();
