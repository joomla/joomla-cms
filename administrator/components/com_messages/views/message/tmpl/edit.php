<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_messages
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined( '_JEXEC' ) or die;

$user = &JFactory::getUser();
?>
<script language="javascript" type="text/javascript">
function submitbutton(pressbutton) {
	var form = document.adminForm;
	if (pressbutton == 'cancel') {
		submitform(pressbutton);
		return;
	}

	// do field validation
	if (form.subject.value == "") {
		alert("<?php echo JText::_('You must provide a subject.'); ?>");
	} else if (form.message.value == "") {
		alert("<?php echo JText::_('You must provide a message.'); ?>");
	} else if (getSelectedValue('adminForm','user_id_to') < 1) {
		alert("<?php echo JText::_('You must select a recipient.'); ?>");
	} else {
		submitform(pressbutton);
	}
}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_messages'); ?>" method="post" name="adminForm">
<div class="width-100">
		<fieldset class="adminform">
		<legend><?php echo JText::_('NEW_PRIVATE_MESSAGE'); ?></legend>
		<?php echo JText::_('To'); ?>:

		<?php echo $this->recipientslist; ?>

		<?php echo JText::_('Subject'); ?>:

		<input type="text" name="subject" size="50" maxlength="100" class="inputbox" value="<?php echo $this->subject; ?>"/>

		<?php echo JText::_('Message'); ?>:

		<textarea name="message" id="message" rows="30" class="inputbox"></textarea>
	</fieldset>
</div>
<input type="hidden" name="user_id_from" value="<?php echo $user->get('id'); ?>">
<input type="hidden" name="task" value="">
<?php echo JHtml::_('form.token'); ?>
</form>