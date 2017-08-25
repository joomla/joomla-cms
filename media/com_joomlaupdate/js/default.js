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
    var PreUpdateChecker = {};  // PreUpdateChecker namespace

    PreUpdateChecker.config = {
        serverUrl: 'index.php?option=com_joomlaupdate&task=update.fetchextensioncompatibility',
        selector: '.extension-check'
    };

    PreUpdateChecker.STATE = {
        INCOMPATIBLE: 0,
        COMPATIBLE: 1,
        MISSING_COMPATIBILITY_TAG: 2,
        SERVER_ERROR: 3
    };

    PreUpdateChecker.run = function () {
        PreUpdateChecker.joomlaTargetVersion = window.joomlaTargetVersion;

        var $extensions = $(PreUpdateChecker.config.selector);
        $extensions.each(function () {
            PreUpdateChecker.checkCompatibility($(this), PreUpdateChecker.setResultView);
        });
    }

    PreUpdateChecker.checkCompatibility = function ($extension, callback) {
        var extension = {
            $element: $extension,
            state: PreUpdateChecker.STATE.SERVER_ERROR,
            compatibleVersion: 0
        };

        $.getJSON(PreUpdateChecker.config.serverUrl, {
            'joomla-target-version': PreUpdateChecker.joomlaTargetVersion,
            'extension-id': $extension.data('extensionId')
        }).done(function() {
            extension.state = data.state;
            extension.compatibleVersion = data.compatibleVersion;

            callback(extension);
        }).fail(function(e) {
            extension.state = PreUpdateChecker.STATE.SERVER_ERROR;
            callback(extension);
        });
    }

    PreUpdateChecker.setResultView = function (extensionData) {
        extensionData.$element.empty();

        var html = '';
        switch (extensionData.state) {
            case PreUpdateChecker.STATE.COMPATIBLE:
                html = '<p class="label label-success">' + COM_JOOMLAUPDATE_VIEW_DEFAULT_PREUPDATE_CHECK_YES
                    + ' (' + extensionData.compatibleVersion + ')</p>';
                break;
            case PreUpdateChecker.STATE.INCOMPATIBLE:
                html = '<p class="label label-important">' + COM_JOOMLAUPDATE_VIEW_DEFAULT_PREUPDATE_CHECK_NO + '</p>';
                break;
            case PreUpdateChecker.STATE.MISSING_COMPATIBILITY_TAG:
                html = '<p class="label label-warning">' + COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_VERSION_MISSING + '</p>';
                break;
            default:
                html = '<p class="label">' + COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_WARNING_UNKNOWN + '</p>';
        }
        extensionData.$element.html(html);
    }
    // Run PreUpdateChecker on document ready
    $(PreUpdateChecker.run);
})(jQuery, document, window);