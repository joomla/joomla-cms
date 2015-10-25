function cbsaveorder( cb, n, fldName, task, subtaskName, subtaskValue ) {
    cbCheckAllRowsAndSubTask( cb, n, fldName, subtaskName, subtaskValue );
    cbsubmitform( task );
}
//needed by cbsaveorder function
function cbCheckAllRowsAndSubTask( cb, n, fldName, subtaskName, subtaskValue ) {
    if (!fldName) {
        fldName = 'cb';
    }
    f = cbParentForm( cb );
    for ( var i = 0; i < n; i++ ) {
             box = f.elements[fldName+i];
             if ( box.checked == false ) {
                     box.checked = true;
             }
    }
	if (subtaskName && subtaskValue) {
		f.elements[subtaskName].value = subtaskValue;
	}
}
/**
* Toggles the check state of a group of boxes
*
* Checkboxes must have an id attribute in the form cb0, cb1...
* @param  tgl      The id of the toggle button
* @param  n        The number of box to 'check'
* @param  fldName  An alternative field name id prefix
*/
function cbToggleAll( tgl, n, fldName ) {
	if ( ! fldName ) {
		fldName = 'cb';
	}

	var frm = tgl.form;
	var checked = 0;

	for ( i = 0; i < n; i++ ) {
		cb = eval( 'frm.' + fldName + i );

		if ( cb ) {
			cb.checked = tgl.checked;

			if ( tgl.checked == true ) {
				checked++;
			}
		}
	}

	if ( typeof( frm.boxchecked ) != 'undefined' ) {
		frm.boxchecked.value = checked;
	}

	return true;
}

function cbParentForm(cb) {
	var f;
	if ( cb == window  ) {
		f = window.event.srcElement;	// IE
	} else {
		f = cb;
	}
	while (f) {
		f = f.parentNode;
		if (f.nodeName == 'FORM') {
			break;
		}
	}
	return f;
}
/**
* Performs task/subtask on table row id
*/
function cbIsChecked(isitchecked) {
	if (isitchecked == true) {
		document.adminForm.boxchecked.value++;
	} else {
		document.adminForm.boxchecked.value--;
	}
}
/**
* Performs task/subtask on table row id
*/
function cbListItemTask( cb, task, subtaskName, subtaskValue, fldName, id ) {
    var f = cbParentForm(cb);
    if (cb && f) {
    	var cbRegexp = new RegExp('^'+fldName+'[0-9]+$');

    	for (i = 0; i < f.elements.length; i++) {
            cbx = f.elements[i];
            if ( ( cbx.type != 'checkbox' ) || ( ! cbRegexp.test(cbx.id) ) ) {
            	continue;
            }
            if ( cbx.id == fldName+id ) {
	            cbx.checked = true;
            } else {
	            cbx.checked = false;
        	}
        }
		if (subtaskName && subtaskValue) {
			f.elements[subtaskName].value = subtaskValue;
		}
        submitbutton(task);
    }
    return false;
}
/**
* Performs task/subtask on selected table rows
*/
function cbDoListTask( cb, task, subtaskName, subtaskValue, fldName ) {
    var f = document.forms['adminForm'];
    if (cb) {
    	var oneChecked = false;
        for (i = 0; true; i++) {
            cbx = f.elements[fldName+i];
            if ( ! cbx ) {
            	break;
            }
            if ( cbx.checked ) {
	            oneChecked = true;
	            break;
        	}
        }
        if ( oneChecked ) {
			if (subtaskName && subtaskValue) {
				if ( subtaskValue == 'deleterows' ) {
					if ( ! confirm('Are you sure you want to delete selected items ?') ) { 
						return false;
					}
				}
				f.elements[subtaskName].value = subtaskValue;
			}
    	    submitbutton(task);
        } else {
        	alert( "no items selected" );
        }
    }
    return false;
}
/**
* Performs task/subtask
*/
function cbDoSubTask( cb, task, subtaskName, subtaskValue ) {
	var f = document.forms['adminForm'];

	if ( cb ) {
		if (subtaskName && subtaskValue) {
			f.elements[subtaskName].value = subtaskValue;
		}

		submitbutton(task);
	}

	return false;
}

function cbhideMainMenu() {
	if ( document.adminForm.hidemainmenu ) {
		document.adminForm.hidemainmenu.value	=	1;
	}
}

function submitbutton(pressbutton) {
	cbsubmitform(pressbutton);
	return false;
}

