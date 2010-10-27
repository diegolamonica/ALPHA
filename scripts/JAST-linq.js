var linq = {
	
	namingObject: 'item',
	items: [],
	from: function(genericObject, namingObject){
		if(namingObject == null) namingObject = 'item';
		this.namingObject = namingObject;
		var a = Array();
		for(o in genericObject){
			a.push(genericObject[o]);
		}
		
		this.items =a;
		return this;
	},

	select: function(expression){
		var a = Array();
		
		for(item in this.items){
			var re = eval('/' + this.namingObject +'/g');
			var expres = expression.replace(re,'this.items[item]');
			
			var result = null;
			expr = expres.split(/,/g);
			
			if(expr.length==1){
				result = eval(expr[0]);
			}else{
				try{
					var obj = new Object();
					for(var i =0; i<expr.length; i++){
						
						var field = expr[i];
						field = field.replace('this.items[item].','');
						var exp = 'obj.' + field + '=' +expr[i];
						eval(exp);
						
						
					}
					result = obj;
					//
				}catch(e){
					result = eval(expres);
				}
				a.push(result);
			}
		}
		//alert(a);
		this.items = a;
		return this;
	},
	where: function(condition){
		var a = Array();
		
		for(item in this.items){
			var re = eval('/' + this.namingObject +'/g');
			var expr = condition.replace(re,'this.items[item]');
			if(eval(expr)) a.push(this.items[item]);
		}
		
		this.items = a;
		return this;
	},
	get: function(){
		return this.items;
	}
};

