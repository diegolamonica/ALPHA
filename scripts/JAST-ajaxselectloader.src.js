/*
Script Name: 	Ajax Select Loader - (http://JastEgg.it/eggs/asl ) 
version: 		1.3
version date:	2008-05-23
Plugin for:		JAST ( http://jastegg.it)
--------------------------------
*/
_.extend(
	'AjaxSelectLoader', {
		info: {
			title: 		'AJAX Select Loader',
			version:	'1.3',
			eggUrl:		'http://jastegg.it/eggs/asl',
			author:		'Diego La Monica',
			url:		'http://diegolamonica.info'
		},
		options:	{
			pageURL: 		'/test/testo.json',
			loadText:		'Caricamento in corso...',
			method:			'GET',
			destination:	null,
			table:			'',
			fields:			'',
			filter:			'',
			startValue:		'',
			glue:			' ',
			textIfEmpty:	null,
			valueIfEmpty:	null,
			onFill:			null,
			loadFirst:		false
		},
		_asl: [],
		_loadSelect: function(sId, selectId, table, fields, filter, value){
			
			var params = {
					tbl:	table,
					flds:	fields,
					wfld:	filter,
					wval:	value	
			};
			if(_.Array.is(filter)){
				for(var i = 0; i<filter.length; i++){
					params['wfld' + (i+1)] =  escape(filter[i].name);
					params['wval' + (i+1)] =  escape(filter[i].value);
				}				
			}
			
			var sel = _._id(selectId);
			sel.options.length = 1;
			var len = 0;

			var options = _.AjaxSelectLoader._asl[sId];
			
			sel.options[len].text = options.loadText;
			sel.options[len].value = '';

			_.xhttp.sendRequest(options.method, options.pageURL, params, function(buffer){
				_.AjaxSelectLoader._fillSelect(selectId,sId,buffer);
			});
		},
		_fillSelect:	function (selId, srcId, json){
			var o = _.AjaxSelectLoader._asl[srcId];
			fireEvent = false;
			var obj = eval(json);
			var sel = _._id(selId);
			sel.options.length = 0;
			if(obj==null) obj = [];
			var items = sel.options;
			if(obj.length>0 || o.textIfEmpty){
				var len = items.length++;
				items[len].text = '';
				items[len].value = ''; 					
			}
			if(o.textIfEmpty && obj.length==0){
				len = items.length++;
				items[len].text = o.textIfEmpty;
				items[len].value = o.valueIfEmpty;
			}
			for(i = 0; i< obj.length; i++){
				var item = obj[i];
				len = items.length++;
				var text = '';

				for(var j=1; j<item.length; j++){
					if(text != '') text += o.glue; 
					text += item[j];
				}
				items[len].text = text;
				items[len].value = item[0]; 
				if(o.startValue == item[0] && (o.defaultValue == _._id(srcId).value || o.defaultValue==null)){
					items[len].selected=true;
					o.defaultValue = _._id(srcId).value; 
					fireEvent = true;
				}
			};
			if(fireEvent) _.Events.fire(selId,'change');
			if(o.onFill) o.onFill(selId, srcId);
			
		},
		_change: function(event){
			
			if(event == null) event = window.event;
			var g = _.Events.generator(event);
			var id = g.getAttribute('id');
			var v = g.value;
			
			for(var aslId in _.AjaxSelectLoader._asl){
				var asl = _.AjaxSelectLoader._asl[aslId];
				if(asl.destination==id){
					if(asl.defaultValue==_._id(aslId).value){
					}else if(asl.defaultValue){
						asl.defaultValue = _._id(aslId).value;
					}
					asl.startValue = g.value;
					break;
				}	
				
			}
			
			var options = _.AjaxSelectLoader._asl[id];
			
			if(options){
				
				var filter = _.Array.is(options.filter)?options.filter:'';

				_.AjaxSelectLoader._loadSelect(id, 
						options.destination, 
						options.table, 
						options.fields,
						filter,
						v);
			}
			
		},
		setup: function(id, options){
			var o = _.Objects.clone(_.mergeOptions(options, this.options));
			if(o.destination==null) o.destination = id; 

			_.AjaxSelectLoader._asl[id] = o;
			_.Events.add(id, 'change', _.AjaxSelectLoader._change);
			if(o.loadFirst) _.Events.fire(id,'change');
			if(o.destination!=id){
				_.Events.add(o.destination, 'change', _.AjaxSelectLoader._change);
				
			}	
			
		}
	}
);