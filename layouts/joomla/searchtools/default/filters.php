<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$data = $displayData;

// Load the form filters
$filters = $data['view']->filterForm->getGroup('filter');

// Remove the search filter
unset($filters['filter_search']);

// Introduced 'removedFilters' (array) option that lets remove filters
if (isset($data['options']['removedFilters']))
{
	foreach ($data['options']['removedFilters'] as $removedFilters)
	{
		unset($filters[$removedFilters]);
	}
}

?>
<?php if ($filters) : ?>
	<?php foreach ($filters as $fieldName => $field) : ?>
			<div class="js-stools-field-filter">
				<?php echo $field->input; ?>
			</div>
	<?php endforeach; ?>
<?php endif; ?>
