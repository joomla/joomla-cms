<?php
/**
 * Abstract class for database schema change
 *
 * @package     CMS.Library
 * @subpackage  Schema
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Each object represents one query, which is one line from a DDS SQL query.
 * This class is used to check the site's database to see if the DDL query has been run.
 * If not, it provides the ability to fix the database by re-running the DDL query.
 * The queries are parsed from the update files in the folder
 * administrator/components/com_admin/sql/updates/<database>.
 * These updates are run automatically if the site was updated using com_installer.
 * However, it is possible that the program files could be updated without udpating
 * the database (for example, if a user just copies the new files over the top of an
 * existing installation).
 *
 * This is an abstract class. We need to extend it for each database and add a
 * buildCheckQuery() method that creates the query to check that a DDL query has been run.
 *
 * @package     CMS.Library
 * @subpackage  Schema
 * @since       2.5
 */
abstract class JSchemaChangeitem extends JObject
{
	/**
	* Update file: full path file name where query was found
	*
	* @var    string
	*/
	public $file = null;

	/**
	 * Update query: query used to change the db schema (one line from the file)
	 *
	 * @var    string
	 */
	public $updateQuery = null;

	/**
	* Check query: query used to check the db schema
	*
	* @var    string
	*/
	public $checkQuery = null;

	/**
	* Check query result: expected result of check query if database is up to date
	*
	* @var    string
	*/
	public $checkQueryExpected = 1;

	/**
	* JDatabase object
	*
	* @var    string
	*/
	public $db = null;

	/**
	 * Query type: To be used in building a language key for a
	 * message to tell user what was checked / changed
	 * Possible values: ADD_TABLE, ADD_COLUMN, CHANGE_COLUMN_TYPE, ADD_INDEX
	 *
	 * @var   string
	 *
	 */
	public $queryType = null;

	/**
	* Array with values for use in a JText::sprintf statment indicating what was checked
	*
	*   Tells you what the message should be, based on which elements are defined, as follows:
	*     For ADD_TABLE: table
	*     For ADD_COLUMN: table, column
	*     For CHANGE_COLUMN_TYPE: table, column, type
	*     For ADD_INDEX: table, index
	*
	* @var    array
	*/
	public $msgElements = array();

	/**
	* Checked status
	*
	* @var    int   0=not checked, -1=skipped, -2=failed, 1=succeeded
	*/
	public $checkStatus = 0;

	/**
	* Rerun status
	*
	* @var    int   0=not rerun, -1=skipped, -2=failed, 1=succeeded
	*/
	public $rerunStatus = 0;

	/**
	 * Constructor: builds check query and message from $updateQuery
	 *
	 * @param   JDatabase  $db
	 * @param   string     $file   full path name of the sql file
	 * @param   string     $query   text of the sql query (one line of the file)
	 * @param   string     $checkQuery
	 *
	 * @since   2.5
	 */
	public function __construct($db, $file, $updateQuery)
	{
		$this->updateQuery = $updateQuery;
		$this->file = $file;
		$this->db = $db;
		$this->buildCheckQuery();
	}

	/**
	 *
	 * Returns an instance of the correct schemachangeitem for the $db
	 *
	 * @param   JDatabase $db
	 * @param   string    $file   full path name of the sql file
	 * @param   string    $query  text of the sql query (one line of the file)
	 *
	 * @return  JSchemaChangeItem for the $db driver
	 *
	 * @since   2.5
	 */
	public static function getInstance($db, $file, $query)
	{
		$instance = null;
		// Get the class name (mysql and mysqli both use mysql)
		$dbname = (substr($db->name, 0, 5) == 'mysql') ? 'mysql' : $db->name;
		$path = dirname(__FILE__).'/' . 'changeitem' . $dbname . '.php'  ;
		$class = 'JSchemaChangeitem' . $dbname;

		// If the file exists register the class with our class loader.
		if (file_exists($path)) {
			JLoader::register($class, $path);
			$instance = new $class($db, $file, $query);
		}
		return $instance;

	}

	/**
	 * Runs the check query and checks that 1 row is returned
	 * If yes, return true, otherwise return false
	 *
	 * @return  boolean  true on success, false otherwise
	 *
	 * @since  2.5
	 */
	public function check()
	{
		$this->checkStatus = -1;
		if ($this->checkQuery) {
			$this->db->setQuery($this->checkQuery);
			$rows = $this->db->loadObject();
			if ($rows !== false) {
				if (count($rows) === $this->checkQueryExpected) {
					$this->checkStatus = 1;
				} else {
					$this->checkStatus = -2;
				}
			}
			else {
				$this->checkStatus = -2;
			}
		}
		return $this->checkStatus;
	}

	/**
	 * Runs the update query to apply the change to the database
	 *
	 * @since  2.5
	 */
	public function fix()
	{
		if ($this->checkStatus === -2) {
			// at this point we have a failed query
			$this->db->setQuery($this->updateQuery);
			if ($this->db->execute()) {
				if ($this->check()) {
					$this->checkStatus = 1;
					$this->rerunStatus = 1;
				} else {
					$this->rerunStatus = -2;
				}
			} else {
				$this->rerunStatus = -2;
			}
		}
	}
}
