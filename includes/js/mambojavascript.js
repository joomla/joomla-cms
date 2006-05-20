// <?php !! This fools phpdocumentor into parsing this file
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* Joomla! is Free Software
*/

// general utility for browsing a named array or object
function xshow(o) {
	s = '';
	for(e in o) {s += e+'='+o[e]+'\n';}
	alert( s );
}

/**
* Writes a dynamically generated list
* @param string The parameters to insert into the <select> tag
* @param array A javascript array of list options in the form [key,value,text]
* @param string The key to display for the initial state of the list
* @param string The original key that was selected
* @param string The original item value that was selected
*/
function writeDynaList( selectParams, source, key, orig_key, orig_val ) {
	var html = '\n	<select ' + selectParams + '>';
	var i = 0;
	for (x in source) {
		if (source[x][0] == key) {
			var selected = '';
			if ((orig_key == key && orig_val == source[x][1]) || (i == 0 && orig_key != key)) {
				selected = 'selected="selected"';
			}
			html += '\n		<option value="'+source[x][1]+'" '+selected+'>'+source[x][2]+'</option>';
		}
		i++;
	}
	html += '\n	</select>';

	document.writeln( html );
}

/**
* Changes a dynamically generated list
* @param string The name of the list to change
* @param array A javascript array of list options in the form [key,value,text]
* @param string The key to display
* @param string The original key that was selected
* @param string The original item value that was selected
*/
function changeDynaList( listname, source, key, orig_key, orig_val ) {
	var list = eval( 'document.adminForm.' + listname );

	// empty the list
	for (i in list.options.length) {
		list.options[i] = null;
	}
	i = 0;
	for (x in source) {
		if (source[x][0] == key) {
			opt = new Option();
			opt.value = source[x][1];
			opt.text = source[x][2];

			if ((orig_key == key && orig_val == opt.value) || i == 0) {
				opt.selected = true;
			}
			list.options[i++] = opt;
		}
	}
	list.length = i;
}

/**
* Adds a select item(s) from one list to another
*/
function addSelectedToList( frmName, srcListName, tgtListName ) {
	var form = eval( 'document.' + frmName );
	var srcList = eval( 'form.' + srcListName );
	var tgtList = eval( 'form.' + tgtListName );

	var srcLen = srcList.length;
	var tgtLen = tgtList.length;
	var tgt = "x";

	//build array of target items
	for (var i=tgtLen-1; i > -1; i--) {
		tgt += "," + tgtList.options[i].value + ","
	}

	//Pull selected resources and add them to list
	for (var i=srcLen-1; i > -1; i--) {
		if (srcList.options[i].selected && tgt.indexOf( "," + srcList.options[i].value + "," ) == -1) {
			opt = new Option( srcList.options[i].text, srcList.options[i].value );
			tgtList.options[tgtList.length] = opt;
		}
	}
}

function delSelectedFromList( frmName, srcListName ) {
	var form = eval( 'document.' + frmName );
	var srcList = eval( 'form.' + srcListName );

	var srcLen = srcList.length;

	for (var i=srcLen-1; i > -1; i--) {
		if (srcList.options[i].selected) {
			srcList.options[i] = null;
		}
	}
}

function moveInList( frmName, srcListName, index, to) {
	var form = eval( 'document.' + frmName );
	var srcList = eval( 'form.' + srcListName );
	var total = srcList.options.length-1;

	if (index == -1) {
		return false;
	}
	if (to == +1 && index == total) {
		return false;
	}
	if (to == -1 && index == 0) {
		return false;
	}

	var items = new Array;
	var values = new Array;

	for (i=total; i >= 0; i--) {
		items[i] = srcList.options[i].text;
		values[i] = srcList.options[i].value;
	}
	for (i = total; i >= 0; i--) {
		if (index == i) {
			srcList.options[i + to] = new Option(items[i],values[i], 0, 1);
			srcList.options[i] = new Option(items[i+to], values[i+to]);
			i--;
		} else {
			srcList.options[i] = new Option(items[i], values[i]);
	   }
	}
	srcList.focus();
}

