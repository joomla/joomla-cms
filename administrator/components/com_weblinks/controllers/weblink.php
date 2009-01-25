<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

defined('_JEXEC') or die;
/**
 * Weblink controller class.
 *
 * @package		Joomla.Administrator
 * @subpackage	Weblinks
 * @version		1.6
 */
class WeblinksControllerWeblink extends JController
{
	/**
	 * Dummy method to redirect back to standard controller
	 *
	 * @return	void
	 */
	public function display()
	{
		$this->setRedirect(JRoute::_('index.php?option=com_weblinks', false));
	}

	/**
	 * Method to add a new weblink.
	 *
	 * @return	void
	 */
	public function add()
	{
		// Initialize variables.
		$app = &JFactory::getApplication();

		// Clear the level edit information from the session.
		$app->setUserState('com_weblinks.edit.weblink.id', null);
		$app->setUserState('com_weblinks.edit.weblink.data', null);

		// Redirect to the edit screen.
		$this->setRedirect(JRoute::_('index.php?option=com_weblinks&view=weblink&layout=edit', false));
	}

	/**
	 * Method to edit an existing weblink.
	 *
	 * @return	void
	 */
	public function edit()
	{
		// Initialize variables.
		$app	= &JFactory::getApplication();
		$model	= &$this->getModel('Weblink', 'WeblinksModel');
		$cid	= JRequest::getVar('cid', array(), 'post', 'array');

		// Get the previous label id (if any) and the current label id.
		$previousId	= (int) $app->getUserState('com_labels.edit.label.id');
		$weblinkId		= (int) (count($cid) ? $cid[0] : JRequest::getInt('weblink_id'));

		// If label ids do not match, checkin previous label.
		if (($previousId > 0) && ($weblinkId != $previousId))
		{
			if (!$model->checkin($previousId))
			{
				// Check-in failed, go back to the label and display a notice.
				$message = JText::sprintf('JError_Checkin_failed', $model->getError());
				$this->setRedirect('index.php?option=com_weblinks&view=weblink&layout=edit', $message, 'error');
				return false;
			}
		}

		// Attempt to check-out the new label for editing and redirect.
		if (!$model->checkout($weblinkId))
		{
			// Check-out failed, go back to the list and display a notice.
			$message = JText::sprintf('LABELS_LABEL_CHECKOUT_FAILED', $model->getError());
			$this->setRedirect('index.php?option=com_weblinks&view=weblink&label_id='.$weblinkId, $message, 'error');
			return false;
		}
		else
		{
			// Check-out succeeded, push the new label id into the session.
			$app->setUserState('com_weblinks.edit.weblink.id',	$weblinkId);
			$app->setUserState('com_weblinks.edit.weblink.data', null);
			$this->setRedirect('index.php?option=com_weblinks&view=weblink&layout=edit');
			return true;
		}
	}

	/**
	 * Method to cancel an edit
	 *
	 * @access	public
	 * @return	void
	 * @since	1.0
	 */
	public function cancel()
	{
		// Initialize variables.
		$app = &JFactory::getApplication();

		// Clear the member edit information from the session.
		$app->setUserState('com_weblinks.edit.weblink.id', null);
		$app->setUserState('com_weblinks.edit.weblink.data', null);

		// Redirect to the list screen.
		$this->setRedirect(JRoute::_('index.php?option=com_weblinks&view=weblinks', false));
	}

	/**
	 * Method to save a weblink.
	 *
	 * @access	public
	 * @return	void
	 * @since	1.0
	 */
	public function save()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JX_INVALID_TOKEN'));

		// Initialize variables.
		$app = &JFactory::getApplication();

		// Get the posted values from the request.
		$data = JRequest::getVar('jxform', array(), 'post', 'array');

		// Populate the row id from the session.
		$data['id'] = (int) $app->getUserState('com_weblinks.edit.weblink.id');

		// Get the model and attempt to validate the posted data.
		$model = &$this->getModel('Member');
		$return	= $model->validate($data);

		// Get and sanitize the group data.
		$data['groups'] = JRequest::getVar('groups', array(), 'post', 'array');
		$data['groups'] = array_unique($data['groups']);
		JArrayHelper::toInteger($data['groups']);

		// Remove any values of zero.
		if (array_search(0, $data['groups'], true)) {
			unset($data['groups'][array_search(0, $data['groups'], true)]);
		}

		// Check for validation errors.
		if ($return === false)
		{
			// Get the validation messages.
			$errors	= $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if (JError::isError($errors[$i])) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'notice');
				} else {
					$app->enqueueMessage($errors[$i], 'notice');
				}
			}

			// Save the data in the session.
			$app->setUserState('com_weblinks.edit.weblink.data', $data);

			// Redirect back to the edit screen.
			$this->setRedirect(JRoute::_('index.php?option=com_weblinks&view=weblink&layout=edit', false));
			return false;
		}

		// Attempt to save the data.
		$return	= $model->save($data);

		// Check for errors.
		if ($return === false)
		{
			// Save the data in the session.
			$app->setUserState('com_weblinks.edit.weblink.data', $data);

			// Redirect back to the edit screen.
			$this->setMessage(JText::sprintf('MEMBERS_MEMBER_SAVE_FAILED', $model->getError()), 'notice');
			$this->setRedirect(JRoute::_('index.php?option=com_weblinks&view=weblink&layout=edit', false));
			return false;
		}

		// Redirect the user and adjust session state based on the chosen task.
		switch ($this->_task)
		{
			case 'apply':
				// Redirect back to the edit screen.
				$this->setMessage(JText::_('MEMBERS_MEMBER_SAVE_SUCCESS'));
				$this->setRedirect(JRoute::_('index.php?option=com_weblinks&view=weblink&layout=edit', false));
				break;

			case 'save2new':
				// Clear the member id and data from the session.
				$app->setUserState('com_weblinks.edit.weblink.id', null);
				$app->setUserState('com_weblinks.edit.weblink.data', null);

				// Redirect back to the edit screen.
				$this->setMessage(JText::_('MEMBERS_MEMBER_SAVE_SUCCESS'));
				$this->setRedirect(JRoute::_('index.php?option=com_weblinks&view=weblink&layout=edit', false));
				break;

			default:
				// Clear the member id and data from the session.
				$app->setUserState('com_weblinks.edit.weblink.id', null);
				$app->setUserState('com_weblinks.edit.weblink.data', null);

				// Redirect to the list screen.
				$this->setMessage(JText::_('MEMBERS_MEMBER_SAVE_SUCCESS'));
				$this->setRedirect(JRoute::_('index.php?option=com_weblinks&view=weblinks', false));
				break;
		}
	}
}