<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_installer
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

// Import library dependencies
JLoader::register('InstallerModel', dirname(__FILE__) . '/extension.php');
JLoader::register('joomlaInstallerScript', JPATH_ADMINISTRATOR . '/components/com_admin/script.php');

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
		$this->setState('message', $app->getUserState('com_installer.message'));
		$this->setState('extension_message', $app->getUserState('com_installer.extension_message'));
		$app->setUserState('com_installer.message', '');
		$app->setUserState('com_installer.extension_message', '');
		parent::populateState('name', 'asc');
	}

	/**
	 *
	 * Fixes database problems
	 */
	public function fix()
	{
		$changeSet = $this->getItems();
		$changeSet->fix();
		$this->fixSchemaVersion($changeSet);
		$this->fixUpdateVersion();
		$installer = new joomlaInstallerScript();
		$installer->deleteUnexistingFiles();
		$this->fixDefaultTextFilters();
	}

	/**
	 *
	 * Gets the changeset object
	 *
	 * @return  JSchemaChangeset
	 */
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
	 *
	 * @return  mixed  the return value from the query, or null if the query fails
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
	 *
	 * @param JSchemaChangeSet
	 *
	 * @return   mixed  string schema version if success, false if fail
	 */
	public function fixSchemaVersion($changeSet)
	{
		// Get correct schema version -- last file in array
		$schema = $changeSet->getSchema();
		$db = JFactory::getDbo();
		$result = false;

		// Check value. If ok, don't do update
		$version = $this->getSchemaVersion();
		if ($version == $schema)
		{
			$result = $version;
		}
		else
		{
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
				$result = $schema;
			}
		}
		return $result;
	}

	/**
	 * Get current version from #__extensions table
	 *
	 * @return  mixed   version if successful, false if fail
	 */

	public function getUpdateVersion()
	{
		$table = JTable::getInstance('Extension');
		$table->load('700');
		$cache = new JRegistry($table->manifest_cache);
		return $cache->get('version');
	}

	/**
	 * Fix Joomla version in #__extensions table if wrong (doesn't equal JVersion short version)
	 *
	 * @return   mixed  string update version if success, false if fail
	 */
	public function fixUpdateVersion()
	{
		$table = JTable::getInstance('Extension');
		$table->load('700');
		$cache = new JRegistry($table->manifest_cache);
		$updateVersion =  $cache->get('version');
		$cmsVersion = new JVersion();
		if ($updateVersion == $cmsVersion->getShortVersion())
		{
			return $updateVersion;
		}
		else
		{
			$cache->set('version', $cmsVersion->getShortVersion());
			$table->manifest_cache = $cache->toString();
			if ($table->store())
			{
				return $cmsVersion->getShortVersion();
			}
			else
			{
				return false;
			}

		}
	}

	/**
	 * For version 2.5.x only
	 * Check if com_config parameters are blank.
	 *
	 * @return  string  default text filters (if any)
	 */
	public function getDefaultTextFilters()
	{
		$table = JTable::getInstance('Extension');
		$table->load($table->find(array('name' => 'com_config')));
		return $table->params;
	}
	/**
	 * For version 2.5.x only
	 * Check if com_config parameters are blank. If so, populate with com_content text filters.
	 *
	 * @return  mixed  boolean true if params are updated, null otherwise
	 */
	public function fixDefaultTextFilters()
	{
		$table = JTable::getInstance('Extension');
		$table->load($table->find(array('name' => 'com_config')));

		// Check for empty $config and non-empty content filters
		if (!$table->params)
		{
			// Get filters from com_content and store if you find them
			$contentParams = JComponentHelper::getParams('com_content');
			if ($contentParams->get('filters'))
			{
				$newParams = new JRegistry();
				$newParams->set('filters', $contentParams->get('filters'));
				$table->params = (string) $newParams;
				$table->store();
				return true;
			}
		}
	}
}

