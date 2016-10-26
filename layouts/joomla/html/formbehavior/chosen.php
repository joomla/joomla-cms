<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

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
JHtml::_('script', 'jui/chosen.jquery.min.js', false, true, false, false, $debug);
JHtml::_('stylesheet', 'jui/chosen.css', false, true);

// Options array to json options string
$options_str = json_encode($options, ($debug && defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : false));


JFactory::getDocument()->addScriptDeclaration(
	"
		jQuery(document).ready(function (){
			jQuery('" . $selector . "').chosen(" . $options_str . ");
		});
	"
);