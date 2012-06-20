<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'link.cancel' || document.formvalidator.isValid(document.id('link-form'))) {
			Joomla.submitform(task, document.getElementById('link-form'));
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_redirect&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="link-form" class="form-validate form-horizontal">
	<fieldset>
		<ul class="nav nav-tabs">
			<li class="active"><a href="#basic" data-toggle="tab"><?php echo empty($this->item->id) ? JText::_('COM_REDIRECT_NEW_LINK') : JText::sprintf('COM_REDIRECT_EDIT_LINK', $this->item->id); ?></a></li>
			<li><a href="#options" data-toggle="tab"><?php echo JText::_('COM_REDIRECT_OPTIONS');?></a></li>
			<li><a href="#details" data-toggle="tab"><?php echo JText::_('COM_REDIRECT_DETAILS');?></a></li>
		</ul>
		<div class="tab-content">
			<div class="tab-pane active" id="basic">
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('old_url'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('old_url'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('new_url'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('new_url'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('comment'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('comment'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('id'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('id'); ?></div>
				</div>
			</div>
			<div class="tab-pane active" id="options">
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('published'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('published'); ?></div>
				</div>
			</div>
			<div class="tab-pane active" id="details">
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('created_date'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('created_date'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('modified_date'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('modified_date'); ?></div>
				</div>
			</div>
		</div>
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</fieldset>
</form>
