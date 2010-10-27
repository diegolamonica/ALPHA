_.extend('ariaMenu',{
	info: {
		title: 		'ARIA Menu',
		version:	'1.0 beta',
		eggUrl:		'http://jastegg.it/eggs/ariaMenu',
		author:		'Diego La Monica',
		url:		'http://diegolamonica.info'
	},
	options: {
		defaultClassName: 'aria-menu',
		defaultMenuItemClassName: 'menu-item',
		expandMenuItemClassName: 'expand-menu-item',
		menuStyle:{
			border: '1px solid #ccc',
			borderTop: '1px solid #eee',
			borderLeft: '1px solid #eee',
			display: 'block',
			margin: '0',
			padding: '0',
			listStyle: 'none',
			listStyleType: 'none'
		},
		mainMenuStyle:{
			//border: '1px solid #ccc',
			//cssFloat: 'left',
			//Float: 	 'left',
			display: 'inline'
		},
		_isExpanded: false,
		_isDisplaying: [],
		onSubmenuShow: null
	},
	menu: [],
	startup: function(){
		var me = _.ariaMenu;
		var o = me.options;
		
		allMenu = _._class(o.defaultClassName);
		me.setup(allMenu, o);
	},
	
	/*
	Internal methods
	*/
	_eventClick: function(event){
		var gen = _.Events.generator(event);
		var me = _.ariaMenu;
		var o = me._identify(gen);
		
		if(_.DOM.hasClass(gen.parentNode,o.defaultClassName)){
			
			var ul = _._name('ul',gen);
			var expanded = false;
			if(ul.length>0) o.isExpanded =  (ul[0].style.display!='none');
			
			if(o._isExpanded){
				ul[0].style.display= 'none';
				o._isExpanded = false;
			}else{
				me._eventClickFocus(event, true);
			}	
		}else{
			me._cleanSubmenu(document);
		}
	},
	_eventClickFocus: function(event, fromEventClick){
		var me = _.ariaMenu;
		var gen = _.Events.generator(event);
		if(gen.parentNode!=null && gen.tagName=='A') gen = gen.parentNode;
		var o = me._identify(gen);
		
		if(o!=null){
		
			if(fromEventClick==null) fromEventClick=false;
			if(!o._isExpanded && !fromEventClick && _.DOM.hasClass(gen.parentNode, o.defaultClassName )) return false;
			var mainMenu = _._id(o.id);
			if(gen.id!=null && gen.id!='' && _.DOM.isChildOf(gen.id, mainMenu.id)){
				me._displaySubmenu(gen);
			}
		}
		me._cleanSubmenu(gen);
	},
	_eventKeyDown: function(event){
	
		var me = _.ariaMenu;
		var aria = _.ARIA;
		var gen = _.Events.generator(event);
		var o = me._identify(gen);
		
		var key = _.kbd.getKeyPressed(event);
		
		/*
		 <- e  -> se il focus � su un livello 0 o se � su un subitem classico,
		 do il focus al menu di primo livello successivo
		 se il focus � su un item espandibile il focus viene dato all'elemento strettamente successivo (se � l'ultimo elemento fa perdere il focus)
		*/
		var p = gen.parentNode; // UL
		p = p.parentNode;		// LI
		if(key==13) me._eventClick(event);
		if(key==37){ 
			// freccia verso sinistra 
			switch(true){
				case _.DOM.hasClass(p, o.expandMenuItemClassName):
					// se il padre � un item espandibile deve assumere il focus		
					p.focus();
					break;
				default:
					// altrimenti devo spostarmi all'elemento padre precedente
					var ul = _._id(o.id);
					var previousItem = null;
					var lis = ul.childNodes;
					for(var i = 0; i<lis.length; i++){
						
						if(	lis[i].nodeType==1){ // � un nodo HTML del DOM
							
							if (_.DOM.isChildOf(gen, lis[i].id)) { // � un nodo figlio di questa radice){
								if (previousItem == null) {
									return false;
								}
								else {
									previousItem.focus();
									return true;
								}
							}
							previousItem = lis[i];
						}
						
					}
					
					if(previousItem!=null) previousItem.focus();
			}
			return;
		}
		if(key == 39){ // Freccia a destra
			if (_.DOM.hasClass(gen.parentNode, o.defaultClassName)) {
				aria.focusNextNode(gen);
			}else{
				if (_.DOM.hasClass(gen, o.expandMenuItemClassName)) {
					lis = _._name('li', gen);
					if(lis.length!=0) lis[0].focus();
				}
			}
		}
		if(key == 38){
			var ret = aria.focusPreviousNode(gen);
			if(!ret) gen.parentNode.parentNode.focus();
		}
		if(key == 40){
			if(_.DOM.hasClass(gen.parentNode,o.defaultClassName)){
				lis = _._name('li', gen);
				if(lis.length!=0) lis[0].focus();
			}else{
				aria.focusNextNode(gen);
			}
				
		}
		
	},
	_cleanSubmenu: function(currentSubmenu, forceHideAll){
		if(forceHideAll==null) forceHideAll = false;
		var me = _.ariaMenu;
		var aria = _.ARIA;
		var o = me._identify(currentSubmenu);
		if(o == null || !forceHideAll){
			
			for(m in me.menu){
				if(!_.DOM.isChildOf(currentSubmenu, m) ) me._cleanSubmenu(_._id(m), true);
			}
			if(forceHideAll) return true;
				
		}
		if(o == null) return true;
		var isD = o._isDisplaying;
		var buffer = '';
		for(var i=0; i<isD.length; i++){
			if(isD[i]!=null){
				if(!_.DOM.isChildOf(currentSubmenu,isD[i]) || forceHideAll){
					var submenu = _._id(isD[i]);
					var ul = _._name('ul', submenu);
					if(ul.length>0){
						_.DOM.setStyle(ul[0], {display: 'none'});
						aria.setProperty(ul[0].parentNode, 'expanded','false');
						
					} 
					isD[i] = null;
					_.DOM.removeClass(submenu, 'selected');
				}
			}
			isD = _.Array.purge(isD);
			o._isExpanded = (isD.length!=0);
			
			o._isDisplaying = isD;
		
		}
		
	},
	
	_setupSubmenu: function(menuItem){
		_.DOM.setStyle(menuItem, {
			display: 'none'
		});
		
	},
	_displaySubmenu: function(menuParent){
		var me = _.ariaMenu;
		var aria = _.ARIA;
		var o = me._identify(menuParent);
		//alert(o._isDisplaying);
		if(menuParent!=null){
			var submenu = _._name('ul', menuParent);
			if(submenu.length!=0){
				_.DOM.appendClass(menuParent,o.expandMenuItemClassName);
				if(o.onSubmenuShow!=null) o.onSubmenuShow(submenu[0]);
				for(var i=0; i<submenu.length; i++) _.DOM.setStyle(submenu[i], {display: 'none'});
				_.DOM.setStyle(submenu[0], {display: 'block'});
				aria.setProperty(menuParent,'expanded','true');
				if(!_.Array.isIn(o._isDisplaying, menuParent.id)) o._isDisplaying.push(menuParent.id);
			}
		}
		
	},
	_identify: function(currentItem){
		var menu = _.ariaMenu.menu;
		for(m in menu){
			
			if(_.DOM.isChildOf(currentItem, m)){
				return menu[m];
			}
		}
		
		return null;
	},
	/*
	------------------
	*/
	
	setup: function(menuItem, options){
		var me = _.ariaMenu;
		var aria = _.ARIA;
		if(_.Array.is(menuItem)){
			for(var i=0; i<menuItem.length; i++){
				me.setup(menuItem[i], options);
			}
		}else{
			if(menuItem.id==null || menuItem.id == '') menuItem.id = _.generateUniqueId('ariamenu');
			
			aria.addRole(menuItem, 'menubar');
			var o = JASTEggIt.Objects.clone(options);
			o._isDisplaying = [];
			o = _.mergeOptions(o, me.options);
			o.id = menuItem.id;
			me.menu[menuItem.id] = o;
			_.DOM.setStyle(menuItem, o.menuStyle);
			_.Events.add(document,'click', me._eventClickFocus);
			var cn = _._name('LI', menuItem);

			_.Events.add(cn,{
				mouseover: me._eventClickFocus,
				focus: me._eventClickFocus,
				keydown: me._eventKeyDown	
			});
			for(var i = 0; i<cn.length; i++){
				cn[i].id = _.generateUniqueId('ariamenu');
				cn[i].setAttribute('tabIndex', '0');
				
				if(_.DOM.hasClass(cn[i].parentNode, o.defaultClassName)){
					_.DOM.setStyle(cn[i].id, o.mainMenuStyle);
					_.Events.add(cn[i],'click', me._eventClick);
				}
				
				_.DOM.appendClass(cn[i],o.defaultMenuItemClassName);
				var submenu = _._name('ul', cn[i]);
				if(submenu.length!=0){
					me._setupSubmenu(submenu[0]);
					aria.addRole(cn[i],'menuitem');
					aria.setProperty(cn[i],{
						haspopup: 'true',
						expanded: 'false'
					});
				}else{
					
				} 
				var sz = _.DOM.realSize(cn[i]);
				if(_.DOM.realSize(menuItem).height<sz.height) _.DOM.setStyle(menuItem, {height: sz.height + 'px'});
			}
		}
	}
});

_.ariaMenu.options.onSubmenuShow = function(item){
	var p = item.parentNode;
	var pos = _.DOM.position(p);
	var sz = _.DOM.realSize(p);
	if(_.DOM.hasClass(p.parentNode,_.ariaMenu.options.defaultClassName)){
		_.DOM.locate(item, pos.x, p.offsetTop+p.offsetHeight);
	}else{
		var posi = _.DOM.position(item);
		var x = p.offsetLeft;
		var y = p.offsetTop;
		
		_.DOM.locate(item, x+sz.width, y);
	}
	
	_.DOM.appendClass(p,'selected');
	
}
