/*
Script Name: 	Accessible Rich Text Box Editor- (http://jastegg.it/eggs/richtextbox/ )
Author:			Diego La Monica 
version: 		1.0 beta
version date:	2007-17-01
Plugin for:		JAST ( http://jastegg.it )
--------------------------------
*/
JASTEggIt.extend('RTB',{
		options: {
			preview: 		true,
			toolbar: 		true,
			bold:			null,
			italic: 		null,
			underline: 		null,
			strikethru:		null,
			extra: 			null,
			/*
			 * extra: [
			 * 		{
			 * 			content: 'title',
			 * 			description:	'description tip',
			 * 			className:		'extra-button-class-name',
			 * 			event:			function(id){
			 * 			}
			 * 		}, 
			 * 		{
			 * 			...
			 * 		}
			 * ]
			 */ 
			extraRendering:	null,
			className: 		'jast-rtb-toolbar'
		},
		_options: [],
		id: '',
		action: function(id, param){
			_.RTB.insertAtCursor(id, param, param);
			return false; 
		},
		setup: function(id, options){

			options = JASTEggIt.mergeOptions(options, this.options);
			options.id = id;

			if (options.toolbar ) this.createToolbar(options); 
			if (options.preview ) this.createPreview(options);

			if(JASTEggIt.Browser.ie){
				
				JASTEggIt.event(JASTEggIt._id(id), 'keydown', JASTEggIt.RTB.keyDown );
			} else{
				JASTEggIt._id(id).onkeypress = JASTEggIt.RTB.checkCombination;
			}

			this._options[id] = options;
		},
		isIn: function(code, cases){
			for(var i =0; i< cases.length; i++){
				if(code == cases.charCodeAt(i)) return true;
			}
			return false;
		},
		insertAtCursor: function (element, start, end, replacement) {
			var e = JASTEggIt._el(element);
			if (document.selection) {// IE
				e.focus();
				caretPos = document.selection.createRange().duplicate();
				if(replacement!=null)
					caretPos.text = start + replacement + end;
				else
					caretPos.text = start + caretPos.text + end;
				if (caretPos.text.length == 0){
					caretPos.moveStart("character", -end.length);
					caretPos.moveEnd("character", -end.length);
					caretPos.select();
				}
				e.focus(caretPos);
			} else if (e.selectionStart || e.selectionStart == '0') {// MOZILLA
				e.focus();
				var startPos = e.selectionStart;
				var endPos = e.selectionEnd;
				var preTxt = e.value.substring(0, startPos);
				if(replacement!=null)
					var selTxt =  replacement;
				else
					var selTxt = e.value.substring(startPos, endPos) ;
				var follTxt = e.value.substring(endPos, e.value.length);
				var scrollPos = e.scrollTop;
				e.value = preTxt + start + selTxt + end + follTxt;
				if (e.setSelectionRange){
					if (selTxt.length == 0)
						e.setSelectionRange(startPos + start.length, startPos + start.length);
					else
						e.setSelectionRange(startPos, startPos + start.length + selTxt.length + end.length);
					e.focus();
				}
				e.scrollTop = scrollPos;
			} else {
				e.value += start + end;
			}
		},
		parseString: function(id,p){
			p = p.replace(/\\\*/g, "&#42;");
			p = p.replace(/\\\//g, "&#47;");
			p = p.replace(/\\\_/g, "&#95;");
			p = p.replace(/\\\-/g, "&#45;");
//			p = p.replace(/\\(.)/g, "$1");
			if(this._options[id].italic!=null)  	p = p.replace(/(^|[^\/:a-z0-9])\/(.+?)\/([^:\/a-z]|$)/g, "$1<em>$2</em>$3");
			if(this._options[id].bold!=null)  		p = p.replace(/(^|[^\*:a-z0-9])\*(.+?)\*([^:\*a-z0-9]|$)/g, "$1<strong>$2</strong>$3");
			if(this._options[id].underline!=null)  	p = p.replace(/(^|[^\_:a-z0-9])\_(.+?)\_([^:\_a-z0-9]|$)/g, "$1<span style=\"text-decoration:underline;\">$2</em>$3");
			if(this._options[id].strikethru!=null)  p = p.replace(/(^|[^\-:a-z0-9])\-(.+?)\-([^:\-a-z0-9]|$)/g, "$1<del>$2</del>$3");
			
			p = p.replace(/\^2\s*([^\^]+)\^\r?\n/g, "<h2>$1</h2>");
			p = p.replace(/\^3\s*([^\^]+)\^\r?\n/g, "<h3>$1</h3>");
			p = p.replace(/\^4\s*([^\^]+)\^\r?\n/g, "<h4>$1</h4>");
			p = p.replace(/\^5\s*([^\^]+)\^\r?\n/g, "<h5>$1</h5>");
			p = p.replace(/\^6\s*([^\^]+)\^\r?\n/g, "<h6>$1</h6>");
			p = p.replace(/\^([^\^]+)\^\r?\n/g, "<h1>$1</h1>");
			p = p.replace(/(((^|\n)>.*)+)/g, "<blockquote>$1</blockquote>");
			p = p.replace(/(^|\n)(-\s)(.*)/mg, "$1<li>$3</li>");
			p = p.replace(/((<li>.*<\/li>(\n)*)+)/g, "<ul>$1</ul>");
			p = p.replace(/((^|\n)(1\.\s?))(.*)/g, "$1<li>$4</li>");
			p = p.replace(/((1\.\s?<li>.*<\/li>(\n)*)+)/g, "<ol>$1</ol>");
			p = p.replace(/1\.\s?<li>/g, "<li>");
			p = p.replace(/<\/li>\n/g, "</li>");
			p = p.replace(/<\/blockquote>\n/, '</blockquote>');
			
			// Gesitone dei links
			p = p.replace(/(^|[^<])((https?|ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])($|[^>])/ig, "$1<a href=\"$2\" title=\"open url\">$2</a>$4" );
			p = p.replace(/"([^"]+)"\s+<((https?|ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])>/ig, "<a href=\"$2\" title=\"open url\">$1</a>" );
			//p = p.replace(/\b((https?|ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig, "<a href=\"$1\" title=\"open url\">$1</a>" );
			p = p.replace(/\b(?:mailto:)?([A-Z0-9._%-]+@[A-Z0-9._%-]+\.[A-Z]{2,4})\b/ig, "<a href=\"mailto:$1\" title=\"send mail\">$1</a>");
			
			// Gestione del colore
			p = p.replace(/(\[([a-fA-F0-9]{3})\](.*?)\[\2\])/g, "<span style=\"color: #$2\">$3</span>");
			p = p.replace(/\n/g, "<br />");
			if(this._options[id].extraRendering!=null) p = this._options[id].extraRendering(p); 
			return p;
		},
		showPreview: function(id){
			var area 	= JASTEggIt._el(id);
			var id 		= area.id;
			var p 		= area.value;
			p = this.parseString(id, p);
			var pr = JASTEggIt._id(id + '-preview');
			pr.innerHTML = '<h3>Preview</h3>' + '<p>' + p + '</p>';
		},
		checkCombination: function(event){
			if(!event.ctrlKey) return true;
			var cc = JASTEggIt.kbd.getKeyPressed(event);
			if(cc==17) return false;
			if( JASTEggIt.RTB.isIn(cc, 'buis')){
				//alert('checking');
				var id = JASTEggIt.Events.generator(event).id;
				var o = JASTEggIt.RTB._options[id];
				
				if( JASTEggIt.RTB.isIn(cc, 'b') && (o.bold!=null) ) JASTEggIt.RTB.action(id, '*');
				else if( JASTEggIt.RTB.isIn(cc, 'i') && (o.italic!=null) ) JASTEggIt.RTB.action(id, '/');
				else if( JASTEggIt.RTB.isIn(cc, 'u') && (o.underline!=null) ) JASTEggIt.RTB.action(id, '_');
				else if( JASTEggIt.RTB.isIn(cc, 's') && (o.strikethru!=null) ) JASTEggIt.RTB.action(id, '-');

				return false;
			}
		},
		
		keyDown: function(){
			
			if(window.event) return JASTEggIt.RTB.checkCombination(window.event);
		},
		createLink: function(container, title, className, textContent, idRel){
			var a = _.DOM.createChild('A',container);
			a.setAttribute('title', title);
			a.rel=idRel;
			var txt = document.createTextNode(textContent);
			a.appendChild(txt);
			//a.innerHTML = htmlContent;
			_.DOM.appendClass(a,className);
			return a;
		},
		createToolbar: function(options){
			var id = options.id;
			var rtba = _._id(id);
			
			var tb = JASTEggIt.DOM.createOnDocument('div', rtba, null, id + '-toolbar');
			if(options.bold!=null) 		var boldLink = _.RTB.createLink(tb, 'Grassetto', 'bold', options.bold, id);
			if(options.italic!=null) 	var italicLink = _.RTB.createLink(tb, 'Corsivo', 'italic', options.italic, id);
			if(options.underline!=null) var underLink = _.RTB.createLink(tb, 'Sottolineato', 'underline', options.underline, id);
			if(options.strikethru!=null) var strikeLink = _.RTB.createLink(tb, 'Barrato', 'strikethru', options.strikethru, id);
			if(boldLink) _.Events.add(boldLink, 'click', function(event){
				_.RTB.action(id,'*');
				_.Events.abort(event);
			});
			if(italicLink) _.Events.add(italicLink, 'click', function(event){
				_.RTB.action(id,'/');
				_.Events.abort(event);
			});
			if(underLink) _.Events.add(underLink, 'click', function(event){
				_.RTB.action(id,'_');
				_.Events.abort(event);
			});
			if(strikeLink) _.Events.add(underLink, 'click', function(event){
				_.RTB.action(id,'-');
				_.Events.abort(event);
			});
			if(options.extra){
				for(var i = 0; i<options.extra.length; i++){
					var xb = options.extra[i];
					var xlink = _.RTB.createLink(tb, xb.title,xb.className, xb.content, id+'@'+i);
					_.Events.add(xlink, 'click', function(event){
						
						var theLink = _.Events.generator(event);
						var rel = theLink.attributes['rel'].value;
						var j = rel.indexOf('@');
						var theId = rel.substr(0, j);
						var theIdx = rel.substr(j+1);
						var opts = _.RTB._options[theId];
						var xb = opts.extra[theIdx];
						xb.event(theId);
					});
				}
			}
			
		},
		createPreview: function(options){
			var rtba = JASTEggIt._id(options.id);
			var div = JASTEggIt.DOM.createOnDocument('div', null, rtba, options.id + "-preview");
			JASTEggIt.Listener.watch(options.id, 'value', function(itm){ JASTEggIt.RTB.showPreview(itm.id); } );
		}
	}
);