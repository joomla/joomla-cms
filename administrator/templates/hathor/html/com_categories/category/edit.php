<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	Templates.hathor
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.6
 */

// no direct access
defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
?>

<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'category.cancel' || document.formvalidator.isValid(document.id('item-form'))) {
			<?php echo $this->form->getField('description')->save(); ?>
			Joomla.submitform(task, document.getElementById('item-form'));
		} else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>

<div class="category-edit">

<form action="<?php echo JRoute::_('index.php?option=com_categories&extension='.JRequest::getCmd('extension', 'com_content').'&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
	<div class="col main-section">
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_CATEGORIES_FIELDSET_DETAILS');?></legend>
			<ul class="adminformlist">
				<li><?php echo $this->form->getLabel('title'); ?>
				<?php echo $this->form->getInput('title'); ?></li>

				<li><?php echo $this->form->getLabel('alias'); ?>
				<?php echo $this->form->getInput('alias'); ?></li>

				<li><?php echo $this->form->getLabel('extension'); ?>
				<?php echo $this->form->getInput('extension'); ?></li>

				<li><?php echo $this->form->getLabel('parent_id'); ?>
				<?php echo $this->form->getInput('parent_id'); ?></li>

				<li><?php echo $this->form->getLabel('published'); ?>
				<?php echo $this->form->getInput('published'); ?></li>

				<li><?php echo $this->form->getLabel('access'); ?>
				<?php echo $this->form->getInput('access'); ?></li>

				<?php if ($this->canDo->get('core.admin')): ?>
					<li><span class="faux-label"><?php echo JText::_('JGLOBAL_ACTION_PERMISSIONS_LABEL'); ?></span>
      					<button type="button" onclick="document.location.href='#access-rules';">
      					<?php echo JText::_('JGLOBAL_PERMISSIONS_ANCHOR'); ?></button>
    				</li>
    			<?php endif; ?>

				<li><?php echo $this->form->getLabel('language'); ?>
				<?php echo $this->form->getInput('language'); ?></li>

				<li><?php echo $this->form->getLabel('id'); ?>
				<?php echo $this->form->getInput('id'); ?></li>
			</ul>

			<div class="clr"></div>
			<?php echo $this->form->getLabel('description'); ?>
			<div class="clr"></div>
			<?php echo $this->form->getInput('description'); ?>
			<div class="clr"></div>
		</fieldset>
	</div>

	<div class="col options-section">

		<?php echo JHtml::_('sliders.start', 'categories-sliders-'.$this->item->id, array('useCookie'=>1)); ?>
			<?php echo $this->loadTemplate('options'); ?>
			<div class="clr"></div>

			<?php echo JHtml::_('sliders.panel', JText::_('JGLOBAL_FIELDSET_METADATA_OPTIONS'), 'meta-options'); ?>
			<fieldset class="panelform">
				<legend class="element-invisible"><?php echo JText::_('JGLOBAL_FIELDSET_METADATA_OPTIONS'); ?></legend>
				<?php echo $this->loadTemplate('metadata'); ?>
			</fieldset>

	<?php  $fieldSets = $this->form->getFieldsets('attribs'); ?>
		<?php foreach ($fieldSets as $name => $fieldSet) : ?>
			<?php $label = !empty($fieldSet->label) ? $fieldSet->label : 'COM_CATEGORIES_'.$name.'_FIELDSET_LABEL'; ?>
			<?php if ($name != 'editorConfig' && $name != 'basic-limited') : ?>
				<?php echo JHtml::_('sliders.panel', JText::_($label), $name.'-options'); ?>
				<?php if (isset($fieldSet->description) && trim($fieldSet->description)) : ?>
					<p class="tip"><?php echo $this->escape(JText::_($fieldSet->description));?></p>
				<?php endif; ?>
				<fieldset class="panelform">
					<ul class="adminformlist">
					<?php foreach ($this->form->getFieldset($name) as $field) : ?>
						<li><?php echo $field->label; ?>
						<?php echo $field->input; ?></li>
					<?php endforeach; ?>
					</ul>
				</fieldset>
			<?php endif ?>
		<?php endforeach; ?>
		<?php echo JHtml::_('sliders.end'); ?>
	</div>
	<div class="clr"></div>

	<?php if ($this->canDo->get('core.admin')): ?>
		<div  class="col rules-section">

			<?php echo JHtml::_('sliders.start', 'permissions-sliders-'.$this->item->id, array('useCookie'=>1)); ?>

			<?php echo JHtml::_('sliders.panel', JText::_('COM_CATEGORIES_FIELDSET_RULES'), 'access-rules'); ?>
			<fieldset class="panelform">
			<legend class="element-invisible"><?php echo JText::_('COM_CATEGORIES_FIELDSET_RULES'); ?></legend>
				<?php echo $this->form->getLabel('rules'); ?>
				<?php echo $this->form->getInput('rules'); ?>
			</fieldset>

			<?php echo JHtml::_('sliders.end'); ?>
		</div>
	<?php endif; ?>
	<div>
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
<div class="clr"></div>
</div>
