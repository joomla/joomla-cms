<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Updater\Adapter;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Updater\UpdateAdapter;
use Joomla\CMS\Updater\Updater;
use Joomla\CMS\Version;
use Joomla\CMS\TUF\TufValidation;
use Joomla\Database\ParameterType;

/**
 * Extension class for updater
 *
 * @since  1.7.0
 */
class TufAdapter extends UpdateAdapter
{

	/**
	 * Finds an update.
	 *
	 * @param   array  $options  Update options.
	 *
	 * @return  array|boolean  Array containing the array of update sites and array of updates. False on failure
	 *
	 * @since   1.7.0
	 */
	public function findUpdate($options)
	{
		// Get extension_id for TufValidation
		$db = $this->parent->getDbo();

		$query = $db->getQuery(true)
			->select($db->quoteName('extension_id'))
			->from($db->quoteName('#__update_sites_extensions'))
			->where($db->quoteName('update_site_id') . ' = :id')
			->bind(':id', $options['update_site_id'], ParameterType::INTEGER);
		$db->setQuery($query);

		try
		{
			$extension_id = $db->loadResult();
		}
		catch (\RuntimeException $e)
		{
			// Do nothing
		}

		$params = [
			'url_prefix' => 'https://raw.githubusercontent.com',
			'metadata_path' => '/joomla/updates/test/repository/',
			'targets_path' => '/targets/',
			'mirrors' => []
		];

		$TufValidation = new TufValidation($extension_id, $params);
		$metaData = $TufValidation->getValidUpdate();

		if ($metaData === false)
		{
			return false;
		}

		$metaData = json_decode($metaData);

		$b = $metaData['signed']['targets'];

		if (isset($metaData->signed->targets))
		{
			$targets = $metaData->signed->targets;
			foreach ($targets as $filename => $target)
			{

			}
			$c = $metaData->signed->targets;
		}



		//print_r($metaData->version);
		var_dump($metaData);die();

		$table = Table::getInstance('Update');

		// Evaluate Data


		$this->currentUpdate->update_site_id = $this->updateSiteId;
		$this->currentUpdate->detailsurl = $this->_url;
		$this->currentUpdate->folder = '';
		$this->currentUpdate->client_id = 1;
		$this->currentUpdate->infourl = '';
		/**
		if (\in_array($name, $this->updatecols))
		{
		$name = strtolower($name);
		$this->currentUpdate->$name = '';
		}

		if ($name === 'TARGETPLATFORM')
		{
		$this->currentUpdate->targetplatform = $attrs;
		}

		if ($name === 'PHP_MINIMUM')
		{
		$this->currentUpdate->php_minimum = '';
		}

		if ($name === 'SUPPORTED_DATABASES')
		{
		$this->currentUpdate->supported_databases = $attrs;
		}
		 **/
		// Lower case and remove the exclamation mark
		$product = strtolower(InputFilter::getInstance()->clean(Version::PRODUCT, 'cmd'));
		print_r($product);

		// Check that the product matches and that the version matches (optionally a regexp)
		if ($product == $this->currentUpdate->targetplatform['NAME']
			&& preg_match('/^' . $this->currentUpdate->targetplatform['VERSION'] . '/', JVERSION))
		{
			// Check if PHP version supported via <php_minimum> tag, assume true if tag isn't present
			if (!isset($this->currentUpdate->php_minimum) || version_compare(PHP_VERSION, $this->currentUpdate->php_minimum, '>='))
			{
				$phpMatch = true;
			}
			else
			{
				// Notify the user of the potential update
				$msg = Text::sprintf(
					'JLIB_INSTALLER_AVAILABLE_UPDATE_PHP_VERSION',
					$this->currentUpdate->name,
					$this->currentUpdate->version,
					$this->currentUpdate->php_minimum,
					PHP_VERSION
				);

				Factory::getApplication()->enqueueMessage($msg, 'warning');

				$phpMatch = false;
			}

			$dbMatch = false;

			// Check if DB & version is supported via <supported_databases> tag, assume supported if tag isn't present
			if (isset($this->currentUpdate->supported_databases))
			{
				$db           = Factory::getDbo();
				$dbType       = strtoupper($db->getServerType());
				$dbVersion    = $db->getVersion();
				$supportedDbs = $this->currentUpdate->supported_databases;

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
				if (\array_key_exists($dbType, $supportedDbs))
				{
					$minimumVersion = $supportedDbs[$dbType];
					$dbMatch        = version_compare($dbVersion, $minimumVersion, '>=');

					if (!$dbMatch)
					{
						// Notify the user of the potential update
						$dbMsg = Text::sprintf(
							'JLIB_INSTALLER_AVAILABLE_UPDATE_DB_MINIMUM',
							$this->currentUpdate->name,
							$this->currentUpdate->version,
							Text::_($db->name),
							$dbVersion,
							$minimumVersion
						);

						Factory::getApplication()->enqueueMessage($dbMsg, 'warning');
					}
				}
				else
				{
					// Notify the user of the potential update
					$dbMsg = Text::sprintf(
						'JLIB_INSTALLER_AVAILABLE_UPDATE_DB_TYPE',
						$this->currentUpdate->name,
						$this->currentUpdate->version,
						Text::_($db->name)
					);

					Factory::getApplication()->enqueueMessage($dbMsg, 'warning');
				}
			}
			else
			{
				// Set to true if the <supported_databases> tag is not set
				$dbMatch = true;
			}

			// Check minimum stability
			$stabilityMatch = true;

			if (isset($this->currentUpdate->stability) && ($this->currentUpdate->stability < $this->minimum_stability))
			{
				$stabilityMatch = false;
			}

			// Some properties aren't valid fields in the update table so unset them to prevent J! from trying to store them
			unset($this->currentUpdate->targetplatform);

			if (isset($this->currentUpdate->php_minimum))
			{
				unset($this->currentUpdate->php_minimum);
			}

			if (isset($this->currentUpdate->supported_databases))
			{
				unset($this->currentUpdate->supported_databases);
			}

			if (isset($this->currentUpdate->stability))
			{
				unset($this->currentUpdate->stability);
			}

			// If the PHP version and minimum stability checks pass, consider this version as a possible update
			if ($phpMatch && $stabilityMatch && $dbMatch)
			{
				if (isset($this->latest))
				{
					// We already have a possible update. Check the version.
					if (version_compare($this->currentUpdate->version, $this->latest->version, '>') == 1)
					{
						$this->latest = $this->currentUpdate;
					}
				}
				else
				{
					// We don't have any possible updates yet, assume this is an available update.
					$this->latest = $this->currentUpdate;
				}
			}
		}

		if (\array_key_exists('minimum_stability', $options))
		{
			$this->minimum_stability = $options['minimum_stability'];
		}
//$this->update_sites[] = array('type' => 'collection', 'location' => $attrs['REF'], 'update_site_id' => $this->updateSiteId);
		if (isset($this->latest))
		{
			if (isset($this->latest->client) && \strlen($this->latest->client))
			{
				$this->latest->client_id = ApplicationHelper::getClientInfo($this->latest->client, true)->id;

				unset($this->latest->client);
			}

			$updates = array($this->latest);
		}
		else
		{
			$updates = array();
		}

		return array('update_sites' => array(), 'updates' => $updates);
	}

	/**
	 * Converts a tag to numeric stability representation. If the tag doesn't represent a known stability level (one of
	 * dev, alpha, beta, rc, stable) it is ignored.
	 *
	 * @param   string  $tag  The tag string, e.g. dev, alpha, beta, rc, stable
	 *
	 * @return  integer
	 *
	 * @since   3.4
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
