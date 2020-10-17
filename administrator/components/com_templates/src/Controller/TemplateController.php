<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Templates\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Client\ClientHelper;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Input\Input;
use Joomla\Utilities\ArrayHelper;

/**
 * Template style controller class.
 *
 * @since  1.6
 */
class TemplateController extends BaseController
{
	/**
	 * Constructor.
	 *
	 * @param   array                $config   An optional associative array of configuration settings.
	 * @param   MVCFactoryInterface  $factory  The factory.
	 * @param   CMSApplication       $app      The JApplication for the dispatcher
	 * @param   Input                $input    Input
	 *
	 * @since  1.6
	 * @see    BaseController
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null)
	{
		parent::__construct($config, $factory, $app, $input);

		// Apply, Save & New, and Save As copy should be standard on forms.
		$this->registerTask('apply', 'save');
		$this->registerTask('unpublish', 'publish');
		$this->registerTask('publish',   'publish');
		$this->registerTask('deleteOverrideHistory', 'publish');
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
		$this->setRedirect(Route::_('index.php?option=com_templates&view=templates', false));
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
		$file = base64_encode('home');
		$id   = $this->input->get('id');
		$url  = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
		$this->setRedirect(Route::_($url, false));
	}

	/**
	 * Marked as Checked/Unchecked of override history.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function publish()
	{
		// Check for request forgeries.
		$this->checkToken();

		$file = $this->input->get('file');
		$id   = $this->input->get('id');

		$ids    = $this->input->get('cid', array(), 'array');
		$values = array('publish' => 1, 'unpublish' => 0, 'deleteOverrideHistory' => -3);
		$task   = $this->getTask();
		$value  = ArrayHelper::getValue($values, $task, 0, 'int');

		if (empty($ids))
		{
			$this->setMessage(Text::_('COM_TEMPLATES_ERROR_NO_FILE_SELECTED'), 'warning');
		}
		else
		{
			/* @var \Joomla\Component\Templates\Administrator\Model\TemplateModel $model */
			$model = $this->getModel();

