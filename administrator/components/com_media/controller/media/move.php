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
 * Move Controller for Media Manager files
 *
 * @since  3.5
 */
class MediaControllerMediaMove extends JControllerBase
{
	/**
	 * Method to move media manager files.
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

		// Nothing to move
		if (empty($paths) || is_null($targetPath))
		{
			return true;
		}

		$user	= JFactory::getUser();

		// Authorize the user
		if (!$user->authorise('core.create', 'com_media'))
		{
			// User is not authorised to create
			$this->app->enqueueMessage(JText::_('JLIB_APPLICATION_ERROR_CREATE_NOT_PERMITTED'));

			return false;
		}

		if (!$user->authorise('core.delete', 'com_media'))
		{
			// User is not authorised to delete
			$this->app->enqueueMessage(JText::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'));

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
				$this->app->enqueueMessage(JText::sprintf('COM_MEDIA_ERROR_UNABLE_TO_MOVE_FILE_WARNFILENAME', substr($filename, strlen(COM_MEDIA_BASE))), 'error');
				continue;
			}

			// Existing filepath filename
			$fullPath = JPath::clean(implode(DIRECTORY_SEPARATOR, array(COM_MEDIA_BASE, $folder, $path)));

			// Destination filepath with filename
			$targetFullPath = JPath::clean(implode(DIRECTORY_SEPARATOR, array(COM_MEDIA_BASE, $targetPath, $path)));
			$object_file = new JObject(array('filepath' => $fullPath, 'targetpath' => $targetFullPath));

			if (is_file($object_file->filepath))
			{
				// Trigger the onContentBeforeMove event.
				$result = $dispatcher->trigger('onContentBeforeMove', array('com_media.file', &$object_file));

				if (in_array(false, $result, true))
				{
					// There are some errors in the plugins
					$this->app->enqueueMessage(JText::plural('COM_MEDIA_ERROR_BEFORE_MOVE', count($errors = $object_file->getErrors()), implode('<br />', $errors)), 'error');
					continue;
				}

				if (!JFile::move($object_file->filepath, $object_file->targetpath))
				{
					$this->app->enqueueMessage(JText::sprintf('COM_MEDIA_ERROR_FILE_MOVE', substr($object_file->filepath, strlen(COM_MEDIA_BASE))), 'error');
					$this->app->redirect(JRoute::_($return . '&folder=' . $this->folder, false));
				}

				// Model to change table record of the media
				$model = new MediaModelMedia;

				if (!$model->moveMediaFromTable($fullPath, $targetFullPath))
				{
					$this->app->enqueueMessage(JText::sprintf('COM_MEDIA_ERROR_DATABASE_MOVE', substr($object_file->filepath, strlen(COM_MEDIA_BASE))), 'error');
					$this->app->redirect(JRoute::_($return . '&folder=' . $this->folder, false));
				}

				// Trigger the onContentAfterMove event.
				$dispatcher->trigger('onContentAfterMove', array('com_media.file', &$object_file));
				$this->app->enqueueMessage(JText::sprintf('COM_MEDIA_MOVE_COMPLETE', substr($object_file->filepath, strlen(COM_MEDIA_BASE))));
			}
			elseif (is_dir($object_file->filepath))
			{
				// Create a new media folder
				if (!JFolder::create($object_file->targetpath))
				{
					$this->app->enqueueMessage(JText::sprintf('COM_MEDIA_ERROR_FOLDER_MOVE', substr($object_file->filepath, strlen(COM_MEDIA_BASE))), 'error');
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
						if (!JFile::move($fileFullPath, $targetFileFullPath))
						{
							$this->app->enqueueMessage(JText::sprintf('COM_MEDIA_ERROR_FILE_MOVE', substr($object_file->filepath, strlen(COM_MEDIA_BASE))), 'error');
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

				$moveController = new MediaControllerMediaMove;

				if ($moveController->execute())
				{
					// Delete the source base folder
					JFolder::delete($object_file->filepath);
				}
				else
				{
					$this->app->enqueueMessage(JText::sprintf('COM_MEDIA_ERROR_FOLDER_MOVE', substr($object_file->filepath, strlen(COM_MEDIA_BASE))), 'error');
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
