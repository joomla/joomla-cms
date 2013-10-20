<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$data = $displayData;

// Receive overridable options
$data['options'] = !empty($data['options']) ? $data['options'] : array();

$doc = JFactory::getDocument();

$doc->addStyleDeclaration("
	/* Fixed filter field in search bar */
	.js-stools .js-stools-menutype {
		float: left;
		margin-right: 10px;
	}
	.js-stools .js-stools-container-bar .js-stools-field-filter .chzn-container {
		padding: 3px 0;
	}
");

// Menutype filter doesn't have to activate the filter bar
unset($data['view']->activeFilters['menutype']);

// We will get the menutype filter & remove it from the form filters
$filters = $data['view']->filterForm->getGroup('filter');
$data['menutype'] = $filters['filter_menutype'];
$data['view']->filterForm->removeField('menutype', 'filter');

// Display the main joomla layout
echo JLayoutHelper::render('joomla.searchtools.default', $data, null, array('component' => 'none'));
