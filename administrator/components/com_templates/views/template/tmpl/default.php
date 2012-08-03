<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.modal');
$canDo	= TemplatesHelper::getActions();
?>
<form action="<?php echo JRoute::_('index.php?option=com_templates&view=template'); ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal">
		<fieldset id="template-manager">
			<div class="pull-left">
				<?php echo JHtml::_('templates.thumb', $this->template->element, $this->template->client_id); ?>
			</div>
			<h2><?php echo ucfirst($this->template->element); ?></h2>
			<?php $client = JApplicationHelper::getClientInfo($this->template->client_id); ?>
			<p><?php $this->template->xmldata = TemplatesHelper::parseXMLTemplateFile($client->path, $this->template->element);?></p>
			<p><?php  echo JText::_($this->template->xmldata->description); ?></p>
			<div class="clearfix"></div>
			<hr />
			<div class="row-fluid">
				<div class="span6">
					<div class="page-header">
						<?php echo JText::_('COM_TEMPLATES_TEMPLATE_MASTER_FILES');?>
					</div>
					<ul class="nav nav-tabs nav-stacked">
						<li>
							<?php $id = $this->files['main']['index']->id; ?>
							<?php if ($canDo->get('core.edit')) : ?>
							<a href="<?php echo JRoute::_('index.php?option=com_templates&task=source.edit&id='.$id);?>">
								<i class="icon-edit"></i>
							<?php endif; ?>
								<?php echo JText::_('COM_TEMPLATES_TEMPLATE_EDIT_MAIN');?>
							<?php if ($canDo->get('core.edit')) : ?>
								</a>
							<?php endif; ?>
						</li>
						<?php if ($this->files['main']['error']->exists) : ?>
						<li>
							<?php $id = $this->files['main']['error']->id; ?>
							<?php if ($canDo->get('core.edit')) : ?>
								<a href="<?php echo JRoute::_('index.php?option=com_templates&task=source.edit&id='.$id);?>">
									<i class="icon-edit"></i>
							<?php endif; ?>
								<?php echo JText::_('COM_TEMPLATES_TEMPLATE_EDIT_ERROR');?>
							<?php if ($canDo->get('core.edit')) : ?>
								</a>
							<?php endif; ?>
						</li>
						<?php endif; ?>
						<?php if ($this->files['main']['offline']->exists) : ?>
							<li>
								<?php $id = $this->files['main']['offline']->id; ?>
								<?php if ($canDo->get('core.edit')) : ?>
									<a href="<?php echo JRoute::_('index.php?option=com_templates&task=source.edit&id='.$id);?>">
										<i class="icon-edit"></i>
								<?php endif; ?>
								<?php echo JText::_('COM_TEMPLATES_TEMPLATE_EDIT_OFFLINEVIEW');?>
								<?php if ($canDo->get('core.edit')) : ?>
									</a>
								<?php endif; ?>
							</li>
						<?php endif; ?>
						<?php if ($this->files['main']['print']->exists) : ?>
						<li>
							<?php $id = $this->files['main']['print']->id; ?>
							<?php if ($canDo->get('core.edit')) : ?>
								<a href="<?php echo JRoute::_('index.php?option=com_templates&task=source.edit&id='.$id);?>">
									<i class="icon-edit"></i>
							<?php endif; ?>
								<?php echo JText::_('COM_TEMPLATES_TEMPLATE_EDIT_PRINTVIEW');?>
							<?php if ($canDo->get('core.edit')) : ?>
								</a>
							<?php endif; ?>
						</li>
						<?php endif; ?>
					</ul>
				</div>
				<div class="span6">
					<div class="page-header">
						<?php echo JText::_('COM_TEMPLATES_TEMPLATE_CSS');?>
					</div>
					<?php if (!empty($this->files['css'])) : ?>
					<ul class="nav nav-tabs nav-stacked">
						<?php foreach ($this->files['css'] as $file) : ?>
						<li>
							<?php if ($canDo->get('core.edit')) : ?>
							<a href="<?php echo JRoute::_('index.php?option=com_templates&task=source.edit&id='.$file->id);?>">
								<i class="icon-edit"></i>
							<?php endif; ?>

								<?php echo JText::sprintf('COM_TEMPLATES_TEMPLATE_EDIT_CSS', $file->name);?>
							<?php if ($canDo->get('core.edit')) : ?>
							</a>
							<?php endif; ?>
						</li>
						<?php endforeach; ?>
					</ul>
					<?php endif; ?>
				</div>
			</div>
			<!--<div>
				<a href="#" class="modal">
					<?php echo JText::sprintf('COM_TEMPLATES_TEMPLATE_ADD_CSS');?></a>
			</div>-->

		</fieldset>

		<input type="hidden" name="task" value="" />
</form>
<form action="<?php echo JRoute::_('index.php?option=com_templates&task=template.copy&id=' . JRequest::getInt('id')); ?>"
		method="post" name="adminForm" id="adminForm">
	<fieldset class="adminform" id="template-manager-css">
		<legend><?php echo JText::_('COM_TEMPLATES_TEMPLATE_COPY');?></legend>
		<label id="new_name" class="hasTip"  title="<?php echo JText::_('COM_TEMPLATES_TEMPLATE_NEW_NAME_DESC'); ?>"><?php echo JText::_('COM_TEMPLATES_TEMPLATE_NEW_NAME_LABEL')?></label>
		<input class="inputbox" type="text" id="new_name" name="new_name"  />
		<button type="submit"><?php echo JText::_('COM_TEMPLATES_TEMPLATE_COPY'); ?></button>
	</fieldset>
	<?php echo JHtml::_('form.token'); ?>
</form>
</div>
