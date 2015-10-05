<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Updater
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.path');
jimport('joomla.base.adapter');
jimport('joomla.utilities.arrayhelper');

/**
 * Updater Class
 *
 * @since  11.1
 */
class JUpdater extends JAdapter
{
	/**
	 * Development snapshots, nightly builds, pre-release versions and so on
	 *
	 * @const  integer
	 * @since  3.4
	 */
	const STABILITY_DEV = 0;

	/**
	 * Alpha versions (work in progress, things are likely to be broken)
	 *
	 * @const  integer
	 * @since  3.4
	 */
	const STABILITY_ALPHA = 1;

	/**
	 * Beta versions (major functionality in place, show-stopper bugs are likely to be present)
	 *
	 * @const  integer
	 * @since  3.4
	 */
	const STABILITY_BETA = 2;

	/**
	 * Release Candidate versions (almost stable, minor bugs might be present)
	 *
	 * @const  integer
	 * @since  3.4
	 */
	const STABILITY_RC = 3;

	/**
	 * Stable versions (production quality code)
	 *
	 * @const  integer
	 * @since  3.4
	 */
	const STABILITY_STABLE = 4;

	/**
	 * @var    JUpdater  JUpdater instance container.
	 * @since  11.3
	 */
	protected static $instance;

	/**
	 * Constructor
	 *
	 * @since   11.1
	 */
	public function __construct()
	{
		// Adapter base path, class prefix
		parent::__construct(__DIR__, 'JUpdater');
	}

	/**
	 * Returns a reference to the global Installer object, only creating it
	 * if it doesn't already exist.
	 *
	 * @return  JUpdater  An installer object
	 *
	 * @since   11.1
	 */
	public static function getInstance()
	{
		if (!isset(self::$instance))
		{
			self::$instance = new JUpdater;
		}

		return self::$instance;
	}

	/**
	 * Finds an update for an extension
	 *
	 * @param   integer  $eid                Extension Identifier; if zero use all sites
	 * @param   integer  $cacheTimeout       How many seconds to cache update information; if zero, force reload the update information
	 * @param   integer  $minimum_stability  Minimum stability for the updates; 0=dev, 1=alpha, 2=beta, 3=rc, 4=stable
	 *
	 * @return  boolean True if there are updates
	 *
	 * @since   11.1
	 */
	public function findUpdates($eid = 0, $cacheTimeout = 0, $minimum_stability = self::STABILITY_STABLE)
	{
		$db     = $this->getDbo();
		$query  = $db->getQuery(true);

		$retval = false;

		$query->select('DISTINCT a.update_site_id, a.type, a.location, a.last_check_timestamp, a.extra_query')
			->from('#__update_sites AS a')
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
		$results = $db->loadAssocList();
		$result_count = count($results);
		$now = time();

		for ($i = 0; $i < $result_count; $i++)
		{
			$result = &$results[$i];
			$this->setAdapter($result['type']);

			if (!isset($this->_adapters[$result['type']]))
			{
				// Ignore update sites requiring adapters we don't have installed
				continue;
			}

			if ($cacheTimeout > 0)
			{
				if (isset($result['last_check_timestamp']) && ($now - $result['last_check_timestamp'] <= $cacheTimeout))
				{
					// Ignore update sites whose information we have fetched within
					// the cache time limit
					$retval = true;
					continue;
				}
			}

			$result['minimum_stability'] = $minimum_stability;

			/** @var JUpdateAdapter $adapter */
			$adapter       = $this->_adapters[$result['type']];
			$update_result = $adapter->findUpdate($result);

			if (is_array($update_result))
			{
				if (array_key_exists('update_sites', $update_result) && count($update_result['update_sites']))
				{
					$results = JArrayHelper::arrayUnique(array_merge($results, $update_result['update_sites']));
					$result_count = count($results);
				}

				if (array_key_exists('updates', $update_result) && count($update_result['updates']))
				{
					for ($k = 0, $count = count($update_result['updates']); $k < $count; $k++)
					{
						$current_update = &$update_result['updates'][$k];
						$current_update->extra_query = $result['extra_query'];
						$update = JTable::getInstance('update');
						$extension = JTable::getInstance('extension');
						$uid = $update
							->find(
							array(
								'element' => strtolower($current_update->get('element')), 'type' => strtolower($current_update->get('type')),
								'client_id' => strtolower($current_update->get('client_id')),
								'folder' => strtolower($current_update->get('folder'))
							)
						);

						$eid = $extension
							->find(
							array(
								'element' => strtolower($current_update->get('element')), 'type' => strtolower($current_update->get('type')),
								'client_id' => strtolower($current_update->get('client_id')),
								'folder' => strtolower($current_update->get('folder'))
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

								if (version_compare($current_update->version, $data['version'], '>') == 1)
								{
									$current_update->extension_id = $eid;
									$current_update->store();
								}
							}
							else
							{
								// A potentially new extension to be installed
								$current_update->store();
							}
						}
						else
						{
							$update->load($uid);

							// If there is an update, check that the version is newer then replaces
							if (version_compare($current_update->version, $update->version, '>') == 1)
							{
								$current_update->store();
							}
						}
					}
				}
			}

			// Finally, update the last update check timestamp
			$query = $db->getQuery(true)
				->update($db->quoteName('#__update_sites'))
				->set($db->quoteName('last_check_timestamp') . ' = ' . $db->quote($now))
				->where($db->quoteName('update_site_id') . ' = ' . $db->quote($result['update_site_id']));
			$db->setQuery($query);
			$db->execute();
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
	 * @since   11.1
	 */
	public function update($id)
	{
		$updaterow = JTable::getInstance('update');
		$updaterow->load($id);
		$update = new JUpdate;

		if ($update->loadFromXml($updaterow->detailsurl))
		{
			return $update->install();
		}

		return false;
	}
}
