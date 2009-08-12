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
<form action="index.php" method="post" name="adminForm">

<table class="adminform">
	<tr>
		<td width="100">
			<?php echo JText::_('From'); ?>:
		</td>
		<td width="85%" bgcolor="#ffffff">
			<?php echo $this->item->user_from;?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo JText::_('Posted'); ?>:
		</td>
		<td bgcolor="#ffffff">
			<?php echo $this->item->date_time;?>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo JText::_('Subject'); ?>:
		</td>
		<td bgcolor="#ffffff">
			<?php echo $this->item->subject;?>
		</td>
	</tr>
	<tr>
		<td valign="top">
			<?php echo JText::_('Message'); ?>:
		</td>
		<td width="100%" bgcolor="#ffffff">
			<pre><?php echo htmlspecialchars($this->item->message, ENT_COMPAT, 'UTF-8');?></pre>
		</td>
	</tr>
</table>

<input type="hidden" name="option" value="<?php echo $option;?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="cid[]" value="<?php echo $this->item->message_id; ?>" />
<input type="hidden" name="userid" value="<?php echo $this->item->user_id_from; ?>" />
<input type="hidden" name="subject" value="Re: <?php echo $this->item->subject; ?>" />
<?php echo JHtml::_('form.token'); ?>
</form>
