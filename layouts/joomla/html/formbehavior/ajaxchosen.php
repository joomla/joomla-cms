<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Layout variables
 * ---------------------
 *
 * @var  string   $selector        The id of the field
 * @var  array    $options         The options array
 * @var  boolean  $debug           Are we in debug mode?
 * @var  string   $type            Get or Post
 * @var  string   $url             The URL
 * @var  string   $dataType        Data type returned
 * @var  string   $jsonTermKey     Extra JSON terminator key
 * @var  integer  $afterTypeDelay  Delay for the execution
 * @var  integer  $minTermLength   The minimum characters required
 */

extract($displayData);

JText::script('JGLOBAL_KEEP_TYPING');
JText::script('JGLOBAL_LOOKING_FOR');

// Include jQuery
JHtml::_('jquery.framework');
JHtml::_('script', 'jui/ajax-chosen.min.js', array('version' => 'auto', 'relative' => true, 'detectDebug' => $debug));

JFactory::getDocument()->addScriptDeclaration(
	"
		jQuery(document).ready(function ($) {
			$('" . $selector . "').ajaxChosen({
				type: '" . $type . "',
				url: '" . $url . "',
				dataType: '" . $dataType . "',
				jsonTermKey: '" . $jsonTermKey . "',
				afterTypeDelay: '" . $afterTypeDelay . "',
				minTermLength: '" . $minTermLength . "'
			}, function (data) {
				var results = [];

				$.each(data, function (i, val) {
					results.push({ value: val.value, text: val.text });
				});

				return results;
			});
		});
	"
);