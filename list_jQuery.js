
function updateAutocomplete(ctrlname,identifier)
{
	var ctrlname_show = ctrlname+"_show";

	$(ctrlname_show).autocomplete(
	{
		source    : function(request, response){
			$.ajax(
			{
				url: 'json.php',
				scriptCharset: 'utf-8',
				type: 'GET',
				data:{
						key:'',
						search:$(ctrlname_show).val(),
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
				var _CODE = ui.item.code;
				$(ctrlname).val(_CODE);
			}
		},
		change: function (event, ui) {
			if (!ui.item) {
				$(ctrlname).val('');
			}
		}
	}).focus(function() {
		$(this).autocomplete('search', '');
	});
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
						arrayData[counter] = hashData;
						counter++;
					});
					response(arrayData);
				}
			});
		},
		autoFocus : true,
		delay     : 100,
		minLength : 0

	}).focus(function() {
		$(this).autocomplete('search', '');
	});
}

function updateShowValue(ctrlname,identifier)
{
	var ctrlname_show = ctrlname+"_show";

	$.ajax(
	{
		url: 'json.php',
		scriptCharset: 'utf-8',
		type: 'GET',
		data:{
				key:$(ctrlname).val(),
				search:'',
				table_id: identifier								
			 },
		dataType: 'json',
		timeout: 5000,
		cache: false,

		success: function(data)
		{
			var dataArray = data.results;

			$.each(dataArray, function(i)
			{
				$(ctrlname_show).val(dataArray[i].VALUE);
			});
		}

	});
}

function updateAutocompleteByID(ctrl_auto,ctrl_id,identifier)
{
	$(ctrl_auto).autocomplete(
	{
		source : function(request, response){
			$.ajax(
			{
				url: 'json.php',
				scriptCharset: 'utf-8',
				type: 'GET',
				data:{
						key:$(ctrl_id).val(),
						search:'',
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
		}
	}).focus(function() {
		$(this).autocomplete('search', '');
	});
}
