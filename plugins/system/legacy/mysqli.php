<?php
/**
* @version		$Id: classes.php 9198 2007-10-08 19:39:40Z jinx $
* @package		Joomla.Legacy
* @subpackage	1.5
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

// Register legacy classes for autoloading
JLoader::register('JDatabaseMySQLi', JPATH_LIBRARIES.DS.'joomla'.DS.'database'.DS.'database'.DS.'mysqli.php');

/**
 * Legacy class, use {@link JDatabase} instead
 *
 * @deprecated	As of version 1.5
 * @package	Joomla.Legacy
 * @subpackage	1.5
 */
class database extends JDatabaseMySQLi
{
	function __construct ($host='localhost', $user, $password, $database='', $prefix='', $offline = true)
	{
		$options        = array ( 'host' => $host, 'user' => $user, 'password' => $password, 'database' => $database, 'prefix' => $prefix );
		parent::__construct( $options );
	}

	/**
	* This global function loads the first row of a query into an object
	*
	* If an object is passed to this function, the returned row is bound to the existing elements of <var>object</var>.
	* If <var>object</var> has a value of null, then all of the returned query fields returned in the object.
	*
	* @param object The address of variable
	*/
	function loadObject( &$object )
	{
		if ($object != null)
		{
			if (!($cur = $this->query())) {
				return false;
			}

			if ($array = mysql_fetch_assoc( $cur ))
			{
				mysql_free_result( $cur );
				mosBindArrayToObject( $array, $object, null, null, false );
				return true;
			} else {
				return false;
			}

		}
		else
		{
			$object = parent::loadObject();
			return $object;
		}
	}

	/**
	* Execute a batch query
	*
	* @abstract
	* @access public
	* @return mixed A database resource if successful, FALSE if not.
	*/
	function query_batch( $abort_on_error=true, $p_transaction_safe = false)
	{
		return parent::queryBatch( $abort_on_error, $p_transaction_safe);
	}
}