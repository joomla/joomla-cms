<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

// Create the copy/move options.
$options = array(
	JHtml::_('select.option', 'add', JText::_('COM_USERS_BATCH_ADD')),
	JHtml::_('select.option', 'del', JText::_('COM_USERS_BATCH_DELETE')),
	JHtml::_('select.option', 'set', JText::_('COM_USERS_BATCH_SET'))
);

// Create the reset password options.
$resetOptions = array(
	JHtml::_('select.option', '', JText::_('COM_USERS_NO_ACTION')),
	JHtml::_('select.option', 'yes', JText::_('JYES')),
	JHtml::_('select.option', 'no', JText::_('JNO'))
);
JHtml::_('formbehavior.chosen', 'select');
?>

<div class="container-fluid">
	<div class="row-fluid">
		<div class="controls">
			<label id="batch-choose-action-lbl" class="control-label" for="batch-group-id">
				<?php echo JText::_('COM_USERS_BATCH_GROUP'); ?>
			</label>
			<div id="batch-choose-action" class="combo controls">
				<div class="control-group">
					<select name="batch[group_id]" id="batch-group-id">
						<option value=""><?php echo JText::_('JSELECT'); ?></option>
						<?php echo JHtml::_('select.options', JHtml::_('user.groups')); ?>
					</select>
				</div>
			</div>
			<div class="control-group radio">
				<?php echo JHtml::_('select.radiolist', $options, 'batch[group_action]', '', 'value', 'text', 'add'); ?>
			</div>
		</div>
	</div>
	<label><?php echo JText::_('COM_USERS_REQUIRE_PASSWORD_RESET'); ?></label>
	<div class="control-group radio">
		<?php echo JHtml::_('select.radiolist', $resetOptions, 'batch[reset_id]', '', 'value', 'text', ''); ?>
	</div>
</div>
