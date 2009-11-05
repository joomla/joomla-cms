<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_templates
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.modal');
?>
<form action="<?php echo JRoute::_('index.php?option=com_templates&view=template'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="width-50 fltlft">
		<fieldset>
			<legend><?php echo JText::_('Templates_Template_Master_files');?></legend>

			<?php echo JHtml::_('templates.thumb', $this->template->element, $this->template->client_id); ?>

			<h2><?php echo $this->template->element; ?></h2>
			<ul>
				<li>
					<?php $id = $this->files['main']['index']->id; ?>
					<a href="<?php echo JRoute::_('index.php?option=com_templates&task=source.edit&id='.$id);?>">
						<?php echo JText::_('Templates_Template_Edit_main');?></a>
				</li>
				<li>
					<?php $id = $this->files['main']['error']->id; ?>
					<?php if ($this->files['main']['error']->exists) : ?>
					<a href="<?php echo JRoute::_('index.php?option=com_templates&task=source.edit&id='.$id);?>">
						<?php echo JText::_('Templates_Template_Edit_error');?></a>
					| <a href="<?php echo JRoute::_('index.php?option=com_templates&task=source.delete&id='.$id);?>">
						<?php echo JText::_('Templates_Template_Delete_error');?></a>
					<?php else : ?>
					<a href="<?php echo JRoute::_('index.php?option=com_templates&task=source.adderror&id='.$id);?>">
						<?php echo JText::_('Templates_Template_Add_error');?></a>
					<?php endif; ?>
				</li>
				<li>
					<?php $id = $this->files['main']['print']->id; ?>
					<?php if ($this->files['main']['print']->exists) : ?>
					<a href="<?php echo JRoute::_('index.php?option=com_templates&task=source.edit&id='.$id);?>">
						<?php echo JText::_('Templates_Template_Edit_printview');?></a>
					<?php else : ?>
					<a href="<?php echo JRoute::_('index.php?option=com_templates&task=source.addprint&id='.$id);?>">
						<?php echo JText::_('Templates_Template_Add_printview');?></a>
					<?php endif; ?>
				</li>
			<ul>
		</fieldset>

		<fieldset>
			<legend><?php echo JText::_('Templates_Template_CSS');?></legend>

			<?php if (!empty($this->files['css'])) : ?>
			<ul>
				<?php foreach ($this->files['css'] as $file) : ?>
				<li>
					<a href="<?php echo JRoute::_('index.php?option=com_templates&task=source.edit&id='.$file->id);?>">
						<?php echo JText::sprintf('Templates_Template_Edit_css', $file->name);?></a>
				</li>
				<?php endforeach; ?>
			<ul>
			<?php endif; ?>

			<div>
				<a href="#" class="modal">
					<?php echo JText::sprintf('Templates_Template_Add_css');?></a>
			</div>

		</fieldset>

		<br class="clr" />
	</div>

	<div class="width-50 fltlft">

		<fieldset>
			<legend><?php echo JText::_('Templates_Template_Layout_overrides');?></legend>

			<h3><?php echo JText::sprintf('Templates_Template_Override_Extension', 'Articles Manager'); ?></h3>
			<ul>
				<li>
					<a href="#">
						<?php echo JText::sprintf('Templates_Template_Edit_override', 'category/blog.php');?></a>
				</li>
				<li>
					<a href="#">
						<?php echo JText::sprintf('Templates_Template_Edit_override', 'article/default.php');?></a>
				</li>
			<ul>

			<div>
				<a href="#" class="modal">
					<?php echo JText::sprintf('Templates_Template_Add_override');?></a>
			</div>
		</fieldset>

		<div class="clr"></div>
	</div>

	<input type="hidden" name="task" value="" />
</form>

