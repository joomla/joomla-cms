<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Schema;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Filesystem\Folder;
use Joomla\Database\DatabaseDriver;

/**
 * Contains a set of JSchemaChange objects for a particular instance of Joomla.
 * Each of these objects contains a DDL query that should have been run against
 * the database when this database was created or updated. This enables the
 * Installation Manager to check that the current database schema is up to date.
 *
 * @since  2.5
 */
class ChangeSet
{
	/**
	 * Array of ChangeItem objects
	 *
	 * @var    ChangeItem[]
	 * @since  2.5
	 */
	protected $changeItems = array();

	/**
	 * DatabaseDriver object
	 *
	 * @var    DatabaseDriver
	 * @since  2.5
	 */
	protected $db = null;

	/**
	 * Folder where SQL update files will be found
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $folder = null;

	/**
	 * The singleton instance of this object
	 *
	 * @var    ChangeSet
	 * @since  3.5.1
	 */
	protected static $instance;

	/**
	 * Constructor: builds array of $changeItems by processing the .sql files in a folder.
	 * The folder for the Joomla core updates is `administrator/components/com_admin/sql/updates/<database>`.
	 *
	 * @param   DatabaseDriver  $db      The current database object
	 * @param   string          $folder  The full path to the folder containing the update queries
	 *
	 * @since   2.5
	 */
	public function __construct($db, $folder = null)
	{
		$this->db = $db;
		$this->folder = $folder;
		$updateFiles = $this->getUpdateFiles();

		// If no files were found nothing more we can do - continue
		if ($updateFiles === false)
		{
			return;
		}

		$updateQueries = $this->getUpdateQueries($updateFiles);

		foreach ($updateQueries as $obj)
		{
			$this->changeItems[] = ChangeItem::getInstance($db, $obj->file, $obj->updateQuery);
		}

		// If on mysql, add a query at the end to check for utf8mb4 conversion status
		if ($this->db->getServerType() === 'mysql')
		{
			// Check if the #__utf8_conversion table exists
			$this->db->setQuery('SHOW TABLES LIKE ' . $this->db->quote($this->db->getPrefix() . 'utf8_conversion'));

			try
			{
				$rows = $this->db->loadRowList(0);

				$tableExists = \count($rows);
			}
			catch (\RuntimeException $e)
			{
				$tableExists = 0;
			}

			// If the table exists add a change item for utf8mb4 conversion to the end
			if ($tableExists > 0)
			{
				// Let the update query do nothing
				$tmpSchemaChangeItem = ChangeItem::getInstance(
					$db,
					'database.php',
					'UPDATE ' . $this->db->quoteName('#__utf8_conversion')
					. ' SET ' . $this->db->quoteName('converted') . ' = '
					. $this->db->quoteName('converted') . ';'
				);

				// Set to not skipped
				$tmpSchemaChangeItem->checkStatus = 0;

				// Set the check query
				$tmpSchemaChangeItem->queryType = 'UTF8_CONVERSION_UTF8MB4';

				$tmpSchemaChangeItem->checkQuery = 'SELECT '
					. $this->db->quoteName('converted')
					. ' FROM ' . $this->db->quoteName('#__utf8_conversion')
					. ' WHERE ' . $this->db->quoteName('converted') . ' = 5';

				// Set expected records from check query
				$tmpSchemaChangeItem->checkQueryExpected = 1;

				$tmpSchemaChangeItem->msgElements = array();

				$this->changeItems[] = $tmpSchemaChangeItem;
			}
		}
	}

	/**
	 * Returns a reference to the ChangeSet object, only creating it if it doesn't already exist.
	 *
	 * @param   DatabaseDriver  $db      The current database object
	 * @param   string          $folder  The full path to the folder containing the update queries
	 *
	 * @return  ChangeSet
	 *
	 * @since   2.5
	 */
	public static function getInstance($db, $folder = null)
	{
		if (!\is_object(static::$instance))
		{
			static::$instance = new static($db, $folder);
		}

		return static::$instance;
	}

