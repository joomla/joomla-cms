/**
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

Joomla = window.Joomla || {};

(function() {
	'use strict';

	var installPackageButtonId = 'installbutton_package';

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

		Joomla.submitbuttonUpload = function() {
			var form = document.getElementById('uploadForm');

			// do field validation
			if (form.install_package.value == '') {
				alert(Joomla.JText._('COM_INSTALLER_MSG_INSTALL_PLEASE_SELECT_A_PACKAGE'), true);
			}
			else {
				Joomla.displayLoader();

				form.submit();
			}
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

		document.getElementById(installPackageButtonId).addEventListener('click', function(event) {
      event.preventDefault();
      Joomla.submitbuttonpackage();
    })
	});

}());


jQuery(document).ready(function($) {

	if (typeof FormData === 'undefined') {
		$('#legacy-uploader').show();
		$('#uploader-wrapper').hide();
		return;
	}

	var dragZone  = $('#dragarea'),
		fileInput = $('#install_package'),
		loading   = $('#loading'),
		button    = $('#select-file-button'),
		returnUrl = $('#installer-return').val(),
		token     = $('#installer-token').val(),
		url       = 'index.php?option=com_installer&task=install.ajax_upload';

	if (returnUrl) {
		url += '&return=' + returnUrl;
	}

	button.on('click', function(e) {
		fileInput.click();
	});

	fileInput.on('change', function (e) {
		Joomla.submitbuttonpackage();
	});

	dragZone.on('dragenter', function(e) {
		e.preventDefault();
		e.stopPropagation();

		dragZone.addClass('hover');

		return false;
	});

	// Notify user when file is over the drop area
	dragZone.on('dragover', function(e) {
		e.preventDefault();
		e.stopPropagation();

		dragZone.addClass('hover');

		return false;
	});

	dragZone.on('dragleave', function(e) {
		e.preventDefault();
		e.stopPropagation();
		dragZone.removeClass('hover');

		return false;
	});

	dragZone.on('drop', function(e) {
		e.preventDefault();
		e.stopPropagation();

		dragZone.removeClass('hover');

		var files = e.originalEvent.target.files || e.originalEvent.dataTransfer.files;

		if (!files.length) {
			return;
		}

		var file = files[0],
			data = new FormData;

		data.append('install_package', file);
		data.append('installtype', 'upload');
		data.append(token, 1);

		loading.css('display', 'block');

		$.ajax({
			url: url,
			data: data,
			type: 'post',
			processData: false,
			cache: false,
			contentType: false
		}).done(function(res) {
			if (!res.success) {
				console.log(res.message, res.messages);
			}
			// Always redirect that can show message queue from session 
			if (res.data.redirect) {
				location.href = res.data.redirect;
			} else {
				location.href = 'index.php?option=com_installer&view=install';
			}
		}).fail(function(error) {
			loading.css('display', 'none');
			alert(error.statusText);
		});
	});
});
