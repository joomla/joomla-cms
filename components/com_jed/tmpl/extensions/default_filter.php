<?php

/**
 * @package    JED
 *
 * @copyright  (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

$data = $displayData;

// Receive overridable options
$data['options'] = !empty($data['options']) ? $data['options'] : array();

// Check if any filter field has been filled
$filters       = false;
$filtered      = false;
$search_filter = false;

if (isset($data['view']->filterForm)) {
    $filters = $data['view']->filterForm->getGroup('filter');
}

// Check if there are filters set.
if ($filters !== false) {
    $filterFields = array_keys($filters);
    $filled       = false;

    foreach ($filterFields as $filterField) {
        $filterField = substr($filterField, 7);
        $filter      = $data['view']->getState('filter.' . $filterField);

        if (!empty($filter)) {
            $filled = $filter;
        }

        if (!empty($filled)) {
            $filtered = true;
            break;
        }
    }

    $search_filter = $filters['filter_search'];
    unset($filters['filter_search']);
}

$options = $data['options'];

// Set some basic options
try {
    $customOptions = array(
        'filtersHidden'       => $options['filtersHidden'] ?? empty($data['view']->activeFilters) && !$filtered,
        'defaultLimit'        => $options['defaultLimit'] ?? Factory::getApplication()->get('list_limit', 20),
        'searchFieldSelector' => '#filter_search',
        'orderFieldSelector'  => '#list_fullordering'
    );
} catch (Exception $e) {
}

$data['options'] = array_unique(array_merge($customOptions, $data['options']));

$formSelector = !empty($data['options']['formSelector']) ? $data['options']['formSelector'] : '#adminForm';

// Load search tools
HTMLHelper::_('searchtools.form', $formSelector, $data['options']);
?>

<div class="js-stools clearfix">
    <div class="clearfix">
        <div class="js-stools-container-bar">
            <label for="filter_search" class="element-invisible"
                aria-invalid="false"><?php echo Text::_('JSEARCH_FILTER'); ?></label>
                <div class="input-group mb-3">
                    <?php echo $search_filter->input; ?>
                    <div class="input-group-append">
                        <button class="btn btn-outline-success hasTooltip" title=""
                    data-original-title="<?php echo Text::_('JSEARCH_FILTER'); ?>">
                            <i class="icon-search"></i>
                        </button>
                        <?php if ($filters) : ?>
                        <button type="button" class="btn hasTooltip btn-outline-info dropdown-toggle js-stools-btn-filter" title=""
                            data-original-title="<?php echo Text::_('JSEARCH_TOOLS_DESC'); ?>">
                            <?php echo Text::_('JSEARCH_TOOLS'); ?>
                        </button>
                        <?php endif; ?>
                        <button type="button" class="btn btn-outline-primary hasTooltip js-stools-btn-clear" title=""
                            data-original-title="<?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>"
                            onclick="jQuery(this).closest('form').find('input').val('');">
                            <?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>
                        </button>
                    </div>
                </div>
        </div>
    </div>
    <!-- Filters div -->
    <div class="js-stools-container-filters hidden-phone clearfix" style="">
        <?php // Load the form filters ?>
        <?php if ($filters) : ?>
            <?php foreach ($filters as $fieldName => $field) : ?>
                <?php if ($fieldName != 'filter_search') : ?>
                    <div class="js-stools-field-filter">
                        <?php echo $field->renderField(array('hiddenLabel' => true)); ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