/**
* Submit the admin form
*/
function cbsubmitform(pressbutton){
	if (pressbutton.indexOf('=') == -1) {
		if ( document.forms['adminForm'].elements['task'] ) {
			document.forms['adminForm'].elements['task'].value = pressbutton;
		} else if ( document.forms['adminForm'].elements['view'] ) {
			document.forms['adminForm'].elements['view'].value = pressbutton;
		}
	} else {
		var formchanges = pressbutton.split('&');
		for (var i = 0; i < formchanges.length; i++) {
			var nv = formchanges[i].split('=');
			if ( ( typeof( document.forms['adminForm'].elements[nv[0]] ) == 'undefined' ) && ( typeof( cbjQuery ) != 'undefined' ) ) {
				cbjQuery('<input type="hidden" />').attr('name', nv[0]).attr('value', nv[1]).appendTo(cbjQuery(document.forms['adminForm']));
			}
			document.forms['adminForm'].elements[nv[0]].value = nv[1];
		}
	}
	if ( typeof( cbjQuery ) != 'undefined' ) {
		cbjQuery( document.forms['adminForm'] ).submit();
	} else {
		if ( typeof(document.forms['adminForm']) != 'undefined' ) {
			try {
				document.forms['adminForm'].onsubmit();
				}
			catch(e){}
		}
		document.forms['adminForm'].submit();
	}
}

/**
* general cb DOM events handler
*/

var cbW3CDOM = (document.createElement && document.getElementsByTagName);

function cbGetElementsByClass(searchClass,node,tag) {
	var classElements = new Array();
	if ( node == null ) {
		node = document;
	}
	if ( tag == null ) {
		tag = '*';
	}
	var els = node.getElementsByTagName(tag);
	var elsLen = els.length;
	var pattern = new RegExp('(^|\\s)'+searchClass+'(\\s|$)');
	for (i = 0, j = 0; i < elsLen; i++) {
		if ( pattern.test(els[i].className) ) {
			classElements[j] = els[i];
			j++;
		}
	}
	return classElements;
}

function cbAddEvent(obj, evType, fn){
	if (obj.addEventListener){
		obj.addEventListener(evType, fn, true);
		return true;
	} else if (obj.attachEvent){
		var r = obj.attachEvent("on"+evType, fn);
		return r;
	} else {
		return false;
	}
}

function cbAddEventObjArray(objArr, evType, fn){
	for (var j=0;j<objArr.length;j++) {
		if (objArr[j].type != 'hidden') {
			eval('objArr[j].on' + evType + '=fn');
			/* cbAddEvent( objArr[j], evType, fn ); */
		}
	}
}

/**
* CB hide and set fields depending on other fields:
*/

var cbHideFields = new Array();
var cbParamsSaveBefHide = new Array();
var cbSels = new Array();
var cbPreviousOnChangeValues = new Array();

function cbGetDisplayStyle( dt ) {
	var ds;
	if (dt.style.getPropertyValue) {
		ds = dt.style.getPropertyValue("display");
	} else {
		ds = dt.style.display;
	}
	return ds;
}

