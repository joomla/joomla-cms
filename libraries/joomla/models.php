<?php

/**
* @version $Id:  $
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
* Component database table class
* @package Joomla
* @since 1.0
*/
class mosComponent extends mosDBTable {
	/** @var int Primary key */
	var $id					= null;
	/** @var string */
	var $name				= null;
	/** @var string */
	var $link				= null;
	/** @var int */
	var $menuid				= null;
	/** @var int */
	var $parent				= null;
	/** @var string */
	var $admin_menu_link	= null;
	/** @var string */
	var $admin_menu_alt		= null;
	/** @var string */
	var $option				= null;
	/** @var string */
	var $ordering			= null;
	/** @var string */
	var $admin_menu_img		= null;
	/** @var int */
	var $iscore				= null;
	/** @var string */
	var $params				= null;

	/**
	* @param database A database connector object
	*/
	function mosComponent( &$db ) {
		$this->mosDBTable( '#__components', 'id', $db );
	}
}

/**
* Category database table class
* @package Joomla
* @since 1.0
*/
class mosCategory extends mosDBTable {
	/** @var int Primary key */
	var $id					= null;
	/** @var int */
	var $parent_id			= null;
	/** @var string The menu title for the Category (a short name)*/
	var $title				= null;
	/** @var string The full name for the Category*/
	var $name				= null;
	/** @var string */
	var $image				= null;
	/** @var string */
	var $section			= null;
	/** @var int */
	var $image_position		= null;
	/** @var string */
	var $description		= null;
	/** @var boolean */
	var $published			= null;
	/** @var boolean */
	var $checked_out		= null;
	/** @var time */
	var $checked_out_time	= null;
	/** @var int */
	var $ordering			= null;
	/** @var int */
	var $access				= null;
	/** @var string */
	var $params				= null;

	/**
	* @param database A database connector object
	*/
	function mosCategory( &$db ) {
		$this->mosDBTable( '#__categories', 'id', $db );
	}
	// overloaded check function
	function check() {
		global $_LANG;

		// check for valid name
		if (trim( $this->title ) == '') {
			$this->_error = $_LANG->_( 'Your') ." ". $_LANG->_( 'Category') ." ". $_LANG->_( 'must contain a title.' );
			return false;
		}
		if (trim( $this->name ) == '') {
			$this->_error = $_LANG->_( 'Your') ." ". $_LANG->_( 'Category') ." ". $_LANG->_( 'must have a name.' );
			return false;
		}
		// check for existing name
		$query = "SELECT id"
		. "\n FROM #__categories "
		. "\n WHERE name = '$this->name'"
		. "\n AND section = '$this->section'"
		;
		$this->_db->setQuery( $query );

		$xid = intval( $this->_db->loadResult() );
		if ($xid && $xid != intval( $this->id )) {
			$this->_error = $_LANG->_( 'There is a' ) ." ". $_LANG->_( 'Category') ." ". $_LANG->_( 'already with that name, please try again.' );
			return false;
		}
		return true;
	}
}

/**
* Section database table class
* @package Joomla
* @since 1.0
*/
class mosSection extends mosDBTable {
	/** @var int Primary key */
	var $id					= null;
	/** @var string The menu title for the Section (a short name)*/
	var $title				= null;
	/** @var string The full name for the Section*/
	var $name				= null;
	/** @var string */
	var $image				= null;
	/** @var string */
	var $scope				= null;
	/** @var int */
	var $image_position		= null;
	/** @var string */
	var $description		= null;
	/** @var boolean */
	var $published			= null;
	/** @var boolean */
	var $checked_out		= null;
	/** @var time */
	var $checked_out_time	= null;
	/** @var int */
	var $ordering			= null;
	/** @var int */
	var $access				= null;
	/** @var string */
	var $params				= null;

