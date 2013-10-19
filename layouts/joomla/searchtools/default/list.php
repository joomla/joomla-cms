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

$pagination = $data->get('pagination');

$list = $data->filterForm->getGroup('list');
?>
<?php if ($list) : ?>
	<div class="ordering-select hidden-phone">
		<?php foreach ($list as $fieldName => $field) : ?>
				<?php echo $field->input; ?>
		<?php endforeach; ?>
		<?php  /* ?>
		<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?></label>
		<?php echo $pagination->getLimitBox(); ?>
		<?php */ ?>
	</div>
<?php endif; ?>
