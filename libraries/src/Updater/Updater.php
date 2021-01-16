<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Updater;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;

\JLoader::import('joomla.filesystem.file');
\JLoader::import('joomla.filesystem.folder');
\JLoader::import('joomla.filesystem.path');
\JLoader::import('joomla.base.adapter');

/**
 * Updater Class
 *
 * @since  1.7.0
 */
class Updater extends \JAdapter
{
	/**
	 * Development snapshots, nightly builds, pre-release versions and so on
	 *
	 * @var    integer
	 * @since  3.4
	 */
	const STABILITY_DEV = 0;

	/**
	 * Alpha versions (work in progress, things are likely to be broken)
	 *
	 * @var    integer
	 * @since  3.4
	 */
	const STABILITY_ALPHA = 1;

	/**
	 * Beta versions (major functionality in place, show-stopper bugs are likely to be present)
	 *
	 * @var    integer
	 * @since  3.4
	 */
	const STABILITY_BETA = 2;

	/**
	 * Release Candidate versions (almost stable, minor bugs might be present)
	 *
	 * @var    integer
	 * @since  3.4
	 */
	const STABILITY_RC = 3;

	/**
	 * Stable versions (production quality code)
	 *
	 * @var    integer
	 * @since  3.4
	 */
	const STABILITY_STABLE = 4;

	/**
	 * Updater instance container.
	 *
	 * @var    Updater
	 * @since  1.7.3
	 */
	protected static $instance;

	/**
	 * Constructor
	 *
	 * @param   string  $basepath       Base Path of the adapters
	 * @param   string  $classprefix    Class prefix of adapters
	 * @param   string  $adapterfolder  Name of folder to append to base path
	 *
	 * @since   3.1
	 */
	public function __construct($basepath = __DIR__, $classprefix = '\\Joomla\\CMS\\Updater\\Adapter', $adapterfolder = 'Adapter')
	{
		parent::__construct($basepath, $classprefix, $adapterfolder);
	}

	/**
	 * Returns a reference to the global Installer object, only creating it
	 * if it doesn't already exist.
	 *
	 * @return  Updater  An installer object
	 *
	 * @since   1.7.0
	 */
	public static function getInstance()
	{
		if (!isset(self::$instance))
		{
			self::$instance = new Updater;
		}

		return self::$instance;
	}

	/**
	 * Finds the update for an extension. Any discovered updates are stored in the #__updates table.
	 *
	 * @param   int|array  $eid               Extension Identifier or list of Extension Identifiers; if zero use all
	 *                                        sites
	 * @param   integer    $cacheTimeout      How many seconds to cache update information; if zero, force reload the
	 *                                        update information
	 * @param   integer    $minimumStability  Minimum stability for the updates; 0=dev, 1=alpha, 2=beta, 3=rc,
	 *                                        4=stable
	 * @param   boolean    $includeCurrent    Should I include the current version in the results?
	 *
	 * @return  boolean True if there are updates
	 *
	 * @since   1.7.0
	 */
	public function findUpdates($eid = 0, $cacheTimeout = 0, $minimumStability = self::STABILITY_STABLE, $includeCurrent = false)
	{
		$retval = false;

		$results = $this->getUpdateSites($eid);

		if (empty($results))
		{
			return $retval;
		}

		$now              = time();
		$earliestTime     = $now - $cacheTimeout;
		$sitesWithUpdates = array();

		if ($cacheTimeout > 0)
		{
			$sitesWithUpdates = $this->getSitesWithUpdates($earliestTime);
		}

		foreach ($results as $result)
		{
			/**
			 * If we have already checked for updates within the cache timeout period we will report updates available
			 * only if there are update records matching this update site. Then we skip processing of the update site
			 * since it's already processed within the cache timeout period.
			 */
			if (($cacheTimeout > 0)
				&& isset($result['last_check_timestamp'])
				&& ($result['last_check_timestamp'] >= $earliestTime))
			{
				$retval = $retval || in_array($result['update_site_id'], $sitesWithUpdates);

				continue;
			}

			// Make sure there is no update left over in the database.
			$db = $this->getDbo();
			$query = $db->getQuery(true)
				->delete($db->quoteName('#__updates'))
				->where($db->quoteName('update_site_id') . ' = ' . $db->quote($result['update_site_id']));
			$db->setQuery($query);
			$db->execute();

			$updateObjects = $this->getUpdateObjectsForSite($result, $minimumStability, $includeCurrent);

			if (!empty($updateObjects))
			{
				$retval = true;

				/** @var \JTableUpdate $update */
				foreach ($updateObjects as $update)
				{
					$update->check();
					$update->store();
				}
			}

			// Finally, update the last update check timestamp
			$this->updateLastCheckTimestamp($result['update_site_id']);
		}

		return $retval;
	}

