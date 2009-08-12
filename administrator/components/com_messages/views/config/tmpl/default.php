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

<div id="editcell">
	<table class="adminform">
	<tr>
		<td width="20%">
			<?php echo JText::_('Lock Inbox'); ?>:
		</td>
		<td>
			<?php echo $this->vars['lock']; ?>
		</td>
	</tr>
	<tr>
		<td width="20%">
			<?php echo JText::_('Mail me on new Message'); ?>:
		</td>
		<td>
			<?php echo $this->vars['mail_on_new']; ?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo JText::_('Auto Purge Messages'); ?>:
		</td>
		<td>
			<input type="text" name="vars[auto_purge]" size="5" value="<?php echo $this->vars['auto_purge']; ?>" class="inputbox" />
			<?php echo JText::_('days old'); ?>
		</td>
	</tr>
	</table>
</div>

<input type="hidden" name="option" value="<?php echo $option; ?>" />
<input type="hidden" name="task" value="" />
<?php echo JHtml::_('form.token'); ?>
</form>
