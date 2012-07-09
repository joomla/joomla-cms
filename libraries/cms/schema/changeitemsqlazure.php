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
}
