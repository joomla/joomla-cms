<?php
/**
 * @copyright  Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 * @package    Joomla.Platform
 */

defined('JPATH_PLATFORM') or die;

// Define the Joomla Platform version if not already defined.
if (!defined('JVERSION')) {
	define('JVERSION', JVersion::getShortVersion());
}

/**
 * Version information class for the Joomla Platform.
 *
 * @package  Joomla.Platform
 * @since    11.1
 */
final class JVersion
{
	// Product name.
	const PRODUCT = 'Joomla Platform';
	// Release version.
	const RELEASE = '11.1';
	// Maintenance version.
	const MAINTENANCE = '0';
	// Development STATUS.
	const STATUS = 'Dev';
	// Build number.
	const BUILD = 0;
	// Code name.
	const CODE_NAME = 'Ember';
	// Release date.
	const RELEASE_DATE = '15-Apr-2011';
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
	 * @return  bool    True if the version is compatible.
	 *
	 * @see     http://www.php.net/version_compare
	 * @since   11.1
	 */
	public static function isCompatible($minimum)
	{
		return (version_compare(JVERSION, $minimum, 'eq') == 1);
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
		return JVersion::RELEASE.'.'.JVersion::MAINTENANCE;
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
		return JVersion::PRODUCT.' '. JVersion::RELEASE.'.'.JVersion::MAINTENANCE.' '
				. JVersion::STATUS.' [ '.JVersion::CODE_NAME.' ] '.JVersion::RELEASE_DATE.' '
				.JVersion::RELEASE_TIME.' '.JVersion::RELEASE_TIME_ZONE;
	}
}
