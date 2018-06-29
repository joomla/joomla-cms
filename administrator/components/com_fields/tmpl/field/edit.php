<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tabstate');
JHtml::_('formbehavior.chosen', '#jform_catid', null, array('disable_search_threshold' => 0 ));

$app = JFactory::getApplication();
$input = $app->input;

HTMLHelper::_('script', 'com_fields/admin-field-edit.js', ['relative' => true, 'version' => 'auto']);
?>

<form action="<?php echo Route::_('index.php?option=com_fields&context=' . $input->getCmd('context', 'com_content') . '&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">

	<?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>
	<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', Text::_('COM_FIELDS_VIEW_FIELD_FIELDSET_GENERAL', true)); ?>
	<div class="row">
		<div class="col-md-9">
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
		<div class="col-md-3">
			<div class="card card-light">
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
	<?php echo JHtml::_('bootstrap.endTab'); ?>
	<?php $this->set('ignore_fieldsets', array('fieldparams')); ?>
	<?php echo LayoutHelper::render('joomla.edit.params', $this); ?>
	<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', Text::_('JGLOBAL_FIELDSET_PUBLISHING', true)); ?>
	<div class="row">
		<div class="col-md-6">
			<?php echo LayoutHelper::render('joomla.edit.publishingdata', $this); ?>
		</div>
		<div class="col-md-6">
		</div>
	</div>
	<?php echo JHtml::_('bootstrap.endTab'); ?>
	<?php if ($this->canDo->get('core.admin')) : ?>
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'rules', Text::_('JGLOBAL_ACTION_PERMISSIONS_LABEL', true)); ?>
		<?php echo $this->form->getInput('rules'); ?>
		<?php echo JHtml::_('bootstrap.endTab'); ?>
	<?php endif; ?>
	<?php echo JHtml::_('bootstrap.endTabSet'); ?>
	<?php echo $this->form->getInput('context'); ?>
	<input type="hidden" name="task" value="">
	<?php echo JHtml::_('form.token'); ?>
</form>
