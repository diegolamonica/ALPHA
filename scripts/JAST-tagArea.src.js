/**
 * @author Diego
 */
JASTEggIt.extend('tagArea', {
	items:	[],
	
	options: {
		itemFormatter: function(itemString){ return itemString },
		itemRemover:	'[x]',
		itemAddText:	'add new',
		divId:			'',
		suggestUrl:		''
	},
	
	setup: function(id, options){
		var div_id = JASTEggIt.generateUniqueId(id+'-tagarea');
		options = JASTEggIt.mergeOptions(options, this.options);
		options.divId = div_id;
		this.items[id] = options;

		var size = JASTEggIt.DOM.realSize(id);
		var div = JASTEggIt.DOM.createOnDocument('div', null, id, div_id);
		JASTEggIt.DOM.setStyle(div, {width: size.width + 'px', minHeight: size.height+ 'px'});
		JASTEggIt.DOM.setStyle(id, {display: 'none'});
		
		var buffer = JASTEggIt._id(id).value;
		this.format(id,buffer);
		
	},
	format: function(id, buffer){
		var o = JASTEggIt.tagArea.items[id];
		if (buffer !=''){
			
			var items = JASTEggIt.strings.split(buffer,',');
			var buffer = '';
			for(var i=0; i<items.length; i++ ){
				items[i] = JASTEggIt.strings.trim(items[i]);
				
				buffer += '<div class="tag-item">'
				if(JASTEggIt.Browser.ie) buffer +='<nobr>';
				
				buffer += o.itemFormatter([items[i]]);
				buffer += '<a href="javascript:JASTEggIt.tagArea.removeItem(\''+id+'\',\'' + escape(escape(items[i])) + '\');">';
				buffer += o.itemRemover + '</a>';
				
				if(JASTEggIt.Browser.ie) buffer +='</nobr>';
				buffer += '</div>';	
				
			}
		}
		
		buffer+='<div id="'+ id + '-addItemActivator" class="tag-item" style="cursor: pointer;" onclick="javascript:JASTEggIt.tagArea.addItem(\''+ id + '\')">'
		if(JASTEggIt.Browser.ie) buffer +='<nobr>';
		buffer+= o.itemAddText 
		if(JASTEggIt.Browser.ie) buffer +='</nobr>';
		buffer+='</div>';
		buffer+='<input type="text" class="tag-item" style="display:none;" id="' + id +'-addItem" value="" />';
		buffer+='<span style="display: block; clear: both;">&nbsp;</span>';
		JASTEggIt._id(o.divId).innerHTML = buffer;
		JASTEggIt.autocomplete.setup(id+'-addItem', {
			source:		'page',
			outputId: 	'tag-list',
			page:		o.suggestUrl,
			minChar:	3,
			separator:	'',
			formatOutput: o.itemFormatter,
			onConfirm:	function(idx){
				var buffer = JASTEggIt._id(id).value;
				if(buffer!='') buffer += ', ';
				buffer += JASTEggIt._id(idx).value;
				JASTEggIt._id(id).value = buffer;
				JASTEggIt.tagArea.format(id, buffer);
			}
		});
		
		
	},
	addItem: function(id){
		JASTEggIt.DOM.setStyle(id+'-addItemActivator', {display:'none'});
		JASTEggIt.DOM.setStyle(id+'-addItem', {display:'block'});
		_._id(id+'-addItem').focus();
	},
	removeItem: function(id, item){
		var buffer = _._id(id).value;
		var items = JASTEggIt.strings.split(buffer,',');
		var o = JASTEggIt.tagArea.items[id];
		item = unescape(item);
		buffer  ='';
		for (var i = 0; i < items.length; i++) {
			items[i] = JASTEggIt.strings.trim(items[i]);
			if(items[i]!= item){
				if(buffer != '') buffer += ', ';
				buffer += items[i]; 
			}
		}
		_._id(id).value = buffer; 
		JASTEggIt.tagArea.format(id,buffer);
	}
	
});
