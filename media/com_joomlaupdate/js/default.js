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
        proxyUrl: 'index.php?option=com_joomlaupdate&task=update.fetchurl&url=',
        selector: '.extension-check'
    };

    PreUpdateChecker.STATE = {
        INCOMPATIBLE: 0,
        COMPATIBLE: 1,
        MISSING_COMPATIBILITY_TAG: 2,
        SERVER_ERROR: 3
    };

    PreUpdateChecker.joomlaTargetVersion = window.joomlaTargetVersion;

    PreUpdateChecker.run = function () {
        var $extensions = $(PreUpdateChecker.config.selector);
        $extensions.each(function () {
            PreUpdateChecker.checkCompatibility($(this), PreUpdateChecker.setResultView);
        });
    }

    PreUpdateChecker.checkCompatibility = function ($extension, callback) {
        var extension = {
            $element: $extension,
            updateUrl: $extension.data('extensionUpdateUrl'),
            version: $extension.data('extensionCurrentVersion'),
            state: PreUpdateChecker.STATE.SERVER_ERROR,
            compatibleVersion: 0
        };

        if(!extension.updateUrl) {
            extension.state = PreUpdateChecker.STATE.MISSING_COMPATIBILITY_TAG;
            callback(extension);
        }

        $.get(PreUpdateChecker.config.proxyUrl + extension.updateUrl, function (data) {
            var parseResult = PreUpdateChecker.parseXML(extension.version, data);
            extension.compatibleVersion = parseResult.version;
            extension.state = parseResult.state;

            callback(extension);
        }).fail(function() {
            extension.state = PreUpdateChecker.STATE.SERVER_ERROR;
            callback(extension);
        });
    }

    PreUpdateChecker.parseXML = function (currentVersion, xml) {
        var result = {
            version: 0,
            state: PreUpdateChecker.STATE.INCOMPATIBLE
        };

        // Parse XML via browser's native parsing function and convert it to a valid jQuery object
        try {
            var $xmlDoc = $($.parseXML(xml));
        } catch(e) {
            result.state = PreUpdateChecker.STATE.SERVER_ERROR;
            return result;
        }
        
        // Iterate all updates..
        $xmlDoc.find('update').each(function() {
            // TODO: fix check, respect 3.[1234] as well
            // TODO: set state: current version compatible or update compatible
            // TODO: check all updates, don't break loop. Chose oldest update
            // TODO: check if .attr breaks code in case there is no targetplatform node
            // Check if update matches new joomla version
            if($(this).find('targetplatform[name="joomla"]').attr('version') == PreUpdateChecker.joomlaTargetVersion) {
                result.state = PreUpdateChecker.STATE.COMPATIBLE;
                result.version = $(this).find('version').text();
            }
        });
        // Return compatibility = false in case no update matches
        return result;
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
                html = '<p class="label">' + COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_VERSION_MISSING + '</p>';
                break;
            default:
                html = '<p class="label">' + COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_WARNING_UNKNOWN + '</p>';
        }
        extensionData.$element.html(html);
    }
    // Run PreUpdateChecker on document ready
    $(PreUpdateChecker.run);
})(jQuery, document, window);