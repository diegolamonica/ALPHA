/*
Script Name: 	Windowed Links (http://jastegg.it/eggs/wili/ ) 
version: 		2.0 beta
version date:	2007-10-19
Plugin for:		JAST ( http://jastegg.it )
--------------------------------
*/
JASTEggIt.extend(
	'wiLi', {
		info: {
			title: 		'Windowed Links',
			version:	'2.0 beta',
			eggUrl:		'http://jastegg.it/eggs/fx',
			author:		'Diego La Monica',
			url:		'http://diegolamonica.info',
			previousVer:'http://wili.diegolamonica.info'
		},
	options: {
		id:  						'windowedLinks',
		autoStart:					true,
		accessKey:					'w',
		newWindowHTMLDescription:	'Apri i collegamenti esterni in una nuova finestra',
		sameWindowHTMLDescription: 	'Apri tutti i collegamenti in questa finestra',
		newWindowTitleDescription: 	'Apre tutti i link definiti come esterni in una nuova finestra',
		sameWindowTitleDescription: 'Apre indiscriminatamente tutti i link nella stessa finestra',
		className: 					'wl-ext',
		noNewWindowsURL: 			[ ],
		forcedNewWindowURL: 		['/tutorials/examples/', '/external_dir/'],
		newWindowForceCSSClass:		'wili-forced',
		popupWindowName: 			'',
		popupProperties:			'',
		openNewWindow:				false
	},



	/* Cookies Manager */

	setCookie: function (c_name,value,expiredays){
		var exdate=new Date();
		exdate.setDate(exdate.getDate()+expiredays);
		document.cookie=c_name+ "=" +escape(value) + ((expiredays==null) ? "" : ";expires="+exdate.toGMTString());
	},


	getCookie: function (c_name){
		if (document.cookie.length>0){
			c_start=document.cookie.indexOf(c_name + "=");
			if (c_start!=-1){ 
				c_start=c_start + c_name.length+1;
				c_end=document.cookie.indexOf(";",c_start);
				if (c_end==-1) c_end=document.cookie.length;
    				return unescape(document.cookie.substring(c_start,c_end));
			} 
		}
		return "";
	},

	
	/* End System Functions */
	
	/* Link Manager */	
	onKeyPressEvent:  function(link,event){
		if(link.realkeypress != null) link.realkeypress(event);
		var keynum = JASTEggIt.kbd.getKeyPressed(event);
		if(keynum == 13 || keynum==32){
			this.openLink(link);
			return false;
		}
	},
	alterLinks: function(){
		var links = window.document.getElementsByTagName('a');
		for(var i = 0; i<links.length; i++){
			if( links[i].href != null && links[i].onclick == null){
				var escludi = false;
				for(var j = 0; j < this.options.noNewWindowsURL.length; j++){
					var escluso = this.options.noNewWindowsURL[j]; 
					if(links[i].href.substr(0, escluso.length) == escluso){
						escludi = true;
						break;
					}
				}
				if(
					links[i].protocol == 'javascript:' ||
					links[i].protocol == 'mailto:' ||
					links[i].protocol == 'callto:'
				) {
					escludi = true;
					otherProtocol = true;	
				}else{
					otherProtocol = false;
				}
				if(escludi && !otherProtocol){
					// Only if link is excluded by opening in new window
					// we make sure it's not into forcing array
					
					for(var j = 0; j < this.options.forcedNewWindowURL.length; j++){
						var incluso = this.options.forcedNewWindowURL[j]; 
						if (incluso.substr(0,1) == '/') incluso = window.location.protocol + '//' + window.location.host + incluso;
						if(links[i].href.substr(0, incluso.length) == incluso){
							escludi = false;
							break;
						}
						if(links[i].className.indexOf(this.options.newWindowForceCSSClass)>-1){
							escludi = false;
							break;
						}						
					}
				}
				if(!escludi){
					if(links[i].onkeypress!= null){
						// Extends onkeypress event
						links[i].realkeypress = links[i].onkeypress;
					}
					eval("links[i].onkeypress = function(event){ return JASTEggIt['" + this.name + "'].onKeyPressEvent(this,event);}");
					eval("links[i].onclick = function (){  return JASTEggIt['" + this.name + "'].openLink(this); }; ");
					
					if(this.options.className!=''){
						links[i].className += (links[i].className!=''?' ':'') + this.options.className;
					}
					
				}
			}
		}
	},
	
	openLink:	function(link){
		if(this.options.openNewWindow || link.className.indexOf(this.options.newWindowForceCSSClass)> -1){
			/*
			 * Check if properties are defined on class attribute
			 */
			var popupProperties = this.options.popupProperties;
			if(classes!=''){
				var firstProperty = true;
				var classes = JASTEggIt.strings.split(link.className, ' ');
				for(i = 0; i < classes.length; i++){
					if(classes[i].substr(0,5) == 'wili-' && classes[i] != 'wili-forced'){
						var suffix = classes[i].substr(5,classes[i].length);
						if(firstProperty){
							popupProperties = '';
							firstProperty = false;
						}
						if(popupProperties != '') popupProperties +=',';
						var params = JASTEggIt.strings.split(suffix,'-');
						popupProperties += params[0] + '=' + params[1];
					}
				}
			}
			
			window.open(link.href, this.options.popupWindowName, popupProperties);
			return false;
		}
		return true;
	},
	
	newWindowYesNo: function(firstTime){
		if(firstTime){
			this.options.openNewWindow = this.getCookie('wiLi')==null?this.options.openNewWindow:this.getCookie('wiLi');
		}else{
			this.options.openNewWindow = !this.options.openNewWindow;
			this.setCookie('wiLi', this.options.openNewWindow,9000);
		}
		var l = document.getElementById(this.options.id);
		if(l == null) return false;
		var buffer = this.options.sameWindowHTMLDescription;
		var title = this.options.sameWindowTitleDescription;
		if(this.options.openNewWindow){
			buffer = this.options.newWindowHTMLDescription;
			title = this.options.newWindowTitleDescription;
		}
		title = title.replace(/"/g, "&quot;");
		buffer = '<a accesskey="' + this.options.accessKey + '"' +
			(title!=''?' title="' + title + '"':'') +
			' href="#" onclick="return JASTEggIt[\'' + this.name + '\'].newWindowYesNo();">' + 
			buffer + '<\/a>';
		
		l.innerHTML = buffer;
		return false;
	},
	startup: function(){
		if(this.options.autoStart) this.init();
	},
	
	init: function(){
		this.options.noNewWindowsURL[this.options.noNewWindowsURL.length] = window.location.protocol + '//' + window.location.host;
		this.newWindowYesNo(true);
		this.alterLinks();
	}
});
