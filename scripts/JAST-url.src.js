/*
Script Name: 	URL Manager (http://jastegg.it/eggs/URL/ ) 
version: 		1.0
version date:	2007-10-19
Plugin for:		JastEggIt ( http://jastegg.it)
--------------------------------
*/
JASTEggIt.extend(
	'URL', {
		info: {
			title: 		'URL Manager',
			version:	'1.0',
			eggUrl:		'http://jastegg.it/eggs/URL',
			author:		'Diego La Monica',
			url:		'http://diegolamonica.info'
		},
		_p: [],
		_url: '',
		completeUrl: window.location.href,
		getTo: function(occourrence){
			var url = this.completeUrl;
			var i = url.indexOf(occourrence);
			url = url.substring(0,i+occourrence.length);
			return url;
		},
		go: function(){
			var bufferURL = '';
			for(param in this._p){
				if(this._p[param] != null){
					if( bufferURL != '') bufferURL += '&';
					bufferURL += escape(param);
					if(this._p[param].hasValue) bufferURL += '=' + escape(this._p[param].value);
				}
			}
			window.location.href = this._url + '?' + bufferURL;
		},
		
		replaceParam: function(param, newValue){
			if(this._p==null) return false;
			this._p[param]= {
				value: newValue,
				hasValue: (newValue!=null)
			}
		},
		removeParam: function(param){
			if(this._p!=null &&  this._p[param]!=null) this._p[param] = null;
		},
		getParam: function (paramName){
			if(this._p == null) return null;
			return this._p[paramName];
		},
		startup: function(){
			var location = this.completeUrl.toString();
			var qs = '';
			i = location.indexOf('?');
			if(i!=-1){
				this._url = location.substring(0,i);
				qs = location.substring(i+1);
		 	}else{
				this._url = window.location.href;
			}
			this.queryString = qs;
			if(qs!=''){
				qs = qs.replace('%26','&');
				qs = qs.replace('&amp;','&');
				qs = qs.replace('%20',' ');
				qs = qs.replace('&nbsp;',' ');
				urlParams = qs.split('&');
				for(i = 0; i< urlParams.length; i++){
					var paramArray = urlParams[i].split('=');
					this._p[paramArray[0]] = {
						value: paramArray[1],
						hasValue: (paramArray.length>1)
					}
				}
			}
		}
	}
 );