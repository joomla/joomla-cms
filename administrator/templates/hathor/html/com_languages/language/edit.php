<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_languages
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.DS.'helpers'.DS.'html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
$canDo = LanguagesHelper::getActions();
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'language.cancel' || document.formvalidator.isValid(document.id('language-form'))) {
			Joomla.submitform(task, document.getElementByID('language-form'));
		}
	}
</script>

<form action="<?php JRoute::_('index.php?option=com_languages'); ?>" method="post" name="adminForm" id="language-form" class="form-validate">
	<div class="col main-section">
		<fieldset class="adminform">
		<legend><?php echo JText::sprintf('JGLOBAL_RECORD_NUMBER', $this->item->lang_id); ?></legend>
			<?php if ($this->item->lang_id) : ?>
				<legend><?php echo JText::sprintf('JGLOBAL_RECORD_NUMBER', $this->item->lang_id); ?></legend>
			<?php endif; ?>

			<?php echo $this->form->getLabel('title'); ?>
			<?php echo $this->form->getInput('title'); ?>

			<?php echo $this->form->getLabel('title_native'); ?>
			<?php echo $this->form->getInput('title_native'); ?>

			<?php echo $this->form->getLabel('sef'); ?>
			<?php echo $this->form->getInput('sef'); ?>

			<?php echo $this->form->getLabel('image'); ?>
			<?php echo $this->form->getInput('image'); ?>

			<?php echo $this->form->getLabel('lang_code'); ?>
			<?php echo $this->form->getInput('lang_code'); ?>

			<?php if ($canDo->get('core.edit.state')) : ?>
				<?php echo $this->form->getLabel('published'); ?>
				<?php echo $this->form->getInput('published'); ?>
			<?php endif ?>
			
			<?php echo $this->form->getLabel('description'); ?>
			<?php echo $this->form->getInput('description'); ?>

			<?php echo $this->form->getLabel('lang_id'); ?>
			<?php echo $this->form->getInput('lang_id'); ?>
		</fieldset>
	</div>
	<div class="col options-section">
		<?php echo JHtml::_('sliders.start','language-sliders-'.$this->item->lang_code, array('useCookie'=>1)); ?>

		<?php echo JHtml::_('sliders.panel',JText::_('JGLOBAL_FIELDSET_METADATA_OPTIONS'), 'metadata'); ?>
			<fieldset class="panelform">
			<legend class="element-invisible"><?php echo JText::_('JGLOBAL_FIELDSET_METADATA_OPTIONS'); ?></legend>
			<ul class="adminformlist">
				<?php foreach($this->form->getFieldset('metadata') as $field): ?>
					<li>
						<?php if (!$field->hidden): ?>
							<?php echo $field->label; ?>
						<?php endif; ?>
						<?php echo $field->input; ?>
					</li>
				<?php endforeach; ?>
			</fieldset>

		<?php echo JHtml::_('sliders.end'); ?>
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
	<div class="clr"> </div>
</form>
