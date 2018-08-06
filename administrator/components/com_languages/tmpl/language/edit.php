<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.tabstate');

HTMLHelper::_('script', 'com_languages/admin-language-edit-change-flag.js', ['relative' => true, 'version' => 'auto']);
?>

<form action="<?php echo Route::_('index.php?option=com_languages&view=language&layout=edit&lang_id=' . (int) $this->item->lang_id); ?>" method="post" name="adminForm" id="language-form" class="form-validate">

	<?php echo LayoutHelper::render('joomla.edit.item_title', $this); ?>

	<fieldset>
	<?php echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>

		<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'details', Text::_('JDETAILS')); ?>
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
		<?php echo HTMLHelper::_('bootstrap.endTab'); ?>

		<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'metadata', Text::_('JGLOBAL_FIELDSET_METADATA_OPTIONS')); ?>
		<?php echo $this->form->renderFieldset('metadata'); ?>
		<?php echo HTMLHelper::_('bootstrap.endTab'); ?>

		<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'site_name', Text::_('COM_LANGUAGES_FIELDSET_SITE_NAME_LABEL')); ?>
		<?php echo $this->form->renderFieldset('site_name'); ?>
		<?php echo HTMLHelper::_('bootstrap.endTab'); ?>

	<?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>
	</fieldset>
	<input type="hidden" name="task" value="">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