var cbFirstTimeChange = true;
/**
* CB change params hidding/showing actions
*/
function cbParamChange() {
	var fieldsToShow = new Array();
	var fieldsToHide = new Array();
	var value, nameValue, inputToSet, field;
	var changedDoPost	=	false;
	var i,j,k,alreadyHidden;
	for (i=0;i<cbHideFields.length;i++) {
		value = '';
		for (j=1;j<cbSels[i].length;j++) {
			if (cbSels[i][j].type != 'hidden') {
				/*
				var name = cbSels[i][j].name;
				if ( name.substr(-2, 2)  == '[]' ) {
					name = name.substr(0, name.length-2);
				}
				*/
				if ((cbSels[i][j].type == 'radio') || (cbSels[i][j].type == 'checkbox') ) {
					if ( cbSels[i][j].checked ) {
						value += ( value === '' ? '' : '|*|' ) + cbSels[i][j].value;
					}
				} else if ( cbSels[i][j].tagName == 'SELECT' ) {
					var l = cbSels[i][j].options.length;
					for ( var o = 0; o < l; o++ ) {
						if ( cbSels[i][j].options[o].selected ) {
							value += ( value === '' ? '' : '|*|' ) + cbSels[i][j].options[o].value;
						}
					}
				} else {
					value += ( value === '' ? '' : '|*|' ) + cbSels[i][j].value;
				}
			}
		}
		// already the case: if (cbHideFields[1] == cbSels[i][0].id)
		var cMatch = false;
		switch (cbHideFields[i][2]) {
			case '==': if ( value == cbHideFields[i][3] ) { cMatch = true; } break;
			case '!=': if ( value != cbHideFields[i][3] ) { cMatch = true; } break;
			case '>=': if ( value >= cbHideFields[i][3] ) { cMatch = true; } break;
			case '<=': if ( value <= cbHideFields[i][3] ) { cMatch = true; } break;
			case '>' : if ( value >  cbHideFields[i][3] ) { cMatch = true; } break;
			case '<' : if ( value <  cbHideFields[i][3] ) { cMatch = true; } break;
			case 'contains' :
				cMatch = ( value.indexOf( cbHideFields[i][3] ) == -1 );
				break;
			case '!contains' :
				cMatch = ( value.indexOf( cbHideFields[i][3] ) != -1 );
				break;
			case 'in' :
				var values = cbHideFields[i][3].toString().split( '|*|' );
				cMatch = ( values.length > 0 ? ( values.indexOf( value ) == -1 ) : true );
				break;
			case '!in' :
				var values = cbHideFields[i][3].toString().split( '|*|' );
				cMatch = ( values.length > 0 ? ( values.indexOf( value ) != -1 ) : false );
				break;
			case 'regexp' :
				var cbRegexp = new RegExp(cbHideFields[i][3]);
				cMatch = ( ! cbRegexp.test(value) );
				break;
			case '!regexp' :
				var cbRegexp = new RegExp(cbHideFields[i][3]);
				cMatch = cbRegexp.test(value);
				break;
			case 'evaluate' :
				break;
			default:
				alert('js error operator "'+cbHideFields[i][2]+'" unknown.');
				break;
		}
		if (cbHideFields[i][2] == 'evaluate' ) {
			if (typeof(cbPreviousOnChangeValues[cbHideFields[i][1]]) != 'undefined') {
				if (cbPreviousOnChangeValues[cbHideFields[i][1]] != value) {
					changedDoPost	=	true;
				}
			}
			cbPreviousOnChangeValues[cbHideFields[i][1]]	=	value;
		} else {
			if ( cMatch ) {
				// Match: Hide fields, removing them from the shown fields:

				for (j=0;j<fieldsToShow.length;j++) {
					for (k=0;k<cbHideFields[i][4].length;k++) {
						if (cbHideFields[i][4][k] == fieldsToShow[j]) {
							fieldsToShow.splice(j, 1);
						}
					}
				}

				fieldsToHide = fieldsToHide.concat( cbHideFields[i][4] );
				if ( cbHideFields[i][5].length > 0 ) {

					// Fields to set: set them now so they can evaluate properly above:
					if ( cbGetDisplayStyle( document.getElementById( cbHideFields[i][0] ) ) != 'none' ) {
						for (j=0;j<cbHideFields[i][5].length;j++) {
							nameValue  = cbHideFields[i][5][j].split('=',3);
							if ( cbGetDisplayStyle( document.getElementById( cbHideFields[i][4][0] /* nameValue[0] */ ) ) != 'none' ) {
								inputToSet = document.getElementById( nameValue[1] );
								if (typeof(cbParamsSaveBefHide[i])=='undefined') {
									cbParamsSaveBefHide[i] = new Array();
								}
								if ( inputToSet ) {
									cbParamsSaveBefHide[i][j] = inputToSet.value;
									inputToSet.value = nameValue[2];
								} else {
									alert('InputToSet undefined: '+cbHideFields[i][4][0]);
								}
							}
						}
					}

				}
			} else {
				// No match: Show fields if not already hidden:
				alreadyHidden = false;
				for (j=0;j<fieldsToHide.length;j++) {
					for (k=0;k<cbHideFields[i][4].length;k++) {
						if (cbHideFields[i][4][k] == fieldsToHide[j]) {
							alreadyHidden = true;
						}
					}
				}
				if ( ! alreadyHidden ) {
					fieldsToShow = fieldsToShow.concat( cbHideFields[i][4] );
					if ( cbHideFields[i][5].length > 0 ) {
	
						// Fields to restore: restore them now, so they can evaluate properly above:
						// TBD:Opera doesn't restore correctly with radio choice
	//					if ( cbGetDisplayStyle( document.getElementById( cbHideFields[i][0] ) ) == 'none' ) {
							for (j=0;j<cbHideFields[i][5].length;j++) {
								nameValue  = cbHideFields[i][5][j].split('=',3);
								if ( cbGetDisplayStyle( document.getElementById( cbHideFields[i][4][0] /* nameValue[0] */ ) ) == 'none' ) {
									inputToSet = document.getElementById( nameValue[1] );
									if (typeof(cbParamsSaveBefHide[i])!='undefined') {
										inputToSet.value = cbParamsSaveBefHide[i][j];
									} else {
										alert('inputToSet saved param undefined for input id: '+nameValue[1]+' as following id is hidden: '+cbHideFields[i][4][0]);
									}
								}
							}
	//					}
	
					}
				}
			}
		}
	}

	if ( typeof( cbjQuery ) == 'undefined' ) {
		for ( i = 0; i < fieldsToShow.length; i++ ) {
			field = document.getElementById( fieldsToShow[i] );

			if ( field ) {
				if ( ( field.type == 'radio' ) || ( field.type == 'checkbox' ) ) {
					field.parent.style.display = '';
				} else {
					field.style.display = '';

					if ( field.tagName.toLowerCase() == 'option' ) {
						field.disabled = false;
					}
				}
			} else {
				alert('field not found number: '+i+' id:'+fieldsToShow[i]);
			}
		}

		for ( i = 0; i < fieldsToHide.length; i++ ) {
			field = document.getElementById( fieldsToHide[i] );

			if ( field ) {
				if ( ( field.type == 'radio' ) || ( field.type == 'checkbox' ) ) {
					field.parent.style.display = 'none';
					field.checked = false;
				} else {
					field.style.display = 'none';

					if ( field.tagName.toLowerCase() == 'option' ) {
						field.selected = false;
						field.disabled = true;
					}
				}
			} else {
				alert('field not found number: '+i+' id:'+fieldsToHide[i]);
			}
		}
	} else if ( cbFirstTimeChange ) {
		for ( i = 0; i < fieldsToShow.length; i++ ) {
			field = document.getElementById( fieldsToShow[i] );

			if ( field ) {
				if ( ( field.type == 'radio' ) || ( field.type == 'checkbox' ) ) {
					jQuery( field.parentNode ).show();
				} else {
					jQuery( field ).show();

					if ( field.tagName.toLowerCase() == 'option' ) {
						jQuery( field ).prop( 'disabled', false );
					}
				}

				jQuery( field ).removeClass( 'cbDisplayDisabled' );
				jQuery( field ).find( 'input,select,textarea' ).removeClass( 'cbValidationDisabled' );
			}
		}

		for (i=0;i<fieldsToHide.length;i++) {
			field = document.getElementById( fieldsToHide[i] );

			if ( field ) {
				if ( ( field.type == 'radio' ) || ( field.type == 'checkbox' ) ) {
					jQuery( field.parentNode ).hide();
					jQuery( field ).prop( 'checked', false );
				} else {
					jQuery( field ).hide();

					if ( field.tagName.toLowerCase() == 'option' ) {
						jQuery( field ).prop( 'selected', false );
						jQuery( field ).prop( 'disabled', true );
					}
				}

				jQuery( field ).addClass( 'cbDisplayDisabled' );
				jQuery( field ).find( 'input,select,textarea' ).addClass( 'cbValidationDisabled' );
			}
		}
	} else {
		for ( i = 0; i < fieldsToShow.length; i++ ) {
			field = document.getElementById( fieldsToShow[i] );

			if ( field ) {
				if ( ( field.type == 'radio' ) || ( field.type == 'checkbox' ) ) {
					jQuery( field.parentNode ).fadeIn( "slow", function() {
						jQuery( this ).show();
					});
				} else {
					jQuery( field ).fadeIn( "slow", function() {
						jQuery( this ).show();
					});

					if ( field.tagName.toLowerCase() == 'option' ) {
						jQuery( field ).prop( 'disabled', false );
					}
				}

				jQuery( field ).removeClass( 'cbDisplayDisabled' );
				jQuery( field ).find( 'input,select,textarea' ).removeClass( 'cbValidationDisabled' );
			}
		}

		for ( i = 0; i < fieldsToHide.length; i++ ) {
			field = document.getElementById( fieldsToHide[i] );

			if ( field ) {
				if ( ( field.type == 'radio' ) || ( field.type == 'checkbox' ) ) {
					jQuery( field.parentNode ).fadeOut( "slow", function() {
						jQuery( this ).hide();
					});

					jQuery( field ).prop( 'checked', false );
				} else {
					jQuery( field ).fadeOut( "slow", function() {
						jQuery( this ).hide();
					});

					if ( field.tagName.toLowerCase() == 'option' ) {
						jQuery( field ).prop( 'selected', false );
						jQuery( field ).prop( 'disabled', true );
					}
				}

				jQuery( field ).addClass( 'cbDisplayDisabled' );
				jQuery( field ).find( 'input,select,textarea' ).addClass( 'cbValidationDisabled' );
			}
		}
	}

	cbFirstTimeChange = false;

	if ( changedDoPost ) {
		cbParentForm(this).submit();
		return false;
	}
}

