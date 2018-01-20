<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * This file contains post-installation message handling for Joomla 4.0 pre checks
 */

defined('_JEXEC') or die;

/**
 * Checks if the installation meats the current requirements for Joomla 4
 *
 * @return  boolean  True if any check fails.
 *
 * @since   3.7
 *
 * @link    https://developer.joomla.org/news/658-joomla4-manifesto.html
 * @link    https://developer.joomla.org/news/704-looking-forward-with-joomla-4.html
 */
function admin_postinstall_joomla40checks_condition()
{
	$db            = JFactory::getDbo();
	$serverType    = $db->getServerType();
	$serverVersion = $db->getVersion();

	if ($serverType == 'mssql')
	{
		// MS SQL support will be dropped
		return true;
	}

	if ($serverType == 'postgresql' && version_compare($serverVersion, '9.2', 'lt'))
	{
		// PostgreSQL minimum version is 9.2
		return true;
	}

	if ($serverType == 'mysql' && version_compare($serverVersion, '5.5.3', 'lt'))
	{
		// MySQL minimum version is 5.5.3
		return true;
	}

	if ($db->name === 'mysql')
	{
		// Using deprecated MySQL driver
		return true;
	}

	// PHP minimum version is 7.0
	return version_compare(PHP_VERSION, '7.0', 'lt');
}
