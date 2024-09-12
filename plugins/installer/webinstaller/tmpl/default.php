<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Installer.Webinstaller
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/** @var \Joomla\Plugin\Installer\Web\Extension\WebInstaller $this */

$dir = $this->isRTL() ? ' dir="ltr"' : '';

Text::script('JSEARCH_FILTER_CLEAR');
Text::script('PLG_INSTALLER_WEBINSTALLER_INSTALL_WEB_LOADING_ERROR');

?>
<div id="jed-container" class="tab-pane">
    <div class="card" id="web-loader">
        <div class="card-body">
            <h2 class="card-title"><?php echo Text::_('PLG_INSTALLER_WEBINSTALLER_INSTALL_WEB_LOADING'); ?></h2>
        </div>
    </div>
    <div class="hidden" id="web-loader-error">
    </div>
</div>

<fieldset class="form-group hidden" id="uploadform-web"<?php echo $dir; ?>>
    <p><strong><?php echo Text::_('PLG_INSTALLER_WEBINSTALLER_INSTALL_WEB_CONFIRM'); ?></strong></p>
    <dl>
        <dt id="uploadform-web-name-label"><?php echo Text::_('PLG_INSTALLER_WEBINSTALLER_INSTALL_WEB_CONFIRM_NAME'); ?></dt>
        <dd id="uploadform-web-name"></dd>
        <dt><?php echo Text::_('PLG_INSTALLER_WEBINSTALLER_INSTALL_WEB_CONFIRM_URL'); ?></dt>
        <dd id="uploadform-web-url"></dd>
    </dl>
    <div class="card card-light">
        <div class="card-body">
            <div class="card-text">
                <button type="button" class="btn btn-primary" id="uploadform-web-install"><?php echo Text::_('COM_INSTALLER_INSTALL_BUTTON'); ?></button>
                <button type="button" class="btn btn-secondary" id="uploadform-web-cancel"><?php echo Text::_('JCANCEL'); ?></button>
            </div>
        </div>
    </div>
</fieldset>
