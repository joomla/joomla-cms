<?php

/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * Session table
 *
 * @package 	Joomla.Framework
 * @subpackage 	Model
 * @since		1.0
 */
class JTableSession extends JTable 
{
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
	/** @var int */
	var $client_id			= null;

	/**
	 * Constructor
	 * @param database A database connector object
	 */
	function __construct( &$db ) 
	{
		parent::__construct( '#__session', 'session_id', $db );

		$this->guest = 1;
		$this->username = '';
		$this->gid = 0;
	}

	function insert($sessionId, $clientId) 
	{
		$this->session_id = $sessionId;
		$this->client_id  = $clientId;

		$this->time = time();
		$ret = $this->_db->insertObject( $this->_tbl, $this );

		if( !$ret ) {
			$this->_error = strtolower(get_class( $this ))."::". JText::_( 'store failed' ) ."<br />" . $this->_db->stderr();
			return false;
		} else {
			return true;
		}
	}

	function update( $updateNulls=false ) 
	{
		$this->time = time();
		$ret = $this->_db->updateObject( $this->_tbl, $this, 'session_id', $updateNulls );

		if( !$ret ) {
			$this->_error = strtolower(get_class( $this ))."::". JText::_( 'store failed' ) ." <br />" . $this->_db->stderr();
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Allows site to remember login
	 * @param string The username
	 * @param string The user password
	 */
	function remember( $username, $password ) 
	{
		$lifetime = time() + 365*24*60*60;
		setcookie( 'usercookie[username]', $username, $lifetime, '/' );
		setcookie( 'usercookie[password]', $password, $lifetime, '/' );
	}

	/**
	 * Destroys the pesisting session
	 */
	function destroy() 
	{
		if ($this->userid) {
			// update the user last visit
			$query = "UPDATE #__users"
			. "\n SET lastvisitDate = " . $this->_db->Quote( date( 'Y-m-d\TH:i:s' ) )
			. "\n WHERE id='". intval( $this->userid ) ."'";
			$this->_db->setQuery( $query );

			if ( !$this->_db->query() ) {
		 		$this->_error =  $this->_db->stderr();
				return false;
			}
		}

		$query = "DELETE FROM #__session"
			. "\n WHERE session_id = ". $this->_db->Quote( $this->session_id )
			;
		$this->_db->setQuery( $query );
		if ( !$this->_db->query() ) {
			$this->_error =  $this->_db->stderr();
			return false;
		}
		
		return true;
	}

	/**
	* Purge old sessions
	* @param int Session age in seconds
	* @return mixed Resource on success, null on fail
	*/
	function purge( $age=1800 ) 
	{
		$past = time() - $age;
		$query = "DELETE FROM $this->_tbl"
		. "\n WHERE ( time < $past )"
		;
		$this->_db->setQuery($query);

		return $this->_db->query();
	}
}
?>
