/*
Script Name: 	Step Wizard (http://jastegg.it/eggs/stepwizard/ )
Author:			Diego La Monica 
version: 		1.3 
version date:	2009-11-19
Plugin for:		JAST ( http://jastegg.it )
--------------------------------
Change log:
1.2 (2008-07-20): 
	aggiunto metodo che preleva dal primo nodo figlio il titolo dello step nel caso in cui
	l'elemento che fa da container per lo step wizard non disponga di un suo titolo (attributo title)

1.3 (2009-11-19):
	controllo l'hash a cui punta la pagina per verificare se è un tab da attivare.
	Se l'ancora ricevuta contiene degli \ (es. #hash1\hash2 ) viene interpretata come ancora gerarchica e ricercherà
	il tab che corrisponde all'hash1 e se trovato verrà rimosso l'hash1 dall'hash che verrà trasformato in
	#hash2.
	
--------------------------------
*/

JASTEggIt.extend('StepWizard', {
	options: {
		formId:						'',
		startFrom:					0,
		nextButton: 				true,
		prevButton: 				true,
		replaceSubmit: 				true,
		stepListClassName:			'step-list',
		stepListCurrentClassName:	'current',
		buttonContainerClassName: 	'',
		buttonClassName: 			'',
		buttonPreviousClassName:	'',
		buttonPreviousText:			'Previous',
		buttonNextClassName:		'',
		buttonNextText:				'Next',
		isStepClickable:			false
	},
	_checkArrow: function(event,id, i){
		var jast = JASTEggIt;
		if(event==null) event = window.event;
		
		var f = jast._id(id);
		var o = f.options;
		
		var keynum = jast.kbd.getKeyPressed(event);
		if(keynum==37 && i>0) jast.StepWizard.goTo(id, i-1);
		if(keynum==39 && i< o._elementsId.length-1) jast.StepWizard.goTo(id, i+1);
		
	},
	setup: function(elementsId, options ){
		var jast = JASTEggIt;
		var dom = jast.DOM;
		var aria = jast.ARIA;
		var events = jast.Events;
		
		options = jast.mergeOptions(options, this.options);
		if(options.formId=='' || _._id(options.formId)==null) return false;
		//Disegna prima del primo elemento l'elenco puntato
		var ul = dom.createOnDocument('ol', elementsId[0], null);
		options._ulId = ul.id;
		options._elementsId = elementsId;
		
		dom.appendClass(ul, options.stepListClassName);
		aria.addRole(ul,'tablist');
		// Se nell'anchor della pagina è presente uno degli elementi devo usarlo come riferimento primario per lo "start from"
		var h = window.location.hash;
		var hashIndex = h.indexOf('\\');
		if(hashIndex!=-1){
			h = h.substring(0, hashIndex);
		}
		
		for(var i=0; i<elementsId.length; i++){
			var e = jast._id(elementsId[i]);
			if(h == '#' + elementsId[i]){
				options.startFrom = i;
				if(hashIndex!=-1) window.location.hash = window.location.hash.substring(hashIndex+1);
				break;
			}
		}
		for(var i=0; i<elementsId.length; i++){
			// Crea gli elementi dell'elenco puntato
			var e = jast._id(elementsId[i]);
			aria.addRole(e,'tabpanel');
			if(options.startFrom==null && (('#'+elementsId[i]) == window.location.hash)) options.startFrom = i;
			if(options.panelsClassName!=null && options.panelsClassName!=''){
				dom.appendClass(e, options.panelsClassName);
			}
			var t = e.title;
			
			if(t == '' || t==null) 
				for(var j=0; j<e.childNodes.length; j++)
					if(e.childNodes[j].innerHTML!=null){
						t = '<nobr>' + e.childNodes[j].innerHTML +'</nobr>';
						break;
					};

			var li = dom.createChild('li', ul);
			aria.addRole(li,'tab');
			li.id = jast.generateUniqueId('stepWizard');
			aria.setProperty(e, 'labelledby',li.id);
			if(options.isStepClickable){
				events.add(li, 'click', 'JASTEggIt.StepWizard.goTo(\'' + options.formId + '\', ' + i + '); ');
				events.add(li, 'keyup', 'JASTEggIt.StepWizard._checkArrow(event, \'' + options.formId + '\', ' + i + ');');
				li.innerHTML = t;
				li.tabIndex=-1;
				
			}else{
				li.innerHTML = t;
			}
			
			
			// nasconde tutti gli step tranne quello attuale
			if(i!=options.startFrom){
				dom.setStyle(e, {
					display: 'none'
				});
				aria.setProperty(li,'selected','false');
				aria.setProperty(e, 'hidden','true');
			}else{
				dom.appendClass(li, options.stepListCurrentClassName);
				li.tabIndex = 0;
				aria.setProperty(li,'selected','true');
				aria.setProperty(e,'hidden','false');

			}
		}
		
		// Disegna il next e il prev dopo l'ultimo elemento della lista
		
		var last = elementsId[elementsId.length-1];
		var div = dom.createOnDocument('div', null, last);

		if(options.buttonContainerClassName!=''){
			dom.appendClass(div, options.buttonContainerClassName);
		}
		var linkPrev = dom.createChild('a', div);
		var linkNext = dom.createChild('a', div);
		if(options.buttonClassName!=''){
			dom.appendClass(linkPrev, options.buttonClassName);
			dom.appendClass(linkNext, options.buttonClassName);
		}
		if(options.buttonPreviousClassName!=''){
			dom.appendClass(linkPrev, options.buttonPreviousClassName);
		}
		if(options.buttonNextClassName!=''){
			dom.appendClass(linkNext, options.buttonNextClassName);
		}
		
		linkPrev.href= 'javascript:JASTEggIt.StepWizard.goPrevious("' + options.formId + '")';
		linkNext.href= 'javascript:JASTEggIt.StepWizard.goNext("' + options.formId + '")';
		linkPrev.innerHTML = options.buttonPreviousText;
		linkNext.innerHTML = options.buttonNextText;
		var f = jast._id(options.formId);
		if(options.replaceSubmit){
			var inputs = jast._name('input', f);
			
			for(var i=0; i<inputs.length; i++){
				if(inputs[i].type=='submit'){
					dom.setStyle(inputs[i], {
						display: 'none'
						
					});
					inputs[i].onclick= function(){return false;}
				}
				
			}
		
		}
		f.options = options;
		
	},
	goTo: function(formId, index){
		var jast = JASTEggIt;
		var aria = jast.ARIA;
		var dom = jast.DOM;
		
		var f = jast._id(formId);
		var o = f.options;

		 var ul = jast._id(o._ulId);
		 var li = jast._name('li', ul);
		 dom.removeClass(li[o.startFrom], o.stepListCurrentClassName );
		 
		 dom.setStyle(o._elementsId[o.startFrom], {display: 'none'});
		 aria.setProperty(jast._id(o._elementsId[o.startFrom]), 'hidden','true');
		 li[o.startFrom].tabIndex=-1;
		 
		 aria.setProperty(li[o.startFrom], 'selected','false');
		 o.startFrom= index;
		 dom.appendClass(li[o.startFrom], o.stepListCurrentClassName );
		 dom.setStyle(o._elementsId[o.startFrom], {display: 'block'});
		 aria.setProperty(li[o.startFrom], 'selected','true');
		 aria.setProperty(jast._id(o._elementsId[o.startFrom]), 'hidden','false');
		 li[o.startFrom].focus();
		 li[o.startFrom].tabIndex=0;
		 if(o.onStep) o.onStep(formId,index);
		 
	},
	goNext: function(formId){
		
		var jast = JASTEggIt;
		var dom = jast.DOM;
		
		 var f = jast._id(formId);
		 var o = f.options;

		if(o.startFrom==o._elementsId.length-1 && o.replaceSubmit){
			if(f.onsubmit==null || f.onsubmit!=null && f.onsubmit()) f.submit();
		}else{
			 var ul = jast._id(o._ulId);
			 var li = jast._name('li', ul);
			 dom.removeClass(li[o.startFrom], o.stepListCurrentClassName );
			 dom.setStyle(o._elementsId[o.startFrom], {display: 'none'});
			 o.startFrom+=1;
			 dom.appendClass(li[o.startFrom], o.stepListCurrentClassName );
			 dom.setStyle(o._elementsId[o.startFrom], {display: 'block'});
		}
		
	},
	goPrevious: function(formId){
		
		var jast = JASTEggIt;
		var dom = jast.DOM;
		
		 var f = jast._id(formId);
		 var o = f.options;
		 if(o.startFrom != 0){
			var ul = jast._id(o._ulId);
			var li = jast._name('li', ul);
			dom.removeClass(li[o.startFrom], o.stepListCurrentClassName );
			dom.setStyle(o._elementsId[o.startFrom], {display: 'none'});
			o.startFrom-=1;
			dom.appendClass(li[o.startFrom], o.stepListCurrentClassName );
			dom.setStyle(o._elementsId[o.startFrom], {display: 'block'});
		 }
	}
})