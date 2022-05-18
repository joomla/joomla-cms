<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\ParameterType;

/**
 * Post-installation message about the new Multi-factor Authentication: condition check.
 *
 * Returns true if neither of the two new core MFA plugins are enabled.
 *
 * @return  boolean
 * @since   __DEPLOY_VERSION__
 */
// phpcs:ignore
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
 * @since   __DEPLOY_VERSION__
 */
// phpcs:ignore
function com_users_postinstall_mfa_action(): void
{
	/** @var DatabaseDriver $db */
	$db             = Factory::getContainer()->get('DatabaseDriver');
	$coreMfaPlugins = ['email', 'totp', 'webauthn', 'yubikey'];

	$query = $db->getQuery(true)
		->update($db->quoteName('#__extensions'))
		->set($db->quoteName('enabled') . ' = 1')
		->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
		->where($db->quoteName('folder') . ' = ' . $db->quote('multifactorauth'))
		->whereIn($db->quoteName('element'), $coreMfaPlugins, ParameterType::STRING);
	$db->setQuery($query);
	$db->execute();

	$query = $db->getQuery(true)
		->select($db->quoteName('extension_id'))
		->from($db->quoteName('#__extensions'))
		->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
		->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
		->where($db->quoteName('element') . ' = ' . $db->quote('httpheaders'));
	$db->setQuery($query);
	$extensionId = $db->loadResult();

	$url = 'index.php?option=com_plugins&task=plugin.edit&extension_id=' . $extensionId;
	Factory::getApplication()->redirect($url);
}
