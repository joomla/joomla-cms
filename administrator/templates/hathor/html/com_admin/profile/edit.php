<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.hathor
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');

// Get the form fieldsets.
$fieldsets = $this->form->getFieldsets();
?>

<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'profile.cancel' || document.formvalidator.isValid(document.id('profile-form'))) {
			Joomla.submitform(task, document.getElementById('profile-form'));
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_admin&view=profile&layout=edit&id='.$this->item->id); ?>" method="post" name="adminForm" id="profile-form" class="form-validate" enctype="multipart/form-data">
	<div class="col main-section">
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_ADMIN_USER_ACCOUNT_DETAILS'); ?></legend>
			<ul class="adminformlist">
			<?php foreach($this->form->getFieldset('user_details') as $field) :?>
				<li><?php echo $field->label; ?>
				<?php echo $field->input; ?></li>
			<?php endforeach; ?>
			</ul>
		</fieldset>
	</div>

	<div class="col options-section">
		<?php
		echo JHtml::_('sliders.start');
		foreach ($fieldsets as $fieldset) :
			if ($fieldset->name == 'user_details') :
				continue;
			endif;
			echo JHtml::_('sliders.panel', JText::_($fieldset->label), $fieldset->name);
		?>
		<fieldset class="panelform">
		<legend class="element-invisible"><?php echo JText::_($fieldset->label); ?></legend>
		<ul class="adminformlist">
		<?php foreach($this->form->getFieldset($fieldset->name) as $field): ?>
			<?php if ($field->hidden): ?>
				<?php echo $field->input; ?>
			<?php else: ?>
				<li><?php echo $field->label; ?>
				<?php echo $field->input; ?></li>
			<?php endif; ?>
		<?php endforeach; ?>
		</ul>
		</fieldset>
		<?php endforeach; ?>
		<?php echo JHtml::_('sliders.end'); ?>

		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
