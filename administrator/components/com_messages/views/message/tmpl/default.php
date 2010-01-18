<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_messages
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;
?>
<form action="<?php echo JRoute::_('index.php?option=com_messages'); ?>" method="post" name="adminForm">
	<div class="width-60 fltlft">

		<?php echo JText::_('Messages_Field_User_id_from_Label'); ?>
		<?php echo $this->item->get('from_user_name');?>

		<?php echo JText::_('Messages_Field_Date_time_Label'); ?>
		<?php echo JHtml::date($this->item->date_time);?>

		<?php echo JText::_('Messages_Field_Subject_Label'); ?>
		<?php echo $this->item->subject;?>

		<?php echo JText::_('Messages_Field_Message_Label'); ?>
		<pre><?php echo $this->escape($this->item->message);?></pre>
	</div>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="reply_id" value="<?php echo $this->item->message_id; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
