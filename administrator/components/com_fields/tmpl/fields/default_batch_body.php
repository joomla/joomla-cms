<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

HTMLHelper::_('script', 'com_fields/admin-fields-default-batch.js', ['version' => 'auto', 'relative' => true]);

$context   = $this->escape($this->state->get('filter.context'));
?>

<div class="container">
	<div class="row">
		<div class="form-group col-md-6">
			<div class="controls">
				<?php echo LayoutHelper::render('joomla.html.batch.language', array()); ?>
			</div>
		</div>
		<div class="form-group col-md-6">
			<div class="controls">
				<?php echo LayoutHelper::render('joomla.html.batch.access', array()); ?>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="form-group col-md-6">
			<div class="controls">
				<?php $options = array(
					HTMLHelper::_('select.option', 'c', Text::_('JLIB_HTML_BATCH_COPY')),
					HTMLHelper::_('select.option', 'm', Text::_('JLIB_HTML_BATCH_MOVE'))
				);
				?>
				<label id="batch-choose-action-lbl" for="batch-group-id">
					<?php echo Text::_('COM_FIELDS_BATCH_GROUP_LABEL'); ?>
				</label>
				<div id="batch-choose-action" class="control-group">
					<select name="batch[group_id]" class="custom-select" id="batch-group-id">
						<option value=""><?php echo Text::_('JLIB_HTML_BATCH_NO_CATEGORY'); ?></option>
						<option value="nogroup"><?php echo Text::_('COM_FIELDS_BATCH_GROUP_OPTION_NONE'); ?></option>
						<?php echo HTMLHelper::_('select.options', $this->get('Groups'), 'value', 'text'); ?>
					</select>
				</div>
				<div id="batch-copy-move" class="control-group radio">
					<?php echo Text::_('JLIB_HTML_BATCH_MOVE_QUESTION'); ?>
					<?php echo HTMLHelper::_('select.radiolist', $options, 'batch[move_copy]', '', 'value', 'text', 'm'); ?>
				</div>
			</div>
		</div>
	</div>
</div>
