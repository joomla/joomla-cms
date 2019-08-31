<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * This file contains post-installation message handling for Joomla 4.0 pre checks
 */

defined('_JEXEC') or die;

/**
 * Checks if the installation meets the current requirements for Joomla 4
 *
 * @return  boolean  True if any check fails.
 *
 * @since   3.7
 *
 * @link    https://developer.joomla.org/news/658-joomla4-manifesto.html
 * @link    https://developer.joomla.org/news/704-looking-forward-with-joomla-4.html
 * @link    https://developer.joomla.org/news/788-joomla-4-on-the-move.html
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

	if ($serverType == 'postgresql' && version_compare($serverVersion, '11.0', 'lt'))
	{
		// PostgreSQL minimum version is 11.0
		return true;
	}

	// Check whether we have a MariaDB version string and extract the proper version from it
	if ($serverType == 'mysql' && preg_match('/^(?:5\.5\.5-)?(mariadb-)?(?P<major>\d+)\.(?P<minor>\d+)\.(?P<patch>\d+)/i', $serverVersion, $versionParts))
	{
		$dbVersion = $versionParts['major'] . '.' . $versionParts['minor'] . '.' . $versionParts['patch'];

		// MariaDB minimum version is 10.1
		return version_compare($dbVersion, '10.1', 'lt');
	}

	if ($serverType == 'mysql' && version_compare($serverVersion, '5.6', 'lt'))
	{
		// MySQL minimum version is 5.6.0
		return true;
	}

	if ($db->name === 'mysql')
	{
		// Using deprecated MySQL driver
		return true;
	}

	if ($db->name === 'postgresql')
	{
		// Using deprecated PostgreSQL driver
		return true;
	}

	// PHP minimum version is 7.0
	return version_compare(PHP_VERSION, '7.2', 'lt');
}
