<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_messages
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$hideLinks = $app->getInput()->getBool('hidemainmenu');

if ($hideLinks || $countUnread < 1) {
    return;
}

$route = 'index.php?option=com_messages&view=messages';
?>
<a class="header-item-content" href="<?php echo Route::_($route); ?>" title="<?php echo Text::_('MOD_MESSAGES_PRIVATE_MESSAGES'); ?>">
    <div class="header-item-icon">
        <div class="w-auto">
            <span class="icon-envelope icon-fw" aria-hidden="true"></span>
            <small class="header-item-count"><?php echo $countUnread; ?></small>
        </div>
    </div>
    <div class="header-item-text">
        <?php echo Text::_('MOD_MESSAGES_PRIVATE_MESSAGES'); ?>
    </div>
</a>
