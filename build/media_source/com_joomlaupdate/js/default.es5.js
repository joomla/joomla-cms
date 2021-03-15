/**
 * @copyright   (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

Joomla = window.Joomla || {};

(function(document, Joomla) {
	'use strict';

	Joomla.extractionMethodHandler = function(element, prefix) {
		var dom = [
			prefix + '_hostname',
			prefix + '_port',
			prefix + '_username',
			prefix + '_password',
			prefix + '_directory'
		];

		if (element.value === 'direct') {
			dom.map(el => document.getElementById(el).classList.add('hidden'));
		} else {
			dom.map(el => document.getElementById(el).classList.remove('hidden'));
		}
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
	 * Warning visibility flags
	 *
	 * @type {Boolean}
	 */
	var showorangewarning = false;
	var showyellowwarning = false;

	/**
	 * Config object
	 *
	 * @type {{serverUrl: string, selector: string}}
	 */
	PreUpdateChecker.config = {
		serverUrl: 'index.php?option=com_joomlaupdate&task=update.fetchextensioncompatibility',
		selector: '.extension-check',
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
		SERVER_ERROR: 3,
	};

	/**
	 * Run the PreUpdateChecker.
	 * Called by document ready, setup below.
	 */
	PreUpdateChecker.run = function () {
		// Get version of the available joomla update
		PreUpdateChecker.joomlaTargetVersion = document.getElementById('joomlaupdate-wrapper').getAttribute('data-joomla-target-version');
		PreUpdateChecker.joomlaCurrentVersion = document.getElementById('joomlaupdate-wrapper').getAttribute('data-joomla-current-version');

		// No point creating and loading a component stylesheet for 4 settings
		$('.compatibilitytypes img').css('height', '20px');
		[].slice.call(document.querySelectorAll('.compatibilitytypes')).forEach((el) => {
			el.style.display = 'none';
			el.style.marginLeft = 0;
		});
		// The currently processing line should show until it’s finished
		document.getElementById('compatibilitytype0').style.display = 'block';
		[].slice.call(document.querySelectorAll('.compatibilitytoggle')).forEach((el) => {
			el.style.float = 'right';
			el.style.cursor = 'pointer';
		});

		$('.compatibilitytoggle').on('click', function(toggle, index)
		{
			var compatibilitytypes = $(this).closest('fieldset.compatibilitytypes');
			if($(this).data('state') == 'closed')
			{
				$(this).data('state', 'open');
				$(this).html( COM_JOOMLAUPDATE_VIEW_DEFAULT_SHOW_LESS_EXTENSION_COMPATIBILITY_INFORMATION);
				compatibilitytypes.find('.exname').removeClass('col-md-8').addClass('col-md-4');
				compatibilitytypes.find('.extype').removeClass('col-md-4').addClass('col-md-2');
				compatibilitytypes.find('.upcomp').removeClass('hidden').addClass('col-md-2');
				compatibilitytypes.find('.currcomp').removeClass('hidden').addClass('col-md-2');
				compatibilitytypes.find('.instver').removeClass('hidden').addClass('col-md-2');

				if (PreUpdateChecker.showyellowwarning)
				{
					compatibilitytypes.find("#updateyellowwarning").removeClass('hidden');
				}
				if (PreUpdateChecker.showorangewarning)
				{
					compatibilitytypes.find("#updateorangewarning").removeClass('hidden');
				}
			}
			else
			{
				$(this).data('state', 'closed');
				$(this).html( COM_JOOMLAUPDATE_VIEW_DEFAULT_SHOW_MORE_EXTENSION_COMPATIBILITY_INFORMATION);
				compatibilitytypes.find('.exname').addClass('col-md-8').removeClass('col-md-4');
				compatibilitytypes.find('.extype').addClass('col-md-4').removeClass('col-md-2');
				compatibilitytypes.find('.upcomp').addClass('hidden').removeClass('col-md-2');
				compatibilitytypes.find('.currcomp').addClass('hidden').removeClass('col-md-2');
				compatibilitytypes.find('.instver').addClass('hidden').removeClass('col-md-2');

				compatibilitytypes.find("#updateyellowwarning").addClass('hidden');
				compatibilitytypes.find("#updateorangewarning").addClass('hidden');
			}
		});

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
			compatibleVersion: 0,
			serverError: 1,
		};

		// Request the server to check the compatiblity for the passed extension and joomla version
		Joomla.request({
			url: PreUpdateChecker.config.serverUrl
				+ '&joomla-target-version=' + encodeURIComponent(PreUpdateChecker.joomlaTargetVersion)
				+ 'joomla-current-version=' + PreUpdateChecker.joomlaCurrentVersion
				+ 'extension-version=' + node.getAttribute('data-extension-current-version')
				+ '&extension-id=' + encodeURIComponent(node.getAttribute('data-extension-id')),
			onSuccess(data) {
				var response = JSON.parse(data);
				// Extract the data from the JResponseJson object
				extension.serverError = 0;
				extension.compatibilityData = response.data;
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

		// Process Target Version Extension Compatibility
		if (extensionData.serverError) {
			// An error occurred -> show unknown error note
			html = Joomla.JText._('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_SERVER_ERROR');
		}
		else {
			// Switch the compatibility state
			switch (extensionData.compatibilityData.upgradeCompatibilityStatus.state) {
				case PreUpdateChecker.STATE.COMPATIBLE:
					if (extensionData.compatibilityData.upgradeWarning)
					{
						html = '<span class="label label-warning">' + extensionData.compatibilityData.upgradeCompatibilityStatus.compatibleVersion + '</span>';
						PreUpdateChecker.showyellowwarning = true;
					}
					else {
						html = extensionData.compatibilityData.upgradeCompatibilityStatus.compatibleVersion == false ? Joomla.JText._('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_NO_COMPATIBILITY_INFORMATION') : extensionData.compatibilityData.upgradeCompatibilityStatus.compatibleVersion;
					}
					break;
				case PreUpdateChecker.STATE.INCOMPATIBLE:
					// No compatible version found -> display error label
					html = Joomla.JText._('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_NO_COMPATIBILITY_INFORMATION');
					PreUpdateChecker.showorangewarning = true;
					break;
				case PreUpdateChecker.STATE.MISSING_COMPATIBILITY_TAG:
					// Could not check compatibility state -> display warning
					html = Joomla.JText._('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_NO_COMPATIBILITY_INFORMATION');
					PreUpdateChecker.showorangewarning = true;
					break;
				default:
					// An error occured -> show unknown error note
					html = Joomla.JText._('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_WARNING_UNKNOWN');
			}
		}

		// Insert the generated html
      extensionData.element.innerHTML = html;

		// Process Current Version Extension Compatibility
		html = '';
		if (extensionData.serverError) {
			// An error occured -> show unknown error note
			html = Joomla.JText._('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_SERVER_ERROR');
		}
		else {
			// Switch the compatibility state
			switch (extensionData.compatibilityData.currentCompatibilityStatus.state) {
				case PreUpdateChecker.STATE.COMPATIBLE:
					html = extensionData.compatibilityData.currentCompatibilityStatus.compatibleVersion == false ? Joomla.JText._('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_NO_COMPATIBILITY_INFORMATION') : extensionData.compatibilityData.currentCompatibilityStatus.compatibleVersion;
					break;
				case PreUpdateChecker.STATE.INCOMPATIBLE:
					// No compatible version found -> display error label
					html = Joomla.JText._('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_NO_COMPATIBILITY_INFORMATION');
					break;
				case PreUpdateChecker.STATE.MISSING_COMPATIBILITY_TAG:
					// Could not check compatibility state -> display warning
					html = Joomla.JText._('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_NO_COMPATIBILITY_INFORMATION');
					break;
				default:
					// An error occured -> show unknown error note
					html = Joomla.JText._('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_WARNING_UNKNOWN');
			}
		}
		// Insert the generated html
		var extensionId = extensionData.element.getAttribute('data-extension-id');
		document.getElementById('available-version-' + extensionId ).innerHTML = html;

		var compatType = document.querySelector('#compatibilitytype' + extensionData.compatibilityData.resultGroup + ' tbody')

		if (compatType)
		{
			compatType.appendChild(extensionData.element.closest('tr'));
		}

		document.getElementById('compatibilitytype' + extensionData.compatibilityData.resultGroup).style.display = 'block';
		document.getElementById('compatibilitytype0').style.display = 'block';

		// Have we finished?
		if ($('#compatibilitytype0 tbody td').length == 0) {
			document.getElementById('compatibilitytype0').style.display = 'none';
		}
	}
    // Run PreUpdateChecker on document ready
  document.addEventListener('DOMContentLoaded', PreUpdateChecker.run, false);
})(document, Joomla);
