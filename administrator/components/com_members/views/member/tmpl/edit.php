<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_members
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('_JEXEC') or die('Invalid Request.');

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');

// Load the default stylesheet.
JHtml::stylesheet('default.css', 'administrator/components/com_members/media/css/');

// Build the toolbar.
$this->buildDefaultToolBar();
?>

<script type="text/javascript">
<!--
	function submitbutton(task)
	{
		if (task == 'member.cancel' || document.formvalidator.isValid($('member-form'))) {
			submitform(task);
		}
	}
// -->
</script>

<form action="<?php JRoute::_('index.php?option=com_members'); ?>" method="post" name="adminForm" id="member-form">
	<fieldset style="width:60%; float:left;">
		<legend><?php echo JText::_('Member_Account_Details'); ?></legend>

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
			<li>
				<?php echo $this->form->getLabel('gid'); ?><br />
				<?php echo $this->form->getInput('gid'); ?>
			</li>
		</ol>
	</fieldset>

	<fieldset style="width:35%; float:right;" id="member-groups">
		<legend><?php echo JText::_('Members_Assigned_Groups'); ?></legend>
			<?php if ($this->grouplist) :
				echo $this->loadTemplate('groups');
			endif; ?>
	</fieldset>

	<fieldset style="width:60%; float:left;">
		<legend><?php echo JText::_('Member_User_Options'); ?></legend>

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

	<fieldset style="width:60%; float:left;">
		<legend><?php echo JText::_('Member_User_Profile'); ?></legend>

		<table>
		<?php foreach($this->form->getFields('profile') as $field): ?>
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
