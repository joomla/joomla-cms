<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Post-installation message about the new Multi-factor Authentication: condition check.
 *
 * Returns true if neither of the two new core MFA plugins are enabled.
 *
 * @return  boolean
 * @since   4.2.0
 */
function com_users_postinstall_mfa_condition(): bool
{
    return count(PluginHelper::getPlugin('multifactorauth')) < 1;
}

/**
 * Post-installation message about the new Multi-factor Authentication: action.
 *
 * Enables the core MFA plugins.
 *
 * @return  void
 * @since   4.2.0
 */
function com_users_postinstall_mfa_action(): void
{
    /** @var DatabaseInterface $db */
    $db             = Factory::getContainer()->get(DatabaseInterface::class);
    $coreMfaPlugins = ['email', 'totp', 'webauthn', 'yubikey'];

    $query = $db->getQuery(true)
        ->update($db->quoteName('#__extensions'))
        ->set($db->quoteName('enabled') . ' = 1')
        ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
        ->where($db->quoteName('folder') . ' = ' . $db->quote('multifactorauth'))
        ->whereIn($db->quoteName('element'), $coreMfaPlugins, ParameterType::STRING);
    $db->setQuery($query);
    $db->execute();

    $url = 'index.php?option=com_plugins&filter[folder]=multifactorauth';
    Factory::getApplication()->redirect($url);
}
