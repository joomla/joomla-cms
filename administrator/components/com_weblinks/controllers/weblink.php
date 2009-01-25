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
class WeblinksControllerWeblink extends WeblinksController
{
	/**
	 * Method to save the changes to the current label and return
	 * back to the label edit view.
	 *
	 * @access	public
	 * @return	bool	False on failure or error, true on success.
	 * @since	1.0
	 */
	function apply()
	{
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Initialize variables.
		$app	= &JFactory::getApplication();
		$model	= &$this->getModel('Label');
		$data	= JRequest::getVar('jxform', array(), 'post', 'array');

		// Validate the posted data.
		$data = $model->validate($data);

		// Check for validation errors.
		if ($data === false)
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
			$app->setUserState('com_labels.edit.label.data', $data);

			// Redirect back to the edit screen.
			$this->setRedirect(JRoute::_('index.php?option=com_labels&view=label&layout=edit&hidemainmenu=1', false));
			return false;
		}

		// Attempt to save the label.
		$return = $model->save($data);

		if ($return === false)
		{
			// Save failed, go back to the label and display a notice.
			$message = JText::sprintf('LABELS_LABEL_APPLY_FAILED', $model->getError());
			$this->setRedirect('index.php?option=com_labels&view=label&layout=edit&hidemainmenu=1', $message, 'error');
			return false;
		}
		else
		{
			// Attempt to check-out the new label for editing and redirect.
			if (!$model->checkout($return))
			{
				// Check-out failed, go back to the list and display a notice.
				$message = JText::sprintf('LABELS_LABEL_CHECKOUT_FAILED', $model->getError());
				$this->setRedirect('index.php?option=com_labels&view=labels', $message, 'error');
				return false;
			}
			else
			{
				// Save succeeded, go back to the label and display a message.
				$app->setUserState('com_labels.edit.label.id', $return);
				$message = JText::_('LABELS_LABEL_APPLY_SUCCESS');
				$this->setRedirect('index.php?option=com_labels&view=label&layout=edit&hidemainmenu=1', $message);
				return true;
			}
		}
	}

	/**
	 * Method to cancel the edit operation, check-in the checked-out
	 * label and go back to the label list view.
	 *
	 * @access	public
	 * @return	bool	False on failure or error, true on success.
	 * @since	1.0
	 */
	function cancel()
	{
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Initialize variables.
		$app	= &JFactory::getApplication();
		$model	= &$this->getModel('Label', 'LabelsModel');

		// Get the label id.
		$label_id = (int) $app->getUserState('com_labels.edit.label.id');

		// Attempt to check-in the current label.
		if ($label_id)
		{
			if (!$model->checkin($label_id))
			{
				// Check-in failed, go back to the label and display a notice.
				$message = JText::sprintf('LABELS_LABEL_CHECKIN_FAILED', $model->getError());
				$this->setRedirect('index.php?option=com_labels&view=label&layout=edit&hidemainmenu=1', $message, 'error');
				return false;
			}
		}

		// Clean the session data and redirect.
		$app->setUserState('com_labels.edit.label.id', null);
		$this->setRedirect('index.php?option=com_labels&view=labels');
	}

	/**
	 * Method to checkout a label for editing.  If a different label
	 * was previously checked-out, the previous label will be checked
	 * in first.
	 *
	 * @access	public
	 * @return	bool	False on failure or error, true on success.
	 * @since	1.0
	 */
	function edit()
	{
		// Initialize variables.
		$app	= &JFactory::getApplication();
		$model	= &$this->getModel('Label', 'LabelsModel');
		$cid	= JRequest::getVar('cid', array(), 'post', 'array');

		// Get the previous label id (if any) and the current label id.
		$previous_id	= (int) $app->getUserState('com_labels.edit.label.id');
		$label_id		= (int) (count($cid) ? $cid[0] : JRequest::getInt('label_id'));

		// If label ids do not match, checkin previous label.
		if (($previous_id > 0) && ($label_id != $previous_id))
		{
			if (!$model->checkin($previous_id))
			{
				// Check-in failed, go back to the label and display a notice.
				$message = JText::sprintf('LABELS_LABEL_CHECKIN_FAILED', $model->getError());
				$this->setRedirect('index.php?option=com_labels&view=label&layout=edit&hidemainmenu=1', $message, 'error');
				return false;
			}
		}

		// Attempt to check-out the new label for editing and redirect.
		if (!$model->checkout($label_id))
		{
			// Check-out failed, go back to the list and display a notice.
			$message = JText::sprintf('LABELS_LABEL_CHECKOUT_FAILED', $model->getError());
			$this->setRedirect('index.php?option=com_labels&view=label&label_id='.$label_id, $message, 'error');
			return false;
		}
		else
		{
			// Check-out succeeded, push the new label id into the session.
			$app->setUserState('com_labels.edit.label.id', $label_id);
			$this->setRedirect('index.php?option=com_labels&view=label&layout=edit&hidemainmenu=1');
			return true;
		}
	}

	/**
	 * Method to get a fresh label form for creating a new label.
	 *
	 * @access	public
	 * @return	bool	False on failure or error, true on success.
	 * @since	1.0
	 */
	function createnew()
	{
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Initialize variables.
		$app = &JFactory::getApplication();

		// Prepare the session data and redirect.
		$app->setUserState('com_labels.edit.label.id', -1);
		$this->setRedirect('index.php?option=com_labels&view=label&layout=edit&hidemainmenu=1');
		return true;
	}

	/**
	 * Method to check-in a label and redirect to a content item.
	 *
	 * @access	public
	 * @return	bool	False on failure or error, true on success.
	 * @since	1.0
	 */
	function viewitem()
	{
		// Initialize variables.
		$app	= &JFactory::getApplication();
		$model	= &$this->getModel('Label');
		$target	= JRequest::getVar('target', '', 'request', 'base64');
		$target	= base64_decode($target, true);

		// Prepare the model state.
		$model->getState();
		$model->setState('label.id', $app->getUserState('com_labels.edit.label.id'));

		// Save succeeded, check-in the label.
		if (!$model->checkin())
		{
			// Check-in failed, go back to the label and display a notice.
			$message = JText::sprintf('LABELS_LABEL_CHECKIN_FAILED', $model->getError());
			$this->setRedirect('index.php?option=com_labels&view=label&layout=edit&hidemainmenu=1', $message, 'error');
			return false;
		}

		if (!$target)
		{
			// Redirect decode failed, go back to the label and display a notice.
			$message = JText::sprintf('LABELS_LABEL_REDIRECT_FAILED', $model->getError());
			$this->setRedirect('index.php?option=com_labels&view=label&layout=edit&hidemainmenu=1', $message, 'error');
			return false;
		}
		else
		{
			// Prepare the session data and redirect.
			$app->setUserState('com_labels.edit.label.id', -1);
			$this->setRedirect($target);
			return true;
		}
	}

	/**
	 * Method to save the changes to the current label and return
	 * back to the label list view.
	 *
	 * @access	public
	 * @return	bool	False on failure or error, true on success.
	 * @since	1.0
	 */
	function save()
	{
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Initialize variables.
		$app	= &JFactory::getApplication();
		$model	= &$this->getModel('Label');
		$data	= JRequest::getVar('jxform', array(), 'post', 'array');

		// Validate the posted data.
		$data = $model->validate($data);

		// Check for validation errors.
		if ($data === false)
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
			$app->setUserState('com_labels.edit.label.data', $data);

			// Redirect back to the edit screen.
			$this->setRedirect(JRoute::_('index.php?option=com_labels&view=label&layout=edit&hidemainmenu=1', false));
			return false;
		}

		// Attempt to save the label.
		$return = $model->save($data);

		if ($return === false)
		{
			// Save failed, go back to the label and display a notice.
			$message = JText::sprintf('LABELS_LABEL_SAVE_FAILED', $model->getError());
			$this->setRedirect('index.php?option=com_labels&view=label&layout=edit&hidemainmenu=1', $message, 'error');
			return false;
		}

		// Save succeeded, check-in the label.
		if (!$model->checkin())
		{
			// Check-in failed, go back to the label and display a notice.
			$message = JText::sprintf('LABELS_LABEL_CHECKIN_FAILED', $model->getError());
			$this->setRedirect('index.php?option=com_labels&view=label&layout=edit&hidemainmenu=1', $message, 'error');
			return false;
		}

		// Clean the session data.
		$app->setUserState('com_labels.edit.label.id', null);

		$message = JText::_('LABELS_LABEL_SAVE_SUCCESS');
		$this->setRedirect('index.php?option=com_labels&view=labels', $message);
		return true;
	}

	/**
	 * Method to save the changes to the current label and return
	 * back to the label edit view with a clean form.
	 *
	 * @access	public
	 * @return	bool	False on failure or error, true on success.
	 * @since	1.0
	 */
	function savenew()
	{
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Initialize variables.
		$app	= &JFactory::getApplication();
		$model	= &$this->getModel('Label');
		$data	= JRequest::getVar('jxform', array(), 'post', 'array');

		// Validate the posted data.
		$data = $model->validate($data);

		// Check for validation errors.
		if ($data === false)
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
			$app->setUserState('com_labels.edit.label.data', $data);

			// Redirect back to the edit screen.
			$this->setRedirect(JRoute::_('index.php?option=com_labels&view=label&layout=edit&hidemainmenu=1', false));
			return false;
		}

		// Attempt to save the label.
		$return = $model->save($data);

		if ($return === false)
		{
			// Save failed, go back to the label and display a notice.
			$message = JText::sprintf('LABELS_LABEL_SAVE_FAILED', $model->getError());
			$this->setRedirect('index.php?option=com_labels&view=label&layout=edit&hidemainmenu=1', $message, 'error');
			return false;
		}

		// Save succeeded, check-in the label.
		if (!$model->checkin())
		{
			// Check-in failed, go back to the label and display a notice.
			$message = JText::sprintf('LABELS_LABEL_CHECKIN_FAILED', $model->getError());
			$this->setRedirect('index.php?option=com_labels&view=label&layout=edit&hidemainmenu=1', $message, 'error');
			return false;
		}

		// Prepare the session data.
		$app->setUserState('com_labels.edit.label.id', -1);

		$message = JText::_('LABELS_LABEL_SAVE_SUCCESS');
		$this->setRedirect('index.php?option=com_labels&view=label&layout=edit&hidemainmenu=1', $message);
		return true;
	}

	/**
	 * Method to view a label.
	 *
	 * @access	public
	 * @return	bool	False on failure or error, true on success.
	 * @since	1.0
	 */
	function view()
	{
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		$app		= &JFactory::getApplication();
		$model		= &$this->getModel('Label', 'LabelsModel');
		$cid		= JRequest::getVar('cid', array(), 'post', 'array');
		$label_id	= (int) (count($cid) ? $cid[0] : JRequest::getInt('label_id'));

		// Check-in the label just to be safe.
		$model->checkin($label_id);

		$app->setUserState('com_labels.view.label.id', $label_id);
		$this->setRedirect('index.php?option=com_labels&view=label&layout=default&label_id='.$label_id.'&hidemainmenu=1');
		return true;
	}
}