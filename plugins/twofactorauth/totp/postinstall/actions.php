<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Twofactorauth.totp
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * This file contains the functions used by the com_postinstall code to deliver
 * the necessary post-installation messages concerning the activation of the
 * two-factor authentication code.
 */

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
	$db = JFactory::getDbo();

	$query = $db->getQuery(true)
		->select('*')
		->from($db->qn('#__extensions'))
		->where($db->qn('type') . ' = ' . $db->q('plugin'))
		->where($db->qn('enabled') . ' = ' . $db->q('1'))
		->where($db->qn('folder') . ' = ' . $db->q('twofactorauth'));
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
	$db = JFactory::getDbo();

	$query = $db->getQuery(true)
		->update($db->qn('#__extensions'))
		->set($db->qn('enabled') . ' = ' . $db->q(1))
		->where($db->qn('type') . ' = ' . $db->q('plugin'))
		->where($db->qn('folder') . ' = ' . $db->q('twofactorauth'));
	$db->setQuery($query);
	$db->execute();

	// Redirect the user to their profile editor page
	$url = 'index.php?option=com_users&task=user.edit&id=' . JFactory::getUser()->id;
	JFactory::getApplication()->redirect($url);
}
