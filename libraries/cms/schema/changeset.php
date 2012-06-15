<?php
/**
 * @package     CMS.Library
 * @subpackage  Schema
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
JLoader::register('JSchemaChangeitem', JPATH_LIBRARIES . '/cms/schema/changeitem.php');

/**
 * Contains a set of JSchemaChange objects for a particular instance of Joomla.
 * Each of these objects contains a DDL query that should have been run against
 * the database when this database was created or updated. This enables the
 * Installation Manager to check that the current database schema is up to date.
 *
 * @package     CMS.Library
 * @subpackage  Schema
 * @since       2.5
 */
class JSchemaChangeset extends JObject
{
	/**
	 * Array of JSchemaChangeItem objects
	 *
	 * @var    string
	 */
	protected $changeItems = array();

	/**
	* JDatabase object
	*
	* @var    string
	*/
	protected $db = null;

	/**
	* Folder where SQL update files will be found
	*
	* @var    string
	*/
	protected $folder = null;

	/**
	 * Constructor: builds array of $changeItems by processing the .sql files in a folder.
	 * The folder for the Joomla core updates is administrator/components/com_admin/sql/updates/<database>.
	 *
	 * @param   JDatabase  $db      The current database object
	 * @param   string     $folder  The full path to the folder containing the update queries
	 *
	 * @since   2.5
	 */
	public function __construct($db, $folder = null)
	{
		$this->db = $db;
		$this->folder = $folder;
		$updateFiles = $this->getUpdateFiles();
		$updateQueries = $this->getUpdateQueries($updateFiles);
		foreach ($updateQueries as $obj)
		{
			$this->changeItems[] = JSchemaChangeItem::getInstance($db, $obj->file, $obj->updateQuery);
		}
	}

	/**
	 * Returns the existing JSchemaChangeset object if it exists.
	 * Otherwise, it creates a new one.
	 *
	 * @param   JDatabase  $db      The current database object
	 * @param   string     $folder  The full path to the folder containing the update queries
	 *
	 * @return  JSchemaChangeSet    The (possibly chached) instance of JSchemaChangeSet
	 *
	 * @since   2.5
	 */
	public static function getInstance($db, $folder)
	{
		static $instance;
		if (!is_object($instance))
		{
			$instance = new JSchemaChangeSet($db, $folder);
		}
		return $instance;
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
		$result = new SplFileInfo(array_pop($updateFiles));
		return $result->getBasename('.sql');
	}

	/**
	 * Get list of SQL update files for this database
	 *
	 * @return  array  list of sql update full-path names
	 *
	 * @since   2.5
	 */
	private function getUpdateFiles()
	{
		// Get the folder from the database name
		$sqlFolder = $this->db->name;
		if (substr($sqlFolder, 0, 5) == 'mysql')
		{
			$sqlFolder = 'mysql';
		}

		// Default folder to core com_admin
		if (!$this->folder)
		{
			$this->folder = JPATH_ADMINISTRATOR . '/components/com_admin/sql/updates/';
		}
		return JFolder::files($this->folder . '/' . $sqlFolder, '\.sql$', 1, true);
	}

	/**
	 * Get array of SQL queries
	 *
	 * @param   array  $sqlfiles  Array of .sql update filenames.
	 *
	 * @return  array  Array of stdClass objects where:
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
			$queries = $this->db->splitSql($buffer);
			foreach ($queries as $query)
			{
				if (trim($query))
				{
					$fileQueries = new stdClass;
					$fileQueries->file = $file;
					$fileQueries->updateQuery = $query;
					$result[] = $fileQueries;
				}
			}
		}
		return $result;
	}

}