function cbInitFields()
{
	if (!cbW3CDOM) {
		return;
	}

	if (typeof(overlib_pagedefaults)!='undefined') {
		overlib_pagedefaults(WIDTH,250,VAUTO,RIGHT,AUTOSTATUSCAP, CSSCLASS,TEXTFONTCLASS,'cb-tips-font',FGCLASS,'cb-tips-fg',BGCLASS,'cb-tips-bg',CAPTIONFONTCLASS,'cb-tips-capfont', CLOSEFONTCLASS, 'cb-tips-closefont');
	}
	if (typeof(cbHideFields)=='undefined') {
		return;
	}

	for (var i=0;i<cbHideFields.length;i++) {
		var inputDom = document.getElementById(cbHideFields[i][0]);
		if ( inputDom === null ) {
			alert('xml name ' + cbHideFields[i][0] + ' is undefined. It is cbHideFields[' + i + '][0].');
		} else {
			var input = document.getElementById(cbHideFields[i][1]);
			var sels = [];
			if ( input !== null ) { // use the exact element based off id sent if possible
				sels[0] = input;
			}
			if ( sels.length == 0 ) { // fallback to finding all the inputs in the input parent (for B/C!)
				sels = inputDom.getElementsByTagName('input');
			}
			if ( sels.length == 0 ) { // fallback to finding all the selects in the input parent (for B/C!)
				sels = inputDom.getElementsByTagName('select');
			}
			var k = 1;
			cbSels[i] = new Array();
			cbSels[i][0] = inputDom;
			for (var j=0;j<sels.length;j++) {
				if (sels[j].type != 'hidden') {
					if (sels[j].type == 'text') {
						cbAddEvent( sels[j], 'change', cbParamChange );
					} else {
						cbAddEvent( sels[j], ( window.ActiveXObject ? 'click' : 'change' ), cbParamChange );		// IE doesn't trigger change until the drop-down is unselected...
					}
					cbSels[i][k++] = sels[j];
				}
			}
		}
	}
	cbParamChange();
}

