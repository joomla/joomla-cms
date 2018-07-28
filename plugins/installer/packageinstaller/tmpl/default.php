<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Installer.packageinstaller
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Filesystem\FilesystemHelper;

$token   = Session::getFormToken();
$return  = Factory::getApplication()->input->getBase64('return');
$maxSize = FilesystemHelper::fileUploadMaxSize();
?>

<legend><?php echo Text::_('PLG_INSTALLER_PACKAGEINSTALLER_UPLOAD_INSTALL_JOOMLA_EXTENSION'); ?></legend>

<hr>

<div id="uploader-wrapper">
	<div id="dragarea">
		<div id="dragarea-content" class="text-center">
			<p>
				<span id="upload-icon" class="icon-upload" aria-hidden="true"></span>
			</p>
			<p class="lead">
				<?php echo Text::_('PLG_INSTALLER_PACKAGEINSTALLER_DRAG_FILE_HERE'); ?>
			</p>
			<p>
				<button id="select-file-button" type="button" class="btn btn-success">
					<span class="icon-copy" aria-hidden="true"></span>
					<?php echo Text::_('PLG_INSTALLER_PACKAGEINSTALLER_SELECT_FILE'); ?>
				</button>
			</p>
			<p>
				<?php echo Text::sprintf('JGLOBAL_MAXIMUM_UPLOAD_SIZE_LIMIT', $maxSize); ?>
			</p>
		</div>
	</div>
</div>

<div id="legacy-uploader" style="display: none;">
	<div class="control-group">
		<label for="install_package" class="control-label"><?php echo Text::_('PLG_INSTALLER_PACKAGEINSTALLER_EXTENSION_PACKAGE_FILE'); ?></label>
		<div class="controls">
			<input class="form-control-file" id="install_package" name="install_package" type="file">
			<small class="form-text text-muted"><?php echo Text::sprintf('JGLOBAL_MAXIMUM_UPLOAD_SIZE_LIMIT', $maxSize); ?></small>
		</div>
	</div>
	<div class="form-actions">
		<button class="btn btn-primary" type="button" id="installbutton_package">
			<?php echo Text::_('PLG_INSTALLER_PACKAGEINSTALLER_UPLOAD_AND_INSTALL'); ?>
		</button>
	</div>

	<input id="installer-return" name="return" type="hidden" value="<?php echo $return; ?>">
	<input id="installer-token" name="return" type="hidden" value="<?php echo $token; ?>">
</div>
