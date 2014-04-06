<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Joomla.Media
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

/**
 * Base Upload Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       3.3
*/
class MediaControllerMediaUpload extends JControllerBase
{
	/**
	 * Application object - Redeclared for proper typehinting
	 *
	 * @var    JApplicationCms
	 * @since  3.3
	 */
	protected $app;

	/**
	 * Prefix for the view and model classes
	 *
	 * @var    string
	 * @since  3.3
	 */
	public $prefix = 'Media';

	/**
	 * Execute the controller.
	 *
	 * @return  mixed  A rendered view or true
	 *
	 * @since   3.3
	 */
	public function execute()
	{
		// Check for request forgeries
		if (!JSession::checkToken('request'))
		{
			$this->app->enqueueMessage(JText::_('JINVALID_TOKEN'));

			return false;
		}
		$params = JComponentHelper::getParams('com_media');

		$user  = JFactory::getUser();

		// Get some data from the request
		$files        = $this->input->files->get('Filedata', '', 'array');
		$return       = $this->input->post->get('return-url', null, 'base64');
		$this->folder = $this->input->get('folder', '', 'path');

		// Authorize the user
		if (!$user->authorise('core.create', 'com_media'))
		{
			// User is not authorised to create
			$this->app->enqueueMessage(JText::_('JLIB_APPLICATION_ERROR_CREATE_NOT_PERMITTED'));

			return false;
		}

		if (($params->get('upload_maxsize', 0) * 1024 * 1024) != 0)
		{
			if (
			$_SERVER['CONTENT_LENGTH'] > ($params->get('upload_maxsize', 0) * 1024 * 1024)
			|| $_SERVER['CONTENT_LENGTH'] > (int) (ini_get('upload_max_filesize')) * 1024 * 1024
			|| $_SERVER['CONTENT_LENGTH'] > (int) (ini_get('post_max_size')) * 1024 * 1024
			|| (($_SERVER['CONTENT_LENGTH'] > (int) (ini_get('memory_limit')) * 1024 * 1024) && ((int) (ini_get('memory_limit')) != -1))
			)
			{
				$this->app->enqueueMessage(JText::_('COM_MEDIA_ERROR_WARNFILETOOLARGE'), 'warning');

				return false;
			}
		}

		// Perform basic checks on file info before attempting anything
		foreach ($files as &$file)
		{
			$file['name']     = JFile::makeSafe($file['name']);
			$file['filepath'] = JPath::clean(implode(DIRECTORY_SEPARATOR, array(COM_MEDIA_BASE, $this->folder, $file['name'])));

			if ($file['error'] == 1)
			{
				$this->app->enqueueMessage(JText::_('COM_MEDIA_ERROR_WARNFILETOOLARGE'), 'warning');

				return false;
			}

			if (($params->get('upload_maxsize', 0) * 1024 * 1024) != 0 && $file['size'] > ($params->get('upload_maxsize', 0) * 1024 * 1024))
			{
				$this->app->enqueueMessage(JText::_('COM_MEDIA_ERROR_WARNFILETOOLARGE'), 'warning');

				return false;
			}

			if (JFile::exists($file['filepath']))
			{
				// A file with this name already exists
				$this->app->enqueueMessage(JText::_('COM_MEDIA_ERROR_FILE_EXISTS'), 'warning');

				return false;
			}

			if (!isset($file['name']))
			{
				// No filename (after the name was cleaned by JFile::makeSafe)
				$this->app->enqueueMessage(JText::_('COM_MEDIA_INVALID_REQUEST'), 'error');

				return false;

			}

			// Hash destination filename
			$fileparts = pathinfo($file['filepath']);
			$file['filepath'] = $fileparts['dirname'] . '\\' . md5($fileparts['filename']) . '.' . $fileparts['extension'];

		}

		// Set FTP credentials, if given
		JClientHelper::setCredentialsFromRequest('ftp');
		JPluginHelper::importPlugin('content');
		$dispatcher	= JEventDispatcher::getInstance();

		foreach ($files as &$file)
		{
			// The request is valid
			$err = null;

			if (!JHelperMedia::canUpload($file, 'com_media'))
			{
				// The file can't be uploaded

				return false;
			}

			// Trigger the onContentBeforeSave event.
			$object_file = new JObject($file);
			$result = $dispatcher->trigger('onContentBeforeSave', array('com_media.file', &$object_file, true));

			if (in_array(false, $result, true))
			{
				// There are some errors in the plugins
				$this->app->enqueueMessage(JText::plural('COM_MEDIA_ERROR_BEFORE_SAVE', count($errors = $object_file->getErrors()), implode('<br />', $errors)), 'warning');

				return false;
			}

			if (!JFile::upload($object_file->tmp_name, $object_file->filepath))
			{
				// Error in upload
				$this->app->enqueueMessage(JText::_('COM_MEDIA_ERROR_UNABLE_TO_UPLOAD_FILE'), 'warning');

				return false;
			}
			else
			{
				// add to table
				$model = new MediaModelMedia();
				$model->addMediaToTable($object_file->filepath);
				
				// Trigger the onContentAfterSave event.
				$dispatcher->trigger('onContentAfterSave', array('com_media.file', &$object_file, true));
				$this->app->enqueueMessage(JText::sprintf('COM_MEDIA_UPLOAD_COMPLETE', substr($object_file->filepath, strlen(COM_MEDIA_BASE))));
			}
		}

		// Set the redirect
		if ($return)
		{
			$this->app->redirect(JRoute::_(base64_decode($return) . '&folder=' . $this->folder, false));
		}

		return true;

	}
}
