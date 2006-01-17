/**
 * @version $Id$
 * @package Joomla
 * @subpackage Installation
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

function toggleAdvanced() {


	if (document.getElementById("advanced").checked == false) {
		document.getElementById("host").style.display = 'none';
	} else {
		document.getElementById("host").style.display = '';
	}
}

/**
 * Function to enable/disable the ftp configuration form based upon whether or not the
 * Enable FTP checkbox is checked.
 */
function formState() {

	var ftpRoot = document.getElementById("ftproot");
	var ftpUser = document.getElementById("ftpuser");
	var ftpPass = document.getElementById("ftppass");
	var findButton = document.getElementById("findbutton");

	if (document.getElementById('ftpenable').checked == false) {

		// Disable form fields
		ftpRoot.disabled = true;
		findButton.disabled = true;
	} else {
	
		// Enable form fields	
		ftpRoot.disabled = false;
		findButton.disabled = false;
	}

}