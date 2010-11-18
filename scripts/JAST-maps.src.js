_.extend('Maps',{
	options: {
		
		url: '',
		parameters: {},
		width: 400,
		height:	350,
		zoom:	100,
		displayRules: true,
		trackXY:	true,
		offsetX:	0,
		offsetY:	0,
		allowMultipleMarkers:	true,
		allowZoomBox:	true,
		markers:	[],
		controls:	[],
		addControlPanel:	true,
		markerOffsetX: 0,
		markerOffsetY: 0,
		rect:	{
			xmin:	0,
			ymin:	0,
			xmax:	1,
			ymax:	1
			
		},
		actions: null,
		onMarkerMove: 	null,
		onMarkerClick: 	null,
		onMove:			null,
		onZoom:			null,
		onZoomBoxEnd:	null,
		_markersCount: 0
	},
	markerOptions: {
		x:			 0,	// Coordinate in Gauss-Boaga
		y:			 0, // Coordinate in Gauss-Boaga
		width:		16,
		height:		16,
		imageSource: '',
		description: '',
		position:	1,		// RELATIVE - 0
							// ABSOULTE	- 1
		metric:		1,		// PIXELS	- 0
							// GB		- 1
		centerMapOnClick: true
	},
	isDragging: false,
	_maps: [],
	setup: function(id, options){
		options = _.mergeOptions(options, _.Maps.options);
		
		_.DOM.setStyle(id,
			{
				width: options.width + 'px',
				height: options.height + 'px'
			}
		);
		if(options.trackXY){
			var div = _.DOM.createChild('DIV', id, id+'-coords');
			_.DOM.appendClass(div, 'map-coords');
			div.innerHTML = 'X: 0, Y: 0';
			
			_.Events.add(id, 'mousemove', _.Maps._trackCoords);
		}
		if(options.addControlPanel){
			var div = _.DOM.createChild('DIV', id, id+'-cpanel');
			_.DOM.appendClass(div, 'map-cpanel');
		}
		
		_.Events.add(id, 'dblclick', _.Maps._addMarker);
		_.Events.add(id, 'DOMMouseScroll', _.Maps._wheel);
		_.Events.add(id, 'mousewheel', _.Maps._wheel);
		_.Events.add(id, 'mousedown',		_.Maps._startZoomBox);
		_.Events.add(id, 'mousemove',		_.Maps._growZoomBox);
		_.Events.add(id, 'mouseup',			_.Maps._endZoomBox);
		
		_.Maps._maps[id] = options;
		
		_.Maps.redraw(id, true);
		
	},
	_growZoomBox:	function(event){
		var g = _.Events.generator(event);
		var id = g.id;
		
		var zoomBox = _._id(id +'-zoom-box');
		if( zoomBox ){
		
			_.Maps._startZoomBox(event, true);
			_.Maps.isDragging = true;
		}
	},
	_startZoomBox: function(event, fromMove){
		var g = _.Events.generator(event);
		var id = g.id;
		
		var mapXY = _.DOM.position(id);
		var zoomBox = _._id(id +'-zoom-box');
		if( zoomBox ){
			
			// Lo ingrandisco
			_.Maps.isDragging = true;
			var margins = _.DOM.style(zoomBox, 'marginLeft marginTop');
			
			
			var newSzX = (event.clientX-mapXY.x-_.strings.parseInt(margins.marginLeft));
			var newSzY = (event.clientY-mapXY.y-_.strings.parseInt(margins.marginTop));
			window.title = newSzX + ', ' + newSzY;
			if(newSzX<10) newSzX = 10;
			if(newSzY<10) newSzY = 10;
			
			_.DOM.setStyle(zoomBox, {
				width: newSzX+'px',
				height: newSzY+'px'
			});
			
			
		}else if(!fromMove){
			_.Maps.isDragging = false;
			// Creo l'elemento
			
			var zoomBoxDiv = _.DOM.createChild('DIV', id, id +'-zoom-box');
			_.DOM.setStyle(zoomBoxDiv, 
				{
					position: 'fixed',
					border:	'1px solid #888',
					backgroundColor: '#aaa',
					opacity:	'0.5',
					marginLeft:	(event.clientX-mapXY.x) + 'px',
					marginTop:	(event.clientY-mapXY.y) + 'px',
					width:	'10px',
					height: '10px'
				}
			);
			
			
		}
	},
	_getScrollTop: function(){
		if(document.documentElement && document.documentElement.scrollTop) return document.documentElement.scrollTop;
		return document.body.scrollTop;
	},
	_endZoomBox: function(event){
		var g = _.Events.generator(event);
		var id = g.id;
		var zoomBox = _._id(id +'-zoom-box');
		if(_.Maps.isDragging){
			_.Maps.isDragging = false;
			var mapXY = _.DOM.position(id);
			var mapSz = _.DOM.realSize(id);
			
			
			var margins = _.DOM.style(zoomBox, 'marginLeft marginTop');
			
			margins.marginLeft = _.strings.parseInt(margins.marginLeft);
			margins.marginTop = _.strings.parseInt(margins.marginTop);
			
			
			var size = _.DOM.realSize(zoomBox);
			var deltaX = size.width/mapSz.width;
			var deltaY = size.height/mapSz.height;
			
			
			var delta = (deltaX < deltaY)?deltaX:deltaY;
			size.height = (size.height/deltaY) * delta;
			size.width =  (size.width/deltaX)  * delta;
			
			/*
			 bbymax
			 +--------------------------------------------------------------+bbxmax
			 |                                                              | ^
			 |                                                              | |
			 |                  margins (marginLeft,marginTop)              | |
			 |    ymax ----------> +--------------------------------+       | |
			 |                     |                                |       | |
			 |                     |                                |       | |
			 |                     |                                |       | |
			 |                     |                                |       | |
			 |                     |                                |       | |mapSz.height
			 |                     |                                |       | |
			 |                     |                                |       | |
			 |                     |                                |       | |
			 |    ymin ----------> +--------------------------------+       | |
			 |                                        size(width,height)    | |
			 |                                                              | |
			 |                                                              | |
			 |                                                              | |
			 |                                                              | |
			 |                                                              | v
 		  	 +--------------------------------------------------------------+bbymin
			 bbxmin
			 |<------------------------------------------------------------>|
			 				           	mapSz.width
			 
			 se y è invertita in GB:
			 
			 	ymin = bbymax - (margins.y+margins.h)
			 	ymax = bbymax - margins.y
			 
			 */
			
			var ymin = mapSz.height-(margins.marginTop+_.Maps._getScrollTop()+size.height);
			var ymax = mapSz.height- margins.marginTop+_.Maps._getScrollTop();
			
			
			var gbXYmin = _.Maps.px2gb(id, margins.marginLeft, ymin);
			var gbXYmax = _.Maps.px2gb(id, size.width+margins.marginLeft, ymax);
			
			var m = _.Maps._maps[id];
			
			var r = m.rect;
	
			var diffX = gbXYmax.gbx - gbXYmin.gbx;
			
			m.zoom = diffX/2;
			
			
			m.onMove(id, m.rect.xmin + gbXYmin.gbx+ m.zoom, m.rect.ymin + Math.abs(gbXYmin.gby)+ m.zoom);
	/*
			debugBuffer += '<strong>after redefined:</strong> currentRect: [(' + r.xmin + ',' + r.ymin + '), ';
			debugBuffer += ' (' + r.xmax + ',' + r.ymax + ')]<br />';
			
			
			_._id('debug-map').innerHTML = debugBuffer;
*/
		}
		_.DOM.remove(zoomBox);
		
	},
	
	_wheel: function(event){
		
        if (!event) event = window.event;
        var delta = _.Events.getWheelDelta(event);
        /** If delta is nonzero, handle it.
         * Basically, delta is now positive if wheel was scrolled up,
         * and negative, if wheel was scrolled down.
         */
        
        if (delta){
                if(delta>0) _.Maps.zoomIn(event);
                else  _.Maps.zoomOut(event);
        }
        /** Prevent default actions caused by mouse wheel.
         * That might be ugly, but we handle scrolls somehow
         * anyway, so don't bother here..
         */
        _.Events.abort(event);
	},

	setRectFromPoint: function(id, x, y){
		
		var m = _.Maps._maps[id];
		if(m){
			var xmin = parseFloat(x) - m.zoom;
			var ymin = parseFloat(y) - m.zoom;
			var xmax = parseFloat(x) + m.zoom;
			var ymax = parseFloat(y) + m.zoom;
			_.Maps.setRect(id, xmin, ymin,xmax, ymax);
		}
	},
	getRect: function(id){
		
		var m = _.Maps._maps[id];
		if(m)return m.rect;
	},
	doZoom: function(idMap, event){
		var map = _.Maps._maps[idMap];
		if(map && map.onZoom){
			var rect = _.Maps.getRect(idMap);
			map.onZoom(idMap, (map.rect.xmax + map.rect.xmin)/2, (map.rect.ymax + map.rect.ymin)/2);
		}
	},
	zoomIn: function(event){
		var el =_.Events.generator(event);
		var idMap = el.id;
		if(!_.Maps._maps[idMap]) var idMap = _.Maps.getMapByControl(idMap);
		var map = _.Maps._maps[idMap];
		if(map){
			map.zoom -= 20;
			_.Maps.doZoom(idMap, event);
		}
	},
	zoomOut: function(event){
		var el =_.Events.generator(event);
		var idMap = el.id;
		if(!_.Maps._maps[idMap]) var idMap = _.Maps.getMapByControl(idMap);
		var map = _.Maps._maps[idMap];
		if(map){
			map.zoom += 20;
			_.Maps.doZoom(idMap, event);
		}
	},
	
	move: function(idMap, offsetX, offsetY, event){
		
		var map = _.Maps._maps[idMap];
		if(map){
			
			map.rect.xmin += offsetX/2;
			map.rect.ymin += offsetY/2;
			map.rect.xmax += offsetX/2;
			map.rect.ymax += offsetY/2;
			
			if(map.onMove) map.onMove(idMap, (map.rect.xmax + map.rect.xmin)/2, (map.rect.ymax + map.rect.ymin)/2);
			
		}
		
		
	},
	
	moveLeft: function(event){
		var el =_.Events.generator(event);
		var id = el.id;
		var idMap = _.Maps.getMapByControl(id);
		_.Maps.move(idMap, -_.Maps._maps[idMap].zoom, 0, event);
	},
	moveRight: function(event){
		var el =_.Events.generator(event);
		var id = el.id;
		var idMap = _.Maps.getMapByControl(id);
		_.Maps.move(idMap, _.Maps._maps[idMap].zoom, 0, event);
	},
	moveUp: function(event){
		var el =_.Events.generator(event);
		var id = el.id;
		var idMap = _.Maps.getMapByControl(id);
		_.Maps.move(idMap, 0, _.Maps._maps[idMap].zoom, event);
	},
	moveDown: function(event){
		var el =_.Events.generator(event);
		var id = el.id;
		var idMap = _.Maps.getMapByControl(id);
		_.Maps.move(idMap, 0, -_.Maps._maps[idMap].zoom, event);
	},
	addControlGroup: function(id, className, controls){
		
		m = _.Maps._maps[id];
		if(m){
			var cpanel = _._class('map-cpanel',id);
			if(cpanel.length!=0){
				cpanel = cpanel[0];
				var link = _.DOM.createChild('span', cpanel);
				link.id = _.generateUniqueId('maps');
				_.DOM.appendClass(link, className);
				for(var i=0; i<controls.length; i++){
					var c= controls[i];
					_.Maps.addControl(id, c[0], c[1], c[2],  link.id);
				}
			}
		}
	},
	addControl: function(id, className, fn, tip, subgroup){
		m = _.Maps._maps[id];
		if(m){
			var cpanel = _._class('map-cpanel',id);
			if(cpanel.length!=0){
				cpanel = cpanel[0];
				if(subgroup!=null) cpanel = subgroup;
				var link = _.DOM.createChild('span', cpanel);
				link.id = _.generateUniqueId('maps');
				_.DOM.appendClass(link, 'action');
				_.DOM.appendClass(link, className);
				if(tip!=null){
					
					link.setAttribute('title', tip);
					
				}
				_.Events.add(link,'click', fn);
				m.controls[link.id] = true;
			}
		}
	},
	getMapByMarker: function(idMarker){
		var maps = _.Maps._maps;
		for(mapId in maps){
			var map = maps[mapId];
			for(markerId in map.markers){
				if(markerId == idMarker) return mapId;
			}
		}
	},

	getMapByControl: function(idControl){
		var maps = _.Maps._maps;
		for(mapId in maps){
			var map = maps[mapId];
			for(controlId in map.controls){
				if(controlId == idControl) return mapId;
			}
		}
	},
	_addMarker: function(event){
		
		if(event == null) event = window.event;
		
		var itm = _.Events.generator(event);
		var id = itm.id;
		var m = _.Maps._maps[id];
		if(m == null) return false;
		
		var pos = _.DOM.position(id);
		var ox = event.layerX - pos.x;
		var oy = event.layerY - pos.y;
		oy = m.height - oy;
		if(m.allowMultipleMarkers || m._markersCount==0){
			_.Maps.addMarker(id, {
				
				x: ox,
				y: oy,
				position: 0,
				metric:	  0,
				imageSource: '',
				description: 'Marker'}
			);
		}else{
			
			for(markerId in m.markers) _.Maps.moveMarker(id,markerId, {
				
				x: ox,
				y: oy,
				position: 0,
				metric:	  0,
				imageSource: '',
				description: 'Marker'});
		}
	
		
		
		
	},
	
	setRect: function(id, xmin, ymin, xmax, ymax){
		
		var m = _.Maps._maps[id];
		if(m == null) return false;
		
		m.rect.xmin = xmin;
		m.rect.ymin = ymin;
		m.rect.xmax = xmax;
		m.rect.ymax = ymax;
		
	},
	_markerClick: function(event){
		
		var ms = _.Maps._maps;
		var id = _.Events.generator(event).id;
		map = _.Maps.getMapByMarker(id);
		var m = ms[map];
		if(m.onMarkerClick) {
			var mk = m.markers[id];
			m.onMarkerClick(map, id, mk.x, mk.y);
		}

	},
	_trackCoords: function(event){
		if(event == null) event = window.event;
		
		var itm = _.Events.generator(event);
		var id = itm.id;
		var m = _.Maps._maps[id];
		if(m == null) return false;
		
		var pos = _.DOM.position(id);
		var x = event.layerX - pos.x;
		var y = event.layerY - pos.y;
		y = m.height - y;
		
		var c = _.Maps.px2gb(id, x,y);
		x = c.gbx+ m.rect.xmin;
		y = c.gby+ m.rect.ymin;
		_._id(id + '-coords').innerHTML = 'X: ' + parseInt(x) +'; Y: ' + parseInt(y);
	},
	
	setMapParams: function(id, parameters){
		var m = _.Maps._maps[id];
		if(m==null) return false;
		m.parameters = _.mergeOptions(parameters, m.parameters);

		_.Maps.redraw(id, true);
	},
	
	redraw: function(id, redrawMarkers){
		if(redrawMarkers==null) redrawMarkers = false;
		var m = _.Maps._maps[id];
		if(m == null) return false;
		
		var url = m.url + '?'+_.xhttp._createQueryString(m.parameters);
		
		_.DOM.setStyle(id, {
			backgroundImage: "url('"+url+"')"});
		
		if(redrawMarkers){
			
			_.Maps.redrawMarkers(id);
		}
	},
	
	redrawMarkers: function(id, markerId){
		
		if(markerId == null){
			var m = _.Maps._maps[id];
			if(m == null) return false;
			
			for(markerId in m.markers) _.Maps.redrawMarkers(id, markerId);
			
		}else{
			
			var m = _.Maps._maps[id];
			if(m == null) return false;

			var marker = m.markers[markerId];
			
			
			if(marker.x<m.rect.xmin || marker.x>m.rect.xmax || marker.y<m.rect.ymin || marker.y>m.rect.ymax){
				// Sono fuori, non lo devo disegnare
				_.DOM.setStyle(markerId,{
					display: 'none'});
			}else{
				
				var c = _.Maps.gb2px(id, marker.x - m.rect.xmin, marker.y - m.rect.ymin );
				_.DOM.setStyle(markerId,{
					display: 	'block',
					position: 	'absolute',
					marginLeft:	parseInt(c.pxx-(marker.width/2)+m.markerOffsetX) +'px',
					marginTop:	parseInt(c.pxy-(marker.height/2)+m.markerOffsetY)+'px'
				});
			}
		
		}
	},
	
	moveMarker: function(id, markerId, options){
		
		if(options==null) options = _.Maps._maps[id].markers[markerId];
		
		options = _.mergeOptions(options,_.Maps._maps[id].markers[markerId]);
		if(options.metric==0){
			// Le coordinate sono specificate in Pixel
			
			var px = _.Maps.px2gb(id, options.x, options.y);
			options.x = px.gbx;
			options.y = px.gby;
			options.metric = 1;
			
		}
		
		var m = _.Maps._maps[id];
		
		if(options.position==0){
			// La posizione è relativa devo trasformarla in posizione assoluta
			options.x +=  m.rect.xmin;
			options.y +=  m.rect.ymin;
			options.position = 1;
			
		}
		_.Maps._maps[id].markers[markerId] = options;
		_.Maps.redrawMarkers(id, markerId);
		if(m.onMarkerMove) m.onMarkerMove(id, markerId, options.x, options.y);
	},
	
	addMarker: function(id, options){
		m = _.Maps._maps[id];
		if(m==null) return false;
		options = _.mergeOptions(options, _.Maps.markerOptions);
		if(m.allowMultipleMarkers || m._markersCount==0){
			
			var itm = _._id(id);
			var marker = _.DOM.createChild('SPAN', itm);
			marker.id = _.generateUniqueId('maps');
			_.Maps._maps[id]._markersCount+=1;
			_.Maps._maps[id].markers[marker.id] =options;
			
			
			_.DOM.appendClass(marker, 'marker');
			if(options.imageSource != ''){
				_.DOM.setStyle(marker,{
					backgroundImage: "url('"+ options.imageSource +"')"
				});
			}
			marker.setAttribute('title', options.description);
			_.Events.add(marker, 'click', _.Maps._markerClick);
			var markerId = marker.id;
		}else{
			for(var mkid in m.markers) markerId = mkid; 
		}
		_.Maps.moveMarker(id, markerId, options);
		
		

		
		
	},
	
	/**
	 * Converte una coordinata da pixels in coordinata Gauss-Boaga
	 * in dipendenza di un bounding box definito per l'oggetto id
	 */
	px2gb: function(id, x,y){
	
		var m = _.Maps._maps[id];
		if(m == null) return false;
		
		var szx = (m.rect.xmax -m.rect.xmin) / m.width;
		var szy = (m.rect.ymax -m.rect.ymin) / m.height;		
		
		return {
			gbx: szx*x,
			gby: szy*y
		}
		
	},
	/**
	 * Converte una coordinata da Gauss-Boaga a Pixel
	 * in dipendenza del bounding box definito per l'oggetto id
	 */
	gb2px: function(id, x, y){
		var m = _.Maps._maps[id];
		if(m == null) return false;
		
		var szx = (m.rect.xmax -m.rect.xmin) / m.width;
		var szy = (m.rect.ymax -m.rect.ymin) / m.height;		
		
		return {
			pxx: x/szx,
			pxy: m.height-(y/szy)
		}
	}
	
	
});