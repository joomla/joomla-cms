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
            compatible: false,
            error: false
        };

        if(extension.updateUrl == undefined) {
            extension.error = true;
            callback(extension);
        }

        $.get('index.php?option=com_joomlaupdate&task=update.fetchurl&url=' + extension.updateUrl, function (data) {
            extension.compatible = PreUpdateChecker.parseXML(extension.version, data);
            callback(extension);
        }).fail(function() {
            extension.error = true;
            callback(extension);
        });
    }

    PreUpdateChecker.parseXML = function (currentVersion, xml) {
        var compatible = false;
        // Parse XML via browser's native parsing function and convert it to a valid jQuery object
        try {
            var $xmlDoc = $($.parseXML(xml));
        } catch(e) {
            return compatible;
        }
        
        // Iterate all updates..
        $xmlDoc.find('update').each(function() {
            // TODO: fix check, respect 3.[1234] as well
            // TODO: set state: current version compatible or update compatible
            // TODO: check all updates, don't break loop. Chose oldest update
            // Check if update matches new joomla version
            if($(this).find('targetplatform[name="joomla"]').attr('version') == PreUpdateChecker.joomlaTargetVersion) {
                compatible = $(this).find('version').text();
                return;
            }
        });
        // Return compatibility = false in case no update matches
        return compatible;
    }

    PreUpdateChecker.setResultView = function (extensionData) {
        extensionData.$element.empty();
        // TODO: localize strings
        var html = '<p class="label label-important">NO</p>';
        if(extensionData.error) {
            html = '<p class="label">Error</p>';
        }
        if(extensionData.compatible) {
            // TODO: distinct, current version compatible and future version compatible
            html = '<p class="label label-success">YES (' + extensionData.compatible + ')</p>';
        }
        extensionData.$element.html(html);
    }
    // Run PreUpdateChecker on document ready
    $(PreUpdateChecker.run);
})(jQuery, document, window);