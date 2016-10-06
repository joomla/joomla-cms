/**
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Edit Associations javascript behavior
 *
 * Used for editing associations in the backend.
 *
 * @package  Joomla
 * @since    __DEPLOY_VERSION__
 */

window.hideAssociation = function(formControl, languageCode)
{
	jQuery('#associations .control-group').each(function()
	{
		// Current selected language. Hide it.
		if (jQuery(this).find('.control-label label').attr('id') == formControl + '_associations_' + languageCode.replace('-', '_') + '_id-lbl')
		{
			jQuery(this).hide();
		}
	});
}

window.showTab = function(tabid)
{
	jQuery('a[data-toggle="tab"][href="#' + tabid + '"]').show();
	jQuery('#' + tabid + '').show();
}

window.hideTab = function(tabid)
{
	jQuery('a[data-toggle="tab"][href="#' + tabid + '"]').hide();
	jQuery('#' + tabid + '').hide();
}

!(function()
{
	jQuery(document).ready(function($)
	{	
		var associationsEditOptions = Joomla.getOptions('system.associations.edit'), formControl = associationsEditOptions.formControl || 'jform';
		// Hide the associations tab if needed.
		if (associationsEditOptions.hidden == 1)
		{
			window.hideTab('associations');
		}
		// Hide only the associations for the current language.
		else
		{
			window.hideAssociation(formControl, jQuery('#' + formControl + '_language').val());
		}

		if (associationsEditOptions.disabled != 1)
		{
			// When changing the language.
			$('#' + formControl + '_language').on('change', function(event)
			{
				// For each language, remove the associations, ie, empty the associations fields and reset the buttons to Select/Create.
				$('#associations .control-group').each(function()
				{
					// Show the association fields.
					$(this).show();

					// Call the modal clear button.
					$('#' + $(this).find('.control-label label').attr('id').replace('_id-lbl', '') + '_clear').click();
				});

				var selectedLanguage = $(this).val();

				// If the selected language is All hide the associations fields/buttons ans reset the buttons.
				if (selectedLanguage == '*')
				{
					window.hideTab('associations');
				}
				// Else show the associations fields/buttons and hide the current selected language.
				else
				{
					window.hideAssociation(formControl, selectedLanguage);
					window.showTab('associations');
				}
			});
		}
	});
})(window, document, Joomla);
