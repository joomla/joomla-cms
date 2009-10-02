<?php
/**
 * Joomla! Update System
 *
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Updater
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.archive');
jimport('joomla.filesystem.path');
jimport('joomla.base.adapter');

/**
 * Updater Class
 * @since 1.6
 */
class JUpdater extends JAdapter {

	/**
	 * Constructor
	 */
	public function __construct() {
		// adapter base path, class prefix
		parent::__construct(dirname(__FILE__),'JUpdater');
	}

	/**
	 * Returns a reference to the global Installer object, only creating it
	 * if it doesn't already exist.
	 *
	 * @static
	 * @return	object	An installer object
	 */
	public function &getInstance()
	{
		static $instance;

		if (!isset ($instance)) {
			$instance = new JUpdater();
		}
		return $instance;
	}

	/**
	 * Finds an update for an extension
	 * @param int Extension Identifier; if zero use all sites
	 * @return boolean If there are updates or not
	 */
	public function findUpdates($eid=0) {
		$dbo =& $this->getDBO();
		$retval = false;
		// push it into an array
		if(!is_array($eid)) {
			$query = 'SELECT DISTINCT update_site_id, type, location FROM #__update_sites WHERE enabled = 1';
		} else {
			$query = 'SELECT DISTINCT update_site_id, type, location FROM #__update_sites WHERE update_site_id IN (SELECT update_site_id FROM #__update_sites_extensions WHERE extension_id IN ('. implode(',', $eid) .'))';
		}
		$dbo->setQuery($query);
		$results = $dbo->loadAssocList();
		$result_count = count($results);
		for($i = 0; $i < $result_count; $i++) {
			$result =& $results[$i];
			$this->setAdapter($result['type']);
			$update_result = $this->_adapters[$result['type']]->findUpdate($result);
			if(is_array($update_result)) {
				if(array_key_exists('update_sites',$update_result) && count($update_result['update_sites'])) {
					$results = $this->arrayUnique(array_merge($results, $update_result['update_sites']));
					$result_count = count($results);
				}
				if(array_key_exists('updates', $update_result) && count($update_result['updates'])) {
					for($k = 0; $k < count($update_result['updates']); $k++) {
						$current_update =& $update_result['updates'][$k];
						$update =& JTable::getInstance('update');
						$extension =& JTable::getInstance('extension');
						$uid = $update->find(Array('element'=>strtolower($current_update->get('element')),
								'type'=>strtolower($current_update->get('type')),
								'client_id'=>strtolower($current_update->get('client_id')),
								'folder'=>strtolower($current_update->get('folder'))));

						$eid = $extension->find(Array('element'=>strtolower($current_update->get('element')),
								'type'=>strtolower($current_update->get('type')),
								'client_id'=>strtolower($current_update->get('client_id')),
								'folder'=>strtolower($current_update->get('folder'))));
						if(!$uid) {
							// set the extension id
							if($eid) {
								// we have an installed extension, check the update is actually newer
								$extension->load($eid);
								$data = unserialize($extension->manifest_cache);
								if(version_compare($current_update->version, $data['version'], '>') == 1) {
									//echo '<p>Storing extension since '. $attrs['VERSION'] .' > ' . $data['version']. '</p>';
									$current_update->extension_id = $eid;
									$current_update->store();
								}
							} else {
								// a potentially new extension to be installed
								//echo '<p>Storing since no equivalent extension is installed</p>';
								$current_update->store();
							}
						} else {
							$update->load($uid);
							// if there is an update, check that the version is newer then replaces
							if(version_compare($current_update->version, $update->version, '>') == 1) {
								//echo '<p>Storing extension since '. $attrs['VERSION'] .' > ' . $data['version']. '</p>';
								$current_update->store();
							}
						}//else { echo '<p>Found a matching update for '. $attrs['NAME'] .'</p>';}
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
	 * Borrowed from PHP.net
	 * @url http://au2.php.net/manual/en/function.array-unique.php
	 */
	public function arrayUnique($myArray)
	{
	    if(!is_array($myArray))
	           return $myArray;

	    foreach ($myArray as &$myvalue){
	        $myvalue=serialize($myvalue);
	    }

	    $myArray=array_unique($myArray);

	    foreach ($myArray as &$myvalue){
	        $myvalue=unserialize($myvalue);
	    }

	    return $myArray;
	}

	public function update($id) {
		$updaterow =& JTable::getInstance('update');
		$updaterow->load($id);
		$update = new JUpdate();
		if($update->loadFromXML($updaterow->detailsurl)) {
			return $update->install();
		}
		return false;
	}
}
