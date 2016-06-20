<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.hathor
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

$saveHistory = $this->state->get('params')->get('save_history', 0);

JHtml::_('behavior.formvalidator');

JFactory::getDocument()->addScriptDeclaration("
	Joomla.submitbutton = function(task)
	{
		if (task == 'tag.cancel' || document.formvalidator.isValid(document.getElementById('tag-form')))
		{
			" . $this->form->getField('description')->save() . "
			Joomla.submitform(task, document.getElementById('tag-form'));
		}
	}
");
?>

<div class="weblink-edit">

<form action="<?php echo JRoute::_('index.php?option=com_tags&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="tag-form" class="form-validate">
	<div class="col main-section">
		<fieldset class="adminform">
			<legend><?php echo empty($this->item->id) ? JText::_('JTOOLBAR_NEW') : JText::sprintf('JTOOLBAR_EDIT', $this->item->id); ?></legend>
			<ul class="adminformlist">
				<li><?php echo $this->form->getLabel('title'); ?>
				<?php echo $this->form->getInput('title'); ?></li>

				<li><?php echo $this->form->getLabel('alias'); ?>
				<?php echo $this->form->getInput('alias'); ?></li>

				<li><?php echo $this->form->getLabel('parent_id'); ?>
				<?php echo $this->form->getInput('parent_id'); ?></li>

				<li><?php echo $this->form->getLabel('published'); ?>
				<?php echo $this->form->getInput('published'); ?></li>

				<li><?php echo $this->form->getLabel('access'); ?>
				<?php echo $this->form->getInput('access'); ?></li>

				<li><?php echo $this->form->getLabel('language'); ?>
				<?php echo $this->form->getInput('language'); ?></li>

				<?php if ($saveHistory) : ?>
					<li><?php echo $this->form->getLabel('version_note'); ?>
					<?php echo $this->form->getInput('version_note'); ?></li>
				<?php endif; ?>

				<li><?php echo $this->form->getLabel('id'); ?>
				<?php echo $this->form->getInput('id'); ?></li>
			</ul>

			<div>
				<?php echo $this->form->getLabel('description'); ?>
				<div class="clr"></div>
				<?php echo $this->form->getInput('description'); ?>
			</div>
		</fieldset>
	</div>

	<div class="col options-section">
		<?php echo JHtml::_('sliders.start', 'weblink-sliders-'.$this->item->id, array('useCookie' => 1)); ?>

		<?php echo JHtml::_('sliders.panel', JText::_('JGLOBAL_FIELDSET_PUBLISHING'), 'publishing-details'); ?>

		<fieldset class="panelform">
			<legend class="element-invisible"><?php echo JText::_('JGLOBAL_FIELDSET_PUBLISHING'); ?></legend>
			<ul class="adminformlist">

				<li><?php echo $this->form->getLabel('created_by'); ?>
				<?php echo $this->form->getInput('created_by'); ?></li>

				<li><?php echo $this->form->getLabel('created_by_alias'); ?>
				<?php echo $this->form->getInput('created_by_alias'); ?></li>

				<li><?php echo $this->form->getLabel('created'); ?>
				<?php echo $this->form->getInput('created'); ?></li>

				<li><?php echo $this->form->getLabel('publish_up'); ?>
				<?php echo $this->form->getInput('publish_up'); ?></li>

				<li><?php echo $this->form->getLabel('publish_down'); ?>
				<?php echo $this->form->getInput('publish_down'); ?></li>

				<?php if ($this->item->modified_user_id) : ?>
					<li><?php echo $this->form->getLabel('modified_user_id'); ?>
					<?php echo $this->form->getInput('modified_user_id'); ?></li>

					<li><?php echo $this->form->getLabel('modified'); ?>
					<?php echo $this->form->getInput('modified'); ?></li>
				<?php endif; ?>

				<?php if ($this->item->hits) : ?>
					<li><?php echo $this->form->getLabel('hits'); ?>
					<?php echo $this->form->getInput('hits'); ?></li>
				<?php endif; ?>

			</ul>
		</fieldset>

		<?php echo $this->loadTemplate('options'); ?>

		<?php echo $this->loadTemplate('metadata'); ?>

		<?php echo JHtml::_('sliders.end'); ?>

		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
	<div class="clr"></div>
</form>
</div>

