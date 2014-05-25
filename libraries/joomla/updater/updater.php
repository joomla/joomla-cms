<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Updater
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.archive');
jimport('joomla.filesystem.path');
jimport('joomla.base.adapter');
jimport('joomla.utilities.arrayhelper');

/**
 * Updater Class
 *
 * @package     Joomla.Platform
 * @subpackage  Updater
 * @since       11.1
 */
class JUpdater extends JAdapter
{
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
		parent::__construct(dirname(__FILE__), 'JUpdater');
	}

	/**
	 * Returns a reference to the global Installer object, only creating it
	 * if it doesn't already exist.
	 *
	 * @return  object  An installer object
	 *
	 * @since   11.1
	 */
	public static function &getInstance()
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
	 * @param   integer  $eid           Extension Identifier; if zero use all sites
	 * @param   integer  $cacheTimeout  How many seconds to cache update information; if zero, force reload the update information
	 *
	 * @return  boolean True if there are updates
	 *
	 * @since   11.1
	 */
	public function findUpdates($eid = 0, $cacheTimeout = 0)
	{
		$dbo = $this->getDBO();
		$retval = false;

		// Push it into an array
		if (!is_array($eid))
		{
			$query = 'SELECT DISTINCT update_site_id, type, location, last_check_timestamp FROM #__update_sites WHERE enabled = 1';
		}
		else
		{
			$query = 'SELECT DISTINCT update_site_id, type, location, last_check_timestamp FROM #__update_sites' .
				' WHERE update_site_id IN' .
				'  (SELECT update_site_id FROM #__update_sites_extensions WHERE extension_id IN (' . implode(',', $eid) . '))';
		}
		$dbo->setQuery($query);
		$results = $dbo->loadAssocList();
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
			$update_result = $this->_adapters[$result['type']]->findUpdate($result);
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
							// if there is an update, check that the version is newer then replaces
							if (version_compare($current_update->version, $update->version, '>') == 1)
							{
								$current_update->store();
							}
						}
					}
				}
				$update_result = true;
			}
			elseif ($retval)
			{
				$update_result = true;
			}

			// Finally, update the last update check timestamp
			$query = $dbo->getQuery(true);
			$query->update($dbo->quoteName('#__update_sites'));
			$query->set($dbo->quoteName('last_check_timestamp') . ' = ' . $dbo->quote($now));
			$query->where($dbo->quoteName('update_site_id') . ' = ' . $dbo->quote($result['update_site_id']));
			$dbo->setQuery($query);
			$dbo->execute();
		}
		return $retval;
	}

	/**
	 * Multidimensional array safe unique test
	 *
	 * @param   array  $myArray  The source array.
	 *
	 * @return  array
	 *
	 * @deprecated    12.1
	 * @note    Use JArrayHelper::arrayUnique() instead.
	 * @note    Borrowed from PHP.net
	 * @see     http://au2.php.net/manual/en/function.array-unique.php
	 * @since   11.1
	 *
	 */
	public function arrayUnique($myArray)
	{
		JLog::add('JUpdater::arrayUnique() is deprecated. See JArrayHelper::arrayUnique() . ', JLog::WARNING, 'deprecated');
		return JArrayHelper::arrayUnique($myArray);
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
		if ($update->loadFromXML($updaterow->detailsurl))
		{
			return $update->install();
		}
		return false;
	}
}
