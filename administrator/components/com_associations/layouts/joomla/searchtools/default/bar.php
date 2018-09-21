<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_associations
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$data = $displayData;

?>
<?php if ($data['view'] instanceof AssociationsViewAssociations) : ?>
	<?php $app = JFactory::getApplication(); ?>
	<?php // We will get the component item type and language filters & remove it from the form filters. ?>
	<?php if ($app->input->get('forcedItemType', '', 'string') == '') : ?>
		<?php $itemTypeField = $data['view']->filterForm->getField('itemtype'); ?>
		<div class="js-stools-field-filter js-stools-selector">
			<?php echo $itemTypeField->input; ?>
		</div>
	<?php endif; ?>
	<?php if ($app->input->get('forcedLanguage', '', 'cmd') == '') : ?>
		<?php $languageField = $data['view']->filterForm->getField('language'); ?>
		<div class="js-stools-field-filter js-stools-selector">
			<?php echo $languageField->input; ?>
		</div>
	<?php endif; ?>
<?php endif; ?>
<?php // Display the main joomla layout ?>
<?php echo JLayoutHelper::render('joomla.searchtools.default.bar', $data, null, array('component' => 'none')); ?>
