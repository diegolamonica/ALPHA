_.extend('Date',{
	
	options: {
		xformat: 			'%D, %d %xM (%m) %y (settimana n. %w: %n giorni dall\'inizio dell\'anno)',
		format:				'%d-%m-%y',
		dayPlaceholder: 	'%d',
		xtDayPlaceholder:	'%D',
		daysPlaceholder:	'%n',
		monthPlaceholder: 	'%m',
		xtMonthPlaceholder:	'%xM',
		weekPlaceholder:	'%w',
		yearPlaceholder: 	'%y',
		separator:			'-',
		sameDay:			true,
		digitsPerMonth:		2,
		digitsPerDay:		2,
		digitsPerYear:		4,
		year2DigitLimit:	50,
		weekStartFrom:		1,	/* 0: Sunday - 1: Monday - .... */
		weekPrecision:      true,
		daysExtended:		['domenica', 'lunedì', 'martedì', 'mercoledì', 'giovedì','venerdì','sabato'],
		daysAbbreviated:	['dom', 'lun', 'mar', 'mer', 'gio','ven','sab'],
		monthsExtended:		['gennaio','febbraio','marzo','aprile','maggio','giugno','luglio','agosto','settembre','ottobre','novembre','dicembre'],
   		monthsAbbreviated:	['gen','feb','mar','apr','mag','giu','lug','ago','set','ott','nov','dic'],
   		daysPerMonth:		[31,28,31,30,31,30,31,31,30,31,30,31]
	},
	_replaceInDate: function(xdate, values, placeholder){

		var possibleItem = -1
		for(var i=0; i<values.length; i++){
			if(xdate.toUpperCase().replace(values[i].toUpperCase(),'')!= xdate.toUpperCase()){
				if(possibleItem==-1 || values[possibleItem].length < values[i].length ){
					possibleItem = i;
				} 
			}
		}
		if(possibleItem!=-1) xdate = xdate.toUpperCase().replace(values[possibleItem].toUpperCase(), placeholder);
		return [xdate,possibleItem];
	},
	getDateElements: function(date){

		var dateFormat = this.autodetectFormat(date);
		if(dateFormat==null) return null;
		return dateFormat[1];
	},
	autodetectFormat: function(dateFormat){
		
		var o = _.Date.options;
		
		var dateFormatArray = _.strings.split(dateFormat, o.separator);
		var baseFormatArray = _.strings.split(o.format, o.separator);
		if(baseFormatArray.length==dateFormatArray.length){
			var result = [];
			for(var i = 0; i<baseFormatArray.length; i++){
				if(baseFormatArray[i]==o.dayPlaceholder) 	result[0] = dateFormatArray[i]; 
				if(baseFormatArray[i]==o.monthPlaceholder) 	result[1] = dateFormatArray[i];
				if(baseFormatArray[i]==o.yearPlaceholder) 	result[2] = dateFormatArray[i];
			}
			if(!isNaN(Number(result[0])) &&
					!isNaN(Number(result[1])) &&
					!isNaN(Number(result[2])))
			return [o.format, result];
		}
		var result = _.Date._replaceInDate(dateFormat, o.daysExtended, o.xtDayPlaceholder);
		dateFormat = result[0];
		var identifiedWeekDay = result[1];
		result = _.Date._replaceInDate(dateFormat, o.monthsExtended, o.xtMonthPlaceholder);
		dateFormat = result[0];
		var identifiedMonth = result[1]+1;
		if(identifiedMonth==0){
			var re = /([^\d]|^)((0?[1-9])|(1[0-2]))([^\d]+|$)/;
			var result = re.exec(dateFormat);
			if(result==null) return null;
			identifiedMonth = result[2];
			dateFormat = dateFormat.replace(/([^\d]|^)((0?[1-9])|(1[0-2]))([^\d]+|$)/,'$1'+o.monthPlaceholder+'$5');
		}
		var re =/([^\d]|^)((0?[1-9])|((1|2)[0-9])|(3[0-1]))([^\d]+|$)/;
		var identifiedDay = re.exec(dateFormat);
		if(identifiedDay!=null) identifiedDay = identifiedDay[2];
		dateFormat = dateFormat.replace(/([^\d]|^)((0?[1-9])|((1|2)[0-9])|(3[0-1]))([^\d]+|$)/,'$1'+o.dayPlaceholder+'$7'); 
		if(identifiedMonth!=0){
			var re = new RegExp('([^\\d|^])0?'+identifiedMonth+'([^\\d]+|$)','g');
			dateFormat = dateFormat.replace(re,'$1'+o.monthPlaceholder+'$2');
		}
		var identifiedYear = 0;
		var re = new RegExp('\\d{4}','g');
		var result = re.exec(dateFormat);
		if(result==null) var re = new RegExp('\\d{2}','g'); 
		if(result!=null) identifiedYear = result[0]; 
		dateFormat = dateFormat.replace(/([^\d|^])(\d{4})([^\d]+|$)/,'$1'+o.yearPlaceholder+'$3');
		dateFormat = dateFormat.replace(/([^\d|^])(\d{2})([^\d]+|$)/,'$1'+o.yearPlaceholder+'$3'); 
		
		if(identifiedWeekDay>=0){
			var combos = ['012','021','102','120','201','210'];
			var e = [ identifiedDay, identifiedMonth, identifiedYear];
			
			for (var key in combos){
				
				var c = combos[key];
				
				var idGiorno = Number(c[0]);
				var idMese = Number(c[1]);
				var idAnno = Number(c[2]);
				
				if(e[idGiorno]<0 || e[idGiorno]>31 || e[idMese]<0 || e[idMese]>12){
				}else{
					if (identifiedWeekDay==this.nDay(this.make(e[idAnno],e[idMese],e[idGiorno]))){
						
						var pGiorno = dateFormat.indexOf(o.dayPlaceholder);
						var pMese 	= dateFormat.indexOf(o.monthPlaceholder);
						var pAnno 	= dateFormat.indexOf(o.yearPlaceholder);
						var mainSort = [o.dayPlaceholder, o.monthPlaceholder, o.yearPlaceholder];
						var sortPlaceholders = [pGiorno, pMese, pAnno];
						var keys = [ mainSort[c[0]], mainSort[c[1]], mainSort[c[2]]];
						
						for(var i = 2; i>=0; i--){
							dateFormat = dateFormat.replace(keys[i], mainSort[i]);
						}
						
						return [dateFormat, [e[idAnno], e[idMese], e[idGiorno], identifiedWeekDay]];
					}
				}
			}
			
			return [null, null];
		
		}
		else 
		{
			identifiedWeekDay = this.nDay(this.make(identifiedYear, identifiedMonth, identifiedDay));
			return [dateFormat, [identifiedYear, identifiedMonth, identifiedDay, identifiedWeekDay]];
		}
	},
	isDate: function(date){
		var o = this.options;
		var dmy = this.getDateElements(date);
		if(dmy==null) return false;
		var day = dmy[0];
		var month = dmy[1];
		var year = dmy[2];
		var months = o.daysPerMonth;
		if (this.isLeap(year)) months[1]=29;
		return (months[month-1] && day>0 && day<=months[month-1]);
	},
	isLeap: function(year){
		return (year % 4 == 0 && (year % 100 != 0 || year % 400 == 0));
	},
	dateAdd: function(date, increment, type){
		// Incrementa la data corrente di un valore in base alla sua tipologia
		var o = this.options;
		var dataElements = this.getDateElements(date);
		var oldDay=dataElements[0];
		var oldMonth=dataElements[1];
		var item = [];
		
		switch(type)
		{
			case o.dayPlaceholder:
				item=[increment,0,0];
			break;
			case o.monthPlaceholder:
				item=[0,increment,0];
			break;
			case o.yearPlaceholder:
				item=[0,0,increment];
			break;
		}
		for(i=0; i<3; i++){
			
			dataElements[i] = Number(dataElements[i].toString().replace(/^0+/,'')) + Number(item[i]);
		}
		
		var day 	= dataElements[0];
		var month 	= dataElements[1];
		var year 	= dataElements[2];
		var months = o.daysPerMonth;
		
		if (this.isLeap(year)) months[1]=29;
		else months[1]=28;
		
		//Incremento o decremento la data a seconda del tipo
		if(increment>0){ 
			switch(type)
			{
				case o.dayPlaceholder:
					while(day>months[month-1]){
						day -= months[month-1];
						month++;
						if(month>12){
							month -= 12;
							year += 1;
						}
						if (this.isLeap(year)) months[1]=29;
						else months[1]=28;
					} 
				break;
				
				case o.monthPlaceholder:
					while(month>12)
					{
						month -= 12;
						year += 1;
					}
					
				break;
				
				case o.yearPlaceholder:
					year += increment;
				break;
			}
		}
		else{
			switch(type)
			{
				case o.dayPlaceholder:
					while(day<1){
						month--;
						if(month<1){
							month += 12;
							year -= 1;
						}
						if (this.isLeap(year)) months[1]=29;
						else months[1]=28;
						day += months[month-1];
						 
					} 
				break;
				
				case o.monthPlaceholder:
					while(month<12)
					{
						month += 12;
						year -= 1;
					}
				break;
				
				case o.yearPlaceholder:
					year -= increment;
				break;
			}
		}
		
		if (this.isLeap(year)) months[1]=29;
		else months[1]=28;

		if(day>months[month-1])
			day=months[month-1];
		
		return this.make(year, month, day);
	},
	dateSubtract: function(date, increment, type){
		// Sottrae la data corrente di un valore in base alla sua tipologia
		return this.dateAdd(date, -increment, type);
	},
	daysOfTheYear: function(year, to){
		if(to==null) to = this.options.daysPerMonth.length;
		var days = 0;
		for(var i= 0; i<to; i++){
			days+=this.options.daysPerMonth[i];
		}
		if(to>2 && this.isLeap(year)) days+=1;
		return days;
	},
	dateDiff: function(date1, date2){
		// Calcola la differenza in giorni tra due date
		var o = this.options;
		var dmy1 = this.getDateElements(date1);
		var dmy2 = this.getDateElements(date2);
		
		for(var i=0; i<3;i++){
			dmy1[i] = Number((dmy1[i]+'').replace(/^0+/,''));
			dmy2[i] = Number((dmy2[i]+'').replace(/^0+/,''));
		}
		
		if(dmy1[0]<100) dmy1[2]+= (dmy1[0]>o.year2DigitLimit?1900:2000);
		if(dmy2[0]<100) dmy2[2]+= (dmy2[0]>o.year2DigitLimit?1900:2000);
		
		if(	(dmy1[0]*1000)+(dmy1[1]*100)+dmy1[2]> 
			(dmy2[0]*1000)+(dmy2[1]*100)+dmy2[2]
		){
			var tmp=dmy1;
			dmy1=dmy2;
			dmy2=tmp;
		}
		
		var dDays = 0;
		for(var year=dmy1[0]; year<dmy2[0]; year++) dDays += this.daysOfTheYear(year);
		
		dDays += this.daysOfTheYear(dmy2[0], dmy2[1]-1);
		dDays += dmy2[2];
		dDays -= this.daysOfTheYear(dmy1[0], dmy1[1]-1);
		dDays -= dmy1[2];
		
		return dDays;
	},
	weeks: function(date){
		// Settimane dall'inizio dell'anno
		var o = this.options;
		var days = this.days(date);
		if (o.weekPrecision==true){
			start=o.weekStartFrom;
			first=this.nDay(this.make(this.year(date),1,1));
			days=days-(7-start-first);
			
		}
		var weeks = Number(days/7);
		return weeks;
	},
	days: function(date){
		// Giorni dall'inizio dell'anno
		var year = this.year(date);
		var month = this.month(date);
		var days = this.daysOfTheYear(year, month-1);
		return days+Number(this.day(date));
	},
	nDay: function(date){
		// Giorno della settimana (numero)
		// (calcolato con data di riferimento: Martedì 1 Gennaio 1980)
		var o = this.options;
		var daysDiff=this.dateDiff(this.make(1980,1,1),date);
		var dayOfTheWeek=2+Number(daysDiff%7)-1;
		if (dayOfTheWeek>6) dayOfTheWeek=dayOfTheWeek-7;
		return dayOfTheWeek;
	},
	sDay: function(day,option){
		// Giorno della settimana (stringa, in formato abbreviato se option == 'a') 
		var o = this.options;
		if (option=='a') return o.daysAbbreviated[day];
		else return o.daysExtended[day];
	},
	sMonth: function(month,option){
		// Mese dell'anno (stringa, in formato abbreviato se option == 'a') 
		var o = this.options;
		if (option=='a') return o.monthsAbbreviated[month-1];
		else return o.monthsExtended[month-1];
	},
	year: function(date){
		// Anno
		return this.getDateElements(date)[0];
	},
	month: function(date){
		// Mese
		return this.getDateElements(date)[1];
	},
	day: function(date){
		// Giorno
		return this.getDateElements(date)[2];
	},
	make: function(year, month, day){
		var o = this.options;
		day=''+day;
		month=''+month;
		while (day.length<o.digitsPerDay) day='0'+day;
		while (month.length<o.digitsPerMonth) month='0'+month;
	    if(year<100) year+= (year>o.year2DigitLimit?1900:2000);
	    year = year%Math.pow(10,o.digitsPerYear);
	    return o.format.replace(o.dayPlaceholder, day).replace(o.monthPlaceholder,month).replace(o.yearPlaceholder, year);
	}
});