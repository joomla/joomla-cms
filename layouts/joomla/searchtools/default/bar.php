<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\Registry\Registry;

$data = $displayData;

// Receive overridable options
$data['options'] = !empty($data['options']) ? $data['options'] : array();

if (is_array($data['options']))
{
	$data['options'] = new Registry($data['options']);
}

// Options
$filterButton = $data['options']->get('filterButton', true);
$searchButton = $data['options']->get('searchButton', true);

$filters = $data['view']->filterForm->getGroup('filter');
?>

<?php if (!empty($filters['filter_search'])) : ?>
	<?php if ($searchButton) : ?>
		<label for="filter_search" class="element-invisible">
			<?php echo JText::_('JSEARCH_FILTER'); ?>
		</label>
		<div class="btn-wrapper input-append">
			<?php echo $filters['filter_search']->input; ?>
			<button type="submit" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>">
				<i class="icon-search"></i>
			</button>
		</div>
		<?php if ($filterButton) : ?>
			<div class="btn-wrapper hidden-phone">
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
<?php endif;