	/**
	* @param database A database connector object
	*/
	function mosSection( &$db ) {
		$this->mosDBTable( '#__sections', 'id', $db );
	}
	// overloaded check function
	function check() {
		global $_LANG;

		// check for valid name
		if (trim( $this->title ) == '') {
			$this->_error = $_LANG->_( 'Your') ." ". $_LANG->_( 'Section') ." ". $_LANG->_( 'must contain a title.' );
			return false;
		}
		if (trim( $this->name ) == '') {
			$this->_error = $_LANG->_( 'Your') ." ". $_LANG->_( 'Section') ." ". $_LANG->_( 'must have a name.' );
			return false;
		}
		// check for existing name
		$query = "SELECT id"
		. "\n FROM #__sections "
		. "\n WHERE name = '$this->name'"
		. "\n AND scope = '$this->scope'"
		;
		$this->_db->setQuery( $query );

		$xid = intval( $this->_db->loadResult() );
		if ($xid && $xid != intval( $this->id )) {
			$this->_error = $_LANG->_( 'There is a' ) ." ". $_LANG->_( 'Section') ." ". $_LANG->_( 'already with that name, please try again.' );
			return false;
		}
		return true;
	}
}

/**
* Content database table class
* @package Joomla
* @since 1.0
*/
class mosContent extends mosDBTable {
	/** @var int Primary key */
	var $id					= null;
	/** @var string */
	var $title				= null;
	/** @var string */
	var $title_alias		= null;
	/** @var string */
	var $introtext			= null;
	/** @var string */
	var $fulltext			= null;
	/** @var int */
	var $state				= null;
	/** @var int The id of the category section*/
	var $sectionid			= null;
	/** @var int DEPRECATED */
	var $mask				= null;
	/** @var int */
	var $catid				= null;
	/** @var datetime */
	var $created			= null;
	/** @var int User id*/
	var $created_by			= null;
	/** @var string An alias for the author*/
	var $created_by_alias	= null;
	/** @var datetime */
	var $modified			= null;
	/** @var int User id*/
	var $modified_by		= null;
	/** @var boolean */
	var $checked_out		= null;
	/** @var time */
	var $checked_out_time	= null;
	/** @var datetime */
	var $frontpage_up		= null;
	/** @var datetime */
	var $frontpage_down		= null;
	/** @var datetime */
	var $publish_up			= null;
	/** @var datetime */
	var $publish_down		= null;
	/** @var string */
	var $images				= null;
	/** @var string */
	var $urls				= null;
	/** @var string */
	var $attribs			= null;
	/** @var int */
	var $version			= null;
	/** @var int */
	var $parentid			= null;
	/** @var int */
	var $ordering			= null;
	/** @var string */
	var $metakey			= null;
	/** @var string */
	var $metadesc			= null;
	/** @var int */
	var $access				= null;
	/** @var int */
	var $hits				= null;

	/**
	* @param database A database connector object
	*/
	function mosContent( &$db ) {
		$this->mosDBTable( '#__content', 'id', $db );
	}

	/**
	 * Validation and filtering
	 */
	function check() {
		// filter malicious code
		$ignoreList = array( 'introtext', 'fulltext' );
		$this->filter( $ignoreList );

		/*
		TODO: This filter is too rigorous,
		need to implement more configurable solution
		// specific filters
		$iFilter = new InputFilter( null, null, 1, 1 );
		$this->introtext = trim( $iFilter->process( $this->introtext ) );
		$this->fulltext =  trim( $iFilter->process( $this->fulltext ) );
		*/

		if (trim( str_replace( '&nbsp;', '', $this->fulltext ) ) == '') {
			$this->fulltext = '';
		}

		return true;
	}

	/**
	* Converts record to XML
	* @param boolean Map foreign keys to text values
	*/
	function toXML( $mapKeysToText=false ) {
		global $database;

		if ($mapKeysToText) {
			$query = "SELECT name"
			. "\n FROM #__sections"
			. "\n WHERE id = $this->sectionid"
			;
			$database->setQuery( $query );
			$this->sectionid = $database->loadResult();

			$query = "SELECT name"
			. "\n FROM #__categories"
			. "\n WHERE id $this->catid"
			;
			$database->setQuery( $query );
			$this->catid = $database->loadResult();

			$query = "SELECT name"
			. "\n FROM #__users"
			. "\n WHERE id = $this->created_by"
			;
			$database->setQuery( $query );
			$this->created_by = $database->loadResult();
		}

		return parent::toXML( $mapKeysToText );
	}
}

