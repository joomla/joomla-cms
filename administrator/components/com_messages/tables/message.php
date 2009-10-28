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
class TableMessage extends JTable
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
	* Validation and filtering
	*/
	function check()
	{
		return true;
	}

	/**
	 * Method to send a private message
	 *
	 * @param	int		$fromId		Sender's userid
	 * @param	int		$toId		Recipient's userid
	 * @param	string	$subject	The message subject
	 * @param	string	$message	The message body
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	public function send($fromId = null, $toId = null, $subject = null, $message = null, $mailfrom = null, $fromname = null)
	{
		$app	= &JFactory::getApplication();
		$db		= &JFactory::getDbo();

		if (is_object($this))
		{
			$fromId		= $fromId	? $fromId	: $this->user_id_from;
			$toId		= $toId		? $toId		: $this->user_id_to;
			$subject	= $subject	? $subject	: $this->subject;
			$message	= $message	? $message	: $this->message;
		}

		$query = 'SELECT cfg_name, cfg_value' .
				' FROM #__messages_cfg' .
				' WHERE user_id = '.(int) $toId;
		$db->setQuery($query);

		$config = $db->loadObjectList('cfg_name');
		$locked = @ $config['lock']->cfg_value;
		$domail = @ $config['mail_on_new']->cfg_value;

		if (!$locked)
		{
			$this->user_id_from	= $fromId;
			$this->user_id_to	= $toId;
			$this->subject		= $subject;
			$this->message		= $message;
			$this->date_time	= JFactory::getDate()->toMySQL();

			if ($this->store())
			{
				if ($domail)
				{
					$query = 'SELECT name, email' .
							' FROM #__users' .
							' WHERE id = '.(int) $fromId;
					$db->setQuery($query);
					$fromObject = $db->loadObject();
					$fromname	= $fromObject->name;
					$mailfrom	= $fromObject->email;
					$siteURL		= JURI::base();
					$sitename 		= $app->getCfg('sitename');

					$query = 'SELECT email' .
							' FROM #__users' .
							' WHERE id = '.(int) $toId;
					$db->setQuery($query);
					$recipient	= $db->loadResult();

					$subject	= sprintf (JText::_('A new private message has arrived'), $sitename);
					$msg		= sprintf (JText::_('Please login to read your message'), $siteURL);

					JUtility::sendMail($mailfrom, $fromname, $recipient, $subject, $msg);
				}
				return true;
			}
		}
		else
		{
			if (is_object($this)) {
				$this->setError(JText::_('MESSAGE_FAILED'));
			}
		}
		return false;
	}
}
