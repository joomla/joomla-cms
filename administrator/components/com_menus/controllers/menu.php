<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * The Menu Type Controller
 *
 * @since  1.6
 */
class MenusControllerMenu extends JControllerForm
{
	/**
	 * Dummy method to redirect back to standard controller
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached.
	 * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JController		This object to support chaining.
	 *
	 * @since   1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$this->setRedirect(JRoute::_('index.php?option=com_menus&view=menus', false));
	}

	/**
	 * Method to save a menu item.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if successful, false otherwise.
	 *
	 * @since   1.6
	 */
	public function save($key = null, $urlVar = null)
	{
		// Check for request forgeries.
		$this->checkToken();

		$app      = JFactory::getApplication();
		$data     = $this->input->post->get('jform', array(), 'array');
		$context  = 'com_menus.edit.menu';
		$task     = $this->getTask();
		$recordId = $this->input->getInt('id');

		// Prevent using 'main' as menutype as this is reserved for backend menus
		if (strtolower($data['menutype']) == 'main')
		{
			$msg = JText::_('COM_MENUS_ERROR_MENUTYPE');
			JFactory::getApplication()->enqueueMessage($msg, 'error');

			// Redirect back to the edit screen.
			$this->setRedirect(JRoute::_('index.php?option=com_menus&view=menu&layout=edit', false));

			return false;
		}

		// Populate the row id from the session.
		$data['id'] = $recordId;

		// Get the model and attempt to validate the posted data.
		$model = $this->getModel('Menu');
		$form  = $model->getForm();

		if (!$form)
		{
			JError::raiseError(500, $model->getError());

			return false;
		}

		$validData = $model->validate($form, $data);

		// Check for validation errors.
		if ($validData === false)
		{
			// Get the validation messages.
			$errors = $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else
				{
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			// Save the data in the session.
			$app->setUserState($context . '.data', $data);

			// Redirect back to the edit screen.
			$this->setRedirect(JRoute::_('index.php?option=com_menus&view=menu&layout=edit', false));

			return false;
		}

		if (isset($validData['preset']))
		{
			$preset = trim($validData['preset']) ?: null;

			unset($validData['preset']);
		}

		// Attempt to save the data.
		if (!$model->save($validData))
		{
			// Save the data in the session.
			$app->setUserState($context . '.data', $validData);

			// Redirect back to the edit screen.
			$this->setMessage(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_menus&view=menu&layout=edit', false));

			return false;
		}

		// Import the preset selected
		if (isset($preset) && $data['client_id'] == 1)
		{
			try
			{
				MenusHelper::installPreset($preset, $data['menutype']);

				$this->setMessage(JText::_('COM_MENUS_PRESET_IMPORT_SUCCESS'));
			}
			catch (Exception $e)
			{
				// Save was successful but the preset could not be loaded. Let it through with just a warning
				$this->setMessage(JText::sprintf('COM_MENUS_PRESET_IMPORT_FAILED', $e->getMessage()));
			}
		}
		else
		{
			$this->setMessage(JText::_('COM_MENUS_MENU_SAVE_SUCCESS'));
		}

		// Redirect the user and adjust session state based on the chosen task.
		switch ($task)
		{
			case 'apply':
				// Set the record data in the session.
				$recordId = $model->getState($this->context . '.id');
				$this->holdEditId($context, $recordId);

				// Redirect back to the edit screen.
				$this->setRedirect(JRoute::_('index.php?option=com_menus&view=menu&layout=edit' . $this->getRedirectToItemAppend($recordId), false));
				break;

			case 'save2new':
				// Clear the record id and data from the session.
				$this->releaseEditId($context, $recordId);
				$app->setUserState($context . '.data', null);

				// Redirect back to the edit screen.
				$this->setRedirect(JRoute::_('index.php?option=com_menus&view=menu&layout=edit', false));
				break;

			default:
				// Clear the record id and data from the session.
				$this->releaseEditId($context, $recordId);
				$app->setUserState($context . '.data', null);

				// Redirect to the list screen.
				$this->setRedirect(JRoute::_('index.php?option=com_menus&view=menus', false));
				break;
		}
	}

	/**
	 * Method to display a menu as preset xml.
	 *
	 * @return  boolean  True if successful, false otherwise.
	 *
	 * @since   3.8.0
	 */
	public function exportXml()
	{
		// Check for request forgeries.
		$this->checkToken();

		$cid   = $this->input->get('cid', array(), 'array');
		$model = $this->getModel('Menu');
		$item  = $model->getItem(reset($cid));

		if (!$item->menutype)
		{
			$this->setMessage(JText::_('COM_MENUS_SELECT_MENU_FIRST_EXPORT'), 'warning');

			$this->setRedirect(JRoute::_('index.php?option=com_menus&view=menus', false));

			return false;
		}

		$this->setRedirect(JRoute::_('index.php?option=com_menus&view=menu&menutype=' . $item->menutype . '&format=xml', false));

		return true;
	}
}
