JASTEggIt.extend('Tabs',
{
	options:	{
		selectedStyle:	{
			backgroundColor: '#ffe',
			color: '#00a',
			border: '1px solid #00a',
			borderBottom: '0px',
			padding: '5px',
			cursor: 'pointer'
		},
		unselectedStyle:	{
			backgroundColor: '#ccb',
			color:	'#008',
			border: '1px solid #008',
			borderBottom: '0px',
			padding: '5px',
			cursor: 'pointer'
		},
		clickAction: null
	},
	_selectedItem: null, 
	setup: function (id, options){
		
		var e = JASTEggIt._id(id);
		if(e==null) return false;
		
		options = JASTEggIt.mergeOptions(options,this.options);
		e.options = options;
		var ul = JASTEggIt._name('ul', e)[0];
		var li = JASTEggIt._name('li', ul);
		JASTEggIt.DOM.setStyle(ul, {
			listStyle: 'none',
			margin: '0',
			padding: '0'
		});
		for(var i = 0; i< li.length; i++){
			var itm = li[i];
			if(itm.id == null || itm.id == '') itm.id = JASTEggIt.generateUniqueId('tabs');
			JASTEggIt.DOM.setStyle(itm, {
				display: 	'inline',
				listStyle: 	'none',
				textAlign: 'right'
			});
			
			var a = JASTEggIt._name('a', itm);
			if(a.length>0){
				a = a[0];
				if(a.id == null || a.id == '') a.id = JASTEggIt.generateUniqueId('tabs');
				
				if(window.location.href == a.href){
					this.doSelect(itm, options.selectedStyle, options.unselectedStyle);
				}else
					JASTEggIt.DOM.setStyle(itm, options.unselectedStyle);
			}else
				JASTEggIt.DOM.setStyle(itm, options.unselectedStyle);
			JASTEggIt.Accessibility.clickEvent(itm.id, 'JASTEggIt.Tabs.doClick("' + id + '","' + itm.id + '","' + a.id + '")' );
		}
	},
	doSelect: function(itm, style, unstyle){
		if( this._selectedItem!=null ){
			var oldItem = _._id(this._selectedItem);
			JASTEggIt.DOM.setStyle(oldItem, unstyle);
		}
		JASTEggIt.DOM.setStyle(itm, style);
		this._selectedItem = itm.id;
	},
	
	doClick: function(divId, liId, aId){
		var div = JASTEggIt._id(divId);
		var li = JASTEggIt._id(liId);
		var a  = JASTEggIt._id(aId);
		var o = div.options;
		var oldHref = null;
		if(this._selectedItem != null){
			var oldLi = _._id(this._selectedItem);
			var oldA = _._name('a', oldLi);
			if(oldA.length>0) oldHref = oldA[0].href;	
		};
		var navigate = true;
		if(o.clickAction!=null) navigate = o.clickAction(a.href, oldHref);
		this.doSelect(li, o.selectedStyle, o.unselectedStyle);
		if(navigate) window.location.href = a.href; 
		 
		
	}
}
);