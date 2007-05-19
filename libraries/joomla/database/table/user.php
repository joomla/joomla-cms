<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	Table
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

/**
 * Users table
 *
 * @package 	Joomla.Framework
 * @subpackage		Table
 * @since	1.0
 */
class JTableUser extends JTable
{
	/**
	 * Unique id
	 *
	 * @var int
	 */
	var $id				= null;

	/**
	 * The users real name (or nickname)
	 *
	 * @var string
	 */
	var $name			= null;

	/**
	 * The login name
	 *
	 * @var string
	 */
	var $username		= null;

	/**
	 * The email
	 *
	 * @var string
	 */
	var $email			= null;

	/**
	 * MD5 encrypted password
	 *
	 * @var string
	 */
	var $password		= null;

	/**
	 * Description
	 *
	 * @var string
	 */
	var $usertype		= null;

	/**
	 * Description
	 *
	 * @var int
	 */
	var $block			= null;

	/**
	 * Description
	 *
	 * @var int
	 */
	var $sendEmail		= null;

	/**
	 * The group id number
	 *
	 * @var int
	 */
	var $gid			= null;

	/**
	 * Description
	 *
	 * @var datetime
	 */
	var $registerDate	= null;

	/**
	 * Description
	 *
	 * @var datetime
	 */
	var $lastvisitDate	= null;

	/**
	 * Description
	 *
	 * @var string activation hash
	 */
	var $activation		= null;

	/**
	 * Description
	 *
	 * @var string
	 */
	var $params			= null;

	/**
	* @param database A database connector object
	*/
	function __construct( &$db )
	{
		parent::__construct( '#__users', 'id', $db );

		//initialise
		$this->id        = 0;
		$this->gid       = 0;
		$this->sendEmail = 1;
	}

	/**
	 * Validation and filtering
	 *
	 * @return boolean True is satisfactory
	 */
	function check()
	{
		// Validate user information
		if (trim( $this->name ) == '') {
			$this->setError( JText::_( 'Please enter your name.' ) );
			return false;
		}

		if (trim( $this->username ) == '') {
			$this->setError( JText::_( 'Please enter a user name.') );
			return false;
		}


		if (eregi( "[\<|\>|\"|\'|\%|\;|\(|\)|\&|\+|\-]", $this->username) || JString::strlen( $this->username ) < 2) {
			$this->setError( JText::sprintf( 'VALID_AZ09', JText::_( 'Username' ), 2 ) );
			return false;
		}

		if ((trim($this->email == "")) || (preg_match("/[\w\.\-]+@\w+[\w\.\-]*?\.\w{1,4}/", $this->email )==false)) {
			$this->setError( JText::_( 'WARNREG_MAIL' ) );
			return false;
		}

		// check for existing username
		$query = 'SELECT id'
		. ' FROM #__users '
		. ' WHERE username = "' . $this->username . '"'
		. ' AND id != '. $this->id;
		;
		$this->_db->setQuery( $query );
		$xid = intval( $this->_db->loadResult() );
		if ($xid && $xid != intval( $this->id )) {
			$this->setError( JText::_( 'WARNREG_INUSE' ) );
			return false;
		}


		// check for existing email
		$query = 'SELECT id'
			. ' FROM #__users '
			. ' WHERE email = "'. $this->email . '"'
			. ' AND id != '. $this->id
			;
		$this->_db->setQuery( $query );
		$xid = intval( $this->_db->loadResult() );
		if ($xid && $xid != intval( $this->id )) {
			$this->setError( JText::_( 'WARNREG_EMAIL_INUSE' ) );
			return false;
		}

		return true;
	}

