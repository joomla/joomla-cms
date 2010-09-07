<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
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
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		// Register proxy tasks.
		$this->registerTask('apply',		'save');
	}

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
	 * Method to add a new menu item.
	 *
	 * @return	void
	 */
	public function add()
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Clear the menu item edit information from the session.
		$app->setUserState('com_menus.edit.menu.id', null);
		$app->setUserState('com_menus.edit.menu.data', null);

		// Redirect to the edit screen.
		$this->setRedirect(JRoute::_('index.php?option=com_menus&view=menu&layout=edit', false));
	}

	/**
	 * Method to edit an existing menu item.
	 *
	 * @return	void
	 */
	public function edit()
	{
		// Initialise variables.
		$app	= JFactory::getApplication();
		$ids	= JRequest::getVar('cid', array(), '', 'array');

		// Get the id of the group to edit.
		$id		=  (empty($ids) ? JRequest::getInt('menu_id') : (int) array_pop($ids));

		// Push the new row id into the session.
		$app->setUserState('com_menus.edit.menu.id',	$id);
		$app->setUserState('com_menus.edit.menu.data', null);
		$this->setRedirect('index.php?option=com_menus&view=menu&layout=edit');
		return true;
	}

	/**
	 * Method to cancel an edit
	 *
	 * Checks the item in, sets item ID in the session to null, and then redirects to the list page.
	 *
	 * @return	void
	 */
	public function cancel()
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Clear the menu item edit information from the session.
		$app->setUserState('com_menus.edit.menu.id', null);
		$app->setUserState('com_menus.edit.menu.data', null);

		// Redirect to the list screen.
		$this->setRedirect(JRoute::_('index.php?option=com_menus&view=menus', false));
	}

	/**
	 * Method to save a menu item.
	 *
	 * @return	void
	 */
	public function save()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app	= JFactory::getApplication();
		$task	= $this->getTask();

		// Get the posted values from the request.
		$data	= JRequest::getVar('jform', array(), 'post', 'array');

		// Check the menutype
		if($data['menutype'] == '_adminmenu'){
			JError::raiseNotice(0, JText::_('COM_MENUS_MENU_TYPE_NOT_ALLOWED'));
			// Redirect back to the edit screen.
			$this->setRedirect(JRoute::_('index.php?option=com_menus&view=menu&layout=edit', false));
			return false;
		}

		// Populate the row id from the session.
		$data['id'] = (int) $app->getUserState('com_menus.edit.menu.id');

		// Get the model and attempt to validate the posted data.
		$model	= $this->getModel('Menu');
		$form	= $model->getForm();
		if (!$form) {
			JError::raiseError(500, $model->getError());
			return false;
		}
		$data	= $model->validate($form, $data);

		// Check for validation errors.
		if ($data === false)
		{
			// Get the validation messages.
			$errors	= $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if (JError::isError($errors[$i])) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				} else {
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
		if (!$model->save($data))
		{
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
				// Redirect back to the edit screen.
				$this->setRedirect(JRoute::_('index.php?option=com_menus&view=menu&layout=edit', false));
				break;

			case 'save2new':
				// Clear the menu id and data from the session.
				$app->setUserState('com_menus.edit.menu.id', null);
				$app->setUserState('com_menus.edit.menu.data', null);

				// Redirect back to the edit screen.
				$this->setRedirect(JRoute::_('index.php?option=com_menus&view=menu&layout=edit', false));
				break;

			default:
				// Clear the menu id and data from the session.
				$app->setUserState('com_menus.edit.menu.id', null);
				$app->setUserState('com_menus.edit.menu.data', null);

				// Redirect to the list screen.
				$this->setRedirect(JRoute::_('index.php?option=com_menus&view=menus', false));
				break;
		}
	}
}
