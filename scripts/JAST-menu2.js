JASTEggIt.extend('menu', {
	info:	{
		title:	'Menu',
		version: 1.0,
		eggUrl:	'http://jastegg.it/eggs/menu',
		author:	'Diego La Monica',
		url:	'http://diegolamonica.info'
	},
	options:	{
		expandTo: 		1, // level up to which you want your lists to be initially expanded. 1 is minimum
		listClass: 		'.jast-menu',
		expandedClass: 	'menu-collapse',
		collapsedClass:	'menu-expand'
	},
	
	startup: function(){
		var ul = JASTEggIt._get(this.options.listClass);
		for (var i=0;i<ul.length;i++){
			this.setup(ul[i]);
		};
	},
	
	setup: function(list) {	
		var items = JASTEggIt._name("li", list);
		for(var i=0;i<items.length;i++){
			this.listItem(items[i]);
		};
	},
	listItem: function(li){
		if(JASTEggIt._name('ul',li).length > 0){
			var ul = JASTEggIt._name('ul',li)[0];
			if(ul.id == null || ul.id == '') ul.id = JASTEggIt.generateUniqueId('menu'); 
			if(li.id == null || li.id == '') li.id = JASTEggIt.generateUniqueId('menu'); 
			JASTEggIt.DOM.setStyle(ul,{
				display: (this.depth(ul) <= this.options.expandTo) ? "block" : "none"
			});
			li.className = (this.depth(ul) <= this.options.expandTo) ? this.options.expandedClass : this.options.collapsedClass;
			li.tabIndex= '0';
			li.over = true;
			JASTEggIt.Events.add(ul.id, 'mouseover', function(){li.over = false;});	
			JASTEggIt.Events.add(ul.id, 'mouseout', function(){li.over = true;});
			JASTEggIt.Events.add(li.id, 'keypress', function(event){
				var itm = JASTEggIt.Events.generator(event);
				if (itm==null ) return false;

				var key = JASTEggIt.kbd.getKeyPressed(event);
				if(
					(key == '+'.charCodeAt(0) && itm.className == JASTEggIt.menu.options.collapsedClass) ||
					(key == '-'.charCodeAt(0) && itm.className == JASTEggIt.menu.options.expandedClass) ||
					(key == 13) 
				){
					if(itm.over){
						//if(ul.style.display == "none")  JASTEggIt.fx.resize(ul.id, {width:0, height: 0}, ul.realSize, 10, 10 );
						ul.style.display = (ul.style.display == "none") ? "block" : "none";
						this.className = (ul.style.display == "none") ? JASTEggIt.menu.options.collapsedClass: JASTEggIt.menu.options.expandedClass;				
					}	
				};
			} );
			JASTEggIt.Events.add(li.id, 'click',function(event){
				var id = '';
				var e = JASTEggIt.Events.generator(event);
				if(e.over){
					//if(ul.style.display == "none")  JASTEggIt.fx.resize(ul.id, {width:0, height: 0}, ul.realSize, 10, 10 );
					ul.style.display = (ul.style.display == "none") ? "block" : "none";
					this.className = (ul.style.display == "none") ? JASTEggIt.menu.options.collapsedClass: JASTEggIt.menu.options.expandedClass;				
				}
			});
		};		
	},
	depth: function(obj){
		var level = 1;
		while("." + obj.parentNode.className != this.options.listClass){
			if (obj.tagName == "UL") level++;
			obj = obj.parentNode;
		};
		return level;
	}

});
