<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

/**
 * Copy Controller for Media Manager files
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       3.5
 */
class MediaControllerMediaCopy extends JControllerBase
{
	/**
	 * Method to copy media manager files.
	 *
	 * @return  mixed  Calls $app->redirect() for all cases
	 *
	 * @since   3.5
	 */
	public function execute()
	{
		if (!JSession::checkToken('request'))
		{
			$this->app->enqueueMessage(JText::_('JINVALID_TOKEN'));
			$this->app->redirect('index.php');
		}

		// Get some data from the request
		$tmpl	= $this->input->get('tmpl');
		$paths	= $this->input->get('rm', array(), 'array');
		$folder = $this->input->get('folder', '', 'raw');
		$targetPath = $this->input->get('targetPath', '', 'raw');
		$return = JFactory::getSession()->get('com_media.return_url', 'index.php?option=com_media&controller=media.display.media');

		// Avoid issue with single string in a relative path
		if (strlen(trim($targetPath)) == 0)
		{
			$targetPath = $this->input->get('targetPath', '');
		}

		if (strlen(trim($folder)) == 0)
		{
			$folder = $this->input->get('folder', '');
		}

		// Nothing to copy
		if (empty($paths) || is_null($targetPath))
		{
			return true;
		}

		$user	= JFactory::getUser();

		// Authorize the user
		if (!$user->authorise('core.create', 'com_media'))
		{
			// User is not authorised to copy
			$this->app->enqueueMessage(JText::_('JLIB_APPLICATION_ERROR_CREATE_NOT_PERMITTED'));

			return false;
		}

		// Set FTP credentials, if given
		JClientHelper::setCredentialsFromRequest('ftp');

		JPluginHelper::importPlugin('content');
		$dispatcher	= JEventDispatcher::getInstance();

		foreach ($paths as $path)
		{
			if ($path !== JFile::makeSafe($path))
			{
				// Filename is not safe
				$filename = htmlspecialchars($path, ENT_COMPAT, 'UTF-8');

				// Seperate method to make this class reusable for folder deletion
				$this->app->enqueueMessage(JText::sprintf('COM_MEDIA_ERROR_UNABLE_TO_COPY_FILE_WARNFILENAME', substr($filename, strlen(COM_MEDIA_BASE))), 'error');
				continue;
			}

			// Existing filepath filename
			$fullPath = JPath::clean(implode(DIRECTORY_SEPARATOR, array(COM_MEDIA_BASE, $folder, $path)));

			// Destination filepath with filename
			$targetFullPath = JPath::clean(implode(DIRECTORY_SEPARATOR, array(COM_MEDIA_BASE, $targetPath, $path)));
			$object_file = new JObject(array('filepath' => $fullPath, 'targetpath' => $targetFullPath));

			if (is_file($object_file->filepath))
			{
				// Trigger the onContentBeforeCopy event.
				$result = $dispatcher->trigger('onContentBeforeCopy', array('com_media.file', &$object_file));

				if (in_array(false, $result, true))
				{
					// There are some errors in the plugins
					$this->app->enqueueMessage(JText::plural('COM_MEDIA_ERROR_BEFORE_COPY', count($errors = $object_file->getErrors()), implode('<br />', $errors)), 'error');
					continue;
				}

				if (!JFile::copy($object_file->filepath, $object_file->targetpath))
				{
					$this->app->enqueueMessage(JText::sprintf('COM_MEDIA_ERROR_FILE_COPY', substr($object_file->filepath, strlen(COM_MEDIA_BASE))), 'error');
					$this->app->redirect(JRoute::_($return . '&folder=' . $this->folder, false));
				}

				// Model to change table record of the media
				$model = new MediaModelMedia;

				// Creating a new table entry
				if (!$model->copyMediaFromTable($fullPath, $targetFullPath))
				{
					// Can't create a record in database
					$this->app->enqueueMessage(JText::sprintf('COM_MEDIA_ERROR_DATABASE_COPY', substr($object_file->filepath, strlen(COM_MEDIA_BASE))), 'error');
					$this->app->redirect(JRoute::_($return . '&folder=' . $this->folder, false));
				}

				// Trigger the onContentAfterCopy event.
				$dispatcher->trigger('onContentAfterCopy', array('com_media.file', &$object_file));
				$this->app->enqueueMessage(JText::sprintf('COM_MEDIA_COPY_COMPLETE', substr($object_file->filepath, strlen(COM_MEDIA_BASE))));

			}
			elseif (is_dir($object_file->filepath))
			{
				// Create a new media folder
				if (!JFolder::create($object_file->targetpath))
				{
					$this->app->enqueueMessage(JText::sprintf('COM_MEDIA_ERROR_FOLDER_COPY', substr($object_file->filepath, strlen(COM_MEDIA_BASE))), 'error');
					$this->app->redirect(JRoute::_($return . '&folder=' . $this->folder, false));
				}

				// Get files & folders inside the folder as an array
				$mediaArray = array_merge(JFolder::files($object_file->filepath), JFolder::folders($object_file->filepath));

				// Filter images only
				for ($index = 0; $index < count($mediaArray); $index++)
				{
					$file = JPath::clean($mediaArray[$index]);
					$fileFullPath = JPath::clean(implode(DIRECTORY_SEPARATOR, array(COM_MEDIA_BASE, $folder, $path, $file)));
					$targetFileFullPath = JPath::clean(implode(DIRECTORY_SEPARATOR, array(COM_MEDIA_BASE, $targetPath, $path, $file)));

					if (!JHelperMedia::isImage($file) && !is_dir($fileFullPath))
					{
						// Move other files directly
						if (!JFile::copy($fileFullPath, $targetFileFullPath))
						{
							$this->app->enqueueMessage(JText::sprintf('COM_MEDIA_ERROR_FILE_COPY', substr($object_file->filepath, strlen(COM_MEDIA_BASE))), 'error');
							$this->app->redirect(JRoute::_($return . '&folder=' . $this->folder, false));
						}

						// Remove non-image from media array
						unset($mediaArray[$index]);
					}
				}

				// New JInput for recursively move internal media
				$newInput = new JInput;
				$newInput->set('rm', $mediaArray);
				$newInput->set('folder', JPath::clean(implode(DIRECTORY_SEPARATOR, array($folder, $path))));
				$newInput->set('targetPath', JPath::clean(implode(DIRECTORY_SEPARATOR, array($targetPath, $path))));
				$this->input = $newInput;

				// Not to redirect during recursion
				JFactory::getSession()->set('com_media.return_url', false);

				$copyController = new MediaControllerMediaCopy;

				if (!$copyController->execute())
				{
					$this->app->enqueueMessage(JText::sprintf('COM_MEDIA_ERROR_FOLDER_COPY', substr($object_file->filepath, strlen(COM_MEDIA_BASE))), 'error');
					$this->app->redirect(JRoute::_($return . '&folder=' . $this->folder, false));
				}
			}
		}

		if ($return)
		{
			$this->app->redirect(JRoute::_($return . '&folder=' . $folder, false));
		}
		else
		{
			return true;
		}
	}
}
