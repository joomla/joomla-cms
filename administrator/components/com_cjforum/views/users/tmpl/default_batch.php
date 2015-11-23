<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

// Create the copy/move options.
$options = array(
	JHtml::_('select.option', 'add', JText::_('COM_CJFORUM_BATCH_ADD')),
	JHtml::_('select.option', 'del', JText::_('COM_CJFORUM_BATCH_DELETE')),
	JHtml::_('select.option', 'set', JText::_('COM_CJFORUM_BATCH_SET'))
);

// Create the reset password options.
$resetOptions = array(
	JHtml::_('select.option', '', JText::_('COM_CJFORUM_NO_ACTION')),
	JHtml::_('select.option', 'yes', JText::_('JYES')),
	JHtml::_('select.option', 'no', JText::_('JNO'))
);
JHtml::_('formbehavior.chosen', 'select');
?>
<div class="modal hide fade" id="collapseModal">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">&#215;</button>
		<h3><?php echo JText::_('COM_CJFORUM_BATCH_OPTIONS'); ?></h3>
	</div>
	<div class="modal-body modal-batch">
		<div class="row-fluid">
            <div id="batch-choose-action" class="combo control-group">
				<label id="batch-choose-action-lbl" class="control-label" for="batch-choose-action">
					<?php echo JText::_('COM_CJFORUM_BATCH_RANK') ?>
				</label>
			</div>
			<div id="batch-choose-action" class="combo controls">
				<div class="control-group">
					<select name="batch[rank_id]" id="batch-rank-id">
						<option value=""><?php echo JText::_('JSELECT') ?></option>
						<?php echo JHtml::_('select.options', JHtml::_('users.ranks')); ?>
					</select>
				</div>
			</div>
			<div class="control-group radio">
				<label for="batch[rank_action]add" id="batch[rank_action]add-lbl" class="radio" aria-invalid="false">
				    <input type="radio" name="batch[rank_action]" id="batch[rank_action]add" value="add"> <?php echo JText::_('COM_CJFORUM_ASSIGN_RANK');?>
                </label>
			</div>
		</div>
		<label><?php echo JText::_('COM_CJFORUM_REQUIRE_PASSWORD_RESET'); ?></label>
		<div class="control-group radio">
			<?php echo JHtml::_('select.radiolist', $resetOptions, 'batch[reset_id]', '', 'value', 'text', '') ?>
		</div>
	</div>
	<div class="modal-footer">
		<button class="btn" type="button" onclick="document.id('batch-group-id').value=''" data-dismiss="modal">
			<?php echo JText::_('JCANCEL'); ?>
		</button>
		<button class="btn btn-primary" type="submit" onclick="Joomla.submitbutton('user.batch');">
			<?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
		</button>
	</div>
</div>
