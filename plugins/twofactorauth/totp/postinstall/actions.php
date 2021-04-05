<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Twofactorauth.totp
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * This file contains the functions used by the com_postinstall code to deliver
 * the necessary post-installation messages concerning the activation of the
 * two-factor authentication code.
 */

use Joomla\CMS\Factory;

/**
 * Checks if the plugin is enabled. If not it returns true, meaning that the
 * message concerning two factor authentication should be displayed.
 *
 * @return  integer
 *
 * @since   3.2
 */
function twofactorauth_postinstall_condition()
{
	$db = Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);

	$query = $db->getQuery(true)
		->select('*')
		->from($db->quoteName('#__extensions'))
		->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
		->where($db->quoteName('enabled') . ' = ' . 1)
		->where($db->quoteName('folder') . ' = ' . $db->quote('twofactorauth'));
	$db->setQuery($query);
	$enabled_plugins = $db->loadObjectList();

	return count($enabled_plugins) === 0;
}

/**
 * Enables the two factor authentication plugin and redirects the user to their
 * user profile page so that they can enable two factor authentication on their
 * account.
 *
 * @return  void
 *
 * @since   3.2
 */
function twofactorauth_postinstall_action()
{
	// Enable the plugin
	$db = Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);

	$query = $db->getQuery(true)
		->update($db->quoteName('#__extensions'))
		->set($db->quoteName('enabled') . ' = ' . 1)
		->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
		->where($db->quoteName('folder') . ' = ' . $db->quote('twofactorauth'));
	$db->setQuery($query);
	$db->execute();

	// Clean cache.
	Factory::getCache()->clean('com_plugins');

	// Redirect the user to their profile editor page
	$url = 'index.php?option=com_users&task=user.edit&id=' . Factory::getUser()->id;
	Factory::getApplication()->redirect($url);
}
