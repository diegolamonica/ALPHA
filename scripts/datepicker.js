/**
 * Versione originale: http://www.nsftools.com/tips/NotesTips.htm#datepicker
 * 
 */
var datePickerDivID = "datepicker";
var iFrameDivID = "datepickeriframe";

var dayArrayShort = new Array('Do', 'Lu', 'Ma', 'Me', 'Gi', 'Ve', 'Sa');
var dayArrayMed = new Array('Dom', 'Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab');
var dayArrayLong = new Array('Domenica', 'Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato');
var monthArrayShort = new Array('Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic');
var monthArrayMed = new Array('Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic');
var monthArrayLong = new Array('Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre');

// these variables define the date formatting we're expecting and outputting.
// If you want to use a different format by default, change the defaultDateSeparator
// and defaultDateFormat variables either here or on your HTML page.
var defaultDateSeparator = "/"; // common values would be "/" or "."
var defaultDateFormat = "dmy"; // valid values are "mdy", "dmy", and "ymd"
var dateSeparator = defaultDateSeparator;
var dateFormat = defaultDateFormat;

function displayDatePicker(dateFieldName, displayBelowThisObject, dtFormat, dtSep){
    var targetDateField = _._id(dateFieldName);
    
    // if we weren't told what node to display the datepicker beneath, just display it
    // beneath the date field we're updating
    if (!displayBelowThisObject) 
        displayBelowThisObject = targetDateField;
    
    // if a date separator character was given, update the dateSeparator variable
    if (dtSep) 
        dateSeparator = dtSep;
    else 
        dateSeparator = defaultDateSeparator;
    
    // if a date format was given, update the dateFormat variable
    if (dtFormat) 
        dateFormat = dtFormat;
    else 
        dateFormat = defaultDateFormat;
    
    var x = displayBelowThisObject.offsetLeft;
    var y = displayBelowThisObject.offsetTop + displayBelowThisObject.offsetHeight;
    
    // deal with elements inside tables and such
    var parent = displayBelowThisObject;
    while (parent.offsetParent) {
        parent = parent.offsetParent;
        x += parent.offsetLeft;
        y += parent.offsetTop;
    }
    
    drawDatePicker(targetDateField, x, y);
}

function drawDatePicker(targetDateField, x, y){
    var dt = getFieldDate(targetDateField.value);
    
    // the datepicker table will be drawn inside of a <div> with an ID defined by the
    // global datePickerDivID variable. If such a div doesn't yet exist on the HTML
    // document we're working with, add one.
    if (!_._id(datePickerDivID)) {
        // don't use innerHTML to update the body, because it can cause global variables
        // that are currently pointing to objects on the page to have bad references
        //document.body.innerHTML += "<div id='" + datePickerDivID + "' class='dpDiv'></div>";
        var newNode = document.createElement("div");
        newNode.setAttribute("id", datePickerDivID);
        newNode.setAttribute("class", "dpDiv");
        newNode.setAttribute("style", "visibility: hidden;");
        document.body.appendChild(newNode);
    }
    
    // move the datepicker div to the proper x,y coordinate and toggle the visiblity
    var pickerDiv = _._id(datePickerDivID);
    
    _.DOM.setStyle(datePickerDivID, {
        position: "absolute",
        left: x + 'px',
        top: y + "px",
        visibility: (pickerDiv.style.visibility == "visible" ? "hidden" : "visible"),
        display: (pickerDiv.style.display == "block" ? "none" : "block"),
        zIndex: 10000
    })
    
    // draw the datepicker table
    refreshDatePicker(targetDateField.id, dt.getFullYear(), dt.getMonth(), dt.getDate());
}

/**
 This is the function that actually draws the datepicker calendar.
 */
