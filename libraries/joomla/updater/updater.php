<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Updater
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.archive');
jimport('joomla.filesystem.path');
jimport('joomla.base.adapter');
jimport('joomla.utilities.arrayhelper');
jimport('joomla.log.log');

/**
 * Updater Class
 * @package     Joomla.Platform
 * @subpackage  Update
 * @since       11.1
 */
class JUpdater extends JAdapter {

	/**
	 * Constructor
	 * 
	 * @since   11.1
	 */
	public function __construct() {
		// Adapter base path, class prefix
		parent::__construct(dirname(__FILE__),'JUpdater');
	}

	/**
	 * Returns a reference to the global Installer object, only creating it
	 * if it doesn't already exist.
	 *
	 * @return  object  An installer object
	 * @since   11.1
	 */
	public static function &getInstance()
	{
		static $instance;

		if (!isset ($instance)) {
			$instance = new JUpdater;
		}
		return $instance;
	}

	/**
	 * Finds an update for an extension
	 *
	 * @param   integer  $eid  Extension Identifier; if zero use all sites
	 *
	 * @return  boolean True if there are updates
	 * @since   11.1
	 */
	public function findUpdates($eid=0) {
		$dbo = $this->getDBO();
		$retval = false;
		// Push it into an array
		if(!is_array($eid)) {
			$query = 'SELECT DISTINCT update_site_id, type, location FROM #__update_sites WHERE enabled = 1';
		} else {
			$query = 'SELECT DISTINCT update_site_id, type, location FROM #__update_sites WHERE update_site_id IN (SELECT update_site_id FROM #__update_sites_extensions WHERE extension_id IN ('. implode(',', $eid) .'))';
		}
		$dbo->setQuery($query);
		$results = $dbo->loadAssocList();
		$result_count = count($results);
		for($i = 0; $i < $result_count; $i++)
		{
			$result = &$results[$i];
			$this->setAdapter($result['type']);
			if(!isset($this->_adapters[$result['type']])) {
				// Ignore update sites requiring adapters we don't have installed
				continue;
			}
			$update_result = $this->_adapters[$result['type']]->findUpdate($result);
			if(is_array($update_result))
			{
				if(array_key_exists('update_sites',$update_result) && count($update_result['update_sites']))
				{
					$results = JArrayHelper::arrayUnique(array_merge($results, $update_result['update_sites']));
					$result_count = count($results);
				}
				if(array_key_exists('updates', $update_result) && count($update_result['updates']))
				{
					for($k = 0, $count = count($update_result['updates']); $k < $count; $k++)
					{
						$current_update = &$update_result['updates'][$k];
						$update = JTable::getInstance('update');
						$extension = JTable::getInstance('extension');
						$uid = $update->find(Array('element'=>strtolower($current_update->get('element')),
								'type'=>strtolower($current_update->get('type')),
								'client_id'=>strtolower($current_update->get('client_id')),
								'folder'=>strtolower($current_update->get('folder'))));

						$eid = $extension->find(Array('element'=>strtolower($current_update->get('element')),
								'type'=>strtolower($current_update->get('type')),
								'client_id'=>strtolower($current_update->get('client_id')),
								'folder'=>strtolower($current_update->get('folder'))));
						if(!$uid)
						{
							// Set the extension id
							if($eid)
							{
								// We have an installed extension, check the update is actually newer
								$extension->load($eid);
								$data = json_decode($extension->manifest_cache, true);
								if(version_compare($current_update->version, $data['version'], '>') == 1)
								{
									$current_update->extension_id = $eid;
									$current_update->store();
								}
							} else
							{
								// A potentially new extension to be installed
								$current_update->store();
							}
						} else
						{
							$update->load($uid);
							// if there is an update, check that the version is newer then replaces
							if(version_compare($current_update->version, $update->version, '>') == 1)
							{
								$current_update->store();
							}
						}
					}
				}
				$update_result = true;
			} else if ($retval) {
				$update_result = true;
			}
		}
		return $retval;
	}

	/**
	 * Multidimensional array safe unique test
	 * 
	 * @param   array  $myarray
	 * 
	 * @return  array
	 * @since   11.1
	 * Borrowed from PHP.net
	 * @see http://au2.php.net/manual/en/function.array-unique.php
	 *
	 * @deprecated	11.1	Use JArrayHelper::arrayUnique() instead.
	 */
	public function arrayUnique($myArray)
	{
		JLog::add('JUpdater::arrayUnique() is deprecated. See JArrayHelper::arrayUnique().', JLog::WARNING, 'deprecated');
		return JArrayHelper::arrayUnique($myArray);
	}
	/**
	 * Finds an update for an extension
	 *
	 * @param   integer  $id
	 *
	 * @return  mixed
	 * @since   11.1
	 */
	public function update($id)
	{
		$updaterow = JTable::getInstance('update');
		$updaterow->load($id);
		$update = new JUpdate();
		if($update->loadFromXML($updaterow->detailsurl)) {
			return $update->install();
		}
		return false;
	}
}
