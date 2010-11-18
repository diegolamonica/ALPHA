/*
Script Name: 	Validator  
version: 		1.0.1
version date:	2010-03-01
Plugin for:		JAST ( http://jastegg.it )
--------------------------------------------------------------
Version | Changelog
--------+-----------------------------------------------------
1.0.1   | BUGFIX: Submit handler added, if some errors occours
		|		  the submit event on the form is aborted
--------+-----------------------------------------------------  
*/

var APPLY_TO_ITEM 	=	0;
var APPLY_TO_LABEL	=	1;
var APPLY_TO_PARENT	=	3;
_.extend('Validator',{
	forms: [],
	_temporaryRestoreItem: [],
	_temporaryErrorData: [],
	constants: {
		REQUIRED:			'required',
		EMAIL:				'email',
		NUMBER_INTEGER:		'nint',
		NUMBER_FLOAT:		'nfloat',
		LESS_THAN:			'lt_',
		GREATER_THAN:		'gt_',
		LESS_THAN_EQUAL:	'lte_',
		GREATER_THAN_EQUAL:	'gte_',
		EQUAL_TO:			'eq_',
		NOT_EQUAL_TO:		'neq_',
		OR:					'or_',
		AND:				'and_',
		XOR:				'xor_',
		DO:					'do_', 		// Executes a custom user function
		APPLY_TO_ITEM:		'apply_to_item',
		APPLY_TO_LABEL:		'apply_to_label',
		APPLY_TO_PARENT:	'apply_to_parent',
		APPLY_TEXT_TO_ID:	'apply_text_to_id_',
		APPLY_TO_ID:		'apply_to_id_',
		VALUE:				'_value_',
		EMPTY_VALUE:		'_empty_value',
		IS_DATE:			'is_date'
	},
	errorMessages:	[
	    'success!',	/* Never used */
		'Il campo %s è obbligatorio',						// 1
		'Specificare il valore per uno solo dei seguenti campi: %s',		// 2
		'Il valore di %s deve essere più piccolo di %s',	// 3
		'Il valore di %s deve essere più grande di %s',		// 4
		'Il valore di %s non può essere inferiore a %s',	// 5
		'Il valore di %s non può essere superiore a %s',	// 6
		'Il valore di %s deve essere diverso da %s',		// 7
		'Non è stata specificata una email valida',			// 8
		'Non è stato specificato un numero valido',			// 9
		'Ci sono %s problemi sul modulo, risolverli prima di effettuare il salvataggio',	// 10
		'è necessario che %s e %s siano definiti oppure nessuno dei due',					// 11
		'Il valore di %s deve essere uguale a %s',			// 12
		'Per $s bisogna specificare il valore di uno solo di essi.',	// 13
		'Manca il metodo custom %s per la validazione del campo',	// 14
		'Il valore del campo %s deve essere compatibile con le informazioni specificate',		//15
		'Non è stata specificata una data valida',					// 16
		''
	],
	_circularReference: [],
	_circularReferenceExclude: '',
	options: {
		searchByClass:	true,
		className:		'validator',
		errorClassName:	'field-error',
		successClassName:'success',
		validateOnBlur: true,
		onValidate:		null,
		onError:		null,
		onSuccess:		null,
		notifyOnSubmit:	true,
		notificationId:	null,			
		dateFormat:		null
	},
	_updateNotification: function(formId, forceNotification){
		var f = _.Validator.forms[formId];
		var e = _._class(f.errorClassName, formId);
		if(e.length>0){
			if (f.notificationId && _._id(f.notificationId)) {
				_._id(f.notificationId).innerHTML = _.Validator.getError(10, [e.length]);
				_.DOM.setStyle(f.notificationId, {display: 'block'});
			}
			else if(forceNotification) {
				alert(_.Validator.getError(10, [e.length]));
			}
		}else{
			if(f.notificationId) _.DOM.setStyle(f.notificationId, {display: 'none'});
		}
	},
	setup: function(formsId, o){
		if(!_._id(formsId)) return false;
		var options = _.Objects.clone(o);
		var opts = _.Objects.clone(_.mergeOptions(options, this.options));
		var flds = [];
		var fields = _._class(opts.className,formsId);
		
		for(i in fields){
			var f = fields[i];
			
			var fieldId = f.attributes['id'].value;
			if(fieldId==null || fieldId=='' ){
				f.setAttribute('id', _.generateUniqueId('validator'));
				fieldId = f.attributes['id'];
			}
			if(o.validateOnBlur){
				_.Events.add(fieldId, 'change', 	_.Validator.check);
				_.Events.add(fieldId, 'blur', 	_.Validator.check);
			}
			flds.push(fieldId);
		}
		opts.formsId = formsId;
		opts.fields = flds;
		opts.errors = 0;
		_.Validator.forms[formsId] = opts;
		_.Validator._updateNotification(formsId, false);
		_.Events.add(formsId,'submit', _.Validator.onSubmit);
	},
	onSubmit: function(event){
		if(event == null) event = window.event;
		var g = _.Events.generator(event);
		var theFormId =g.attributes['id'].value;
		var V = _.Validator;
		var f = V.forms[theFormId];
		
		for(var i=0; i<f.fields.length; i++) V.check(null, f.fields[i]);
		var items = _._class(f.errorClassName, theFormId);
		var l = items.length;
		if(l!=0){
			V._updateNotification(theFormId, f.notifyOnSubmit);
			_.Events.abort(event);
			return false;
		}
		if(f.onSubmit && !f.onSubmit(theFormId)){
			
			_.Events.abort(event);
			return false;
		}	
		return true;
	},
	getFormFromField: function(id){
		var V = _.Validator;
		var Vf = V.forms;
		for(f in Vf){
			var o = Vf[f];
			if(_.Array.isIn(o.fields, id)) return f;
		}
		return '';
	},
	check: function(event, id){
		var V = _.Validator;
		if(event!=null) V._circularReference = [];
		if(id==null){
			var itm = _.Events.generator(event);
			id = itm.id;
			V._circularReferenceExclude = id;
		}else{
			itm = _._id(id);
		}
		var f = V.getFormFromField(id);
		var o = V.forms[f];
		var actions = itm.className;
		if (V._circularReference[id]!=null) return V._circularReference[id];
		return V.validate(id, actions, o);
	},
	validate: function(id, actions, options){
		var V = _.Validator;
		var c = V.constants;
		var theFormId = V.getFormFromField(id);
		var options = V.forms[theFormId];
		if(options.onValidate){
			var ret = options.onValidate(id);
			if(ret===false) return;
		}
		actions = _.strings.split(actions, ' ');
		var itm = _._id(id);
		var conditions = {
				required:		_.Array.lookFor(actions, c.REQUIRED, false),
				/* Fields comparator */
				thisOrAnother:	_.Array.lookFor(actions, c.OR, true, true),
				thisAndAnother:	_.Array.lookFor(actions, c.AND, true, true),
				thisXorAnother:	_.Array.lookFor(actions, c.XOR, true),					// TODO: testare valdiazione A XOR B
				greaterThan:	_.Array.lookFor(actions, c.GREATER_THAN, true),
				lessThan:		_.Array.lookFor(actions, c.LESS_THAN, true),
				greatOrEqualTo:	_.Array.lookFor(actions, c.GREATER_THAN_EQUAL, true), 	
				lessOrEqualTo:	_.Array.lookFor(actions, c.LESS_THAN_EQUAL, true), 		
				equalTo:		_.Array.lookFor(actions, c.EQUAL_TO, true),				
				differentThan:	_.Array.lookFor(actions, c.NOT_EQUAL_TO, true),	
				/* Custom validation */
				custom:			_.Array.lookFor(actions, c.DO, true),					
				/* validator templates */
				integerNumber:	_.Array.lookFor(actions, c.NUMBER_INTEGER, true),
				floatNumber:	_.Array.lookFor(actions, c.NUMBER_FLOAT, true),
				email:			_.Array.lookFor(actions, c.EMAIL, true),				// TODO: valdiazione email 
				/* How to notify ? */
				applyToItem:	_.Array.lookFor(actions, c.APPLY_TO_ITEM, true),	
				applyToLabel:	_.Array.lookFor(actions, c.APPLY_TO_LABEL, true),	
				applyToParent:	_.Array.lookFor(actions, c.APPLY_TO_PARENT, true),	
				applyToId:		_.Array.lookFor(actions, c.APPLY_TO_ID, true),
				applyTextToId:	_.Array.lookFor(actions, c.APPLY_TEXT_TO_ID, true),
				isDate:			_.Array.lookFor(actions, c.IS_DATE, true)
		};
		var conditionsOrder= [
		    ['thisAndAnother', 	c.AND,					15],
			['required', 		c.REQUIRED,				1],
			['integerNumber', 	c.NUMBER_INTEGER,		9],
			['floatNumber', 	c.NUMBER_FLOAT,			9],
			['isDate',			c.IS_DATE,				16],
			['email',			c.EMAIL,				8],
			['custom',			c.DO,					14],
			['thisXorAnother', 	c.XOR,					2],			// TODO: da implementare (?)
			['greaterThan', 	c.GREATER_THAN,			4,	'lessOrEqualTo'],
			['lessThan', 		c.LESS_THAN,			3,	'greatOrEqualTo'],
			['greatOrEqualTo',	c.GREATER_THAN_EQUAL, 	5],
			['lessOrEqualTo',	c.LESS_THAN_EQUAL,		6],
			['equalTo',			c.EQUAL_TO,				12],
			['differentThan',	c.NOT_EQUAL_TO,			7,	'equalTo']
			
		];
		var errorCode = false;
		var restoreItem = [id];
		var errorData = [];
		for(var i=0; i<conditionsOrder.length; i++){
			var cond = conditionsOrder[i];
			if(_.Array.is(conditions[cond[0]]) && conditions[cond[0]].length>0 || !_.Array.is(conditions[cond[0]]) && conditions[cond[0]]!=-1 ) {
				var functionToCall = '_check_'+cond[0]; 
				if(cond[3]){
					functionToCall = '_check_'+cond[3]; 
				}
				errorCode = V[functionToCall](itm, cond,actions, conditions, options );
				if(cond[3] && !errorCode) errorCode = cond[2]; 
				if(errorCode){
					errorData = V.decodeLabel([id]);
					errorData = _.Array.merge(errorData, V._temporaryErrorData);
					restoreItem = _.Array.merge(restoreItem, V._temporaryRestoreItem);
					V._temporaryErrorData = [];
					V._temporaryRestoreItem = [];
					break;
				}else{
					V._temporaryErrorData = [];
					V._temporaryRestoreItem = [];
					restoreItem = _.Array.merge(restoreItem, V._temporaryRestoreItem);
				}
			}
		}
		if(_.Array.is(conditions.thisOrAnother) && conditions.thisOrAnother.length>0){
			if(errorCode = V._check_thisOrAnother(itm, ['thisOrAnother', c.OR, errorCode], actions, conditions, options)){
				restoreItem = _.Array.merge(restoreItem, V._temporaryRestoreItem);
				errorData = _.Array.merge(errorData, V._temporaryErrorData);
			}
		}
		V._temporaryErrorData = [];
		V._temporaryRestoreItem = [];
		V.notifyError(errorCode, id, options, conditions, errorData, restoreItem, itm, actions);
		V._updateNotification(theFormId, false);
		return errorCode;
	},
	_check_required: function(itm, cond,actions, conditions, options){
		var value = itm.value;
		if(itm.options && itm['selectedIndex']!=null){
			value = itm.options[itm.selectedIndex].value; 
		}
		if(itm.type.toUpperCase()=="RADIO"){
			var theForm = _._id(options.formsId);
			var itms = theForm.elements[itm.name];
			value = '';
			for(all in itms) 
				if(itms[all].checked)
					value = itms[all].value;
		}
		if(itm.type.toUpperCase()=="CHECKBOX" && !itm.checked) value='';
		if(value=='') return 1;
		return false;
	},
	_check_isDate: function(itm, cond, actions, conditions,options){
		
		var dateFormat = options.dateFormat;
		
		if(dateFormat == null) dateFormat = '(\\d+)\\/(\\d+)\/(\\d+)';
		var rx = new RegExp(dateFormat);
		var results = rx.exec(itm.value);
		if(itm.value=='') return false;
		if(results){
			if( 	(Number(results[1])>0 && Number(results[2])>0) &&
					(Number(results[1])<32 && Number(results[2])<13) )return false;
		}
		return cond[2];
	},

	_check_email: function(itm){
		var value = itm.value;
		if(value=='') return false;
		var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
		return !reg.test(value)?8:false;
	},
	_check_integerNumber: function(itm){
		if(itm.value!='' && parseInt(itm.value)+''!= itm.value) return 9;
		return false;
	},
	_check_floatNumber: function(itm){
		if(itm.value!='' && parseFloat(itm.value).toString()!= itm.value) return 9;
		return false;
	},
	_check_equalTo: function(itm, cond, actions, conditions,options){
		var V = _.Validator;
		var dataItem = V.getDataItemFromAction(actions, conditions[cond[0]], cond[1], conditions.isDate, options.dateFormat);

		var value = dataItem.value; 
		if(dataItem.type=='value'){
			V._temporaryRestoreItem = [itm.id];
			V._temporaryErrorData.push(value);
		}else{
			V._temporaryRestoreItem = [itm.id, dataItem.id];
			V._temporaryErrorData = V.decodeLabel(V._temporaryRestoreItem);
		}
		if(itm.value!=value ) return cond[2];
		return false
	},
	_check_lessOrEqualTo: function(itm, cond, actions, conditions,options){
		var V = _.Validator;
		var dataItem = V.getDataItemFromAction(actions, conditions[cond[0]], cond[1], conditions.isDate, options.dateFormat);
		var compareItem = V.decodeValue(_.Validator.constants.VALUE + itm.value, conditions.isDate, options.dateFormat);
		V._temporaryRestoreItem = [itm.id];
		V._temporaryErrorData.push(dataItem.value);
		if(compareItem.value<=dataItem.value || 
			!isNaN(compareItem.value) && !isNaN(dataItem.value) &&  (
				(conditions.integerNumber!=-1 && parseInt(compareItem.value)<=parseInt(dataItem.value)) ||
				(conditions.floatNumber!=-1 && parseFloat(compareItem.value)<=parseFloat(dataItem.value))
			)
		){
			return false;
		}else{
			return cond[2];	
		}
	},
	_check_greatOrEqualTo: function(itm, cond, actions, conditions,options){
		var V = _.Validator;
		var dataItem = V.getDataItemFromAction(actions, conditions[cond[0]], cond[1], conditions.isDate, options.dateFormat);
		var compareItem = V.decodeValue(_.Validator.constants.VALUE + itm.value, conditions.isDate, options.dateFormat);
		V._temporaryRestoreItem = [itm.id];
		V._temporaryErrorData.push(dataItem.value);
		if(compareItem.value>=dataItem.value || (parseInt('0'+compareItem.value)>=parseInt('0'+dataItem.value) && conditions.integerNumber!=-1)){
			return false;
		}else{
			return cond[2];	
		}		
	},
	_check_custom: function(itm, cond, actions, conditions,options){
		var V = _.Validator;
		var a = actions[conditions[cond[0]]];
		var dataItem = a.substring(cond[1].length);
		V._temporaryRestoreItem = [itm.id];
		V._temporaryErrorData.push(dataItem.value);
		var errorCode = false;
		if(window[dataItem]){
			var f = window[dataItem];
			errorCode = f(itm.value, itm.id);
		}else{
			errorCode = cond[2];
			V.temporaryErrorData = [dataItem];
		}
		return errorCode;
	},
	_check_thisAndAnother: function(itm, cond, actions, conditions,options){
		var taa = conditions.thisAndAnother;
		var V = _.Validator;
		V._circularReference[itm.id] = false;
		for(var i in taa){
			// Prendo l'AND
			var condizioneItem = actions[taa[i]];
			var idAnd = condizioneItem.substring(cond[1].length);
			V._circularReference[idAnd] = V.check(null, idAnd);
		}
		V._temporaryErrorData[0] = itm.id;
		for(var i in V._circularReference){
			if(V._circularReference[i]){
				return cond[2];
				break;
			}else{
				if(i!=itm.id) V._temporaryErrorData[0] +=', '+ i; 
			}
		}
		return false;
	},
	_check_thisOrAnother: function(itm, cond, actions, conditions,options){
		var taa = conditions.thisOrAnother;
		var V = _.Validator;
		V._circularReference[itm.id] = cond[2];
		for(var i in taa){
			// Prendo l'OR
			var condizioneItem = actions[taa[i]];
			var idOr = condizioneItem.substring(cond[1].length);
			V._circularReference[idOr] = V.check(null, idOr);
		}
		V._temporaryErrorData[0] = itm.id;
		var isSomeoneInError = true;
		for(var i in V._circularReference){
			isSomeoneInError = isSomeoneInError && (V._circularReference[i]!==false);
			V._temporaryErrorData.push(i);
			V._temporaryRestoreItem.push(i);
		}
		if(isSomeoneInError){
			V._temporaryRestoreItem= [];
			return cond[2];
		}else{
			V._temporaryErrorData = [];
			return false;
		} 
	},
	notifyError: function(errorCode, id, options, conditions, errorData, restoreItem, itm, actions){
		var V = _.Validator;
		var ecn = options.errorClassName;
		if(errorCode){
			if(options.onError){
				var ret = options.onError(id);
				if(ret===false) return false;
			}
			switch(true){
			case conditions.applyToLabel!=-1:
				var lbl = _.DOM.labelOf(id, options.formId);
				lbl.title = V.getError(errorCode, errorData);
				_.DOM.appendClass(lbl, ecn);
				break;
			case conditions.applyToParent!=-1:
				var itmx = itm.parentNode;
				itmx.title = V.getError(errorCode, errorData);
				_.DOM.appendClass(itmx, ecn);
				break;
			case conditions.applyTextToId!=-1:
				var a = actions[conditions.applyTextToId];
				a = a.substring(V.constants.APPLY_TEXT_TO_ID.length);
				var itmx = _._id(a);
				 if(itmx!=null){
					 itmx.innerHTML = V.getError(errorCode, errorData);
					 _.DOM.appendClass(itmx, ecn);
					 break;
				 }
			case conditions.applyToId!=-1:
				var a = actions[conditions.applyToId];
				a = a.substring(V.constants.APPLY_TO_ID.length);
				var itmx = _._id(a);
				 if(itmx!=null){
					 itmx.title = V.getError(errorCode, errorData);
					 _.DOM.appendClass(itmx, ecn);
					 break;
				 }
			case APPLY_TO_ITEM:
			default:
				itm.title = V.getError(errorCode, errorData);
				_.DOM.appendClass(restoreItem, ecn);
				break;
			}
			return false;
		}else{
			if(options.onSuccess){
				var ret = options.onSuccess(id);
				if(ret===false) return false;
			}
			switch(true){
			case conditions.applyToLabel!=-1:
				restoreItem =  _.DOM.labelOf(id, options.formId);
				break;
			case conditions.applyToParent!=-1:
				restoreItem =  itm.parentNode;
				break;
			case conditions.applyToId!=-1:
				var a = actions[conditions.applyToId];
				a = a.substring(V.constants.APPLY_TO_ID.length);
				restoreItem =  a;
				break;
			}
			
			_.DOM.removeClass(restoreItem, ecn);
			return true;
		}
	},
	getDataItemFromAction:function(actions, condition, constant, isDate, dateFormat){
		var a = actions[condition];
		var dataItem = a.substring(constant.length);
		return _.Validator.decodeValue(dataItem, isDate, dateFormat);
	},
	decodeValue: function(dataValue, isDate, dateFormat){
		if(isDate==null) isDate = false;
		c = _.Validator.constants;
		var t = 'value';
		if(dataValue.substr(0, c.VALUE.length)== c.VALUE){
			var v = dataValue.substr(c.VALUE.length);
		}else if(dataValue.substr(0, c.EMPTY_VALUE.length)== c.EMPTY_VALUE){
			var v = '';
		}else{
			var v = _._id(dataValue).value;
			var t = 'node';
		}
		if(isDate){
			if(dateFormat == null) dateFormat = '(\\d+)\\/(\\d+)\/(\\d+)';
			var rx = new RegExp(dateFormat);
			var results = rx.exec(v);
			if(results) v = results[3]+'-'+results[2]+'-'+results[1];
		}
		return {
			id:		dataValue,
			value: 	v,
			type:	t	
		};
	},
	decodeLabel: function(i){
		var id = _.Objects.clone(i);
		if(!_.Array.is(id)) id = [id];
		var l = JASTEggIt._name('label');
	  	
		for(var i=0; i<l.length; i++){
			var lbl = l[i];
			var lf = _.Array.lookFor(id, lbl.attributes['for'].value,false);
			var buffer = lbl.innerHTML;
			if(lf!=-1) id[lf] = buffer.replace(/[:.\n\r\t]*$/i,'');
		}
		return id;
	},
	getError: function(errorCode, data){
		var e = _.Validator.errorMessages[errorCode];
		for(i in data) e = e.replace('\%s',data[i]);
		return e;
	}
});