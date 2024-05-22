<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_login
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = \Joomla\CMS\Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->useScript('keepalive');

?>
<form class="mod-login-logout form-vertical" action="<?php echo Route::_('index.php', true); ?>" method="post" id="login-form-<?php echo $module->id; ?>">
<?php if ($params->get('greeting', 1)) : ?>
    <div class="mod-login-logout__login-greeting login-greeting">
    <?php if (!$params->get('name', 0)) : ?>
        <?php echo Text::sprintf('MOD_LOGIN_HINAME', htmlspecialchars($user->name, ENT_COMPAT, 'UTF-8')); ?>
    <?php else : ?>
        <?php echo Text::sprintf('MOD_LOGIN_HINAME', htmlspecialchars($user->username, ENT_COMPAT, 'UTF-8')); ?>
    <?php endif; ?>
    </div>
<?php endif; ?>
<?php if ($params->get('profilelink', 0)) : ?>
    <ul class="mod-login-logout__options list-unstyled">
        <li>
            <a href="<?php echo Route::_('index.php?option=com_users&view=profile'); ?>">
            <?php echo Text::_('MOD_LOGIN_PROFILE'); ?></a>
        </li>
    </ul>
<?php endif; ?>
    <div class="mod-login-logout__button logout-button">
        <button type="submit" name="Submit" class="btn btn-primary"><?php echo Text::_('JLOGOUT'); ?></button>
        <input type="hidden" name="option" value="com_users">
        <input type="hidden" name="task" value="user.logout">
        <input type="hidden" name="return" value="<?php echo $return; ?>">
        <?php echo HTMLHelper::_('form.token'); ?>
    </div>
</form>
