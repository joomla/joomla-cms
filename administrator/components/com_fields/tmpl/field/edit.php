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

$app = Factory::getApplication();
$input = $app->input;

$this->useCoreUI = true;

HTMLHelper::_('script', 'com_fields/admin-field-edit.js', ['version' => 'auto', 'relative' => true]);
?>

<form action="<?php echo Route::_('index.php?option=com_fields&context=' . $input->getCmd('context', 'com_content') . '&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">

	<?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'general')); ?>
	<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('COM_FIELDS_VIEW_FIELD_FIELDSET_GENERAL', true)); ?>
	<div class="row">
		<div class="col-lg-9">
			<div class="card">
				<div class="card-body">
				<?php echo $this->form->renderField('type'); ?>
				<?php echo $this->form->renderField('name'); ?>
				<?php echo $this->form->renderField('label'); ?>
				<?php echo $this->form->renderField('description'); ?>
				<?php echo $this->form->renderField('required'); ?>
				<?php echo $this->form->renderField('default_value'); ?>

				<?php foreach ($this->form->getFieldsets('fieldparams') as $name => $fieldSet) : ?>
					<?php foreach ($this->form->getFieldset($name) as $field) : ?>
						<?php echo $field->renderField(); ?>
					<?php endforeach; ?>
				<?php endforeach; ?>
				</div>
			</div>
		</div>
		<div class="col-lg-3">
			<div class="card">
				<div class="card-body">
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
					<?php echo LayoutHelper::render('joomla.edit.global', $this); ?>
					<?php $this->set('fields', null); ?>
				</div>
			</div>
		</div>
	</div>
	<?php echo HTMLHelper::_('uitab.endTab'); ?>
	<?php $this->set('ignore_fieldsets', array('fieldparams')); ?>
	<?php echo LayoutHelper::render('joomla.edit.params', $this); ?>
	<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'publishing', Text::_('JGLOBAL_FIELDSET_PUBLISHING', true)); ?>
	<div class="row">
		<div class="col-md-6">
			<fieldset id="fieldset-publishingdata" class="options-form">
				<legend><?php echo Text::_('JGLOBAL_FIELDSET_PUBLISHING'); ?></legend>
				<div>
				<?php echo LayoutHelper::render('joomla.edit.publishingdata', $this); ?>
				</div>
			</fieldset>
		</div>
		<div class="col-md-6">
		</div>
	</div>
	<?php echo HTMLHelper::_('uitab.endTab'); ?>
	<?php if ($this->canDo->get('core.admin')) : ?>
		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'rules', Text::_('JGLOBAL_ACTION_PERMISSIONS_LABEL', true)); ?>
			<fieldset id="fieldset-rules" class="options-form">
				<legend><?php echo Text::_('JGLOBAL_ACTION_PERMISSIONS_LABEL'); ?></legend>
				<div>
				<?php echo $this->form->getInput('rules'); ?>
				</div>
			</fieldset>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>
	<?php endif; ?>
	<?php echo HTMLHelper::_('uitab.endTabSet'); ?>
	<?php echo $this->form->getInput('context'); ?>
	<input type="hidden" name="task" value="">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
