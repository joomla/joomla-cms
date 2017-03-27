<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.core');
JText::script('PLG_INSTALLER_PACKAGEINSTALLER_NO_PACKAGE', true);
JText::script('PLG_INSTALLER_FOLDERINSTALLER_NO_INSTALL_PATH', true);
JText::script('PLG_INSTALLER_URLINSTALLER_NO_URL', true);
JText::script('COM_INSTALLER_MSG_INSTALL_ENTER_A_URL', true);

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.tabstate');
JHtml::_('stylesheet', 'com_installer/installer.css', false, true);
JHtml::_('script', 'com_installer/installer.js', false, true);
?>

<div id="installer-install" class="clearfix">

	<form enctype="multipart/form-data" action="<?php echo JRoute::_('index.php?option=com_installer&view=install'); ?>" method="post" name="adminForm" id="adminForm">
		<div class="row">
			<div id="j-sidebar-container" class="col-md-2">
				<?php echo $this->sidebar; ?>
			</div>
			<div class="col-md-10">
				<div id="j-main-container" class="j-main-container">
					<?php // Render messages set by extension install scripts here ?>
					<?php if ($this->showMessage) : ?>
						<?php echo $this->loadTemplate('message'); ?>
					<?php elseif ($this->showJedAndWebInstaller) : ?>
						<div class="alert alert-info j-jed-message" style="margin-bottom: 40px; line-height: 2em; color:#333333;">
							<?php echo JHtml::_(
								'link',
								JRoute::_('index.php?option=com_config&view=component&component=com_installer&path=&return=' . urlencode(base64_encode(JUri::getInstance()))),
								'',
								'class="alert-options float-right hasTooltip icon-options icon-white" data-dismiss="alert" title="' . str_replace('"', '&quot;', JText::_('COM_INSTALLER_SHOW_JED_INFORMATION_TOOLTIP')) . '"'
							);
							?>
							<p><?php echo JText::_('COM_INSTALLER_INSTALL_FROM_WEB_INFO'); ?>
								<?php echo JText::_('COM_INSTALLER_INSTALL_FROM_WEB_TOS'); ?></p>
							<button class="btn btn-primary" type="button" onclick="Joomla.submitbuttonInstallWebInstaller()">
								<?php echo JText::_('COM_INSTALLER_INSTALL_FROM_WEB_ADD_TAB'); ?>
							</button>
						</div>
					<?php endif; ?>
					<?php echo JHtml::_('bootstrap.startTabSet', 'myTab'); ?>
					<?php // Show installation tabs at the start ?>
					<?php $firstTab = JFactory::getApplication()->triggerEvent('onInstallerViewBeforeFirstTab', array()); ?>
					<?php // Show installation tabs ?>
					<?php $tabs = JFactory::getApplication()->triggerEvent('onInstallerAddInstallationTab', array()); ?>
					<?php foreach ($tabs as $tab) : ?>
						<?php echo JHtml::_('bootstrap.addTab', 'myTab', $tab['name'], $tab['label']); ?>
						<fieldset class="uploadform">
							<?php echo $tab['content']; ?>
						</fieldset>
						<?php echo JHtml::_('bootstrap.endTab'); ?>
					<?php endforeach; ?>
					<?php // Show installation tabs at the end ?>
					<?php $lastTab = JFactory::getApplication()->triggerEvent('onInstallerViewAfterLastTab', array()); ?>
					<?php $tabs = array_merge($firstTab, $tabs, $lastTab); ?>
					<?php if (!$tabs) : ?>
						<?php JFactory::getApplication()->enqueueMessage(JText::_('COM_INSTALLER_NO_INSTALLATION_PLUGINS_FOUND'), 'warning'); ?>
					<?php endif; ?>

					<?php if ($this->ftp) : ?>
						<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'ftp', JText::_('COM_INSTALLER_MSG_DESCFTPTITLE')); ?>
						<?php echo $this->loadTemplate('ftp'); ?>
						<?php echo JHtml::_('bootstrap.endTab'); ?>
					<?php endif; ?>

					<input type="hidden" name="installtype" value="">
					<input type="hidden" name="task" value="install.install">
					<?php echo JHtml::_('form.token'); ?>

					<?php echo JHtml::_('bootstrap.endTabSet'); ?>
				</div>
			</div>
		</div>
	</form>
</div>
<div id="loading"></div>