function refreshDatePicker(dateFieldName, year, month, day){

    // if no arguments are passed, use today's date; otherwise, month and year
    // are required (if a day is passed, it will be highlighted later)
    var thisDay = new Date();
    
    if ((month >= 0) && (year > 0)) {
        thisDay = new Date(year, month, 1);
    }
    else {
        day = thisDay.getDate();
        thisDay.setDate(1);
    }
    
    // the calendar will be drawn as a table
    // you can customize the table elements with a global CSS style sheet,
    // or by hardcoding style and formatting elements below
    var crlf = "\r\n";
    var TABLE = "<table cols=7 class='dpTable'>" + crlf;
    var xTABLE = "</table>" + crlf;
    var TR = "<tr class='dpTR'>";
    var TR_title = "<tr class='dpTitleTR'>";
    var TR_days = "<tr class='dpDayTR'>";
    var TR_todaybutton = "<tr class='dpTodayButtonTR'>";
    var xTR = "</tr>" + crlf;
    var TD = "<td class='dpTD' onMouseOut='this.className=\"dpTD\";' onMouseOver=' this.className=\"dpTDHover\";' "; // leave this tag open, because we'll be adding an onClick event
    var TD_title = "<td colspan=5 class='dpTitleTD'>";
    var TD_buttons = "<td class='dpButtonTD'>";
    var TD_todaybutton = "<td colspan=7 class='dpTodayButtonTD'>";
    var TD_days = "<td class='dpDayTD'>";
    var TD_selected = "<td class='dpDayHighlightTD' onMouseOut='this.className=\"dpDayHighlightTD\";' onMouseOver='this.className=\"dpTDHover\";' "; // leave this tag open, because we'll be adding an onClick event
    var xTD = "</td>" + crlf;
    var DIV_title = "<div class='dpTitleText'>";
    var DIV_selected = "<div class='dpDayHighlight'>";
    var xDIV = "</div>";
    
    // start generating the code for the calendar table
    var html = TABLE;
    
    // this is the title bar, which displays the month and the buttons to
    // go back to a previous month or forward to the next month
    html += TR_title;
    html += TD_buttons + getButtonCode(dateFieldName, thisDay, -1, "&lt;") + xTD;
    html += TD_title + DIV_title + monthArrayLong[thisDay.getMonth()] + " " + thisDay.getFullYear() + xDIV + xTD;
    html += TD_buttons + getButtonCode(dateFieldName, thisDay, 1, "&gt;") + xTD;
    html += xTR;
    
    // this is the row that indicates which day of the week we're on
    html += TR_days;
    for (i = 0; i < dayArrayShort.length; i++) 
        html += TD_days + dayArrayShort[i] + xTD;
    html += xTR;
    
    // now we'll start populating the table with days of the month
    html += TR;
    
    // first, the leading blanks
    for (i = 0; i < thisDay.getDay(); i++) 
        html += TD + "&nbsp;" + xTD;
    
    // now, the days of the month
    do {
        dayNum = thisDay.getDate();
        TD_onclick = " onclick=\"updateDateField('" + dateFieldName + "', '" + getDateString(thisDay) + "');\">";
        
        if (dayNum == day) 
            html += TD_selected + TD_onclick + DIV_selected + dayNum + xDIV + xTD;
        else 
            html += TD + TD_onclick + dayNum + xTD;
        
        // if this is a Saturday, start a new row
        if (thisDay.getDay() == 6) 
            html += xTR + TR;
        
        // increment the day
        thisDay.setDate(thisDay.getDate() + 1);
    }
    while (thisDay.getDate() > 1)
    
    // fill in any trailing blanks
    if (thisDay.getDay() > 0) {
        for (i = 6; i > thisDay.getDay(); i--) 
            html += TD + "&nbsp;" + xTD;
    }
    html += xTR;
    
    // add a button to allow the user to easily return to today, or close the calendar
    var today = new Date();
    var todayString = "Today is " + dayArrayMed[today.getDay()] + ", " + monthArrayMed[today.getMonth()] + " " + today.getDate();
    html += TR_todaybutton + TD_todaybutton;
    html += "<button class='dpTodayButton' onClick='refreshDatePicker(\"" + dateFieldName + "\");'>this month</button> ";
    html += "<button class='dpTodayButton' onClick='updateDateField(\"" + dateFieldName + "\");'>close</button>";
    html += xTD + xTR;
    
    // and finally, close the table
    html += xTABLE;
    
    _._id(datePickerDivID).innerHTML = html;
    // add an "iFrame shim" to allow the datepicker to display above selection lists
    adjustiFrame(_._id(datePickerDivID));
}


/**
 Convenience function for writing the code for the buttons that bring us back or forward
 a month.
 */
function getButtonCode(dateFieldName, dateVal, adjust, label){
    var newMonth = (dateVal.getMonth() + adjust) % 12;
    var newYear = dateVal.getFullYear() + parseInt((dateVal.getMonth() + adjust) / 12);
    if (newMonth < 0) {
        newMonth += 12;
        newYear += -1;
    }
    
    return "<button class='dpButton' onClick='refreshDatePicker(\"" + dateFieldName + "\", " + newYear + ", " + newMonth + ");'>" + label + "</button>";
}


/**
 Convert a JavaScript Date object to a string, based on the dateFormat and dateSeparator
 variables at the beginning of this script library.
 */
