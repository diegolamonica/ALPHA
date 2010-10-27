/*
Script Name: 	Ajax Page Loader (http://JastEgg.it/eggs/apl ) 
version: 		1.2.0
version date:	2007-05-31
Plugin for:		JAST ( http://JastEgg.it )
--------------------------------
*/

var APL_ALL_LINKS 	= '@links';
var APL_PARENT_NODE = '@parent';

JASTEggIt.extend(
	'apl', {
		info: {
			title: 		'AJAX Page Loader',
			version:	'1.1.0',
			eggUrl:		'http://jastegg.it/eggs/apl',
			author:		'Diego La Monica',
			url:		'http://diegolamonica.info'
		},
		linkIsAccepted: function(id, url){ return true;},
		options: {
			targetElementId:		'out',
			extraParameters:		{ /* default same parameters */ },
			postProcessFunction:	 null,
			preProcessFunction:		null,
			formatFunction:			null
		},
		_setupLink: function(options){
			var els = JASTEggIt._name('a');
			options = JASTEggIt.mergeOptions(options, this.options);
			for(var i = 0; i<els.length; i++){
				var el = els[i];
				if(el.href.protocol!='javascript:' && el.href.protocol!='mailto:'){
					if(el.id==null || el.id=='') el.id = JASTEggIt.generateUniqueId('apl' + i );
					if(this.linkIsAccepted(el.id, el.href)){
						eval("el.onkeypress = function(event){ return JASTEggIt.apl.onKeyPressEvent(this.id,event);}");
						eval("el.onclick = function (){  return JASTEggIt.apl.openLink(this.id); }; ");
						el.options = options;
					}
				}
			}
		},
		_setup: function(id, options){
			var el = JASTEggIt._el(id);
			var tagName = el.tagName.toUpperCase();
			if(tagName=='A'){
				if(el.id==null || el.id=='') el.id = JASTEggIt.generateUniqueId('apl' );
				eval("el.onkeypress = function(event){ return JASTEggIt.apl.onKeyPressEvent(this.id,event);}");
				eval("el.onclick = function (){  return JASTEggIt.apl.openLink(this.id); }; ");
			};
			options = JASTEggIt.mergeOptions(options, this.options);
			el.options = options;
		},
		setup: function(id, options){
			if(typeof(id) != 'string'){
				for(var i=0; i<id.length; i++){
					this._setup(id[i], options);
				}
			}else{
				if(id==APL_ALL_LINKS){
					this._setupLink(options);
				}else{
					this._setup(id, options);
				}
			}
		},
		onKeyPressEvent:  function(elementid,event){
			var keynum = JASTEggIt.kbd.getKeyPressed(event);
			if(keynum == 13 || keynum==32){
				this.openLink(elementid);
				return false;
			}
		},
		openLink:	function(elementid){
			var link = JASTEggIt._id(elementid);
			var url = link.href;
			// Bugfix doppia richiesta
			var fnString = 'JASTEggIt.apl.replaceContents(%%BUFFER%%, \'' + link.options.targetElementId + '\',\'' + elementid + '\')';
			JASTEggIt.xhttp.sendRequest('GET', url, link.options.extraParameters, fnString );
			return false;
		},
		replaceContents: function(b, l, elid){
			if(l==APL_PARENT_NODE){
				var app = JASTEggIt._id(elid);
				app = app.parentNode;
			}else{
				var app = JASTEggIt._id(l);
			};
			var lnk = JASTEggIt._id(elid);

			if (lnk==null) return;
			var o = lnk.options;
			if(o.preProcessFunction != null){
				o.preProcessFunction();
			}
			if(o.formatFunction) b = o.formatFunction(b);
			if(b.substr(0,15) == '<' + '!-- append --' + '>'){
				app.innerHTML = app.innerHTML + b;
				return false;	
			}
			try{
				if(app.tagName!=null && app.tagName.toLowerCase() == 'input'){
					app.value = b;
				}else
					if(app.tagName!=null && app.tagName.toLowerCase() == 'select' && app.outerHTML != null){
						var i = app.outerHTML.indexOf('></');
						var tmpb = app.outerHTML.substr(0, i+1);
						app.outerHTML = tmpb + b + '</select>';
					}else{
						app.innerHTML = b;		
					}
				if(o.postProcessFunction != null){
					o.postProcessFunction();
				}
			}catch(e){
				alert('errrore: ' +  e.description );
			}
		}
	}
);