<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$data = $displayData;

// Receive overridable options
$data['options'] = !empty($data['options']) ? $data['options'] : array();

if ($data['view'] instanceof LanguagesViewInstalled)
{
	JFactory::getDocument()->addStyleDeclaration('
		/* Fixed filter field in search bar */
		.js-stools .js-stools-client_id {
			float: left;
			margin-right: 10px;
			min-width: 220px;
		}
		html[dir=rtl] .js-stools .js-stools-client_id {
			float: right;
			margin-left: 10px
			margin-right: 0;
		}
		.js-stools .js-stools-container-bar .js-stools-field-filter .chzn-container {
			padding: 3px 0;
		}');

	// Client id filter doesn't have to activate the filter bar
	unset($data['view']->activeFilters['client_id']);
}

// Display the main joomla layout
echo JLayoutHelper::render('joomla.searchtools.default', $data, null, array('component' => 'none'));
