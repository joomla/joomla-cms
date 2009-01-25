<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Message
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

/**
 * Messages Component Message Model
 *
 * @package		Joomla.Administrator
 * @subpackage	Message
 * @since 1.5
 */
class MessagesModelMessage extends JModel
{
	/**
	 * Message id
	 *
	 * @var int
	 */
	var $_id = null;

	/**
	 * Message data
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct()
	{
		parent::__construct();

		$array = JRequest::getVar('cid', array(0), '', 'array');
		$edit	= JRequest::getVar('edit',true);
		// Note: Opposite to most similar models!
		if(!$edit)
			$this->setId((int)$array[0]);
	}

	/**
	 * Method to set the message identifier
	 *
	 * @access	public
	 * @param	int Message identifier
	 */
	function setId($id)
	{
		// Set message id and wipe data
		$this->_id		= $id;
		$this->_data	= null;
	}

	/**
	 * Method to get a message
	 *
	 * @since 1.5
	 */
	function &getData()
	{
		// Load the message data
		if (!$this->_loadData())
		{
			$this->_initData();
		}

		return $this->_data;
	}

	/**
	 * Method to store the message
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function send($data)
	{
		$row =& $this->getTable();

		// Bind the form fields to the web link table
		if (!$row->bind($data)) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Make sure the data is valid
		if (!$row->check()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Store the data to the database
		if (!$row->send()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		return true;
	}

	/**
	 * Method to remove a message
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function delete($cid = array())
	{
		$result = false;

		if (count( $cid ))
		{
			JArrayHelper::toInteger($cid);
			$cids = implode( ',', $cid );
			$query = 'DELETE FROM #__messages'
				. ' WHERE message_id IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			if(!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to load message data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function _loadData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = 'SELECT a.*, u.name AS user_from'
				. ' FROM #__messages AS a'
				. ' INNER JOIN #__users AS u ON u.id = a.user_id_from'
				. ' WHERE a.message_id = '.(int) $this->_id
				. ' ORDER BY date_time DESC'
			;
			$this->_db->setQuery($query);
			$this->_data = $this->_db->loadObject();
			return (boolean) $this->_data;
		}
		return true;
	}

	/**
	 * Method to initialise the message data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function _initData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$message = new stdClass();
			$message->id					= 0;
			$message->user_id_from			= 0;
			$message->user_id_to			= 0;
			$message->folder_id				= 0;
			$message->date_time				= null;
			$message->state					= 0;
			$message->priority				= 0;
			$message->subject				= null;
			$message->message				= null;
			$this->_data					= $message;
			return (boolean) $this->_data;
		}
		return true;
	}

	/**
	 * Method to set the message as read
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function markAsRead()
	{
		$query = 'UPDATE #__messages'
			. ' SET state = 1'
			. ' WHERE message_id = '.(int) $this->_id
		;
		$this->_db->setQuery( $query );
		if ($this->_db->query() === false)
			return false;

		return true;
	}
}