JASTEggIt.extend('DragNDrop',{
		options:	{
			id:				'',
			title:			'drag\'n drop',
			dragOnTitle:	true,
			titleStyle:		{
				fontFamily:			'verdana',
				fontSize:			'12px',
				backgroundColor: 	'#008',
				color: 				'#fff'
			},
			sel: 			false,
			isDragged:		false,
			x:				0, 
			y:				0,
			tmpX:			0,
			tmpY:			0
		},
		setup: function(id, options){
			options = JASTEggIt.mergeOptions(options, this.options);
			
			var toBeDragged = _._id(id);
			var html = toBeDragged.innerHTML;
			toBeDragged.innerHTML = '';
			var contentsId = JASTEggIt.generateUniqueId('dragndrop');
			var contents = JASTEggIt.DOM.createChild('div', id, contentsId);
			contents.innerHTML = html;
			if(options.title!=''){
				var titleDiv = JASTEggIt.DOM.createOnDocument('div', contentsId, null );
				titleDiv.id = JASTEggIt.generateUniqueId('dragndrop');
				JASTEggIt.DOM.setStyle(titleDiv, options.titleStyle);
				titleDiv.innerHTML = options.title;
				
			}
			JASTEggIt.DOM.setStyle(id, { position: 'absolute' });
			options.id = id;
			JASTEggIt.Events.add(id, 'mousedown', JASTEggIt.DragNDrop.select);
			JASTEggIt.Events.add(id, 'mouseup', JASTEggIt.DragNDrop.mouseUp);
			JASTEggIt.Events.add(id, 'release', JASTEggIt.DragNDrop.mouseUp);
			JASTEggIt.Events.add(id, 'mousemove', JASTEggIt.DragNDrop.move);
			var el = JASTEggIt._id(options.id);
			el.options = options;
			
			JASTEggIt.DOM.appendClass(el.parentNode, 'draggable');
		},
		startup: function(){
			
			var dragitems = JASTEggIt._get('.dragndrop');
			for(var i = 0; i<dragitems.length; i++){
				alert(dragitems[i].tagName);
				if(dragitems[i].id==null) 
					dragitems[i].id = JASTEggIt.generateUniqueId('dragndrop');
				else if (dragitems[i].id=='')
					dragitems[i].id = JASTEggIt.generateUniqueId('dragndrop');
				JASTEggIt.DragNDrop.setup(dragitems[i].id);	
			}
		},
		mouseUp: function(e){
			document.title='';

			var obj = JASTEggIt.Events.generator(e);
			obj = JASTEggIt.DragNDrop.matchDraggingContainer(obj);
			obj.options.isDragged = false;
			obj.options.sel = false;
		},
		matchDraggingContainer: function(obj){
			var o	= obj.options;
			while(o == null || o.isDragged == null){
				if(obj.parentNode==null) break;
				obj = obj.parentNode;
				o = obj.options;
			};
			return obj;

		},
		move: function(e) {
			var obj = JASTEggIt.Events.generator(e);
			obj = JASTEggIt.DragNDrop.matchDraggingContainer(obj);
			var o	= obj.options;
			document.title = 'move ' + o.id;
			if (o.isDragged) {
				JASTEggIt.DOM.setStyle(obj,
					{
						left:	(o.tmpX + e.clientX - o.x) + "px",
						top:	(o.tmpY + e.clientY - o.y) + "px",
						position:	'absolute'
					}
				);
				o.sel = true;
			}
			return false;
		},
		select: function(e) {
			var el = JASTEggIt.Events.generator(e);
			el = JASTEggIt.DragNDrop.matchDraggingContainer(el);

			var o = el.options;
			if (o==null) {
				document.title='error otpions is null';
				return;				
			};
			var _obj = JASTEggIt._id(o.id);
			if( _obj == null) return;
			if( _obj.parentNode==null) return;
			var obj = _obj.parentNode;
			
			if (obj.className=="draggable") {     
				o.isDragged = true;
				if (o.sel==false) {
					var pos = JASTEggIt.DOM.position(_obj);
					o.tmpX = parseInt(pos.x);
					o.tmpY = parseInt(pos.y);
				} else {
					var pos = JASTEggIt.DOM.position(_obj);
					o.tmpX = parseInt(pos.x);
					o.tmpY = parseInt(pos.y);
				}
			};
			var evt = JASTEggIt.Browser.mozilla ? e:event;
			o.x = evt.clientX;
			o.y = evt.clientY;
			o.isDragged = true;
		}
	}
);