	/**
	 * Finds an update for an extension
	 *
	 * @param   integer  $id  Id of the extension
	 *
	 * @return  mixed
	 *
	 * @since   3.6.0
	 *
	 * @deprecated  4.0  No replacement.
	 */
	public function update($id)
	{
		$updaterow = Table::getInstance('update');
		$updaterow->load($id);
		$update = new Update;

		if ($update->loadFromXml($updaterow->detailsurl))
		{
			return $update->install();
		}

		return false;
	}

	/**
	 * Returns the update site records for an extension with ID $eid. If $eid is zero all enabled update sites records
	 * will be returned.
	 *
	 * @param   int  $eid  The extension ID to fetch.
	 *
	 * @return  array
	 *
	 * @since   3.6.0
	 */
	private function getUpdateSites($eid = 0)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('DISTINCT a.update_site_id, a.type, a.location, a.last_check_timestamp, a.extra_query')
			->from($db->quoteName('#__update_sites', 'a'))
			->where('a.enabled = 1');

		if ($eid)
		{
			$query->join('INNER', '#__update_sites_extensions AS b ON a.update_site_id = b.update_site_id');

			if (is_array($eid))
			{
				$query->where('b.extension_id IN (' . implode(',', $eid) . ')');
			}
			elseif ((int) $eid)
			{
				$query->where('b.extension_id = ' . $eid);
			}
		}

		$db->setQuery($query);

		$result = $db->loadAssocList();

		if (!is_array($result))
		{
			return array();
		}

