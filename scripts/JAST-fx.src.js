/*
Script Name: 	FX (http://jastegg.it/eggs/fx ) 
version: 		1.1.2 beta
version date:	2008-06-03
Plugin for:		JAST ( http://jastegg.it )
--------------------------------
*/

var FX_RESIZE_ORIGINAL 	= 'original';
var FX_SCROLL_TOP		= 'top';
var FX_SCROLL_LEFT		= 'left';
JASTEggIt.extend(
	'fx', {
		info: {
			title: 		'FX - Effects',
			version:	'1.1 beta',
			eggUrl:		'http://jastegg.it/eggs/fx',
			author:		'Diego La Monica',
			url:		'http://diegolamonica.info'
		},
		_fx: [],
	  	_queueCommands: null,
		_createEffect: function(options, queueIndex){
			var i = this._fx.length;
			options.done = false;
			this._fx[i] = options;
			if(queueIndex!=null) this._queueCommands[queueIndex].idx = i;
			return i;
		},
		_isPaused: function(obj){
			if(obj.paused!=null)
				return obj.paused;
			else
				return false;
		},
	  	_queue: function(checkFrom){
			if(checkFrom == null) checkFrom = 0; 
	  	  	var q = _.fx._queueCommands;
	  		if(q==null) return false;
	  	  	for(var i = checkFrom; i<q.length; i++){
	  	  		var idx = i;
	  	  	  	var itm = q[idx];
	  	  	  	if(itm){
					// Ho richiamato la funzione, ma non e' ancora terminata l'esecuzione
					if(itm.called && itm.idx!=-1 && _.fx._fx[itm.idx].done == false) break;
					if(itm.idx!=-1 && _.fx._fx[itm.idx].done){
						q[idx] = null;
					}else{
						// Non e' stata ancora eseguita la chiamata quindi eseguo
			  	  	  	if(!itm.called && itm.idx == -1){
							// Devo eseguirlo
							itm.called = true;
							itm.funct(itm.pid, idx);
							break;
						}
					}
	  	  	  	}
			}
		},
		queueCommandFromElement: function(e){
			return this.queueCommandFromIdx(e.idx);
		},
		queueCommandFromIdx: function(idx){
			if(idx == null) idx = -1;
	  	  	var q = this._queueCommands;
	  		if(q==null) return -1;
			for(var i = 0; i<q.length; i++){
				if(q[i].idx == idx && !q[i].done ) return i;
			}
			return -1;
		},
		queueDone: function(idx){
			if(_.fx._queueCommands && _.fx._queueCommands[idx]){
				_.fx._queueCommands[idx].done = true;
				_.fx._queue();
			}
		},
		_fade: function(id, speed, step, limit, queueIndex,progress){
			var obj = JASTEggIt._id(id);
			if(obj==null) return false;
			var s = obj.style;
			if(progress==null){
				// Provo ad identificare il livello di fading già attivo
				if(s.opacity) progress = parseFloat(s.opacity);
				if(s.MozOpacity) progress = parseFloat(s.MozOpacity);
				if(s.KhtmlOpacity) progress = parseFloat(s.KhtmlOpacity);
				if(progress==null) progress = (step>0?0:1);
				progress *=100;
			}
			if(_.fx._isPaused(obj)) return false;
			progress+=step;
			
			if((progress > limit && step>0) || (progress < limit && step<0) ){
				if(queueIndex!=null) _.fx.queueDone(queueIndex);
			}else{
				
				if(JASTEggIt.Browser.ie){
				    s.filter = "alpha(opacity=" + progress + ")";
					if (s.height == null || s.height == '' && progress != 100) {
						s.height = '1%';
					} else {
						if(s.height == '1%' && progress>=100) s.height = null;
					}
				}else{
					s.opacity = (progress / 100);
				    s.MozOpacity = (progress / 100);
				    s.KhtmlOpacity = (progress / 100);
				}
				window.setTimeout(function(){
					_.fx._fade(id, speed, step, limit, queueIndex,progress);
				},speed);
			}
		},
		fadeIn: function(id,speed,limit, step, queueIndex){
			if(step==null) step = 1;
			if(step<0) step = step * -1;
			this._fade(id, speed, step,limit, queueIndex);
		},
		fadeOut: function(id,speed,limit, step, queueIndex){
			if(step==null) step = -1;
			if(step>0) step = step * -1;
			this._fade(id, speed, step,limit, queueIndex);
		},
		resize: function(id,from, to, speed, steps, queueIndex,stepW, stepH, current){
			var obj = _._id(id);
			if(obj==null) return;
			if(current==null) current = 0;
			if(from==FX_RESIZE_ORIGINAL) 	from 	= _.DOM.realSize(obj);
			if(to==FX_RESIZE_ORIGINAL) 		to 		= _.DOM.realSize(obj);
			if(stepW==null) stepW = (to.width - from.width ) / steps;
			if(stepH==null) stepH = (to.height - from.height ) / steps;
			if(this._isPaused(obj)) return false;
			_.DOM.setStyle(obj, { 
				width: (from.width + stepW * current) + 'px',
				height: (from.height + stepH * current) + 'px',
				overflow: 'hidden'
			} );
			current+=1;
			if(current > steps){
				if(queueIndex!=null) _.fx.queueDone(queueIndex);
			}else{
				window.setTimeout(function(){
					_.fx.resize(id,from, to, speed, steps, queueIndex,stepW, stepH, current);
				},speed);
			}
		},
		vscroll:	function(id, speed, step, limit, queueIndex, absoluteLimit){
			_.fx.scroll(id, speed, step, limit, queueIndex, FX_SCROLL_TOP, absoluteLimit);
		},
		hscroll:	function(id, speed, step, limit, queueIndex, absoluteLimit){
			_.fx.scroll(id, speed, step, limit, queueIndex, FX_SCROLL_LEFT, absoluteLimit);
		},
		scroll: 	function(id, speed, step, limit, queueIndex, direction, absoluteLimit){
			var obj = _._id(id);
			if(obj.style.position!='relative' && obj.style.position!='absolute') obj.style.position='relative';
			var l = _.DOM.style(obj, direction);
			l = parseInt(l[direction]);
			if(isNaN(l)) l = 0;
			if(!absoluteLimit){
				limit = parseInt(limit) - parseInt(l);
				absoluteLimit = true;
			}
			
			l += step;
			if(l>=-limit && step>0) l = -limit;
			if(l<=-limit && step<0) l = -limit;
			obj.style[direction] = l + 'px';
			if(l!=-limit && step<0 || l!=-limit && step>0){
				window.setTimeout(function(){
					_.fx.scroll(id, speed, step, limit, queueIndex, direction, absoluteLimit);
				}, speed);
			}else{
				if(queueIndex!=null) _.fx.queueDone(queueIndex);
			}
		},
		queue: function(fnList, prepare){
			this._queueCommands = [];
			for(var i=0; i<fnList.length; i++){
				var itm = fnList[i];
				this._queueCommands[i] ={
					pid:		itm[0],
				  	funct: 		itm[1],
				    called: 	false,
					idx:		-1
				}
			}
			if(!prepare) JASTEggIt.fx._queue();
		}
	}
);