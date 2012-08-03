<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
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
$canDo = UsersHelper::getActions();

// Get the form fieldsets.
$fieldsets = $this->form->getFieldsets();
?>

<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'user.cancel' || document.formvalidator.isValid(document.id('user-form'))) {
			Joomla.submitform(task, document.getElementById('user-form'));
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_users&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="user-form" class="form-validate form-horizontal" enctype="multipart/form-data">
	<fieldset>
		<ul class="nav nav-tabs">
		<li class="active"><a href="#details" data-toggle="tab"><?php echo JText::_('COM_USERS_USER_ACCOUNT_DETAILS');?></a></li>
			<?php if ($this->grouplist) :?>
				<li><a href="#groups" data-toggle="tab"><?php echo JText::_('COM_USERS_ASSIGNED_GROUPS');?></a></li>
			<?php endif; ?>
			<?php
			foreach ($fieldsets as $fieldset) :
				if ($fieldset->name == 'user_details') :
					continue;
				endif;
				?>
				<li><a href="#settings" data-toggle="tab"><?php echo JText::_($fieldset->label);?></a></li>
			<?php endforeach; ?>
			</ul>

			<div class="tab-content">
				<div class="tab-pane active" id="details">
					<?php foreach($this->form->getFieldset('user_details') as $field) :?>
						<div class="control-group">
							<div class="control-label">
								<?php echo $field->label; ?>
							</div>
							<div class="controls">
								<?php echo $field->input; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				<?php if ($this->grouplist) :?>
					<div class="tab-pane" id="groups">
						<?php echo $this->loadTemplate('groups');?>
					</div>
				<?php endif; ?>
				<?php
				foreach ($fieldsets as $fieldset) :
					if ($fieldset->name == 'user_details') :
						continue;
					endif;
				?>
				<div class="tab-pane" id="settings">
					<?php foreach($this->form->getFieldset($fieldset->name) as $field): ?>
						<?php if ($field->hidden): ?>
							<div class="control-group">
								<div class="controls">
									<?php echo $field->input; ?>
								</div>
							</div>
						<?php else: ?>
							<div class="control-group">
								<div class="control-label">
									<?php echo $field->label; ?>
								</div>
								<div class="controls">
									<?php echo $field->input; ?>
								</div>
							</div>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</fieldset>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
