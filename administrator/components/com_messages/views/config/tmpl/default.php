<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_messages
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
?>
<script language="javascript" type="text/javascript">
function submitbutton(pressbutton) {
	var form = document.adminForm;
	if (pressbutton == 'saveconfig') {
		if (confirm ("<?php echo JText::_('Are you sure?'); ?>")) {
			submitform(pressbutton);
		}
	} else {
		document.location.href = 'index.php?option=<?php echo $option;?>';
	}
}
</script>
<form action="index.php" method="post" name="adminForm">
	<div class="width-40">
		<fieldset class="adminform">
			<legend><?php echo JText::_('CONFIGURATION_DETAILS'); ?></legend>
			<label><?php echo JText::_('Lock Inbox'); ?>:</label>

			<?php echo $this->vars['lock']; ?>

			<label><?php echo JText::_('Mail me on new Message'); ?>:</label>

			<?php echo $this->vars['mail_on_new']; ?>

			<label><?php echo JText::_('Auto Purge Messages'); ?>:</label>

			<input type="text" name="vars[auto_purge]" size="5" value="<?php echo $this->vars['auto_purge']; ?>" class="inputbox" />
			<?php echo JText::_('days old'); ?>
		</fieldset>
	</div>

<input type="hidden" name="option" value="<?php echo $option; ?>" />
<input type="hidden" name="task" value="" />
<?php echo JHtml::_('form.token'); ?>
</form>
