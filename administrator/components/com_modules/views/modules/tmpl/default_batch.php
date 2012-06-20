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

<div class="accordion" id="accordion1">
	<div class="accordion-group">
	  <div class="accordion-heading">
	    <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#batch">
	      <?php echo JText::_('COM_MODULES_BATCH_OPTIONS');?>
	    </a>
	  </div>
	  <div id="batch" class="accordion-body collapse">
	    <div class="accordion-inner">
	      <fieldset class="batch form-inline">
	      	<legend><?php echo JText::_('COM_MODULES_BATCH_OPTIONS');?></legend>
	      	<p><?php echo JText::_('COM_MODULES_BATCH_TIP'); ?></p>
	      	<div class="control-group">
		      	<?php echo JHtml::_('batch.access');?>
		      	<?php echo JHtml::_('batch.language'); ?>
		    </div>
	      	<?php if ($published >= 0) : ?>
	      		<div class="control-group">
	      			<?php echo JHtml::_('modules.positions', $clientId);?>
	      		</div>
	      	<?php endif; ?>
	      	<button class="btn btn-primary" type="submit" onclick="Joomla.submitbutton('module.batch');">
	      		<?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
	      	</button>
	      	<button class="btn" type="button" onclick="document.id('batch-position-id').value='';document.id('batch-access').value='';document.id('batch-language-id').value=''">
	      		<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>
	      	</button>
	      </fieldset>
	    </div>
	  </div>
	</div>
</div>
