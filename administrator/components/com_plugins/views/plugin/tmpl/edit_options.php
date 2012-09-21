<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$fieldsets = $this->form->getFieldsets('params');
?>
<?php foreach ($fieldsets as $name => $fieldset) : ?>
	<?php if (!in_array($fieldset->name, array('description', 'basic'))) : ?>
		<div class="tab-pane" id="options-<?php echo $name; ?>">
			<?php $label = !empty($fieldset->label) ? $fieldset->label : 'COM_PLUGINS_' . $name . '_FIELDSET_LABEL'; ?>
			<?php if (isset($fieldset->description) && trim($fieldset->description)) : ?>
				<p class="tip"><?php echo $this->escape(JText::_($fieldset->description)); ?></p>
			<?php endif; ?>
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
				<?php else : ?>
					<?php $hidden_fields .= $field->input; ?>
				<?php endif; ?>
			<?php endforeach; ?>
			<?php echo $hidden_fields; ?>
		</div>
	<?php endif; ?>
<?php endforeach; ?>
