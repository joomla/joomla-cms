<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
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
	<?php foreach ($this->form->getFieldset('com_config') as $field) : ?>
		<div class="control-group">
			<div class="control-label">
				<?php echo $field->label; ?>
			</div>
			<div class="controls">
				<?php echo $field->input; ?>
			</div>
		</div>
	<?php endforeach; ?>
	</fieldset>
<?php else : ?>
	<?php // Fall-back to display all in params ?>
	<?php foreach ($fieldSets as $name => $fieldSet) : ?>
	<?php $label = !empty($fieldSet->label) ? $fieldSet->label : 'COM_CONFIG_' . $name . '_FIELDSET_LABEL'; ?>
	<?php if (isset($fieldSet->description) && trim($fieldSet->description)) : ?>
		<?php echo '<p class="tip">' . $this->escape(JText::_($fieldSet->description)) . '</p>'; ?>
	<?php endif; ?>
	<fieldset class="form-horizontal">
		<?php foreach ($this->form->getFieldset($name) as $field) : ?>
			<div class="control-group">
				<div class="control-label">
					<?php echo $field->label; ?>
				</div>
				<div class="controls">
					<?php echo $field->input; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</fieldset>
	<?php endforeach; ?>
<?php endif; ?>
