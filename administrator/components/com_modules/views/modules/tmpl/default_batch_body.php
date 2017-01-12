<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$clientId  = $this->state->get('client_id');

// Show only Module Positions of published Templates
$published = 1;
$positions = JHtml::_('modules.positions', $clientId, $published);
$positions['']['items'][] = ModulesHelper::createOption('nochange', JText::_('COM_MODULES_BATCH_POSITION_NOCHANGE'));
$positions['']['items'][] = ModulesHelper::createOption('noposition', JText::_('COM_MODULES_BATCH_POSITION_NOPOSITION'));

// Add custom position to options
$customGroupText = JText::_('COM_MODULES_CUSTOM_POSITION');

// Build field
$attr = array(
	'id'        => 'batch-position-id',
	'list.attr' => 'class="chzn-custom-value" '
		. 'data-custom_group_text="' . $customGroupText . '" '
		. 'data-no_results_text="' . JText::_('COM_MODULES_ADD_CUSTOM_POSITION') . '" '
		. 'data-placeholder="' . JText::_('COM_MODULES_TYPE_OR_SELECT_POSITION') . '" '
);

JHtml::_('formbehavior.chosen', '.chzn-custom-value');
?>

<p><?php echo JText::_('COM_MODULES_BATCH_TIP'); ?></p>
<div class="container">
	<div class="row">
		<div class="form-group col-md-6">
			<div class="controls">
				<?php echo JHtml::_('batch.language'); ?>
			</div>
		</div>
		<div class="form-group col-md-6">
			<div class="controls">
				<?php echo JHtml::_('batch.access'); ?>
			</div>
		</div>
	</div>
	<div class="row">
		<?php if ($published >= 0) : ?>
			<div class="col-md-6">
				<div class="controls">
					<label id="batch-choose-action-lbl" for="batch-choose-action">
						<?php echo JText::_('COM_MODULES_BATCH_POSITION_LABEL'); ?>
					</label>
					<div id="batch-choose-action" class="control-group">
						<?php echo JHtml::_('select.groupedlist', $positions, 'batch[position_id]', $attr); ?>
						<div id="batch-copy-move" class="control-group radio">
							<?php echo JHtml::_('modules.batchOptions'); ?>
						</div>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
