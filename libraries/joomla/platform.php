<?php
/**
 * @package    Joomla.Platform
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Version information class for the Joomla Platform.
 *
 * @since       1.7.0
 * @deprecated  4.0  Deprecated without replacement
 */
final class JPlatform
{
	// Product name.
	const PRODUCT = 'Joomla Platform';

	// Release version.
	const RELEASE = '13.1';

	// Maintenance version.
	const MAINTENANCE = '0';

	// Development STATUS.
	const STATUS = 'Stable';

	// Build number.
	const BUILD = 0;

	// Code name.
	const CODE_NAME = 'Curiosity';

	// Release date.
	const RELEASE_DATE = '24-Apr-2013';

	// Release time.
	const RELEASE_TIME = '00:00';

	// Release timezone.
	const RELEASE_TIME_ZONE = 'GMT';

	// Copyright Notice.
	const COPYRIGHT = 'Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.';

	// Link text.
	const LINK_TEXT = '<a href="https://www.joomla.org">Joomla!</a> is Free Software released under the GNU General Public License.';

	/**
	 * Compares two a "PHP standardized" version number against the current Joomla Platform version.
	 *
	 * @param   string  $minimum  The minimum version of the Joomla Platform which is compatible.
	 *
	 * @return  boolean  True if the version is compatible.
	 *
	 * @link    https://www.php.net/version_compare
	 * @since   1.7.0
	 * @deprecated  4.0  Deprecated without replacement
	 */
	public static function isCompatible($minimum)
	{
		return version_compare(self::getShortVersion(), $minimum, 'eq') == 1;
	}

	/**
	 * Gets a "PHP standardized" version string for the current Joomla Platform.
	 *
	 * @return  string  Version string.
	 *
	 * @since   1.7.0
	 * @deprecated  4.0  Deprecated without replacement
	 */
	public static function getShortVersion()
	{
		return self::RELEASE . '.' . self::MAINTENANCE;
	}

	/**
	 * Gets a version string for the current Joomla Platform with all release information.
	 *
	 * @return  string  Complete version string.
	 *
	 * @since   1.7.0
	 * @deprecated  4.0  Deprecated without replacement
	 */
	public static function getLongVersion()
	{
		return self::PRODUCT . ' ' . self::RELEASE . '.' . self::MAINTENANCE . ' ' . self::STATUS . ' [ ' . self::CODE_NAME . ' ] '
			. self::RELEASE_DATE . ' ' . self::RELEASE_TIME . ' ' . self::RELEASE_TIME_ZONE;
	}
}
