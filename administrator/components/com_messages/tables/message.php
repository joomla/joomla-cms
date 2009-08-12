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
	 * @access	public
	 * @var		int
	 */
	var $message_id	= null;

	/**
	 * Sender's userid
	 *
	 * @access	public
	 * @var		int
	 */
	var $user_id_from	= null;

	/**
	 * Recipient's userid
	 *
	 * @access	public
	 * @var		int
	 */
	var $user_id_to		= null;

	/**
	 * @access	public
	 * @var		int
	 */
	var $folder_id			= null;

	/**
	 * Message creation timestamp
	 *
	 * @access	public
	 * @var		datetime
	 */
	var $date_time		= null;

	/**
	 * Message state
	 *
	 * @access	public
	 * @var		int
	 */
	var $state				= null;

	/**
	 * Priority level of the message
	 *
	 * @access	public
	 * @var		int
	 */
	var $priority			= null;

	/**
	 * The message subject
	 *
	 * @access	public
	 * @var		string
	 */
	var $subject			= null;

	/**
	 * The message body
	 *
	 * @access	public
	 * @var		text
	 */
	var $message			= null;

	/**
	 * Constructor
	 *
	 * @access	protected
	 * @param database A database connector object
	 */
	function __construct(& $db)
	{
		parent::__construct('#__messages', 'message_id', $db);
	}

	/**
	* Validation and filtering
	*/
	function check() {
		return true;
	}

	/**
	 * Method to send a private message
	 *
	 * @access	public
	 * @param	int		$fromId		Sender's userid
	 * @param	int		$toId		Recipient's userid
	 * @param	string	$subject	The message subject
	 * @param	string	$message	The message body
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function send($fromId = null, $toId = null, $subject = null, $message = null, $mailfrom = null, $fromname = null)
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
			$date = &JFactory::getDate();
			$this->date_time	= $date->toMySQL();

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
