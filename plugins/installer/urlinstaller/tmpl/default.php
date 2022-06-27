<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Installer.urlinstaller
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/** @var PlgInstallerUrlInstaller $this */

$this->app->getDocument()->getWebAssetManager()
    ->registerAndUseScript('plg_installer_urlinstaller.urlinstaller', 'plg_installer_urlinstaller/urlinstaller.js', [], ['defer' => true], ['core']);

?>
<legend><?php echo Text::_('PLG_INSTALLER_URLINSTALLER_TEXT'); ?></legend>

<div class="control-group">
    <label for="install_url" class="control-label">
        <?php echo Text::_('PLG_INSTALLER_URLINSTALLER_TEXT'); ?>
    </label>
    <div class="controls">
        <input type="text" id="install_url" name="install_url" class="form-control" placeholder="https://">
    </div>
</div>
<div class="control-group">
    <div class="controls">
        <button type="button" class="btn btn-primary" id="installbutton_url" onclick="Joomla.submitbuttonurl()">
            <?php echo Text::_('PLG_INSTALLER_URLINSTALLER_BUTTON'); ?>
        </button>
    </div>
</div>
