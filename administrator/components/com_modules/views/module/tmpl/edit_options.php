<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

	foreach ($this->fieldsets as $name => $fieldset) :

		echo '<div class="tab-pane" id="options-'.$name.'">';

		$label = !empty($fieldset->label) ? $fieldset->label : 'COM_MODULES_'.$name.'_FIELDSET_LABEL';
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
			<?php else :?>
			<?php $hidden_fields .= $field->input; ?>
			<?php endif; ?>
		<?php endforeach; ?>
		<?php echo $hidden_fields; ?>

		<?php echo '</div>'; // .tab-pane div ?>
	<?php endforeach; ?>