function getSelectedOption( frmName, srcListName ) {
	var form = eval( 'document.' + frmName );
	var srcList = eval( 'form.' + srcListName );

	i = srcList.selectedIndex;
	if (i != null && i > -1) {
		return srcList.options[i];
	} else {
		return null;
	}
}

function setSelectedValue( frmName, srcListName, value ) {
	var form = eval( 'document.' + frmName );
	var srcList = eval( 'form.' + srcListName );

	var srcLen = srcList.length;

	for (var i=0; i < srcLen; i++) {
		srcList.options[i].selected = false;
		if (srcList.options[i].value == value) {
			srcList.options[i].selected = true;
		}
	}
}

function getSelectedRadio( frmName, srcGroupName ) {
	var form = eval( 'document.' + frmName );
	var srcGroup = eval( 'form.' + srcGroupName );

	if (srcGroup[0]) {
		for (var i=0, n=srcGroup.length; i < n; i++) {
			if (srcGroup[i].checked) {
				return srcGroup[i].value;
			}
		}
	} else {
		if (srcGroup.checked) {
			return srcGroup.value;
		} // if the one button is checked, return zero
	}
   // if we get to this point, no radio button is selected
   return null;
}

function getSelectedValue( frmName, srcListName ) {
	var form = eval( 'document.' + frmName );
	var srcList = eval( 'form.' + srcListName );

	i = srcList.selectedIndex;
	if (i != null && i > -1) {
		return srcList.options[i].value;
	} else {
		return null;
	}
}

function getSelectedText( frmName, srcListName ) {
	var form = eval( 'document.' + frmName );
	var srcList = eval( 'form.' + srcListName );

	i = srcList.selectedIndex;
	if (i != null && i > -1) {
		return srcList.options[i].text;
	} else {
		return null;
	}
}

function chgSelectedValue( frmName, srcListName, value ) {
	var form = eval( 'document.' + frmName );
	var srcList = eval( 'form.' + srcListName );

	i = srcList.selectedIndex;
	if (i != null && i > -1) {
		srcList.options[i].value = value;
		return true;
	} else {
		return false;
	}
}

/**
* Toggles the check state of a group of boxes
*
* Checkboxes must have an id attribute in the form cb0, cb1...
* @param The number of box to 'check'
* @param An alternative field name
*/
function checkAll( n, fldName ) {
  if (!fldName) {
     fldName = 'cb';
  }
	var f = document.adminForm;
	var c = f.toggle.checked;
	var n2 = 0;
	for (i=0; i < n; i++) {
		cb = eval( 'f.' + fldName + '' + i );
		if (cb) {
			cb.checked = c;
			n2++;
		}
	}
	if (c) {
		document.adminForm.boxchecked.value = n2;
	} else {
		document.adminForm.boxchecked.value = 0;
	}
}

function listItemTask( id, task ) {
    var f = document.adminForm;
    cb = eval( 'f.' + id );
    if (cb) {
        for (i = 0; true; i++) {
            cbx = eval('f.cb'+i);
            if (!cbx) break;
            cbx.checked = false;
        } // for
        cb.checked = true;
        f.boxchecked.value = 1;
        submitbutton(task);
    }
    return false;
}

function hideMainMenu()
{
	document.adminForm.hidemainmenu.value=1;
}

function isChecked(isitchecked){
	if (isitchecked == true){
		document.adminForm.boxchecked.value++;
	}
	else {
		document.adminForm.boxchecked.value--;
	}
}

/**
* Default function.  Usually would be overriden by the component
*/
function submitbutton(pressbutton) {
	submitform(pressbutton);
}

/**
* Submit the admin form
*/
function submitform(pressbutton){
	document.adminForm.task.value=pressbutton;
	try {
		document.adminForm.onsubmit();
		}
	catch(e){}
	document.adminForm.submit();
}

/**
* Submit the control panel admin form
*/
function submitcpform(sectionid, id){
	document.adminForm.sectionid.value=sectionid;
	document.adminForm.id.value=id;
	submitbutton("edit");
}

