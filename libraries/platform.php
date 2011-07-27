<?php
/**
 * @package     Joomla.Platform
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Version information class for the Joomla Platform.
 *
 * @package  Joomla.Platform
 * @since    11.1
 */
final class JPlatform
{
	// Product name.
	const PRODUCT = 'Joomla Platform';
	// Release version.
	const RELEASE = '11.2';
	// Maintenance version.
	const MAINTENANCE = '0';
	// Development STATUS.
	const STATUS = 'Stable';
	// Build number.
	const BUILD = 0;
	// Code name.
	const CODE_NAME = 'Omar';
	// Release date.
	const RELEASE_DATE = '27-Jul-2011';
	// Release time.
	const RELEASE_TIME = '00:00';
	// Release timezone.
	const RELEASE_TIME_ZONE = 'GMT';
	// Copyright Notice.
	const COPYRIGHT = 'Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.';
	// Link text.
	const LINK_TEXT = '<a href="http://www.joomla.org">Joomla!</a> is Free Software released under the GNU General Public License.';

	/**
	 * Compares two a "PHP standardized" version number against the current Joomla Platform version.
	 *
	 * @param   string  $minimum  The minimum version of the Joomla Platform which is compatible.
	 *
	 * @return  boolean  True if the version is compatible.
	 *
	 * @see     http://www.php.net/version_compare
	 * @since   11.1
	 */
	public static function isCompatible($minimum)
	{
		return (version_compare(self::getShortVersion(), $minimum, 'eq') == 1);
	}

	/**
	 * Gets a "PHP standardized" version string for the current Joomla Platform.
	 *
	 * @return  string  Version string.
	 *
	 * @since   11.1
	 */
	public static function getShortVersion()
	{
		return self::RELEASE.'.'.self::MAINTENANCE;
	}

	/**
	 * Gets a version string for the current Joomla Platform with all release information.
	 *
	 * @return  string  Complete version string.
	 *
	 * @since   11.1
	 */
	public static function getLongVersion()
	{
		return self::PRODUCT.' '. self::RELEASE.'.'.self::MAINTENANCE.' '
				. self::STATUS.' [ '.self::CODE_NAME.' ] '.self::RELEASE_DATE.' '
				.self::RELEASE_TIME.' '.self::RELEASE_TIME_ZONE;
	}
}