function getDateString(dateVal){
    var dayString = "00" + dateVal.getDate();
    var monthString = "00" + (dateVal.getMonth() + 1);
    dayString = dayString.substring(dayString.length - 2);
    monthString = monthString.substring(monthString.length - 2);
    
    switch (dateFormat) {
        case "dmy":
            return dayString + dateSeparator + monthString + dateSeparator + dateVal.getFullYear();
        case "ymd":
            return dateVal.getFullYear() + dateSeparator + monthString + dateSeparator + dayString;
        case "mdy":
        default:
            return monthString + dateSeparator + dayString + dateSeparator + dateVal.getFullYear();
    }
}


/**
 Convert a string to a JavaScript Date object.
 */
function getFieldDate(dateString){
    var dateVal;
    var dArray;
    var d, m, y;
    
    try {
        dArray = splitDateString(dateString);
        if (dArray) {
            switch (dateFormat) {
                case "dmy":
                    d = parseInt(dArray[0], 10);
                    m = parseInt(dArray[1], 10) - 1;
                    y = parseInt(dArray[2], 10);
                    break;
                case "ymd":
                    d = parseInt(dArray[2], 10);
                    m = parseInt(dArray[1], 10) - 1;
                    y = parseInt(dArray[0], 10);
                    break;
                case "mdy":
                default:
                    d = parseInt(dArray[1], 10);
                    m = parseInt(dArray[0], 10) - 1;
                    y = parseInt(dArray[2], 10);
                    break;
            }
            dateVal = new Date(y, m, d);
        }
        else 
            if (dateString) {
                dateVal = new Date(dateString);
            }
            else {
                dateVal = new Date();
            }
    } 
    catch (e) {
        dateVal = new Date();
    }
    
    return dateVal;
}


/**
 Try to split a date string into an array of elements, using common date separators.
 If the date is split, an array is returned; otherwise, we just return false.
 */
function splitDateString(dateString){
    var dArray;
    if (dateString.indexOf("/") >= 0) 
        dArray = dateString.split("/");
    else 
        if (dateString.indexOf(".") >= 0) 
            dArray = dateString.split(".");
        else 
            if (dateString.indexOf("-") >= 0) 
                dArray = dateString.split("-");
            else 
                if (dateString.indexOf("\\") >= 0) 
                    dArray = dateString.split("\\");
                else 
                    dArray = false;
    
    return dArray;
}

function updateDateField(dateFieldName, dateString){

    var targetDateField = _._id(dateFieldName);
    if (dateString) 
        targetDateField.value = dateString;
    
    var pickerDiv = _._id(datePickerDivID);
    
    _.DOM.setStyle(datePickerDivID, {
        visibility: "hidden",
        display: "none"
    });
    
    adjustiFrame(pickerDiv);
    targetDateField.focus();
    
    // after the datepicker has closed, optionally run a user-defined function called
    // datePickerClosed, passing the field that was just updated as a parameter
    // (note that this will only run if the user actually selected a date from the datepicker)
    if ((dateString) && (typeof(datePickerClosed) == "function")) 
        datePickerClosed(targetDateField);
}

function adjustiFrame(pickerDiv, iFrameDiv){
    // we know that Opera doesn't like something about this, so if we
    // think we're using Opera, don't even try
    
    if (_.Browser.opera)  return;
    
    // put a try/catch block around the whole thing, just in case
    if (!_._id(iFrameDivID)) {
        // don't use innerHTML to update the body, because it can cause global variables
        // that are currently pointing to objects on the page to have bad references
        //document.body.innerHTML += "<iframe id='" + iFrameDivID + "' src='javascript:false;' scrolling='no' frameborder='0'>";
        var newNode = document.createElement("iFrame");
        newNode.setAttribute("id", iFrameDivID);
        newNode.setAttribute("src", "about:blank");
        newNode.setAttribute("scrolling", "no");
        newNode.setAttribute("frameborder", "0");
        document.body.appendChild(newNode);
    }
	if(pickerDiv!=null){
   		
		var jsonStyle = {
		    position: "absolute",
		    width: pickerDiv.offsetWidth,
		    height: pickerDiv.offsetHeight,
		    top: pickerDiv.style.top,
		    left: pickerDiv.style.left,
		    zIndex: pickerDiv.style.zIndex - 1,
		    visibility: pickerDiv.style.visibility,
		    display: pickerDiv.style.display
		}
        _.DOM.setStyle(datePickerDivID, jsonStyle);
		jsonStyle.zIndex = jsonStyle.zIndex- 1;
        _.DOM.setStyle(iFrameDivID, jsonStyle);
	}
           
}
