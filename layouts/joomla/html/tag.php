<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\Registry\Registry;

/**
 * Layout variables
 * ---------------------
 *
 * @var  string   $selector       The id of the field
 * @var  string   $minTermLength  The minimum number of characters for the tag
 * @var  boolean  $allowCustom    Can we insert custom tags?
 */

extract($displayData);


// Tags field ajax
$chosenAjaxSettings = new Registry(
	array(
		'selector'      => $selector,
		'type'          => 'GET',
		'url'           => JUri::root() . 'index.php?option=com_tags&task=tags.searchAjax',
		'dataType'      => 'json',
		'jsonTermKey'   => 'like',
		'minTermLength' => $minTermLength
	)
);

JHtml::_('formbehavior.ajaxchosen', $chosenAjaxSettings);

// Allow custom values ?
if ($allowCustom)
{
	JFactory::getDocument()->addScriptDeclaration(
		"
		jQuery(document).ready(function ($) {

			var customTagPrefix = '#new#';

			// Method to add tags pressing enter
			$('" . $selector . "_chzn input').keyup(function(event) {

				// Tag is greater than the minimum required chars and enter pressed
				if (this.value && this.value.length >= " . $minTermLength . " && event.which === 13) {

					// Search an highlighted result
					var highlighted = $('" . $selector . "_chzn').find('li.active-result.highlighted').first();

					// Add the highlighted option
					if (event.which === 13 && highlighted.text() !== '')
					{
						// Extra check. If we have added a custom tag with this text remove it
						var customOptionValue = customTagPrefix + highlighted.text();
						$('" . $selector . " option').filter(function () { return $(this).val() == customOptionValue; }).remove();

						// Select the highlighted result
						var tagOption = $('" . $selector . " option').filter(function () { return $(this).html() == highlighted.text(); });
						tagOption.attr('selected', 'selected');
					}
					// Add the custom tag option
					else
					{
						var customTag = this.value;

						// Extra check. Search if the custom tag already exists (typed faster than AJAX ready)
						var tagOption = $('" . $selector . " option').filter(function () { return $(this).html() == customTag; });
						if (tagOption.text() !== '')
						{
							tagOption.attr('selected', 'selected');
						}
						else
						{
							var option = $('<option>');
							option.text(this.value).val(customTagPrefix + this.value);
							option.attr('selected','selected');

							// Append the option an repopulate the chosen field
							$('" . $selector . "').append(option);
						}
					}

					this.value = '';
					$('" . $selector . "').trigger('liszt:updated');
					event.preventDefault();

				}
			});
		});
		"
	);
}
