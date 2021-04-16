<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('InstallerModelInstall', JPATH_ADMINISTRATOR . '/components/com_installer/models/install.php');

use Joomla\CMS\Filter\InputFilter;

/**
 * Template style controller class.
 *
 * @since  1.6
 */
class TemplatesControllerTemplate extends JControllerLegacy
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JControllerLegacy
	 * @since   3.2
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		// Apply, Save & New, and Save As copy should be standard on forms.
		$this->registerTask('apply', 'save');
	}

	/**
	 * Method for closing the template.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function cancel()
	{
		$this->setRedirect(JRoute::_('index.php?option=com_templates&view=templates', false));
	}

	/**
	 * Method for closing a file.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function close()
	{
		$app  = JFactory::getApplication();
		$file = base64_encode('home');
		$id   = (int) $app->input->get('id', 0, 'int');
		$url  = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
		$this->setRedirect(JRoute::_($url, false));
	}

	/**
	 * Method for copying the template.
	 *
	 * @return  boolean     true on success, false otherwise
	 *
	 * @since   3.2
	 */
	public function copy()
	{
		// Check for request forgeries
		$this->checkToken();

		$app = JFactory::getApplication();
		$this->input->set('installtype', 'folder');
		$newName    = (string) $this->input->get('new_name', null, 'cmd');
		$newNameRaw = (string) $this->input->get('new_name', null, 'string');
		$templateID = (int) $this->input->get('id', 0, 'int');
		$file       = (string) $this->input->get('file', '', 'cmd');

		// Access check.
		if (!$this->allowEdit())
		{
			$app->enqueueMessage(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'), 'error');

			return false;
		}

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
				$app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_CREATE_NOT_PERMITTED'), 'error');

				return false;
			}

			// Set FTP credentials, if given
			JClientHelper::setCredentialsFromRequest('ftp');

			// Check that new name is valid
			if (($newNameRaw !== null) && ($newName !== $newNameRaw))
			{
				$app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_INVALID_TEMPLATE_NAME'), 'error');

				return false;
			}

			// Check that new name doesn't already exist
			if (!$model->checkNewName())
			{
				$app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_DUPLICATE_TEMPLATE_NAME'), 'error');

				return false;
			}

			// Check that from name does exist and get the folder name
			$fromName = $model->getFromName();

			if (!$fromName)
			{
				$app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_INVALID_FROM_NAME'), 'error');

				return false;
			}

			// Call model's copy method
			if (!$model->copy())
			{
				$app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_COULD_NOT_COPY'), 'error');

				return false;
			}

			// Call installation model
			$this->input->set('install_directory', JFactory::getConfig()->get('tmp_path') . '/' . $model->getState('tmp_prefix'));
			$installModel = $this->getModel('Install', 'InstallerModel');
			JFactory::getLanguage()->load('com_installer');

			if (!$installModel->install())
			{
				$app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_COULD_NOT_INSTALL'), 'error');

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
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional (note, the empty array is atypical compared to other models).
	 *
	 * @return  JModelLegacy  The model.
	 *
	 * @since   3.2
	 */
	public function getModel($name = 'Template', $prefix = 'TemplatesModel', $config = array())
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Method to check if the user can modify template files
	 *
	 * @return  boolean
	 *
	 * @since   3.2
	 */
	protected function allowEdit()
	{
		return JFactory::getUser()->authorise('core.admin');
	}

	/**
	 * Saves a template source file.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function save()
	{
		// Check for request forgeries.
		$this->checkToken();

		$app          = JFactory::getApplication();
		$data         = $this->input->post->get('jform', array(), 'array');
		$task         = $this->getTask();
		$model        = $this->getModel();
		$fileName     = (string) $app->input->get('file', '', 'cmd');
		$explodeArray = explode(':', base64_decode($fileName));

		// Access check.
		if (!$this->allowEdit())
		{
			$app->enqueueMessage(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'), 'error');

			return false;
		}

		// Match the stored id's with the submitted.
		if (empty($data['extension_id']) || empty($data['filename']))
		{
			$app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_SOURCE_ID_FILENAME_MISMATCH'), 'error');

			return false;
		}
		elseif ($data['extension_id'] != $model->getState('extension.id'))
		{
			$app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_SOURCE_ID_FILENAME_MISMATCH'), 'error');

			return false;
		}
		elseif ($data['filename'] != end($explodeArray))
		{
			$app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_SOURCE_ID_FILENAME_MISMATCH'), 'error');

			return false;
		}

		// Validate the posted data.
		$form = $model->getForm();

		if (!$form)
		{
			$app->enqueueMessage($model->getError(), 'error');

			return false;
		}

		$data = $model->validate($form, $data);

		// Check for validation errors.
		if ($data === false)
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
				$file = base64_encode('home');
				$id   = (int) $app->input->get('id', 0, 'int');
				$url  = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
				$this->setRedirect(JRoute::_($url, false));
				break;
		}
	}

	/**
	 * Method for creating override.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function overrides()
	{
		// Check for request forgeries.
		$this->checkToken('get');

		$app      = JFactory::getApplication();
		$model    = $this->getModel();
		$file     = (string) $app->input->get('file', '', 'cmd');
		$override = (string) InputFilter::getInstance(array(), array(), 1, 1)->clean(base64_decode($app->input->get('folder', '', 'base64')), 'path');
		$id       = (int) $app->input->get('id', 0, 'int');

		// Access check.
		if (!$this->allowEdit())
		{
			$app->enqueueMessage(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'), 'error');

			return false;
		}


		if ($model->createOverride($override))
		{
			$this->setMessage(JText::_('COM_TEMPLATES_OVERRIDE_SUCCESS'));
		}

		// Redirect back to the edit screen.
		$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
		$this->setRedirect(JRoute::_($url, false));
	}

	/**
	 * Method for compiling LESS.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function less()
	{
		// Check for request forgeries
		$this->checkToken();

		$app   = JFactory::getApplication();
		$model = $this->getModel();
		$id    = (int) $app->input->get('id', 0, 'int');
		$file  = (string) $app->input->get('file', '', 'cmd');

		// Access check.
		if (!$this->allowEdit())
		{
			$app->enqueueMessage(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'), 'error');

			return false;
		}

		if ($model->compileLess($file))
		{
			$this->setMessage(JText::_('COM_TEMPLATES_COMPILE_SUCCESS'));
		}
		else
		{
			$app->enqueueMessage(JText::_('COM_TEMPLATES_COMPILE_ERROR'), 'error');
		}

		$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
		$this->setRedirect(JRoute::_($url, false));
	}

	/**
	 * Method for deleting a file.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function delete()
	{
		// Check for request forgeries
		$this->checkToken();

		$app   = JFactory::getApplication();
		$model = $this->getModel();
		$id    = (int) $app->input->get('id', 0, 'int');
		$file  = (string) $app->input->get('file', '', 'cmd');

		// Access check.
		if (!$this->allowEdit())
		{
			$app->enqueueMessage(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'), 'error');

			return false;
		}

		if (base64_decode(urldecode($file)) == '/index.php')
		{
			$app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_INDEX_DELETE'), 'warning');
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(JRoute::_($url, false));
		}

		elseif ($model->deleteFile($file))
		{
			$this->setMessage(JText::_('COM_TEMPLATES_FILE_DELETE_SUCCESS'));
			$file = base64_encode('home');
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(JRoute::_($url, false));
		}
		else
		{
			$app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_FILE_DELETE'), 'error');
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(JRoute::_($url, false));
		}
	}

	/**
	 * Method for creating a new file.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function createFile()
	{
		// Check for request forgeries
		$this->checkToken();

		$app      = JFactory::getApplication();
		$model    = $this->getModel();
		$id       = (int) $app->input->get('id', 0, 'int');
		$file     = (string) $app->input->get('file', '', 'cmd');
		$name     = (string) $app->input->get('name', '', 'cmd');
		$location = (string) InputFilter::getinstance(array(), array(), 1, 1)->clean(base64_decode($app->input->get('address', '', 'base64')), 'path');
		$type     = (string) $app->input->get('type', '', 'cmd');

		// Access check.
		if (!$this->allowEdit())
		{
			$app->enqueueMessage(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'), 'error');

			return false;
		}

		if ($type == 'null')
		{
			$app->enqueueMessage(JText::_('COM_TEMPLATES_INVALID_FILE_TYPE'), 'error');
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(JRoute::_($url, false));
		}
		elseif (!preg_match('/^[a-zA-Z0-9-_]+$/', $name))
		{
			$app->enqueueMessage(JText::_('COM_TEMPLATES_INVALID_FILE_NAME'), 'error');
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(JRoute::_($url, false));
		}
		elseif ($model->createFile($name, $type, $location))
		{
			$this->setMessage(JText::_('COM_TEMPLATES_FILE_CREATE_SUCCESS'));
			$file = urlencode(base64_encode($location . '/' . $name . '.' . $type));
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(JRoute::_($url, false));
		}
		else
		{
			$app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_FILE_CREATE'), 'error');
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(JRoute::_($url, false));
		}
	}

	/**
	 * Method for uploading a file.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function uploadFile()
	{
		// Check for request forgeries
		$this->checkToken();

		$app      = JFactory::getApplication();
		$model    = $this->getModel();
		$id       = (int) $app->input->get('id', 0, 'int');
		$file     = (string) $app->input->get('file', '', 'cmd');
		$upload   = $app->input->files->get('files');
		$location = (string) InputFilter::getinstance(array(), array(), 1, 1)->clean(base64_decode($app->input->get('address', '', 'base64')), 'path');

		// Access check.
		if (!$this->allowEdit())
		{
			$app->enqueueMessage(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'), 'error');

			return false;
		}

		if ($return = $model->uploadFile($upload, $location))
		{
			$app->enqueueMessage(JText::_('COM_TEMPLATES_FILE_UPLOAD_SUCCESS') . $upload['name']);
			$redirect = base64_encode($return);
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $redirect;
			$this->setRedirect(JRoute::_($url, false));
		}
		else
		{
			$app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_FILE_UPLOAD'), 'error');
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(JRoute::_($url, false));
		}
	}

	/**
	 * Method for creating a new folder.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function createFolder()
	{
		// Check for request forgeries
		$this->checkToken();

		$app      = JFactory::getApplication();
		$model    = $this->getModel();
		$id       = (int) $app->input->get('id', 0, 'int');
		$file     = (string) $app->input->get('file', '', 'cmd');
		$name     = $app->input->get('name');
		$location = (string) InputFilter::getinstance(array(), array(), 1, 1)->clean(base64_decode($app->input->get('address', '', 'base64')), 'path');

		// Access check.
		if (!$this->allowEdit())
		{
			$app->enqueueMessage(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'), 'error');

			return false;
		}

		if (!preg_match('/^[a-zA-Z0-9-_.]+$/', $name))
		{
			$app->enqueueMessage(JText::_('COM_TEMPLATES_INVALID_FOLDER_NAME'), 'error');
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(JRoute::_($url, false));
		}
		elseif ($model->createFolder($name, $location))
		{
			$this->setMessage(JText::_('COM_TEMPLATES_FOLDER_CREATE_SUCCESS'));
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(JRoute::_($url, false));
		}
		else
		{
			$app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_FOLDER_CREATE'), 'error');
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(JRoute::_($url, false));
		}
	}

	/**
	 * Method for deleting a folder.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function deleteFolder()
	{
		// Check for request forgeries
		$this->checkToken();

		$app      = JFactory::getApplication();
		$model    = $this->getModel();
		$id       = (int) $app->input->get('id', 0, 'int');
		$file     = (string) $app->input->get('file', '', 'cmd');
		$location = (string) InputFilter::getinstance(array(), array(), 1, 1)->clean(base64_decode($app->input->get('address', '', 'base64')), 'path');

		// Access check.
		if (!$this->allowEdit())
		{
			$app->enqueueMessage(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'), 'error');

			return false;
		}

		if (empty($location))
		{
			$app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_ROOT_DELETE'), 'warning');
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(JRoute::_($url, false));
		}
		elseif ($model->deleteFolder($location))
		{
			$this->setMessage(JText::_('COM_TEMPLATES_FOLDER_DELETE_SUCCESS'));

			if (stristr(base64_decode($file), $location) != false)
			{
				$file = base64_encode('home');
			}

			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(JRoute::_($url, false));
		}
		else
		{
			$app->enqueueMessage(JText::_('COM_TEMPLATES_FOLDER_DELETE_ERROR'), 'error');
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(JRoute::_($url, false));
		}
	}

	/**
	 * Method for renaming a file.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function renameFile()
	{
		// Check for request forgeries
		$this->checkToken();

		$app     = JFactory::getApplication();
		$model   = $this->getModel();
		$id      = (int) $app->input->get('id', 0, 'int');
		$file    = (string) $app->input->get('file', '', 'cmd');
		$newName = $app->input->get('new_name');

		// Access check.
		if (!$this->allowEdit())
		{
			$app->enqueueMessage(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'), 'error');

			return false;
		}

		if (base64_decode(urldecode($file)) == '/index.php')
		{
			$app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_RENAME_INDEX'), 'warning');
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(JRoute::_($url, false));
		}
		elseif (!preg_match('/^[a-zA-Z0-9-_]+$/', $newName))
		{
			$app->enqueueMessage(JText::_('COM_TEMPLATES_INVALID_FILE_NAME'), 'error');
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(JRoute::_($url, false));
		}
		elseif ($rename = $model->renameFile($file, $newName))
		{
			$this->setMessage(JText::_('COM_TEMPLATES_FILE_RENAME_SUCCESS'));
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $rename;
			$this->setRedirect(JRoute::_($url, false));
		}
		else
		{
			$app->enqueueMessage(JText::_('COM_TEMPLATES_ERROR_FILE_RENAME'), 'error');
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(JRoute::_($url, false));
		}
	}

	/**
	 * Method for cropping an image.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function cropImage()
	{
		// Check for request forgeries
		$this->checkToken();

		$app   = JFactory::getApplication();
		$id    = (int) $app->input->get('id', 0, 'int');
		$file  = (string) $app->input->get('file', '', 'cmd');
		$x     = $app->input->get('x');
		$y     = $app->input->get('y');
		$w     = $app->input->get('w');
		$h     = $app->input->get('h');
		$model = $this->getModel();

		// Access check.
		if (!$this->allowEdit())
		{
			$app->enqueueMessage(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'), 'error');

			return false;
		}

		if (empty($w) && empty($h) && empty($x) && empty($y))
		{
			$app->enqueueMessage(JText::_('COM_TEMPLATES_CROP_AREA_ERROR'), 'error');
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(JRoute::_($url, false));
		}
		elseif ($model->cropImage($file, $w, $h, $x, $y))
		{
			$app->enqueueMessage(JText::_('COM_TEMPLATES_FILE_CROP_SUCCESS'));
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(JRoute::_($url, false));
		}
		else
		{
			$app->enqueueMessage(JText::_('COM_TEMPLATES_FILE_CROP_ERROR'), 'error');
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(JRoute::_($url, false));
		}
	}

	/**
	 * Method for resizing an image.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function resizeImage()
	{
		// Check for request forgeries
		$this->checkToken();

		$app    = JFactory::getApplication();
		$id     = (int) $app->input->get('id', 0, 'int');
		$file   = (string) $app->input->get('file', '', 'cmd');
		$width  = $app->input->get('width');
		$height = $app->input->get('height');
		$model  = $this->getModel();

		// Access check.
		if (!$this->allowEdit())
		{
			$app->enqueueMessage(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'), 'error');

			return false;
		}

		if ($model->resizeImage($file, $width, $height))
		{
			$app->enqueueMessage(JText::_('COM_TEMPLATES_FILE_RESIZE_SUCCESS'));
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(JRoute::_($url, false));
		}
		else
		{
			$app->enqueueMessage(JText::_('COM_TEMPLATES_FILE_RESIZE_ERROR'), 'error');
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(JRoute::_($url, false));
		}
	}

	/**
	 * Method for copying a file.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function copyFile()
	{
		// Check for request forgeries
		$this->checkToken();

		$app      = JFactory::getApplication();
		$id       = (int) $app->input->get('id', 0, 'int');
		$file     = (string) $app->input->get('file', '', 'cmd');
		$newName  = $app->input->get('new_name');
		$location = (string) InputFilter::getinstance(array(), array(), 1, 1)->clean(base64_decode($app->input->get('address', '', 'base64')), 'path');
		$model    = $this->getModel();

		// Access check.
		if (!$this->allowEdit())
		{
			$app->enqueueMessage(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'), 'error');

			return false;
		}

		if (!preg_match('/^[a-zA-Z0-9-_]+$/', $newName))
		{
			$app->enqueueMessage(JText::_('COM_TEMPLATES_INVALID_FILE_NAME'), 'error');
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(JRoute::_($url, false));
		}
		elseif ($model->copyFile($newName, $location, $file))
		{
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(JRoute::_($url, false));
		}
		else
		{
			$app->enqueueMessage(JText::_('COM_TEMPLATES_FILE_COPY_FAIL'), 'error');
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(JRoute::_($url, false));
		}
	}

	/**
	 * Method for extracting an archive file.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function extractArchive()
	{
		// Check for request forgeries
		$this->checkToken();

		$app   = JFactory::getApplication();
		$id    = (int) $app->input->get('id', 0, 'int');
		$file  = (string) $app->input->get('file', '', 'cmd');
		$model = $this->getModel();

		// Access check.
		if (!$this->allowEdit())
		{
			$app->enqueueMessage(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'), 'error');

			return false;
		}

		if ($model->extractArchive($file))
		{
			$app->enqueueMessage(JText::_('COM_TEMPLATES_FILE_ARCHIVE_EXTRACT_SUCCESS'));
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(JRoute::_($url, false));
		}
		else
		{
			$app->enqueueMessage(JText::_('COM_TEMPLATES_FILE_ARCHIVE_EXTRACT_FAIL'), 'error');
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(JRoute::_($url, false));
		}
	}
}
