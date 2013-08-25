<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$clientId  = $this->state->get('filter.client_id');
$published = $this->state->get('filter.published');
$positions = JHtml::_('modules.positions', $clientId, $published);

// Add custom position to options
$customGroupText = JText::_('COM_MODULES_CUSTOM_POSITION');

// Build field
$attr = array(
	'id'          => 'batch-position-id',
	'list.attr'   => 'class="chzn-custom-value input-xlarge" '
	. 'data-custom_group_text="' . $customGroupText . '" '
	. 'data-no_results_text="' . JText::_('COM_MODULES_ADD_CUSTOM_POSITION') . '" '
	. 'data-placeholder="' . JText::_('COM_MODULES_TYPE_OR_SELECT_POSITION') . '" '
);

?>
<div class="modal hide fade" id="collapseModal">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">x</button>
		<h3><?php echo JText::_('COM_MODULES_BATCH_OPTIONS');?></h3>
	</div>
	<div class="modal-body">
		<p><?php echo JText::_('COM_MODULES_BATCH_TIP'); ?></p>
		<div class="control-group">
			<div class="controls">
				<?php echo JHtml::_('batch.access');?>
			</div>
		</div>
		<div class="control-group">
			<div class="controls">
				<?php echo JHtml::_('batch.language'); ?>
			</div>
		</div>
		<?php if ($published >= 0) : ?>
		<div class="control-group">
			<div class="controls">
				<label id="batch-choose-action-lbl" for="batch-choose-action">
					<?php echo JText::_('COM_MODULES_BATCH_POSITION_LABEL'); ?>
				</label>
				<div id="batch-choose-action" class="control-group">
					<?php echo JHtml::_('select.groupedlist', $positions, 'batch[position_id]', $attr) ?>
					<div id="batch-move-copy" class="control-group radio">
						<?php echo JHtml::_('modules.batchOptions'); ?>
					</div>
				</div>
			</div>
		</div>
		<?php endif; ?>
	</div>
	<div class="modal-footer">
		<button class="btn" type="button" onclick="document.id('batch-position-id').value='';document.id('batch-access').value='';document.id('batch-language-id').value=''" data-dismiss="modal">
			<?php echo JText::_('JCANCEL'); ?>
		</button>
		<button class="btn btn-primary" type="submit" onclick="Joomla.submitbutton('module.batch');">
			<?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
		</button>
	</div>
</div>
