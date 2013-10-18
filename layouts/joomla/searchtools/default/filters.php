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

// Is multilanguage enabled?
$langs = isset(JFactory::getApplication()->languages_enabled);

$filters = $data->filterForm->getGroup('filter');
?>
<?php if ($filters) : ?>
	<div class="filter-select hidden-phone">
		<?php foreach ($filters as $fieldName => $field) : ?>
			<?php if ($fieldName != 'filter_search') : ?>
				<?php echo $field->input; ?>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
