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

$canDo = TemplatesHelper::getActions();
$input = JFactory::getApplication()->input;
?>

<form action="<?php echo JRoute::_('index.php?option=com_templates&view=template'); ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal">
		<fieldset id="template-manager">
			<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'browser')); ?>
				<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'browser', JText::_('File Browser', true)); ?>
					<div class="row-fluid">
						<div class="span3">
							<?php if(!empty($this->tree)):?>
								<?php echo $this->loadTemplate('tree');?>
							<?php endif;?>
						</div>
						<div class="span9"><?php var_dump($this->files);?></div>
					</div>
				<?php echo JHtml::_('bootstrap.endTab'); ?>
				<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'preview', JText::_('Template Preview', true)); ?>
					
				<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php echo JHtml::_('bootstrap.endTabSet'); ?>
			<!--<div>
				<a href="#" class="modal">
					<?php echo JText::sprintf('COM_TEMPLATES_TEMPLATE_ADD_CSS');?></a>
			</div>-->

		</fieldset>

		<input type="hidden" name="task" value="" />
</form>
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