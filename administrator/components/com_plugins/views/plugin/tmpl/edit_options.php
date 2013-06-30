<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

foreach ($this->fieldsets as $name => $fieldset) :
	$label = !empty($fieldset->label) ? JText::_($fieldset->label, true) : JText::_('COM_PLUGINS_'.$fieldset->name.'_FIELDSET_LABEL', true);
	$optionsname = 'options-' . $fieldset->name;
	echo JHtml::_('bootstrap.addTab', 'myTab', $optionsname,  $label);
	if (isset($fieldset->description) && trim($fieldset->description)) :
		echo '<p class="tip">'.$this->escape(JText::_($fieldset->description)).'</p>';
	endif;
	?>
	<?php $hidden_fields = ''; ?>
	<?php foreach ($this->form->getFieldset($name) as $field) : ?>
		<?php if (!$field->hidden) : ?>
		<div class="control-group">
			<div class="control-label">
				<?php echo $field->label; ?>
			</div>
			<div class="controls">
				<?php echo $field->input; ?>
			</div>
		</div>
		<?php else : $hidden_fields .= $field->input; ?>
		<?php endif; ?>
	<?php endforeach; ?>
	<?php echo $hidden_fields; ?>

<?php echo JHtml::_('bootstrap.endTab'); ?>

<?php endforeach; ?>
