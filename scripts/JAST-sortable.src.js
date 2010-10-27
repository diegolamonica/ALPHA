/*
Script Name: 	Step Wizard (http://jastegg.it/eggs/sortable/ )
Author:			Diego La Monica 
version: 		1.2 beta
version date:	2009-07-07
Plugin for:		JAST ( http://jastegg.it )
--------------------------------

Change log:
*/
_.extend('Sortable',{
	info: {
		title: 		'Sortable',
		version:	'0.1',
		author:		'Diego La Monica',
		url:		'http://diegolamonica.info'
	},
	_onselectstart: null,
	container: [],
	currentSortableItem: null,
	currentContainer: null,
	currentTempItem: '',
	baseX: 0,
	baseY: 0,
	options:	{
		dragClass: 'drag',
		dropClass: 'drop',
		draggableClass: 'draggable',
		onDrag:		null,
		onDrop:		null
	},
	_find: function(activatorId){
		for(c in _.Sortable.container){
			var cn = _.Sortable.container[c];
			for(var i = 0; i<cn.length; i++){
				if(cn[i].activatorId == activatorId){
					_.Sortable.currentContainer = c;
					return cn[i];
				}
			}
			
		}
		return null;
	},
	_createDropItem: function(itm, dropClass, before){
		itm = _._el(itm);
		var id = _.generateUniqueId('drag');
		var sz = _.DOM.realSize(itm);
		
		if(before)
			var tempItem = _.DOM.createOnDocument(itm.tagName,  itm, null, id);
		else
			var tempItem = _.DOM.createOnDocument(itm.tagName,  null, itm,id);
		_.DOM.setStyle(tempItem, itm.style);
		
		if(_.DOM.style(tempItem, 'display')['display']=='inline'){
			_.DOM.setStyle(tempItem, {display: 'inline-block'});
		}
		
		_.DOM.setStyle(tempItem, {width: sz.width+'px', height: sz.height+'px'});
		_.DOM.appendClass(tempItem, itm.className);
		_.DOM.appendClass(tempItem, dropClass);
		tempItem.innerHTML = '&nbsp;';
		_.Sortable.currentTempItem = id;
	},
	avoidSelection: function(event){
		
		_.Sortable._onselectstart(event);
		document.title = (_.Sortable.currentSortableItem==null);
		return (_.Sortable.currentSortableItem==null);
		
	},
	drag: function(event){
		var itm = _.Events.generator(event);
		while(itm.id==null || itm.id==''){
			
			itm = itm.parentNode;
			if(itm==null) return false;
			
		}
		var o = _.Sortable._find(itm.id);
		if(o!=null){
			_.DOM.setStyle(_.Sortable.currentContainer, {'MozUserSelect': 'none'});	// Only for firefox
			if(o.onDrag!=null){
				var r = o.onDrag(o);
				if(r===false) return false; 
			}

			itm = _._id(o.itemId);
			var rp = _.DOM.position(itm);

			o.position = _.DOM.style(itm,'position');
			o.position=o.position['position'];
			o.x = rp.x;
			o.y = rp.y;
			_.Sortable.currentSortableItem = o;
			
			
			_.Sortable._createDropItem(itm, o.dropClass);
			
			_.DOM.appendClass(o.itemId, o.dragClass);
			_.DOM.setStyle(o.itemId, {position: 'absolute'});
			_.Sortable.baseX = event.screenX;
			_.Sortable.baseY = event.screenY;
		}
		
	},
	move: function(event){
		if(_.Sortable.currentSortableItem!=null){
			var o = _.Sortable.currentSortableItem;
			
			var x =event.screenX-_.Sortable.baseX + o.x;
			var y =event.screenY-_.Sortable.baseY + o.y;
			_.DOM.setStyle(o.itemId, {left:  x + 'px', top: y+'px'});
			var canDrop = true;
			if(o.onDrop) canDrop = o.onDrop(o);
			if(canDrop==false){
				
			}else{
				var c = _.Sortable.container[_.Sortable.currentContainer];
				for(var i= 0; i<c.length; i++){
					if(c[i].itemId!=o.itemId){
						var p = _.DOM.position(c[i].itemId);
						var s = _.DOM.realSize(c[i].itemId);
						document.title = p.x + ',' +p.y +  ' -' +x	+','+y;
						
						if(		(p.x<=x) && 			(p.y<=y) && 
								(p.x+s.width>=x) &&	(p.y+s.height>=y) ){
							// Devo fare lo swap degli elementi
							
							var cn = _._id(_.Sortable.currentContainer);
							var leader = _.DOM.position(_.Sortable.currentTempItem);
							cn.replaceChild(_._id(c[i].itemId), _._id(_.Sortable.currentTempItem));
							var leadY= false;
							
							
							
							if(Math.abs(leader.x/p.x)> Math.abs(leader.y/p.y)) leadY = true;
							
							document.title = (leadY?'Lead Y':'Lead X') + ' - ' + p.x + ',' +p.y;
							
							if(((x-p.x<s.width/2) && !leadY) || ((y-p.y<s.height/2) && leadY)){
								_.Sortable._createDropItem(c[i].itemId, o.dropClass,true);
							}else{
								_.Sortable._createDropItem(c[i].itemId, o.dropClass,false);
							}
						}
	
					}
				}
			}
		}

	},
	
	drop: function(event){
		var o = _.Sortable.currentSortableItem;
		if(o==null) return;
		var c = _._id(_.Sortable.currentContainer);
		if(c!=null){
			var canDrop = true;
			if(o.onDrop) canDrop = o.onDrop(o);
			if(canDrop==false)
				_.DOM.remove(_.Sortable.currentTempItem);
			else
				c.replaceChild(_._id(o.itemId), _._id(_.Sortable.currentTempItem));
				
			
			_.DOM.removeClass(o.itemId,o.dragClass);
			_.DOM.setStyle(o.itemId, {'position': o.position, left: o.x +'px', top: o.y +'px'});
			_.DOM.setStyle(_.Sortable.currentContainer, {'MozUserSelect': ''});	// Only for firefox

			_.Sortable.currentTempItem = null;
			_.Sortable.currentContainer = null;
			_.Sortable.currentSortableItem = null;
			_.Sortable.baseX = 0;
			_.Sortable.baseY = 0;
		}
		
	},
	add: function(itemId, activatorId, options){
		
		if(_.Array.is(itemId)){
			for(var i=0; i<itemId.length; i++) _.Sortable.add(itemId[i], activatorId[i], options);
		}else{
			var item = _._id(itemId);
			var parent = item.parentNode;
			if(parent.id==null || parent.id=='') parent.id = _.generateUniqueId('drag');
			var o = new Object();
			
			var o = _.mergeOptions(o, options);
			var o = _.mergeOptions(o, this.options);
			
			if(activatorId==null) activatorId = itemId;
			o.itemId = itemId;
			o.activatorId = activatorId;
			if(this.container[parent.id]==null) this.container[parent.id] = [];
			this.container[parent.id].push(o);
			_.DOM.appendClass(activatorId,o.draggableClass);
			
		}
		
	},
	startup: function(){
		_.Events.add(document, 'mouseup', _.Sortable.drop);
		_.Events.add(document, 'mousemove', _.Sortable.move);
		_.Events.add(document, 'mousedown', _.Sortable.drag);
		if(document.onselectstart!=null) _.Sortable._onselectstart = document.onselectstart; 
		document.onselectstart = _.Sortable.avoidSelection;
	}
	
});