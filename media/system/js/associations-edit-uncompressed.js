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
 * @since    3.7.0
 */

window.hideAssociation = function(formControl, languageCode)
{
	jQuery('#associations .control-group').each(function()
	{
		// Current selected language. Hide it.
		if (jQuery(this).find('.control-label label').attr('for').replace('_id', '') == formControl + '_associations_' + languageCode.replace('-', '_'))
		{
			jQuery(this).hide();
		}
	});
}

window.showAssociationMessage = function()
{
	jQuery('#associations .control-group').hide();
	jQuery('#associations').prepend('<div id="associations-notice" class="alert alert-info">' + Joomla.JText._('JGLOBAL_ASSOC_NOT_POSSIBLE') + '</div>');
}

!(function()
{
	jQuery(document).ready(function($)
	{	
		var associationsEditOptions = Joomla.getOptions('system.associations.edit'), formControl = associationsEditOptions.formControl || 'jform';

		// Hide the associations tab if needed.
		if (associationsEditOptions.hidden == 1)
		{
			window.showAssociationMessage();
		}
		// Hide only the associations for the current language.
		else
		{
			window.hideAssociation(formControl, $('#' + formControl + '_language').val());
		}

		// When changing the language.
		$('#' + formControl + '_language').on('change', function(event)
		{
			// Remove message if any.
			Joomla.removeMessages();
			$('#associations-notice').remove();

			var existsAssociations = false;

			// For each language, remove the associations, ie, empty the associations fields and reset the buttons to Select/Create.
			$('#associations .control-group').each(function()
			{
				var languageCode = $(this).find('.control-label label').attr('for').replace('_id', '').replace('jform_associations_', '');

				// Show the association fields.
				$(this).show();

				// Check if there was an association selected for this language.
				if (!existsAssociations && $('#' + formControl + '_associations_' + languageCode + '_id').val() !== '')
				{
					existsAssociations = true;
				}

				// Call the modal clear button.
				$('#' + formControl + '_associations_' + languageCode + '_clear').click();
			});

			// If associations existed, send a warning to the user.
			if (existsAssociations)
			{
				Joomla.renderMessages({warning: [Joomla.JText._('JGLOBAL_ASSOCIATIONS_RESET_WARNING')]});
			}

			var selectedLanguage = $(this).val();

			// If the selected language is All hide the fields and add a message.
			if (selectedLanguage == '*')
			{
				window.showAssociationMessage();
			}
			// Else show the associations fields/buttons and hide the current selected language.
			else
			{
				window.hideAssociation(formControl, selectedLanguage);
			}
		});
	});
})(window, document, Joomla);
