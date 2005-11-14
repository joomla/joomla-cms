<?php

/**
* @version $Id$
* @package Joomla 
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
* Users Table Class
*
* Provides access to the jos_user table
* @package Joomla
* @since 1.0
*/
class mosUser extends mosDBTable {
	/** @var int Unique id*/
	var $id				= null;
	/** @var string The users real name (or nickname)*/
	var $name			= null;
	/** @var string The login name*/
	var $username		= null;
	/** @var string email*/
	var $email			= null;
	/** @var string MD5 encrypted password*/
	var $password		= null;
	/** @var string */
	var $usertype		= null;
	/** @var int */
	var $block			= null;
	/** @var int */
	var $sendEmail		= null;
	/** @var int The group id number */
	var $gid			= null;
	/** @var datetime */
	var $registerDate	= null;
	/** @var datetime */
	var $lastvisitDate	= null;
	/** @var string activation hash*/
	var $activation		= null;
	/** @var string */
	var $params			= null;

	/**
	* @param database A database connector object
	*/
	function mosUser( &$database ) {
		$this->mosDBTable( '#__users', 'id', $database );
		
		//initialise
		$this->id  = 0;
		$this->gid = 0;
	}

	/**
	 * Validation and filtering
	 * @return boolean True is satisfactory
	 */
	function check() {
		global $mosConfig_uniquemail;
		;

		// filter malicious code
		//$this->filter();

		// Validate user information
		if (trim( $this->name ) == '') {
			$this->_error = JText::_( 'Please enter your name.' );
			return false;
		}

		if (trim( $this->username ) == '') {
			$this->_error = JText::_( 'Please enter a user name.');
			return false;
		}

		if (eregi( "[\<|\>|\"|\'|\%|\;|\(|\)|\&|\+|\-]", $this->username) || strlen( $this->username ) < 3) {
			$this->_error = sprintf( JText::_( 'VALID_AZ09' ), JText::_( 'Username' ), 2 );
			return false;
		}

		if ((trim($this->email == "")) || (preg_match("/[\w\.\-]+@\w+[\w\.\-]*?\.\w{1,4}/", $this->email )==false)) {
			$this->_error = JText::_( 'WARNREG_MAIL' );
			return false;
		}

		// check for existing username
		$query = "SELECT id"
		. "\n FROM #__users "
		. "\n WHERE username = '$this->username'"
		. "\n AND id != $this->id"
		;
		$this->_db->setQuery( $query );
		$xid = intval( $this->_db->loadResult() );
		if ($xid && $xid != intval( $this->id )) {
			$this->_error = JText::_( 'WARNREG_INUSE' );
			return false;
		}

		if ($mosConfig_uniquemail) {
			// check for existing email
			$query = "SELECT id"
			. "\n FROM #__users "
			. "\n WHERE email = '$this->email'"
			. "\n AND id != $this->id"
			;
			$this->_db->setQuery( $query );
			$xid = intval( $this->_db->loadResult() );
			if ($xid && $xid != intval( $this->id )) {
				$this->_error = JText::_( 'WARNREG_EMAIL_INUSE' );
				return false;
			}
		}

		return true;
	}

	function store( $updateNulls=false ) {
		global $acl, $migrate;
		;

		$section_value = 'users';

		$k = $this->_tbl_key;
		$key =  $this->$k;
		if( $key && !$migrate) {
			// existing record
			$ret = $this->_db->updateObject( $this->_tbl, $this, $this->_tbl_key, $updateNulls );
			// syncronise ACL
			// single group handled at the moment
			// trivial to expand to multiple groups
			$groups = $acl->get_object_groups( $section_value, $this->$k, 'ARO' );
			$acl->del_group_object( $groups[0], $section_value, $this->$k, 'ARO' );
			$acl->add_group_object( $this->gid, $section_value, $this->$k, 'ARO' );

			$object_id = $acl->get_object_id( $section_value, $this->$k, 'ARO' );
			$acl->edit_object( $object_id, $section_value, $this->_db->getEscaped( $this->name ), $this->$k, 0, 0, 'ARO' );
		} else {
			// new record
			$ret = $this->_db->insertObject( $this->_tbl, $this, $this->_tbl_key );
			// syncronise ACL
			$acl->add_object( $section_value, $this->_db->getEscaped( $this->name ), $this->$k, null, null, 'ARO' );
			$acl->add_group_object( $this->gid, $section_value, $this->$k, 'ARO' );
		}
		if( !$ret ) {
			$this->_error = strtolower(get_class( $this ))."::". JText::_( 'store failed' ) ."<br />" . $this->_db->getErrorMsg();
			return false;
		} else {
			return true;
		}
	}

