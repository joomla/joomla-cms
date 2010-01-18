<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined( '_JEXEC' ) or die;

jimport('joomla.application.component.controller');

/**
 * Messages Component Message Model
 *
 * @package		Joomla.Administrator
 * @subpackage	com_messages
 * @since		1.6
 */
class MessagesControllerConfig extends JController
{
	/**
	 * Method to save a record.
	 */
	public function save()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Initialise variables.
		$app		= JFactory::getApplication();
		$model		= $this->getModel('Config', 'MessagesModel');
		$data		= JRequest::getVar('jform', array(), 'post', 'array');

		// Validate the posted data.
		$form	= $model->getForm();
		if (!$form) {
			JError::raiseError(500, $model->getError());
			return false;
		}
		$data = $model->validate($form, $data);

		// Check for validation errors.
		if ($data === false) {
			// Get the validation messages.
			$errors	= $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
				if (JError::isError($errors[$i])) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'notice');
				} else {
					$app->enqueueMessage($errors[$i], 'notice');
				}
			}

			// Redirect back to the main list.
			$this->setRedirect(JRoute::_('index.php?option=com_messages&view=messages', false));
			return false;
		}

		// Attempt to save the data.
		if (!$model->save($data))
		{
			// Redirect back to the main list.
			$this->setMessage(JText::sprintf('JError_Save_failed', $model->getError()), 'notice');
			$this->setRedirect(JRoute::_('index.php?option=com_messages&view=messages', false));
			return false;
		}

		// Redirect to the list screen.
		$this->setMessage(JText::_('Messages_Config_Saved'));
		$this->setRedirect(JRoute::_('index.php?option=com_messages&view=messages', false));

		return true;
	}
}