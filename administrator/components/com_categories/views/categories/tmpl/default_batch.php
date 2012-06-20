<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$options = array(
	JHtml::_('select.option', 'c', JText::_('JLIB_HTML_BATCH_COPY')),
	JHtml::_('select.option', 'm', JText::_('JLIB_HTML_BATCH_MOVE'))
);
$published	= $this->state->get('filter.published');
$extension	= $this->escape($this->state->get('filter.extension'));
?>
<div class="accordion" id="accordion1">
	<div class="accordion-group">
	  <div class="accordion-heading">
	    <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#batch">
	      <?php echo JText::_('COM_CATEGORIES_BATCH_OPTIONS');?>
	    </a>
	  </div>
	  <div id="batch" class="accordion-body collapse">
	    <div class="accordion-inner">
	      <fieldset class="batch form-inline">
	      	<legend><?php echo JText::_('COM_CATEGORIES_BATCH_OPTIONS');?></legend>
	      	<p><?php echo JText::_('COM_CATEGORIES_BATCH_TIP'); ?></p>
	      	<?php echo JHtml::_('batch.access');?>
	      	<?php echo JHtml::_('batch.language'); ?>
	      
	      	<?php if ($published >= 0) : ?>
	      	<div class="control-group">
	      		<label id="batch-choose-action-lbl" for="batch-category-id" class="control-label">
	      			<?php echo JText::_('COM_CATEGORIES_BATCH_CATEGORY_LABEL'); ?>
	      		</label>
	      		<div id="batch-choose-action" class="combo controls">
	      		<select name="batch[category_id]" class="inputbox" id="batch-category-id">
	      			<option value=""><?php echo JText::_('JSELECT') ?></option>
	      			<?php echo JHtml::_('select.options', JHtml::_('category.categories', $extension, array('filter.published' => $published)));?>
	      		</select>
	      		<?php echo JHtml::_( 'select.radiolist', $options, 'batch[move_copy]', '', 'value', 'text', 'm'); ?>
	      		</div>
	      	</div>
	      	<?php endif; ?>
	      
	      	<button type="submit" class="btn btn-primary" onclick="submitbutton('category.batch');">
	      		<?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
	      	</button>
	      	<button type="button" class="btn" onclick="document.id('batch-category-id').value='';document.id('batch-access').value='';document.id('batch-language-id').value=''">
	      		<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>
	      	</button>
	      </fieldset>
	    </div>
	  </div>
	</div>
</div>
