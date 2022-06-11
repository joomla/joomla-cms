<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Updater;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Updater\Updater;
use Joomla\CMS\Version;

/**
 * ConstrainChecker Class
 *
 * @since  __DEPLOY_VERSION__
 */
class ConstrainChecker
{
	/**
	 * Checks whether the passed constraints are matched
	 *
	 * @param   array  $constraints
	 *
	 * @return  bool
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function check($constraints)
	{
		if (!isset($constraints['targetplatform']))
		{
			// targetplatform is required
			return false;
		}

		// check targetplatform -> true/false
		if (!$this->checkTargetplatform($constraints['targetplatform']))
		{
			return false;
		}

		// check php_minimum
		if (isset($constraints['phpMinimum']) && !$this->checkPhpMinimum($constraints['phpMinimum']))
		{
			return false;
		}

		// check supported databases
		if (isset($constraints['supportedDatabases']) && !$this->checkSupportedDatabases($constraints['supportedDatabases']))
		{
			return false;
		}

		// check stability
		if (isset($constraints['stability']) && !$this->checkStability($constraints['stability']))
		{
			return false;
		}

		return true;

	}

	/**
	 * Check the targetPlatform
	 *
	 * @param   object  $targetPlatform
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function checkTargetplatform($targetPlatform)
	{
		// Lower case and remove the exclamation mark
		$product = strtolower(InputFilter::getInstance()->clean(Version::PRODUCT, 'cmd'));

		// Check that the product matches and that the version matches (optionally a regexp)
		if ($product === $targetPlatform->name
			&& preg_match('/^' . $targetPlatform->version . '/', JVERSION))
		{
			return true;
		}

		return false;
	}

	/**
	 * Character Parser Function
	 *
	 * @param   string  $phpMinimum  The minimum php version
	 *
	 * @return  bool
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function checkPhpMinimum($phpMinimum)
	{
		// Check if PHP version supported via <php_minimum> tag, assume true if tag isn't present
		if (version_compare(PHP_VERSION, $phpMinimum, '>='))
		{
			return true;
		}
	}

	/**
	 * Character Parser Function
	 *
	 * @param   object  $supportedDatabases  stdClass of supporte databases and versions
	 *
	 * @return  bool
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function checkSupportedDatabases($supportedDatabases)
	{
			$db           = Factory::getDbo();
			$dbType       = strtoupper($db->getServerType());
			$dbVersion    = $db->getVersion();

			// MySQL and MariaDB use the same database driver but not the same version numbers
			if ($dbType === 'mysql')
			{
				// Check whether we have a MariaDB version string and extract the proper version from it
				if (stripos($dbVersion, 'mariadb') !== false)
				{
					// MariaDB: Strip off any leading '5.5.5-', if present
					$dbVersion = preg_replace('/^5\.5\.5-/', '', $dbVersion);
					$dbType    = 'mariadb';
				}
			}

			// Do we have an entry for the database?
			if (\property_exists($dbType, $supportedDatabases))
			{
				$minimumVersion = $supportedDatabases[$dbType];

				return version_compare($dbVersion, $minimumVersion, '>=');
			}

			return false;
	}

	/**
	 * Character Parser Function
	 *
	 * @param   string  $stability  Stability to check
	 *
	 * @return  bool
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function checkStability($stability)
	{
		$minimumStability = ComponentHelper::getParams('com_installer')->get('minimum_stability', Updater::STABILITY_STABLE);

		$stability = $this->stabilityTagToInteger($stability);

		if (($stability < $minimumStability))
		{
			return false;
		}

		return true;
	}

	/**
	 * Converts a tag to numeric stability representation. If the tag doesn't represent a known stability level (one of
	 * dev, alpha, beta, rc, stable) it is ignored.
	 *
	 * @param   string  $tag  The tag string, e.g. dev, alpha, beta, rc, stable
	 *
	 * @return  integer
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function stabilityTagToInteger($tag)
	{
		$constant = '\\Joomla\\CMS\\Updater\\Updater::STABILITY_' . strtoupper($tag);

		if (\defined($constant))
		{
			return \constant($constant);
		}

		return Updater::STABILITY_STABLE;
	}
}