/**
* Menu database table class
* @package Joomla
* @since 1.0
*/
class mosMenu extends mosDBTable {
	/** @var int Primary key */
	var $id					= null;
	/** @var string */
	var $menutype			= null;
	/** @var string */
	var $name				= null;
	/** @var string */
	var $link				= null;
	/** @var int */
	var $type				= null;
	/** @var int */
	var $published			= null;
	/** @var int */
	var $componentid		= null;
	/** @var int */
	var $parent				= null;
	/** @var int */
	var $sublevel			= null;
	/** @var int */
	var $ordering			= null;
	/** @var boolean */
	var $checked_out		= null;
	/** @var datetime */
	var $checked_out_time	= null;
	/** @var boolean */
	var $pollid				= null;
	/** @var string */
	var $browserNav			= null;
	/** @var int */
	var $access				= null;
	/** @var int */
	var $utaccess			= null;
	/** @var string */
	var $params				= null;

	/**
	* @param database A database connector object
	*/
	function mosMenu( &$db ) {
		$this->mosDBTable( '#__menu', 'id', $db );
	}
}

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
		global $_LANG;

		// filter malicious code
		//$this->filter();

		// Validate user information
		if (trim( $this->name ) == '') {
			$this->_error = $_LANG->_( 'Please enter your name.' );
			return false;
		}

		if (trim( $this->username ) == '') {
			$this->_error = $_LANG->_( 'Please enter a user name.');
			return false;
		}

		if (eregi( "[\<|\>|\"|\'|\%|\;|\(|\)|\&|\+|\-]", $this->username) || strlen( $this->username ) < 3) {
			$this->_error = sprintf( $_LANG->_( 'VALID_AZ09' ), $_LANG->_( 'Username' ), 2 );
			return false;
		}

		if ((trim($this->email == "")) || (preg_match("/[\w\.\-]+@\w+[\w\.\-]*?\.\w{1,4}/", $this->email )==false)) {
			$this->_error = $_LANG->_( 'WARNREG_MAIL' );
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
			$this->_error = $_LANG->_( 'WARNREG_INUSE' );
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
				$this->_error = $_LANG->_( 'WARNREG_EMAIL_INUSE' );
				return false;
			}
		}

		return true;
	}

	function store( $updateNulls=false ) {
		global $acl, $migrate;
		global $_LANG;

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
			$this->_error = strtolower(get_class( $this ))."::". $_LANG->_( 'store failed' ) ."<br />" . $this->_db->getErrorMsg();
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
		global $_LANG;

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

/**
* Template Table Class
*
* Provides access to the jos_templates table
* @package Joomla
* @since 1.0
*/
class mosTemplate extends mosDBTable {
	/** @var int */
	var $id				= null;
	/** @var string */
	var $cur_template	= null;
	/** @var int */
	var $col_main		= null;

	/**
	* @param database A database connector object
	*/
	function mosTemplate( &$database ) {
		$this->mosDBTable( '#__templates', 'id', $database );
	}
}

/**
* Class mosMambot
* @package Joomla
* @since 1.0
*/
class mosMambot extends mosDBTable {
	/** @var int */
	var $id					= null;
	/** @var varchar */
	var $name				= null;
	/** @var varchar */
	var $element			= null;
	/** @var varchar */
	var $folder				= null;
	/** @var tinyint unsigned */
	var $access				= null;
	/** @var int */
	var $ordering			= null;
	/** @var tinyint */
	var $published			= null;
	/** @var tinyint */
	var $iscore				= null;
	/** @var tinyint */
	var $client_id			= null;
	/** @var int unsigned */
	var $checked_out		= null;
	/** @var datetime */
	var $checked_out_time	= null;
	/** @var text */
	var $params				= null;

	function mosMambot( &$db ) {
		$this->mosDBTable( '#__mambots', 'id', $db );
	}
}

/**
* Module database table class
* @package Joomla
* @since 1.0
*/
class mosModule extends mosDBTable {
	/** @var int Primary key */
	var $id					= null;
	/** @var string */
	var $title				= null;
	/** @var string */
	var $showtitle			= null;
	/** @var int */
	var $content			= null;
	/** @var int */
	var $ordering			= null;
	/** @var string */
	var $position			= null;
	/** @var boolean */
	var $checked_out		= null;
	/** @var time */
	var $checked_out_time	= null;
	/** @var boolean */
	var $published			= null;
	/** @var string */
	var $module				= null;
	/** @var int */
	var $numnews			= null;
	/** @var int */
	var $access				= null;
	/** @var string */
	var $params				= null;
	/** @var string */
	var $iscore				= null;
	/** @var string */
	var $client_id			= null;

	/**
	* @param database A database connector object
	*/
	function mosModule( &$db ) {
		$this->mosDBTable( '#__modules', 'id', $db );
	}
	// overloaded check function
	function check() {
		global $_LANG;

		// check for valid name
		if (trim( $this->title ) == '') {
			$this->_error = $_LANG->_( 'Your' ) ." ". $_LANG->_( 'Module' ) ." ". $_LANG->_( 'must contain a title.' );
			return false;
		}

		// limitation has been removed
		// check for existing title
		//$this->_db->setQuery( "SELECT id FROM #__modules"
		//. "\nWHERE title='$this->title'"
		//);
		// check for module of same name
		//$xid = intval( $this->_db->loadResult() );
		//if ($xid && $xid != intval( $this->id )) {
		//	$this->_error = "There is a module already with that name, please try again.";
		//	return false;
		//}
		return true;
	}
}

/**
* Session database table class
* @package Joomla
* @since 1.0
*/
class mosSession extends mosDBTable {
	/** @var int Primary key */
	var $session_id			= null;
	/** @var string */
	var $time				= null;
	/** @var string */
	var $userid				= null;
	/** @var string */
	var $usertype			= null;
	/** @var string */
	var $username			= null;
	/** @var time */
	var $gid				= null;
	/** @var int */
	var $guest				= null;

	/** @var string */
	var $_session_cookie	= null;
	/** @var string */
	var $_sessionType		= null;

	/**
	 * Constructor
	 * @param database A database connector object
	 */
	function mosSession( &$db, $type='cookie' ) {
		$this->mosDBTable( '#__session', 'session_id', $db );

		$this->guest = 1;
		$this->username = '';
		$this->gid = 0;
		$this->_sessionType = $type;
	}

	function insert() {
		global $_LANG;

		$this->generateId();
		$this->time = time();
		$ret = $this->_db->insertObject( $this->_tbl, $this );

		if( !$ret ) {
			$this->_error = strtolower(get_class( $this ))."::". $_LANG->_( 'store failed' ) ."<br />" . $this->_db->stderr();
			return false;
		} else {
			return true;
		}
	}

	function update( $updateNulls=false ) {
		global $_LANG;

		$this->time = time();
		$ret = $this->_db->updateObject( $this->_tbl, $this, 'session_id', $updateNulls );

		if( !$ret ) {
			$this->_error = strtolower(get_class( $this ))."::". $_LANG->_( 'store failed' ) ." <br />" . $this->_db->stderr();
			return false;
		} else {
			return true;
		}
	}

	/**
	 * @return string The id of the session
	 */
	function restore() {
		switch ($this->_sessionType) {
			case 'php':
				$id = mosGetParam( $_SESSION, 'session_id', null );
				break;

			case 'cookie':
			default:
				$id = mosGetParam( $_COOKIE, 'sessioncookie', null );
				break;
		}
		return $id;
	}

	/**
	 * Set the information to allow a session to persist
	 */
	function persist() {
		global $mainframe;

		switch ($this->_sessionType) {
			case 'php':
				$_SESSION['session_id'] = $this->getCookie();
				break;

			case 'cookie':
			default:
				setcookie( 'sessioncookie', $this->getCookie(), time() + 43200, '/' );

				$usercookie = mosGetParam( $_COOKIE, 'usercookie', null );
				if ($usercookie) {
					// Remember me cookie exists. Login with usercookie info.
					$mainframe->login( $usercookie['username'], $usercookie['password'] );
				}
				break;
		}
	}

	/**
	 * Allows site to remember login
	 * @param string The username
	 * @param string The user password
	 */
	function remember( $username, $password ) {
		switch ($this->_sessionType) {
			case 'php':
				// not recommended
				break;

			case 'cookie':
			default:
				$lifetime = time() + 365*24*60*60;
				setcookie( 'usercookie[username]', $user->username, $lifetime, '/' );
				setcookie( 'usercookie[password]', $user->password, $lifetime, '/' );
				break;
		}
	}

	/**
	 * Destroys the pesisting session
	 */
	function destroy() {
		if ($this->userid) {
			// update the user last visit
			$query = "UPDATE #__users"
			. "\n SET lastvisitDate = " . $this->_db->Quote( date( 'Y-m-d\TH:i:s' ) )
			. "\n WHERE id='". intval( $this->userid ) ."'";
			$this->_db->setQuery( $query );

			if ( !$this->_db->query() ) {
		 		mosErrorAlert( $database->stderr() );
			}
		}

		switch ($this->_sessionType) {
			case 'php':
				$query = "DELETE FROM #__session"
				. "\n WHERE session_id = ". $this->_db->Quote( $this->session_id )
				;
				$this->_db->setQuery( $query );
				if ( !$this->_db->query() ) {
			 		mosErrorAlert( $this->_db->stderr() );
				}

				session_unset();
				//session_unregister( 'session_id' );
				if ( session_is_registered( 'session_id' ) ) {
					session_destroy();
				}
				break;
			case 'cookie':
			default:
				// revert the session
				$this->guest 	= 1;
				$this->username = '';
				$this->userid 	= '';
				$this->usertype = '';
				$this->gid 		= 0;

				$this->update();

				$lifetime = time() - 1800;
				setcookie( 'usercookie[username]', ' ', $lifetime, '/' );
				setcookie( 'usercookie[password]', ' ', $lifetime, '/' );
				setcookie( 'usercookie', ' ', $lifetime, '/' );
				@session_destroy();
				break;
		}
	}

	/**
	 * Generates a unique id for the session
	 */
	function generateId() {
		$failsafe = 20;
		$randnum = 0;
		while ($failsafe--) {
			$randnum = md5( uniqid( microtime(), 1 ) );
			if ($randnum != '') {
				$cryptrandnum = md5( $randnum );
				$query = "SELECT $this->_tbl_key"
				. "\n FROM $this->_tbl"
				. "\n WHERE $this->_tbl_key = ". $this->_db->Quote( md5( $randnum ) )
				;
				$this->_db->setQuery( $query );
				if(!$result = $this->_db->query()) {
					die( $this->_db->stderr( true ));
					// todo: handle gracefully
				}
				if ($this->_db->getNumRows($result) == 0) {
					break;
				}
			}
		}
		$this->_session_cookie = $randnum;
		$this->session_id = $this->hash( $randnum );
	}

	/**
	 * @return string The cookie|session based session id
	 */
	function getCookie() {
		return $this->_session_cookie;
	}

	/**
	 * Encodes a session id
	 */
	function hash( $value ) {
		if (phpversion() <= '4.2.1') {
			$agent = getenv( 'HTTP_USER_AGENT' );
		} else {
			$agent = $_SERVER['HTTP_USER_AGENT'];
		}

		return md5( $agent . $GLOBALS['mosConfig_secret'] . $value . $_SERVER['REMOTE_ADDR'] );
	}

	/**
	* Purge old sessions
	* @param int Session age in seconds
	* @return mixed Resource on success, null on fail
	*/
	function purge( $age=1800 ) {
		$past = time() - $age;
		$query = "DELETE FROM $this->_tbl"
		. "\n WHERE ( time < $past )"
		;
		$this->_db->setQuery($query);

		return $this->_db->query();
	}
}
?>
