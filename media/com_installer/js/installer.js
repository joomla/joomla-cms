/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

Joomla = window.Joomla || {};

(function (Joomla) {
		'use strict';

		var installPackageButtonId = 'installbutton_package';

		document.addEventListener('DOMContentLoaded', function () {
				Joomla.submitbuttonpackage = function () {
						var form = document.getElementById('adminForm');

						// do field validation
						if (form.install_package.value == '') {
								alert(Joomla.JText._('PLG_INSTALLER_PACKAGEINSTALLER_NO_PACKAGE'), true);
						} else {
								Joomla.displayLoader();

								form.installtype.value = 'upload';
								form.submit();
						}
				};

				Joomla.submitbuttonfolder = function () {
						var form = document.getElementById('adminForm');

						// do field validation
						if (form.install_directory.value == '') {
								alert(Joomla.JText._('PLG_INSTALLER_FOLDERINSTALLER_NO_INSTALL_PATH'), true);
						} else {
								Joomla.displayLoader();

								form.installtype.value = 'folder';
								form.submit();
						}
				};

				Joomla.submitbuttonurl = function () {
						var form = document.getElementById('adminForm');

						// do field validation
						if (form.install_url.value == '' || form.install_url.value == 'http://' || form.install_url.value == 'https://') {
								alert(Joomla.JText._('PLG_INSTALLER_URLINSTALLER_NO_URL'), true);
						} else {
								Joomla.displayLoader();

								form.installtype.value = 'url';
								form.submit();
						}
				};

				Joomla.submitbutton4 = function () {
						var form = document.getElementById('adminForm');

						// do field validation
						if (form.install_url.value == '' || form.install_url.value == 'http://' || form.install_url.value == 'https://') {
								alert(Joomla.JText._('COM_INSTALLER_MSG_INSTALL_ENTER_A_URL'), true);
						} else {
								Joomla.displayLoader();

								form.installtype.value = 'url';
								form.submit();
						}
				};

				Joomla.submitbuttonUpload = function () {
						var form = document.getElementById('uploadForm');

						// do field validation
						if (form.install_package.value == '') {
								alert(Joomla.JText._('COM_INSTALLER_MSG_INSTALL_PLEASE_SELECT_A_PACKAGE'), true);
						} else {
								Joomla.displayLoader();

								form.submit();
						}
				};

				Joomla.displayLoader = function () {
						var loading = document.getElementById('loading');
						if (loading) {
								loading.style.display = 'block';
						}
				};

				var loading = document.getElementById('loading'),
				    installer = document.getElementById('installer-install');
				if (loading && installer) {
						loading.style.top = parseInt(installer.offsetTop - window.pageYOffset);
						loading.style.left = 0;
						loading.style.width = '100%';
						loading.style.height = '100%';
						loading.style.display = 'none';
						loading.style.marginTop = '-10px';
				}

				document.getElementById(installPackageButtonId).addEventListener('click', function (event) {
						event.preventDefault();
						Joomla.submitbuttonpackage();
				});
		});
})(Joomla);

document.addEventListener('DOMContentLoaded', function () {
		if (typeof FormData === 'undefined') {
				document.querySelector('#legacy-uploader').style.display = 'block';
				document.querySelector('#uploader-wrapper').style.display = 'none';
				return;
		}

		var dragZone = document.querySelector('#dragarea');
		var fileInput = document.querySelector('#install_package');
		var loading = document.querySelector('#loading');
		var button = document.querySelector('#select-file-button');
		var returnUrl = document.querySelector('#installer-return').value;
		var token = document.querySelector('#installer-token').value;
		var url = 'index.php?option=com_installer&task=install.ajax_upload';

		if (returnUrl) {
				url += '&return=' + returnUrl;
		}

		button.addEventListener('click', function () {
				fileInput.click();
		});

		fileInput.addEventListener('change', function () {
				Joomla.submitbuttonpackage();
		});

		dragZone.addEventListener('dragenter', function (event) {
				event.preventDefault();
				event.stopPropagation();

				dragZone.classList.add('hover');

				return false;
		});

		// Notify user when file is over the drop area
		dragZone.addEventListener('dragover', function (event) {
				event.preventDefault();
				event.stopPropagation();

				dragZone.classList.add('hover');

				return false;
		});

		dragZone.addEventListener('dragleave', function (event) {
				event.preventDefault();
				event.stopPropagation();
				dragZone.classList.remove('hover');

				return false;
		});

		dragZone.addEventListener('drop', function (event) {
				event.preventDefault();
				event.stopPropagation();

				dragZone.classList.remove('hover');

				var files = event.target.files || event.dataTransfer.files;

				if (!files.length) {
						return;
				}

				var file = files[0];
				var data = new FormData();

				data.append('install_package', file);
				data.append('installtype', 'upload');
				data.append(token, 1);

				loading.style.display = 'block';

				// @TODO Allow Joomla.request to make request without header 'content-type'
				var xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('MSXML2.XMLHTTP.3.0');
				xhr.open('POST', url, true);

				var token = Joomla.getOptions('csrf.token', '');

				if (token) {
						xhr.setRequestHeader('X-CSRF-Token', token);
				}

				xhr.onreadystatechange = function () {
						// Request not finished
						if (xhr.readyState !== 4) return;

						// Request finished and response is ready
						if (xhr.status === 200) {
								var res = JSON.parse(xhr.responseText);
								if (!res.success) {
										console.log(res.message, res.messages);
								}
								// Always redirect that can show message queue from session
								if (res.data.redirect) {
										location.href = res.data.redirect;
								} else {
										location.href = 'index.php?option=com_installer&view=install';
								}
						} else if (options.onError) {
								loading.style.display = 'none';
								alert(xhr.statusText);
						}
				};

				xhr.send(data);

				// @TODO Use Joomla.request once the code is patched to support headerless requests!
				// Joomla.request({
				// 	url: url,
				// 	method: 'POST',
				// 	perform: true,
				// 	headers: {'Content-Type': 'remove'},
				// 	onSuccess: (response) => {
				// 		console.log(response)
				// 		const res = JSON.parse(response);
				// 		if (!res.success) {
				// 			console.log(res.message, res.messages);
				// 		}
				// 		// Always redirect that can show message queue from session
				// 		if (res.data.redirect) {
				// 			location.href = res.data.redirect;
				// 		} else {
				// 			location.href = 'index.php?option=com_installer&view=install';
				// 		}
				// 	},
				// 	onError: (error) => {
				// 		loading.style.display = 'none';
				// 		alert(error.statusText);
				// 	}
				// });
		});
});
