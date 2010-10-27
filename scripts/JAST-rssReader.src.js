/**
 * @author Diego
 */
JASTEggIt.extend('rssReader', {
	
	options:{
		
		width: 				'auto',
		height: 			'auto',
		contentsId:			null,
		titleId:			null,
		navigatorId:		null,	
		rssSource:			null,
		hideIfEmpty:		false,
		itemsPerPage:		9999,
		displayRssIcon:		true,
		displayItemTitle:	true,
		displayItemDescription:	true,
		startPage:			1,
		previousTitle:		'',
		nextTitle:			'',
		extraFunctions:		[],
		readyContents:		null
	},
	_rss: [],
	readyContents: function(id){
		var rss = JASTEggIt.rssReader._rss[id];
		var xml = rss._xmlDocument;
		var items = xml.getNodes('item'); 
		if(rss.readyContents) rss.readyContents(id, items.length);
	},
	_setup: function(id, options){
		options = JASTEggIt.mergeOptions(options, this.options);
		this.reload(id);
	},
	startup: function(){
		for(id in this._rss)
			this._setup(id, this._rss[id]);
	},
	setup: function(id, options){
		if(options == null) options = this.options;
		this._rss[id] = JASTEggIt.Objects.clone(options);
		if(JASTEggIt._ready) this._setup(id, this._rss[id]);
	},
	
	xmlReadyState: function(_this){
		var id = _this.id;

		JASTEggIt.rssReader._rss[id].xml;
		var rss = JASTEggIt.rssReader._rss[id];
		
		JASTEggIt.rssReader.readTitle(id, _this);
		JASTEggIt.rssReader.readPage(id, rss.startPage, _this );
		JASTEggIt.rssReader.readyContents(id);
	},
	
	reload: function(id, url){
		var rss = this._rss[id];
		if(url!=null) rss.rssSource = url;
		var xml = JASTEggIt.Objects.clone(JASTEggIt.XML);
		xml.id = id;
		xml.onReady = JASTEggIt.rssReader.xmlReadyState;
		rss._xmlDocument = xml;
		xml.urlLoad(rss.rssSource);
	},
	readTitle: function(id, xml){
		var rss = JASTEggIt.rssReader._rss[id];
		if(xml==null) xml = rss._xmlDocument;
		
		var tid = rss.titleId;
		if(tid=='' || tid==null) return false;
		var canale = xml.getNode('channel');
		JASTEggIt._id(tid).innerHTML = xml.getValue(canale, 'title');
		JASTEggIt._id(tid).title = xml.getValue(canale, 'description');
		
	},
	readNext: function(id){
		var rss = JASTEggIt.rssReader._rss[id];
		this.readPage(id, rss._currentPage+1);
	},
	readPrevious: function(id){
		var rss = JASTEggIt.rssReader._rss[id];
		this.readPage(id, rss._currentPage-1);		
	},
	readPage: function(id, pageNumber, xml){
		var rss = JASTEggIt.rssReader._rss[id];
		if(xml==null) xml = rss._xmlDocument;
		var items = xml.getNodes('item');
		if(items.length==0 && rss.hideIfEmpty){
			JASTEggIt.DOM.setStyle(id, {display: 'none'});
			return false;
		}
		
		var pageCount = (items.length/rss.itemsPerPage);
		pageCount = parseInt(pageCount+0.9);	// Ottengo il numero intero di pagine
		
		var cp = rss._currentPage;
		if(cp==null){
			rss._currentPage = 1;
			cp = 1;
		};
		if(pageNumber<1) pageNumber = 1;
		if(pageNumber >pageCount) pageNumber = pageCount;
		if (pageNumber == 0) {
			// Se non ci sono reord devo svuotare l'area
			JASTEggIt._id(rss.contentsId).innerHTML = '';
		}
		else {
			rss._currentPage = pageNumber;
			
			var s = (pageNumber - 1) * rss.itemsPerPage;
			var e = (pageNumber) * rss.itemsPerPage;
			if (e > items.length) 
				e = items.length;
			
			var b = '';
			for (var i = s; i < e; i++) {
				b += '<div class="rssItem">';
				if (rss.displayItemTitle) 
					b += '<a href="' + xml.getValue(items[i], 'link') + '">' + xml.getValue(items[i], 'title') + '</a>';
				if (rss.displayItemDescription) 
					b += '<p>' + xml.getValue(items[i], 'description') + '</p>';
				b += '</div>';
			};
			JASTEggIt._id(rss.contentsId).innerHTML = b;
		}
		JASTEggIt.rssReader.updateNavigator(id, pageNumber, pageCount  );
	},
	updateNavigator: function(id, cp, pc){
		var rss = this._rss[id];
		var xf = rss.extraFunctions;
		var n = '';
		if (pc>1){
			// Se c'è più di una pagina, devo mostrare gli elementi di navigazione
			n+='<li class=	"previous" title="' + rss.previousTitle +'"><a href="javascript:JASTEggIt.rssReader.readPrevious(\''+id+'\');">&nbsp;</a></li>';
			n+='<li class="next" title="' + rss.nextTitle +'"><a href="javascript:JASTEggIt.rssReader.readNext(\''+id+'\');">&nbsp;</a></li>';
		}
		if(rss.displayRssIcon){
			n+='<li class="rss"><a href="' + rss.rssSource + '">&nbsp;</a></li>';
		}

		for(x in xf){
			n+= '<li';
			n+= ' class="' + xf[x].className + '"';
			n+= ' title="' + xf[x].title + '">';
			n+= '<a href="' + xf[x].action + '">';
			n+= xf[x].description;
			n+= '</a>';
			n+= '</li>';
		}
		if(n!='') n='<ul>'+n+'</ul>';
		JASTEggIt._id(rss.navigatorId).innerHTML = n;
	}
});