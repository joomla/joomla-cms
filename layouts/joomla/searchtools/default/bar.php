<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

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

if (empty($filters['filter_search']) || !$searchButton)
{
	return;
}
?>

<div class="filter-search-bar btn-group">
	<div class="input-group">
		<?php echo $filters['filter_search']->input; ?>
		<?php if ($filters['filter_search']->description) : ?>
		<div role="tooltip" id="<?php echo ($filters['filter_search']->id ?: $filters['filter_search']->name) . '-desc'; ?>" class="filter-search-bar__description">
			<?php echo htmlspecialchars(Text::_($filters['filter_search']->description), ENT_COMPAT, 'UTF-8'); ?>
		</div>
		<?php endif; ?>
		<span class="filter-search-bar__label visually-hidden">
			<?php echo $filters['filter_search']->label; ?>
		</span>
		<button type="submit" class="filter-search-bar__button btn btn-primary" aria-label="<?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?>">
			<span class="filter-search-bar__button-icon icon-search" aria-hidden="true"></span>
		</button>
	</div>
</div>
<div class="filter-search-actions btn-group">
	<?php if ($filterButton) : ?>
		<button type="button" class="filter-search-actions__button btn btn-primary js-stools-btn-filter">
			<?php echo Text::_('JFILTER_OPTIONS'); ?>
			<span class="icon-angle-down" aria-hidden="true"></span>
		</button>
	<?php endif; ?>
	<button type="button" class="filter-search-actions__button btn btn-primary js-stools-btn-clear">
		<?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>
	</button>
</div>
