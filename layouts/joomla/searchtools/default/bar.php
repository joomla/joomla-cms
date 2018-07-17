<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
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
		<label for="filter_search" class="sr-only">
			<?php if (isset($filters['filter_search']->label)) : ?>
				<?php echo Text::_($filters['filter_search']->label); ?>
			<?php else : ?>
				<?php echo Text::_('JSEARCH_FILTER'); ?>
			<?php endif; ?>
		</label>
		<div class="btn-toolbar">
			<div class="btn-group mr-2">
				<div class="input-group">
					<?php echo $filters['filter_search']->input; ?>
					<span class="input-group-append">
						<button type="submit" class="btn btn-primary hasTooltip" title="<?php echo HTMLHelper::_('tooltipText', 'JSEARCH_FILTER_SUBMIT'); ?>"  aria-label="<?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?>">
							<span class="fa fa-search" aria-hidden="true"></span>
						</button>
					</span>
				</div>
			</div>
			<button type="button" class="btn btn-primary hasTooltip js-stools-btn-clear mr-2" title="<?php echo HTMLHelper::_('tooltipText', 'JSEARCH_FILTER_CLEAR'); ?>">
				<?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>
			</button>
			<div class="btn-group">
				<button type="button" class="btn btn-primary hasTooltip js-stools-btn-filter">
					<?php echo Text::_('JTABLE_OPTIONS'); ?>
					<span class="fa fa-caret-down" aria-hidden="true"></span>
				</button>
			</div>
		</div>
	<?php endif; ?>
<?php endif;
