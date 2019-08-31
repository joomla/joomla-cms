<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_messages
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.core');
?>
<form action="<?php echo Route::_('index.php?option=com_messages'); ?>" method="post" name="adminForm" id="adminForm">
	<fieldset>
		<div class="form-group">
			<?php echo Text::_('COM_MESSAGES_FIELD_USER_ID_FROM_LABEL'); ?>
			<?php echo $this->item->get('from_user_name'); ?>
		</div>
		<div class="form-group">
			<?php echo Text::_('COM_MESSAGES_FIELD_DATE_TIME_LABEL'); ?>
			<?php echo HTMLHelper::_('date', $this->item->date_time, Text::_('DATE_FORMAT_LC2')); ?>
		</div>
		<div class="form-group">
			<?php echo Text::_('COM_MESSAGES_FIELD_SUBJECT_LABEL'); ?>
			<?php echo $this->item->subject; ?>
		</div>
		<div class="form-group">
			<?php echo Text::_('COM_MESSAGES_FIELD_MESSAGE_LABEL'); ?>
			<?php echo $this->item->message; ?>
		</div>
		<input type="hidden" name="task" value="">
		<input type="hidden" name="reply_id" value="<?php echo $this->item->message_id; ?>">
		<?php echo HTMLHelper::_('form.token'); ?>
	</fieldset>
</form>
