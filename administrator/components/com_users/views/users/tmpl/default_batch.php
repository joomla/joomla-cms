<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Create the copy/move options.
$options = array(
	JHtml::_('select.option', 'add', JText::_('COM_USERS_BATCH_ADD')),
	JHtml::_('select.option', 'del', JText::_('COM_USERS_BATCH_DELETE')),
	JHtml::_('select.option', 'set', JText::_('COM_USERS_BATCH_SET'))
);

?>
<div class="accordion" id="accordion1">
	<div class="accordion-group">
	  <div class="accordion-heading">
	    <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#batch">
	      <?php echo JText::_('COM_USERS_BATCH_OPTIONS');?>
	    </a>
	  </div>
	  <div id="batch" class="accordion-body collapse">
	    <div class="accordion-inner">
	      <fieldset class="batch form-inline">
	      	<legend><?php echo JText::_('COM_USERS_BATCH_OPTIONS');?></legend>
	      	<div id="batch-choose-action" class="combo control-group">
				<label id="batch-choose-action-lbl" class="control-label" for="batch-choose-action">
					<?php echo JText::_('COM_USERS_BATCH_GROUP') ?>
				</label>
				<div id="batch-choose-action" class="combo controls">
					<div class="control-group">
						<select name="batch[group_id]" class="inputbox" id="batch-group-id">
							<option value=""><?php echo JText::_('JSELECT') ?></option>
							<?php echo JHtml::_('select.options', JHtml::_('user.groups')); ?>
						</select>
					</div>
				</div>
	      	</div>
			</div>
				<div class="control-group radio">
					<?php echo JHtml::_('select.radiolist', $options, 'batch[group_action]', '', 'value', 'text', 'add') ?>
				</div>

	      	<button class="btn btn-primary" type="submit" onclick="Joomla.submitbutton('user.batch');">
	      		<?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
	      	</button>
	      	<button class="btn" type="button" onclick="document.id('batch-group-id').value=''">
	      		<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>
	      	</button>
	      </fieldset>
	    </div>
	  </div>
	</div>
</div>
