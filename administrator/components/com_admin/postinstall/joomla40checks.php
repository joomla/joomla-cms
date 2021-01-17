<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
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
	if ($serverType == 'mysql' && stripos($serverVersion, 'mariadb') !== false)
	{
		$serverVersion = preg_replace('/^5\.5\.5-/', '', $serverVersion);

		// MariaDB minimum version is 10.1
		if (version_compare($serverVersion, '10.1', 'lt'))
		{
			return true;
		}
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

	// PHP minimum version is 7.2.5
	return version_compare(PHP_VERSION, '7.2.5', 'lt');
}
