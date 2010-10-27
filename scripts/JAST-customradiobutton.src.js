/*
Script Name: 	CustomRadioButton (http://jastegg.it/eggs/CustomRadioButton/ ) 
version: 		1.0
version date:	2007-10-19
Plugin for:		JAST ( http://jastegg.it )
--------------------------------
*/
JASTEggIt.extend(
	'CustomRadioButton', {
		info: {
			title: 		'Custom Radio Button',
			version:	'1.0',
			eggUrl:		'http://jastegg.it/eggs/CustomRadioButton',
			author:		'Diego La Monica',
			url:		'http://diegolamonica.info'
		},
	  	name: '',
	  	_radio: Array(),
	  	options:{
		    checkedClassName: 'radio-checked',
		    uncheckedClassName: 'radio-unchecked',
		   	radioButtonClassName: 'radiobutton'
		},
		onchangeevent: function(id){
			var j = JASTEggIt;
			var crb = j.CustomRadioButton;
			var o = crb.options;
			var radio = j._id(id);
			var lbl = j._id(this._radio[id]);
			if(radio.checked){
				j.DOM.appendClass(lbl,o.checkedClassName);
				j.DOM.removeClass(lbl,o.uncheckedClassName);
			}else{
				j.DOM.appendClass(lbl,o.uncheckedClassName);
				j.DOM.removeClass(lbl,o.checkedClassName);
				
			}
			// lbl.className=(radio.checked?JASTEggIt.CustomRadioButton.options.checkedClassName:JASTEggIt.CustomRadioButton.options.uncheckedClassName);
		},
		startup: function(){
		  	var l = JASTEggIt._name('label');
		  	for(i=0; i<l.length; i++){
			    var f = l[i].attributes['for'].value;
				var c = null;
				if(f!=null) c = JASTEggIt._id(f);
			    if(c!=null && c.type.toUpperCase() == 'RADIO'){
				    if(l[i].id==null || l[i].id=='') l[i].id = this.name + f;
					
			      	//eval("l[i].onclick = function(){ JASTEggIt['" + this.name + "'].onclickevent('" + l[i].id + "'); }");
					JASTEggIt.Listener.watch(c.id, 'checked', function(itm){
						JASTEggIt.CustomRadioButton.onchangeevent(itm.id);
					});
					//eval("c.onkeypress = function(event){ JASTEggIt['" + this.name + "'].onkeypressevent( JASTEggIt['" + this.name + "']._radio[this.id], event);}");
					JASTEggIt.DOM.appendClass(l[i],(c.checked?this.options.checkedClassName:this.options.uncheckedClassName));
					_.DOM.appendClass(c.id, this.options.radioButtonClassName)
					//c.className = this.options.radioButtonClassName;
				    this._radio[f] = l[i].id; //Associo a ciascun radiobutton una label
				    this._radio[l[i].id] = f; //Associo a ciascun radiobutton una label
				}
			}
		}
	}
)