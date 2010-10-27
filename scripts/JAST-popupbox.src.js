/*
Script Name: 	PopupBox - (http://jastegg.it/eggs/PopupBox/ ) 
version: 		1.0.13 beta
version date:	2008-06-11
Plugin for:		JAST ( http://jastegg.it )
--------------------------------
*/
var POPUP_CENTER 	= 'center';
var POPUP_LEFT		= 'left';
var POPUP_RIGHT		= 'right';
var POPUP_TOP		= 'top';
var POPUP_BOTTOM 	= 'bottom';
JASTEggIt.extend('PopupBox', {

	options: {
		backgroundColor: 	'#000',
		backgroundOpacity: 	0.5,
		zIndex:				1,
		position: 	{x:POPUP_CENTER, y: POPUP_CENTER},

		cancel:	{
				allow:		false,
				label:		'Cancel',
				className:	'button',
				onClick:	null		
			},
		confirm:	{
				allow:		false,
				label:		'Ok',
				className:	'button',
				onClick:	null		
			},
		buttonContainer: 			null,
		buttonContainerClassName:	null
	},
	
	setup: function (id,options){
		options = JASTEggIt.mergeOptions(options, this.options);
		options.elementId = id;

		var el = JASTEggIt._id(options.elementId);
		JASTEggIt.DOM.setStyle(el,
			{
				display: 'none',
				position: 'absolute',
				zIndex:		options.zIndex+1,
				left:		'0px',
				top:		'0px',
				border:		'1px solid black',
				backgroundColor:	'#fff'
			}
		);

		// Devo aggiungere forse il pulsante di confirm e di cancel
		if((options.cancel.allow || options.confirm.allow) && options.buttonContainer){
			var bc= JASTEggIt.DOM.createChild(options.buttonContainer, el);
			if(options.buttonContainerClassName)
				JASTEggIt.DOM.appendClass(bc, options.buttonContainerClassName);
			
		}else{
			bc = el;
		}
				
		if(options.cancel.allow){
			JASTEggIt.Events.add(document, 'keydown', function(event){
				if(event==null) event = window.event;
				if(_.DOM.style(id, 'display')['display']!='none'){
					if(event.keyCode==27){
						JASTEggIt.PopupBox.cancel(id);
					}
				}
			});
			this._createButton(bc, options.elementId, options.cancel.label, options.cancel.className,'cancel');
		}
		if(options.confirm.allow){
			this._createButton(bc, options.elementId, options.confirm.label, options.confirm.className,'confirm');
		}

		el.options = options;
	},
	
	cancel: function(id){
		var options = JASTEggIt._id(id).options;
		
		if(options.cancel.onClick){
			if(!options.cancel.onClick(id)) return false;
		}			
		JASTEggIt.DOM.setStyle(id,{ display: 'none' } );
		JASTEggIt.DOM.setStyle(options.shadowElementId,{ display: 'none' } );
	},
	
	confirm: function(id){
		var options = JASTEggIt._id(id).options;
		
		if(options.confirm.onClick){
			if(!options.confirm.onClick(id)) return;
		}
		JASTEggIt.DOM.setStyle(id,{ display: 'none' } );
		JASTEggIt.DOM.setStyle(options.shadowElementId,{ display: 'none' } );
	},
	
	display: function(id){
		var options = JASTEggIt._id(id).options;
		
		if(options.onBeforeDisplay){
			var ret = options.onBeforeDisplay(id);
			if(ret!=null && ret == false) return false;
		}
		
		if(options.shadowElementId==null)
			options.shadowElementId=this._createShadow(options.backgroundColor, options.backgroundOpacity, options.zIndex);
		else
			JASTEggIt.DOM.setStyle(options.shadowElementId,{ display: 'block' } );		
		JASTEggIt.DOM.setStyle(id,{ display: 'block' } );
		this._locatePopup(id);
		if(options.onAfterDisplay) options.onAfterDisplay(id);
	},
	hide: function(id){
		var options = JASTEggIt._id(id).options;
		JASTEggIt.DOM.setStyle(options.shadowElementId,{ display: 'none' } );		
		JASTEggIt.DOM.setStyle(id,{ display: 'none' } );
	},
	_getScrollTop: function(){
		if(document.documentElement && document.documentElement.scrollTop) return document.documentElement.scrollTop;
		return document.body.scrollTop;
	},
	_getWindowHeight: function () {
		myHeight = 0;
		if( typeof( window.innerWidth ) == 'number' ) {
			//Non-IE
			myHeight = window.innerHeight;
		} else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
			//IE 6+ in 'standards compliant mode'
			myHeight = document.documentElement.clientHeight;
		} else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
			//IE 4 compatible
			myHeight = document.body.clientHeight;
		}
		return myHeight;
	},
	_locatePopup: function(id){
		var el =_._id(id);
		options = el.options;
		var sz = JASTEggIt.DOM.realSize(el);
		var ww = 0;
		if (window.innerWidth){
			ww = window.innerWidth;
		}else if (document.body.clientWidth){
			ww = document.body.clientWidth;
		}else if (document.documentElement.clientWidth){
			ww = document.documentElement.clientWidth;
		}
		var wh = this._getWindowHeight();
		var x = options.position.x;
		var y = options.position.y;
		if(options.position.x==POPUP_LEFT) x = 0;
		if(options.position.x==POPUP_RIGHT) x =  ww - sz.width ;
		if(options.position.x==POPUP_CENTER) x =  (ww - sz.width)/2;
		
		if(options.position.y==POPUP_TOP) y = 0;
		if(options.position.y==POPUP_BOTTOM) y =  wh - sz.height;
		if(options.position.y==POPUP_CENTER) y =  (wh - sz.height)/2;

		y+=this._getScrollTop();

		JASTEggIt.DOM.setStyle(el,
			{
				left:		x + 'px',
				top:		y + 'px'
			}
		);

	},
	_createShadow: function(backgroundColor, backgroundOpacity, zIndex){
		var div = JASTEggIt.DOM.createOnDocument('span');
		var docHeight;
		var h = JASTEggIt.PopupBox._getWindowHeight();
		var el = null;
		if(window.scrollMaxY!=null){
			el = window;
		}else{
			var body = JASTEggIt._name('body');
			body = body[0];
			if(body.scrollMaxY!=null)el = body;
		}
		if(el!=null) h+= el.scrollMaxY;
		JASTEggIt.DOM.setStyle(div,
			{
				position: ((JASTEggIt.Browser.ie && JASTEggIt.Browser.version=='6')?'absolute':'fixed'),
				width: 		'100%',
				height:		h + 'px',
				left:		'0px',
				top:		'0px',
				margin:		'0px',
				padding:	'0px',
				backgroundColor: backgroundColor,
				display:	'block',
				zIndex:		zIndex
			}
			
		);
		if(JASTEggIt.Browser.ie){
			JASTEggIt.DOM.setStyle(div, {	filter:	'alpha(opacity: ' + (backgroundOpacity * 100) + ')' }	);
		}else{
			JASTEggIt.DOM.setStyle(div, {	opacity:	backgroundOpacity } );
		}
		return div.id;
	},
	_createButton: function(bc, id, label, className, functionName){
		var lnk = JASTEggIt.DOM.createChild('a', bc);
		lnk.href='javascript:JASTEggIt.PopupBox.'+functionName+'("'+ id +'")';
		lnk.innerHTML = label;
		if(JASTEggIt.Array.is(className)){
			for(var j=0; j<className.length; j++){
				JASTEggIt.DOM.appendClass(lnk,className[j]);
			}
		}else
			JASTEggIt.DOM.appendClass(lnk,className);
		
	}
	
})