<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

HTMLHelper::_('script', 'com_contenthistory/admin-history-versions.js', ['version' => 'auto', 'relative' => true]);

$app = Factory::getApplication();
$input = $app->input;

$assoc = Associations::isEnabled();
// Are associations implemented for this extension?
$extensionassoc = array_key_exists('item_associations', $this->form->getFieldsets());

// Fieldsets to not automatically render by /layouts/joomla/edit/params.php
$this->ignore_fieldsets = ['jmetadata', 'item_associations'];
$this->useCoreUI = true;

// In case of modal
$isModal = $input->get('layout') === 'modal';
$layout  = $isModal ? 'modal' : 'edit';
$tmpl    = $isModal || $input->get('tmpl', '', 'cmd') === 'component' ? '&tmpl=component' : '';
?>

<form action="<?php echo Route::_('index.php?option=com_categories&extension=' . $input->getCmd('extension', 'com_content') . '&layout=' . $layout . $tmpl . '&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">

	<?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<div>
		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'general')); ?>
		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('JCATEGORY')); ?>
		<div class="row">
			<div class="col-lg-9">
				<div class="card">
					<div class="card-body">
						<?php echo $this->form->getLabel('description'); ?>
						<?php echo $this->form->getInput('description'); ?>
					</div>
				</div>
			</div>
			<div class="col-lg-3">
				<div class="card card-block">
					<div class="card-body">
					<?php echo LayoutHelper::render('joomla.edit.global', $this); ?>
					</div>
				</div>
			</div>
		</div>

		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo LayoutHelper::render('joomla.edit.params', $this); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'publishing', Text::_('JGLOBAL_FIELDSET_PUBLISHING')); ?>
		<div class="row">
			<div class="col-12 col-lg-6">
				<fieldset id="fieldset-publishingdata" class="options-form">
					<legend><?php echo Text::_('JGLOBAL_FIELDSET_PUBLISHING'); ?></legend>
					<div>
					<?php echo LayoutHelper::render('joomla.edit.publishingdata', $this); ?>
					</div>
				</fieldset>
			</div>
			<div class="col-12 col-lg-6">
				<fieldset id="fieldset-metadata" class="options-form">
					<legend><?php echo Text::_('JGLOBAL_FIELDSET_METADATA_OPTIONS'); ?></legend>
					<div>
					<?php echo LayoutHelper::render('joomla.edit.metadata', $this); ?>
					</div>
				</fieldset>
			</div>
		</div>

		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php if (!$isModal && $assoc && $extensionassoc) : ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'associations', Text::_('JGLOBAL_FIELDSET_ASSOCIATIONS')); ?>
			<fieldset id="fieldset-associations" class="options-form">
			<legend><?php echo Text::_('JGLOBAL_FIELDSET_ASSOCIATIONS'); ?></legend>
			<div>
			<?php echo LayoutHelper::render('joomla.edit.associations', $this); ?>
			</div>
			</fieldset>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php elseif ($isModal && $assoc && $extensionassoc) : ?>
			<div class="hidden"><?php echo LayoutHelper::render('joomla.edit.associations', $this); ?></div>
		<?php endif; ?>

		<?php if ($this->canDo->get('core.admin')) : ?>

			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'rules', Text::_('COM_CATEGORIES_FIELDSET_RULES')); ?>
			<fieldset id="fieldset-rules" class="options-form">
				<legend><?php echo Text::_('COM_CATEGORIES_FIELDSET_RULES'); ?></legend>
				<div>
				<?php echo $this->form->getInput('rules'); ?>
				</div>
			</fieldset>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php endif; ?>

		<?php echo HTMLHelper::_('uitab.endTabSet'); ?>

		<?php echo $this->form->getInput('extension'); ?>
		<input type="hidden" name="task" value="">
		<input type="hidden" name="forcedLanguage" value="<?php echo $input->get('forcedLanguage', '', 'cmd'); ?>">
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
