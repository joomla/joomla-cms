<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$clientId = $this->state->get('filter.client_id');
$published = $this->state->get('filter.published');
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
				<?php echo JHtml::_('modules.positions', $clientId);?>
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
