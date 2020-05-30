/**
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

Joomla = window.Joomla || {};

(function(document, Joomla) {
	'use strict';

	Joomla.extractionMethodHandler = function(element, prefix) {
		var displayStyle = element.value === 'direct' ? 'hidden' : 'table-row';

		document.getElementById(prefix + '_hostname').classList.add(displayStyle);
		document.getElementById(prefix + '_port').classList.add(displayStyle);
		document.getElementById(prefix + '_username').classList.add(displayStyle);
		document.getElementById(prefix + '_password').classList.add(displayStyle);
		document.getElementById(prefix + '_directory').classList.add(displayStyle);
	}

	Joomla.submitbuttonUpload = function() {
		var form = document.getElementById('uploadForm');

		// do field validation
		if (form.install_package.value == '') {
			alert(Joomla.JText._('COM_INSTALLER_MSG_INSTALL_PLEASE_SELECT_A_PACKAGE'), true);
		}
		else if (form.install_package.files[0].size > form.max_upload_size.value) {
			alert(Joomla.JText._('COM_INSTALLER_MSG_WARNINGS_UPLOADFILETOOBIG'), true);
		}
		else {
			form.submit();
		}
	};

	Joomla.installpackageChange = function() {
		var form = document.getElementById('uploadForm');
		var fileSize = form.install_package.files[0].size;
		var fileSizeMB = fileSize * 1.0 / 1024.0 / 1024.0;
		var fileSizeElement = document.getElementById('file_size');
		var warningElement = document.getElementById('max_upload_size_warn');

		if (form.install_package.value == '') {
			fileSizeElement.classList.add('hidden');
			warningElement.classList.add('hidden');
		}
		else if (fileSize) {
			fileSizeElement.classList.remove('hidden');
			fileSizeElement.innerHTML = Joomla.JText._('JGLOBAL_SELECTED_UPLOAD_FILE_SIZE').replace('%s', fileSizeMB.toFixed(2) + ' MB');

			if (fileSize > form.max_upload_size.value) {
				warningElement.classList.remove('hidden');
			} else {
				warningElement.classList.add('hidden');
			}
		}
	};

	document.addEventListener('DOMContentLoaded', function() {

		var extractionMethod = document.getElementById('extraction_method'),
			uploadMethod     = document.getElementById('upload_method'),
			uploadButton     = document.getElementById('uploadButton'),
			downloadMsg      = document.getElementById('downloadMessage');

		if (extractionMethod) {
			extractionMethod.addEventListener('change', function() {
				Joomla.extractionMethodHandler(extractionMethod, 'row_ftp');
			});
		}

		if (uploadMethod) {
			uploadMethod.addEventListener('change', function() {
				Joomla.extractionMethodHandler(uploadMethod, 'upload_ftp');
			});
		}

		if (uploadButton) {
			uploadButton.addEventListener('click', function() {
				if (downloadMsg) {
					downloadMsg.classList.remove('hidden');
				}
			});
		}

	});

})(document, Joomla);

(function(document, Joomla) {
	/**
	 * PreUpdateChecker
	 *
	 * @type {Object}
	 */
	var PreUpdateChecker = {};

	/**
	 * Config object
	 *
	 * @type {{serverUrl: string, selector: string}}
	 */
	PreUpdateChecker.config = {
		serverUrl: 'index.php?option=com_joomlaupdate&task=update.fetchextensioncompatibility',
		selector: '.extension-check'
	};

	/**
	 * Extension compatibility states returned by the server.
	 *
	 * @type {{INCOMPATIBLE: number, COMPATIBLE: number, MISSING_COMPATIBILITY_TAG: number, SERVER_ERROR: number}}
	 */
	PreUpdateChecker.STATE = {
		INCOMPATIBLE: 0,
		COMPATIBLE: 1,
		MISSING_COMPATIBILITY_TAG: 2,
		SERVER_ERROR: 3
	};

	/**
	 * Run the PreUpdateChecker.
	 * Called by document ready, setup below.
	 */
	PreUpdateChecker.run = function () {
		// Get version of the available joomla update
		PreUpdateChecker.joomlaTargetVersion = document.getElementById('joomlaupdate-wrapper').getAttribute('data-joomla-target-version');

		// Grab all extensions based on the selector set in the config object
		[].slice.call(document.querySelectorAll(PreUpdateChecker.config.selector)).forEach(function (extension) {
			// Check compatibility for each extension, pass an object and a callback
			// function after completing the request
			PreUpdateChecker.checkCompatibility(extension, PreUpdateChecker.setResultView);
		});
	}

	/**
	 * Check the compatibility for a single extension.
	 * Requests the server checking the compatibility based on the data set in the element's data attributes.
	 *
	 * @param {Object} extension
	 * @param {callable} callback
	 */
	PreUpdateChecker.checkCompatibility = function (node, callback) {
		// Result object passed to the callback
		// Set to server error by default
		var extension = {
			element: node,
			state: PreUpdateChecker.STATE.SERVER_ERROR,
			compatibleVersion: 0
		};

		// Request the server to check the compatiblity for the passed extension and joomla version
		Joomla.request({
			url: PreUpdateChecker.config.serverUrl,
			data: {
				'joomla-target-version': PreUpdateChecker.joomlaTargetVersion,
				'extension-id': node.getAttribute('data-extensionId')
			},
			onSuccess(data) {
				var response = JSON.parse(data);
				// Extract the data from the JResponseJson object
				extension.state = response.data.state;
				extension.compatibleVersion = response.data.compatibleVersion;
				extension.currentVersion = node.getAttribute('data-extensionCurrentVersion');

				// Pass the retrieved data to the callback
				callback(extension);
			},
			onError() {
				// Pass the retrieved data to the callback
				callback(extension);
			}
		});
	}

    /**
     * Set the result for a passed extensionData object containing state, jQuery object and compatible version
     *
     * @param {Object} extensionData
     */
    PreUpdateChecker.setResultView = function (extensionData) {
        var html = '';
        var direction = (document.dir !== undefined) ? document.dir : document.getElementsByTagName("html")[0].getAttribute("dir");

        // Switch the compatibility state
        switch (extensionData.state) {
            case PreUpdateChecker.STATE.COMPATIBLE:
                if (extensionData.compatibleVersion == extensionData.currentVersion) {
                    // The compatible version matches the current version -> diplay success label.
                    html = '<span class="badge badge-success">' + Joomla.JText._('JYES') + '</span>';
                } else {
                    // The compatible version does not match the current version => display warning label.
                    if (direction === 'rtl') {
                        html = '<span class="badge badge-warning">' + '(' + extensionData.compatibleVersion + ') '
                           + Joomla.JText._('JYES') + '</span>';
                    } else {
                        html = '<span class="badge badge-warning">' + Joomla.JText._('JYES')
                            + ' (' + extensionData.compatibleVersion + ')</span>';
                    }
                }
                break;
            case PreUpdateChecker.STATE.INCOMPATIBLE:
                // No compatible version found -> display error label
                html = '<span class="badge badge-danger">' + Joomla.JText._('JNO') + '</span>';
                break;
            case PreUpdateChecker.STATE.MISSING_COMPATIBILITY_TAG:
                // Could not check compatibility state -> display warning
                html = '<span class="badge badge-secondary">' + Joomla.JText._('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_VERSION_MISSING') + '</span>';
                break;
            default:
                // An error occured -> show unknown error note
                html = '<span class="badge badge-secondary">' + Joomla.JText._('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_WARNING_UNKNOWN') + '</span>';
        }
        // Insert the generated html
      extensionData.element.innerHTML = html;
    }
    // Run PreUpdateChecker on document ready
  document.addEventListener('DOMContentLoaded', PreUpdateChecker.run, false);
})(document, Joomla);
