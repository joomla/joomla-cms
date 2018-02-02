<?php
/**
 * @package     Joomla.Installation
 * @subpackage  Response
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\Response;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;

/**
 * JSON Response class for the Joomla Installer.
 *
 * @since  3.1
 */
class JsonResponse
{
	/**
	 * Constructor for the JSON response
	 *
	 * @param   mixed  $data  Exception if there is an error, otherwise, the session data
	 *
	 * @since   3.1
	 */
	public function __construct($data)
	{
		// The old token is invalid so send a new one.
		$this->token = Session::getFormToken(true);

		// Get the language and send it's tag along
		$this->lang = Factory::getLanguage()->getTag();

		// Get the message queue
		$messages = Factory::getApplication()->getMessageQueue();

		// Build the sorted message list
		if (is_array($messages) && count($messages))
		{
			foreach ($messages as $msg)
			{
				if (isset($msg['type'], $msg['message']))
				{
					$lists[$msg['type']][] = $msg['message'];
				}
			}
		}

		// If messages exist add them to the output
		if (isset($lists) && is_array($lists))
		{
			$this->messages = $lists;
		}

		// Check if we are dealing with an error.
		if ($data instanceof \Throwable)
		{
			// Prepare the error response.
			$this->error   = true;
			$this->header  = \JText::_('INSTL_HEADER_ERROR');
			$this->message = $data->getMessage();
		}
		else
		{
			// Prepare the response data.
			$this->error = false;
			$this->data  = $data;
		}
	}
}
