/**
* JAST Placeholder: JAST-placeholder.src.js
* @autor: Diego La Monica
* @version: 1.0
**/

_.extend('Placeholder', {
	options: {
		itemClass: 		'placeholder',
		subitemClass: 	'plc-item',
		hiddenClass:	'plc-hidden',
		dataClass:		'plc-data',
		focusClass:		'plc-focus',
		commitOnBlur:	true,
		onEdit:			null,
		onCommit:		null,
		onCancel:		null,
		onTextRender:	null
	},
	systemOptions: {
		fromMarker:		'plc-from-',
		actionItemId: 	'plc-actions'
	},
	placeholders: [],
	activePlaceholder: '',
	activePlaceholderData: '',
	_getThePlaceholder: function(itm, itemClass){
		var P = _.Placeholder;
		var plcs = P.placeholders;
		if(typeof(itm) ==='string') itm = _._id(itm);
		
		if(itemClass==null) itemClass = P.options.itemClass;
		
		if(plcs[itm.id]) itemClass = plcs[itm.id].itemClass;
		while(itm && !_.DOM.hasClass(itm,itemClass)){
			itm = itm.parentNode;
			if(itm){
				if(plcs[itm.id]) itemClass = plcs[itm.id].itemClass;
			}
		}
		return itm;
	},
	_getFrom: function(item){
		var theClasses = item.className;
		var j = theClasses.indexOf(_.Placeholder.systemOptions.fromMarker);
		var textItem;
		if(j!=-1){
			var textItem = theClasses.substring(j+9, theClasses.length);
			var k = textItem.indexOf(' ');
			if(k<0) k = textItem.length;
			textItem = textItem.substr(0, k);
		}
		return textItem;
	},
	_mkLink: function(parent, href, text, cls, clickFn){
		var theLink = _.DOM.createChild('a',parent);
		theLink.href=href;
		theLink.innerHTML = text //'Conferma';
		_.DOM.appendClass(theLink, cls /*'confirm'*/);
		_.Events.add(theLink, 'click', clickFn /*_.Placeholder.commit*/);
	},
	_applyValue: function(src, dst, options){
		var txt ='';
		switch(src.tagName.toUpperCase()){
			case 'IMG':
				txt = src.src;
				break;
			case 'SELECT':
				txt = src.options[src.selectedIndex].text;
				break;
			case 'INPUT':
			case 'TEXTAREA':
				txt = src.value;
		}
		var plc = _.Placeholder._getThePlaceholder(dst.id, options.itemClass);
		if(txt=='' && plc.title!='') txt = plc.title;
		if(typeof(options.onTextRender)==='function' ) txt = options.onTextRender(src.id,txt);
		dst.innerHTML = txt;
	},
	blur: function(event){
		if(_.Placeholder.holdBlur) return;
		if(_.Placeholder.activePlaceholder=='') return;
		var g=_.Events.generator(event);
		setTimeout(function(){
			if(_.Placeholder.activePlaceholder==g.id) _.Placeholder.commit(event);
		},200);
	},
	_keyUp: function(event){
		if(_.kbd.getKeyPressed(event)==27){
			var id = _.Placeholder.activePlaceholder;
			if(id=='') id = _.Events.generator(event).id;
			_.Placeholder.cancel(event, false);
			_.Placeholder.holdBlur = true;
			_._class(_.Placeholder.systemOptions.fromMarker+id)[0].focus();
			_.Placeholder.holdBlur = false;
			
		}
	},
	setup: function(id, options){
		if(_.Array.is(id)){
			
			for(var i = 0; i<id.length; i++)
				_.Placeholder.setup(id[i], options);
			return;
		}
		var P = _.Placeholder;
		var options = _.mergeOptions(options, P.options);
		var item = P._getThePlaceholder(id, options.itemClass);
		
		if(options.commitOnBlur){
			_.Events.add(id,'blur', _.Placeholder.blur);
			//_.Events.add(id,'keyup',_.Placeholder._keyUp);
		}
		var subitem = _._class(options.subitemClass, item);
		
		if(subitem.length>0){
			subitem = subitem[0];
			var D = _.DOM;
			D.appendClass(subitem, options.hiddenClass);
			
			var theSpan = D.createChild('span', item);
			theSpan.id = _.generateUniqueId('placeholder');
			P.placeholders[theSpan.id] = options;
			_.Events.add(theSpan, 'click', P.edit);
			
			var textItem = P._getFrom(item);
			if(textItem!=''){
				var textField = _._id(textItem);
				P._applyValue(textField, theSpan, options);
				D.appendClass(theSpan, options.dataClass);
				P.placeholders[textItem] = options;
			}
		}
	
	},
	
	startup: function(){
		var D = _.DOM;
		var P = _.Placeholder;
		var o = P.options;
		var ps = _._class(o.itemClass);
		for(var i= 0; i<ps.length; i++){
			var id = P._getFrom(ps[i]);
			P.setup(id, o);
		}
		var ac = D.createOnDocument('span', null, null, P.systemOptions.actionItemId);
		D.setStyle(ac, {
			display: 'none'
			});
		P._mkLink(ac, '#', 'Conferma', 'confirm', P.commit);
		P._mkLink(ac, '#', 'Annulla', 'cancel', P.cancel);
		
	},
	
	commit: function(event, fromCancel ){
		var P = _.Placeholder;
		var o = P.placeholders[P.activePlaceholder];
		if(fromCancel || !o.onCommit || o.onCommit && o.onCommit(P.activePlaceholder)){
			var plcItem  =_._id(P.activePlaceholder);
			itm = P._getThePlaceholder(plcItem);
			var plcData = _._class(o.dataClass, itm);
			plcData = plcData[0];
			
			P._applyValue(plcItem, plcData, o);
			
			_.DOM.removeClass(plcData, o.hiddenClass);
			var plcItem = _._class(o.subitemClass, itm);
			plcItem = plcItem[0];
			_.DOM.appendClass(plcItem, o.hiddenClass);
			_.DOM.removeClass(plcItem, o.focusClass);
			_.DOM.setStyle(_._id(P.systemOptions.actionItemId), {
				display: 'none'}
			);
			P.activePlaceholder = '';
			P.activePlaceholderData = '';
		}
	},
	cancel: function(event, abortEvent){
		if(abortEvent == null) abortEvent = true;
		var P = _.Placeholder;
		var ap = P.activePlaceholder;
		if(ap!=''){
			var o = P.placeholders[ap];
			if(!o.onCancel || o.onCancel && o.onCancel(ap)){
				_._id(ap).value = P.activePlaceholderText;
				P.commit(event, true);
				if(abortEvent) _.Events.abort(event);
			}
		}
		
	},
	edit: function(event){
		var P = _.Placeholder;
		var D = _.DOM;
		if(event==null) event = window.event;
		var itm = P._getThePlaceholder( _.Events.generator(event) );
		if(itm){
			var textItem = P._getFrom(itm);
			// document.title = textItem;
			var o = P.placeholders[textItem];
			if(_._class(o.focusClass, itm).length==0){
			
				if(!o.onEdit || o.onEdit && o.onEdit(textItem)){
				
					if(textItem == P.activePlaceholder) return false;
					if(P.activePlaceholder!='') P.commit();
					
					
					var plcHidden = _._class(o.hiddenClass, itm);
					var plcData = _._class(o.dataClass, itm);
					plcData = plcData[0];
					D.appendClass(plcData, o.hiddenClass);
					
					D.removeClass(plcHidden, o.hiddenClass);
					D.appendClass(plcHidden, o.focusClass);
					
					var textItem = P._getFrom(itm);
					
					if(textItem!=-''){
						var textField = _._id(textItem);
						textField.focus();
						P.activePlaceholderText = textField.value;
						var pos = D.position(textField);
						var sz  = D.realSize(textField);
						var ac = _._id(P.systemOptions.actionItemId);
						if(ac){
							D.setStyle(ac, {
								position: 	'absolute',
								display:	'block',
								left:	  	(pos.x+sz.width-70) +'px',
								top:		(pos.y + sz.height+1) +'px'
								
							});
						}
						P.activePlaceholder = textItem;
						_.Events.abort(event);
					}
				}
			}
		}else{
			if(P.activePlaceholder!='') P.commit();
		}
	},
	refresh: function(id){
		_.Placeholder.activePlaceholder = id;
		_.Placeholder.commit();
		return true;
	}
});