<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die; ?>

<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('created_user_id'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('created_user_id'); ?>
	</div>
</div>

<?php if (intval($this->item->created_time)) : ?>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('created_time'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('created_time'); ?>
		</div>
	</div>
<?php endif; ?>

<?php if ($this->item->modified_user_id) : ?>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('modified_user_id'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('modified_user_id'); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('modified_time'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('modified_time'); ?>
		</div>
	</div>
<?php endif; ?>

<?php $fieldSets = $this->form->getFieldsets('params');

foreach ($fieldSets as $name => $fieldSet) :
	$label = !empty($fieldSet->label) ? $fieldSet->label : 'COM_CATEGORIES_'.$name.'_FIELDSET_LABEL';
	if (isset($fieldSet->description) && trim($fieldSet->description)) :
		echo '<p class="tip">'.$this->escape(JText::_($fieldSet->description)).'</p>';
	endif;
	?>
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

		<?php if ($name == 'basic'):?>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('note'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('note'); ?>
				</div>
			</div>
		<?php endif;?>

<?php endforeach; ?>
