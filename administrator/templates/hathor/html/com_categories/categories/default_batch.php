<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$options = array(
	JHtml::_('select.option', 'c', JText::_('JLIB_HTML_BATCH_COPY')),
	JHtml::_('select.option', 'm', JText::_('JLIB_HTML_BATCH_MOVE'))
);
$published = $this->state->get('filter.published');
$extension = $this->escape($this->state->get('filter.extension'));
?>
<div class="modal hide fade" id="collapseModal">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">x</button>
		<h3><?php echo JText::_('COM_CATEGORIES_BATCH_OPTIONS'); ?></h3>
	</div>
	<div class="modal-body">
		<p><?php echo JText::_('COM_CATEGORIES_BATCH_TIP'); ?></p>

		<div class="control-group">
			<div class="controls">
				<?php echo JHtml::_('batch.access'); ?>
			</div>
		</div>
		<div class="control-group">
			<div class="controls">
				<?php echo JHtml::_('batch.language'); ?>
			</div>
		</div>
		<?php if ($published >= 0) : ?>
			<div class="control-group">
				<label id="batch-choose-action-lbl" for="batch-category-id" class="control-label">
					<?php echo JText::_('COM_CATEGORIES_BATCH_CATEGORY_LABEL'); ?>
				</label>

				<div id="batch-choose-action" class="combo controls">
					<select name="batch[category_id]" class="inputbox" id="batch-category-id">
						<option value=""><?php echo JText::_('JSELECT') ?></option>
						<?php echo JHtml::_('select.options', JHtml::_('category.categories', $extension, array('filter.published' => $published))); ?>
					</select>
				</div>
			</div>
			<div class="control-group radio">
				<?php echo JHtml::_('select.radiolist', $options, 'batch[move_copy]', '', 'value', 'text', 'm'); ?>
			</div>
		<?php endif; ?>
	</div>
	<div class="modal-footer">
		<button class="btn" type="button" onclick="document.id('batch-category-id').value='';document.id('batch-access').value='';document.id('batch-language-id').value=''" data-dismiss="modal">
			<?php echo JText::_('JCANCEL'); ?>
		</button>
		<button class="btn btn-primary" type="submit" onclick="Joomla.submitbutton('category.batch');">
			<?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
		</button>
	</div>
</div>
