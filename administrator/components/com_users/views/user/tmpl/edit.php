<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');

// Load the default stylesheet.
JHtml::stylesheet('default.css', 'administrator/components/com_users/media/css/');
?>

<script type="text/javascript">
<!--
	function submitbutton(task)
	{
		if (task == 'user.cancel' || document.formvalidator.isValid($('user-form'))) {
			submitform(task);
		}
	}
// -->
</script>

<form action="<?php JRoute::_('index.php?option=com_users'); ?>" method="post" name="adminForm" id="user-form" class="form-validate">
	<fieldset style="width:60%; float:left;">
		<legend><?php echo JText::_('User_Account_Details'); ?></legend>

		<ol>
			<li>
				<?php echo $this->form->getLabel('name'); ?><br />
				<?php echo $this->form->getInput('name'); ?>
			</li>
			<li>
				<?php echo $this->form->getLabel('username'); ?><br />
				<?php echo $this->form->getInput('username'); ?>
			</li>
			<li>
				<?php echo $this->form->getLabel('password'); ?><br />
				<?php echo $this->form->getInput('password'); ?>
			</li>
			<li>
				<?php echo $this->form->getLabel('password2'); ?><br />
				<?php echo $this->form->getInput('password2'); ?>
			</li>
			<li>
				<?php echo $this->form->getLabel('email'); ?><br />
				<?php echo $this->form->getInput('email'); ?>
			</li>
		</ol>
	</fieldset>

	<fieldset style="width:37%; float:right;" id="user-groups">
		<legend><?php echo JText::_('Users_Assigned_Groups'); ?></legend>
			<?php if ($this->grouplist) :
				echo $this->loadTemplate('groups');
			endif; ?>
	</fieldset>

	<fieldset style="width:60%; float:left;">
		<legend><?php echo JText::_('Users_User_Options'); ?></legend>

		<table>
		<?php foreach($this->form->getFields('params') as $field): ?>
			<?php if ($field->hidden): ?>
				<?php echo $field->input; ?>
			<?php else: ?>
				<tr>
					<td class="paramlist_key" width="40%">
						<?php echo $field->label; ?>
					</td>
					<td class="paramlist_value">
						<?php echo $field->input; ?>
					</td>
				</tr>
			<?php endif; ?>
		<?php endforeach; ?>
		</table>

	</fieldset>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
