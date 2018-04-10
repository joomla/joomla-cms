/**
 * @package     Joomla.Installation
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla = window.Joomla || {};

// @TODO FTP???
Joomla.installation = Joomla.installation || {};
// Initialize the installation data
Joomla.installation.data = {
	// FTP
	ftpUsername: "",
	ftpPassword: "",
	ftpHost: "",
	ftpPort: 21,
	ftpRoot: "/",
};

/**
 * Method to detect the FTP root via AJAX request.
 *
 * @param el  The page element requesting the event
 */
Joomla.installation.detectFtpRoot = function(el) {
	var data, task, form = document.getElementById('ftpForm');

	data = Joomla.serialiseForm(form); //'format: json&' +
	el.setAttribute('disabled', 'disabled');
	task = 'detectftproot';

	Joomla.request({
		type: "POST",
		url : Joomla.installationBaseUrl + '?task=' + task + '&format=json',
		data: data,
		perform: true,
		headers: {'Content-Type': 'application/x-www-form-urlencoded'},
		onSuccess: function(response, xhr){
			var r = JSON.parse(response);

			if (r) {
				Joomla.replaceTokens(r.token)
				console.log(r.messages.error)
				if (r.messages && !r.messages.error) {
					if (r.data && r.data.root) {
						document.getElementById('jform_ftp_host').value += r.data.root;
					}
				} else {
					alert(r.messages.warning);
				}
			}
			el.removeAttribute('disabled');
		},
		onError:   function(xhr){
			try {
				var r = JSON.parse(xhr.responseText);
				Joomla.replaceTokens(r.token);
				alert(xhr.status + ': ' + r.message);
			} catch (e) {
				alert(xhr.status + ': ' + xhr.statusText);
			}
		}
	});
};

if (document.getElementById('showFtp')) {
	// @TODO FTP??
	document.getElementById('showFtp').style.display = 'none';
	document.getElementById('showFtp').addEventListener('click', function(e) {
		e.preventDefault();
		if (document.getElementById('ftpOptions')) {
			document.getElementById('ftpOptions').classList.remove('hidden');
			document.getElementById('ftpOptions').scrollIntoView();
		}
	})
}

if (document.getElementById('verifybutton')) {
	document.getElementById('verifybutton').addEventListener('click', function(e) {
		e.preventDefault();
		// @TODO FTP??
		//onclick="Install.verifyFtpSettings(this);"
		var ftpForm = document.getElementById('ftpForm');
		if (ftpForm) {
			Joomla.installation.data.ftpUsername = document.getElementById('jform_ftp_user').value;
			Joomla.installation.data.ftpPassword = document.getElementById('jform_ftp_pass').value;
			Joomla.installation.data.ftpHost = document.getElementById('jform_ftp_host').value;
			Joomla.installation.data.ftpPort = document.getElementById('jform_ftp_port').value;

			var p, data = [];
			for(p in Joomla.installation.data) {
				data.push(Joomla.installation.data[p])
			}
			sessionStorage.setItem('installData', JSON.stringify(data));
			// get it back: JSON.parse(sessionStorage.installData)
		}
	});
}