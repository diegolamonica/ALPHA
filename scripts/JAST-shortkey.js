/*
Script Name: 	ShortKey Manager - (http://jastegg.it/eggs/shortkey/ ) 
version: 		1.0 alpha
version date:	2008-05-18
Plugin for:		JAST ( http://jastegg.it )
Dependencies:	
	XML Manager 1.1 ( http://jastegg.it/eggs/xml/ )	

--------------------------------
*/
JASTEggIt.extend('Shortkey', {
	info: {
		title: 		'ShortKey Manager',
		version:	'1.0 alpha',
		author:		'Diego La Monica',
		url:		'http://diegolamonica.info'
	},
	_keys: [],
	_clearShortcuts: function(){
		JASTEggIt.Shortkey._keys = [];
	},
	_appendShortcut: function(shortcut){
		JASTEggIt.Shortkey._keys[JASTEggIt.Shortkey._keys.length] = shortcut; 
	},
	_keyFileRead: function(){
		var doc = JASTEggIt.XML.doc;
		var nodi = doc.getElementsByTagName('filter');
		
		// TODO: devo trasformare la combinazione in codici da tastiera
		
		for(var i=0; i< nodi.length; i++){
			JASTEggIt.Shortkey._appendShortcut({
				id:				nodi[i].getAttribute('for'),
				combo: 			JASTEggIt.XML.getValue(nodi[i],'shortkey'),
				description: 	JASTEggIt.XML.getValue(nodi[i],'description'),
				eventType: 		JASTEggIt.XML.getAttribute(nodi[i],'event', 'type'),
				eventMethod: 	JASTEggIt.XML.getAttribute(nodi[i],'event', 'method'),
				eventOn:		JASTEggIt.XML.getAttribute(nodi[i],'event', 'on'),
				eventScript:	JASTEggIt.XML.getValue(nodi[i],'event')
			});
		}
	},
	_loadShortcuts: function(){
		var allHeadLinks = JASTEggIt._name('link');
		JASTEggIt.XML.onReady = JASTEggIt.Shortkey._keyFileRead;
		for(var j=0; j<allHeadLinks.length; j++){
			var link = allHeadLinks[j];
			if (link.rel=='shortcuts'){
				var xmlFile = link.getAttribute('href');
				JASTEggIt.XML.urlLoad(xmlFile);
				
			};
		}
	},
	startup: function(){
		// Load the XML Shortkey file into an array
		this._loadShortcuts();
		
		if(JASTEggIt.Browser.ie){
			document.onkeydown = JASTEggIt.Shortkey.onKeyDown;
		}else{
			JASTEggIt.Events.add(document, 'keydown', JASTEggIt.Shortkey.onKeyDown);
		}
		
	},
	onKeyDown: function(event){
		
		if(event==null) event = window.event;
		var kbd = JASTEggIt.kbd.getKeyPressed(event);
		var k = JASTEggIt.Shortkey._keys;
		var evItem = JASTEggIt.Events.generator(event);
		if(evItem){
			for(var i = 0; i<k.length; i++){
				var itm = k[i];
				if(itm.id == evItem.id || itm.id=='*'){
					var el = null;
					if(itm.eventOn!=null){
						el = JASTEggIt._id(itm.eventOn);
					}else{
						el = JASTEggIt._id(itm.id);
					}
					if((el || itm.id=='*') && JASTEggIt.Shortkey.match(event, itm.combo) ){
						if(k[i].eventType=='system' && el!=null){
							
							var evb = (itm.id=='*'?(itm.eventId!=''?el:document):el);
							
							JASTEggIt.Events.fire(evb, itm.eventMethod);
							
						}else{
							eval(k[i].eventScript);
						}
					}
				}
			}
		}
	},
	match: function(event, combo){
		
		combo = combo.toUpperCase();
		var ctrlKey = (combo.indexOf('CTRL')!=-1)||(combo.indexOf('CONTROL')!=-1);
		var shiftKey = (combo.indexOf('SHIFT')!=-1);
		var altKey = (combo.indexOf('ALT')!=-1);
		combo = combo.replace(/CTRL/g,'');
		combo = combo.replace(/CONTROL/g,'');
		combo = combo.replace(/ALT/g,'');
		combo = combo.replace(/SHIFT/g,'');
		combo = combo.replace(/\+/g,'');
		combo = combo.replace(/TAB/g,'\t');
		combo = combo.replace(/ENTER/g,'\r');
		
		combo = combo.replace(/F12/g, String.fromCharCode(123));
		combo = combo.replace(/F11/g, String.fromCharCode(122));
		combo = combo.replace(/F10/g, String.fromCharCode(121));
		combo = combo.replace(/F9/g, String.fromCharCode(120));
		combo = combo.replace(/F8/g, String.fromCharCode(119));
		combo = combo.replace(/F7/g, String.fromCharCode(118));
		combo = combo.replace(/F6/g, String.fromCharCode(117));
		combo = combo.replace(/F5/g, String.fromCharCode(116));
		combo = combo.replace(/F4/g, String.fromCharCode(115));
		combo = combo.replace(/F3/g, String.fromCharCode(114));
		combo = combo.replace(/F2/g, String.fromCharCode(113));
		combo = combo.replace(/F1/g, String.fromCharCode(112));
		
		if( 
			(event.ctrlKey == ctrlKey) &&
			(event.shiftKey == shiftKey) &&
			(event.altKey == altKey)){
				var kbd = JASTEggIt.kbd.getKeyPressed(event);
				
				return (combo.charCodeAt(0) == kbd || combo.toLowerCase().charCodeAt(0) == kbd);
				
				
			}
			 
		return false;
	},
	getAvailableShortcuts: function(){
		var k = JASTEggIt.Shortkey._keys;
		var shortcuts = [];
		for (var i = 0; i < k.length; i++) {
			var el =  JASTEggIt._id(k[i].id);
			if (el || k[i].id=='*') shortcuts[shortcuts.length] = k[i];
		}
		
		return shortcuts;
	}
	
});
