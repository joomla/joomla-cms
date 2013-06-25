<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.modal');
JHtml::_('formbehavior.chosen', 'select');

JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');

$canDo = TemplatesHelper::getActions();
$input = JFactory::getApplication()->input;
?>

<div class="row-fluid">
	<div class="span3">
		<?php if(!empty($this->tree)):?>
			<?php echo $this->loadTemplate('tree');?>
		<?php endif;?>
	</div>
	<div class="span9">
		<form action="<?php echo JRoute::_('index.php?option=com_templates&view=template&id=' . $input->getInt('id')); ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal">
			<fieldset class="adminform">
				<legend><?php echo JText::sprintf('COM_TEMPLATES_TEMPLATE_FILENAME', $this->source->filename, $this->template->element); ?></legend>
		
				<?php echo $this->form->getLabel('source'); ?>
				<div class="clr"></div>
				<div class="editor-border">
				<?php echo $this->form->getInput('source'); ?>
				</div>
				<input type="hidden" name="task" value="" />
				<?php echo JHtml::_('form.token'); ?>
			</fieldset>
		
			<?php echo $this->form->getInput('extension_id'); ?>
			<?php echo $this->form->getInput('filename'); ?>
		</form>
	</div>
</div>
			<!--<div>
				<a href="#" class="modal">
					<?php echo JText::sprintf('COM_TEMPLATES_TEMPLATE_ADD_CSS');?></a>
			</div>-->

		</fieldset>

		<input type="hidden" name="task" value="" />

<form action="<?php echo JRoute::_('index.php?option=com_templates&task=template.copy&id=' . $input->getInt('id')); ?>"
			method="post" name="adminForm" id="adminForm">
	<div  id="collapseModal" class="modal hide fade">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3><?php echo JText::_('COM_TEMPLATES_TEMPLATE_COPY');?></h3>
		</div>
		<div class="modal-body">
			<div id="template-manager-css" class="form-horizontal">
				<div class="control-group">
					<label for="new_name" class="control-label hasTip" title="<?php echo JText::_('COM_TEMPLATES_TEMPLATE_NEW_NAME_DESC'); ?>"><?php echo JText::_('COM_TEMPLATES_TEMPLATE_NEW_NAME_LABEL')?></label>
					<div class="controls">
						<input class="input-xlarge" type="text" id="new_name" name="new_name"  />
					</div>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<a href="#" class="btn" data-dismiss="modal">Close</a>
			<button class="btn btn-primary" type="submit"><?php echo JText::_('COM_TEMPLATES_TEMPLATE_COPY'); ?></button>
		</div>
	</div>
	<?php echo JHtml::_('form.token'); ?>
</form>