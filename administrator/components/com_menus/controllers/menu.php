<?php
/**
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport( 'joomla.application.component.controllerform' );

/**
 * The Menu Type Controller
 *
 * @package		Joomla.Administrator
 * @subpackage	com_menus
 * @since		1.6
 */
class MenusControllerMenu extends JControllerForm
{
	/**
	 * Dummy method to redirect back to standard controller
	 *
	 * @param	boolean			If true, the view output will be cached
	 * @param	array			An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return	JController		This object to support chaining.
	 * @since	1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$this->setRedirect(JRoute::_('index.php?option=com_menus&view=menus', false));
	}

	/**
	 * Method to save a menu item.
	 *
	 * @return	void
	 */
	public function save($key = null, $urlVar = null)
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app		= JFactory::getApplication();
		$data		= JRequest::getVar('jform', array(), 'post', 'array');
		$context	= 'com_menus.edit.menu';
		$task		= $this->getTask();
		$recordId	= JRequest::getInt('id');

		if (!$this->checkEditId($context, $recordId)) {
			// Somehow the person just went to the form and saved it - we don't allow that.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $recordId));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list.$this->getRedirectToListAppend(), false));

			return false;
		}

		// Make sure we are not trying to modify an administrator menu.
		if ((isset($data['client_id']) && $data['client_id'] == 1) || strtolower($data['menutype']) == 'menu'
			|| strtolower($data['menutype']) == 'main')
		{
			JError::raiseNotice(0, JText::_('COM_MENUS_MENU_TYPE_NOT_ALLOWED'));

			// Redirect back to the edit screen.
			$this->setRedirect(JRoute::_('index.php?option=com_menus&view=menu&layout=edit', false));

			return false;
		}

		// Populate the row id from the session.
		$data['id'] = $recordId;

		// Get the model and attempt to validate the posted data.
		$model	= $this->getModel('Menu');
		$form	= $model->getForm();
		if (!$form) {
			JError::raiseError(500, $model->getError());

			return false;
		}

		$data	= $model->validate($form, $data);

		// Check for validation errors.
		if ($data === false) {
			// Get the validation messages.
			$errors	= $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else {
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}
			// Save the data in the session.
			$app->setUserState('com_menus.edit.menu.data', $data);

			// Redirect back to the edit screen.
			$this->setRedirect(JRoute::_('index.php?option=com_menus&view=menu&layout=edit', false));

			return false;
		}

		// Attempt to save the data.
		if (!$model->save($data)) {
			// Save the data in the session.
			$app->setUserState('com_menus.edit.menu.data', $data);

			// Redirect back to the edit screen.
			$this->setMessage(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()), 'warning');
			$this->setRedirect(JRoute::_('index.php?option=com_menus&view=menu&layout=edit', false));

			return false;
		}

		$this->setMessage(JText::_('COM_MENUS_MENU_SAVE_SUCCESS'));

		// Redirect the user and adjust session state based on the chosen task.
		switch ($task)
		{
			case 'apply':
				// Set the record data in the session.
				$recordId = $model->getState($this->context.'.id');
				$this->holdEditId($context, $recordId);

				// Redirect back to the edit screen.
				$this->setRedirect(JRoute::_('index.php?option=com_menus&view=menu&layout=edit'.$this->getRedirectToItemAppend($recordId), false));
				break;

			case 'save2new':
				// Clear the record id and data from the session.
				$this->releaseEditId($context, $recordId);
				$app->setUserState($context.'.data', null);

				// Redirect back to the edit screen.
				$this->setRedirect(JRoute::_('index.php?option=com_menus&view=menu&layout=edit', false));
				break;

			default:
				// Clear the record id and data from the session.
				$this->releaseEditId($context, $recordId);
				$app->setUserState($context.'.data', null);

				// Redirect to the list screen.
				$this->setRedirect(JRoute::_('index.php?option=com_menus&view=menus', false));
				break;
		}
	}
}
