<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Date\Date;
use Joomla\CMS\Helper\LibraryHelper;

/**
 * Version information class for the Joomla CMS.
 *
 * @since  1.0
 */
final class Version
{
	/**
	 * Product name.
	 *
	 * @var    string
	 * @since  3.5
	 */
	const PRODUCT = 'Joomla!';

	/**
	 * Major release version.
	 *
	 * @var    integer
	 * @since  3.8.0
	 */
	const MAJOR_VERSION = 4;

	/**
	 * Minor release version.
	 *
	 * @var    integer
	 * @since  3.8.0
	 */
	const MINOR_VERSION = 0;

	/**
	 * Patch release version.
	 *
	 * @var    integer
	 * @since  3.8.0
	 */
	const PATCH_VERSION = 4;

	/**
	 * Extra release version info.
	 *
	 * This constant when not empty adds an additional identifier to the version string to reflect the development state.
	 * For example, for 3.8.0 when this is set to 'dev' the version string will be `3.8.0-dev`.
	 *
	 * @var    string
	 * @since  3.8.0
	 */
	const EXTRA_VERSION = '';

	/**
	 * Development status.
	 *
	 * @var    string
	 * @since  3.5
	 */
	const DEV_STATUS = 'Stable';

	/**
	 * Code name.
	 *
	 * @var    string
	 * @since  3.5
	 */
	const CODENAME = 'Furaha';

	/**
	 * Release date.
	 *
	 * @var    string
	 * @since  3.5
	 */
	const RELDATE = '24-October-2021';

	/**
	 * Release time.
	 *
	 * @var    string
	 * @since  3.5
	 */
	const RELTIME = '19:25';

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
	const COPYRIGHT = '(C) 2005 Open Source Matters, Inc. <https://www.joomla.org>';

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
	public function isInDevelopmentState(): bool
	{
		return strtolower(self::DEV_STATUS) !== 'stable';
	}

	/**
	 * Compares two a "PHP standardized" version number against the current Joomla version.
	 *
	 * @param   string  $minimum  The minimum version of the Joomla which is compatible.
	 *
	 * @return  boolean True if the version is compatible.
	 *
	 * @link    https://www.php.net/version_compare
	 * @since   1.0
	 */
	public function isCompatible(string $minimum): bool
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
	public function getHelpVersion(): string
	{
		return '.' . self::MAJOR_VERSION . self::MINOR_VERSION;
	}

	/**
	 * Gets a "PHP standardized" version string for the current Joomla.
	 *
	 * @return  string  Version string.
	 *
	 * @since   1.5
	 */
	public function getShortVersion(): string
	{
		$version = self::MAJOR_VERSION . '.' . self::MINOR_VERSION . '.' . self::PATCH_VERSION;

		if (!empty(self::EXTRA_VERSION))
		{
			$version .= '-' . self::EXTRA_VERSION;
		}

		return $version;
	}

	/**
	 * Gets a version string for the current Joomla with all release information.
	 *
	 * @return  string  Complete version string.
	 *
	 * @since   1.5
	 */
	public function getLongVersion(): string
	{
		return self::PRODUCT . ' ' . $this->getShortVersion() . ' '
			. self::DEV_STATUS . ' [ ' . self::CODENAME . ' ] ' . self::RELDATE . ' '
			. self::RELTIME . ' ' . self::RELTZ;
	}

	/**
	 * Returns the user agent.
	 *
	 * @param   string  $suffix      String to append to resulting user agent.
	 * @param   bool    $mask        Mask as Mozilla/5.0 or not.
	 * @param   bool    $addVersion  Add version afterwards to component.
	 *
	 * @return  string  User Agent.
	 *
	 * @since   1.0
	 */
	public function getUserAgent(string $suffix = '', bool $mask = false, bool $addVersion = true): string
	{
		if ($suffix === '')
		{
			$suffix = 'Framework';
		}

		if ($addVersion)
		{
			$suffix .= '/' . self::MAJOR_VERSION . '.' . self::MINOR_VERSION;
		}

		// If masked pretend to look like Mozilla 5.0 but still identify ourselves.
		return ($mask ? 'Mozilla/5.0 ' : '') . self::PRODUCT . '/' . $this->getShortVersion() . ($suffix ? ' ' . $suffix : '');
	}

	/**
	 * Generate a media version string for assets
	 * Public to allow third party developers to use it
	 *
	 * @return  string
	 *
	 * @since   3.2
	 */
	public function generateMediaVersion(): string
	{
		return md5($this->getLongVersion() . Factory::getApplication()->get('secret') . (new Date)->toSql());
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
	public function getMediaVersion(): string
	{
		// Load the media version and cache it for future use
		static $mediaVersion = null;

		if ($mediaVersion === null)
		{
			// Get the joomla library params and the media version
			$mediaVersion = LibraryHelper::getParams('joomla')->get('mediaversion', '');

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
	 * @return  Version  Instance of $this to allow chaining.
	 *
	 * @since   3.2
	 */
	public function refreshMediaVersion(): Version
	{
		return $this->setMediaVersion($this->generateMediaVersion());
	}

	/**
	 * Sets the media version which is used to append to Joomla core media files.
	 *
	 * @param   string  $mediaVersion  The media version.
	 *
	 * @return  Version  Instance of $this to allow chaining.
	 *
	 * @since   3.2
	 */
	public function setMediaVersion(string $mediaVersion): Version
	{
		// Do not allow empty media versions
		if (!empty($mediaVersion))
		{
			// Get the params ...
			$params = LibraryHelper::getParams('joomla');

			// ... set the media version ...
			$params->set('mediaversion', $mediaVersion);

			// ... and save the modified params
			LibraryHelper::saveParams('joomla', $params);
		}

		return $this;
	}
}
