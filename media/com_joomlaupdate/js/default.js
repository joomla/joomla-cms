function extractionMethodHandler(target, prefix)
{
	jQuery(function ($) {
		$em = $(target);
		displayStyle = ($em.val() === 'direct') ? 'none' : 'table-row';

		document.getElementById(prefix + '_notice').style.display = displayStyle;
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

		$('.settingstoggle').css('float', 'right').css('cursor', 'pointer');
		$('.settingstoggle').on('click', function(toggle, index)
		{
			var settingsfieldset = $(this).closest('fieldset');
			if($(this).data('state') == 'closed')
			{
				$(this).data('state', 'open');
				$(this).html( COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS_SHOW_LESS_COMPATIBILITY_INFORMATION);
				settingsfieldset.find('.settingsInfo').removeClass('hidden');
			}
			else
			{
				$(this).data('state', 'closed');
				$(this).html( COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS_SHOW_MORE_COMPATIBILITY_INFORMATION);
				settingsfieldset.find('.settingsInfo').addClass('hidden');
			}
		});

		PreUpdateChecker.nonCoreCriticalPlugins = Joomla.getOptions('nonCoreCriticalPlugins', []);

		// If there are no non Core Critical Plugins installed then disable the warnings upfront
		if (PreUpdateChecker.nonCoreCriticalPlugins.length === 0)
		{
			$('#preupdateCheckWarning, #preupdateconfirmation, #preupdatecheckbox, #preupdatecheckheadings').css('display', 'none');
			$('#preupdatecheckbox #noncoreplugins').prop('checked', true);
			$('button.submitupdate').removeClass('disabled');
			$('button.submitupdate').prop('disabled', false);
		}

		// Grab all extensions based on the selector set in the config object
		var $extensions = $(PreUpdateChecker.config.selector);

		// If there are no extensions to be checked we can exit here
		if ($extensions.length === 0)
		{
			return;
		}

		$('#preupdatecheckbox #noncoreplugins').on('change', function ()
		{
			if ($('#preupdatecheckbox #noncoreplugins').is(':checked')) {
				if (confirm(Joomla.JText._('COM_JOOMLAUPDATE_VIEW_DEFAULT_POTENTIALLY_DANGEROUS_PLUGIN_CONFIRM_MESSAGE'))) {
					$('button.submitupdate').removeClass('disabled');
					$('button.submitupdate').prop('disabled', false);
				}
				else
				{
					$('#preupdatecheckbox #noncoreplugins').prop('checked', false);
				}
			} else {
				$('button.submitupdate').addClass('disabled');
				$('button.submitupdate').prop('disabled', true);
			}

		});

		// Get version of the available joomla update
        PreUpdateChecker.joomlaTargetVersion = window.joomlaTargetVersion;
        PreUpdateChecker.joomlaCurrentVersion = window.joomlaCurrentVersion;

		// No point creating and loading a component stylesheet for 4 settings
		$('.compatibilitytypes img').css('height', '20px');
		$('.compatibilitytypes').css('display', 'none').css('margin-left', 0);
		// The currently processing line should show until it’s finished
		$('#compatibilitytype0').css('display', 'block');
		$('.compatibilitytoggle').css('float', 'right').css('cursor', 'pointer');

		$('.compatibilitytoggle').on('click', function(toggle, index)
		{
			var compatibilitytypes = $(this).closest('fieldset.compatibilitytypes');
			if($(this).data('state') == 'closed')
			{
				$(this).data('state', 'open');
				$(this).html( COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS_SHOW_LESS_COMPATIBILITY_INFORMATION);
				compatibilitytypes.find('.exname').removeClass('span8').addClass('span4');
				compatibilitytypes.find('.extype').removeClass('span4').addClass('span1');
				compatibilitytypes.find('.upcomp').removeClass('hidden').addClass('span3');
				compatibilitytypes.find('.currcomp').removeClass('hidden').addClass('span3');
				compatibilitytypes.find('.instver').removeClass('hidden').addClass('span1');

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
				$(this).html( COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS_SHOW_MORE_COMPATIBILITY_INFORMATION);
				compatibilitytypes.find('.exname').addClass('span8').removeClass('span4');
				compatibilitytypes.find('.extype').addClass('span4').removeClass('span1');
				compatibilitytypes.find('.upcomp').addClass('hidden').removeClass('span3');
				compatibilitytypes.find('.currcomp').addClass('hidden').removeClass('span3');
				compatibilitytypes.find('.instver').addClass('hidden').removeClass('span1');

				compatibilitytypes.find("#updateyellowwarning").addClass('hidden');
				compatibilitytypes.find("#updateorangewarning").addClass('hidden');
			}
		});

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
			// An error occurred -> show unknown error note
			html = Joomla.JText._('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_SERVER_ERROR');
			// force result into group 4 = Pre update checks failed
			extensionData.compatibilityData = {
				'resultGroup' : 4
			}
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
		var extensionId = extensionData.$element.data('extensionId')
		document.getElementById('available-version-' + extensionId ).innerHTML = html;

		extensionData.$element.closest('tr').appendTo($('#compatibilitytype' + extensionData.compatibilityData.resultGroup + ' tbody'));
		$('#compatibilitytype' + extensionData.compatibilityData.resultGroup).css('display', 'block');

		document.getElementById('compatibilitytype0').style.display = 'block';

		// Process the nonCoreCriticalPlugin list
		if (extensionData.compatibilityData.resultGroup === 3)
		{
			var pluginInfo;

			for (var i = PreUpdateChecker.nonCoreCriticalPlugins.length - 1; i >= 0; i--)
			{
			  pluginInfo = PreUpdateChecker.nonCoreCriticalPlugins[i];

			  if (pluginInfo.package_id == extensionId || pluginInfo.extension_id == extensionId)
			  {
				$('#plg_' + pluginInfo.extension_id).remove();
				PreUpdateChecker.nonCoreCriticalPlugins.splice(i, 1);
			  }
			}
		}

		// Have we finished running through the potentially critical plugins - if so we can hide the warning before all the checks are completed
		if ($('#preupdatecheckheadings table td').length == 0) {
			$('#preupdatecheckheadings').css('display', 'none');
		}

		// Have we finished?
		if ($('#compatibilitytype0 tbody td').length == 0) {
			$('#compatibilitytype0').css('display', 'none');
			for (var cpi in PreUpdateChecker.nonCoreCriticalPlugins)
			{
				var problemPluginRow = $('td[data-extension-id=' + PreUpdateChecker.nonCoreCriticalPlugins[cpi].extension_id +']');
				if (!problemPluginRow.length)
				{
					problemPluginRow = $('td[data-extension-id=' + PreUpdateChecker.nonCoreCriticalPlugins[cpi].package_id +']');
				}
				if (problemPluginRow.length)
				{
					var tableRow = problemPluginRow.closest('tr');
					tableRow.addClass('error');
					var pluginTitleTableCell = tableRow.find('td:first-child');
					pluginTitleTableCell.html(pluginTitleTableCell.html()
						+ '<span class="label label-warning " >'
						+ '<span class="icon-warning"></span>'
						+ Joomla.JText._('COM_JOOMLAUPDATE_VIEW_DEFAULT_POTENTIALLY_DANGEROUS_PLUGIN')
						+ '</span>'

						+ '<span class="label label-info hasPopover" '
						+ ' title="' + Joomla.JText._('COM_JOOMLAUPDATE_VIEW_DEFAULT_POTENTIALLY_DANGEROUS_PLUGIN') +'"'
						+ ' data-content="' + Joomla.JText._('COM_JOOMLAUPDATE_VIEW_DEFAULT_POTENTIALLY_DANGEROUS_PLUGIN_DESC')  +'"'
						+ '>'
						+ '<span class="icon-help"></span>'
						+ Joomla.JText._('COM_JOOMLAUPDATE_VIEW_DEFAULT_HELP')
						+ '</span>'
					);
					var popoverElement = pluginTitleTableCell.find('.hasPopover');
					popoverElement.css('cursor', 'pointer')
					popoverElement.popover({"placement": "top","trigger": "focus click"});
				}
			}
			if (PreUpdateChecker.nonCoreCriticalPlugins.length == 0)
			{
				$('#preupdateCheckWarning, #preupdateconfirmation, #preupdatecheckbox, #preupdatecheckheadings').css('display', 'none');
				$('#preupdatecheckbox #noncoreplugins').prop('checked', true);
				$('button.submitupdate').removeClass('disabled');
				$('button.submitupdate').prop('disabled', false);
			}
			else {
				$('#preupdateCheckWarning').addClass('hidden');
				$('#preupdateCheckCompleteProblems').removeClass('hidden');
				$('#preupdateconfirmation .preupdateconfirmation_label h3').html(Joomla.JText._('COM_JOOMLAUPDATE_VIEW_DEFAULT_POTENTIALLY_DANGEROUS_PLUGIN_LIST'))
				$('#preupdateconfirmation .preupdateconfirmation_label').removeClass('label-warning').addClass('label-important')
			}
		}
    }

    // Run PreUpdateChecker on document ready
	document.addEventListener( "DOMContentLoaded", function() {
		$(PreUpdateChecker.run);
	} );
})(jQuery, document, window);
