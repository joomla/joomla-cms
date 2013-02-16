<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Create the copy/move options.
$options = array(
	JHtml::_('select.option', 'add', JText::_('COM_USERS_BATCH_ADD')),
	JHtml::_('select.option', 'del', JText::_('COM_USERS_BATCH_DELETE')),
	JHtml::_('select.option', 'set', JText::_('COM_USERS_BATCH_SET'))
);

?>
<fieldset class="batch">
	<legend><?php echo JText::_('COM_USERS_BATCH_OPTIONS');?></legend>
	<label id="batch-choose-action-lbl" for="batch-choose-action"><?php echo JText::_('COM_USERS_BATCH_GROUP') ?></label>
	<fieldset id="batch-choose-action" class="combo">
		<select name="batch[group_id]" class="inputbox" id="batch-group-id">
			<option value=""><?php echo JText::_('JSELECT') ?></option>
			<?php echo JHtml::_('select.options', JHtml::_('user.groups', JFactory::getUser()->get('isRoot'))); ?>
		</select>
		<?php echo JHtml::_('select.radiolist', $options, 'batch[group_action]', '', 'value', 'text', 'add') ?>
	</fieldset>

	<button type="submit" onclick="Joomla.submitbutton('user.batch');">
		<?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
	</button>
	<button type="button" onclick="document.id('batch-group-id').value=''">
		<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>
	</button>
</fieldset>
