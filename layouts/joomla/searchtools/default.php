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

// Set some basic options
$customOptions = array(
	'filtersHidden'       => empty($data['view']->activeFilters),
	'defaultLimit'        => JFactory::getApplication()->getCfg('list_limit', 20),
	'searchFieldSelector' => '#filter_search',
	'orderFieldSelector'  => '#list_fullordering'
);

$data['options'] = array_unique(array_merge($customOptions, $data['options']));

$formSelector = !empty($data['options']['formSelector']) ? $data['options']['formSelector'] : '#adminForm';

// Load search tools
JHtml::_('searchtools.form', $formSelector, $data['options']);

?>
<div class="stools js-stools clearfix">
	<div id="filter-bar" class="clearfix">
		<div class="stools-bar js-stools-bar">
			<?php echo $this->sublayout('bar', $data); ?>
		</div>
		<div class="hidden-phone hidden-tablet stools-list js-stools-container-list">
			<?php echo $this->sublayout('list', $data); ?>
		</div>
	</div>
	<!-- Filters div -->
	<div class="stools-container js-stools-container clearfix">
		<div class="stools-filters js-stools-container-filters hidden-phone">
			<?php echo $this->sublayout('filters', $data); ?>
		</div>
	</div>
</div>