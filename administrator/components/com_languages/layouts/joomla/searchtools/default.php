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
use Joomla\CMS\Layout\LayoutHelper;

/** @var  array  $displayData */
$data = $displayData;

// Receive overridable options
$data['options'] = !empty($data['options']) ? $data['options'] : array();

if ($data['view'] instanceof \Joomla\Component\Languages\Administrator\View\Overrides\HtmlView)
{
	// Language/Client selector doesn't have to activate the filter bar.
	unset($data['view']->activeFilters['language_client']);
}

if ($data['view'] instanceof \Joomla\Component\Languages\Administrator\View\Installed\HtmlView)
{
	// Client selector doesn't have to activate the filter bar.
	unset($data['view']->activeFilters['client_id']);
}

// Set some basic options
$customOptions = array(
	'filtersHidden'       => $data['options']['filtersHidden'] ?? empty($data['view']->activeFilters),
	'defaultLimit'        => $data['options']['defaultLimit'] ?? Factory::getApplication()->get('list_limit', 20),
	'searchFieldSelector' => '#filter_search',
	'orderFieldSelector'  => '#list_fullordering',
);

$data['options'] = array_merge($customOptions, $data['options']);

$formSelector = !empty($data['options']['formSelector']) ? $data['options']['formSelector'] : '#adminForm';

// Load search tools
HTMLHelper::_('searchtools.form', $formSelector, $data['options']);

$filtersClass = isset($data['view']->activeFilters) && $data['view']->activeFilters ? ' js-stools-container-filters-visible' : '';
?>
<div class="js-stools" role="search">
	<?php if ($data['view'] instanceof \Joomla\Component\Languages\Administrator\View\Overrides\HtmlView) : ?>
		<?php $langClient = $data['view']->filterForm->getField('language_client'); ?>
		<div class="js-stools-container-selector">
			<div class="js-stools-field-selector js-stools-langclient">
				<?php echo $langClient->input; ?>
			</div>
		</div>
	<?php endif; ?>
	<?php if ($data['view'] instanceof \Joomla\Component\Languages\Administrator\View\Installed\HtmlView) : ?>
		<?php $client = $data['view']->filterForm->getField('client_id'); ?>
		<div class="js-stools-container-selector">
			<div class="js-stools-field-selector js-stools-client">
				<?php echo $client->input; ?>
			</div>
		</div>
	<?php endif; ?>
	<div class="js-stools-container-bar">
		<?php echo LayoutHelper::render('joomla.searchtools.default.bar', $data); ?>
	</div>
	<!-- Filters div -->
	<div class="js-stools-container-filters clearfix<?php echo $filtersClass; ?>">
		<?php echo LayoutHelper::render('joomla.searchtools.default.list', $data); ?>
		<?php echo LayoutHelper::render('joomla.searchtools.default.filters', $data); ?>
	</div>
</div>
