<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.database.table');

/**
 * Message Table class
 *
 * @package		Joomla.Administrator
 * @subpackage	com_messages
 * @since		1.5
 */
class MessagesTableMessage extends JTable
{
	/**
	 * Primary Key
	 *
	 * @var		int
	 */
	public $message_id	= null;

	/**
	 * Sender's userid
	 *
	 * @var		int
	 */
	public $user_id_from = null;

	/**
	 * Recipient's userid
	 *
	 * @var		int
	 */
	public $user_id_to = null;

	/**
	 * @var		int
	 */
	public $folder_id = null;

	/**
	 * Message creation timestamp
	 *
	 * @var		datetime
	 */
	public $date_time = null;

	/**
	 * Message state
	 *
	 * @var		int
	 */
	public $state = null;

	/**
	 * Priority level of the message
	 *
	 * @var		int
	 */
	public $priority = null;

	/**
	 * The message subject
	 *
	 * @var		string
	 */
	public $subject = null;

	/**
	 * The message body
	 *
	 * @var		text
	 */
	public $message = null;

	/**
	 * Constructor
	 *
	 * @param database A database connector object
	 */
	function __construct(& $db)
	{
		parent::__construct('#__messages', 'message_id', $db);
	}

	/**
	 * Validation and filtering.
	 *
	 * @return boolean
	 */
	function check()
	{
		// Check the to and from users.
		$user = new JUser($table->user_id_from);
		if (empty($user->id)) {
			$this->setError('Messages_Error_Invalid_from_user');
			return false;
		}

		$user = new JUser($table->user_id_to);
		if (empty($user->id)) {
			$this->setError('Messages_Error_Invalid_to_user');
			return false;
		}

		if (empty($this->subject)) {
			$this->setError('Messages_Error_Invalid_subject');
			return false;
		}

		if (empty($this->message)) {
			$this->setError('Messages_Error_Invalid_message');
			return false;
		}

		return true;
	}
}