	function store( $updateNulls=false )
	{
		$acl =& JFactory::getACL();

		$section_value = 'users';
		$k = $this->_tbl_key;
		$key =  $this->$k;

		if ($key)
		{
			// existing record
			$ret = $this->_db->updateObject( $this->_tbl, $this, $this->_tbl_key, $updateNulls );

			// syncronise ACL
			// single group handled at the moment
			// trivial to expand to multiple groups
			$object_id = $acl->get_object_id( $section_value, $this->$k, 'ARO' );

			$groups = $acl->get_object_groups( $object_id, 'ARO' );
			$acl->del_group_object( $groups[0], $section_value, $this->$k, 'ARO' );
			$acl->add_group_object( $this->gid, $section_value, $this->$k, 'ARO' );

			$acl->edit_object( $object_id, $section_value, $this->_db->getEscaped( $this->name ), $this->$k, 0, 0, 'ARO' );
		}
		else
		{
			// new record
			$ret = $this->_db->insertObject( $this->_tbl, $this, $this->_tbl_key );
			// syncronise ACL
			$acl->add_object( $section_value, $this->name, $this->$k, null, null, 'ARO' );
			$acl->add_group_object( $this->gid, $section_value, $this->$k, 'ARO' );
		}

		if( !$ret )
		{
			$this->setError( strtolower(get_class( $this ))."::". JText::_( 'store failed' ) ."<br />" . $this->_db->getErrorMsg() );
			return false;
		}
		else
		{
			return true;
		}
	}

	function delete( $oid=null )
	{
		$acl =& JFactory::getACL();

		$k = $this->_tbl_key;
		if ($oid) {
			$this->$k = intval( $oid );
		}
		$aro_id = $acl->get_object_id( 'users', $this->$k, 'ARO' );
		$acl->del_object( $aro_id, 'ARO', true );

		$query = 'DELETE FROM '. $this->_tbl
		. ' WHERE  '. $this->_tbl_key .' = "'. $this->$k .'"';
		;
		$this->_db->setQuery( $query );

		if ($this->_db->query()) {
			// cleanup related data

			// private messaging
			$query = 'DELETE FROM #__messages_cfg'
			. ' WHERE user_id = '. $this->$k
			;
			$this->_db->setQuery( $query );
			if (!$this->_db->query()) {
				$this->setError( $this->_db->getErrorMsg() );
				return false;
			}
			$query = 'DELETE FROM #__messages'
			. ' WHERE user_id_to = '. $this->$k
			;
			$this->_db->setQuery( $query );
			if (!$this->_db->query()) {
				$this->setError( $this->_db->getErrorMsg() );
				return false;
			}

			return true;
		} else {
			$this->setError( $this->_db->getErrorMsg() );
			return false;
		}
	}

	/**
	 * Updates last visit time of user
	 *
	 * @param int The timestamp, defaults to 'now'
	 * @return boolean False if an error occurs
	 */
	function setLastVisit( $timeStamp=null, $id=null )
	{
		// check for User ID
		if (is_null( $id )) {
			if (isset( $this )) {
				$id = $this->id;
			} else {
				// do not translate
				die( 'WARNMOSUSER' );
			}
		}
		// data check
		$id = intval( $id );

		// if no timestamp value is passed to functon, than current time is used
		if ( $timeStamp ) {
			$dateTime = date( 'Y-m-d H:i:s', $timeStamp );
		} else {
			$dateTime = date( 'Y-m-d H:i:s' );
		}

		// updates user lastvistdate field with date and time
		$query = 'UPDATE '. $this->_tbl
		. ' SET lastvisitDate = "'.$dateTime.'"'
		. ' WHERE id = '.$id
		;
		$this->_db->setQuery( $query );
		if (!$this->_db->query()) {
			$this->setError( $this->_db->getErrorMsg() );
			return false;
		}

		return true;
	}

	/**
	* Overloaded bind function
	*
	* @access public
	* @param array $hash named array
	* @return null|string	null is operation was satisfactory, otherwise returns an error
	* @see JTable:bind
	* @since 1.5
	*/

	function bind($array, $ignore = '')
	{
		if (key_exists( 'params', $array ) && is_array( $array['params'] )) {
			$registry = new JRegistry();
			$registry->loadArray($array['params']);
			$array['params'] = $registry->toString();
		}

		return parent::bind($array, $ignore);
	}

	/**
	 * Returns a complete user list
	 *
	 * @return array
	 */
	function getUserList()
	{
		$this->_db->setQuery("SELECT username FROM #__users");
		return $this->_db->loadAssocList();
	}

	/**
	 * Gets the users from a group
	 *
	 * @param string The value for the group (not used 1.0)
	 * @param string The name for the group
	 * @param string If RECURSE, will drill into child groups
	 * @param string Ordering for the list
	 * @return array
	 */
	function getUserListFromGroup( $value, $name, $recurse='NO_RECURSE', $order='name' )
	{
		$acl =& JFactory::getACL();

		// Change back in
		$group_id = $acl->get_group_id( $name, $group_type = 'ARO');
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