cbAddEvent(window, 'load', cbInitFields);


/**
* CB basic ajax library (experimental): OBSOLETED IN CB 1.2: USE JQUERY !
*/


function CBgetHttpRequestInstance() {
	var http_request = false;

	if (window.XMLHttpRequest) { // Mozilla, Safari,...
		http_request = new XMLHttpRequest();
		if (http_request.overrideMimeType) {
			http_request.overrideMimeType('text/xml');
		}
	} else if (window.ActiveXObject) { // IE
		try {
			http_request = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			try {
				http_request = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (e) {}
		}
	}
	return http_request;
}

function CBmakeHttpRequest(url, id, errorText, postsVars, http_request) {
	if ((arguments.length < 5) || (http_request==null) ) {
		http_request = CBgetHttpRequestInstance();
	}
	if (!http_request) {
		// alert('Giving up: Cannot create an XMLHTTP instance');
		return false;
	}
	http_request.onreadystatechange = function() { CBalertContents(http_request); };
	if (postsVars == null) {
		http_request.open('GET', url, true);
		http_request.send(null);
	} else {
		http_request.open('POST', url, true);
		http_request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		http_request.setRequestHeader("Content-length", postsVars.length);
		http_request.send(postsVars);
	}

	function CBalertContents(http_request) {
		if (http_request.readyState == 4) {
			if ((http_request.status == 200) && (http_request.responseText.length > 0) && (http_request.responseText.length < 1025)) {
				document.getElementById(id).innerHTML = http_request.responseText;
			} else if (errorText.length > 0) {
				document.getElementById(id).innerHTML = errorText;
			} else {
				document.getElementById(id).innerHTML = '';
			}
			http_request = null;
		}
	}

	return true;
}
