<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined( '_JEXEC' ) or die;

jimport('joomla.application.component.controllerform');

/**
 * Messages Component Message Model
 *
 * @package		Joomla.Administrator
 * @subpackage	com_messages
 * @since		1.6
 */
class MessagesControllerMessage extends JControllerForm
{
	/**
	 * Method (override) to check if you can save a new or existing record.
	 *
	 * Adjusts for the primary key name and hands off to the parent class.
	 *
	 * @param	array	An array of input data.
	 * @param	string	The name of the key for the primary key.
	 *
	 * @return	boolean
	 */
	protected function allowSave($data, $key = 'message_id')
	{
		return parent::allowSave($data, $key);
	}

	/**
	 * Reply to an existing message.
	 *
	 * This is a simple redirect to the compose form.
	 */
	public function reply()
	{
		if ($replyId = JRequest::getInt('reply_id')) {
			$this->setRedirect('index.php?option=com_messages&view=message&layout=edit&reply_id='.$replyId);
		} else {
			$this->setMessage(JText::_('COM_MESSAGES_INVALID_REPLY_ID'));
			$this->setRedirect('index.php?option=com_messages&view=messages');
		}
	}
}