		return $result;
	}

	/**
	 * Loads the contents of an update site record $updateSite and returns the update objects
	 *
	 * @param   array  $updateSite        The update site record to process
	 * @param   int    $minimumStability  Minimum stability for the returned update records
	 * @param   bool   $includeCurrent    Should I also include the current version?
	 *
	 * @return  array  The update records. Empty array if no updates are found.
	 *
	 * @since   3.6.0
	 */
	private function getUpdateObjectsForSite($updateSite, $minimumStability = self::STABILITY_STABLE, $includeCurrent = false)
	{
		$retVal = array();

		$this->setAdapter($updateSite['type']);

		if (!isset($this->_adapters[$updateSite['type']]))
		{
			// Ignore update sites requiring adapters we don't have installed
			return $retVal;
		}

		$updateSite['minimum_stability'] = $minimumStability;

		// Get the update information from the remote update XML document
		/** @var UpdateAdapter $adapter */
		$adapter       = $this->_adapters[ $updateSite['type']];
		$update_result = $adapter->findUpdate($updateSite);

		// Version comparison operator.
		$operator = $includeCurrent ? 'ge' : 'gt';

		if (is_array($update_result))
		{
			// If we have additional update sites in the remote (collection) update XML document, parse them
			if (array_key_exists('update_sites', $update_result) && count($update_result['update_sites']))
			{
				$thisUrl = trim($updateSite['location']);
				$thisId  = (int) $updateSite['update_site_id'];

				foreach ($update_result['update_sites'] as $extraUpdateSite)
				{
					$extraUrl = trim($extraUpdateSite['location']);
					$extraId  = (int) $extraUpdateSite['update_site_id'];

					// Do not try to fetch the same update site twice
					if (($thisId == $extraId) || ($thisUrl == $extraUrl))
					{
						continue;
					}

					$extraUpdates = $this->getUpdateObjectsForSite($extraUpdateSite, $minimumStability);

					if (count($extraUpdates))
					{
						$retVal = array_merge($retVal, $extraUpdates);
					}
				}
			}

			if (array_key_exists('updates', $update_result) && count($update_result['updates']))
			{
				/** @var \JTableUpdate $current_update */
				foreach ($update_result['updates'] as $current_update)
				{
					$current_update->extra_query = $updateSite['extra_query'];

					/** @var \JTableUpdate $update */
					$update = Table::getInstance('update');

					/** @var \JTableExtension $extension */
					$extension = Table::getInstance('extension');

					$uid = $update
						->find(
							array(
								'element'   => $current_update->get('element'),
								'type'      => $current_update->get('type'),
								'client_id' => $current_update->get('client_id'),
								'folder'    => $current_update->get('folder'),
							)
						);

					$eid = $extension
						->find(
							array(
								'element'   => $current_update->get('element'),
								'type'      => $current_update->get('type'),
								'client_id' => $current_update->get('client_id'),
								'folder'    => $current_update->get('folder'),
							)
						);

					if (!$uid)
					{
						// Set the extension id
						if ($eid)
						{
							// We have an installed extension, check the update is actually newer
							$extension->load($eid);
							$data = json_decode($extension->manifest_cache, true);

							if (version_compare($current_update->version, $data['version'], $operator) == 1)
							{
								$current_update->extension_id = $eid;
								$retVal[] = $current_update;
							}
						}
						else
						{
							// A potentially new extension to be installed
							$retVal[] = $current_update;
						}
					}
					else
					{
						$update->load($uid);

						// We already have an update in the database lets check whether it has an extension_id
						if ((int) $update->extension_id === 0 && $eid)
						{
							// The current update does not have an extension_id but we found one let's use them
							$current_update->extension_id = $eid;
						}

						// If there is an update, check that the version is newer then replaces
						if (version_compare($current_update->version, $update->version, $operator) == 1)
						{
							$retVal[] = $current_update;
						}
					}
				}
			}
		}

		return $retVal;
	}

	/**
	 * Returns the IDs of the update sites with cached updates
	 *
	 * @param   int  $timestamp  Optional. If set, only update sites checked before $timestamp will be taken into
	 *                           account.
	 *
	 * @return  array  The IDs of the update sites with cached updates
	 *
	 * @since   3.6.0
	 */
	private function getSitesWithUpdates($timestamp = 0)
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select('DISTINCT update_site_id')
			->from('#__updates');

		if ($timestamp)
		{
			$subQuery = $db->getQuery(true)
				->select('update_site_id')
				->from('#__update_sites')
				->where($db->qn('last_check_timestamp') . ' IS NULL', 'OR')
				->where($db->qn('last_check_timestamp') . ' <= ' . $db->q($timestamp), 'OR');

			$query->where($db->qn('update_site_id') . ' IN (' . $subQuery . ')');
		}

		$retVal = $db->setQuery($query)->loadColumn(0);

		if (empty($retVal))
		{
			return array();
		}

		return $retVal;
	}

	/**
	 * Update the last check timestamp of an update site
	 *
	 * @param   int  $updateSiteId  The update site ID to mark as just checked
	 *
	 * @return  void
	 *
	 * @since   3.6.0
	 */
	private function updateLastCheckTimestamp($updateSiteId)
	{
		$timestamp = time();
		$db        = Factory::getDbo();

		$query = $db->getQuery(true)
			->update($db->quoteName('#__update_sites'))
			->set($db->quoteName('last_check_timestamp') . ' = ' . $db->quote($timestamp))
			->where($db->quoteName('update_site_id') . ' = ' . $db->quote($updateSiteId));
		$db->setQuery($query);
		$db->execute();
	}
}
