<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_messages
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.core');
JHtml::_('formbehavior.chosen', 'select');
?>
<form action="<?php echo JRoute::_('index.php?option=com_messages'); ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal">
	<fieldset>
		<div class="control-group">
			<div class="control-label">
				<?php echo JText::_('COM_MESSAGES_FIELD_USER_ID_FROM_LABEL'); ?>
			</div>
			<div class="controls">
				<?php echo $this->item->get('from_user_name'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo JText::_('COM_MESSAGES_FIELD_DATE_TIME_LABEL'); ?>
			</div>
			<div class="controls">
				<?php echo JHtml::_('date', $this->item->date_time); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo JText::_('COM_MESSAGES_FIELD_SUBJECT_LABEL'); ?>
			</div>
			<div class="controls">
				<?php echo $this->item->subject; ?>
			</div>
		</div>
		<div class="control-group">
			<div class="control-label">
				<?php echo JText::_('COM_MESSAGES_FIELD_MESSAGE_LABEL'); ?>
			</div>
			<div class="controls">
				<?php echo $this->item->message; ?>
			</div>
		</div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="reply_id" value="<?php echo $this->item->message_id; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</fieldset>
</form>
