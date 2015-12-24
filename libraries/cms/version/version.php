<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Version
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
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
	/** @var  string  Product name. */
	public $PRODUCT = 'Joomla!';

	/** @var  string  Release version. */
	public $RELEASE = '3.4';

	/** @var  string  Maintenance version. */
	public $DEV_LEVEL = '8';

	/** @var  string  Development STATUS. */
	public $DEV_STATUS = 'Stable';

	/** @var  string  Build number. */
	public $BUILD = '';

	/** @var  string  Code name. */
	public $CODENAME = 'Ember';

	/** @var  string  Release date. */
	public $RELDATE = '24-December-2015';

	/** @var  string  Release time. */
	public $RELTIME = '19:30';

	/** @var  string  Release timezone. */
	public $RELTZ = 'GMT';

	/** @var  string  Copyright Notice. */
	public $COPYRIGHT = 'Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.';

	/** @var  string  Link text. */
	public $URL = '<a href="http://www.joomla.org">Joomla!</a> is Free Software released under the GNU General Public License.';

	/**
	 * Check if we are in development mode
	 *
	 * @return  boolean
	 *
	 * @since   3.4.3
	 */
	public function isInDevelopmentState()
	{
		return strtolower($this->DEV_STATUS) != 'stable';
	}

	/**
	 * Compares two a "PHP standardized" version number against the current Joomla version.
	 *
	 * @param   string  $minimum  The minimum version of the Joomla which is compatible.
	 *
	 * @return  boolean True if the version is compatible.
	 *
	 * @see     http://www.php.net/version_compare
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
		return '.' . str_replace('.', '', $this->RELEASE);
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
		return $this->RELEASE . '.' . $this->DEV_LEVEL;
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
		return $this->PRODUCT . ' ' . $this->RELEASE . '.' . $this->DEV_LEVEL . ' '
			. $this->DEV_STATUS . ' [ ' . $this->CODENAME . ' ] ' . $this->RELDATE . ' '
			. $this->RELTIME . ' ' . $this->RELTZ;
	}

	/**
	 * Returns the user agent.
	 *
	 * @param   string  $component    Name of the component.
	 * @param   bool    $mask         Mask as Mozilla/5.0 or not.
	 * @param   bool    $add_version  Add version afterwards to component.
	 *
	 * @return  string  User Agent.
	 *
	 * @since   1.0
	 */
	public function getUserAgent($component = null, $mask = false, $add_version = true)
	{
		if ($component === null)
		{
			$component = 'Framework';
		}

		if ($add_version)
		{
			$component .= '/' . $this->RELEASE;
		}

		// If masked pretend to look like Mozilla 5.0 but still identify ourselves.
		if ($mask)
		{
			return 'Mozilla/5.0 ' . $this->PRODUCT . '/' . $this->RELEASE . '.' . $this->DEV_LEVEL . ($component ? ' ' . $component : '');
		}
		else
		{
			return $this->PRODUCT . '/' . $this->RELEASE . '.' . $this->DEV_LEVEL . ($component ? ' ' . $component : '');
		}
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
		$date   = new JDate;
		$config = JFactory::getConfig();

		return md5($this->getLongVersion() . $config->get('secret') . $date->toSql());
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
			$config = JFactory::getConfig();
			$debugEnabled = $config->get('debug', 0);

			// Get the joomla library params
			$params = JLibraryHelper::getParams('joomla');

			// Get the media version
			$mediaVersion = $params->get('mediaversion', '');

			// Refresh assets in debug mode or when the media version is not set
			if ($debugEnabled || empty($mediaVersion))
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
		$newMediaVersion = $this->generateMediaVersion();

		return $this->setMediaVersion($newMediaVersion);
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
			// Get library parameters
			$params = JLibraryHelper::getParams('joomla');

			$params->set('mediaversion', $mediaVersion);

			// Save modified params
			JLibraryHelper::saveParams('joomla', $params);
		}

		return $this;
	}
}
