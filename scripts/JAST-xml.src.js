/*
Script Name: 	XML Manager (http://jastegg.it/eggs/XML ) 
version: 		1.1
version date:	2008-05-17
Plugin for:		JastEggIt ( http://jastegg.it)
--------------------------------
*/
JASTEggIt.extend(
	'XML', {
		info: {
			title: 		'XML Manager',
			version:	'1.0',
			eggUrl:		'http://jastegg.it/eggs/XML',
			author:		'Diego La Monica',
			url:		'http://diegolamonica.info'
		},
		id: null,
		doc: null,
		ready: false,
		onReady: null,
		parseXML: function(buffer, _this){
			if (window.ActiveXObject){
                _this.doc= new ActiveXObject("Microsoft.XMLDOM");
                _this.doc.async=false;
                _this.doc.loadXML(buffer);
	        }else{
                var parser = new DOMParser();
                _this.doc = parser.parseFromString(buffer,"text/xml");
	        };
			_this.ready = true;
			if(_this.onReady!=null)	_this.onReady(_this);
		},
		urlLoad: function(url){
			JASTEggIt.xhttp.get(url, '', this.parseXML, this );
		},
		getNode:  function(nodeName, from){
			var nodes = this.getNodes(nodeName, from);
			return (nodes!=null)?nodes[0]:null;
		},
		getNodes: function(nodeName, from){
			if(from==null) from = this.doc;
			try{
				return JASTEggIt._name(nodeName,from);
			}catch(e){
				return null;
			}
		},
		getValue: function(from, nodeName){
			try{
				return this.getNode(nodeName, from).firstChild.nodeValue;	
			}catch(e){
				return null;
			}
		},
		getAttribute: function(from, nodeName, attribute){
			try{
				return JASTEggIt._name(nodeName,from)[0].getAttribute(attribute);
			}catch(e){
				return null;
			}
		}		
	}
 );