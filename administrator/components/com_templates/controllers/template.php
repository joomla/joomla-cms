<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('InstallerModelInstall', JPATH_ADMINISTRATOR . '/components/com_installer/models/install.php');

/**
 * Template style controller class.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 * @since       1.6
 */
class TemplatesControllerTemplate extends JControllerLegacy
{
	/**
	 */
	public function cancel()
	{
		$this->setRedirect('index.php?option=com_templates&view=templates');
	}

	public function copy()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app = JFactory::getApplication();
		$this->input->set('installtype', 'folder');
		$newName    = $this->input->get('new_name');
		$newNameRaw = $this->input->get('new_name', null, 'string');
		$templateID = $this->input->getInt('id', 0);

		$this->setRedirect('index.php?option=com_templates&view=template&id=' . $templateID);
		$model = $this->getModel('Template', 'TemplatesModel');
		$model->setState('new_name', $newName);
		$model->setState('tmp_prefix', uniqid('template_copy_'));
		$model->setState('to_path', JFactory::getConfig()->get('tmp_path') . '/' . $model->getState('tmp_prefix'));

		// Process only if we have a new name entered
		if (strlen($newName) > 0)
		{
			if (!JFactory::getUser()->authorise('core.create', 'com_templates'))
			{
				// User is not authorised to delete
				JError::raiseWarning(403, JText::_('COM_TEMPLATES_ERROR_CREATE_NOT_PERMITTED'));
				return false;
			}

			// Set FTP credentials, if given
			JClientHelper::setCredentialsFromRequest('ftp');

			// Check that new name is valid
			if (($newNameRaw !== null) && ($newName !== $newNameRaw))
			{
				JError::raiseWarning(403, JText::_('COM_TEMPLATES_ERROR_INVALID_TEMPLATE_NAME'));
				return false;
			}

			// Check that new name doesn't already exist
			if (!$model->checkNewName())
			{
				JError::raiseWarning(403, JText::_('COM_TEMPLATES_ERROR_DUPLICATE_TEMPLATE_NAME'));
				return false;
			}

			// Check that from name does exist and get the folder name
			$fromName = $model->getFromName();
			if (!$fromName)
			{
				JError::raiseWarning(403, JText::_('COM_TEMPLATES_ERROR_INVALID_FROM_NAME'));
				return false;
			}

			// Call model's copy method
			if (!$model->copy())
			{
				JError::raiseWarning(403, JText::_('COM_TEMPLATES_ERROR_COULD_NOT_COPY'));
				return false;
			}

			// Call installation model
			$this->input->set('install_directory', JFactory::getConfig()->get('tmp_path') . '/' . $model->getState('tmp_prefix'));
			$installModel = $this->getModel('Install', 'InstallerModel');
			JFactory::getLanguage()->load('com_installer');
			if (!$installModel->install())
			{
				JError::raiseWarning(403, JText::_('COM_TEMPLATES_ERROR_COULD_NOT_INSTALL'));
				return false;
			}

			$this->setMessage(JText::sprintf('COM_TEMPLATES_COPY_SUCCESS', $newName));
			$model->cleanup();
			return true;

		}
	}
	
	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string	The model name. Optional.
	 * @param   string	The class prefix. Optional.
	 * @param   array  Configuration array for model. Optional (note, the empty array is atypical compared to other models).
	 *
	 * @return  object  The model.
	 */
	public function getModel($name = 'Template', $prefix = 'TemplatesModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}
	
	/**
	 * Method to check if you can add a new record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array  An array of input data.
	 * @param   string	The name of the key for the primary key.
	 *
	 * @return  boolean
	 */
	protected function allowEdit()
	{
		return JFactory::getUser()->authorise('core.edit', 'com_templates');
	}
	
	/**
	 * Method to check if you can save a new or existing record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array  An array of input data.
	 * @param   string	The name of the key for the primary key.
	 *
	 * @return  boolean
	 */
	protected function allowSave()
	{
		return $this->allowEdit();
	}
	/**
	 * This controller does not have a display method. Redirect back to the list view of the component.
	 *
	 * @param   boolean			If true, the view output will be cached
	 * @param   array  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JController		This object to support chaining.
	 * @since   1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$this->setRedirect(JRoute::_('index.php?option=com_templates&view=templates', false));
	}
	
	/**
	 * Method to edit an existing record.
	 *
	 * @return  void
	 */
	public function edit()
	{
		$app		= JFactory::getApplication();
		$model		= $this->getModel();
		$recordId	= JRequest::getVar('id');
		$context	= 'com_templates.edit.source';
	
		if (preg_match('#\.\.#', base64_decode($recordId)))
		{
			return JError::raiseError(500, JText::_('COM_TEMPLATES_ERROR_SOURCE_FILE_NOT_FOUND'));
		}
	
		// Access check.
		if (!$this->allowEdit())
		{
			return JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'));
		}
	
		// Check-out succeeded, push the new record id into the session.
		$app->setUserState($context.'.id',	$recordId);
		$app->setUserState($context.'.data', null);
		$this->setRedirect('index.php?option=com_templates&view=source&layout=edit');
		return true;
	}
	/**
	 * Saves a template source file.
	 */
	public function save()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
	
		$app     = JFactory::getApplication();
		$data    = $this->input->post->get('jform', array(), 'array');
		$context = 'com_templates.edit.source';
		$task    = $this->getTask();
		$model   = $this->getModel();
	
		// Access check.
		if (!$this->allowSave())
		{
			return JError::raiseWarning(403, JText::_('JERROR_SAVE_NOT_PERMITTED'));
		}
	
		// Match the stored id's with the submitted.
		if (empty($data['extension_id']) || empty($data['filename']))
		{
			return JError::raiseError(500, JText::_('COM_TEMPLATES_ERROR_SOURCE_ID_FILENAME_MISMATCH'));
		}
		elseif ($data['extension_id'] != $model->getState('extension.id'))
		{
			return JError::raiseError(500, JText::_('COM_TEMPLATES_ERROR_SOURCE_ID_FILENAME_MISMATCH'));
		}
		elseif ($data['filename'] != $model->getState('filename'))
		{
			return JError::raiseError(500, JText::_('COM_TEMPLATES_ERROR_SOURCE_ID_FILENAME_MISMATCH'));
		}
	
		// Validate the posted data.
		$form	= $model->getForm();
		if (!$form)
		{
			JError::raiseError(500, $model->getError());
			return false;
		}
		$data = $model->validate($form, $data);
	
		// Check for validation errors.
		if ($data === false)
		{
			// Get the validation messages.
			$errors	= $model->getErrors();
	
			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else {
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}
	
			// Save the data in the session.
			$app->setUserState($context.'.data', $data);
	
			// Redirect back to the edit screen.
			$this->setRedirect(JRoute::_('index.php?option=com_templates&view=source&layout=edit', false));
					return false;
		}
	
		// Attempt to save the data.
		if (!$model->save($data))
		{
		// Save the data in the session.
			$app->setUserState($context.'.data', $data);

			// Redirect back to the edit screen.
			$this->setMessage(JText::sprintf('JERROR_SAVE_FAILED', $model->getError()), 'warning');
			$this->setRedirect(JRoute::_('index.php?option=com_templates&view=source&layout=edit', false));
					return false;
		}

		$this->setMessage(JText::_('COM_TEMPLATES_FILE_SAVE_SUCCESS'));

				// Redirect the user and adjust session state based on the chosen task.
		switch ($task)
		{
		case 'apply':
			// Reset the record data in the session.
			$app->setUserState($context.'.data',	null);

			// Redirect back to the edit screen.
			$this->setRedirect(JRoute::_('index.php?option=com_templates&view=source&layout=edit', false));
					break;

					default:
					// Clear the record id and data from the session.
					$app->setUserState($context.'.id', null);
					$app->setUserState($context.'.data', null);

					// Redirect to the list screen.
					$this->setRedirect(JRoute::_('index.php?option=com_templates&view=template&id='.$model->getState('extension.id'), false));
							break;
		}
	}
	
}
