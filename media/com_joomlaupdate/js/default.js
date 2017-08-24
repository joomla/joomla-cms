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
        selector: '.extension-check'
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
            compatible: false
        };

        if(extension.updateUrl != undefined) {
            // TODO: set status: no update server
            callback(extension);
        }

        $.get(extension.updateUrl, function (data) {
            extension.compatible = PreUpdateChecker.parseXML(extension.version, data);
            callback(extension);
        });
    }

    PreUpdateChecker.parseXML = function (currentVersion, xml) {
        // Parse XML via browser's native parsing function and convert it to a valid jQuery object
        var $xmlDoc = $($.parseXML(xml));
        var compatible = false;
        // Iterate all updates..
        $xmlDoc.find('update').each(function() {
            // TODO: fix check, respect 3.[1234] as well
            // TODO: set state: current version compatible or update compatible
            // Check if update matches new joomla version
            if($(this).find('targetplatform[name="joomla"]').attr('version') == PreUpdateChecker.joomlaTargetVersion) {
                compatible = true;
                return;
            }
        });
        // Return compatibility = false in case no update matches
        return compatible;
    }

    PreUpdateChecker.setResultView = function (extensionData) {
        extensionData.$element.empty();
        // TODO: localize strings
        var html = '<p class=\"label label-important\">NO</p>';

        if(extensionData.compatible) {
            html = '<p class=\"label label-success\">YES</p>';
        }
        extensionData.$element.html(html);
    }
    // Run PreUpdateChecker on document ready
    $(PreUpdateChecker.run);
})(jQuery, document, window);