	/**
	 * Checks the database and returns an array of any errors found.
	 * Note these are not database errors but rather situations where
	 * the current schema is not up to date.
	 *
	 * @return   array Array of errors if any.
	 *
	 * @since    2.5
	 */
	public function check()
	{
		$errors = array();

		foreach ($this->changeItems as $item)
		{
			if ($item->check() === -2)
			{
				// Error found
				$errors[] = $item;
			}
		}

		return $errors;
	}

	/**
	 * Runs the update query to apply the change to the database
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public function fix()
	{
		$this->check();

		foreach ($this->changeItems as $item)
		{
			$item->fix();
		}
	}

	/**
	 * Returns an array of results for this set
	 *
	 * @return  array  associative array of changeitems grouped by unchecked, ok, error, and skipped
	 *
	 * @since   2.5
	 */
	public function getStatus()
	{
		$result = array('unchecked' => array(), 'ok' => array(), 'error' => array(), 'skipped' => array());

		foreach ($this->changeItems as $item)
		{
			switch ($item->checkStatus)
			{
				case 0:
					$result['unchecked'][] = $item;
					break;
				case 1:
					$result['ok'][] = $item;
					break;
				case -2:
					$result['error'][] = $item;
					break;
				case -1:
					$result['skipped'][] = $item;
					break;
			}
		}

		return $result;
	}

	/**
	 * Gets the current database schema, based on the highest version number.
	 * Note that the .sql files are named based on the version and date, so
	 * the file name of the last file should match the database schema version
	 * in the #__schemas table.
	 *
	 * @return  string  the schema version for the database
	 *
	 * @since   2.5
	 */
	public function getSchema()
	{
		$updateFiles = $this->getUpdateFiles();

		// No schema files found - abort and return empty string
		if (empty($updateFiles))
		{
			return '';
		}

		$result = new \SplFileInfo(array_pop($updateFiles));

		return $result->getBasename('.sql');
	}

	/**
	 * Get list of SQL update files for this database
	 *
	 * @return  array|boolean  list of sql update full-path names. False if directory doesn't exist
	 *
	 * @since   2.5
	 */
	private function getUpdateFiles()
	{
		// Get the folder from the database name
		$sqlFolder = $this->db->getServerType();

		// For `mssql` server types, convert the type to `sqlazure`
		if ($sqlFolder === 'mssql')
		{
			$sqlFolder = 'sqlazure';
		}

		// Default folder to core com_admin
		if (!$this->folder)
		{
			$this->folder = JPATH_ADMINISTRATOR . '/components/com_admin/sql/updates/';
		}

		// We don't want to enqueue an error if the directory doesn't exist - this can be handled elsewhere/
		// So bail here.
		if (!is_dir($this->folder . '/' . $sqlFolder))
		{
			return [];
		}

		return Folder::files(
			$this->folder . '/' . $sqlFolder, '\.sql$', 1, true, array('.svn', 'CVS', '.DS_Store', '__MACOSX'), array('^\..*', '.*~'), true
		);
	}

	/**
	 * Get array of SQL queries
	 *
	 * @param   array  $sqlfiles  Array of .sql update filenames.
	 *
	 * @return  array  Array of \stdClass objects where:
	 *                    file=filename,
	 *                    update_query = text of SQL update query
	 *
	 * @since   2.5
	 */
	private function getUpdateQueries(array $sqlfiles)
	{
		// Hold results as array of objects
		$result = array();

		foreach ($sqlfiles as $file)
		{
			$buffer = file_get_contents($file);

			// Create an array of queries from the sql file
			$queries = DatabaseDriver::splitSql($buffer);

			foreach ($queries as $query)
			{
				$fileQueries = new \stdClass;
				$fileQueries->file = $file;
				$fileQueries->updateQuery = $query;
				$result[] = $fileQueries;
			}
		}

		return $result;
	}
}
