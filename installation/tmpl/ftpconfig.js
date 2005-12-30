/**
 * @version $Id: $
 * @package Joomla
 * @subpackage Installation
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */


function validateForm( frm, task ) {
	var ftpEnable = document.getElementById("ftpenable");
	var ftpRoot = document.getElementById("ftproot");
	
	if (ftpEnable.checked == false) {
		alert( '<jos:translate key="warnFtpEnable" escape="yes">Disabling the FTP Filesystem layer can cause problems\nwith filesystem ownership permissions.</jos:translate>' );
		submitForm( frm, task );
	} else if (ftpRoot.value == '') {
		alert( '<jos:translate key="warnFtpRoot" escape="yes">Please enter an FTP Root or use\nthe autofind root button.</jos:translate>' );
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
		ftpRoot = document.getElementById("ftproot");
		ftpUser = document.getElementById("ftpuser");
		ftpPass = document.getElementById("ftppass");
		userIn = document.getElementById("userinput");
		passIn = document.getElementById("passinput");
		ftpRoot.value = data;
		ftpUser.value = userIn.value;
		ftpPass.value = passIn.value;
		//alert( "The Data:\n" + data );
	}
}
	
function JProcess( thisForm ) {
	if( thisForm.elements['user'].value == "" ) {
		alert( "You must enter a valid FTP username." );
		return false;
	}
	if( thisForm.elements['pass'].value == "" ) {
		alert( "You must enter a valid FTP password." );
		return false;
	}

	thisForm.ajform_submit();
}

function goForm(whichForm) {
	var thisForm = document.getElementById( whichForm );
	// thisForm.submit() will not work using AJFORM. Instead, you need to use the following:
	thisForm.ajform_submit();
}

/**
 * Function to enable/disable the ftp configuration form based upon whether or not the
 * Enable FTP checkbox is checked.
 */
function formState() {

	var ftpRoot = document.getElementById("ftproot");
	var ftpUser = document.getElementById("ftpuser");
	var ftpPass = document.getElementById("ftppass");
	var userIn = document.getElementById("userinput");
	var passIn = document.getElementById("passinput");
	var findButton = document.getElementById("findbutton");

	if (document.getElementById('ftpenable').checked == false) {

		// Disable form fields
		userIn.disabled = true;
		passIn.disabled = true;
		ftpRoot.disabled = true;
		findButton.disabled = true;
	} else {
	
		// Enable form fields	
		userIn.disabled = false;
		passIn.disabled = false;
		ftpRoot.disabled = false;
		findButton.disabled = false;
	}

}