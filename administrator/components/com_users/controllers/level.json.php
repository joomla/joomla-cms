<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * The Users Access Level Controller
 * - JSON Protocol -
 *
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @since		1.6
 */
class UsersControllerLevel extends JController
{
	/**
	 * Method to add a comment via JSON.
	 *
	 * @return	void
	 */
	public function save()
	{
		// Check for a valid token. If invalid, send a 403 with the error message.
		JRequest::checkToken('request') or $this->sendResponse(new JException(JText::_('JInvalid_Token'), 403));

		// Get the posted values from the request.
		$data = JRequest::getVar('jform', array(), 'post', 'array');

		// Populate the row id from the session.
		$data['id'] = (int) $app->getUserState('com_users.edit.level.id');

		// Set the default parent id to 1.
		$data['parent_id'] = (!empty($data['parent_id'])) ? (int) $data['parent_id'] : 1;

		// Get the model and attempt to validate the posted data.
		$model = &$this->getModel('Level');
		$return	= $model->validate($data);

		// Check for validation errors.
		if ($return === false)
		{
			JError::raiseError(500, $model->getError());
			return false;
		}

		// Attempt to save the data.
		$return	= $model->save($data);

		// Check for errors.
		if ($return === false)
		{
			JError::raiseError(500, $model->getError());
			return false;
		}

		// Get the access level object.
		$level = $model->getItem($return);

		// Send the response.
		$this->sendResponse($level);
	}

	/**
	 * Method to handle a send a JSON response. The body parameter
	 * can be a JException object for when an error has occurred or
	 * a JObject for a good response.
	 *
	 * @param	object	$body	JObject on success, JException on failure.
	 * @return	void
	 */
	public function sendResponse($body)
	{
		// Check if we need to send an error code.
		if (JError::isError($body))
		{
			// Send the appropriate error code response.
			JResponse::setHeader('status', $body->getCode());
			JResponse::sendHeaders();
		}

		// Send the JSON response.
		echo json_encode(new UsersLevelResponse($body));

		// Close the application.
		$app = &JFactory::getApplication();
		$app->close();
	}
}

/**
 * Users Access Level JSON Response Class
 *
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @since		1.6
 */
class UsersLevelResponse
{
	public function __construct($state)
	{
		// The old token is invalid so send a new one.
		$this->token = JUtility::getToken(true);

		// Check if we are dealing with an error.
		if (JError::isError($state))
		{
			// Prepare the error response.
			$this->error	= true;
			$this->header	= JText::_('USERS_LEVEL_HEADER_ERROR');
			$this->message	= $state->getMessage();
		}
		else
		{
			// Prepare the response data.
			$this->error		= false;
			$this->id			= (int) $state->id;
			$this->name			= $state->name;
			$this->value		= (int) $state->value;
			$this->parent_id	= (int) $state->parent_id;
		}
	}
}

// This needs to be AFTER the class declaration because of PHP 5.1.
JError::setErrorHandling(E_ALL, 'callback', array('UsersControllerLevel', 'sendResponse'));