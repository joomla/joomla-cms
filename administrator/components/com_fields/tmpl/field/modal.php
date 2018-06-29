<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 * 
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Factory;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', '.advancedSelect');

$app = Factory::getApplication();
$input = $app->input;

Factory::getDocument()->addScriptDeclaration("
	Joomla.submitbutton = function(task)
	{
		if (task == 'field.cancel' || document.formvalidator.isValid(document.getElementById('item-form')))
		{
			if (window.opener && (task == 'field.save' || task == 'field.cancel'))
			{
				window.opener.document.closeEditWindow = self;
				window.opener.setTimeout('window.document.closeEditWindow.close()', 1000);
			}

			Joomla.submitform(task, document.getElementById('item-form'));
		}
	};
");
?>
<div class="container-popup">

	<div class="float-right">
		<button class="btn btn-primary" type="button" onclick="Joomla.submitbutton('field.apply');"><?php echo Text::_('JTOOLBAR_APPLY') ?></button>
		<button class="btn btn-primary" type="button" onclick="Joomla.submitbutton('field.save');"><?php echo Text::_('JTOOLBAR_SAVE') ?></button>
		<button class="btn" type="button" onclick="Joomla.submitbutton('field.cancel');"><?php echo Text::_('JCANCEL') ?></button>
	</div>

	<hr>

	<form action="<?php echo Route::_('index.php?option=com_fields&context=' . $input->getCmd('context', 'com_content') . '&layout=modal&tmpl=component&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
		<?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

		<div>
			<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', Text::_('COM_FIELDS', true)); ?>
			<div class="row">
				<div class="col-md-9">
					<?php echo $this->form->getLabel('description'); ?>
					<?php echo $this->form->getInput('description'); ?>
				</div>
				<div class="col-md-3">
					<?php echo LayoutHelper::render('joomla.edit.global', $this); ?>
				</div>
			</div>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', Text::_('COM_FIELDS_FIELDSET_PUBLISHING', true)); ?>
			<div class="row">
				<div class="col-md-6">
					<?php echo LayoutHelper::render('joomla.edit.publishingdata', $this); ?>
				</div>
				<div class="col-md-6">
				</div>
			</div>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php if ($this->canDo->get('core.admin')) : ?>
				<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'rules', Text::_('COM_FIELDS_FIELDSET_RULES', true)); ?>
				<?php echo $this->form->getInput('rules'); ?>
				<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php endif; ?>

			<?php echo LayoutHelper::render('joomla.edit.params', $this); ?>

			<?php echo JHtml::_('bootstrap.endTabSet'); ?>

			<?php echo $this->form->getInput('context'); ?>
			<input type="hidden" name="task" value="">
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>
