/*
Script Name: 	XML Manager (http://jastegg.it/eggs/XML ) 
version: 		1.1
version date:	2008-05-17
Plugin for:		JastEggIt ( http://jastegg.it)
--------------------------------
*/
JASTEggIt.extend('XML',{info:{title:'XML Manager',version:'1.0',eggUrl:'http://jastegg.it/eggs/XML',author:'Diego La Monica',url:'http://diegolamonica.info'},doc:null,ready:false,onReady:null,parseXML:function(buffer){if(window.ActiveXObject){var doc=new ActiveXObject("Microsoft.XMLDOM");doc.async="false";doc.loadXML(buffer);}else{var parser=new DOMParser();doc=parser.parseFromString(buffer,"text/xml");};JASTEggIt.XML.doc=doc;JASTEggIt.XML.ready=true;if(JASTEggIt.XML.onReady!=null){JASTEggIt.XML.onReady();};},urlLoad:function(url){JASTEggIt.xhttp.sendRequest('GET',url,'',this.parseXML);},getValue:function(f,n){try{return JASTEggIt._name(n,f)[0].firstChild.nodeValue;}catch(e){return null;}},getAttribute:function(f,n,a){try{return JASTEggIt._name(n,f)[0].getAttribute(a);}catch(e){return null;}}});