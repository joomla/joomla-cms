<?php
/**
 * @package     CMS.Library
 * @subpackage  Schema
 *
* @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;
JLoader::register('JDatabaseSQLSrv', dirname(__FILE__) . '/Changeitemsqlsrv.php');

/**
 * Checks the database schema against one MySQL DDL query to see if it has been run.
 *
 * @package     CMS.Library
 * @subpackage  Schema
 * @since       2.5
 */
class JSchemaChangeitemsqlazure extends JSchemaChangeitemsqlsrv
{
	public $name = 'sqlazure';
	/**
	 *
	 * Checks a DDL query to see if it is a known type
	 * If yes, build a check query to see if the DDL has been run on the database.
	 * If successful, the $msgElements, $queryType, $checkStatus and $checkQuery fields are populated.
	 * The $msgElements contains the text to create the user message.
	 * The $checkQuery contains the SQL query to check whether the schema change has
	 * been run against the current database. The $queryType contains the type of 
	 * DDL query that was run (for example, CREATE_TABLE, ADD_COLUMN, CHANGE_COLUMN_TYPE, ADD_INDEX).
	 * The $checkStatus field is set to zero if the query is created
	 * 
	 * If not successful, $checkQuery is empty and , and $checkStatus is -1.
	 * For example, this will happen if the current line is a non-DDL statement.
	 *
	 * @return void
	 *
	 * @since  2.5
	 */
	

}
