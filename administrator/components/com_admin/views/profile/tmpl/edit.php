<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.formvalidation');

// Load chosen.css
JHtml::_('formbehavior.chosen', 'select');

// Get the form fieldsets.
$fieldsets = $this->form->getFieldsets();
?>

<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'profile.cancel' || document.formvalidator.isValid(document.id('profile-form')))
		{
			Joomla.submitform(task, document.getElementById('profile-form'));
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_admin&view=profile&layout=edit&id=' . $this->item->id); ?>" method="post" name="adminForm" id="profile-form" class="form-validate form-horizontal" enctype="multipart/form-data">
	<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'account')); ?>

	<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'account', JText::_('COM_ADMIN_USER_ACCOUNT_DETAILS', true)); ?>
	<?php foreach ($this->form->getFieldset('user_details') as $field) : ?>
		<div class="control-group">
			<div class="control-label"><?php echo $field->label; ?></div>
			<div class="controls"><?php echo $field->input; ?></div>
		</div>
	<?php endforeach; ?>
	<?php echo JHtml::_('bootstrap.endTab'); ?>

	<?php foreach ($fieldsets as $fieldset) : ?>
		<?php
		if ($fieldset->name == 'user_details')
		{
			continue;
		}
		?>
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', $fieldset->name, JText::_($fieldset->label, true)); ?>
		<?php foreach ($this->form->getFieldset($fieldset->name) as $field) : ?>
			<?php if ($field->hidden) : ?>
				<div class="control-group">
					<div class="controls"><?php echo $field->input; ?></div>
				</div>
			<?php else: ?>
				<div class="control-group">
					<div class="control-label"><?php echo $field->label; ?></div>
					<div class="controls"><?php echo $field->input; ?></div>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>
		<?php echo JHtml::_('bootstrap.endTab'); ?>
	<?php endforeach; ?>

	<?php echo JHtml::_('bootstrap.endTabSet'); ?>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
