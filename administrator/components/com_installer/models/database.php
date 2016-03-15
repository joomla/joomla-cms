<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

JLoader::register('InstallerModel', __DIR__ . '/extension.php');
JLoader::register('JoomlaInstallerScript', JPATH_ADMINISTRATOR . '/components/com_admin/script.php');

/**
 * Installer Manage Model
 *
 * @since  1.6
 */
class InstallerModelDatabase extends InstallerModel
{
	protected $_context = 'com_installer.discover';

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   1.6
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
	 * Fixes database problems.
	 *
	 * @return  void
	 */
	public function fix()
	{
		// Prepare the utf8mb4 conversion check table
		$this->prepareUtf8mb4StatusTable();

		if (!$changeSet = $this->getItems())
		{
			return false;
		}

		$changeSet->fix();
		$this->fixSchemaVersion($changeSet);
		$this->fixUpdateVersion();
		$installer = new JoomlaInstallerScript;
		$installer->deleteUnexistingFiles();
		$this->fixDefaultTextFilters();

		// Finally make sure the database is converted to utf8mb4 or, if not suported
		// by the server, compatible to it
		$this->convertTablesToUtf8mb4();
	}

	/**
	 * Gets the changeset object.
	 *
	 * @return  JSchemaChangeset
	 */
	public function getItems()
	{
		$folder = JPATH_ADMINISTRATOR . '/components/com_admin/sql/updates/';

		try
		{
			$changeSet = JSchemaChangeset::getInstance($this->getDbo(), $folder);
		}
		catch (RuntimeException $e)
		{
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'warning');

			return false;
		}
		return $changeSet;
	}

	/**
	 * Method to get a JPagination object for the data set.
	 *
	 * @return  boolean
	 *
	 * @since   12.2
	 */
	public function getPagination()
	{
		return true;
	}

	/**
	 * Get version from #__schemas table.
	 *
	 * @return  mixed  the return value from the query, or null if the query fails.
	 *
	 * @throws Exception
	 */
	public function getSchemaVersion()
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->select('version_id')
			->from($db->quoteName('#__schemas'))
			->where('extension_id = 700');
		$db->setQuery($query);
		$result = $db->loadResult();

		return $result;
	}

	/**
	 * Fix schema version if wrong.
	 *
	 * @param   JSchemaChangeSet  $changeSet  Schema change set.
	 *
	 * @return   mixed  string schema version if success, false if fail.
	 */
	public function fixSchemaVersion($changeSet)
	{
		// Get correct schema version -- last file in array.
		$schema = $changeSet->getSchema();

		// Check value. If ok, don't do update.
		if ($schema == $this->getSchemaVersion())
		{
			return $schema;
		}

		// Delete old row.
		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__schemas'))
			->where($db->quoteName('extension_id') . ' = 700');
		$db->setQuery($query);
		$db->execute();

		// Add new row.
		$query->clear()
			->insert($db->quoteName('#__schemas'))
			->columns($db->quoteName('extension_id') . ',' . $db->quoteName('version_id'))
			->values('700, ' . $db->quote($schema));
		$db->setQuery($query);

		if (!$db->execute())
		{
			return false;
		}

		return $schema;
	}

	/**
	 * Get current version from #__extensions table.
	 *
	 * @return  mixed   version if successful, false if fail.
	 */

	public function getUpdateVersion()
	{
		$table = JTable::getInstance('Extension');
		$table->load('700');
		$cache = new Registry($table->manifest_cache);

		return $cache->get('version');
	}

	/**
	 * Fix Joomla version in #__extensions table if wrong (doesn't equal JVersion short version).
	 *
	 * @return   mixed  string update version if success, false if fail.
	 */
	public function fixUpdateVersion()
	{
		$table = JTable::getInstance('Extension');
		$table->load('700');
		$cache = new Registry($table->manifest_cache);
		$updateVersion = $cache->get('version');
		$cmsVersion = new JVersion;

		if ($updateVersion == $cmsVersion->getShortVersion())
		{
			return $updateVersion;
		}

		$cache->set('version', $cmsVersion->getShortVersion());
		$table->manifest_cache = $cache->toString();

		if ($table->store())
		{
			return $cmsVersion->getShortVersion();
		}

		return false;
	}

	/**
	 * For version 2.5.x only
	 * Check if com_config parameters are blank.
	 *
	 * @return  string  default text filters (if any).
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
	 * @return  mixed  boolean true if params are updated, null otherwise.
	 */
	public function fixDefaultTextFilters()
	{
		$table = JTable::getInstance('Extension');
		$table->load($table->find(array('name' => 'com_config')));

		// Check for empty $config and non-empty content filters.
		if (!$table->params)
		{
			// Get filters from com_content and store if you find them.
			$contentParams = JComponentHelper::getParams('com_content');

			if ($contentParams->get('filters'))
			{
				$newParams = new Registry;
				$newParams->set('filters', $contentParams->get('filters'));
				$table->params = (string) $newParams;
				$table->store();

				return true;
			}
		}
	}

	/**
	 * Converts the site's database tables to support UTF-8 Multibyte
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	public function convertTablesToUtf8mb4()
	{
		$db = JFactory::getDbo();

		// Get the SQL file to convert the core tables. Yes, this is hardcoded because we have all sorts of index
		// conversions and funky things we can't automate in core tables without an actual SQL file.
		$serverType = $db->getServerType();

		if ($serverType != 'mysql')
		{
			return;
		}

		// Set required conversion status
		if ($db->hasUTF8mb4Support())
		{
			$converted = 2;
		}
		else
		{
			$converted = 1;
		}

		// Check conversion status in database
		$db->setQuery('SELECT ' . $db->quoteName('converted')
			. ' FROM ' . $db->quoteName('#__utf8_conversion')
			);

		try
		{
			$convertedDB = $db->loadResult();
		}
		catch (Exception $e)
		{
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');

			return;
		}

		// Nothing to do, saved conversion status from DB is equal to required
		if ($convertedDB == $converted)
		{
			return;
		}

		// Step 1: Drop indexes later to be added again with column lengths limitations at step 2
		$fileName1 = JPATH_ADMINISTRATOR . "/components/com_admin/sql/others/$serverType/utf8mb4-conversion-01.sql";

		if (is_file($fileName1))
		{
			$fileContents1 = @file_get_contents($fileName1);
			$queries1 = $db->splitSql($fileContents1);

			if (!empty($queries1))
			{
				foreach ($queries1 as $query1)
				{
					try
					{
						$db->setQuery($query1)->execute();
					}
					catch (Exception $e)
					{
						// If the query fails we will go on. It just means the index to be dropped does not exist.
					}
				}
			}
		}

		// Step 2: Perform the index modifications and conversions
		$fileName2 = JPATH_ADMINISTRATOR . "/components/com_admin/sql/others/$serverType/utf8mb4-conversion-02.sql";

		if (is_file($fileName2))
		{
			$fileContents2 = @file_get_contents($fileName2);
			$queries2 = $db->splitSql($fileContents2);

			if (!empty($queries2))
			{
				foreach ($queries2 as $query2)
				{
					try
					{
						$db->setQuery($db->convertUtf8mb4QueryToUtf8($query2))->execute();
					}
					catch (Exception $e)
					{
						$converted = 0;

						// Still render the error message from the Exception object
						JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
					}
				}
			}
		}

		// Set flag in database if the update is done.
		$db->setQuery('UPDATE ' . $db->quoteName('#__utf8_conversion')
			. ' SET ' . $db->quoteName('converted') . ' = ' . $converted . ';')->execute();
	}

	/**
	 * Prepare the table to save the status of utf8mb4 conversion
	 * Make sure it contains 1 initialized record if there is not
	 * already exactly 1 record.
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	private function prepareUtf8mb4StatusTable()
	{
		$db = JFactory::getDbo();

		$serverType = $db->getServerType();

		if ($serverType != 'mysql')
		{
			return;
		}

		$creaTabSql = 'CREATE TABLE IF NOT EXISTS ' . $db->quoteName('#__utf8_conversion')
			. ' (' . $db->quoteName('converted') . ' tinyint(4) NOT NULL DEFAULT 0'
			. ') ENGINE=InnoDB';

		if ($db->hasUTF8mb4Support())
		{
			$creaTabSql = $creaTabSql
				. ' DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;';
		}
		else
		{
			$creaTabSql = $creaTabSql
				. ' DEFAULT CHARSET=utf8 DEFAULT COLLATE=utf8_unicode_ci;';
		}

		$db->setQuery($creaTabSql)->execute();

		$db->setQuery('SELECT COUNT(*) FROM ' . $db->quoteName('#__utf8_conversion') . ';');

		$count = $db->loadResult();

		if ($count > 1)
		{
			// Table messed up somehow, clear it
			$db->setQuery('DELETE FROM ' . $db->quoteName('#__utf8_conversion')
				. ';')->execute();
			$db->setQuery('INSERT INTO ' . $db->quoteName('#__utf8_conversion')
				. ' (' . $db->quoteName('converted') . ') VALUES (0);')->execute();
		}
		elseif ($count == 0)
		{
			// Record missing somehow, fix this
			$db->setQuery('INSERT INTO ' . $db->quoteName('#__utf8_conversion')
				. ' (' . $db->quoteName('converted') . ') VALUES (0);')->execute();
		}
	}
}
