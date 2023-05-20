<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_post_installation_messages
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$hideLinks = $app->getInput()->getBool('hidemainmenu');

if ($hideLinks || $messagesCount < 1) {
    return;
}
?>
<?php if ($app->getIdentity()->authorise('core.manage', 'com_postinstall')) : ?>
    <a class="header-item-content"
        href="<?php echo Route::_('index.php?option=com_postinstall&eid=' . $joomlaFilesExtensionId); ?>"
         title="<?php echo Text::_('MOD_POST_INSTALLATION_MESSAGES'); ?>">
        <div class="header-item-icon">
            <div class="w-auto">
                <span class="icon-bell icon-fw" aria-hidden="true"></span>
                <small class="header-item-count"><?php echo $messagesCount; ?></small>
            </div>
        </div>
        <div class="header-item-text">
            <?php echo Text::_('MOD_POST_INSTALLATION_MESSAGES'); ?>
        </div>
    </a>
<?php endif; ?>
