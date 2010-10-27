/*
Script Name: 	Menu (http://jastegg.it/eggs/menu/ ) 
version: 		1.0 alpha
version date:	2007-10-19
Plugin for:		JAST ( http://jastegg.it )
--------------------------------
*/
JASTEggIt.extend(
	'menu', {
		info: {
			title: 		'Menu',
			version:	'1.0 alpha',
			eggUrl:		'http://jastegg.it/eggs/menu',
			author:		'Diego La Monica',
			url:		'http://diegolamonica.info'
		},
		options: {
			classIdentifier: 	'.jast-menu',
			menuClassName:		'jast-menu-item',
			submenuClassName:	'jast-sub-menu-item',
			expandLabel:		'espandi',
			collapseLabel:		'comprimi',
			expandClassName:	'menu-expand',
			collapseClassName:	'menu-collapse'
		},
		
		startup: function(){
			var lists = JASTEggIt._get(this.options.classIdentifier);
			for(var i=0; i<lists.length; i++){
				var l = lists[i];
				if(l.id==null || l.id=='') l.id = JASTEggIt.generateUniqueId('mnu');
				this.setup(l.id);
			}
			
		},
		
		setup: function(id, options){
			options = JASTEggIt.mergeOptions(options, this.options);
			
			var ul = JASTEggIt._id(id);
			for(var i = 0; i <ul.childNodes.length; i++){
				var li = ul.childNodes[i];
				if(li.nodeType!=3 && li.tagName=='LI'){
					if(li.id == null || li.id=='') li.id = JASTEggIt.generateUniqueId('mnu');
					li.className = options.menuClassName;
					var subUl = li.getElementsByTagName('ul');
					if(subUl.length > 0){
						var a_id = JASTEggIt.generateUniqueId('lnk');
						li.innerHTML = '<a id="' + a_id + '" class="' + options.expandClassName + '" href="#" onclick="return JASTEggIt.menu.click(\'' + li.id + '\',\'' + a_id + '\');"  onkeypress="return JASTEggIt.menu.keypress(\'' + li.id + '\',\'' + a_id + '\' ,event);" title="' + escape(options.expandLabel) + '" >' + options.expandLabel + '</a>' + li.innerHTML;
						var subUl = li.getElementsByTagName('ul');
						subUl = subUl[0];
						if(subUl.id == null || subUl.id=='') subUl.id = JASTEggIt.generateUniqueId('ul');
						subUl.realSize = JASTEggIt.DOM.realSize(subUl);
						subUl.style.display = 'none';
						JASTEggIt._id(li.id).options = options;
					}
				}	
			}
		},
		click: function(liid,aid){
			var li = JASTEggIt._id(liid);
			var a = JASTEggIt._id(aid);
			var o = li.options;
			if(a.className == o.expandClassName){
				aid.className = o.collapseClassName;
				//var ul = li.getElementsByTagName('ul')[0];
				var ul = JASTEggIt._name('UL',li)[0];
				
				JASTEggIt.fx.resize(ul.id, {width:0, height: 0}, ul.realSize, 10, 10 );
				ul.style.display ='';
				a.title = o.collapseLabel;
				a.className = o.collapseClassName;
				a.innerHTML = o.collapseLabel;
			}else{
				var ul = JASTEggIt._name('UL',li)[0];
				JASTEggIt.fx.queue(
					[
						[ul.id,function(id,q){ JASTEggIt.fx.resize(id, ul.realSize, {width:0, height: 0}, 10, 10,q );}],
						[ul.id,function(id,q){ JASTEggIt._id(id).style.display='none'; JASTEggIt.fx.queueDone(q);}]
					]
				);
				a.title = o.expandLabel;
				a.className = o.expandClassName;
				a.innerHTML = o.expandLabel;
			};
			return false;
		},
		keypress: function(liid,aid,event){
			var kp = JASTEggIt.kbd.getKeyPressed(event);
			if(kp == 13 || kp==32) return this.click(liid,aid);
			return true;
		}
	}
);