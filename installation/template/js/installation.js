/**
 * @version		$Id$
 * @package		Joomla.Installation
 * @subpackage	JavaScript
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// Only define the Install namespace if not defined.
if (typeof(Install) === 'undefined') {
	var Install = {};
};

/**
 * Method to install sample data via AJAX request.
 */
Install.sampleData = function(el) {
	// make the ajax call
	var req = new Request({
		method: 'get',
		url: 'index.php?'+$(el.form).toQueryString(),
		data: {'task':'setup.loadSampleData', 'protocol':'json'},
		onRequest: function() { el.set('disabled', 'disabled'); },
		onComplete: function(response) {
			var r = JSON.decode(response);
			if (r)
			{
				Joomla.replaceTokens(r.token)
				if (r.error == false) {
					el.set('value', r.data.text);
				}
			}
			el.set('disabled', '');
		}
	}).send();
};

/**
 * Method to detect the FTP root via AJAX request.
 */
Install.detectFtpRoot = function(el) {
	// make the ajax call
	var req = new Request({
		method: 'get',
		url: 'index.php?'+$(el.form).toQueryString(),
		data: {'task':'setup.detectFtpRoot', 'protocol':'json'},
		onRequest: function() { el.set('disabled', 'disabled'); },
		onComplete: function(response) {
			var r = JSON.decode(response);
			if (r)
			{
				Joomla.replaceTokens(r.token)
				if (r.error == false) {
					$('ftproot').set('value', r.data.root);
				}
			}
			el.set('disabled', '');
		}
	}).send();
};

/**
 * Method to detect the FTP root via AJAX request.
 */
Install.verifyFtpSettings = function(el) {
	// make the ajax call
	var req = new Request({
		method: 'get',
		url: 'index.php?'+$(el.form).toQueryString(),
		data: {'task':'setup.verifyFtpSettings', 'protocol':'json'},
		onRequest: function() { el.set('disabled', 'disabled'); },
		onComplete: function(response) {
			var r = JSON.decode(response);
			if (r)
			{
				Joomla.replaceTokens(r.token)
				if (r.error == false) {
					alert('Settings Correct');
				}
				else {
					alert(r.message);
				}
			}
			el.set('disabled', '');
		}
	}).send();
};
