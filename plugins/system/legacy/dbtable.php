<?php
/**
* @version		$Id$
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
JLoader::register('JTable', JPATH_LIBRARIES.DS.'joomla'.DS.'database'.DS.'table.php');

/**
 * Legacy class, derive from {@link JTable} instead
 *
 * @deprecated	As of version 1.5
 * @package	Joomla.Legacy
 * @subpackage	1.5
 */
class mosDBTable extends JTable
{
	/**
	 * Error number
	 *
	 * @var		string
	 * @access	protected
	 */
	var $_error = '';

	/**
	 * Error number
	 *
	 * @var		int
	 * @access	protected
	 */
	var $_errorNum = 0;

	/**
	 * Constructor
	 */
	function __construct($table, $key, &$db)
	{
		parent::__construct( $table, $key, $db );
	}

	function mosDBTable($table, $key, &$db)
	{
		parent::__construct( $table, $key, $db );
	}

	/**
	 * Legacy Method, use {@link JTable::reorder()} instead
	 * @deprecated As of 1.5
	 */
	function updateOrder( $where='' )
	{
		return $this->reorder( $where );
	}

	/**
	 * Legacy Method, use {@link JTable::publish()} instead
	 * @deprecated As of 1.0.3
	 */
	function publish_array( $cid=null, $publish=1, $user_id=0 )
	{
		$this->publish( $cid, $publish, $user_id );
	}

	/**
	 * Legacy Method, make sure you use {@link JRequest::get()} or {@link JRequest::getVar()} instead
	 * @deprecated As of 1.5
	 */
	function filter( $ignoreList=null )
	{
		$ignore = is_array( $ignoreList );

		$filter = & JFilterInput::getInstance();
		foreach ($this->getProperties() as $k => $v)
		{
			if ($ignore && in_array( $k, $ignoreList ) ) {
				continue;
			}
			$this->$k = $filter->clean( $this->$k );
		}
	}

	/**
	 * Legacy Method, use {@link JObject::getProperties()}  instead
	 * @deprecated As of 1.5
	 */
	function getPublicProperties()
	{
		$properties = $this->getProperties();
		return array_keys($properties);
	}

	/**
	 * Legacy Method, use {@link JObject::getError()}  instead
	 * @deprecated As of 1.5
	 */
	function getError($i = null, $toString = true )
	{
		return $this->_error;
	}

	/**
	 * Legacy Method, use {@link JObject::setError()}  instead
	 * @deprecated As of 1.5
	 */
	function setErrorNum( $value )
	{
		$this->_errorNum = $value;
	}

	/**
	 * Legacy Method, use {@link JObject::getError()}  instead
	 * @deprecated As of 1.5
	 */
	function getErrorNum()
	{
		return $this->_errorNum;
	}
}