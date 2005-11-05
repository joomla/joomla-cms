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
