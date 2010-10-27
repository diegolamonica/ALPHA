/*
Script Name: 	CustomCheckBox (http://jastegg.it/eggs/CustomCheckBox/ ) 
version: 		1.1
version date:	2007-10-19
Plugin for:		JAST ( http://jastegg.it )
--------------------------------
Changelog:

2009-12-01:		1.1 	bugfix: label loses the defined html class attribute.
2007-10-19:		1.0 	First release
*/
JASTEggIt.extend(
	'CustomCheckBox', {
		info: {
			title: 		'Custom Check Box',
			version:	'1.0',
			eggUrl:		'http://jastegg.it/eggs/CustomCheckBox',
			author:		'Diego La Monica',
			url:		'http://diegolamonica.info'
		},
	  	name: '',
	  	_checks: Array(),
	  	_extend: function(key, object){ window['a-js']._extend(key, object, this); },
	  	options:{
		    checkedClassName: 'checked',
		    uncheckedClassName: 'unchecked',
		    checkBoxClassName: 'checkbox'
		},
		onclickevent: function (id){
			var _ = JASTEggIt;
			var obj = _._id(id);
			var o = this.options;
			var chk = _._id(this._checks[id]);
			_.DOM.appendClass(obj, (chk.checked?o.uncheckedClassName:o.checkedClassName));
			_.DOM.removeClass(obj, (chk.checked?o.checkedClassName:o.uncheckedClassName));
			obj.setAttribute('aria-checked', chk.checked?'true':'false');
			
		},
		onchangeevent: function(id){
			var _ = JASTEggIt;
			var o = this.options;
			var chk = _._id(id);
			var lbl = _._id(this._checks[id]);
			_.DOM.appendClass(lbl, (chk.checked?o.checkedClassName:o.uncheckedClassName));
			_.DOM.removeClass(lbl, (chk.checked?o.uncheckedClassName:o.checkedClassName));
			lbl.setAttribute('aria-checked', chk.checked?'true':'false');
		},
		startup: function(){
			var _ = JASTEggIt;
		  	var l = _._name('label');
		  	for(i=0; i<l.length; i++){
			    var f = l[i].attributes['for'].value;
				var c = null;
				if(f!=null) c = _._id(f);
				
			    if(c!=null && c.type.toUpperCase() == 'CHECKBOX'){
				    if(l[i].id==null || l[i].id=='') l[i].id = this.name + f;
			      	eval("l[i].onclick = function(){ JASTEggIt['" + this.name + "'].onclickevent('" + l[i].id + "'); }");
					_.Listener.watch(c.id, 'checked', function(itm){
						_.CustomCheckBox.onchangeevent(itm.id);
					});
					
					l[i].setAttribute('role','checkbox');
					l[i].setAttribute('aria-checked', c.checked?'true':'false');
					var e = _.Events;
					e.add(c, 'focus', 
						function(event){ 
							var c = _.Events.generator(event); 
							var ccb = _.CustomCheckBox;
							_._id(ccb._checks[c.id]).style.border="1px dotted #ccc";
						});
					e.add(c, 'blur', function(event){ 
						var c = _.Events.generator(event); 
						var ccb = _.CustomCheckBox;
						_._id(ccb._checks[c.id]).style.border="1px solid #fff";
						});
					var d = _.DOM;
					var o = this.options;
					if(c.checked){
						d.appendClass(l[i], o.checkedClassName );
						d.removeClass(l[i], o.uncheckedClassName );
					}else{
						d.appendClass(l[i], o.uncheckedClassName );
						d.removeClass(l[i], o.checkedClassName );
					}
					d.appendClass(c,  o.checkBoxClassName);
					
				    this._checks[f] = l[i].id; //Associo a ciascun checkbox una label
				    this._checks[l[i].id] = f; //Associo a ciascun checkbox una label
				}
			}
		}
	}
)