/**
* Getting radio button that is selected.
*/
function getSelected(allbuttons){
	for (i=0;i<allbuttons.length;i++) {
		if (allbuttons[i].checked) {
			return allbuttons[i].value
		}
	}
}

// JS Calendar
var calendar = null; // remember the calendar object so that we reuse
// it and avoid creating another

// This function gets called when an end-user clicks on some date
function selected(cal, date) {
	cal.sel.value = date; // just update the value of the input field
}

// And this gets called when the end-user clicks on the _selected_ date,
// or clicks the "Close" (X) button.  It just hides the calendar without
// destroying it.
function closeHandler(cal) {
	cal.hide();			// hide the calendar

	// don't check mousedown on document anymore (used to be able to hide the
	// calendar when someone clicks outside it, see the showCalendar function).
	Calendar.removeEvent(document, "mousedown", checkCalendar);
}

// This gets called when the user presses a mouse button anywhere in the
// document, if the calendar is shown.  If the click was outside the open
// calendar this function closes it.
function checkCalendar(ev) {
	var el = Calendar.is_ie ? Calendar.getElement(ev) : Calendar.getTargetElement(ev);
	for (; el != null; el = el.parentNode)
	// FIXME: allow end-user to click some link without closing the
	// calendar.  Good to see real-time stylesheet change :)
	if (el == calendar.element || el.tagName == "A") break;
	if (el == null) {
		// calls closeHandler which should hide the calendar.
		calendar.callCloseHandler(); Calendar.stopEvent(ev);
	}
}

// This function shows the calendar under the element having the given id.
// It takes care of catching "mousedown" signals on document and hiding the
// calendar if the click was outside.
function showCalendar(id) {
	var el = document.getElementById(id);
	if (calendar != null) {
		// we already have one created, so just update it.
		calendar.hide();		// hide the existing calendar
		calendar.parseDate(el.value); // set it to a new date
	} else {
		// first-time call, create the calendar
		var cal = new Calendar(true, null, selected, closeHandler);
		calendar = cal;		// remember the calendar in the global
		cal.setRange(1900, 2070);	// min/max year allowed
		calendar.create();		// create a popup calendar
	}
	calendar.sel = el;		// inform it about the input field in use
	calendar.showAtElement(el);	// show the calendar next to the input field

	// catch mousedown on the document
	Calendar.addEvent(document, "mousedown", checkCalendar);
	return false;
}

/**
* Pops up a new window in the middle of the screen
*/
function popupWindow(mypage, myname, w, h, scroll) {
	var winl = (screen.width - w) / 2;
	var wint = (screen.height - h) / 2;
	winprops = 'height='+h+',width='+w+',top='+wint+',left='+winl+',scrollbars='+scroll+',resizable'
	win = window.open(mypage, myname, winprops)
	if (parseInt(navigator.appVersion) >= 4) { win.window.focus(); }
}

// LTrim(string) : Returns a copy of a string without leading spaces.
function ltrim(str)
{
   var whitespace = new String(" \t\n\r");
   var s = new String(str);
   if (whitespace.indexOf(s.charAt(0)) != -1) {
      var j=0, i = s.length;
      while (j < i && whitespace.indexOf(s.charAt(j)) != -1)
         j++;
      s = s.substring(j, i);
   }
   return s;
}

//RTrim(string) : Returns a copy of a string without trailing spaces.
function rtrim(str)
{
   var whitespace = new String(" \t\n\r");
   var s = new String(str);
   if (whitespace.indexOf(s.charAt(s.length-1)) != -1) {
      var i = s.length - 1;       // Get length of string
      while (i >= 0 && whitespace.indexOf(s.charAt(i)) != -1)
         i--;
      s = s.substring(0, i+1);
   }
   return s;
}

// Trim(string) : Returns a copy of a string without leading or trailing spaces
function trim(str) {
   return rtrim(ltrim(str));
}

