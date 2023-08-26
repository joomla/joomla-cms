<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Installer.packageinstaller
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Filesystem\FilesystemHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Plugin\Installer\Package\Extension\PackageInstaller;

/** @var PackageInstaller $this */

HTMLHelper::_('form.csrf');

Text::script('PLG_INSTALLER_PACKAGEINSTALLER_NO_PACKAGE');
Text::script('PLG_INSTALLER_PACKAGEINSTALLER_UPLOAD_ERROR_UNKNOWN');
Text::script('PLG_INSTALLER_PACKAGEINSTALLER_UPLOAD_ERROR_EMPTY');
Text::script('COM_INSTALLER_MSG_WARNINGS_UPLOADFILETOOBIG');

$this->getApplication()->getDocument()->getWebAssetManager()
    ->registerAndUseScript(
        'plg_installer_packageinstaller.packageinstaller',
        'plg_installer_packageinstaller/packageinstaller.js',
        [],
        ['defer' => true],
        ['core']
    );

$return = $this->getApplication()->getInput()->getBase64('return');
$maxSizeBytes = FilesystemHelper::fileUploadMaxSize(false);
$maxSize = HTMLHelper::_('number.bytes', $maxSizeBytes);
?>
<legend><?php echo Text::_('PLG_INSTALLER_PACKAGEINSTALLER_UPLOAD_INSTALL_JOOMLA_EXTENSION'); ?></legend>

<div id="uploader-wrapper">
    <div id="dragarea" data-state="pending">
        <div id="dragarea-content" class="text-center">
            <p>
                <span id="upload-icon" class="icon-upload" aria-hidden="true"></span>
            </p>
            <div id="upload-progress" class="upload-progress">
                <div class="progress">
                    <div class="progress-bar progress-bar-striped bg-success progress-bar-animated"
                         style="width: 0;"
                         role="progressbar"
                         aria-valuenow="0"
                         aria-valuemin="0"
                         aria-valuemax="100"
                    ></div>
                </div>
                <p class="lead">
                    <span class="uploading-text">
                        <?php echo Text::_('PLG_INSTALLER_PACKAGEINSTALLER_UPLOADING'); ?>
                    </span>
                    <span class="uploading-number">0</span><span class="uploading-symbol">%</span>
                </p>
            </div>
            <div class="install-progress">
                <div class="progress">
                    <div class="progress-bar progress-bar-striped" style="width: 100%;"></div>
                </div>
                <p class="lead">
                    <span class="installing-text">
                        <?php echo Text::_('PLG_INSTALLER_PACKAGEINSTALLER_INSTALLING'); ?>
                    </span>
                </p>
            </div>
            <div class="upload-actions">
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
                    <?php echo Text::sprintf('JGLOBAL_MAXIMUM_UPLOAD_SIZE_LIMIT', '&#x200E;' . $maxSize); ?>
                </p>
            </div>
        </div>
    </div>
</div>

<div id="legacy-uploader" class="hidden">
    <div class="control-group">
        <label for="install_package" class="control-label"><?php echo Text::_('PLG_INSTALLER_PACKAGEINSTALLER_EXTENSION_PACKAGE_FILE'); ?></label>
        <div class="controls">
            <input class="form-control-file" id="install_package" name="install_package" type="file">
            <input id="max_upload_size" name="max_upload_size" type="hidden" value="<?php echo $maxSizeBytes; ?>" />
            <small class="form-text"><?php echo Text::sprintf('JGLOBAL_MAXIMUM_UPLOAD_SIZE_LIMIT', $maxSize); ?></small>
        </div>
    </div>
    <div class="form-actions">
        <button class="btn btn-primary" type="button" id="installbutton_package">
            <?php echo Text::_('PLG_INSTALLER_PACKAGEINSTALLER_UPLOAD_AND_INSTALL'); ?>
        </button>
    </div>

    <input id="installer-return" name="return" type="hidden" value="<?php echo $return; ?>">
</div>
