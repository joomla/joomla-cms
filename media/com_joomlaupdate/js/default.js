function extractionMethodHandler(target, prefix)
{
	jQuery(function ($) {
		$em = $(target);
		displayStyle = ($em.val() === 'direct') ? 'none' : 'table-row';

		document.getElementById(prefix + '_hostname').style.display = displayStyle;
		document.getElementById(prefix + '_port').style.display = displayStyle;
		document.getElementById(prefix + '_username').style.display = displayStyle;
		document.getElementById(prefix + '_password').style.display = displayStyle;
		document.getElementById(prefix + '_directory').style.display = displayStyle;
	});
}

(function($, document, window) {
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
        PreUpdateChecker.joomlaTargetVersion = window.joomlaTargetVersion;
		PreUpdateChecker.joomlaCurrentVersion = window.joomlaCurrentVersion;

        // Grab all extensions based on the selector set in the config object
        var $extensions = $(PreUpdateChecker.config.selector);
        $extensions.each(function () {
            // Check compatibility for each extension, pass jQuery object and a callback
            // function after completing the request
            PreUpdateChecker.checkCompatibility($(this), PreUpdateChecker.setResultView);
        });
		$('.compatibilitytypes').css('display', 'none').css('margin-left', 0);
		// The currently processing line should show until its finished
		$('#compatibilitytype0').css('display', 'block');
    }

    /**
     * Check the compatibility for a single extension.
     * Requests the server checking the compatibility based on the data set in the element's data attributes.
     *
     * @param {Object} $extension
     * @param {callable} callback
     */
    PreUpdateChecker.checkCompatibility = function ($extension, callback) {
        // Result object passed to the callback
        // Set to server error by default
        var extension = {
            $element: $extension,
            compatibleVersion: 0,
			serverError: 1
        };

        // Request the server to check the compatiblity for the passed extension and joomla version
        $.getJSON(PreUpdateChecker.config.serverUrl, {
            'joomla-target-version': PreUpdateChecker.joomlaTargetVersion,
			'joomla-current-version': PreUpdateChecker.joomlaCurrentVersion,
			'extension-version': $extension.data('extension-current-version'),
            'extension-id': $extension.data('extensionId')
        }).done(function(response) {
            // Extract the data from the JResponseJson object
            extension.serverError = 0;
			extension.compatibilityData = response.data;
        }).always(function(e) {
            // Pass the retrieved data to the callback
            callback(extension);
        });
    }

    /**
     * Set the result for a passed extensionData object containing state, jQuery object and compatible version
     *
     * @param {Object} extensionData
     */
    PreUpdateChecker.setResultView = function (extensionData) {
        var html = '';

        // Process Target Version Extension Compatibility
        if (extensionData.serverError) {
			// An error occured -> show unknown error note
			html = Joomla.JText._('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_SERVER_ERROR');
		}
        else {
			// Switch the compatibility state
			switch (extensionData.compatibilityData.upgradeCompatibilityStatus.state) {
				case PreUpdateChecker.STATE.COMPATIBLE:
					if (extensionData.compatibilityData.upgradeWarning)
					{
						html = '<span class="label label-warning">' + extensionData.compatibilityData.upgradeCompatibilityStatus.compatibleVersion + '</span>';
					}
					else {
						html = extensionData.compatibilityData.upgradeCompatibilityStatus.compatibleVersion == false ? Joomla.JText._('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_NO_COMPATIBILITY_INFORMATION') : extensionData.compatibilityData.upgradeCompatibilityStatus.compatibleVersion;
					}
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
        extensionData.$element.html(html);

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
					html = extensionData.compatibilityData.currentCompatibilityStatus.compatibleVersion == false ? Joomla.JText._('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_NO_COMPATIBILITY_INFORMATION') : extensionData.currentCompatibilityStatus.upgradeCompatibilityStatus.compatibleVersion;
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
		var extensionId = extensionData.$element.data('extensionId')
		document.getElementById('available-version-' + extensionId ).innerHTML = html;

		extensionData.$element.closest('tr').appendTo($('#compatibilitytype' + extensionData.compatibilityData.resultGroup + ' tbody'));
		$('#compatibilitytype' + extensionData.compatibilityData.resultGroup).css('display', 'block');

		document.getElementById('compatibilitytype0').style.display = 'block';

		// Have we finished?
		if ($('#compatibilitytype0 tbody td').length == 0) {
			$('#compatibilitytype0').css('display', 'none');
		}
    }

    // Run PreUpdateChecker on document ready
    $(PreUpdateChecker.run);
})(jQuery, document, window);
