// ver 1.0 2018/11/21

var HINMEI_NAME = '';//品目
var TANKA_NAME = '';//単価
var SURYO_NAME = '';//数量
var TANNI_NAME = '';//単位
var ZEIRISTU_NAME = '';//税率
var KINGAKU_NAME = '';//金額項目
var KINGAKUKEI_NAME = '';//合計金額
var ZEI_NAME = '';//税額

//AutoCompleteの制御
function updateAutocompleteHIMValue(ctrlname,identifier,poststr)
{
	$(ctrlname).autocomplete(
	{
		source    : function(request, response){
			$.ajax(
			{
				url: 'json.php',
				scriptCharset: 'utf-8',
				type: 'GET',
				data:{
						key:'',
						search:$(ctrlname).val(),
						table_id: identifier								
					 },
				dataType: 'json',
				timeout: 5000,
				cache: false,

				success: function(data)
				{
					var dataArray = data.results;
					var arrayData =[];
					var counter = 0;

					$.each(dataArray, function(i)
					{
						var hashData = {};
						hashData['label'] = dataArray[i].LABEL;
						hashData['value'] = dataArray[i].VALUE;
						hashData['code'] = dataArray[i].KEY;
						hashData['TANNI'] = dataArray[i].TANNI;
						hashData['TANKA'] = dataArray[i].TANKA;
						hashData['ZEI'] = dataArray[i].ZEIRITSU;

						arrayData[counter] = hashData;
						counter++;
					});

					response(arrayData);
				}

			});
		},
		autoFocus : true,
		delay     : 100,
		minLength : 0,
		
		select : function(e, ui)
		{
			if(ui.item)
			{
				$('#'+TANKA_NAME+poststr).val(ui.item.TANKA);
				$('#'+TANNI_NAME+poststr).val(ui.item.TANNI);
				$('#'+ZEIRISTU_NAME+poststr).val(ui.item.ZEI);
			}
		}

	}).focus(function() {
		$(this).autocomplete('search', '');
	});

}

//金額の計算
function calculateKingaku( poststr )
{
	//値の取得
	var tanka = $('#'+TANKA_NAME+poststr).val();
	var suryo = $('#'+SURYO_NAME+poststr).val();
	var kingaku;        
	var kingaku_zeibetsu = { 0:0, 8:0, 10:0 };//コピー用

	if(tanka !== '' && suryo !== ''){
		//計算
		kingaku = tanka * suryo;
		//金額セット
		$('#'+KINGAKU_NAME+poststr).val(kingaku); 
	}

	//合計金額計算
	var goukei = 0;
	for (var i = 0; i < 15; i++ ){

		kingaku = $('#'+KINGAKU_NAME+'_0_'+i).val();
		var zei = $('#'+ZEIRISTU_NAME+'_0_'+i).val();

		if(kingaku == '')	{
			kingaku = 0;
		}    
		goukei = goukei + parseInt(kingaku);
		kingaku_zeibetsu[zei] = kingaku_zeibetsu[zei] + parseInt(kingaku);
	}
	//合計金額セット
	$('#'+KINGAKUKEI_NAME+'_0').val(goukei);
	//消費税
	var zei_goukei = kingaku_zeibetsu[8]*0.08+kingaku_zeibetsu[10]*0.1;
	$('#'+ZEI_NAME+'_0').val(zei_goukei);
}

//行操作
var row_copy = { HINMEI:'', TANKA:'', SURYO:'', TANNI:'', ZEIRISTU:'0', KINGAKU:'' };//コピー用
var row_init = { HINMEI:'', TANKA:'', SURYO:'', TANNI:'', ZEIRISTU:'0', KINGAKU:'' };//クリア用
//行のデータを連想配列に入れて返す
function getRowData(pos){
	var row = {};
	row.HINMEI = $('#'+HINMEI_NAME+'_0_'+pos).val();
	row.TANKA =  $('#'+TANKA_NAME+'_0_'+pos).val();
	row.SURYO =  $('#'+SURYO_NAME+'_0_'+pos).val();
	row.TANNI =  $('#'+TANNI_NAME+'_0_'+pos).val();
	row.ZEIRISTU =  $('#'+ZEIRISTU_NAME+'_0_'+pos).val();
	row.KINGAKU =  $('#'+KINGAKU_NAME+'_0_'+pos).val();	
	
	return row;
}
//指定行に連想配列のデータをセットする
function setRowData(pos,row){
	$('#'+HINMEI_NAME+'_0_'+pos).val(row.HINMEI );
	$('#'+TANKA_NAME+'_0_'+pos).val( row.TANKA );
	$('#'+SURYO_NAME+'_0_'+pos).val( row.SURYO );
	$('#'+TANNI_NAME+'_0_'+pos).val( row.TANNI );
	$('#'+ZEIRISTU_NAME+'_0_'+pos).val( row.ZEIRISTU );
	$('#'+KINGAKU_NAME+'_0_'+pos).val( row.KINGAKU );	
}
//指定行をグローバル変数にコピーする
function copyRow( pos ){
	row_copy = getRowData(pos);
}
//指定行にグローバル変数のデータをコピーする
function pasteRow( pos ){
	setRowData(pos,row_copy);
	calculateKingaku( '_0_'+String(pos) );
}
//指定箇所に空白行を挿入する
function insertRow( pos ){
	var pos_copy_to=14;
	for( ; pos_copy_to > pos; pos_copy_to-- )	{	//下から順に指定行まで処理
		var row = getRowData(pos_copy_to-1);
		setRowData(pos_copy_to,row);
	}
	setRowData(pos,row_init);	//指定行は空白にする
	calculateKingaku( '_0_'+String(pos) );
}
//指定箇所の行を削除してつめる
function removeRow( pos ){
	var pos_copy_to = pos;
	for( ; pos_copy_to < 14; pos_copy_to++ )	{	//上から順に14行目まで処理
		var row = getRowData(pos_copy_to+1);
		setRowData(pos_copy_to,row);
	}
	setRowData(pos_copy_to,row_init);	//15行目は空白にする
	calculateKingaku( '_0_'+String(pos) );
}

