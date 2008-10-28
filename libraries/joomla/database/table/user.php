<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	Table
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// No direct access
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
	protected $id				= null;

	/**
	 * The users real name (or nickname)
	 *
	 * @var string
	 */
	protected $name			= null;

	/**
	 * The login name
	 *
	 * @var string
	 */
	protected $username		= null;

	/**
	 * The email
	 *
	 * @var string
	 */
	protected $email			= null;

	/**
	 * MD5 encrypted password
	 *
	 * @var string
	 */
	protected $password		= null;

	/**
	 * Description
	 *
	 * @var string
	 */
	protected $usertype		= null;

	/**
	 * Description
	 *
	 * @var int
	 */
	protected $block			= null;

	/**
	 * Description
	 *
	 * @var int
	 */
	protected $sendEmail		= null;

	/**
	 * The group id number
	 *
	 * @var int
	 */
	protected $gid			= null;

	/**
	 * Description
	 *
	 * @var datetime
	 */
	protected $registerDate	= null;

	/**
	 * Description
	 *
	 * @var datetime
	 */
	protected $lastvisitDate	= null;

	/**
	 * Description
	 *
	 * @var string activation hash
	 */
	protected $activation		= null;

	/**
	 * Description
	 *
	 * @var string
	 */
	protected $params			= null;

	/**
	* @param database A database connector object
	*/
	protected function __construct( &$db )
	{
		parent::__construct( '#__users', 'id', $db );

		//initialise
		$this->id		= 0;
		$this->gid		= 0;
		$this->sendEmail = 0;
	}

	/**
	 * Validation and filtering
	 *
	 * @return boolean True is satisfactory
	 */
	public function check()
	{
		jimport('joomla.mail.helper');

		// Validate user information
		if (trim( $this->name ) == '') {
			$this->setError( JText::_( 'Please enter your name.' ) );
			return false;
		}

		if (trim( $this->username ) == '') {
			$this->setError( JText::_( 'Please enter a user name.') );
			return false;
		}

		if (eregi( "[<>\"'%;()&]", $this->username) || strlen(utf8_decode($this->username )) < 2) {
			$this->setError( JText::sprintf( 'VALID_AZ09', JText::_( 'Username' ), 2 ) );
			return false;
		}

		if ((trim($this->email) == "") || ! JMailHelper::isEmailAddress($this->email) ) {
			$this->setError( JText::_( 'WARNREG_MAIL' ) );
			return false;
		}

		if ($this->registerDate == null) {
			// Set the registration timestamp
			$now =& JFactory::getDate();
			$this->registerDate = $now->toMySQL();
		}


		// check for existing username
		$query = 'SELECT id'
		. ' FROM #__users '
		. ' WHERE username = ' . $this->_db->Quote($this->username)
		. ' AND id != '. (int) $this->id;
		;
		$this->_db->setQuery( $query );
		try {
			$xid = intval( $this->_db->loadResult() );
		} catch(JException $e) {
			$this->setError($e->getMessage());
			return false;
		}
		if ($xid && $xid != intval( $this->id )) {
			$this->setError(  JText::_('WARNREG_INUSE'));
			return false;
		}


		// check for existing email
		$query = 'SELECT id'
			. ' FROM #__users '
			. ' WHERE email = '. $this->_db->Quote($this->email)
			. ' AND id != '. (int) $this->id
			;
		$this->_db->setQuery( $query );
		try {
			$xid = intval( $this->_db->loadResult() );
		} catch(JException $e) {
			$this->setError($e->getMessage());
			return false;
		}
		if ($xid && $xid != intval( $this->id )) {
			$this->setError( JText::_( 'WARNREG_EMAIL_INUSE' ) );
			return false;
		}

		return true;
	}

	public function store( $updateNulls=false )
	{
		$acl =& JFactory::getACL();

		$section_value = 'users';
		$k = $this->_tbl_key;
		$key =  $this->$k;
		try {
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
		} catch(JException $e) {
			$this->setError( strtolower(get_class( $this ))."::". JText::_( 'store failed' ) ."<br />" . $e->getMessage() );
			return false;
		}
		return true;
	}

	public function delete( $oid=null )
	{
		$acl =& JFactory::getACL();

		$k = $this->_tbl_key;
		if ($oid) {
			$this->$k = intval( $oid );
		}
		$aro_id = $acl->get_object_id( 'users', $this->$k, 'ARO' );
		$acl->del_object( $aro_id, 'ARO', true );

		$query = 'DELETE FROM '. $this->_tbl
		. ' WHERE '. $this->_tbl_key .' = '. (int) $this->$k
		;
		$this->_db->setQuery( $query );

		try {
			$this->_db->query();
			// cleanup related data

			// private messaging
			$query = 'DELETE FROM #__messages_cfg'
			. ' WHERE user_id = '. (int) $this->$k
			;
			$this->_db->setQuery( $query );
			$this->_db->query();
	
			$query = 'DELETE FROM #__messages'
			. ' WHERE user_id_to = '. (int) $this->$k
			;
			$this->_db->setQuery( $query );
			$this->_db->query();
		} catch(JException $e) {
			$this->setError($e->getMessage());
			return false;
		}
		return true;
	}

	/**
	 * Updates last visit time of user
	 *
	 * @param int The timestamp, defaults to 'now'
	 * @return boolean False if an error occurs
	 */
	public function setLastVisit( $timeStamp=null, $id=null )
	{
		// check for User ID
		if (is_null( $id )) {
			if (isset( $this )) {
				$id = $this->id;
			} else {
				// do not translate
				jexit( 'WARNMOSUSER' );
			}
		}

		// if no timestamp value is passed to functon, than current time is used
		$date =& JFactory::getDate($timeStamp);

		// updates user lastvistdate field with date and time
		$query = 'UPDATE '. $this->_tbl
		. ' SET lastvisitDate = '.$this->_db->Quote($date->toMySQL())
		. ' WHERE id = '. (int) $id
		;
		$this->_db->setQuery( $query );
		try {
			$this->_db->query();
		} catch(JException $e) {
			$this->setError( $e->getMessage() );
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

	public function bind($array, $ignore = '')
	{
		if (key_exists( 'params', $array ) && is_array( $array['params'] )) {
			$registry = new JRegistry();
			$registry->loadArray($array['params']);
			$array['params'] = $registry->toString();
		}

		return parent::bind($array, $ignore);
	}
}
