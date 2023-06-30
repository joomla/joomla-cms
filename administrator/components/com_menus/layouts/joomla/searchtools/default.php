<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

$data = $displayData;

// Receive overridable options
$data['options'] = !empty($data['options']) ? $data['options'] : [];

$noResultsText     = '';
$hideActiveFilters = false;
$showFilterButton  = false;
$showSelector      = false;
$selectorFieldName = $data['options']['selectorFieldName'] ?? 'client_id';

// If a filter form exists.
if (isset($data['view']->filterForm) && !empty($data['view']->filterForm)) {
    // Checks if a selector (e.g. client_id) exists.
    if ($selectorField = $data['view']->filterForm->getField($selectorFieldName)) {
        $showSelector = $selectorField->getAttribute('filtermode', '') === 'selector' ? true : $showSelector;

        // Checks if a selector should be shown in the current layout.
        if (isset($data['view']->layout)) {
            $showSelector = $selectorField->getAttribute('layout', 'default') != $data['view']->layout ? false : $showSelector;
        }

        // Unset the selector field from active filters group.
        unset($data['view']->activeFilters[$selectorFieldName]);
    }

    if ($data['view'] instanceof \Joomla\Component\Menus\Administrator\View\Items\HtmlView) :
        unset($data['view']->activeFilters['client_id']);
    endif;

    // Checks if the filters button should exist.
    $filters = $data['view']->filterForm->getGroup('filter');
    $showFilterButton = isset($filters['filter_search']) && count($filters) === 1 ? false : true;

    // Checks if it should show the be hidden.
    $hideActiveFilters = empty($data['view']->activeFilters);

    // Check if the no results message should appear.
    if (isset($data['view']->total) && (int) $data['view']->total === 0) {
        $noResults = $data['view']->filterForm->getFieldAttribute('search', 'noresults', '', 'filter');
        if (!empty($noResults)) {
            $noResultsText = Text::_($noResults);
        }
    }
}

// Set some basic options.
$customOptions = [
    'filtersHidden'       => isset($data['options']['filtersHidden']) && $data['options']['filtersHidden'] ? $data['options']['filtersHidden'] : $hideActiveFilters,
    'filterButton'        => isset($data['options']['filterButton']) && $data['options']['filterButton'] ? $data['options']['filterButton'] : $showFilterButton,
    'defaultLimit'        => $data['options']['defaultLimit'] ?? Factory::getApplication()->get('list_limit', 20),
    'searchFieldSelector' => '#filter_search',
    'selectorFieldName'   => $selectorFieldName,
    'showSelector'        => $showSelector,
    'orderFieldSelector'  => '#list_fullordering',
    'showNoResults'       => !empty($noResultsText),
    'noResultsText'       => !empty($noResultsText) ? $noResultsText : '',
    'formSelector'        => !empty($data['options']['formSelector']) ? $data['options']['formSelector'] : '#adminForm',
];

// Merge custom options in the options array.
$data['options'] = array_merge($customOptions, $data['options']);

// Add class to hide the active filters if needed.
$filtersActiveClass = $hideActiveFilters ? '' : ' js-stools-container-filters-visible';

// Load search tools
HTMLHelper::_('searchtools.form', $data['options']['formSelector'], $data['options']);
?>
<div class="js-stools" role="search">
    <?php if ($data['view'] instanceof \Joomla\Component\Menus\Administrator\View\Items\HtmlView) : ?>
        <?php // Add the itemtype and language selectors before the form filters. Do not display in modal.?>
        <?php $app = Factory::getApplication(); ?>
        <?php $clientIdField = $data['view']->filterForm->getField('client_id'); ?>
        <?php if ($clientIdField) : ?>
        <div class="js-stools-container-selector">
            <div class="visually-hidden">
                <?php echo $clientIdField->label; ?>
            </div>
            <div class="js-stools-field-selector js-stools-client_id">
                <?php echo $clientIdField->input; ?>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>
    <?php if ($data['options']['showSelector']) : ?>
    <div class="js-stools-container-selector">
        <?php echo LayoutHelper::render('joomla.searchtools.default.selector', $data); ?>
    </div>
    <?php endif; ?>
    <div class="js-stools-container-bar ms-auto">
        <div class="btn-toolbar">
            <?php echo $this->sublayout('bar', $data); ?>
            <?php echo $this->sublayout('list', $data); ?>
        </div>
    </div>
    <!-- Filters div -->
    <div class="js-stools-container-filters clearfix<?php echo $filtersActiveClass; ?>">
        <?php if ($data['options']['filterButton']) : ?>
            <?php echo $this->sublayout('filters', $data); ?>
        <?php endif; ?>
    </div>
</div>
<?php if ($data['options']['showNoResults']) : ?>
    <?php echo $this->sublayout('noitems', $data); ?>
<?php endif; ?>
