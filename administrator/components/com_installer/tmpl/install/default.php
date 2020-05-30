<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.core');
Text::script('PLG_INSTALLER_PACKAGEINSTALLER_NO_PACKAGE');
Text::script('PLG_INSTALLER_FOLDERINSTALLER_NO_INSTALL_PATH');
Text::script('PLG_INSTALLER_URLINSTALLER_NO_URL');
Text::script('COM_INSTALLER_MSG_INSTALL_ENTER_A_URL');

HTMLHelper::_('stylesheet', 'com_installer/installer.css', ['version' => 'auto', 'relative' => true]);
HTMLHelper::_('script', 'com_installer/installer.js', ['version' => 'auto', 'relative' => true]);

$this->document->getWebAssetManager()
	->useScript('webcomponent.core-loader');

$app = Factory::getApplication();
?>

<div id="installer-install" class="clearfix">

	<form enctype="multipart/form-data" action="<?php echo Route::_('index.php?option=com_installer&view=install'); ?>" method="post" name="adminForm" id="adminForm">
		<div class="row">
			<div class="col-md-12">
				<div id="j-main-container" class="j-main-container">
					<?php // Render messages set by extension install scripts here ?>
					<?php if ($this->showMessage) : ?>
						<?php echo $this->loadTemplate('message'); ?>
					<?php endif; ?>
					<?php $tabs = $app->triggerEvent('onInstallerAddInstallationTab', []); ?>
					<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => $tabs[0]['name'] ?? '']); ?>
					<?php // Show installation tabs ?>
					<?php foreach ($tabs as $tab) : ?>
						<?php echo HTMLHelper::_('uitab.addTab', 'myTab', $tab['name'], $tab['label']); ?>
						<fieldset class="uploadform option-fieldset options-form">
							<?php echo $tab['content']; ?>
						</fieldset>
						<?php echo HTMLHelper::_('uitab.endTab'); ?>
					<?php endforeach; ?>
					<?php if (!$tabs) : ?>
						<?php $app->enqueueMessage(Text::_('COM_INSTALLER_NO_INSTALLATION_PLUGINS_FOUND'), 'warning'); ?>
					<?php endif; ?>

					<?php if ($this->ftp) : ?>
						<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'ftp', Text::_('COM_INSTALLER_MSG_DESCFTPTITLE')); ?>
						<?php echo $this->loadTemplate('ftp'); ?>
						<?php echo HTMLHelper::_('uitab.endTab'); ?>
					<?php endif; ?>

					<input type="hidden" name="installtype" value="">
					<input type="hidden" name="task" value="install.install">
					<?php echo HTMLHelper::_('form.token'); ?>

					<?php echo HTMLHelper::_('uitab.endTabSet'); ?>
				</div>
			</div>
		</div>
	</form>
</div>
<div id="loading"></div>
