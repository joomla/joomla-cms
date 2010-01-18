<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>
<script language="javascript" type="text/javascript">
	function submitbutton(pressbutton) {
		var form = document.adminForm;
		if (pressbutton == 'mail.cancel') {
			submitform(pressbutton);
			return;
		}
		// do field validation
		if (form.jform_subject.value == ""){
			alert("<?php echo JText::_('Users_Mail_Please_fill_in_the_subject', true); ?>");
		} else if (getSelectedValue('adminForm','jform_group') < 0){
			alert("<?php echo JText::_('Users_Mail_Please_select_a_group', true); ?>");
		} else if (form.jform_message.value == ""){
			alert("<?php echo JText::_('Users_Mail_Please_fill_in_the_message', true); ?>");
		} else {
			submitform(pressbutton);
		}
	}
</script>

<form action="<?php echo(JRoute::_('index.php?option=com_users&view=mail')); ?>" name="adminForm" method="post">

	<div class="width-30 fltlft">
		<fieldset class="adminform">
			<legend><?php echo JText::_('Users_Mail_Details'); ?></legend>

			<?php echo $this->form->getLabel('recurse'); ?>
			<?php echo $this->form->getInput('recurse'); ?>

			<?php echo $this->form->getLabel('mode'); ?>
			<?php echo $this->form->getInput('mode'); ?>

			<?php echo $this->form->getLabel('group'); ?>
			<?php echo $this->form->getInput('group'); ?>

			<?php echo $this->form->getLabel('bcc'); ?>
			<?php echo $this->form->getInput('bcc'); ?>

		</fieldset>
	</div>

	<div class="width-70 fltrt">
		<fieldset class="adminform">
			<legend><?php echo JText::_('Users_Mail_Message'); ?></legend>

			<?php echo $this->form->getLabel('subject'); ?>
			<?php echo $this->form->getInput('subject'); ?>

			<?php echo $this->form->getLabel('message'); ?>
			<?php echo $this->form->getInput('message'); ?>

		</fieldset>
	</div>

	<div class="clr"></div>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>