			// Change the state of the records.
			if (!$model->publish($ids, $value, $id))
			{
				$this->setMessage(implode('<br>', $model->getErrors()), 'warning');
			}
			else
			{
				if ($value === 1)
				{
					$ntext = 'COM_TEMPLATES_N_OVERRIDE_CHECKED';
				}
				elseif ($value === 0)
				{
					$ntext = 'COM_TEMPLATES_N_OVERRIDE_UNCHECKED';
				}
				elseif ($value === -3)
				{
					$ntext = 'COM_TEMPLATES_N_OVERRIDE_DELETED';
				}

				$this->setMessage(Text::plural($ntext, count($ids)));
			}
		}

		$url  = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
		$this->setRedirect(Route::_($url, false));
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

		$app = $this->app;
		$this->input->set('installtype', 'folder');
		$newName    = $this->input->get('new_name');
		$newNameRaw = $this->input->get('new_name', null, 'string');
		$templateID = $this->input->getInt('id', 0);
		$file       = $this->input->get('file');

		// Access check.
		if (!$this->allowEdit())
		{
			$app->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'), 'error');

			return false;
		}

		$this->setRedirect('index.php?option=com_templates&view=template&id=' . $templateID . '&file=' . $file);

		/* @var \Joomla\Component\Templates\Administrator\Model\TemplateModel $model */
		$model = $this->getModel('Template', 'Administrator');
		$model->setState('new_name', $newName);
		$model->setState('tmp_prefix', uniqid('template_copy_'));
		$model->setState('to_path', $app->get('tmp_path') . '/' . $model->getState('tmp_prefix'));

		// Process only if we have a new name entered
		if (strlen($newName) > 0)
		{
			if (!$this->app->getIdentity()->authorise('core.create', 'com_templates'))
			{
				// User is not authorised to delete
				$this->setMessage(Text::_('COM_TEMPLATES_ERROR_CREATE_NOT_PERMITTED'), 'error');

				return false;
			}

			// Set FTP credentials, if given
			ClientHelper::setCredentialsFromRequest('ftp');

			// Check that new name is valid
			if (($newNameRaw !== null) && ($newName !== $newNameRaw))
			{
				$this->setMessage(Text::_('COM_TEMPLATES_ERROR_INVALID_TEMPLATE_NAME'), 'error');

				return false;
			}

			// Check that new name doesn't already exist
			if (!$model->checkNewName())
			{
				$this->setMessage(Text::_('COM_TEMPLATES_ERROR_DUPLICATE_TEMPLATE_NAME'), 'error');

				return false;
			}

			// Check that from name does exist and get the folder name
			$fromName = $model->getFromName();

			if (!$fromName)
			{
				$this->setMessage(Text::_('COM_TEMPLATES_ERROR_INVALID_FROM_NAME'), 'error');

				return false;
			}

			// Call model's copy method
			if (!$model->copy())
			{
				$this->setMessage(Text::_('COM_TEMPLATES_ERROR_COULD_NOT_COPY'), 'error');

				return false;
			}

			// Call installation model
			$this->input->set('install_directory', $app->get('tmp_path') . '/' . $model->getState('tmp_prefix'));

			/** @var \Joomla\Component\Installer\Administrator\Model\InstallModel $installModel */
			$installModel = $this->app->bootComponent('com_installer')
				->getMVCFactory()->createModel('Install', 'Administrator');
			$this->app->getLanguage()->load('com_installer');

			if (!$installModel->install())
			{
				$this->setMessage(Text::_('COM_TEMPLATES_ERROR_COULD_NOT_INSTALL'), 'error');

				return false;
			}

			$this->setMessage(Text::sprintf('COM_TEMPLATES_COPY_SUCCESS', $newName));
			$model->cleanup();

			return true;
		}

		return false;
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional (note, the empty array is atypical compared to other models).
	 *
	 * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel  The model.
	 *
	 * @since   3.2
	 */
	public function getModel($name = 'Template', $prefix = 'Administrator', $config = array())
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Method to check if you can add a new record.
	 *
	 * @return  boolean
	 *
	 * @since   3.2
	 */
	protected function allowEdit()
	{
		return $this->app->getIdentity()->authorise('core.admin');
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

		$data         = $this->input->post->get('jform', array(), 'array');
		$task         = $this->getTask();

		/** @var \Joomla\Component\Templates\Administrator\Model\TemplateModel $model */
		$model        = $this->getModel();
		$fileName     = $this->input->get('file');
		$explodeArray = explode(':', base64_decode($fileName));

		// Access check.
		if (!$this->allowEdit())
		{
			$this->setMessage(Text::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'), 'error');

			return;
		}

		// Match the stored id's with the submitted.
		if (empty($data['extension_id']) || empty($data['filename']))
		{
			$this->setMessage(Text::_('COM_TEMPLATES_ERROR_SOURCE_ID_FILENAME_MISMATCH'), 'error');

			return;
		}
		elseif ($data['extension_id'] != $model->getState('extension.id'))
		{
			$this->setMessage(Text::_('COM_TEMPLATES_ERROR_SOURCE_ID_FILENAME_MISMATCH'), 'error');

			return;
		}
		elseif (Path::clean($data['filename'], '/') != end($explodeArray))
		{
			$this->setMessage(Text::_('COM_TEMPLATES_ERROR_SOURCE_ID_FILENAME_MISMATCH'), 'error');

			return;
		}

		// Validate the posted data.
		$form = $model->getForm();

		if (!$form)
		{
			$this->setMessage($model->getError(), 'error');

			return;
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
				if ($errors[$i] instanceof \Exception)
				{
					$this->app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else
				{
					$this->app->enqueueMessage($errors[$i], 'warning');
				}
			}

			// Redirect back to the edit screen.
			$url = 'index.php?option=com_templates&view=template&id=' . $model->getState('extension.id') . '&file=' . $fileName;
			$this->setRedirect(Route::_($url, false));

			return;
		}

		// Attempt to save the data.
		if (!$model->save($data))
		{
			// Redirect back to the edit screen.
			$this->setMessage(Text::sprintf('JERROR_SAVE_FAILED', $model->getError()), 'warning');
			$url = 'index.php?option=com_templates&view=template&id=' . $model->getState('extension.id') . '&file=' . $fileName;
			$this->setRedirect(Route::_($url, false));

			return;
		}

		$this->setMessage(Text::_('COM_TEMPLATES_FILE_SAVE_SUCCESS'));

		// Redirect the user based on the chosen task.
		switch ($task)
		{
			case 'apply':
				// Redirect back to the edit screen.
				$url = 'index.php?option=com_templates&view=template&id=' . $model->getState('extension.id') . '&file=' . $fileName;
				$this->setRedirect(Route::_($url, false));
				break;

			default:
				// Redirect to the list screen.
				$file = base64_encode('home');
				$id   = $this->input->get('id');
				$url  = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
				$this->setRedirect(Route::_($url, false));
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

		/* @var \Joomla\Component\Templates\Administrator\Model\TemplateModel $model */
		$model    = $this->getModel();
		$file     = $this->input->get('file');
		$override = base64_decode($this->input->get('folder'));
		$id       = $this->input->get('id');

		// Access check.
		if (!$this->allowEdit())
		{
			$this->app->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'), 'error');

			return;
		}

		if ($model->createOverride($override))
		{
			$this->setMessage(Text::_('COM_TEMPLATES_OVERRIDE_SUCCESS'));
		}

		// Redirect back to the edit screen.
		$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
		$this->setRedirect(Route::_($url, false));
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

		/* @var \Joomla\Component\Templates\Administrator\Model\TemplateModel $model */
		$model = $this->getModel();
		$id    = $this->input->get('id');
		$file  = $this->input->get('file');

		// Access check.
		if (!$this->allowEdit())
		{
			$this->app->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'), 'error');

			return;
		}

		if (base64_decode(urldecode($file)) == '/index.php')
		{
			$this->setMessage(Text::_('COM_TEMPLATES_ERROR_INDEX_DELETE'), 'warning');
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(Route::_($url, false));
		}
		elseif ($model->deleteFile($file))
		{
			$this->setMessage(Text::_('COM_TEMPLATES_FILE_DELETE_SUCCESS'));
			$file = base64_encode('home');
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(Route::_($url, false));
		}
		else
		{
			$this->setMessage(Text::_('COM_TEMPLATES_ERROR_FILE_DELETE'), 'error');
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(Route::_($url, false));
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

		/* @var \Joomla\Component\Templates\Administrator\Model\TemplateModel $model */
		$model    = $this->getModel();
		$id       = $this->input->get('id');
		$file     = $this->input->get('file');
		$name     = $this->input->get('name');
		$location = base64_decode($this->input->get('address'));
		$type     = $this->input->get('type');

		// Access check.
		if (!$this->allowEdit())
		{
			$this->app->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'), 'error');

			return;
		}

		if ($type == 'null')
		{
			$this->setMessage(Text::_('COM_TEMPLATES_INVALID_FILE_TYPE'), 'error');
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(Route::_($url, false));
		}
		elseif (!preg_match('/^[a-zA-Z0-9-_]+$/', $name))
		{
			$this->setMessage(Text::_('COM_TEMPLATES_INVALID_FILE_NAME'), 'error');
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(Route::_($url, false));
		}
		elseif ($model->createFile($name, $type, $location))
		{
			$this->setMessage(Text::_('COM_TEMPLATES_FILE_CREATE_SUCCESS'));
			$file = urlencode(base64_encode($location . '/' . $name . '.' . $type));
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(Route::_($url, false));
		}
		else
		{
			$this->setMessage(Text::_('COM_TEMPLATES_ERROR_FILE_CREATE'), 'error');
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(Route::_($url, false));
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

		/* @var \Joomla\Component\Templates\Administrator\Model\TemplateModel $model */
		$model    = $this->getModel();
		$id       = $this->input->get('id');
		$file     = $this->input->get('file');
		$upload   = $this->input->files->get('files');
		$location = base64_decode($this->input->get('address'));

		// Access check.
		if (!$this->allowEdit())
		{
			$this->app->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'), 'error');

			return;
		}

		if ($return = $model->uploadFile($upload, $location))
		{
			$this->setMessage(Text::_('COM_TEMPLATES_FILE_UPLOAD_SUCCESS') . $upload['name']);
			$redirect = base64_encode($return);
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $redirect;
			$this->setRedirect(Route::_($url, false));
		}
		else
		{
			$this->setMessage(Text::_('COM_TEMPLATES_ERROR_FILE_UPLOAD'), 'error');
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(Route::_($url, false));
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

		/** @var \Joomla\Component\Templates\Administrator\Model\TemplateModel $model */
		$model    = $this->getModel();
		$id       = $this->input->get('id');
		$file     = $this->input->get('file');
		$name     = $this->input->get('name');
		$location = base64_decode($this->input->get('address'));

		// Access check.
		if (!$this->allowEdit())
		{
			$this->app->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'), 'error');

			return;
		}

		if (!preg_match('/^[a-zA-Z0-9-_.]+$/', $name))
		{
			$this->setMessage(Text::_('COM_TEMPLATES_INVALID_FOLDER_NAME'), 'error');
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(Route::_($url, false));
		}
		elseif ($model->createFolder($name, $location))
		{
			$this->setMessage(Text::_('COM_TEMPLATES_FOLDER_CREATE_SUCCESS'));
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(Route::_($url, false));
		}
		else
		{
			$this->setMessage(Text::_('COM_TEMPLATES_ERROR_FOLDER_CREATE'), 'error');
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(Route::_($url, false));
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

		/** @var \Joomla\Component\Templates\Administrator\Model\TemplateModel $model */
		$model    = $this->getModel();
		$id       = $this->input->get('id');
		$file     = $this->input->get('file');
		$location = base64_decode($this->input->get('address'));

		// Access check.
		if (!$this->allowEdit())
		{
			$this->app->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'), 'error');

			return;
		}

		if (empty($location))
		{
			$this->setMessage(Text::_('COM_TEMPLATES_ERROR_ROOT_DELETE'), 'warning');
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(Route::_($url, false));
		}
		elseif ($model->deleteFolder($location))
		{
			$this->setMessage(Text::_('COM_TEMPLATES_FOLDER_DELETE_SUCCESS'));

			if (stristr(base64_decode($file), $location) != false)
			{
				$file = base64_encode('home');
			}

			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(Route::_($url, false));
		}
		else
		{
			$this->setMessage(Text::_('COM_TEMPLATES_FOLDER_DELETE_ERROR'), 'error');
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(Route::_($url, false));
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

		/** @var \Joomla\Component\Templates\Administrator\Model\TemplateModel $model */
		$model   = $this->getModel();
		$id      = $this->input->get('id');
		$file    = $this->input->get('file');
		$newName = $this->input->get('new_name');

		// Access check.
		if (!$this->allowEdit())
		{
			$this->app->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'), 'error');

			return;
		}

		if (base64_decode(urldecode($file)) == '/index.php')
		{
			$this->setMessage(Text::_('COM_TEMPLATES_ERROR_RENAME_INDEX'), 'warning');
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(Route::_($url, false));
		}
		elseif (!preg_match('/^[a-zA-Z0-9-_]+$/', $newName))
		{
			$this->setMessage(Text::_('COM_TEMPLATES_INVALID_FILE_NAME'), 'error');
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(Route::_($url, false));
		}
		elseif ($rename = $model->renameFile($file, $newName))
		{
			$this->setMessage(Text::_('COM_TEMPLATES_FILE_RENAME_SUCCESS'));
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $rename;
			$this->setRedirect(Route::_($url, false));
		}
		else
		{
			$this->setMessage(Text::_('COM_TEMPLATES_ERROR_FILE_RENAME'), 'error');
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(Route::_($url, false));
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

		$id    = $this->input->get('id');
		$file  = $this->input->get('file');
		$x     = $this->input->get('x');
		$y     = $this->input->get('y');
		$w     = $this->input->get('w');
		$h     = $this->input->get('h');

		/** @var \Joomla\Component\Templates\Administrator\Model\TemplateModel $model */
		$model = $this->getModel();

		// Access check.
		if (!$this->allowEdit())
		{
			$this->app->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'), 'error');

			return;
		}

		if (empty($w) && empty($h) && empty($x) && empty($y))
		{
			$this->setMessage(Text::_('COM_TEMPLATES_CROP_AREA_ERROR'), 'error');
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(Route::_($url, false));
		}
		elseif ($model->cropImage($file, $w, $h, $x, $y))
		{
			$this->setMessage(Text::_('COM_TEMPLATES_FILE_CROP_SUCCESS'));
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(Route::_($url, false));
		}
		else
		{
			$this->setMessage(Text::_('COM_TEMPLATES_FILE_CROP_ERROR'), 'error');
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(Route::_($url, false));
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

		$id     = $this->input->get('id');
		$file   = $this->input->get('file');
		$width  = $this->input->get('width');
		$height = $this->input->get('height');

		/** @var \Joomla\Component\Templates\Administrator\Model\TemplateModel $model */
		$model  = $this->getModel();

		// Access check.
		if (!$this->allowEdit())
		{
			$this->app->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'), 'error');

			return;
		}

		if ($model->resizeImage($file, $width, $height))
		{
			$this->setMessage(Text::_('COM_TEMPLATES_FILE_RESIZE_SUCCESS'));
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(Route::_($url, false));
		}
		else
		{
			$this->setMessage(Text::_('COM_TEMPLATES_FILE_RESIZE_ERROR'), 'error');
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(Route::_($url, false));
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

		$id       = $this->input->get('id');
		$file     = $this->input->get('file');
		$newName  = $this->input->get('new_name');
		$location = base64_decode($this->input->get('address'));

		/** @var \Joomla\Component\Templates\Administrator\Model\TemplateModel $model */
		$model    = $this->getModel();

		// Access check.
		if (!$this->allowEdit())
		{
			$this->app->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'), 'error');

			return;
		}

		if (!preg_match('/^[a-zA-Z0-9-_]+$/', $newName))
		{
			$this->setMessage(Text::_('COM_TEMPLATES_INVALID_FILE_NAME'), 'error');
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(Route::_($url, false));
		}
		elseif ($model->copyFile($newName, $location, $file))
		{
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(Route::_($url, false));
		}
		else
		{
			$this->setMessage(Text::_('COM_TEMPLATES_FILE_COPY_FAIL'), 'error');
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(Route::_($url, false));
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

		$id    = $this->input->get('id');
		$file  = $this->input->get('file');

		/** @var \Joomla\Component\Templates\Administrator\Model\TemplateModel $model */
		$model = $this->getModel();

		// Access check.
		if (!$this->allowEdit())
		{
			$this->app->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'), 'error');

			return;
		}

		if ($model->extractArchive($file))
		{
			$this->setMessage(Text::_('COM_TEMPLATES_FILE_ARCHIVE_EXTRACT_SUCCESS'));
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(Route::_($url, false));
		}
		else
		{
			$this->setMessage(Text::_('COM_TEMPLATES_FILE_ARCHIVE_EXTRACT_FAIL'), 'error');
			$url = 'index.php?option=com_templates&view=template&id=' . $id . '&file=' . $file;
			$this->setRedirect(Route::_($url, false));
		}
	}

	/**
	 * Fetch and report updates in \JSON format, for AJAX requests
	 *
	 * @return void
	 *
	 * @since 4.0.0
	 */
	public function ajax()
	{
		$app = $this->app;

		if (!Session::checkToken('get'))
		{
			$app->setHeader('status', 403, true);
			$app->sendHeaders();
			echo Text::_('JINVALID_TOKEN_NOTICE');
			$app->close();
		}

		// Checks status of installer override plugin.
		if (!PluginHelper::isEnabled('installer', 'override'))
		{
			$error = array('installerOverride' => 'disabled');

			echo json_encode($error);

			$app->close();
		}

		/** @var \Joomla\Component\Templates\Administrator\Model\TemplateModel $model */
		$model = $this->getModel();

		$result = $model->getUpdatedList(true, true);

		echo json_encode($result);

		$app->close();
	}
}
