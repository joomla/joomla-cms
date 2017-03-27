/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

(function() {
	"use strict";

	document.addEventListener('DOMContentLoaded', function() {

		Joomla.submitbuttonpackage = function() {
			var form = document.getElementById('adminForm');

			// do field validation 
			if (form.install_package.value == '') {
				alert(Joomla.JText._('PLG_INSTALLER_PACKAGEINSTALLER_NO_PACKAGE'), true);
			}
			else {
				Joomla.displayLoader();

				form.installtype.value = 'upload';
				form.submit();
			}
		};

		Joomla.submitbuttonfolder = function() {
			var form = document.getElementById('adminForm');

			// do field validation 
			if (form.install_directory.value == '') {
				alert(Joomla.JText._('PLG_INSTALLER_FOLDERINSTALLER_NO_INSTALL_PATH'), true);
			}
			else {
				Joomla.displayLoader();

				form.installtype.value = 'folder';
				form.submit();
			}
		};

		Joomla.submitbuttonurl = function() {
			var form = document.getElementById('adminForm');

			// do field validation 
			if (form.install_url.value == '' || form.install_url.value == 'http://' || form.install_url.value == 'https://') {
				alert(Joomla.JText._('PLG_INSTALLER_URLINSTALLER_NO_URL'), true);
			}
			else {
				Joomla.displayLoader();

				form.installtype.value = 'url';
				form.submit();
			}
		};

		Joomla.submitbutton4 = function() {
			var form = document.getElementById("adminForm");

			// do field validation
			if (form.install_url.value == '' || form.install_url.value == 'http://' || form.install_url.value == 'https://') {
				alert(Joomla.JText._('COM_INSTALLER_MSG_INSTALL_ENTER_A_URL'), true);
			}
			else {
				Joomla.displayLoader();

				form.installtype.value = 'url';
				form.submit();
			}
		};

		Joomla.submitbuttonInstallWebInstaller = function() {
			document.getElementById('adminForm').install_url.value = 'https://appscdn.joomla.org/webapps/jedapps/webinstaller.xml';

			Joomla.submitbutton4();
		};
		
		Joomla.displayLoader = function() {
			var loading = document.getElementById('loading');
			if (loading) {
				loading.style.display = 'block';
			}
		};

		var loading   = document.getElementById('loading'),
		    installer = document.getElementById('installer-install');
		if (loading && installer) {
			loading.style.top = parseInt(installer.offsetTop - window.pageYOffset);
			loading.style.left = 0;
			loading.style.width = '100%';
			loading.style.height = '100%';
			loading.style.display = 'none';
			loading.style.marginTop = '-10px';
		}

	});

})();
