<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.hathor
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

JHtml::_('behavior.framework');
JHtml::_('behavior.formvalidator');
JHtml::_('behavior.modal');

$assoc = JLanguageAssociations::isEnabled();

// Ajax for parent items
$script = "
jQuery(document).ready(function ($){
	$('#jform_menutype').change(function(){
		var menutype = $(this).val();
		$.ajax({
			url: 'index.php?option=com_menus&task=item.getParentItem&menutype=' + menutype,
			dataType: 'json'
		}).done(function(data) {
			$('#jform_parent_id option').each(function() {
				if ($(this).val() != '1') {
					$(this).remove();
				}
			});
			$.each(data, function (i, val) {
				var option = $('<option>');
				option.text(val.title).val(val.id);
				$('#jform_parent_id').append(option);
			});
			$('#jform_parent_id').trigger('liszt:updated');
		});
	});
});
Joomla.submitbutton = function(task, type){
	if (task == 'item.setType' || task == 'item.setMenuType')
	{
		if (task == 'item.setType')
		{
			jQuery('#item-form input[name=\"jform[type]\"]').val(type);
			jQuery('#fieldtype').val('type');
		} else {
			jQuery('#item-form input[name=\"jform[menutype]\"]').val(type);
		}
		Joomla.submitform('item.setType', document.getElementById('item-form'));
	} else if (task == 'item.cancel' || document.formvalidator.isValid(document.getElementById('item-form')))
	{
		Joomla.submitform(task, document.getElementById('item-form'));
	}
	else
	{
		// special case for modal popups validation response
		jQuery('#item-form .modal-value.invalid').each(function(){
			var field = jQuery(this),
				idReversed = field.attr('id').split('').reverse().join(''),
				separatorLocation = idReversed.indexOf('_'),
				nameId = '#' + idReversed.substr(separatorLocation).split('').reverse().join('') + 'name';
			jQuery(nameId).addClass('invalid');
		});
	}
};
";
// Add the script to the document head.
JFactory::getDocument()->addScriptDeclaration($script);

?>

<div class="menuitem-edit">

<form action="<?php echo JRoute::_('index.php?option=com_menus&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">

<div class="col main-section">
	<fieldset class="adminform">
		<legend><?php echo JText::_('COM_MENUS_ITEM_DETAILS');?></legend>
			<ul class="adminformlist">

				<li><?php echo $this->form->getLabel('type'); ?>
				<?php echo $this->form->getInput('type'); ?></li>

				<li><?php echo $this->form->getLabel('title'); ?>
				<?php echo $this->form->getInput('title'); ?></li>

				<?php if ($this->item->type == 'url') : ?>
					<?php $this->form->setFieldAttribute('link', 'readonly', 'false');?>
					<li><?php echo $this->form->getLabel('link'); ?>
					<?php echo $this->form->getInput('link'); ?></li>
				<?php endif; ?>

				<?php if ($this->item->type != 'url') : ?>
					<li><?php echo $this->form->getLabel('alias'); ?>
					<?php echo $this->form->getInput('alias'); ?></li>
				<?php endif; ?>

				<li><?php echo $this->form->getLabel('note'); ?>
				<?php echo $this->form->getInput('note'); ?></li>

				<?php if ($this->item->type !== 'url') : ?>
					<li><?php echo $this->form->getLabel('link'); ?>
					<?php echo $this->form->getInput('link'); ?></li>
				<?php endif ?>

				<?php if ($this->canDo->get('core.edit.state')) : ?>
					<li><?php echo $this->form->getLabel('published'); ?>
					<?php echo $this->form->getInput('published'); ?></li>
				<?php endif ?>

				<li><?php echo $this->form->getLabel('access'); ?>
				<?php echo $this->form->getInput('access'); ?></li>

				<li><?php echo $this->form->getLabel('menutype'); ?>
				<?php echo $this->form->getInput('menutype'); ?></li>

				<li><?php echo $this->form->getLabel('parent_id'); ?>
				<?php echo $this->form->getInput('parent_id'); ?></li>

				<li><?php echo $this->form->getLabel('menuordering'); ?>
				<?php echo $this->form->getInput('menuordering'); ?></li>

				<li><?php echo $this->form->getLabel('browserNav'); ?>
				<?php echo $this->form->getInput('browserNav'); ?></li>

				<?php if ($this->canDo->get('core.edit.state')) : ?>
					<?php if ($this->item->type == 'component') : ?>
					<li><?php echo $this->form->getLabel('home'); ?>
					<?php echo $this->form->getInput('home'); ?></li>
					<?php endif; ?>
				<?php endif; ?>

				<li><?php echo $this->form->getLabel('language'); ?>
				<?php echo $this->form->getInput('language'); ?></li>

				<li><?php echo $this->form->getLabel('template_style_id'); ?>
				<?php echo $this->form->getInput('template_style_id'); ?></li>

				<li><?php echo $this->form->getLabel('id'); ?>
				<?php echo $this->form->getInput('id'); ?></li>
		</ul>

	</fieldset>
</div>

<div class="col options-section">
	<?php echo JHtml::_('sliders.start', 'menu-sliders-'.$this->item->id); ?>
	<?php //Load  parameters.
		echo $this->loadTemplate('options'); ?>

		<div class="clr"></div>

		<?php if (!empty($this->modules)) : ?>
			<?php echo JHtml::_('sliders.panel', JText::_('COM_MENUS_ITEM_MODULE_ASSIGNMENT'), 'module-options'); ?>
			<fieldset>
				<?php echo $this->loadTemplate('modules'); ?>
			</fieldset>
		<?php endif; ?>

	<?php echo JHtml::_('sliders.end'); ?>

	<input type="hidden" name="task" value="" />
	<?php echo $this->form->getInput('component_id'); ?>
	<?php echo JHtml::_('form.token'); ?>
	<input type="hidden" id="fieldtype" name="fieldtype" value="" />
</div>
</form>

<div class="clr"></div>
</div>
