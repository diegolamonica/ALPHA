/*
Script Name: 	Character Counter - (http://jastegg.it/eggs/CharacterCounter/ ) 
version: 		1.0 beta
version date:	2007-10-19
Plugin for:		JAST ( http://jastegg.it )
--------------------------------
*/
JASTEggIt.extend(
	'characterCount', {
		info: {
			title: 		'Character Counter',
			version:	'1.0 beta',
			eggUrl:		'http://jastegg.it/eggs/characterCounter',
			author:		'Diego La Monica',
			url:		'http://diegolamonica.info'
		},
		options:	{
			textSource: 	'',	// required
			destination:	null,
			message:		'(%CHARS%)',
			limitWarning:	0,
			warningMethod:	null,
			restoreMethod: 	null
		},
		_counters : [],
		setup: function(options){
			options = JASTEggIt.mergeOptions(options, this.options );
			if(options.textSource=='') return false; 
			var idx = this._counters.length;
			this._counters[idx] = options;
			JASTEggIt.Listener.watch(options.textSource, 'value', function(itm){
				JASTEggIt.characterCount.update(idx);
			} )
		},
		update: function(idx){
			var itm = this._counters[idx];
			var src = JASTEggIt._id(itm.textSource);
			if(src==null) return false;
			var limit = itm.limitWarning;
			var l = src.value.length;
			if((l > limit) && (limit>0) && itm.warningMethod!=null){itm.warningMethod(l);}
			if((l<= limit) && (limit>0) && itm.restoreMethod!=null){itm.restoreMethod();}
			if(itm.destination!=null){
				var dest = JASTEggIt._id(itm.destination);
				if(dest!=null){
					dest.innerHTML = itm.message.replace('%CHARS%', l);
				}
			};
		}
	}
);