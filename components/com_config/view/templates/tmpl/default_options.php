<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Load chosen.css
JHtml::_('formbehavior.chosen', 'select');

$fieldSets = $this->form->getFieldsets('params');

?>
<legend><?php echo JText::_('COM_CONFIG_TEMPLATE_SETTINGS'); ?></legend>
<?php // Search for com_config field set ?>
<?php if (!empty($fieldSets['com_config'])) : ?>
	<fieldset class="form-horizontal">
		<?php echo $this->form->renderFieldset('com_config'); ?>
	</fieldset>
<?php else : ?>
	<?php // Fall-back to display all in params ?>
	<?php foreach ($fieldSets as $name => $fieldSet) : ?>
		<?php $label = !empty($fieldSet->label) ? $fieldSet->label : 'COM_CONFIG_' . $name . '_FIELDSET_LABEL'; ?>
		<?php if (isset($fieldSet->description) && trim($fieldSet->description)) : ?>
			<p class="tip">
				<?php echo $this->escape(JText::_($fieldSet->description)); ?>
			</p>
		<?php endif; ?>
		<fieldset class="form-horizontal">
			<?php echo $this->form->renderFieldset($name); ?>
		</fieldset>
	<?php endforeach; ?>
<?php endif; ?>
