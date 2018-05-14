<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

JHtml::_('formbehavior.chosen', '.advancedSelect');

HTMLHelper::_('script', 'com_fields/admin-fields-default-batch.js', ['relative' => true, 'version' => 'auto']);

$context   = $this->escape($this->state->get('filter.context'));
?>

<div class="container">
	<div class="row">
		<div class="form-group col-md-6">
			<div class="controls">
				<?php echo JLayoutHelper::render('joomla.html.batch.language', array()); ?>
			</div>
		</div>
		<div class="form-group col-md-6">
			<div class="controls">
				<?php echo JLayoutHelper::render('joomla.html.batch.access', array()); ?>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="form-group col-md-6">
			<div class="controls">
				<?php $options = array(
					JHtml::_('select.option', 'c', JText::_('JLIB_HTML_BATCH_COPY')),
					JHtml::_('select.option', 'm', JText::_('JLIB_HTML_BATCH_MOVE'))
				);
				?>
				<label id="batch-choose-action-lbl" for="batch-choose-action"><?php echo JText::_('COM_FIELDS_BATCH_GROUP_LABEL'); ?></label>
				<div id="batch-choose-action" class="control-group">
					<select name="batch[group_id]" class="custom-select" id="batch-group-id">
						<option value=""><?php echo JText::_('JLIB_HTML_BATCH_NO_CATEGORY'); ?></option>
						<option value="nogroup"><?php echo JText::_('COM_FIELDS_BATCH_GROUP_OPTION_NONE'); ?></option>
						<?php echo JHtml::_('select.options', $this->get('Groups'), 'value', 'text'); ?>
					</select>
				</div>
				<div id="batch-copy-move" class="control-group radio">
					<?php echo JText::_('JLIB_HTML_BATCH_MOVE_QUESTION'); ?>
					<?php echo JHtml::_('select.radiolist', $options, 'batch[move_copy]', '', 'value', 'text', 'm'); ?>
				</div>
			</div>
		</div>
	</div>
</div>