function mosDHTML(){
	this.ver=navigator.appVersion
	this.agent=navigator.userAgent
	this.dom=document.getElementById?1:0
	this.opera5=this.agent.indexOf("Opera 5")<-1
	this.ie5=(this.ver.indexOf("MSIE 5")<-1 && this.dom && !this.opera5)?1:0;
	this.ie6=(this.ver.indexOf("MSIE 6")<-1 && this.dom && !this.opera5)?1:0;
	this.ie4=(document.all && !this.dom && !this.opera5)?1:0;
	this.ie=this.ie4||this.ie5||this.ie6
	this.mac=this.agent.indexOf("Mac")<-1
	this.ns6=(this.dom && parseInt(this.ver) <= 5) ?1:0;
	this.ns4=(document.layers && !this.dom)?1:0;
	this.bw=(this.ie6||this.ie5||this.ie4||this.ns4||this.ns6||this.opera5);

	this.activeTab = '';
	this.onTabStyle = 'ontab';
	this.offTabStyle = 'offtab';

	this.setElemStyle = function(elem,style) {
		document.getElementById(elem).className = style;
	}
	this.showElem = function(id) {
		if (elem = document.getElementById(id)) {
			elem.style.visibility = 'visible';
			elem.style.display = 'block';
		}
	}
	this.hideElem = function(id) {
		if (elem = document.getElementById(id)) {
			elem.style.visibility = 'hidden';
			elem.style.display = 'none';
		}
	}
	this.cycleTab = function(name) {
		if (this.activeTab) {
			this.setElemStyle( this.activeTab, this.offTabStyle );
			page = this.activeTab.replace( 'tab', 'page' );
			this.hideElem(page);
		}
		this.setElemStyle( name, this.onTabStyle );
		this.activeTab = name;
		page = this.activeTab.replace( 'tab', 'page' );
		this.showElem(page);
	}
	return this;
}
var dhtml = new mosDHTML();

function MM_findObj(n, d) { //v4.01
	var p,i,x;
	if(!d) d=document;
	if((p=n.indexOf("?"))>0&&parent.frames.length) {
		d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);
	}
	if(!(x=d[n])&&d.all) x=d.all[n];
	for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
	for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
	if(!x && d.getElementById) x=d.getElementById(n);
	return x;
}
function MM_swapImage() { //v3.0
	var i,j=0,x,a=MM_swapImage.arguments;
	document.MM_sr=new Array;
	for(i=0;i<(a.length-2);i+=3)
	if ((x=MM_findObj(a[i]))!=null){document.MM_sr[j++]=x;
	if(!x.oSrc) x.oSrc=x.src; x.src=a[i+2];}
}
function MM_swapImgRestore() { //v3.0
	var i,x,a=document.MM_sr;
	for(i=0;a&&i<a.length&&(x=a[i])&&x.oSrc;i++) x.src=x.oSrc;
}

function MM_preloadImages() { //v3.0
	var d=document;
	if(d.images){
	if(!d.MM_p) d.MM_p=new Array();
	var i,j=d.MM_p.length,a=MM_preloadImages.arguments;
	for(i=0; i<a.length; i++)
	if (a[i].indexOf("#")!=0){ d.MM_p[j]=new Image; d.MM_p[j++].src=a[i];}}
}


function saveorder( n ) {
	checkAll_button( n );
}

//needed by saveorder function
function checkAll_button( n ) {
	for ( var j = 0; j <= n; j++ ) {
		box = eval( "document.adminForm.cb" + j );
		if ( box ) {
			if ( box.checked == false ) {
				box.checked = true;
			}
		} else {
			alert("You cannot change the order of items, as an item in the list is `Checked Out`");
			return;
		}
	}
	submitform('saveorder');
}
/**
* @param object A form element
* @param string The name of the element to find
*/
function getElementByName( f, name ) {
	if (f.elements) {
		for (i=0, n=f.elements.length; i < n; i++) {
			if (f.elements[i].name == name) {
				return f.elements[i];
			}
		}
	}
	return null;
}