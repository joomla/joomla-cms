<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_messages
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;
?>
<form action="<?php echo JRoute::_('index.php?option=com_messages'); ?>" method="post" name="adminForm">
<div class="width-60 fltlft">
		<fieldset class="adminform">
		<legend><?php echo JText::_('NEW_PRIVATE_MESSAGE'); ?></legend>

			<?php echo JText::_('From'); ?>:

			<?php echo $this->item->user_from;?>

			<?php echo JText::_('Posted'); ?>:

			<?php echo $this->item->date_time;?>

			<?php echo JText::_('Subject'); ?>:

			<?php echo $this->item->subject;?>

			<?php echo JText::_('Message'); ?>:

			<pre><?php echo htmlspecialchars($this->item->message, ENT_COMPAT, 'UTF-8');?></pre>
	</fieldset>

<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="cid[]" value="<?php echo $this->item->message_id; ?>" />
<input type="hidden" name="userid" value="<?php echo $this->item->user_id_from; ?>" />
<input type="hidden" name="subject" value="Re: <?php echo $this->item->subject; ?>" />
<?php echo JHtml::_('form.token'); ?>
</form>
