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

        // Grab all extensions based on the selector set in the config object
        var $extensions = $(PreUpdateChecker.config.selector);
        $extensions.each(function () {
            // Check compatibility for each extension, pass jQuery object and a callback
            // function after completing the request
            PreUpdateChecker.checkCompatibility($(this), PreUpdateChecker.setResultView);
        });
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
            state: PreUpdateChecker.STATE.SERVER_ERROR,
            compatibleVersion: 0
        };

        // Request the server to check the compatiblity for the passed extension and joomla version
        $.getJSON(PreUpdateChecker.config.serverUrl, {
            'joomla-target-version': PreUpdateChecker.joomlaTargetVersion,
            'extension-id': $extension.data('extensionId')
        }).done(function(response) {
            // Extract the data from the JResponseJson object
            extension.state = response.data.state;
            extension.compatibleVersion = response.data.compatibleVersion;
            extension.currentVersion = $extension.data('extensionCurrentVersion')
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
        
        // Switch the compatibility state
        switch (extensionData.state) {
            case PreUpdateChecker.STATE.COMPATIBLE:
                if (extensionData.compatibleVersion == extensionData.currentVersion) {
                    // The compatible version matches the current version -> diplay success label.
                    html = '<span class="label label-success">' + Joomla.JText._('JYES') + '</span>';
                } else {
                    // The compatible version does not match the current version => display warning label.
                    html = '<span class="label label-warning">' + Joomla.JText._('JYES')
                        + ' (' + extensionData.compatibleVersion + ')</span>';
                }
                break;
            case PreUpdateChecker.STATE.INCOMPATIBLE:
                // No compatible version found -> display error label
                html = '<span class="label label-important">' + Joomla.JText._('JNO') + '</span>';
                break;
            case PreUpdateChecker.STATE.MISSING_COMPATIBILITY_TAG:
                // Could not check compatibility state -> display warning
                html = '<span class="label">' + Joomla.JText._('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_VERSION_MISSING') + '</span>';
                break;
            default:
                // An error occured -> show unknown error note
                html = '<span class="label">' + Joomla.JText._('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_WARNING_UNKNOWN') + '</span>';
        }
        // Insert the generated html
        extensionData.$element.html(html);
    }
    // Run PreUpdateChecker on document ready
    $(PreUpdateChecker.run);
})(jQuery, document, window);
