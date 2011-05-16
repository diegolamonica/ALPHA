_.extend('AjaxSubmit', {
	options: {
		onBeforeSubmit: null,
		onDataSent:		null,
		onDone:			null,
		onAbort:		null,
		ajaxParams:		[]
	},
	theForms: [],
	setup: function(id, options){
		
		options = _.mergeOptions(options, _.AjaxSubmit.options);
		var V = _.Validator;
		var A = _.AjaxSubmit;
		if(V!=null && V.forms[id]!=null){
			if(V.forms[id].onSubmit!=null) options.validatorSubmit = V.forms[id].onSubmit;
			V.forms[id].onSubmit = A.doSubmit;
		}else{
			_.Events.add(id, 'submit', A.doSubmit);
		}
		A.theForms[id] = options;
		
	},
	doSubmit: function(event){
		if(event == null) event = window.event;
		var g = null;
		if(typeof(event)=='string'){
			g = _._id(event);
		}else{
			g = _.Events.generator(event);
		}
		var A = _.AjaxSubmit;
		var formId = g.attributes["id"].value;
		var f = A.theForms[formId];
		if(!f.validatorSubmit || f.validatorSubmit && f.validatorSubmit(event)){
			var e = g.elements;
			var theFields = [];
			for(var i = 0; i<e.length; i++){
				var el =e[i];
				var eln = el.name;
				if(eln!=''){
					switch(el.tagName.toUpperCase()){
						case 'FIELDSET':
							break;
						case 'TEXTAREA':
							theFields = A.addValue(theFields, eln,  (el.value!=null)?el.value:el.innerHTML);
							break;
						default:
							switch(el.type.toUpperCase()){
								case 'RADIO':
								case 'CHECKBOX':
									if(el.checked) theFields = A.addValue(theFields, eln,  el.value);
									break;
								case 'TEXT':
								default:
									theFields = A.addValue(theFields, eln,  el.value);
							}
							break;
					}
				}
			}
			if(!f.onBeforeSubmit || f.onBeforeSubmit && f.onBeforeSubmit(formId)){
				theFields = _.mergeOptions(theFields, f.ajaxParams, true);
				_.xhttp.sendRequest(g.attributes['method'].value.toUpperCase(), g.attributes["action"].value, theFields, _.AjaxSubmit.done, formId);
				
				if(f.onDataSent) f.onDataSent(formId);
			}
		}
		if(!f.onAbort || f.onAbort && f.onAbort(formId)){
			_.Events.abort(event);
			return false;
		}
		return true;
	},
	addValue: function(theFields, key, value ){
		//value = escape(value);
		if(key.substr(key.length-2,2) == '[]'){
			if(!_.Array.is(theFields[key])) theFields[key] = [];
			theFields[key].push(value);
		}else{
			theFields[key] = value;
		}
		return theFields;
	} ,
	done: function(buffer, id){
		var A = _.AjaxSubmit;
		var f = A.theForms[id];
		if(f.onDone) f.onDone(id, buffer);
	}
});