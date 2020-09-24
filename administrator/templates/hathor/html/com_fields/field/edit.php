<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', '#jform_catid', null, array('disable_search_threshold' => 0 ));

$app = JFactory::getApplication();
$input = $app->input;

JFactory::getDocument()->addScriptDeclaration('
	Joomla.submitbutton = function(task)
	{
		if (task == "field.cancel" || document.formvalidator.isValid(document.getElementById("item-form")))
		{
			Joomla.submitform(task, document.getElementById("item-form"));
		}
	};
	jQuery(document).ready(function() {
		jQuery("#jform_title").data("dp-old-value", jQuery("#jform_title").val());
		jQuery("#jform_title").change(function(data, handler) {
			if(jQuery("#jform_title").data("dp-old-value") == jQuery("#jform_label").val()) {
				jQuery("#jform_label").val(jQuery("#jform_title").val());
			}

			jQuery("#jform_title").data("dp-old-value", jQuery("#jform_title").val());
		});
	});
');
?>
<div class="fields-edit">
<form action="<?php echo JRoute::_('index.php?option=com_fields&context=' . $input->getCmd('context', 'com_content') . '&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
	<div class="col main-section">
		<fieldset class="adminform">
			<ul class="adminformlist">
				<li>
					<?php echo $this->form->getLabel('title'); ?>
					<?php echo $this->form->getInput('title'); ?>
				</li>
			</ul>
			<div class="clr"></div>
		</fieldset>
		<fieldset class="adminform">
			<ul class="adminformlist">
				<li><?php echo $this->form->renderField('type'); ?></li>
				<li><?php echo $this->form->renderField('name'); ?></li>
				<li><?php echo $this->form->renderField('label'); ?></li>
				<li><?php echo $this->form->renderField('description'); ?></li>
				<li><?php echo $this->form->renderField('required'); ?></li>
				<li><?php echo $this->form->renderField('default_value'); ?></li>

				<?php foreach ($this->form->getFieldsets('fieldparams') as $name => $fieldSet) : ?>
					<?php foreach ($this->form->getFieldset($name) as $field) : ?>
						<li><?php echo $field->renderField(); ?></li>
					<?php endforeach; ?>
				<?php endforeach; ?>
			</ul>
		</fieldset>

	</div>
	<div class="col options-section">
		<?php echo JHtml::_('sliders.start', 'groups-sliders-' . $this->item->id, array('useCookie' => 1)); ?>
		<?php echo JHtml::_('sliders.panel', JText::_('COM_FIELDS_VIEW_FIELD_FIELDSET_GENERAL'), 'general'); ?>
				<?php $this->set('fields',
						array(
							array(
								'published',
								'state',
								'enabled',
							),
							'group_id',
							'assigned_cat_ids',
							'access',
							'language',
							'note',
						)
				); ?>
				<?php echo JLayoutHelper::render('joomla.edit.global', $this); ?>
				<?php $this->set('fields', null); ?>

		<?php echo JHtml::_('sliders.panel', JText::_('JGLOBAL_FIELDSET_OPTIONS'), '-options'); ?>
		<?php $this->set('ignore_fieldsets', array('fieldparams')); ?>
		<?php echo JLayoutHelper::render('joomla.edit.params', $this); ?>

		<?php echo JHtml::_('sliders.panel', JText::_('JGLOBAL_FIELDSET_PUBLISHING'), 'publishing-details'); ?>
			<fieldset class="panelform">
			<legend class="element-invisible"><?php echo JText::_('JGLOBAL_FIELDSET_PUBLISHING'); ?></legend>
				<ul class="adminformlist">

					<li><?php echo $this->form->getLabel('created_user_id'); ?>
					<?php echo $this->form->getInput('created_user_id'); ?></li>

					<li><?php echo $this->form->getLabel('created_time'); ?>
					<?php echo $this->form->getInput('created_time'); ?></li>

					<?php if ($this->item->modified_by) : ?>
						<li><?php echo $this->form->getLabel('modified_by'); ?>
						<?php echo $this->form->getInput('modified_by'); ?></li>

						<li><?php echo $this->form->getLabel('modified_time'); ?>
						<?php echo $this->form->getInput('modified_time'); ?></li>
					<?php endif; ?>

					<li><?php echo $this->form->getLabel('id'); ?>
					<?php echo $this->form->getInput('id'); ?></li>

				</ul>
			</fieldset>
		<?php echo JHtml::_('sliders.end'); ?>
		<div class="clr"></div>
	</div>
	<div class="clr"></div>
		<?php if ($this->canDo->get('core.admin')) : ?>
			<div class="col rules-section">
				<?php echo JHtml::_('sliders.start', 'permissions-sliders-' . $this->item->id, array('useCookie' => 1)); ?>

				<?php echo JHtml::_('sliders.panel', JText::_('JGLOBAL_ACTION_PERMISSIONS_LABEL'), 'access-rules'); ?>
				<fieldset class="panelform">
					<legend class="element-invisible"><?php echo JText::_('JGLOBAL_ACTION_PERMISSIONS_LABEL'); ?></legend>
						<?php echo $this->form->getLabel('rules'); ?>
						<?php echo $this->form->getInput('rules'); ?>
				</fieldset>

				<?php echo JHtml::_('sliders.end'); ?>
			</div>
		<?php endif; ?>

		<?php echo $this->form->getInput('context'); ?>
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
<div class="clr"></div>
</div>
