<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_templates
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.modal');
$canDo	= TemplatesHelper::getActions();
?>
<form action="<?php echo JRoute::_('index.php?option=com_templates&view=template'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="width-50 fltlft">
		<fieldset class="adminform" id="template-manager">
			<legend><?php echo JText::_('COM_TEMPLATES_TEMPLATE_MASTER_FILES');?></legend>

			<?php echo JHtml::_('templates.thumb', $this->template->element, $this->template->client_id); ?>

			<h2><?php echo $this->template->element; ?></h2>
			<ul>
				<li>
					<?php $id = $this->files['main']['index']->id; ?>
					<?php if ($canDo->get('core.edit')) : ?>
					<a href="<?php echo JRoute::_('index.php?option=com_templates&task=source.edit&id='.$id);?>">
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
					<?php endif; ?>
						<?php echo JText::_('COM_TEMPLATES_TEMPLATE_EDIT_ERROR');?>
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
					<?php endif; ?>
						<?php echo JText::_('COM_TEMPLATES_TEMPLATE_EDIT_PRINTVIEW');?>
					<?php if ($canDo->get('core.edit')) : ?>
						</a>
					<?php endif; ?>
				</li>
				<?php endif; ?>
			</ul>
		</fieldset>

		<div class="clr"></div>
	</div>

	<div class="width-50 fltrt">

		<fieldset class="adminform" id="template-manager-css">
			<legend><?php echo JText::_('COM_TEMPLATES_TEMPLATE_CSS');?></legend>

			<?php if (!empty($this->files['css'])) : ?>
			<ul>
				<?php foreach ($this->files['css'] as $file) : ?>
				<li>
					<?php if ($canDo->get('core.edit')) : ?>
					<a href="<?php echo JRoute::_('index.php?option=com_templates&task=source.edit&id='.$file->id);?>">
					<?php endif; ?>

						<?php echo JText::sprintf('COM_TEMPLATES_TEMPLATE_EDIT_CSS', $file->name);?>
					<?php if ($canDo->get('core.edit')) : ?>
					</a>
					<?php endif; ?>
				</li>
				<?php endforeach; ?>
			</ul>
			<?php endif; ?>

			<!--<div>
				<a href="#" class="modal">
					<?php echo JText::sprintf('COM_TEMPLATES_TEMPLATE_ADD_CSS');?></a>
			</div>-->

		</fieldset>

		<div class="clr"></div>
		<input type="hidden" name="task" value="" />
	</div>
</form>