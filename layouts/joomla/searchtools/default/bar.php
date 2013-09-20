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

$options = array(
	'filtersApplied' => !empty($data->activeFilters)
);

// Receive
$options = new JRegistry($options);

// Add the default config limit
$options->set('defaultLimit', JFactory::getApplication()->getCfg('list_limit', 20));

$formSelector = $options->get('formSelector', '#adminForm');
$searchString = $options->get('searchString', null);

// Load the jQuery plugin && CSS
JHtml::_('script', 'jui/jquery.searchtools.js', false, true, false, false);
JHtml::_('stylesheet', 'jui/jquery.searchtools.css', false, true);

$doc = JFactory::getDocument();
$script = "
	(function($){
		$(document).ready(function() {
			$('" . $formSelector . "').searchtools(
				" . $options->toString() . "
			);
		});
	})(jQuery);
";
$doc->addScriptDeclaration($script);

// Options
$showFilterButton = $options->get('filterButton', true);
$showOrderButton  = $options->get('orderButton', true);

$filters = $data->filterForm->getGroup('filter');
?>

<?php if (isset($filters['filter_search'])) : ?>
	<div class="stools-buttons">
		<?php if ($options->get('searchButton', true)) : ?>
			<label for="filter_search" class="element-invisible">
				<?php echo JText::_('COM_CONTENT_FILTER_SEARCH_DESC'); ?>
			</label>
			<div class="btn-wrapper input-append">
				<?php echo $filters['filter_search']->input; ?>
				<button type="submit" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>">
					<i class="icon-search"></i>
				</button>
			</div>
			<?php if ($showFilterButton) : ?>
				<div class="btn-wrapper">
					<button type="button" class="btn hasTooltip js-stools-btn-filter" title="<?php echo JHtml::tooltipText('JSEARCH_TOOLS_DESC'); ?>">
						<?php echo JText::_('JSEARCH_TOOLS');?> <i class="caret"></i>
					</button>
				</div>
			<?php endif; ?>
			<div class="btn-wrapper">
				<button type="button" class="btn hasTooltip js-stools-btn-clear" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_CLEAR'); ?>">
					<?php echo JText::_('JSEARCH_FILTER_CLEAR');?>
				</button>
			</div>
		<?php endif; ?>
	</div>
<?php endif;