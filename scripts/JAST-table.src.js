/**
 * @author lamonica
 */
JASTEggIt.extend('Table', {
	options: {
		editableFields: 		null,
		bindingFields: 			null,
		actionBuilderFunction: 	null,
		headerRow: 				null,
		rowSeparator: 			'\n',
		onBeforeBinding:		null
	},
	
	_tables: [],
	
	setup: function(id, options){
		options = JASTEggIt.mergeOptions(JASTEggIt.Objects.clone(this.options), options);
		this._tables[id] = options;
		this.refresh(id);
		
	},
	apply: function(id){
		var options = JASTEggIt.Table._tables[id];
		if (options == null) return;
		
		if (JASTEggIt.Array.is(options.bindingFields)) {
			var righe = JASTEggIt.Table.getData(id);
			if(options.onBeforeBinding && options.onBeforeBinding(id, righe)===false)return false; 
			var values 		= [];
			var offset 		= (options.actionBuilderFunction!=null)?-1:0;
			var primaRiga 	= (options.headerRow==null)?0:1;
			
			
			for(var i=primaRiga; i<righe.length; i++){
				
				for(var j=0; j<righe[i].length; j++){
					
					if(j+offset>=0){
						if(values[j+offset]==null) values[j+offset]='';
						values[j+offset] += ((values[j+offset]!='')?options.rowSeparator:'') + righe[i][j];
					}
					
				}
			}
			
			for(var i = 0; i<options.bindingFields.length; i++){
				JASTEggIt._id(options.bindingFields[i]).value = values[i];
			}
			if(options.onBinding) options.onAfterBinding(id, righe);
		}
		
	},
	refresh: function(id){
		var options = JASTEggIt.Table._tables[id];
		if (options == null) return;
		if(JASTEggIt.Array.is(options.bindingFields)){
			if(options.onBeforeRefresh && options.onBeforeRefresh(id)===false)return false;
			var values = []; 
			for(var i = 0; i< options.bindingFields.length; i++){
				var value = JASTEggIt._id(options.bindingFields[i]).value;
				values.push( JASTEggIt.strings.split(value, options.rowSeparator) );
			}
			
			var righe = [];
			
			// Imposto l'heading
			
			if(options.headerRow!=null){
				var headingRow = [];
				if(options.actionBuilderFunction) headingRow.push('&nbsp;');
				for(i=0;i<options.headerRow.length; i++){
					headingRow.push(options.headerRow[i]);
				}
				righe.push(headingRow);
			} 
			
			if(values[0][0]!=''){
				for(var i=0; i<values[0].length; i++){
					var riga = []; 
					// imposto tutte le righe
					if(options.actionBuilderFunction){
						riga.push(options.actionBuilderFunction);
					}
					for(j=0; j<values.length; j++){
						riga.push(values[j][i]);
					}
					righe.push( riga );
				}
				
			}
			JASTEggIt.Table.setData(id, righe);
			if(options.onRefresh) options.onRefresh(id);
		}
	},
	
	getData: function(id){
		var t = JASTEggIt._el(id);
		var righe = [];
		for(var i=0; i<t.rows.length; i++){
			var colonne = [];
			var r = t.rows[i];
			for (var j = 0; j< r.cells.length; j++){
				var c = r.cells[j];
				colonne.push(c.innerHTML);
			}
			righe.push(colonne);
		}
		return righe; 
	},
	setData: function(id, data, extended){
		var t = JASTEggIt._el(id);
		for(var i = 0; i<data.length; i++){
			this.replaceRow(id, i, data[i]);
		}
		
		while(data.length<t.rows.length){
			t.deleteRow(t.rows.length-1); 
		}
		JASTEggIt.Table.apply(id);
	},
	appendRow: function(id, data){
		data = JASTEggIt.Table.normalizeRow(id, data);
		var o = JASTEggIt.Table._tables[id];
		if(o==null) o = JASTEggIt.Table.options; 
		if(o.onBeforeAppendRow && o.onBeforeAppendRow(id, data)===false)return false;
		var t = JASTEggIt._el(id);
		var r = t.rows;
		var i = r.length;
		
		t.insertRow(i);

		var lastCell = 0;
		var colSpanned = 0;
		var c = r[i].cells;

		for(var j=0; j<data.length; j++){
			
			if(data[j]=='[colspan]'){
				c[lastCell].colSpan += 1;
				colSpanned += 1;
			}else{
				var cx = c[j-colSpanned]; 
				if(cx==null) cx = (r[i].insertCell(j-colSpanned));
				cx.colSpan= 1;
				var d =data[j] ;
				if(typeof d=== 'function') cx.innerHTML = d(id);
						 else cx.innerHTML = d;
				lastCell = j;
			}
		}
		JASTEggIt.Table.apply(id);
		if(o.onAppendRow) o.onAppendRow(id, data);
	},
	
	normalizeRow: function(id, data){
		var o = JASTEggIt.Table._tables[id];
		if (o==null) return false;

		if(o.onBeforeNormalizeRow && o.onBeforeNormalizeRow(id, data)===false)return false;
		if(o.actionBuilderFunction!=null){
			if(data[0]!= o.actionBuilderFunction){
				if(_.strings.trim(data[0])!= _.strings.trim(o.actionBuilderFunction(id))){
					var tempData = [];
					tempData.push(o.actionBuilderFunction);
					for (var i = 0; i < data.length; i++) {
						tempData.push(data[i]);
					}
					data = tempData;
				}
				
			}

		}
		if(o.onNormalizeRow) o.onNormalizeRow(id, data);
		return data;
	},
	
	replaceRow: function(id, i, data, normalize){

		
		
		if(normalize==null) normalize= false;
		
		var t = JASTEggIt._el(id);
		var r = t.rows;
		if(r.length<i+1){
			this.appendRow(id, data);
		} else {
			var o = JASTEggIt.Table._tables[id];
			if(o == null) o = JASTEggIt.Table.options; 
			if(o.onBeforeReplaceRow && o.onBeforeReplaceRow(id, i, data)===false)return false;
			
			if(normalize){
				data = JASTEggIt.Table.normalizeRow(id, data);
			} 
			var collSpanned =0;
			var colSpan = 1;
			var c = r[i].cells;
			
			for(var j=0; j<data.length; j++){
				if(data[j]=='[colspan]'){
					colSpan += 1;
					c[lastCell].colSpan = colSpan;
					collSpanned += 1;
				}else{
					
					if(typeof(data[j])=='function'){
						var fn = data[j];
						data[j] = fn(id);
					}
					colSpan=1;
					cx = c[j-collSpanned];
					if(cx==null) r[i].insertCell(c.length);
					c[j-collSpanned].innerHTML = data[j];
					lastCell = j;
				}
				
			}
			while(c.length>data.length) r[i].deleteCell(c.length-1);
			var o = JASTEggIt.Table._tables[id];
			if(o==null) o = JASTEggIt.Table.options;
			if(o.onReplaceRow) o.onReplaceRow(id, i, data);
		}
		JASTEggIt.Table.apply(id);
	},
	
	switchRow: function(id, rowA, rowB){
		var o = JASTEggIt.Table._tables[id];
		if(o.onBeforeSwitchRow && o.onBeforeSwitchRow(id, rowA, rowB)===false)return false;
		var r = this.getData(id);
		
		if(rowA<0 || rowA>=r.length) return false;
		if(rowB<0 || rowB>=r.length) return false;
		var ra = r[rowA];
		var rb = r[rowB];
		
		r[rowA] = rb;
		r[rowB] = ra;
		this.setData(id, r);
		if(o.onSwitchRow) o.onSwitchRow(id, rowA, rowB);
		JASTEggIt.Table.refresh(id, true);
		JASTEggIt.Table.apply(id);
	},
	dropRow: function(id, rowIndex){
		if(o.onBeforeDropRow && o.onBeforeDropRow(id, rowIndex)===false)return false;
		var t = JASTEggIt._id(id);
		t.deleteRow(rowIndex);
		if(o.onDropRow) o.onDropRow(id, rowIndex);
		JASTEggIt.Table.apply(id);
		JASTEggIt.Table.refresh(id); 
	},
	editRow: function(id, rowIndex){
		var rows = this.getData(id);
		var row = rows[rowIndex];
		var o = JASTEggIt.Table._tables[id];
		var fieldsArray = o.editableFields;
		for(var i=0; i<fieldsArray.length; i++){
			var f = fieldsArray[i];
			var fld = JASTEggIt._id(f);
			var value= JASTEggIt.strings.trim(row[i+1]);
			if (fld.type == 'CHECKBOX' || fld.type=='RADIO'){
				if(fld.value==value) fld.checked=true;
			}else{
				fld.value = value;
			}
		}
	}
	
});
