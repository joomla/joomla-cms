<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

$app = JFactory::getApplication();
$input = $app->input;

$assoc = JLanguageAssociations::isEnabled();

JFactory::getDocument()->addScriptDeclaration("
	Joomla.submitbutton = function(task)
	{
		if (task == 'category.cancel' || document.formvalidator.isValid(document.getElementById('item-form')))
		{
			" . $this->form->getField('description')->save() . "

			if (window.opener && (task == 'category.save' || task == 'category.cancel'))
			{
				window.opener.document.closeEditWindow = self;
				window.opener.setTimeout('window.document.closeEditWindow.close()', 1000);
			}

			Joomla.submitform(task, document.getElementById('item-form'));
		}
	};
");

// Fieldsets to not automatically render by /layouts/joomla/edit/params.php
$this->ignore_fieldsets = array('jmetadata', 'item_associations');
?>
<div class="container-popup">

	<div class="pull-right">
		<button class="btn btn-primary" type="button" onclick="Joomla.submitbutton('category.apply');"><?php echo JText::_('JTOOLBAR_APPLY') ?></button>
		<button class="btn btn-primary" type="button" onclick="Joomla.submitbutton('category.save');"><?php echo JText::_('JTOOLBAR_SAVE') ?></button>
		<button class="btn" type="button" onclick="Joomla.submitbutton('category.cancel');"><?php echo JText::_('JCANCEL') ?></button>
	</div>

	<div class="clearfix"></div>
	<hr class="hr-condensed" />

	<form action="<?php echo JRoute::_('index.php?option=com_categories&extension=' . $input->getCmd('extension', 'com_content') . '&layout=modal&tmpl=component&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate form-horizontal">
		<?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>

		<div class="form-horizontal">
			<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('JCATEGORY', true)); ?>
			<div class="row-fluid">
				<div class="span9">
					<?php echo $this->form->getLabel('description'); ?>
					<?php echo $this->form->getInput('description'); ?>
				</div>
				<div class="span3">
					<?php echo JLayoutHelper::render('joomla.edit.global', $this); ?>
				</div>
			</div>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('COM_CATEGORIES_FIELDSET_PUBLISHING', true)); ?>
			<div class="row-fluid form-horizontal-desktop">
				<div class="span6">
					<?php echo JLayoutHelper::render('joomla.edit.publishingdata', $this); ?>
				</div>
				<div class="span6">
					<?php echo JLayoutHelper::render('joomla.edit.metadata', $this); ?>
				</div>
			</div>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php if ($assoc) : ?>
				<div class="hidden"><?php echo $this->loadTemplate('associations'); ?></div>
			<?php endif; ?>

			<?php if ($this->canDo->get('core.admin')) : ?>
				<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'rules', JText::_('COM_CATEGORIES_FIELDSET_RULES', true)); ?>
				<?php echo $this->form->getInput('rules'); ?>
				<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php endif; ?>

			<?php echo JLayoutHelper::render('joomla.edit.params', $this); ?>

			<?php echo JHtml::_('bootstrap.endTabSet'); ?>

			<?php echo $this->form->getInput('extension'); ?>
			<input type="hidden" name="task" value="" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>
