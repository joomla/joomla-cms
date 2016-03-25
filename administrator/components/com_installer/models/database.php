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

		// Prepare the utf8mb4 conversion check table
		$this->prepareUtf8mb4StatusTable();

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

		/*
		 * Finally, if the schema updates succeeded, make sure the database is
		 * converted to utf8mb4 or, if not suported by the server, compatible to it.
		 */
		$statusArray = $changeSet->getStatus();

		if (count($statusArray['error']) == 0)
		{
			$this->convertTablesToUtf8mb4();
		}
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

		// Get conversion status and last md5 sums of SQL statements from database
		$db->setQuery('SELECT ' . $db->quoteName('converted')
			. ', ' . $db->quoteName('md5_file1')
			. ', ' . $db->quoteName('md5_file2')
			. ' FROM ' . $db->quoteName('#__utf8_conversion')
			. ' WHERE ' . $db->quoteName('extension_id') . ' = 700'
			);

		try
		{
			$dbRecord = $db->loadObject();
		}
		catch (Exception $e)
		{
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');

			return;
		}

		// Get the SQL statements (without comments) and md5sums from the 2 conversion scripts
		$queries1    = array();
		$queries2    = array();
		$md5NewFile1 = '';
		$md5NewFile2 = '';

		$fileName1 = JPATH_ADMINISTRATOR . "/components/com_admin/sql/others/mysql/utf8mb4-conversion-01.sql";
		$fileName2 = JPATH_ADMINISTRATOR . "/components/com_admin/sql/others/mysql/utf8mb4-conversion-02.sql";

		if (is_file($fileName1))
		{
			$fileContents1 = @file_get_contents($fileName1);
			$queries1 = $db->splitSql($fileContents1);

			if (!empty($queries1))
			{
				$md5NewFile1 = md5(serialize($queries1));
			}
		}

		if (is_file($fileName2))
		{
			$fileContents2 = @file_get_contents($fileName2);
			$queries2 = $db->splitSql($fileContents2);

			if (!empty($queries2))
			{
				$md5NewFile2 = md5(serialize($queries2));
			}
		}

		// Nothing to do if none of the files contained any query
		if (!$md5NewFile1 && !$md5NewFile2)
		{
			return;
		}

		// Check if utf8mb4 is supported and set required conversion status
		if ($db->hasUTF8mb4Support())
		{
			$converted = 2;
		}
		else
		{
			$converted = 1;
		}

		// Nothing to do if already converted to desired status and no change in SQL statements
		if ($dbRecord->converted == $converted
		&& $dbRecord->md5_file1 == $md5NewFile1
		&& $dbRecord->md5_file2 == $md5NewFile2)
		{
			return;
		}

		/*
		 * Step 1: Execute the first conversion script (if used) without error reporting.
		 * This is normally only dropping of indexes to be added back in step 2 but with
		 * lengths limitations for particular columns
		 */
		if ($md5NewFile1)
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

		/*
		 * Step 2: Execute the second conversion script (if used) with error reporting.
		 * If some error, the conversion status will be reset, and the old md5 sums
		 * will be kept.
		 */
		if ($md5NewFile2)
		{
			foreach ($queries2 as $query2)
			{
				try
				{
					$db->setQuery($query2)->execute();
				}
				catch (Exception $e)
				{
					$converted = 0;
					$md5NewFile1 = $dbRecord->md5_file1;
					$md5NewFile2 = $dbRecord->md5_file2;

					// Still render the error message from the Exception object
					JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
				}
			}
		}

		// Set flag in database if the update is done, and update md5 sums.
		$db->setQuery('UPDATE ' . $db->quoteName('#__utf8_conversion')
			. ' SET ' . $db->quoteName('converted') . ' = ' . $converted
			. ', ' . $db->quoteName('md5_file1') . ' = ' . $db->quote($md5NewFile1)
			. ', ' . $db->quoteName('md5_file2') . ' = ' . $db->quote($md5NewFile2)
			. ' WHERE ' . $db->quoteName('extension_id') . ' = 700')->execute();
	}

	/**
	 * Prepare the table to save the status of utf8mb4 conversion
	 * Make sure it has the latest structure and contains 1 initialized
	 * record.
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

		$table = $db->quoteName('#__utf8_conversion');
		$colExtId = $db->quoteName('extension_id');
		$colConverted = $db->quoteName('converted');
		$colMd5File1 = $db->quoteName('md5_file1');
		$colMd5File2 = $db->quoteName('md5_file2');

		// If the table does not exist, create it
		$creaTabSql = 'CREATE TABLE IF NOT EXISTS ' . $table
			. ' ('
			. $colExtId . ' int(11) NOT NULL DEFAULT 0,'
			. $colConverted . ' tinyint(4) NOT NULL DEFAULT 0,'
			. $colMd5File1 . ' varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT \'\','
			. $colMd5File2 . ' varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT \'\','
			. 'PRIMARY KEY (' . $colExtId
			. ')) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci';

		if (!$db->hasUTF8mb4Support())
		{
			$creaTabSql = $db->convertUtf8mb4QueryToUtf8($creaTabSql);
		}

		$db->setQuery($creaTabSql)->execute();

		// Check for missing extension_id column, i.e. table has old structure
		$db->setQuery('SHOW COLUMNS IN ' . $table . ' WHERE field = ' . $db->quote('extension_id'));

		try
		{
			$rows = $db->loadObject();
		}
		catch (RuntimeException $e)
		{
			$rows = false;
		}

		if ($rows === false)
		{
			$db->setQuery('ALTER TABLE ' . $table
				. ' ADD COLUMN ' . $colExtId 
				. ' int(11) NOT NULL DEFAULT 0, ADD PRIMARY KEY(' . $colExtId . ')')->execute();
		}

		// Check for missing md5_file1 column, i.e. table has old structure
		$db->setQuery('SHOW COLUMNS IN ' . $table . ' WHERE field = ' . $db->quote('md5_file1'));

		try
		{
			$rows = $db->loadObject();
		}
		catch (RuntimeException $e)
		{
			$rows = false;
		}

		if ($rows === false)
		{
			$db->setQuery('ALTER TABLE ' . $table . ' ADD COLUMN ' . $colMd5File1 . ' varchar(32) CHARACTER SET '
				. ($db->hasUTF8mb4Support() ? 'utf8mb4 COLLATE utf8mb4_bin' : 'utf8 COLLATE utf8_bin')
				. ' NOT NULL DEFAULT \'\'')->execute();
		}

		// Check for missing md5_file2 column, i.e. table has old structure
		$db->setQuery('SHOW COLUMNS IN ' . $table . ' WHERE field = ' . $db->quote('md5_file2'));

		try
		{
			$rows = $db->loadObject();
		}
		catch (RuntimeException $e)
		{
			$rows = false;
		}

		if ($rows === false)
		{
			$db->setQuery('ALTER TABLE ' . $table . ' ADD COLUMN ' . $colMd5File2 . ' varchar(32) CHARACTER SET '
				. ($db->hasUTF8mb4Support() ? 'utf8mb4 COLLATE utf8mb4_bin' : 'utf8 COLLATE utf8_bin')
				. ' NOT NULL DEFAULT \'\'')->execute();
		}

		// Check for inappropriate number of records with extension_id = 0
		$db->setQuery('SELECT COUNT(*) FROM ' . $table
			. ' WHERE ' . $colExtId . ' = 0');

		$count = $db->loadResult();

		if ($count > 1)
		{
			// Table messed up somehow, clear it
			$db->setQuery('DELETE FROM ' . $table . ' WHERE ' . $colExtId . ' = 0')->execute();
		}
		elseif ($count == 1)
		{
			// One record only: Must be the one for core
			$db->setQuery('UPDATE ' .  $table
				. ' SET ' . $colExtId . ' = 700 WHERE ' . $colExtId . ' = 0')->execute();
		}

		// Check for inappropriate number of records with extension_id = 700
		$db->setQuery('SELECT COUNT(*) FROM ' . $table
			. ' WHERE ' . $colExtId . ' = 700');

		$count = $db->loadResult();

		if ($count > 1)
		{
			// Table messed up somehow, clear it
			$db->setQuery('DELETE FROM ' . $table . ' WHERE ' . $colExtId . ' = 700')->execute();
			$db->setQuery('INSERT INTO ' . $table
				. ' (' . $colExtId . ', ' . $colConverted . ', ' . $colMd5File1 . ', ' . $colMd5File2
				. ') VALUES (700, 0, \'\', \'\')')->execute();
		}
		elseif ($count == 0)
		{
			// Record missing somehow, fix this
			$db->setQuery('INSERT INTO ' . $table
				. ' (' . $colExtId . ', ' . $colConverted . ', ' . $colMd5File1 . ', ' . $colMd5File2
				. ') VALUES (700, 0, \'\', \'\')')->execute();
		}
	}
}
