<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_messages
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Messages Component Message Model
 *
 * @since  1.6
 */
class MessagesControllerMessage extends JControllerForm
{
	/**
	 * Method (override) to check if you can save a new or existing record.
	 *
	 * Adjusts for the primary key name and hands off to the parent class.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	protected function allowSave($data, $key = 'message_id')
	{
		return parent::allowSave($data, $key);
	}

	/**
	 * Reply to an existing message.
	 *
	 * This is a simple redirect to the compose form.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function reply()
	{
		if ($replyId = $this->input->getInt('reply_id'))
		{
			$this->setRedirect('index.php?option=com_messages&view=message&layout=edit&reply_id=' . $replyId);
		}
		else
		{
			$this->setMessage(JText::_('COM_MESSAGES_INVALID_REPLY_ID'));
			$this->setRedirect('index.php?option=com_messages&view=messages');
		}
	}
}
