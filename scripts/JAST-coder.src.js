JASTEggIt.extend('Coder',{
	options: {
		codeBoxWidth: '500px',
		codeBoxBorder: '1px solid #ddd',
		lineBottomBorder: '1px solid #ddd',
		lineNumberBackgroundColor: '#ccc',
		lineNumberColor: '#800',
		backgroundColor: '#fff',
		oddRowColor: '#eee',
		foregroundColor: '#008', 
		displayRowNumber: true,
		displayInfo: true,
		onFormatRow: null,
		rowSeparator: '<br />'
	},
	startup: function(){
		var allCode = JASTEggIt._class('code');
		for(var i=0; i<allCode.length; i++){
			JASTEggIt.Coder.setup(allCode[i]);
		}		
	},
	setup: function(codeNode, options){
		if(options==null) options = JASTEggIt.Coder.options;
		options = JASTEggIt.mergeOptions(options, JASTEggIt.Coder.options);
		if(options.onFormatRow == null) options.onFormatRow = JASTEggIt.Coder.simpleFormat;
		
		codeNode = JASTEggIt._el(codeNode);
		var code = codeNode.innerHTML;
		JASTEggIt.DOM.setStyle(codeNode, {
			display: 'block',
			'width': options.codeBoxWidth,
			'border': options.codeBoxBorder,
			'fontFamily':'Courier, fixed'
		});
		codice = code.split(options.rowSeparator);
		var buffer = '';
		var codeBuffer = '';
		for(var i=0; i<codice.length;i++){
			if(codice[i].replace(/\s/g,'')!='') break;
			codice[i] =null;
		}
		for(var i=codice.length-1; i>0;i--){
			if(codice[i].replace(/\s/g,'')!='') break;
			codice[i] =null;
		}
		codice = JASTEggIt.Array.purge(codice);
		for(var i=0; i<codice.length;i++){
			if(options.displayRowNumber) buffer += '<div style="border-bottom: ' + options.lineBottomBorder + ';">'+ (i+1) + '</div>';
			codeBuffer += '<div style="padding-left: 2em; border-bottom: ' + options.lineBottomBorder + ';';
			if((i%2)==1) codeBuffer += ' background-color: ' + options.oddRowColor;
			codeBuffer += '">&nbsp;';
			codeBuffer += /*'<nobr>'+*/ options.onFormatRow(codice[i]) + '&nbsp;' /* + '</nobr>'*/;
			codeBuffer +='</div>';
		}
		codeNode.innerHTML ='';
		buffer = 
		buffer = '<div class=" clear: both; codeLineNumber" style="position: absolute; background-color:'+options.lineNumberBackgroundColor+'; color:'+options.lineNumberColor+'";>' + buffer +'</div>';
		codeBuffer = '<div class="coderContainer" style="white-space: nowrap; overflow: auto; color:'+options.foregroundColor+'">' + codeBuffer +  '</div>';
		codeNode.innerHTML ='<div><div><strong>Made with: <a href="http://jastegg.it">JAST</a></strong></div>'+ buffer + codeBuffer;// + '<span style="display:block; clear: both;">&nbsp;</span></div>';

		var cc = _._class('coderContainer');
		if(cc.length>1) cc = cc[cc.length-1]; 
			else	cc = cc[0];
		
		var ccsw = cc.scrollWidth;
		
		var allDivs=  _._name('div',cc);
		for(var i = 0; i<allDivs.length; i++)
			_.DOM.setStyle(allDivs[i], { width: ccsw + 'px'});
		
		
		
	},
	simpleFormat: function(codeString){
		return codeString.replace(/\t/g,'&nbsp;&nbsp;')
		
	}	
});


