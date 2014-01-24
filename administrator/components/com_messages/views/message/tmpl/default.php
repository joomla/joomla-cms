<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_messages
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;
JHtml::_('behavior.framework');
?>
<form action="<?php echo JRoute::_('index.php?option=com_messages'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="width-60 fltlft">
		<ul class="adminformlist">
		<li><?php echo JText::_('COM_MESSAGES_FIELD_USER_ID_FROM_LABEL'); ?>
		<?php echo $this->item->get('from_user_name');?></li>

		<li><?php echo JText::_('COM_MESSAGES_FIELD_DATE_TIME_LABEL'); ?>
		<?php echo JHtml::_('date', $this->item->date_time);?></li>

		<li><?php echo JText::_('COM_MESSAGES_FIELD_SUBJECT_LABEL'); ?>
		<?php echo $this->item->subject;?></li>

		<li><?php echo JText::_('COM_MESSAGES_FIELD_MESSAGE_LABEL'); ?>
		<pre class="pre_message"><?php echo $this->escape($this->item->message);?></pre></li>
		</ul>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="reply_id" value="<?php echo $this->item->message_id; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