	function delete( $oid=null ) {
		global $acl;

		$k = $this->_tbl_key;
		if ($oid) {
			$this->$k = intval( $oid );
		}
		$aro_id = $acl->get_object_id( 'users', $this->$k, 'ARO' );
//		$acl->del_object( $aro_id, 'ARO', true );

		$query = "DELETE FROM $this->_tbl"
		. "\n WHERE $this->_tbl_key = '". $this->$k ."'"
		;
		$this->_db->setQuery( $query );

		if ($this->_db->query()) {
			// cleanup related data

			// :: private messaging
			$query = "DELETE FROM #__messages_cfg"
			. "\n WHERE user_id = ". $this->$k .""
			;
			$this->_db->setQuery( $query );
			if (!$this->_db->query()) {
				$this->_error = $this->_db->getErrorMsg();
				return false;
			}
			$query = "DELETE FROM #__messages"
			. "\n WHERE user_id_to = ". $this->$k .""
			;
			$this->_db->setQuery( $query );
			if (!$this->_db->query()) {
				$this->_error = $this->_db->getErrorMsg();
				return false;
			}

			return true;
		} else {
			$this->_error = $this->_db->getErrorMsg();
			return false;
		}
	}

	/**
	 * Updates last visit time of user
	 * @param int The timestamp, defaults to 'now'
	 * @return boolean False if an error occurs
	 */
	function setLastVisit( $timeStamp=null, $id=null ) {
		;

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
		$query = "UPDATE $this->_tbl"
		. "\n SET lastvisitDate = '$dateTime'"
		. "\n WHERE id = '$id'"
		;
		$this->_db->setQuery( $query );
		if (!$this->_db->query()) {
			$this->_error = $this->_db->getErrorMsg();
			return false;
		}

		return true;
	}

	/**
	 * Returns if a user exists
	 * @param string The username to search on
	 * @return int Number of matching rows (either 0 or 1)
	 */
        function userExists($username) {
                global $database;
                $database->setQuery("SELECT username FROM #__users WHERE username = '$username' LIMIT 1");
                $database->Query();
                return $database->getNumRows();
        }

	/**
	 * Returns a complete user list
	 * @return array
	 */
        function getUserList() {
                global $database;
                $database->setQuery("SELECT username FROM #__users");
                return $database->loadAssocList();
        }

	/**
	 * Gets the users from a group
	 * @param string The value for the group (not used 1.0)
	 * @param string The name for the group
	 * @param string If RECURSE, will drill into child groups
	 * @param string Ordering for the list
	 * @return array
	 */
	function getUserListFromGroup( $value, $name, $recurse='NO_RECURSE', $order='name' ) {
		global $acl;

		// Change back in
		//$group_id = $acl->get_group_id( $value, $name, $group_type = 'ARO');
		$group_id = $acl->get_group_id( $name, $group_type = 'ARO');
		$objects = $acl->get_group_objects( $group_id, 'ARO', 'RECURSE');

		if (isset( $objects['users'] )) {
			$gWhere = '(id =' . implode( ' OR id =', $objects['users'] ) . ')';

			$query = "SELECT id AS value, name AS text"
			. "\n FROM #__users"
			. "\n WHERE block = '0'"
			. "\n AND " . $gWhere
			. "\n ORDER BY ". $order
			;
			$this->_db->setQuery( $query );
			$options = $this->_db->loadObjectList();
			return $options;
		} else {
			return array();
		}
	}
}

?>
