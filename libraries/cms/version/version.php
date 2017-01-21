<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Version
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Version information class for the Joomla CMS.
 *
 * @since  1.0
 */
final class JVersion
{
	/**
	 * Product name.
	 *
	 * @var    string
	 * @since  3.5
	 */
	const PRODUCT = 'Joomla!';

	/**
	 * Release version.
	 *
	 * @var    string
	 * @since  3.5
	 */
	const RELEASE = '4.0';

	/**
	 * Maintenance version.
	 *
	 * @var    string
	 * @since  3.5
	 */
	const DEV_LEVEL = '0-alpha2';

	/**
	 * Development status.
	 *
	 * @var    string
	 * @since  3.5
	 */
	const DEV_STATUS = 'dev';

	/**
	 * Build number.
	 *
	 * @var    string
	 * @since  3.5
	 */
	const BUILD = '';

	/**
	 * Code name.
	 *
	 * @var    string
	 * @since  3.5
	 */
	const CODENAME = 'Amani';

	/**
	 * Release date.
	 *
	 * @var    string
	 * @since  3.5
	 */
	const RELDATE = '20-December-2016';

	/**
	 * Release time.
	 *
	 * @var    string
	 * @since  3.5
	 */
	const RELTIME = '23:59';

	/**
	 * Release timezone.
	 *
	 * @var    string
	 * @since  3.5
	 */
	const RELTZ = 'GMT';

	/**
	 * Copyright Notice.
	 *
	 * @var    string
	 * @since  3.5
	 */
	const COPYRIGHT = 'Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.';

	/**
	 * Link text.
	 *
	 * @var    string
	 * @since  3.5
	 */
	const URL = '<a href="https://www.joomla.org">Joomla!</a> is Free Software released under the GNU General Public License.';

	/**
	 * Check if we are in development mode
	 *
	 * @return  boolean
	 *
	 * @since   3.4.3
	 */
	public function isInDevelopmentState()
	{
		return strtolower(self::DEV_STATUS) != 'stable';
	}

	/**
	 * Compares two a "PHP standardized" version number against the current Joomla version.
	 *
	 * @param   string  $minimum  The minimum version of the Joomla which is compatible.
	 *
	 * @return  boolean True if the version is compatible.
	 *
	 * @see     https://secure.php.net/version_compare
	 * @since   1.0
	 */
	public function isCompatible($minimum)
	{
		return version_compare(JVERSION, $minimum, 'ge');
	}

	/**
	 * Method to get the help file version.
	 *
	 * @return  string  Version suffix for help files.
	 *
	 * @since   1.0
	 */
	public function getHelpVersion()
	{
		return '.' . str_replace('.', '', self::RELEASE);
	}

	/**
	 * Gets a "PHP standardized" version string for the current Joomla.
	 *
	 * @return  string  Version string.
	 *
	 * @since   1.5
	 */
	public function getShortVersion()
	{
		return self::RELEASE . '.' . self::DEV_LEVEL;
	}

	/**
	 * Gets a version string for the current Joomla with all release information.
	 *
	 * @return  string  Complete version string.
	 *
	 * @since   1.5
	 */
	public function getLongVersion()
	{
		return self::PRODUCT . ' ' . self::RELEASE . '.' . self::DEV_LEVEL . ' '
			. self::DEV_STATUS . ' [ ' . self::CODENAME . ' ] ' . self::RELDATE . ' '
			. self::RELTIME . ' ' . self::RELTZ;
	}

	/**
	 * Returns the user agent.
	 *
	 * @param   string  $component   Name of the component.
	 * @param   bool    $mask        Mask as Mozilla/5.0 or not.
	 * @param   bool    $addVersion  Add version afterwards to component.
	 *
	 * @return  string  User Agent.
	 *
	 * @since   1.0
	 */
	public function getUserAgent($component = null, $mask = false, $addVersion = true)
	{
		if ($component === null)
		{
			$component = 'Framework';
		}

		if ($addVersion)
		{
			$component .= '/' . self::RELEASE;
		}

		// If masked pretend to look like Mozilla 5.0 but still identify ourselves.
		return ($mask ? 'Mozilla/5.0 ' : '') . self::PRODUCT . '/' . self::RELEASE . '.' . self::DEV_LEVEL . ($component ? ' ' . $component : '');
	}

	/**
	 * Generate a media version string for assets
	 * Public to allow third party developers to use it
	 *
	 * @return  string
	 *
	 * @since   3.2
	 */
	public function generateMediaVersion()
	{
		return md5($this->getLongVersion() . JFactory::getConfig()->get('secret') . (new JDate)->toSql());
	}

	/**
	 * Gets a media version which is used to append to Joomla core media files.
	 *
	 * This media version is used to append to Joomla core media in order to trick browsers into
	 * reloading the CSS and JavaScript, because they think the files are renewed.
	 * The media version is renewed after Joomla core update, install, discover_install and uninstallation.
	 *
	 * @return  string  The media version.
	 *
	 * @since   3.2
	 */
	public function getMediaVersion()
	{
		// Load the media version and cache it for future use
		static $mediaVersion = null;

		if ($mediaVersion === null)
		{
			// Get the joomla library params and the media version
			$mediaVersion = JLibraryHelper::getParams('joomla')->get('mediaversion', '');

			// Refresh assets in debug mode or when the media version is not set
			if (JDEBUG || empty($mediaVersion))
			{
				$mediaVersion = $this->generateMediaVersion();

				$this->setMediaVersion($mediaVersion);
			}
		}

		return $mediaVersion;
	}

	/**
	 * Function to refresh the media version
	 *
	 * @return  JVersion  Instance of $this to allow chaining.
	 *
	 * @since   3.2
	 */
	public function refreshMediaVersion()
	{
		return $this->setMediaVersion($this->generateMediaVersion());
	}

	/**
	 * Sets the media version which is used to append to Joomla core media files.
	 *
	 * @param   string  $mediaVersion  The media version.
	 *
	 * @return  JVersion  Instance of $this to allow chaining.
	 *
	 * @since   3.2
	 */
	public function setMediaVersion($mediaVersion)
	{
		// Do not allow empty media versions
		if (!empty($mediaVersion))
		{
			// Get the params ...
			$params = JLibraryHelper::getParams('joomla');

			// ... set the media version ...
			$params->set('mediaversion', $mediaVersion);

			// ... and save the modified params
			JLibraryHelper::saveParams('joomla', $params);
		}

		return $this;
	}
}
