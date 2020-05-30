<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('script', 'com_fields/admin-field-edit-modal.min.js', ['relative' => true, 'version' => 'auto']);

$this->useCoreUI = true;
?>
<div class="container-popup">

	<div class="float-right">
		<button class="btn btn-primary" type="button" onclick="Joomla.submitbutton('field.apply');"><?php echo Text::_('JTOOLBAR_APPLY') ?></button>
		<button class="btn btn-primary" type="button" onclick="Joomla.submitbutton('field.save');"><?php echo Text::_('JTOOLBAR_SAVE') ?></button>
		<button class="btn" type="button" onclick="Joomla.submitbutton('field.cancel');"><?php echo Text::_('JCANCEL') ?></button>
	</div>

	<hr>

	<form action="<?php echo Route::_('index.php?option=com_fields&context=' . Factory::getApplication()->input->getCmd('context', 'com_content') . '&layout=modal&tmpl=component&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
		<?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

		<div>
			<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'general')); ?>

			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('COM_FIELDS', true)); ?>
			<div class="row">
				<div class="col-lg-9">
					<?php echo $this->form->getLabel('description'); ?>
					<?php echo $this->form->getInput('description'); ?>
				</div>
				<div class="col-lg-3">
					<?php echo LayoutHelper::render('joomla.edit.global', $this); ?>
				</div>
			</div>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>

			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'publishing', Text::_('COM_FIELDS_FIELDSET_PUBLISHING', true)); ?>
			<div class="row">
				<div class="col-md-6">
					<?php echo LayoutHelper::render('joomla.edit.publishingdata', $this); ?>
				</div>
				<div class="col-md-6">
				</div>
			</div>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>

			<?php if ($this->canDo->get('core.admin')) : ?>
				<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'rules', Text::_('COM_FIELDS_FIELDSET_RULES', true)); ?>
				<?php echo $this->form->getInput('rules'); ?>
				<?php echo HTMLHelper::_('uitab.endTab'); ?>
			<?php endif; ?>

			<?php echo LayoutHelper::render('joomla.edit.params', $this); ?>

			<?php echo HTMLHelper::_('uitab.endTabSet'); ?>

			<?php echo $this->form->getInput('context'); ?>
			<input type="hidden" name="task" value="">
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
	</form>
</div>
