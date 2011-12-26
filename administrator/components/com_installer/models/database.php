<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_installer
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

// Import library dependencies
require_once dirname(__FILE__) . '/extension.php';

/**
 * Installer Manage Model
 *
 * @package		Joomla.Administrator
 * @subpackage	com_installer
 * @since		1.6
 */
class InstallerModelDatabase extends InstallerModel
{
	protected $_context = 'com_installer.discover';

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication();
		$this->setState('message',$app->getUserState('com_installer.message'));
		$this->setState('extension_message',$app->getUserState('com_installer.extension_message'));
		$app->setUserState('com_installer.message','');
		$app->setUserState('com_installer.extension_message','');
		parent::populateState('name','asc');
	}

	public function fix()
	{
		$changeSet = $this->getItems();
		$changeSet->fix();
		$this->fixSchemaVersion($changeSet);
	}

	public function getItems()
	{
		$folder = JPATH_ADMINISTRATOR . '/components/com_admin/sql/updates/';
		$changeSet = JSchemaChangeset::getInstance(JFactory::getDbo(), $folder);
		return $changeSet;
	}

	public function getPagination()
	{
		return true;
	}

	/**
	* Get version from #__schemas table
	* @throws Exception
	*/

	public function getSchemaVersion() {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('version_id')->from($db->qn('#__schemas'))
			->where('extension_id = 700');
		$db->setQuery($query);
		$result = $db->loadResult();
		if ($db->getErrorNum()) {
			throw new Exception('Database error - getSchemaVersion');
		}
		return $result;
	}

	/**
	* Fix schema version if wrong
	* @param JSchemaChangeSet
	* @return   mixed  string schema version if success, false if fail
	*/
	public function fixSchemaVersion($changeSet) {
		// Get correct schema version -- last file in array
		$schema = $changeSet->getSchema();
		$db = JFactory::getDbo();

		// Delete old row
		$query = $db->getQuery(true);
		$query->delete($db->qn('#__schemas'));
		$query->where($db->qn('extension_id') . ' = 700');
		$db->setQuery($query);
		$db->query();

		// Add new row
		$query = $db->getQuery(true);
		$query->insert($db->qn('#__schemas'));
		$query->set($db->qn('extension_id') . '= 700');
		$query->set($db->qn('version_id') . '= ' . $db->q($schema));
		$db->setQuery($query);
		if ($db->query()) {
			return $schema;
		} else {
			return false;
		}
	}

}
