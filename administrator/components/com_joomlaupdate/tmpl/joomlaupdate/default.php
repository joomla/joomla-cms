<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/** @var \Joomla\Component\Joomlaupdate\Administrator\View\Joomlaupdate\HtmlView $this */

HTMLHelper::_('behavior.core');
HTMLHelper::_('script', 'com_joomlaupdate/default.min.js', array('version' => 'auto', 'relative' => true));
Text::script('COM_INSTALLER_MSG_INSTALL_PLEASE_SELECT_A_PACKAGE', true);
Text::script('JYES');
Text::script('JNO');
Text::script('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_VERSION_MISSING');
Text::script('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_WARNING_UNKNOWN');

$latestJoomlaVersion = $this->updateInfo['latest'];
?>

<div id="joomlaupdate-wrapper" class="mt-3" data-joomla-target-version="<?php echo $latestJoomlaVersion; ?>">
	<?php if ($this->showUploadAndUpdate) : ?>
		<?php echo HTMLHelper::_('uitab.startTabSet', 'joomlaupdate-tabs', array('active' => $this->shouldDisplayPreUpdateCheck() ? 'pre-update-check' : 'online-update')); ?>
		<?php if ($this->shouldDisplayPreUpdateCheck()) : ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'joomlaupdate-tabs', 'pre-update-check', Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_TAB_PRE_UPDATE_CHECK')); ?>
			<?php echo $this->loadTemplate('preupdatecheck'); ?>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php endif; ?>
		<?php echo HTMLHelper::_('uitab.addTab', 'joomlaupdate-tabs', 'online-update', Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_TAB_ONLINE')); ?>
	<?php endif; ?>

	<form enctype="multipart/form-data" action="index.php" method="post" id="adminForm">

		<?php if ($this->selfUpdate) : ?>
			<?php // If we have a self update notice to install it first! ?>
			<?php Factory::getApplication()->enqueueMessage(Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_INSTALL_SELF_UPDATE_FIRST'), 'error'); ?>
			<?php echo $this->loadTemplate('updatemefirst'); ?>
		<?php else : ?>
			<?php if ((!isset($this->updateInfo['object']->downloadurl->_data)
				&& !$this->updateInfo['hasUpdate'])) : ?>
				<?php // If we have no download URL and this is also not a new update at all ?>
				<?php echo $this->loadTemplate('noupdate'); ?>
			<?php elseif (!isset($this->updateInfo['object']->downloadurl->_data)
				|| !$this->getModel()->isDatabaseTypeSupported()
				|| !$this->getModel()->isPhpVersionSupported()) : ?>
				<?php // If we have no download URL or our PHP version or our DB type is not supported then we can't reinstall or update ?>
				<?php echo $this->loadTemplate('nodownload'); ?>
			<?php elseif (!$this->updateInfo['hasUpdate']) : ?>
				<?php // If we have no update but we have a downloadurl then we can reinstall the core ?>
				<?php echo $this->loadTemplate('reinstall'); ?>
			<?php else : ?>
				<?php // Ok let's show the update template ?>
				<?php echo $this->loadTemplate('update'); ?>
			<?php endif; ?>
		<?php endif; ?>

		<input type="hidden" name="task" value="update.download">
		<input type="hidden" name="option" value="com_joomlaupdate">

		<?php echo HTMLHelper::_('form.token'); ?>
	</form>

	<?php // Only Super Users have access to the Update & Install for obvious security reasons ?>
	<?php if ($this->showUploadAndUpdate) : ?>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php echo HTMLHelper::_('uitab.addTab', 'joomlaupdate-tabs', 'upload-update', Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_TAB_UPLOAD')); ?>
		<?php echo $this->loadTemplate('upload'); ?>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>
		<?php echo HTMLHelper::_('uitab.endTabSet'); ?>
	<?php endif; ?>

	<div id="download-message" class="hidden">
		<p class="nowarning"><?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_DOWNLOAD_IN_PROGRESS'); ?></p>
		<div class="joomlaupdate_spinner"></div>
	</div>
	<div id="loading"></div>
</div>
