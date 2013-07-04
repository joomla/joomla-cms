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
	 * Constructor.
	 *
	 * @param   array An optional associative array of configuration settings.
	 * @see     JController
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
	
		// Apply, Save & New, and Save As copy should be standard on forms.
		$this->registerTask('apply', 'save');
	}
	
	public function cancel()
	{
		$this->setRedirect(JRoute::_('index.php?option=com_templates&view=templates', false));
	}

	public function copy()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $app        = JFactory::getApplication();
		$this->input->set('installtype', 'folder');
		$newName    = $this->input->get('new_name');
		$newNameRaw = $this->input->get('new_name', null, 'string');
		$templateID = $this->input->getInt('id', 0);
		$file       = $app->input->get('file');

		$this->setRedirect('index.php?option=com_templates&view=template&id=' . $templateID . '&file=' . $file);
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
				$app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_CREATE_NOT_PERMITTED'),'error');
				return false;
			}

			// Set FTP credentials, if given
			JClientHelper::setCredentialsFromRequest('ftp');

			// Check that new name is valid
			if (($newNameRaw !== null) && ($newName !== $newNameRaw))
			{
                $app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_INVALID_TEMPLATE_NAME'),'error');
				return false;
			}

			// Check that new name doesn't already exist
			if (!$model->checkNewName())
			{
                $app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_DUPLICATE_TEMPLATE_NAME'),'error');
				return false;
			}

			// Check that from name does exist and get the folder name
			$fromName = $model->getFromName();
			if (!$fromName)
			{
                $app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_INVALID_FROM_NAME'),'error');
				return false;
			}

			// Call model's copy method
			if (!$model->copy())
			{
                $app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_COULD_NOT_COPY'),'error');
				return false;
			}

			// Call installation model
			$this->input->set('install_directory', JFactory::getConfig()->get('tmp_path') . '/' . $model->getState('tmp_prefix'));
			$installModel = $this->getModel('Install', 'InstallerModel');
			JFactory::getLanguage()->load('com_installer');
			if (!$installModel->install())
			{
                $app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_COULD_NOT_INSTALL'),'error');
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
	 * Saves a template source file.
	 */
	public function save()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
	
		$app     = JFactory::getApplication();
		$data    = $this->input->post->get('jform', array(), 'array');
		$task    = $this->getTask();
		$model   = $this->getModel();
        $fileName = $app->input->get('file');
	
		// Access check.
		if (!$this->allowSave())
		{
			$app->enqueueMessage(JText::_('JERROR_SAVE_NOT_PERMITTED'),'error');
            return false;
		}
	
		// Match the stored id's with the submitted.
		if (empty($data['extension_id']) || empty($data['filename']))
		{
			$app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_SOURCE_ID_FILENAME_MISMATCH'),'error');
            return false;
		}
		elseif ($data['extension_id'] != $model->getState('extension.id'))
		{
			$app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_SOURCE_ID_FILENAME_MISMATCH'),'error');
            return false;
		}
		elseif ($data['filename'] != base64_decode($fileName))
		{
			$app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_SOURCE_ID_FILENAME_MISMATCH'),'error');
            return false;
		}
	
		// Validate the posted data.
		$form	= $model->getForm();
		if (!$form)
		{
            $app->enqueueMessage($model->getError(),'error');
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
	
			// Redirect back to the edit screen.
            $url = 'index.php?option=com_templates&view=template&id=' . $model->getState('extension.id') . '&file=' . $fileName;
			$this->setRedirect(JRoute::_($url, false));
			return false;
		}
	
		// Attempt to save the data.
		if (!$model->save($data))
		{

			// Redirect back to the edit screen.
			$this->setMessage(JText::sprintf('JERROR_SAVE_FAILED', $model->getError()), 'warning');
            $url = 'index.php?option=com_templates&view=template&id=' . $model->getState('extension.id') . '&file=' . $fileName;
            $this->setRedirect(JRoute::_($url, false));
			return false;
		}

		$this->setMessage(JText::_('COM_TEMPLATES_FILE_SAVE_SUCCESS'));

		// Redirect the user based on the chosen task.
		switch ($task)
		{
		case 'apply':

			// Redirect back to the edit screen.
            $url = 'index.php?option=com_templates&view=template&id=' . $model->getState('extension.id') . '&file=' . $fileName;
            $this->setRedirect(JRoute::_($url, false));
			break;

        default:

            // Redirect to the list screen.
            $this->setRedirect(JRoute::_('index.php?option=com_templates&view=templates', false));
            break;
		}
	}

    public function overrides()
    {
        // Check for request forgeries.
        //JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        $app            = JFactory::getApplication();
        $model          = $this->getModel();
        $file           = $app->input->get('file');
        $override       = urldecode(base64_decode($app->input->get('folder')));
        $id             = $model->getState('extension.id');

        if($model->createOverride($override))
        {
            $this->setMessage(JText::_('Successfully created the override.'));
        }

        // Redirect back to the edit screen.
        $url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
        $this->setRedirect(JRoute::_($url, false));

    }
	
}
