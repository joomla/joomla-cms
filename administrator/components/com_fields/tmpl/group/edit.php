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
use Joomla\CMS\HTML\HTMLHelper;

// Include the component HTML helpers.
HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.tabstate');
HTMLHelper::_('formbehavior.chosen', '.advancedSelect');

$app = Factory::getApplication();
$input = $app->input;
?>

<form action="<?php echo Route::_('index.php?option=com_fields&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
	<?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>
	<div class="form-horizontal">
		<?php echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>
		<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'general', Text::_('COM_FIELDS_VIEW_FIELD_FIELDSET_GENERAL', true)); ?>
		<div class="row">
			<div class="col-md-9">
				<?php echo $this->form->renderField('label'); ?>
				<?php echo $this->form->renderField('description'); ?>
			</div>
			<div class="col-md-3">
				<?php $this->set('fields',
						array(
							array(
								'published',
								'state',
								'enabled',
							),
							'access',
							'language',
							'note',
						)
				); ?>
				<?php echo LayoutHelper::render('joomla.edit.global', $this); ?>
				<?php $this->set('fields', null); ?>
			</div>
		</div>
		<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
		<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'publishing', Text::_('JGLOBAL_FIELDSET_PUBLISHING', true)); ?>
		<div class="row form-horizontal-desktop">
			<div class="col-md-6">
				<?php echo LayoutHelper::render('joomla.edit.publishingdata', $this); ?>
			</div>
			<div class="col-md-6">
			</div>
		</div>
		<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
		<?php $this->set('ignore_fieldsets', array('fieldparams')); ?>
		<?php echo LayoutHelper::render('joomla.edit.params', $this); ?>
		<?php if ($this->canDo->get('core.admin')) : ?>
			<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'rules', Text::_('JGLOBAL_ACTION_PERMISSIONS_LABEL', true)); ?>
			<?php echo $this->form->getInput('rules'); ?>
			<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
		<?php endif; ?>
		<?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>
		<?php echo $this->form->getInput('context'); ?>
		<input type="hidden" name="task" value="">
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
