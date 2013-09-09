<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

/**
 * This file handles file uploads.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       3.2
 */
class MediaControllerUpload extends JControllerBase
{
	public $media;

	/**
	 * Implement method in interface JControllerBase
	 *
	 * @return  boolean        This object redirect to $redirect and $message
	 *
	 * @since   3.2
	 */
	public function execute()
	{
		// Check for request forgeries
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));
		$user = JFactory::getUser();

		if (!$user->authorise('core.create', 'com_media'))
		{
			jexit(JText::_('COM_MEDIA_ERROR_NO_PERMISSION'));

			return false;
		}

		define('DS', DIRECTORY_SEPARATOR);

		$app = $this->getApplication();
		$model = new MediaModelSync;
		$input = $app->input;

		$params = JComponentHelper::getParams('com_media');
		$user = JFactory::getUser();

		// Get some data from the request
		$files = $input->files->get('Filedata', '', 'array');
		$return = $input->post->get('return-url', null, 'base64');
		$this->folder = $input->get('folder', '', 'path');
		$redirect = '';
		$message = '';

		// Set the redirect
		if ($return)
		{
			$redirect = base64_decode($return) . '&folder=' . $this->folder;
		}

		if ($_SERVER['CONTENT_LENGTH'] > ($params->get('upload_maxsize', 0) * 1024 * 1024)
			|| $_SERVER['CONTENT_LENGTH'] > (int) (ini_get('upload_max_filesize')) * 1024 * 1024
			|| $_SERVER['CONTENT_LENGTH'] > (int) (ini_get('post_max_size')) * 1024 * 1024
			|| (($_SERVER['CONTENT_LENGTH'] > (int) (ini_get('memory_limit')) * 1024 * 1024) && ((int) (ini_get('memory_limit')) != -1)))

		{
			JError::raiseWarning(100, JText::_('COM_MEDIA_ERROR_WARNFILETOOLARGE'));
			$app->redirect($redirect, $message);
		}

		// Perform basic checks on file info before attempting anything
		foreach ($files as &$file)
		{
			$file['name'] = JFile::makeSafe($file['name']);
			$file['filepath'] = JPath::clean(implode(DIRECTORY_SEPARATOR, array(COM_MEDIA_BASE, $this->folder, $file['name'])));

			if ($file['error'] == 1)
			{
				JError::raiseWarning(100, JText::_('COM_MEDIA_ERROR_WARNFILETOOLARGE'));
				$app->redirect($redirect, $message);
			}

			if ($file['size'] > ($params->get('upload_maxsize', 0) * 1024 * 1024))
			{
				JError::raiseNotice(100, JText::_('COM_MEDIA_ERROR_WARNFILETOOLARGE'));
				$app->redirect($redirect, $message);
			}

			if (JFile::exists($file['filepath']))
			{
				// A file with this name already exists
				JError::raiseWarning(100, JText::_('COM_MEDIA_ERROR_FILE_EXISTS'));
				$app->redirect($redirect, $message);
			}

			if (!isset($file['name']))
			{
				// No filename (after the name was cleaned by JFile::makeSafe)
				$redirect = 'index.php';
				$app->redirect($redirect, $message);
			}
		}

		// Set FTP credentials, if given
		JClientHelper::setCredentialsFromRequest('ftp');
		JPluginHelper::importPlugin('content');
		$dispatcher = JEventDispatcher::getInstance();

		foreach ($files as &$file)
		{
			// The request is valid
			$err = null;

			if (!MediaHelper::canUpload($file, $err))
			{
				// The file can't be upload
				$message = JError::raiseNotice(100, JText::_($err));
				$app->redirect($redirect, $message);
			}

			// Trigger the onContentBeforeSave event.
			$object_file = new JObject($file);
			$result = $dispatcher->trigger('onContentBeforeSave', array('com_media.file', &$object_file));

			if (in_array(false, $result, true))
			{
				// There are some errors in the plugins
				JError::raiseWarning(100, JText::plural('COM_MEDIA_ERROR_BEFORE_SAVE', count($errors = $object_file->getErrors()), implode('<br />', $errors)));
				$app->redirect($redirect, $message);
			}

			if (!JFile::upload($object_file->tmp_name, $object_file->filepath))
			{
				// Error in upload
				JError::raiseWarning(100, JText::_('COM_MEDIA_ERROR_UNABLE_TO_UPLOAD_FILE'));
				$app->redirect($redirect, $message);
			}
			else
			{
				// Trigger the onContentAfterSave event.
				$db = JFactory::getDbo();
				$model->addImageFromUploading(str_replace(JPATH_SITE . DS . 'images' . DS, '', $object_file->filepath));

				$dispatcher->trigger('onContentAfterSave', array('com_media.file', &$object_file, true));
				$message = JText::sprintf('COM_MEDIA_UPLOAD_COMPLETE', substr($object_file->filepath, strlen(COM_MEDIA_BASE)));
				if (MediaHelper::isImage($object_file->filepath))
				{
					$params = JComponentHelper::getParams('COM_MEDIA');
					$auto_thumb = $params->get('auto_thumb');

					if ($auto_thumb == '1')
					{
						$image = new JImage($object_file->filepath);
						$width = $image->getWidth();
						$height = $image->getHeight();

						$thumb_width = $params->get('thumb_width');
						$thumb_ratio = $params->get('thumb_ratio');

						if ($thumb_width != '')
						{
							$thumb_height = (int) ($height * $thumb_width / $width);
						}
						elseif ($thumb_ratio != '')
						{
							$thumb_width = (int) ($width * $thumb_ratio);
							$thumb_height = (int) ($height * $thumb_ratio);
						}

						$newImage = $image->resize($thumb_width, $thumb_height, true);
						$pathinfo = pathinfo($object_file->filepath);
						$fileofthumb = $pathinfo['dirname'] . DS . $pathinfo['filename'] . $thumb_width . 'x' . $thumb_height . '.' . $pathinfo['extension'];
						$newImage->toFile($fileofthumb);
						$model->addImageFromUploading(str_replace(JPATH_SITE . DS . 'images' . DS, '', $fileofthumb));
					}
				}
			}
		}

		$app->redirect($redirect, $message);
	}
}
