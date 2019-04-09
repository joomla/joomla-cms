<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$published = $this->state->get('filter.published');
?>
<div class="modal hide fade" id="collapseModal">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-label="<?php echo JText::_('JLIB_HTML_BEHAVIOR_CLOSE'); ?>">
			<span aria-hidden="true">&times;</span>
		</button>
		<h3><?php echo JText::_('COM_CONTACT_BATCH_OPTIONS'); ?></h3>
	</div>
	<div class="modal-body modal-batch">
		<p><?php echo JText::_('COM_CONTACT_BATCH_TIP'); ?></p>
		<div class="row-fluid">
			<div class="control-group span6">
				<div class="controls">
					<?php echo JHtml::_('batch.language'); ?>
				</div>
			</div>
			<div class="control-group span6">
				<div class="controls">
					<?php echo JHtml::_('batch.access'); ?>
				</div>
			</div>
		</div>
		<div class="row-fluid">
		<?php if ($published >= 0) : ?>
			<div class="control-group span6">
				<div class="controls">
					<?php echo JHtml::_('batch.item', 'com_contact'); ?>
				</div>
			</div>
		<?php endif; ?>
		<div class="control-group span6">
			<div class="controls">
				<?php echo JHtml::_('batch.tag'); ?>
			</div>
		</div>
		<div class="row-fluid">
			<div class="control-group">
				<div class="controls">
					<?php echo JHtml::_('batch.user'); ?>
				</div>
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<button type="button" class="btn" onclick="document.getElementById('batch-category-id').value='';document.getElementById('batch-access').value='';document.getElementById('batch-language-id').value='';document.getElementById('batch-user-id').value='';document.getElementById('batch-tag-id').value=''" data-dismiss="modal">
			<?php echo JText::_('JCANCEL'); ?>
		</button>
		<button type="submit" class="btn btn-primary" onclick="Joomla.submitbutton('contact.batch');">
			<?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
		</button>
	</div>
</div>
