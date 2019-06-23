<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

/** @var  array  $displayData */
$data = $displayData;

// Receive overridable options
$data['options'] = !empty($data['options']) ? $data['options'] : array();
$showSelector    = false;

if ($data['view'] instanceof \Joomla\Component\Associations\Administrator\View\Associations\HtmlView)
{
	// Client selector doesn't have to activate the filter bar.
	unset($data['view']->activeFilters['itemtype']);

	// Menutype filter doesn't have to activate the filter bar
	unset($data['view']->activeFilters['language']);
}

// Set some basic options
$customOptions = array(
	'filtersHidden'       => $data['options']['filtersHidden'] ?? empty($data['view']->activeFilters),
	'defaultLimit'        => $data['options']['defaultLimit'] ?? Factory::getApplication()->get('list_limit', 20),
	'searchFieldSelector' => '#filter_search',
	'orderFieldSelector'  => '#list_fullordering',
	'formSelector'        => !empty($data['options']['formSelector']) ? $data['options']['formSelector'] : '#adminForm',
);

$data['options'] = array_merge($customOptions, $data['options']);

// Load search tools
HTMLHelper::_('searchtools.form', $data['options']['formSelector'], $data['options']);

$filtersClass = isset($data['view']->activeFilters) && $data['view']->activeFilters ? ' js-stools-container-filters-visible' : '';
?>
<div class="js-stools" role="search">
	<?php // Add the itemtype and language selectors before the form filters. Do not display in modal. ?>
	<?php $app = Factory::getApplication(); ?>
	<?php if ($app->input->get('forcedItemType', '', 'string') == '') : ?>
		<?php $itemTypeField = $data['view']->filterForm->getField('itemtype'); ?>
		<div class="js-stools-container-selector-first">
			<div class="js-stools-field-selector js-stools-itemtype">
				<?php echo $itemTypeField->input; ?>
			</div>
		</div>
	<?php endif; ?>
	<?php if ($app->input->get('forcedLanguage', '', 'cmd') == '') : ?>
		<?php $languageField = $data['view']->filterForm->getField('language'); ?>
		<div class="js-stools-container-selector">
			<div class="js-stools-field-selector js-stools-language">
				<?php echo $languageField->input; ?>
			</div>
		</div>
	<?php endif; ?>

	<div class="js-stools-container-bar">
		<?php echo $this->sublayout('bar', $data); ?>
	</div>

	<!-- Filters div -->
	<div class="js-stools-container-filters clearfix<?php echo $filtersClass; ?>">
		<?php echo $this->sublayout('list', $data); ?>
		<?php echo $this->sublayout('filters', $data); ?>
	</div>
</div>
