<?php
/**
* @version		$Id$
* @package		Joomla.Legacy
* @subpackage	1.5
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
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
JLoader::register('JTableUser', JPATH_LIBRARIES.DS.'joomla'.DS.'database'.DS.'table'.DS.'user.php');

/**
 * Legacy class, use {@link JTableUser} instead
 *
 * @deprecated	As of version 1.5
 * @package	Joomla.Legacy
 * @subpackage	1.5
 */
class mosUser extends JTableUser
{
	/**
	 * Constructor
	 */
	function __construct(&$db)
	{
		parent::__construct( $db );
	}

	function mosUser(&$db)
	{
		parent::__construct( $db);
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
	 * Returns a complete user list
	 *
	 * @return array
	 * @deprecated As of 1.5
	 */
	function getUserList()
	{
		$this->_db->setQuery("SELECT username FROM #__users");
		return $this->_db->loadAssocList();
	}

	/**
	 * Gets the users from a group
	 *
	 * @param	string	The value for the group
	 * @param	string	The name for the group
	 * @param	string	If RECURSE, will drill into child groups
	 * @param	string	Ordering for the list
	 * @return	array
	 * @deprecated As of 1.5
	 */
	function getUserListFromGroup( $value, $name, $recurse='NO_RECURSE', $order='name' )
	{
		$acl =& JFactory::getACL();

		// Change back in
		$group_id = $acl->get_group_id( $value, $name, 'ARO');
		$objects = $acl->get_group_objects( $group_id, 'ARO', 'RECURSE');

		if (isset( $objects['users'] ))
		{
			$gWhere = '(id =' . implode( ' OR id =', $objects['users'] ) . ')';

			$query = 'SELECT id AS value, name AS text'
			. ' FROM #__users'
			. ' WHERE block = "0"'
			. ' AND ' . $gWhere
			. ' ORDER BY '. $order
			;
			$this->_db->setQuery( $query );
			$options = $this->_db->loadObjectList();
			return $options;
		} else {
			return array();
		}
	}
}