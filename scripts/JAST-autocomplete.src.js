/*
Script Name: 	Autocomplete
version: 		2.0.1
version date:	2012-08-28
Plugin for:		JAST ( http://jastegg.it )
Inside:			ALPHA Framework ( http://www.alpha-framework.com )
License:		LGPL v3.0
--------------------------------------------------------------
Version | Changelog
--------+-----------------------------------------------------
1.0.12  | BUGFIX: Multiple enter gets the last selected value
		| BUGFIX: List is not selectable by mouse
--------+-----------------------------------------------------		
1.0.13  | BUGFIX: If null is returned, script will raise a 
		|         runtime erorr
--------+-----------------------------------------------------  
2.0.1	| BUGFIX: Fixed element listing from cache
		| BUGFIX: If no items are listed the output block will
		|		  not be shown
		| BUGFIX: Fixed navigation throug keyboard arrows
		| BUGFIX: Fixed event on item
		| NEW:    Added "limit" option
		| UPDATE: Some code refactoring
		| NEW:    Encapsulated the code in a lamba function
--------+-----------------------------------------------------  
*/

var AC_SOURCE_PAGE = 'page';
var AC_SOURCE_JSON = 'json';
var AC_PARSER_JSON = 'parseJSON';

(function(_){
	
	_.extend(
		'Autocomplete', {
			info: {
				title: 		'Autocompleter',
				version:	'2.0',
				eggUrl:		'http://jastegg.it/eggs/autocomplete',
				author:		'Diego La Monica',
				url:		'http://diegolamonica.info'
			},
			keyboards:	{
				up:		38,
				down:	40,
				enter:	13
			},
			options:	{
				source:			AC_SOURCE_PAGE,
				page:			'autocomplete-response.php',
				pageParam:		'q',
				method:			'GET',
				outputId:		'div-autocomplete',
				parser:			AC_PARSER_JSON,
				formatOutput:	null,
				caseSensitive:	false,
				minChar:		3,
				separator:		',',
				separatorOnConfirm: true,
				refresh:		100,
				jsonItems:		null,
				cacheValue:		'',
				selectedItem:	null,
				onConfirm:		null,
				onBlur:			null,
				onTyping:		null,
				isActive:		false,
				limit:			30
			},
			_items:	[],
			_currentEditingOn: null,
			setup: function(id, options){
				var txt = _._id(id);
				if(txt==null) return false;
				options =_.mergeOptions(options, this.options);
				options['currentValue'] = txt.value;
				this._items[id] = options;
				var E = _.Events;
				var A = _.Autocomplete;
				E.add(id, 'keydown', 	A._keyDown);
				E.add(id, 'keypress', 	A._keyConfirm);
				E.add(id, 'blur', 		A._blur);
				E.add(id, 'focus', 		A._focus);
				E.add(id, 'keyup',		A._keyUp);
				txt.setAttribute('autocomplete','off');
				
			},
			_keyUp: function(e){
				if(_.kbd.getKeyPressed(e) != _.Autocomplete.keyboards.enter) _.Autocomplete._check(_.Events.generator(e).id);
				
			},
			_focus: function(e){
				var id = _.Events.generator(e).id;
				var itm = _.Autocomplete._items[id];
				_.Autocomplete._currentEditingOn = id;
				itm.currentValue = _._id(id).value;
				itm.isActive = true;
			},
			_blur: function(e){
				var id = _.Events.generator(e).id;
				var itm = _.Autocomplete._items[id];
				var oid = _._id(itm.outputId);
				
				if(oid!=null)
					setTimeout('_.DOM.setStyle("' + itm.outputId + '", {display: "none"}); ', 100 );
				if(itm.onBlur!=null) itm.onBlur(itm);
				itm.isActive=false;
			},
			_keyConfirm: function(e){
				var A = _.Autocomplete;
				var kp = _.kbd.getKeyPressed(e);
				var id = _.Events.generator(e).id;
				if(kp== A.keyboards.enter){
					var itm = A._items[id];
					var o = itm.outputId;
					if(_._id(o)!=null){
						if (_._id(o).style.display=='none'){
							_.Events.abort(e);
						}
					}
					var i = itm.selectedItem;
					A._append(id,i);
					_.Events.abort(e); 
				}
			},
			_keyDown: function(e){
				var A = _.Autocomplete;
				var kp = _.kbd.getKeyPressed(e);
				var id = _.Events.generator(e).id;
				var refresh = false;
				var o = A._items[id];
				var div = _._id(o.outputId);
				if(div!=null && (kp == A.keyboards.down || kp == A.keyboards.up)){
					_.Events.abort(e);
					var ul = _._name('ul', div);
					ul = ul[0];
					var lis = _._name('li', ul);
					if(o.selectedItem== null || o.selectedItem==-1){
						o.selectedItem = (kp == A.keyboards.down)?0:(lis.length-1);
						_.DOM.appendClass(lis[o.selectedItem],'selected');
						_.ARIA.setProperty(lis[o.selectedItem], 'selected','true');
					}else{
						for(var i= 0; i<lis.length; i++){
							if(_.DOM.hasClass(lis[i],'selected')){
								if (kp == A.keyboards.down) {
									if (i == lis.length - 1){
										o.selectedItem = 0;
									} else{
										o.selectedItem = i+1;
									}
								}else{
									if (i == 0){
										o.selectedItem = lis.length-1;
									} else{
										o.selectedItem = i-1;
									}
								}
								_.DOM.removeClass(lis[i],'selected');
								_.ARIA.setProperty(lis[i], 'selected','false');
								_.DOM.appendClass(lis[o.selectedItem],'selected');
								_.ARIA.setProperty(lis[o.selectedItem], 'selected','true');
								break;
							}
						}
						
					}
					
				}
			},
			_check: function(id){
				var A = _.Autocomplete;
				var itm = _.Autocomplete._items[id];
				if(!itm.isActive) return;
				
				var txt = _._id(id);
				if(txt==null) return false;
				var currentValue =txt.value;
				var itm = A._items[id];
				if (currentValue != itm.currentValue ){
					if(itm.onTyping) itm.onTyping(itm);
					var detail = '';
					if(itm.separator != ''){
						var details = eval('currentValue.split(/' + itm.separator + '/)');
						detail = details[details.length-1]; 
					}else{
						detail = currentValue;
					}
					detail = _.strings.trim(detail);
	
					if(detail.length >= itm.minChar){
						A._items[id].currentValue = currentValue;
						switch(itm.source){
							case AC_SOURCE_PAGE:
								var params = new Object; 
								params[itm.pageParam] = detail;
								_.xhttp.sendRequest(itm.method, itm.page, params, '_.Autocomplete.' + itm.parser + '(\'' + id + '\',%%BUFFER%%);');
								break;
							case AC_SOURCE_JSON:
								this._processJSON(id, itm.jsonItems);
								break;
						}
					}else{
						_.DOM.setStyle(itm.outputId, {
							display: 'none'
						});
						
					}
				}
			},
			_append: function(id, rowIndex){
				if(rowIndex==null){
					var li = _.Events.generator(id);
					while(li.tagName.toUpperCase() != 'LI') 
						li = li.parentNode;
					_.DOM.appendClass(li, 'selected');
					var ul = li.parentNode;
					var lis = _._name('li',ul);
					for(var i=0; i<lis.length; i++){
						if (_.DOM.hasClass(lis[i], 'selected')) {
							rowIndex = i;
							break;
						}
					}
					id = _.Autocomplete._currentEditingOn;
					
				}
				var itm = _.Autocomplete._items[id];
				if(!itm.cacheValue || !itm.cacheValue[rowIndex] || !itm.cacheValue[rowIndex][0] ) return false;
				var value = itm.cacheValue[rowIndex][0];
				var el = _._id(id);
				if(itm.separator != ''){
					var details = el.value.split(itm.separator);
					details[details.length-1] = (details.length!=1?' ':'') + value;
					detail = details.join(itm.separator);
					
					if(itm.separatorOnConfirm) detail += itm.separator;
				}else{
					detail = value;
				};
				el.value = detail;
				_.Autocomplete._items[id].currentValue = detail;
				_.DOM.setStyle(itm.outputId, {
					display: 'none'
				});
				if(itm.onConfirm){
					_.Autocomplete._items[id].selectedItem = rowIndex;
					itm.onConfirm(id);
				}
				
			},
			_processJSON: function(id, json){
				var itm = this._items[id];
				
				var selected = itm.selectedItem;
				var buffer = '';
				var detail = '';
				if(itm.separator != ''){
					var details = eval('itm.currentValue.split(/' + itm.separator + '/)');
					detail = details[details.length-1]; 
				}else{
					detail = itm.currentValue;
				};
				detail = _.strings.trim(detail);
				var viewCount = 0;
				json = this._purgeJson(json, detail, itm.caseSensitive);
				var ul = null;
				var D =_.DOM;
				var outElem = _._id(itm.outputId);
				if(outElem==null){
					outElem = D.createOnDocument('div', null, null, itm.outputId);
					ul = D.createChild('ul', outElem);
					_.ARIA.addRole(_._id(id),'combobox');
					_.ARIA.setProperty(ul,{
						live: 		'assertive',
						relevant: 	'additions,text'
					});
				}else{
					ul = _._name('ul', outElem);
					ul = ul[0];
				}
				var lis = _._name('li', ul);
				_.DOM.remove( lis );
				
				if(!_.Array.is(itm.cacheValue)) itm.cacheValue = [];
				var selectedRow = (itm.selectedItem!=null && itm.selectedItem != -1)?itm.cacheValue[itm.selectedItem]:null;
				itm.selectedItem = -1;
				if (json != null) {
					if(_.Array.is(itm.cacheValue)){
						/*
						 * Removing unnecessary items from the cache
						 */
						for (var i=itm.cacheValue.length-1;  i>=0; i--){
							if(!_.Array.isIn(json, itm.cacheValue[i])){
								itm.cacheValue.splice(i, 1);
							}
						}
						/*
						 * Pushing in the cache the new values
						 */
						for (var i=0; i<json.length; i++){
							if(!_.Array.isIn(itm.cacheValue, json[i])){
								itm.cacheValue.push(json[i]);
							}
						}
						
						itm.cacheValue.sort(function(a,b){
							return(a[0]>b[0]);
						});
					}else{
						 _.Autocomplete._items[id].selectedItem = -1;
					}
					
					
					
					if(itm.cacheValue.length>0){
						var txt = _._id(id);
						var pos = D.position(txt);
						D.setStyle(outElem, {
							display: 'block',
							position: 'absolute',
							left: 	pos.x + 'px',
							top: 	pos.y + txt.offsetHeight + 'px',
							zIndex:	99
						});
						for(var i = 0; i<itm.cacheValue.length && i < itm.limit; i++){
							
							var row = itm.cacheValue[i];
							if(row!=null){
								
								var li = _.DOM.createChild('li',ul);
								_.ARIA.addRole(li, 'options');
								_.ARIA.setProperty(li,'selected','false');
								
								var bufferText = '';
								if (itm.formatOutput != null) {
									bufferText = itm.formatOutput(row);
								}else {
									bufferText += row[0];
								};
								
								var k = bufferText.toUpperCase().indexOf(detail.toUpperCase());
								if (k != -1) {
									var textBefore = (k>0)?(bufferText.substring(0, k)):'';
									var detail = bufferText.substring(k, k+detail.length);
									var textAfter = bufferText.substring(k + detail.length, bufferText.length)
									bufferText = textBefore + '<strong>' + detail + '</strong>' + textAfter;
								};
								
								li.innerHTML = bufferText;
								if(selectedRow === row){
									D.appendClass(li, 'selected');
									_.ARIA.setProperty(li, 'selected','true');
									itm.selectedItem = i;
								}
								_.Events.add(li, 'mousedown',function(e){
									var element = _.Events.generator(e);
									while(element.tagName.toUpperCase() != 'LI') 
										element = element.parentNode;
									console.log(element);
									// Remove the selection from the LI
									var lis = _._name('li', element.parentNode);
									D.removeClass(lis, 'selected');
									
									D.appendClass(element, 'selected');
									_.ARIA.setProperty(element, 'selected','true');
									for(var i = 0; i<lis.length; i++){
										if(D.hasClass(lis[i],'selected')){
											itm.selectedItem = i;
											break;
										}
									}
									_.Autocomplete._append(e);
								});
								
							}
						}
					}
				}
				
			},
			_purgeJson: function(json, detail, caseSensitive){
				var retJson = Array();
				if(caseSensitive) detail = detail;
				if(json==null) return json;
				for(var i=0; i<json.length; i++){
					var row = json[i];
					var k = -1;
					if(caseSensitive) 
						var k = row[0].indexOf(detail);
					else
						var k = row[0].toUpperCase().indexOf(detail.toUpperCase());
					
					if(k!=-1) retJson[retJson.length] = json[i];
				}
				return retJson;
			},
			parseJSON: function(id,buffer){
				var json = eval(buffer);
				_.Autocomplete._processJSON(id, json);
			}
		}
	);
})(JASTEggIt);