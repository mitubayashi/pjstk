<?php

/**
 * 遷移先の判定を行い、Pageオブジェクトを生成する
 */
class PageFactory
{
	protected static $factory;
	public $pbFormIni;
	/**
	 * コンストラクタ
	 */
	protected function __construct()
	{
		$this->pbFormIni = parse_ini_file('./ini/form.ini', true);
		require_once("classesPageContainer.php");
		require_once("classesExecute.php");
	}
	
   /**
    * インスタンス生成と取得用関数
    * 
     * @return PageFactoryインスタンス
    */
	public static function getInstance()
	{
		if(!isset(self::$factory))
		{
			self::$factory = new PageFactory();
		}
		return self::$factory;
	}
	
    /**
    * Pageオブジェクトの生成
    * 
    * @param string $filename ページID指定文字列
    * @return BasePage
    */
	public function createPage($filename,$container)
	{
		$page = null;
		$pre_url = explode('_',$filename);
		
		//画面判定変数
		$step = $container->pbStep;
		
		//--↓↓↓↓↓ワンオフの特殊ページ↓↓↓↓↓--
		if($filename === 'SHUTAIKIN_1')
		{
			$page = new ShuTaikinPage($container);
		}
		if($filename === 'WORKINFO_2')
		{
			$page = new WorkInfoPage($container);
		}
                if($filename === 'CSVIMPORT_1')
                {
                        $page = new csvImport($container);
                }
		if($page !== null)
		{
			return $page;
		}
		//--↑↑↑↑↑ワンオフの特殊ページ↑↑↑↑↑--
		
		//汎用ページ
		if($pre_url[1] === '1')//登録、編集
		{
			if($step == STEP_INSERT)//データ登録
			{
				if($container->pbPageCheck === 'InsertCheck')
				{
					$page = new InsertCheckPage($container);
				}
				else
				{
					$page = new InsertPage($container);
				}
			}
			else if($step == STEP_EDIT)//データ編集
			{
				if($container->pbPageCheck === 'EditCheck')
				{	
					if($filename ==='USERMASTER_1' ){
						$page = new UserMasterCheckPage($container);
					}
					else{
						$page = new EditCheckPage($container);
					}
				}
				else
				{
					if($filename ==='USERMASTER_1' ){
						$page = new UserMasterPage($container);
					}
					else{
						$page = new EditPage($container);
					}
				}
			}
			else if($step == STEP_DELETE)//データ削除
			{
				$page = new DeletePage($container);
			}
		}
		else if($pre_url[1] === '2')//リスト
		{
			$page = new ListPage($container);
		}
		else if($pre_url[1] === '3')//編集のみ
		{
			if($container->pbPageCheck === 'EditCheck')
			{	
				$page = new EditCheckPage($container);
			}
			else if($step == STEP_DELETE)
			{
				$page = new DeletePage($container);
			}	
			else
			{
				$page = new EditPage($container);
			}
				
		}	
		else if($pre_url[1] === '5')//印刷
		{
			if($filename === 'MITSUMORIPRINT_5')
			{
				$page = new MitsumoriPrintPage($container);
			}	
			else if($filename === 'SEIKYUPRINT_5')
			{
				$page = new SeikyuPrintPage($container);
			}
			else if($filename === 'URIAGEPRINT_5')
			{
				$page = new UriageListPrintPage($container);
			}
			else if($filename === 'NYUKINPRINT_5')
			{
				$page = new NyukinListPrintPage($container);
			}
			
		}
		else if($pre_url[1] === '6')//条件指定
		{
			$page = new CondisionPage($container);
		}
		else
		{
			$page = new TopPage($container);			
		}
		
		return $page;
	}
	
	/**
    * データ処理オブジェクトの生成
    * 
    * @param string $filename
    * @return BaseLogicExecuter
    */
	public function createExecuter($filename,$container)
	{
		$executer = null;
		
		
		if($container->pbPageCheck === 'Execute')//データ処理
		{
			//--特殊処理--//
			if($filename === 'SHUTAIKIN_1')
			{
				$executer = new ShutaikinExecuteSQL($container);
			}
                        else if($filename === 'CSVIMPORT_1')
                        {
                                $executer = new CsvImportExecuteSQL($container);
                        }
			
			if($executer !== null)
			{
				return $executer;
			}
			//--特殊処理--//
			
			$executer = new BaseLogicExecuter($container);
		}
		
		
		return $executer;
	}		
}
