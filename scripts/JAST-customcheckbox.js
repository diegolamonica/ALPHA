/*
Script Name: 	CustomCheckBox (http://jastegg.it/eggs/CustomCheckBox/ ) 
version: 		1.0
version date:	2007-10-19
Plugin for:		JAST ( http://jastegg.it )
--------------------------------
*/
JASTEggIt.extend('CustomCheckBox',{info:{title:'Custom Check Box',version:'1.0',eggUrl:'http://jastegg.it/eggs/CustomCheckBox',author:'Diego La Monica',url:'http://diegolamonica.info'},name:'',_checks:Array(),_extend:function(key,object){window['a-js']._extend(key,object,this);},options:{checkedClassName:'checked',uncheckedClassName:'unchecked',checkBoxClassName:'checkbox'},onkeypressevent:function(id,event){var obj=JASTEggIt._id(id);if(obj.realkeypress!=null)obj.realkeypress(event);var keynum=JASTEggIt.kbd.getKeyPressed(event);if(keynum==13||keynum==32)obj.onclick();},onclickevent:function(id){var obj=JASTEggIt._id(id);var chk=JASTEggIt._id(this._checks[id]);obj.className=(chk.checked?'unchecked':'checked');},onchangeevent:function(id){var chk=JASTEggIt._id(id);var lbl=JASTEggIt._id(this._checks[id]);lbl.className=(chk.checked?'checked':'unchecked');},startup:function(){var l=JASTEggIt._name('label');for(i=0;i<l.length;i++){var f=l[i].attributes['for'].value;var c=JASTEggIt._id(f);if(f!=null&&c.type.toUpperCase()=='CHECKBOX'){if(l[i].id==null||l[i].id=='')l[i].id=this.name+f;eval("l[i].onclick = function(){ JASTEggIt['"+this.name+"'].onclickevent('"+l[i].id+"'); }");JASTEggIt.Listener.watch(c.id,'checked',function(itm){JASTEggIt.CustomCheckBox.onchangeevent(itm.id);});eval("c.onkeypress = function(event){ JASTEggIt['"+this.name+"'].onkeypressevent( JASTEggIt['"+this.name+"']._checks[this.id], event);}");l[i].className=(c.checked?this.options.checkedClassName:this.options.uncheckedClassName);c.className=this.options.checkBoxClassName;};this._checks[f]=l[i].id;this._checks[l[i].id]=f;}}})