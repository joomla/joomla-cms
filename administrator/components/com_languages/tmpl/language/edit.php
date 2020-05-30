<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

HTMLHelper::_('script', 'com_languages/admin-language-edit-change-flag.js', ['version' => 'auto', 'relative' => true]);
?>

<form action="<?php echo Route::_('index.php?option=com_languages&view=language&layout=edit&lang_id=' . (int) $this->item->lang_id); ?>" method="post" name="adminForm" id="language-form" class="form-validate">

	<h2 class="my-4 text-primary"><?php echo $this->form->getValue('title'); ?></h2>

	<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'details')); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_('JDETAILS')); ?>
			<fieldset id="fieldset-details" class="options-form">
				<legend><?php echo Text::_('JDETAILS'); ?></legend>
				<div>
				<?php echo $this->form->renderField('title'); ?>
				<?php echo $this->form->renderField('title_native'); ?>
				<?php echo $this->form->renderField('lang_code'); ?>
				<?php echo $this->form->renderField('sef'); ?>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('image'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('image'); ?>
						<span id="flag">
							<?php echo HTMLHelper::_('image', 'mod_languages/' . $this->form->getValue('image') . '.gif', $this->form->getValue('image'), null, true); ?>
						</span>
					</div>
				</div>
				<?php if ($this->canDo->get('core.edit.state')) : ?>
					<?php echo $this->form->renderField('published'); ?>
				<?php endif; ?>

				<?php echo $this->form->renderField('access'); ?>
				<?php echo $this->form->renderField('description'); ?>
				<?php echo $this->form->renderField('lang_id'); ?>
				</div>
			</fieldset>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'metadata', Text::_('JGLOBAL_FIELDSET_OPTIONS')); ?>
			<div class="row">
				<div class="col-md-6 mb-3">
					<fieldset id="fieldset-sitename" class="options-form">
						<legend><?php echo Text::_('COM_LANGUAGES_FIELDSET_SITE_NAME_LABEL'); ?></legend>
						<div>
						<?php echo $this->form->renderFieldset('site_name'); ?>
						</div>
					</fieldset>
				</div>
				<div class="col-md-6 mb-3">
					<fieldset id="fieldset-metadata" class="options-form">
						<legend><?php echo Text::_('JGLOBAL_FIELDSET_METADATA_OPTIONS'); ?></legend>
						<div>
						<?php echo $this->form->renderFieldset('metadata'); ?>
						</div>
					</fieldset>
				</div>
			</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

	<?php echo HTMLHelper::_('uitab.endTabSet'); ?>

	<input type="hidden" name="task" value="">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
