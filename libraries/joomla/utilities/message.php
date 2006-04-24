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

jimport('joomla.database.table');

/**
 * Message Class
 *
 * Provides a common interface to send an internal message to
 * a JUser via the Joomla! Framework
 *
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package 	Joomla.Framework
 * @subpackage 	Utilities
 * @since		1.5
 */
class JMessage extends JTable
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
	 * JMessage constructor
	 *
	 * @access	protected
	 * @param database A database connector object
	 */
	function __construct(& $db)
	{
		parent::__construct('#__messages', 'message_id', $db);
	}

	/**
	 * Method to send a private message
	 *
	 * @access	public
	 * @param	int		$fromId		Sender's userid
	 * @param	int		$toId			Recipient's userid
	 * @param	string	$subject		The message subject
	 * @param	string	$message	The message body
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function send($fromId = null, $toId = null, $subject = null, $message = null)
	{
		global $mainframe, $mosConfig_mailfrom, $mosConfig_fromname;

		$database =& $mainframe->getDBO();

		if (is_object($this))
		{
			$fromId		= $fromId	? $fromId	: $this->user_id_from;
			$toId		= $toId		? $toId		: $this->user_id_to;
			$subject	= $subject	? $subject	: $this->subject;
			$message	= $message	? $message	: $this->message;
		}

		$query = "SELECT cfg_name, cfg_value" .
				"\n FROM #__messages_cfg" .
				"\n WHERE user_id = $toId";
		$database->setQuery($query);

		$config = $database->loadObjectList('cfg_name');
		$locked = @ $config['lock']->cfg_value;
		$domail = @ $config['mail_on_new']->cfg_value;

		if (!$locked)
		{
			$this->user_id_from	= $fromId;
			$this->user_id_to	= $toId;
			$this->subject		= $subject;
			$this->message		= $message;
			$this->date_time	= date('Y-m-d H:i:s');

			if ($this->store())
			{
				if ($domail)
				{
					$query = "SELECT email" .
							"\n FROM #__users" .
							"\n WHERE id = $to_id";
					$database->setQuery($query);
					$recipient	= $database->loadResult();
					$subject	= JText::_('A new private message has arrived');
					$msg		= JText::_('A new private message has arrived');

					josMail($mosConfig_mailfrom, $mosConfig_fromname, $recipient, $subject, $msg);
				}
				return true;
			}
		}
		else
		{
			if (is_object($this))
			{
				$this->_error = JText::_('MESSAGE_FAILED');
			}
		}
		return false;
	}
}
?>