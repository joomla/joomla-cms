<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

// Load JavaScript message titles
Text::script('ERROR');
Text::script('WARNING');
Text::script('NOTICE');
Text::script('MESSAGE');

Text::script('COM_INSTALLER_MSG_INSTALL_ENTER_A_URL');

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('core')
	->usePreset('com_installer.installer')
	->useScript('webcomponent.core-loader');

$app  = Factory::getApplication();
$tabs = $app->triggerEvent('onInstallerAddInstallationTab', []);

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

					<?php if (!$tabs) : ?>
						<div class="alert alert-warning">
							<span class="icon-exclamation-circle" aria-hidden="true"></span><span class="sr-only"><?php echo Text::_('WARNING'); ?></span>
							<?php echo Text::_('COM_INSTALLER_NO_INSTALLATION_PLUGINS_FOUND'); ?>
						</div>
					<?php endif; ?>

					<?php if ($tabs || $this->ftp) : ?>
						<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => $tabs[0]['name'] ?? '']); ?>
						<?php // Show installation tabs ?>
						<?php foreach ($tabs as $tab) : ?>
							<?php echo HTMLHelper::_('uitab.addTab', 'myTab', $tab['name'], $tab['label']); ?>
							<fieldset class="uploadform option-fieldset options-form">
								<?php echo $tab['content']; ?>
							</fieldset>
							<?php echo HTMLHelper::_('uitab.endTab'); ?>
						<?php endforeach; ?>

						<?php if ($this->ftp) : ?>
							<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'ftp', Text::_('COM_INSTALLER_MSG_DESCFTPTITLE')); ?>
							<?php echo $this->loadTemplate('ftp'); ?>
							<?php echo HTMLHelper::_('uitab.endTab'); ?>
						<?php endif; ?>
						<?php echo HTMLHelper::_('uitab.endTabSet'); ?>
					<?php endif; ?>

					<input type="hidden" name="installtype" value="">
					<input type="hidden" name="task" value="install.install">
					<?php echo HTMLHelper::_('form.token'); ?>
				</div>
			</div>
		</div>
	</form>
</div>
<div id="loading"></div>
