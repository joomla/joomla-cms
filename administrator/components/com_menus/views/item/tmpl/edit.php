<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.core');
JHtml::_('behavior.tabstate');
JHtml::_('behavior.formvalidator');
JHtml::_('formbehavior.chosen', 'select');

JText::script('ERROR');
JText::script('JGLOBAL_VALIDATION_FORM_FAILED');

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

<form action="<?php echo JRoute::_('index.php?option=com_menus&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">

	<?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<div class="form-horizontal">

		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', JText::_('COM_MENUS_ITEM_DETAILS', true)); ?>
		<div class="row-fluid">
			<div class="span9">
				<?php
				if ($this->item->type == 'alias')
				{
					echo $this->form->getControlGroup('aliastip');
				}

				echo $this->form->getControlGroup('type');

				if ($this->item->type == 'alias')
				{
					echo $this->form->getControlGroups('aliasoptions');
				}

				echo $this->form->getControlGroups('request');

				if ($this->item->type == 'url')
				{
					$this->form->setFieldAttribute('link', 'readonly', 'false');
				}

				echo $this->form->getControlGroup('link');

				echo $this->form->getControlGroup('browserNav');
				echo $this->form->getControlGroup('template_style_id');
				?>
			</div>
			<div class="span3">
				<?php
				// Set main fields.
				$this->fields = array(
					'menutype',
					'parent_id',
					'menuordering',
					'published',
					'home',
					'access',
					'language',
					'note'

				);

				if ($this->item->type != 'component')
				{
					$this->fields = array_diff($this->fields, array('home'));
				}
				?>
				<?php echo JLayoutHelper::render('joomla.edit.global', $this); ?>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php
		$this->fieldsets = array();
		$this->ignore_fieldsets = array('aliasoptions', 'request', 'item_associations');
		echo JLayoutHelper::render('joomla.edit.params', $this);
		?>

		<?php if ($assoc) : ?>
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'associations', JText::_('JGLOBAL_FIELDSET_ASSOCIATIONS', true)); ?>
			<?php echo $this->loadTemplate('associations'); ?>
			<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php endif; ?>

		<?php if (!empty($this->modules)) : ?>
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'modules', JText::_('COM_MENUS_ITEM_MODULE_ASSIGNMENT', true)); ?>
			<?php echo $this->loadTemplate('modules'); ?>
			<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php endif; ?>

		<?php echo JHtml::_('bootstrap.endTabSet'); ?>
	</div>

	<input type="hidden" name="task" value="" />
	<?php echo $this->form->getInput('component_id'); ?>
	<?php echo JHtml::_('form.token'); ?>
	<input type="hidden" id="fieldtype" name="fieldtype" value="" />
</form>
