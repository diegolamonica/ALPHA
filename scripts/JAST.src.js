/* **************************************************
JAST Egg it: JAvaScript Toolkit
url: http://www.jastegg.it 
--------------------------------------------------
Version:		1.5.2
Author: 		Diego La Monica (http://diegolamonica.info)
Revision of: 	2012-08-28
Works With:		Internet Explorer 6+
				Firefox 2.0+ 
				Safari 3.x
				Opera 9.x
				Chrome
************************************************** 
 Version History:
 
 1.5.1:	method setStyle of DOM class accepts now both 
 		id, any DOM object and array of mixed both id 
 		and DOM objects.
 		
 1.5.2: method remove of DOM class has been improved
 		to remove correctly all DOMNodes passed as
 		array to the method.
 		
 		Added parameter attributes to method createChild

		Improved checks to avoid multiple JAST instances 
*/

if(window['JASTEggIt']==null && _==null){
	// Solo una volta deve essere caricato (anche se viene incluso 1.000.000 di volte)
	var JASTEggIt = {
		Browser:{
			name:		null,
			version:	null,
			agent:		navigator.userAgent,
			relDate: 	navigator.productSub?navigator.productSub:'', 
			dom: 		(!document.layers && document.getElementById!=null),
			ns4: 		(document.layers?true:false),
			opera:		(navigator.userAgent.indexOf('Opera')!=-1),
			safari:		(navigator.userAgent.indexOf('Safari')!=-1),
			konqueror:	(!this.safari && (navigator.userAgent.indexOf('Konqueror')!=-1)),
			mozilla:	( (!this.safari && !this.konqueror ) && ( navigator.userAgent.indexOf('Gecko')!=-1 ) ),
			ie:			((navigator.userAgent.indexOf('MSIE')!=-1) && !this.opera)
		},
		_onStartup: [],
		_eggs: '',
		_internal: {
			idCount:	0,
			required:	[]
		},
		_el: function(el){ 
			if(typeof(el) != 'object') return JASTEggIt._id(el); 
			else return el;
		},
		_doDebug: function(msg,clear){
			if(_._id('debug')){
				if(clear==true) _._id('debug').innerHTML = '';
				_._id('debug').innerHTML += '<div onClick="_.DOM.remove(this)">'+msg+'</div>';
			}
		},
		_id: function(id){ return document.getElementById(id); },
		_get: function( cascade, from, p ){
			if( !this.Array.is(cascade) ) cascade = cascade.split('>');
			if(from == null) from = 0;
			if(p == null) p = document;
			if(from == cascade.length) return p;
			var attrs = { };
			var item = cascade[from];
			if(item.substring(0,1) == '#') attrs = { id: item.substring(1,item.length) };
			else if(item.substring(0,1)== '.') attrs = { className: item.substring(1,item.length) };
			else attrs = { tagName: item };
			
			var ret = this.DOM.find( p , attrs );
			for(var i = 0; i<ret.length; i++){
				var k = i;
				ret[k] = this._get( cascade, from+1, ret[k] );
			};
			for(var i = 0; i<ret.length; i++){
				var k = i;
				if (ret[k] == '') ret[k] = null;
				if (this.Array.is(ret[k])){
					this.Array.merge(ret, ret[k]);
					ret[k] = null;
				};
	
			};
			ret = this.Array.purge(ret);
			return ret;
		},
		_class: function(className, elm, tag ){
			tag = tag || "*";
			elm = elm || document;
			elm = JASTEggIt._el(elm);
			var e = JASTEggIt._name(tag, elm);
			var returnElements = [];
			for(var i=0; i<e.length; i++)
				if(JASTEggIt.DOM.hasClass(e[i], className)) returnElements.push(e[i]);
				
			return returnElements;
		},
		_name: function(name, parent){ 
			if(parent==null) parent = document;
			parent= _._el(parent);
			return parent.getElementsByTagName(name); },
		_works: function(){alert('JAST framewor is working fine!');},
		_ready: false,
		_startup: function(){
			var j = JASTEggIt;
			var eggs = j._eggs.split(',');
			
			for ( var i=0; i<eggs.length; i++ ){
				var keyVar = eggs[i];
				if(typeof(j[keyVar]) == 'object' && j[keyVar].startup != null) j[keyVar].startup();			
			}
			JASTEggIt._ready = true;
			
			var s = JASTEggIt._onStartup;
			for(var i=0; i<s.length; i++){
				var f = s[i];
				f();
			}
			
		},
		onStartup: function(fn){
			this._onStartup.push(fn);
		},
		eggs: function(){
			var eggs = Array();
			for(egg in this) if(typeof egg==='object' && egg.info!= null) eggs[eggs.length] = egg.info;
			return eggs;
		},
		require: function(e){
			var j = JASTEggIt;
			var j_i = j._internal;
			var x = j.xhttp;
			for(var i=0; i< e.length; i++){
				if(j[e[i]]==null){
					if(j._internal['scriptPath'] == null){
						var scr = _._name('script');
						for(k=0; k<scr.length; k++){
							var i = scr[k].src.indexOf('JAST.src.js');
							if(i!=-1){
								var path = scr[k].src.substring(0, i);
								j_i['scriptPath'] = path;
							}
							
						}
					}
					if(j.Array.isIn(j_i.required, e[i])) return false;
					j_i.required[j_i.required.length] = e[i];
					
					x.options.asyncMode = false;
					x.get(j_i.scriptPath + 'JAST-'+ e[i] + '.src.js', '', 
						function(buffer){
							
							eval(buffer)
						} );
					this.xhttp.options.asyncMode = true;
				}
			}
		},
		generateUniqueId: function(prefix){
			var d = new Date();
			var id = prefix + '-';
			var summa = d.getDay();
			summa += d.getMonth();
			summa += d.getYear();
			summa += d.getHours();
			summa += d.getMinutes();
			summa += d.getSeconds();
			summa += d.getMilliseconds();
			id += summa; 
			id += '-' + this._internal.idCount++;
			return id; 
		},
		mergeOptions: function(src, full, replace){
			if(replace==null) replace = false;
			if(src == null) src = full;
			for(o in full) if(src[o] == null || replace == true) src[o] = full[o];
			return src;
		},
		startup: function(){
			var b = JASTEggIt.Browser;
			if(b.name==null) JASTEggIt._browser();
	
			if ( (b.mozilla || (b.opera && b.version<9)) && !b.safari){
				JASTEggIt.Events.add(document, "DOMContentLoaded", JASTEggIt._startup);
				
			}else if ( b.ie ) {
				document.write("<scr" + "ipt id=__ie_init defer=true " + "src=\"//:\"><\/script>");
				var script = JASTEggIt._id("__ie_init");
				if ( script ) 
					script.onreadystatechange = function() {
						if ( this.readyState != "complete" ) return;
						JASTEggIt._startup();
					};
			
				script = null;
	
			} else if ( b.safari ||  (b.opera && b.version==9))
				JASTEggIt._onlyForSafari = setInterval(function(){
					if ( document.readyState == "loaded" || 
						document.readyState == "complete" ) {
						clearInterval( JASTEggIt._onlyForSafari );
						JASTEggIt._onlyForSafari = null;
						JASTEggIt._startup();
					}
				}, 10); 
		},
		extend: function(key, object){ 
			this[key] = object; 
			this[key].name = key;
			this._eggs += (this._eggs!=''?',':'')+key;
			if(this._ready && (this[key].startup != null)){
				this[key].startup();
			};
		},
		event: function(item, event, fn ){
			return this.Events.add(item, event, fn);
		},
		ARIA: {
			addRole: function(e, role){
				e.setAttribute('role',role);
			},
			setProperty: function(e, property, value){
				if(typeof (property) == 'object'){
					for(p in property){
						e.setAttribute('aria-' + p, property[p]);
					}
				}else{
					e.setAttribute('aria-' + property,value);
				}
			},
			getProperty: function(e, property){
				return e.getAttribute('aria-' + property); 
			},
			focusNextNode: function(node){
				
				var p = node.parentNode;
				if(p==null) return false;
				var nodes = p.childNodes;
				if(node.id==null || node.id=='') node.id = JASTEggIt.generateUniqueId('jast');
				var isCurrent = false;
				for (var i = 0; i < nodes.length; i++) {
					if(nodes[i].nodeType==1){
						if(isCurrent){
							nodes[i].focus();
							return;
						} 
						isCurrent = (node.id== nodes[i].id);
					}
				}
			},
			focusPreviousNode: function(node){
				var p = node.parentNode;
				if(p==null) return false;
				var nodes = p.childNodes;
				if(node.id==null || node.id=='') node.id = JASTEggIt.generateUniqueId('jast');
				
				var previousItem = null;
				
				for(var i = 0; i<nodes.length; i++){
					if(	nodes[i].nodeType==1){ // è un nodo HTML del DOM
						if (node.id== nodes[i].id) { 
							if (previousItem == null) return false;
	
							previousItem.focus();
							return true;
						}
						previousItem = nodes[i];
					}
				}
				if(previousItem!=null) previousItem.focus();
				return true;
			}
	
		},
		Events: {
			generator: function(event){
				if(event==null) event = window.event;
				if (event.target) return event.target;
				if (event.currentTarget) return event.currentTarget;
				else if (event.srcElement) return event.srcElement;
				return null;
			},
			add: function(item, event, fn){
				if(JASTEggIt.Array.is(item)){
					for(var i =0; i<item.length;i++) JASTEggIt.Events.add(item[i], event, fn);
				}else{
					if(typeof(event)=='object'){
						for(e in event){
							_.Events.add(item, e, event[e]);
						}
					}else{
						var f = null;
						var obj = (typeof(item)=='object'?item:JASTEggIt._id(item));
						if(typeof(fn)==='function'){
							f = fn;
						}else{
							fn = fn.replace(/this\./g, 'JASTEggIt._id(\''+ item + '\').' );
							f = function(event){ eval( fn );  };
						}
						
						if(document.addEventListener){
							obj.addEventListener(event, f, true );
						}else if(document.attachEvent){
							obj.attachEvent('on' + event,  f );
							//eval('obj.on' + event + '=' + f);
						}else{
							obj.setAttribute('on' + event, f  );
						};
					}
				}
			},
			fire: function(e,event,evt){
				var element = _._el(e);
				if (document.createEventObject){
					if(evt==null) evt = document.createEventObject();
					return element.fireEvent('on'+event,evt);
				}else{
					
					if(evt==null) evt = document.createEvent("HTMLEvents");
					evt.initEvent(event, true, true ); // event type,bubbling,cancelable
	        		return !element.dispatchEvent(evt);
		    	}
			},
			getWheelDelta: function(event){
				if (event.wheelDelta) { /* IE/Opera. */
	                delta = event.wheelDelta/120;
	                /** In Opera 9, delta differs in sign as compared to IE.
	                 */
	                if (window.opera) delta = -delta;
				} else if (event.detail) { /** Mozilla case. */
	                /** In Mozilla, sign of delta is different than in IE.
	                 * Also, delta is multiple of 3.
	                 */
	                delta = -event.detail/3;
		        }
				return delta;
			} ,
			abort: function(event){
		        if (event.preventDefault) event.preventDefault();
		        event.returnValue = false;
			}
		},
		Accessibility:	{
			clickEvent:	function(id, fn){
				JASTEggIt.Events.add(id, 'click', fn);
				JASTEggIt.Events.add(id, 'keypress', function(event){
					var keynum = JASTEggIt.kbd.getKeyPressed(event); 
					if(keynum==32 || (keynum==13 && window.event) ){
						// 32 = SPACE BAR (Barra spaziatrice)  
						_.Events.fire(id, 'click');
						//fn(event);
						return false;
					}/*else
						alert(keynum);*/
				} );
			},
			listItemsToButtons: function(itemsList){
				var jast= JASTEggIt;
				var aria = jast.ARIA;
				var dom = jast.DOM;
				if (!jast.Array.is(itemsList)){
					var el = jast._el(itemsList);
					if(el==null) return false;
					
					if (el.tagName.toUpperCase() == 'UL') {
						
						el.setAttribute('role','toolbar');
						itemsList = jast._name('li', el);
					}
					else 
						if (el.tagName.toUpperCase() == 'LI') {
							itemsList[0] = el;
						}
				}
				
				for(var iterator_lists =0; iterator_lists< itemsList.length; iterator_lists++){
					
					var selId = itemsList[iterator_lists]; 
					
					var liElement= jast._el(selId);
					var link = jast._name('a',liElement)[0];
					
					if (liElement!=null && !jast.DOM.hasClass(selId, 'skip') && link != null) {
						var ev = function(event){
							var gen = jast.Events.generator(event);
							if (gen.tagName.toUpperCase() == 'A') 
								return;
							var li = gen;
							while (li.tagName.toUpperCase() != 'LI') {
								li = li.parentNode;
							}
							
							
							if (li.id != null && li.id != '') {
								var lnk = JASTEggIt._name('a', li)[0];
								if(jast.Events.fire(lnk, 'click')){
									window.location.href = lnk.href;
								}
								
							}
							else {
								return false;
							}
							
						};
						liElement.setAttribute('tabIndex', '0');
						aria.addRole(liElement,'button');
						if(liElement.title==null || liElement.title=='') liElement.title = link.title;
						if(liElement.id == null || liElement.id == '') liElement.id = jast.generateUniqueId('jast'); 
						link.setAttribute('tabIndex', '-1');
						dom.setStyle(selId, {
							cursor: 'pointer'
						});
						
						var subEl = dom.find(liElement, {
							nodeType: 1
						});
						for (var i = 0; i < subEl.length; i++) {
							if (subEl[i].tagName != 'A' &&
							subEl[i].tagName != 'BR') {
								jast.Accessibility.clickEvent(subEl[i], ev);
							}
						}
						
						jast.Accessibility.clickEvent(selId, ev);
					}
				}
	
				
			}
		},
		Array:		{
			is: function(a) {
				return 	(a) && 
						(!a.length!=null) && 
						(typeof a === 'object') && 
						(typeof a.length === 'number') && 
						(a.nodeName==null);	// I forms espongono la proprietà length riferita a elements  
			},
			swap: function(arr, a, b){
				var x = arr[a];
				arr[a] = arr[b];
				arr[b] = x;
				return arr;
			},
			isIn: function(a, v){
				for(var i=0; i<a.length; i++){
					if(a[i].toString() ==v.toString()) return true;
				}
				return false;
			},
			lookFor: function(a, v, like, getAll){
				if(getAll==null) getAll = false;
				var myNewArray = [];
				for(var i in a){
					if((like && a[i].substring(0, v.length)== v) || a[i]==v){
						if(!getAll) return i;
						myNewArray.push(i);
					}
				}
				if(getAll) 	return myNewArray;
				
				
				return -1;
			},
			toString: function(a){
				var buffer = '';
				for(var i =0;i<a.length; i++){
					if (buffer!='')buffer += ',';
					if(this.is(a[i])){
						buffer += this.toString(a[i]);
					} else if(typeof a[i] ==='object'){
						buffer += (typeof a[i]);
					}else{
						buffer += a[i];
					};
				};
				buffer = '[' + buffer + ']';
				return buffer;
			},
			merge: function(a1,a2){
				if(a2 == null) return a1;
				for(var i=0; i<a2.length; i++) a1[a1.length] = a2[i];
				return a1;
			},
			purge: function(a){
				var ret = Array();
				for(var i=0; i<a.length; i++ ){
					var k = i;
					if(a[k]!=null) ret[ret.length] = a[k];
				};
				return ret;
			}
		},
		Objects:	{
			clone: function(myObj){
				if(typeof(myObj) != 'object') return myObj;
				if(myObj == null) return myObj;
				var myNewObj = [];
				for(var i in myObj)
					myNewObj[i] = this.clone(myObj[i]);
			
				return myNewObj;
			},
			is: function(myObj){
				return (typeof(myObj) == 'object');
			}
		},
		strings:	{
			trim: function(s){
				while (s.substr(0,1) == ' ') s = s.substr(1, s.length);
				while (s.substr(s.length-1, 1) == ' ') s = s.substring(0,s.length-1);
				return s;
			},
			split: function(stringa, separatore){
				var newArray = stringa.toString().split(separatore);
				return this.removeLastChar(newArray, separatore);
			},
			removeLastChar: function(textArray, lastChar){
				for(var i=0; i < textArray.length; i++){
					var l = textArray[i].length;
					if(textArray[i].substr(l-1,1) == lastChar) textArray[i] = textArray[i].substr(0,l-1);
				};
				return textArray;
				},
				parseInt: function(theString){
					var s = theString.toString().replace(/^0+([\d]+)$/,'$1');
					if(s == '') s = '0';
					return parseInt(s);
			}
		},
		DOM:{
			labelOf: function(id, theForm){
				var l = _._name('label', theForm);
				for(var i=0; i<l.length; i++){
					var lbl = l[i];
					if (id == lbl.attributes['for'].value) return lbl;
				}
				return null;
	
			},
			isChildOf: function(el, parentId){
				el = JASTEggIt._el(el);
				if(el == null)return false;
				if(el.id == parentId) return true;
				if(el.parentNode == null) return false;
				return JASTEggIt.DOM.isChildOf(el.parentNode, parentId);
			},
			createContainer: function(el, tagName){
				el = JASTEggIt._el(el);
					var e = document.createElement(tagName);
					e.id = JASTEggIt.generateUniqueId(tagName);
					var p = el.parentNode;
					if(p!=null) p.appendChild(e);
				e.appendChild(el);
				return e;
			},
				createChild: function(tagName, parentTag, id, attributes){
				parentTag = JASTEggIt._el(parentTag);
				var e = document.createElement(tagName);
				parentTag.appendChild(e);
				if(id!=null) e.id = id;
					
					if(attributes!=null){
						for(var key in attributes){
							
							e.setAttribute(keys, attributes[key]);
							
						}
						
					}
					
				return e;
			},
			createOnDocument: function(tagName, beforeItem, afterItem, id, attributes){
				var e = document.createElement(tagName);
				for(a in attributes) e[a] = attributes[a];
				var body = JASTEggIt._name('BODY')[0];
				if(beforeItem==null && afterItem == null){
					body.appendChild(e);
				}else if(beforeItem){
					beforeItem = JASTEggIt._el(beforeItem);
					var p = beforeItem.parentNode;
					p.insertBefore(e, beforeItem);
				}else if(afterItem){
					afterItem = JASTEggIt._el(afterItem);
					var n = afterItem.nextSibling;
					var p = afterItem.parentNode;
					if(n==null){
						p.appendChild(e);
					}else{
						p.insertBefore(e, n);
					}
				}
				e.id = (id==null?JASTEggIt.generateUniqueId(tagName):id);
				return e;
			},
			remove: function(el){
				if(JASTEggIt.Array.is(el)){ 
						var removed = true;
						/*
						 * To grant the complete array of elements removal
						 */
						while(removed){
							removed = false;
							for(var i=0; i<el.length; i++){
								if(el[i]!=null){
						JASTEggIt.DOM.remove(el[i]);
									removed= true;
									// el[i] = null;
									break;
								}
							}
						}
				}else{
					el = JASTEggIt._el(el);
					if(el==null) return false;
					if(el.parentNode==null) return false; 
					el.parentNode.removeChild(el);
				}
				return true;
			},
			position: function(el) {
				el = JASTEggIt._el(el);
				var sl = 0, st = 0;
				var is_div = /^div$/i.test(el.tagName);
				if (is_div && el.scrollLeft)	sl = el.scrollLeft;
				if (is_div && el.scrollTop)		st = el.scrollTop;
				var r = { 
					x: el.offsetLeft - sl, 
					y: el.offsetTop - st 
				};
				if (el.offsetParent) {
					var tmp = this.position(el.offsetParent);
					r.x += tmp.x;
					r.y += tmp.y;
				};
				return r;
			},
			style: function(el, at){
				var e = JASTEggIt._el(el);
				var st = null;
				if( window.getComputedStyle ) st = window.getComputedStyle(e,null);
					else if( e!=null && e.currentStyle ) st = e.currentStyle;
	
				if(st==null) return null;
				if(at==null) return st;
				at = at.split(' ');
				var ret = new Object();
				for(var i = 0; i<at.length; i++) ret[at[i]]=st[at[i]];
	
				return ret;
			},
			setStyle: function(el, rules){
				if(JASTEggIt.Array.is(el)){
					for(var i=0; i<el.length; i++) this.setStyle(el[i], rules);
				}else{
					var e = JASTEggIt._el(el);
					for(rule in rules)
						try{
							e.style[rule] = rules[rule];	
						} catch(ex) {
							
						}
				}
				
			},
			realSize: function(el){
				el = JASTEggIt._el(el);
				if(el){
					
	
						var att = this.style(el, 'width height paddingTop paddingBottom paddingLeft paddingRight marginTop marginBottom borderLeftWidth borderRightWidth borderTopWidth borderBottomWidth');
						var buffer = "";
						for(prop in att){
							att[prop] = parseInt(att[prop]);
							if(isNaN(att[prop])) att[prop] = 0;
							
						};
						var width = (att.width==0?el.offsetWidth - (att.paddingLeft + att.paddingRight):att.width ) + att.borderLeftWidth + att.borderRightWidth;
						var height = (att.height==0?el.offsetHeight - (att.paddingTop + att.paddingBottom):att.height) + att.borderTopWidth + att.borderBottomWidth;
						return {
								width: width,
								height: height
							};
	
				}
			},
			locate: function(el, x, y, w, h, p){
				el = JASTEggIt._el(el);
				if(p==null) p = 'absolute';
				
				el.style.position = p;
				if (w!=null) el.style.width = w + 'px';
				if (h!=null) el.style.height = h + 'px';
				el.style.left = x +'px'; 
				el.style.top = y +'px';
			},
			nodeMatch: function(e, attrs){
				for(a in attrs){
					if(e[a]==null){
						if(e.attributes==null) return false;
						if(e.attributes[a]==null) return false;	
						if(e.attributes[a].value != attrs[a]) return false;
					}else{
						if (attrs[a] !=  e[a]) return false;
					}
				}
				return true;
			},
			find: function( p, match ){
				var ret = Array();
				for(var i = 0; i<p.childNodes.length; i++){
					var node = p.childNodes[i];
					if ( this.nodeMatch(node, match) ) ret[ret.length] = node;
					ret = JASTEggIt.Array.merge(ret, this.find(node, match));
				};
				return ret;
			  
			},
			hasClass: function(element, className){
				element = JASTEggIt._el(element);
				if(element.className==null) return false; 
				var s = element.className.toString();
				className = className.toLowerCase();
				s = ' ' + s.toLowerCase() + ' ';
				return(s.indexOf(' ' + className + ' ')!=-1);
				
			},
			appendClass: function(element, className){
				if(JASTEggIt.Array.is(element)){
					for(var i=0; i<element.length; i++) this.appendClass(element[i], className);
				}else{
					element = JASTEggIt._el(element);
					if(element== null) return;
					if(element.className==null || element.className==''){
						element.className = className;
					}else{
						if(!this.hasClass(element, className)) element.className += ' ' + className;
					}
				}
			},
			removeClass: function(element, className){
				if(JASTEggIt.Array.is(element)){
					for(var i=0; i<element.length; i++) this.removeClass(element[i], className);
				}else{
		
					element = JASTEggIt._el(element);
						if(element== null) return;
					if(this.hasClass(element, className)){
						var c = ' ' + element.className + ' ';
						var i = c.indexOf(' ' + className + ' ');
						c = c.substr(0, i) + ' ' + c.substr(i+className.length+2, c.length);
						element.className = JASTEggIt.strings.trim(c);
					}
				}
			}
		},
		Listener:	{
			_listener:	[],
			_interval:	null,
			_inside:	false,
			_watch:	function(){
				
				var J = JASTEggIt;
				var L = J.Listener;
				var _l = L._listener;
				
				//if(this._inside) return false;
				//this._inside= true;
				for(var i=0; i< _l.length; i++ ){
					var itm = _l[i];
					var obj = J._id(itm.id);
					if(obj!=null && itm.execute!=null){
						var attributes = itm.attribute.split('|');
						for(var j=0; j<attributes.length; j++){
							var k = j;
							if(obj[attributes[k]] != itm.lastValue[k]){
								itm.lastValue[k] = obj[attributes[k]];
								itm.execute(itm);
							};
						};
					};
				};
				//this._inside= false;
				setTimeout(JASTEggIt.Listener._watch, 10);
			},
			clear: function(){
				// if(this._interval==null) clearInterval(this._interval);
				_listener = [];
			},
			watch: function(id, attribute, fn){
				this._listener.push({
					id:			id,
					attribute:	attribute,
					lastValue:	[],
					execute:	fn
				});
				if(!this._interval){
					this._interval=true;
					setTimeout(JASTEggIt.Listener._watch, 10);
				}
			}
		},
		kbd: {
			getKeyPressed: function(event){
				var keynum = 0;
				if(window.event) keynum = window.event.keyCode; //+ 32;
					else if(event.which) keynum = event.which;
				return keynum;
			}
		},
		xhttp: {
			options:	{
				userName:	null,
				password:	null,
				asyncMode:	true,
				plusAsChar: true,
				headers:	[
					{key: 'Pragma', value:'no-cache'},
					{key: 'Cache-Control', value: 'no-cache'},
					{key: 'Expires', value: '-1'}
					]	
			},
			_xssInstances:	0,
			_xhrInstances:  0,
			_loaderId:		'',
			_xhr:			[],
			_createXhr: function(){
				var obj = null;
				if(typeof(XMLHttpRequest) === "function" || typeof(XMLHttpRequest) === "object"){
					obj = new XMLHttpRequest();
				} else if (window.ActiveXObject) {
					obj = this._createXhrFromActiveX();
				};
				return obj;
			},
			_createXhrFromActiveX: function() {
				var aVersions = [ "MSXML2.XMLHttp.5.0",
					"MSXML2.XMLHttp.4.0","MSXML2.XMLHttp.3.0",
					"MSXML2.XMLHttp","Microsoft.XMLHttp"
				];			
				for (var i = 0; i < aVersions.length; i++) {
					try {
						var oXmlHttp = new ActiveXObject(aVersions[i]);
						return oXmlHttp;
					} catch (e) { };
				};
				return null;
			},
			_createQueryString: function(parameters){
				var params = '';
				
				if(typeof(parameters) == 'object'){
					for(keys in parameters){
						
						if(!_.Array.is(parameters[keys])){
							if(params!='')params += '&';
							params += encodeURIComponent(keys) + '=' + encodeURIComponent(parameters[keys]);
						}else{
							
							for(var i=0; i<parameters[keys].length; i++){
								if(params!='')params += '&';
								params += encodeURIComponent(keys) + '=' + encodeURIComponent(parameters[keys][i]);
							}
						}
					};
					
				}else{
					params = parameters;
				};
				return params;
			},
			get: function(url, parameters, retFunction, _this){
				return this.sendRequest('GET', url, parameters, retFunction, _this);
			},
			post: function(url, parameters, retFunction, _this){
				return this.sendRequest('POST', url, parameters, retFunction, _this);
			},
			
			sendRequest: function(method, url, parameters, retFunction, _this){
				var w = JASTEggIt;
				var _xhr = this._createXhr();
				if (_xhr == null) return false;
				// refreshInstancesCount
				var xhri = 0;
				for(var i=0; i<this._xhr.length; i++){
					if(this._xhr[i]!=null) xhri +=1;
				}
				
				  
				var l = this._xhr.length;
				this._xhr[l] = _xhr;
				this._xhrInstances += 1;
				if(this._loaderId !='')	w._id(this._loaderId).style.display = ''; 
				var params = this._createQueryString(parameters);
				if(this.options.plusAsChar) params = params.replace(/\+/g,"%2b");
				_xhr.onreadystatechange= function(){
					if(_xhr.readyState==4){
						
						var output_buffer = _xhr.responseText;
						output_buffer = output_buffer;
						if(retFunction != null){
							if(typeof retFunction === 'function'){
								retFunction(output_buffer, _this);
							}else{
								retFunction = retFunction.replace('%%BUFFER%%', 'output_buffer');
								eval(retFunction);
							}
						};
						var x = w.xhttp;
						var ldr = x._loaderId;
						x._xhrInstances -=1;
						if(x._xhrInstances==0 && ldr !='')
							w._id(ldr).style.display = 'none'; 
						_xhr = null;
						return true;
					};
				};
				if(method=="POST"){
					_xhr.open("POST", url, this.options.asyncMode);
					
					for(var i =0 ; i<this.options.headers.lenght; i++){
						var h = this.options.headers[i];
						_xhr.setRequestHeader(h.key, h.value);
					}
					_xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=utf-8');
					_xhr.send(params);
				} else {
					if(params!='') url += (url.indexOf('?')!=-1?'&':'?') + params;
					
					_xhr.open("GET", url, this.options.asyncMode );
					for(var i =0 ; i<this.options.headers.lenght; i++){
						var h = this.options.headers[i];
						_xhr.setRequestHeader(h.key, h.value);
					}
					_xhr.setRequestHeader("Connection","close");
					_xhr.send(null);
				};
				return true;
			},
			xsRequest: function(url,parameters, retFunction){
				var _ = JASTEggIt;
				var h = JASTEggIt._name('head').item(0);
				if(this._xssInstances!=0){
					var old = JASTEggIt._id('xsrequest-script');
					if (old) head.removeChild(old);
					this._xssInstances = 0;
				};
				var s = document.createElement('SCRIPT');
				var params = this._createQueryString(parameters);
				url += (url.indexOf('?')!=-1?'&':'?') + params;
				s.type = 'text/javascript' ;
				this._xssInstances+=1;
				s.id = 'xsrequest-script';
				s.defer = true;
				s.src = url;
				s.returnTo = retFunction;
				h.appendChild(s);
			},
			xsResponse: function(data){
				var _ = JASTEggIt;
				var old = JASTEggIt._id('xsrequest-script');
				this._xssInstances = 0;
				old.returnTo(data);
				if (old){
					var h = JASTEggIt._name('head').item(0);
					h.removeChild(old);
				};
			}
		},
		include: function(url, type, handler){
			var _ = JASTEggIt;
			var h = JASTEggIt._name('head').item(0);
			
			var attr = null;
			switch(type){
				case 'css':
					obj = 'LINK';
					attr={
						rel: 'stylesheet',
						type: 'text/css',
						href: url
					};
					break;
				default:
					obj = 'SCRIPT';
					attr = {
						type: 'text/javascript',
						src: url,
						defer: true
					};
					break;
			};
			var s = document.createElement(obj);
			JASTEggIt.mergeOptions(s, attr, true);
			h.appendChild(s);
			if(handler){
				s.onload = s.onreadystatechange = function(){
						if ( (!this.readyState ||
							this.readyState == "loaded" || this.readyState == "complete") ) {
								handler(this);
								
					}
					
				}
			}
			return s;
		},
		_browser: function(){
			var b = JASTEggIt.Browser;
			var name =	navigator.appName;
			var ver = navigator.appVersion;
			var aver = '';
			if (b.opera){
				var str_pos=b.agent.indexOf('Opera');
				aver= b.agent.substr((str_pos+6),4);
				b.name = 'Opera';
			}else if (b.safari){
				var str_pos=b.agent.indexOf('Safari');
				aver=b.agent.substr((str_pos+7),5);
				b.name = 'Safari';
			}else if (b.konqueror){
				var str_pos=b.agent.indexOf('Konqueror');
				aver=b.agent.substr((str_pos+10),3);
				b.name = 'Konqueror';
			}else if (b.mozilla){
				var pattern = /[(); \n]/;
				var mozTypes = new Array( 'Firebird', 'Phoenix', 'Firefox', 'Iceweasel', 'Galeon', 'K-Meleon', 'Camino', 'Epiphany', 'Netscape6', 'Netscape', 'MultiZilla', 'Gecko Debian', 'rv' );
				var rev_pos = b.agent.indexOf( 'rv' );
				var rev_full = b.agent.substr( rev_pos + 3, 6 );// cut out maximum size it can be, eg: 1.8a2, 1.0.0 etc
				var rev_slice = ( rev_full.search( pattern ) != -1 ) ? rev_full.search( pattern ) : '';
				( rev_slice ) ? rev_full = rev_full.substr( 0, rev_slice ) : '';
				aver = rev_full.substr( 0, 3 );
				for (var i=0; i < mozTypes.length; i++){
					if ( b.agent.indexOf( mozTypes[i]) !=-1 ){
						var moz_brow = mozTypes[i];
						break;
					}
				};
				if ( moz_brow ){
					var str_pos = b.agent.indexOf(moz_brow);
					var moz_brow_aver = b.agent.substr( (str_pos + moz_brow.length + 1 ) ,3);
					moz_brow_aver = ( isNaN( moz_brow_aver ) ) ? moz_brow_aver = aver: moz_brow_aver;
					var moz_brow_aver_sub = b.agent.substr( (str_pos + moz_brow.length + 1 ), 8);
					var sub_aver_slice = ( moz_brow_aver_sub.search( pattern ) != -1 ) ? moz_brow_aver_sub.search( pattern ) : '';
					( sub_aver_slice ) ? moz_brow_aver_sub = moz_brow_aver_sub.substr( 0, sub_aver_slice ) : '';
				};
				if ( moz_brow == 'Netscape6' ) moz_brow = 'Netscape';
				else if ( moz_brow == 'rv' || moz_brow == '' ) moz_brow = 'Mozilla'; 
				if ( !moz_brow_aver ){
					moz_brow_aver = aver;
					moz_brow_aver_sub = aver;
				};
				name = moz_brow;
				aver = moz_brow_aver;
			}else if (b.ie){
				var str_pos = b.agent.indexOf('MSIE');
				aver = b.agent.substr((str_pos+5),3);
				b.name = 'Microsoft Internet Explorer';
			}else b.name = name;
			if( b.opera&&(aver.substring(0,1)==5)) 	b.version = 5;
			else if( b.opera&&(aver.substring(0,1)==6) ) b.version = 6;
			else if (b.opera&&(aver.substring(0,1)==7)) 	b.version = 7;
			else if (b.opera&&(aver.substring(0,1)==8)) 	b.version = 8;
			else if (b.opera&&(aver.substring(0,1)==9)) 	b.version = 9;
			else if (b.ie&&!b.dom ) 						b.version = 4; 
			else if (b.ie&&(aver.substring(0,1)==6))		b.version = 6;
			else if (b.ie&&(aver.substring(0,1)==7))		b.version = 7;
			else{
				if(!aver) b.version = ver.substring(0,1);
				else b.version = aver.substring(0,1);
			}
			if(b.name==null) b.name = name;
		}
	};
	var _=JASTEggIt;
		window['JASTEggIt'] = JASTEggIt;
		
	JASTEggIt.startup();
}