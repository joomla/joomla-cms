<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Installer.folderinstaller
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/** @var PlgInstallerFolderInstaller $this */

Text::script('PLG_INSTALLER_FOLDERINSTALLER_NO_INSTALL_PATH');

$this->app->getDocument()->getWebAssetManager()
    ->registerAndUseScript(
        'plg_installer_folderinstaller.folderinstaller',
        'plg_installer_folderinstaller/folderinstaller.js',
        [],
        ['defer' => true],
        ['core']
    );

?>
<legend><?php echo Text::_('PLG_INSTALLER_FOLDERINSTALLER_TEXT'); ?></legend>

<div class="control-group">
    <label for="install_directory" class="control-label">
        <?php echo Text::_('PLG_INSTALLER_FOLDERINSTALLER_TEXT'); ?>
    </label>
    <div class="controls">
        <input type="text" id="install_directory" name="install_directory" class="form-control"
            value="<?php echo $this->app->getInput()->get('install_directory', $this->app->get('tmp_path')); ?>">
    </div>
</div>
<div class="control-group">
    <div class="controls">
        <button type="button" class="btn btn-primary" id="installbutton_directory" onclick="Joomla.submitbuttonfolder()">
            <?php echo Text::_('PLG_INSTALLER_FOLDERINSTALLER_BUTTON'); ?>
        </button>
    </div>
</div>
