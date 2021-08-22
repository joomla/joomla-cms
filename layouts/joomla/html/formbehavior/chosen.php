<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Layout variables
 * ---------------------
 *
 * @var  string   $selector       The id of the field
 * @var  array    $options        The options array
 * @var  boolean  $debug          Are we in debug mode?
 */

extract($displayData);

// Include jQuery
JHtml::_('jquery.framework');
JHtml::_('script', 'jui/chosen.jquery.min.js', array('version' => 'auto', 'relative' => true, 'detectDebug' => $debug));
JHtml::_('stylesheet', 'jui/chosen.css', array('version' => 'auto', 'relative' => true));

// Options array to json options string
$options_str = json_encode($options, ($debug && defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : false));

JFactory::getDocument()->addScriptDeclaration(
	'
	jQuery(function ($) {
		initChosen();
		$("body").on("subform-row-add", initChosen);

		function initChosen(event, container)
		{
			container = container || document;
			$(container).find(' . json_encode($selector) . ').chosen(' . $options_str . ');
		}
	});
	'
);
