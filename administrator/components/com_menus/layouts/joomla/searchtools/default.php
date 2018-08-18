<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/** @var  array  $displayData */
$data = $displayData;

// Receive overridable options
$data['options'] = !empty($data['options']) ? $data['options'] : array();

if ($data['view'] instanceof \Joomla\Component\Menus\Administrator\View\Items\HtmlView
	|| $data['view'] instanceof \Joomla\Component\Menus\Administrator\View\Menus\HtmlView)
{
	// Client selector doesn't have to activate the filter bar.
	unset($data['view']->activeFilters['client_id']);

	// Menutype filter doesn't have to activate the filter bar
	unset($data['view']->activeFilters['menutype']);
}

// Set some basic options
$customOptions = array(
	'filtersHidden'       => $data['options']['filtersHidden'] ?? empty($data['view']->activeFilters),
	'defaultLimit'        => $data['options']['defaultLimit'] ?? JFactory::getApplication()->get('list_limit', 20),
	'searchFieldSelector' => '#filter_search',
	'orderFieldSelector'  => '#list_fullordering',
	'totalResults'        => $data['options']['totalResults'] ?? -1,
	'noResultsText'       => $data['options']['noResultsText'] ?? JText::_('JGLOBAL_NO_MATCHING_RESULTS'),
);

$data['options'] = array_merge($customOptions, $data['options']);

$formSelector = !empty($data['options']['formSelector']) ? $data['options']['formSelector'] : '#adminForm';

// Load search tools
JHtml::_('searchtools.form', $formSelector, $data['options']);

$filtersClass = isset($data['view']->activeFilters) && $data['view']->activeFilters ? ' js-stools-container-filters-visible' : '';
?>
<div class="js-stools clearfix">
	<div class="clearfix">
		<?php
		if ($data['view'] instanceof \Joomla\Component\Menus\Administrator\View\Items\HtmlView)
		{
			// We will get the menutype filter & remove it from the form filters
			$menuTypeField = $data['view']->filterForm->getField('menutype');

			// Add the client selector before the form filters.
			$clientIdField = $data['view']->filterForm->getField('client_id');

			if ($clientIdField): ?>
				<div class="js-stools-container-selector">
					<div class="js-stools-field-selector js-stools-client_id">
						<?php echo $clientIdField->input; ?>
					</div>
				</div>
			<?php endif; ?>
			<div class="js-stools-container-selector">
				<div class="js-stools-field-selector js-stools-menutype">
					<?php echo $menuTypeField->input; ?>
				</div>
			</div>
			<?php
		}
		elseif ($data['view'] instanceof \Joomla\Component\Menus\Administrator\View\Menus\HtmlView)
		{
			// Add the client selector before the form filters.
			$clientIdField = $data['view']->filterForm->getField('client_id');
			?>
			<div class="js-stools-container-selector">
				<div class="js-stools-field-selector js-stools-client_id">
					<?php echo $clientIdField->input; ?>
				</div>
			</div>
			<?php
		}
		?>
		<div class="js-stools-container-bar">
			<?php echo JLayoutHelper::render('joomla.searchtools.default.bar', $data); ?>
		</div>
	</div>
	<!-- Filters div -->
	<div class="js-stools-container-filters clearfix<?php echo $filtersClass; ?>">
		<?php echo JLayoutHelper::render('joomla.searchtools.default.list', $data); ?>
		<?php echo JLayoutHelper::render('joomla.searchtools.default.filters', $data); ?>
	</div>
</div>
<?php if ($data['options']['totalResults'] === 0) : ?>
	<?php echo JLayoutHelper::render('joomla.searchtools.default.noitems', $data); ?>
<?php endif; ?>
