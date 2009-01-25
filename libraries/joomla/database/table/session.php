<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	Table
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// No direct access
defined('JPATH_BASE') or die();

/**
 * Session table
 *
 * @package 	Joomla.Framework
 * @subpackage		Table
 * @since	1.0
 */
class JTableSession extends JTable
{
	/**
	 *
	 * @var int Primary key
	 */
	protected $session_id			= null;

	/**
	 *
	 * @var string
	 */
	protected $time				= null;

	/**
	 *
	 * @var string
	 */
	protected $userid				= null;

	/**
	 *
	 * @var string
	 */
	protected $usertype			= null;

	/**
	 *
	 * @var string
	 */
	protected $username			= null;

	/**
	 *
	 * @var time
	 */
	protected $gid				= null;

	/**
	 *
	 * @var int
	 */
	protected $guest				= null;

	/**
	 *
	 * @var int
	 */
	protected $client_id			= null;

	/**
	 *
	 * @var string
	 */
	protected $data				= null;

	/**
	 * Constructor
	 * @param database A database connector object
	 */
	protected function __construct( &$db )
	{
		parent::__construct( '#__session', 'session_id', $db );

		$this->guest 	= 1;
		$this->username = '';
		$this->gid 		= 0;
	}

	public function insert($sessionId, $clientId)
	{
		$this->session_id	= $sessionId;
		$this->client_id	= $clientId;

		$this->time = time();
		try {
			$this->_db->insertObject( $this->_tbl, $this, 'session_id' );
		} catch(JException $e) {
			$this->setError(strtolower(get_class( $this ))."::". JText::_( 'store failed' ) ."<br />" . $e->getMessage());
			return false;
		}
		return true;
	}

	public function update( $updateNulls = false )
	{
		$this->time = time();
		try {
			$this->_db->updateObject( $this->_tbl, $this, 'session_id', $updateNulls );
		} catch(JException $e) {
			$this->setError(strtolower(get_class( $this ))."::". JText::_( 'store failed' ) ." <br />" . $e->getMessage());
			return false;
		}
		return true;
	}

	/**
	 * Destroys the pesisting session
	 */
	public function destroy($userId, $clientIds = array())
	{
		$clientIds = implode( ',', $clientIds );

		$query = 'DELETE FROM #__session'
			. ' WHERE userid = '. $this->_db->Quote( $userId )
			. ' AND client_id IN ( '.$clientIds.' )'
			;
		$this->_db->setQuery( $query );

		try {
			$this->_db->query();
		} catch(JException $e) {
			$this->setError( $e->getMessage());
			return false;
		}

		return true;
	}

	/**
	* Purge old sessions
	*
	* @param int 	Session age in seconds
	* @return mixed Resource on success, null on fail
	*/
	public function purge( $maxLifetime = 1440 )
	{
		$past = time() - $maxLifetime;
		$query = 'DELETE FROM '. $this->_tbl .' WHERE ( time < \''. (int) $past .'\' )'; // Index on 'VARCHAR'
		$this->_db->setQuery($query);
		try {
			return $this->_db->query();
		} catch(JException $e) {
			$this->setError($e->getMessage());
			return false;
		}
	}

	/**
	 * Find out if a user has a one or more active sessions
	 *
	 * @param int $userid The identifier of the user
	 * @return boolean True if a session for this user exists
	 */
	public function exists($userid)
	{
		$query = 'SELECT COUNT(userid) FROM #__session'
			. ' WHERE userid = '. $this->_db->Quote( $userid );
		$this->_db->setQuery( $query );

		try {
			$result = $this->_db->loadResult();
		} catch(JException $e) {
			$this->setError($e->getMessage());
			return false;
		}

		return (boolean) $result;
	}

	/**
	 * Overloaded delete method
	 *
	 * We must override it because of the non-integer primary key
	 *
	 * @access public
	 * @return true if successful otherwise returns and error message
	 */
	public function delete( $oid=null )
	{
		//if (!$this->canDelete( $msg ))
		//{
		//	return $msg;
		//}

		$k = $this->_tbl_key;
		if ($oid) {
			$this->$k = $oid;
		}

		$query = 'DELETE FROM '.$this->_db->nameQuote( $this->_tbl ).
				' WHERE '.$this->_tbl_key.' = '. $this->_db->Quote($this->$k);
		$this->_db->setQuery( $query );

		try {
			$this->_db->query();
			return true;
		} catch(JException $e) {
			$this->setError($e->getMessage());
			return false;
		}
	}
}
