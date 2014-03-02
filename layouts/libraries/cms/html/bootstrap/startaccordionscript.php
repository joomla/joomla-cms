<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$selector = empty($displayData['selector']) ? '' : $displayData['selector'];

// Setup options object
$opt['parent'] = isset($displayData['params']['parent']) ? (boolean) $displayData['params']['parent'] : false;
$opt['toggle'] = isset($displayData['params']['toggle']) ? (boolean) $displayData['params']['toggle'] : true;
$opt['active'] = isset($displayData['params']['active']) ? (string) $displayData['params']['active'] : '';

$options = JHtml::getJSObject($opt);

// Include Bootstrap JS Framework
JHtml::_('bootstrap.framework');

echo "(function($){
		$('#$selector').collapse($options);
	})(jQuery);"
