<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
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

<form action="<?php echo JRoute::_('index.php?option=com_admin&view=profile&layout=edit&id='.$this->item->id); ?>" method="post" name="adminForm" id="profile-form" class="form-validate form-horizontal" enctype="multipart/form-data">
	<ul class="nav nav-tabs">
		<li class="active"><a href="#account" data-toggle="tab"><?php echo JText::_('COM_ADMIN_USER_ACCOUNT_DETAILS'); ?></a></li>
		<?php
		foreach ($fieldsets as $fieldset) :
			if ($fieldset->name == 'user_details') :
				continue;
			endif;
		?>
		<li><a href="#settings-<?php echo $fieldset->name;?>" data-toggle="tab"><?php echo JText::_($fieldset->label);?></a></li>
	<?php endforeach; ?>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active" id="account">
		<?php foreach($this->form->getFieldset('user_details') as $field) :?>
			<div class="control-group">
				<div class="control-label"><?php echo $field->label; ?></div>
				<div class="controls"><?php echo $field->input; ?></div>
			</div>
		<?php endforeach; ?>
		</div>
		<?php
		foreach ($fieldsets as $fieldset) :
			if ($fieldset->name == 'user_details') :
				continue;
			endif;
			?>
			<div class="tab-pane" id="settings-<?php echo $fieldset->name;?>">
			<?php foreach($this->form->getFieldset($fieldset->name) as $field): ?>
				<?php if ($field->hidden): ?>
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
			</div>
		<?php endforeach; ?>
	</div>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
