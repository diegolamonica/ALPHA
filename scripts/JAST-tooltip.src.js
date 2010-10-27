/*
Script Name: 	Tooltip Beautifier (http://jastegg.it/eggs/tooltips/ )
Author:			Diego La Monica 
version: 		1.3.0 beta
version date:	2009-08-17
Plugin for:		JAST ( http://jastegg.it )
--------------------------------
*/
_.extend('Tooltips', {
	info: {
		title:		'Tooltip Text beautifier',
		version:	'1.3.0',
		author:		'Diego La Monica',
		url:		'http://www.diegolamonica.info'
	},
	options:	{
		HTML:			'%text%',
		style:			null,
		offsetX:		0,
		offsetY:		10
	},
	_id: null,
	_tooltipId: null,
	_tooltipText: [],
	_getScrollTop: function(){
		if(document.documentElement && document.documentElement.scrollTop) return document.documentElement.scrollTop;
		return document.body.scrollTop;
	},
	_getScrollLeft: function(){
		if(document.documentElement && document.documentElement.scrollLeft) return document.documentElement.scrollLeft;
		return document.body.scrollLeft;
	},
	setup: function(e, title){
		e = JASTEggIt._el(e);
		if (e== null) return;
		if((e.title != null && e.title != '') || title !=null){
			if(title==null) title = e.title;
			if(e.id=='' || e.id==null) e.id = JASTEggIt.generateUniqueId('tooltip');

			// JASTEggIt.Events.add(e, 'mousemove', JASTEggIt.Tooltips.displayTooltip);
			// JASTEggIt.Events.add(e, 'mouseout', JASTEggIt.Tooltips.hideTooltip);
			if (this._tooltipText[e.id] == null) {
				this._tooltipText[e.id] = title;
				e.removeAttribute("title");
			}

		}
	},
	startup: function(){
		var a = JASTEggIt._name('*');
		for(i =0; i < a.length; i++){
			JASTEggIt.Tooltips.setup(a[i]);
		}
		JASTEggIt.Events.add(document, 'mousemove', JASTEggIt.Tooltips.displayTooltip);
		//JASTEggIt.Tooltips.setup(document);
		if(_.Browser.ie && JASTEggIt.Carousel ||  JASTEggIt.rssReader){
			// Aggiunge un evento sull'onload per un piccolo problema quando c'è uno script nella pagina:
			// JAST forza la sua creazione quindi è pronto prima che la 
			// pagina sia totalmente creata.
			_.Events.add(window, 'load', _.Tooltips.startup);
		}
		
		_.Tooltips.createTooltip();
	},
	
	
	createTooltip: function(){
		var j = JASTEggIt;
		var jt = j.Tooltips;
		if(jt._tooltipId==null){
			var div = j.DOM.createOnDocument('DIV');
			//div.innerHTML = '';
			jt._tooltipId = div.id;
			jt.hideTooltip();
		}
	},
	
	displayTooltip: function(event){
		var j = JASTEggIt;
		var jt = j.Tooltips;
		var jto = jt.options;
		var jtt = jt._tooltipText;
		var obj = j.Events.generator(event);
		var tooltip = j._id(jt._tooltipId);
		if(obj.id!=null && obj.id == jt._id) return;
		if(obj.title!='' || (obj.id != null && obj.id!='' && jtt[obj.id]) ){
			if(jt._id!=null) jt.hideTooltip();
			jt._id=obj.id;
			if(obj.title!=null && obj.title!=''){
				jtt[obj.id] = obj.title;
				obj.title ='';
			}
			tooltip.innerHTML = jto.HTML.replace('%text%',jtt[obj.id]);
			if(jto.style) j.DOM.setStyle(jt._tooltipId, jto.style);
			
			var c = obj.className;
			
			c = c.split(' ');
			var o = new Object();
			o.x = jto.offsetX;
			o.y = jto.offsetY;
			
			for(var i = 0; i<c.length; i++){
				if(c[i].substring(0,5)=='jast_'){
					var props = c[i].split('_');
					o[props[1]] = props[2];
				}
			}
			
			_.DOM.setStyle(jt._tooltipId, {
				position: 'absolute',
				display: '',
				left: (event.clientX + parseInt(o.x) + jt._getScrollLeft()) + 'px',
				top: (event.clientY + parseInt(o.y) + jt._getScrollTop()) + 'px'
			});
			
		}else{
			jt.hideTooltip();

		}
	},

	hideTooltip: function(){

		var j = JASTEggIt;
		var jt = j.Tooltips;
		if(jt._id!=null){
			j.DOM.setStyle(jt._tooltipId, { display: 'none' });
			JASTEggIt.Tooltips._id=null;
		}
	}
});
