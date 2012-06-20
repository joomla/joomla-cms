<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$options = array(
	JHtml::_('select.option', 'c', JText::_('JLIB_HTML_BATCH_COPY')),
	JHtml::_('select.option', 'm', JText::_('JLIB_HTML_BATCH_MOVE'))
);
$published = $this->state->get('filter.published');
?>
<div class="accordion" id="accordion1">
	<div class="accordion-group">
	  <div class="accordion-heading">
	    <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#batch">
	      <?php echo JText::_('COM_MENUS_BATCH_OPTIONS');?>
	    </a>
	  </div>
	  <div id="batch" class="accordion-body collapse">
	    <div class="accordion-inner">
	      <fieldset class="batch form-inline">
	      	<legend><?php echo JText::_('COM_MENUS_BATCH_OPTIONS');?></legend>
	      	<p><?php echo JText::_('COM_MENUS_BATCH_TIP'); ?></p>
	      	<?php echo JHtml::_('batch.access');?>
	      	<?php echo JHtml::_('batch.language'); ?>
	      
	      	<?php if ($published >= 0) : ?>
	      		<div id="batch-choose-action" class="combo control-group">
	      			<label id="batch-choose-action-lbl" class="control-label" for="batch-choose-action">
	      				<?php echo JText::_('COM_MENUS_BATCH_MENU_LABEL'); ?>
	      			</label>
	      			<div class="controls">
		      			<select name="batch[menu_id]" class="inputbox" id="batch-menu-id">
		      				<option value=""><?php echo JText::_('JSELECT') ?></option>
		      				<?php echo JHtml::_('select.options', JHtml::_('menu.menuitems', array('published' => $published)));?>
		      			</select>
		      			<?php echo JHtml::_( 'select.radiolist', $options, 'batch[move_copy]', '', 'value', 'text', 'm'); ?>
	      			</div>
	      		</div>
	      	<?php endif; ?>
	      
	      	<button class="btn btn-primary" type="submit" onclick="Joomla.submitbutton('item.batch');">
	      		<?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
	      	</button>
	      	<button class="btn" type="button" onclick="document.id('batch-menu-id').value='';document.id('batch-access').value='';document.id('batch-language-id').value=''">
	      		<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>
	      	</button>
	      </fieldset>
	    </div>
	  </div>
	</div>
</div>
