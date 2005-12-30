/**
 * @version $Id: $
 * @package Joomla
 * @subpackage Installation
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

function validateForm( frm, task ) {
	var DBtype = getElementByName( frm, 'vars[DBtype]' );
	var DBhostname = getElementByName( frm, 'vars[DBhostname]' );

	if ( DBtype.selectedIndex == 0 ) {
		alert( '<jos:translate key="validType" escape="yes">Please select the database type</jos:translate>' );
		return;
	} else if (DBhostname.value == '') {
		alert( '<jos:translate key="validHost" escape="yes">Please enter the host name</jos:translate>' );
		return;
	} else if (!document.getElementById('vars_dbcollation')) {
		alert( '<jos:translate key="validCollation" escape="yes">Please choose a database collation</jos:translate>' );
		return;
	} else {
		submitForm( frm, task );
	}
}

function getReturnData( data , statusCode , statusMessage) {
	//AJFORM failed. Submit form normally.
	if( statusCode != AJForm.STATUS['SUCCESS'] ) {
		alert( statusMessage );
		return true;
	}
	//AJFORM succeeded.
	else {

		var tc = document.getElementById("theCollation");
		var cm = document.getElementById("collationMessage");
		
		var split = data.indexOf("\n");
		
		if (data.substr(0, split) == "true") {
			cm.innerHTML = '<jos:translate key="tipCollationUtf" escape="yes"><p>This version of MySQL includes UTF-8 support which is the required encoding.</p><p>Choose a collation from the list. If none appears for your language, that is because the default collation <em>utf8_general_ci</em> is suitable.</p></jos:translate>';
		} else {
			cm.innerHTML = '<jos:translate key="tipCollationNonUtf" escape="yes"><p>This version of MySQL does not have UTF-8 support which is the required encoding.</p><p>It is recommended that you upgrade your database to a version newer than 4.1.2. If this is not possible, Joomla! will store utf-8 encoded content in your existing database in a backward compatibility mode. Collation selection is not possible in this mode and a default collation will be used.</p></jos:translate>';
		}

		tc.innerHTML = data.substr(split + 1);
	}
}
	
function JProcess() {
/*	if( thisForm.elements['user'].value == "" ) {
		alert( "You did not enter a username. The data will not be sent to the server." );
		return false;
	}
	if( thisForm.elements['pass'].value == "" ) {
		alert( "You did not enter a password. The data will not be sent to the server." );
		return false;
	}
*/
	var type = document.getElementById("vars_dbtype");
	var host = document.getElementById("vars_dbhostname");
	var user = document.getElementById("vars_dbusername");
	var pass = document.getElementById("vars_dbpassword");
	var ajtype = document.getElementById("theType");
	var ajhost = document.getElementById("theHost");
	var ajuser = document.getElementById("theUser");
	var ajpass = document.getElementById("thePass");
	var thisForm = document.getElementById( 'mainForm' );
	
	ajtype.value = type.value;
	ajhost.value = host.value;
	ajuser.value = user.value;
	ajpass.value = pass.value;
	
	thisForm.ajform_submit();
}

function goForm(whichForm) {
	thisForm = document.getElementById( whichForm );
	// thisForm.submit() will not work using AJFORM. Instead, you need to use the following:
	thisForm.ajform_submit();
}
