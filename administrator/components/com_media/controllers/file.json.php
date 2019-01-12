<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

/**
 * File Media Controller
 *
 * @since  1.6
 */
class MediaControllerFile extends JControllerLegacy
{
	/**
	 * Upload a file
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public function upload()
	{
		$params = JComponentHelper::getParams('com_media');

		// Check for request forgeries
		if (!JSession::checkToken('request'))
		{
			$response = array(
				'status'  => '0',
				'message' => JText::_('JINVALID_TOKEN'),
				'error'   => JText::_('JINVALID_TOKEN')
			);
			echo json_encode($response);

			return;
		}

		// Get the user
		$user  = JFactory::getUser();
		JLog::addLogger(array('text_file' => 'upload.error.php'), JLog::ALL, array('upload'));

		// Get some data from the request
		$file   = $this->input->files->get('Filedata', '', 'array');
		$folder = $this->input->get('folder', '', 'path');

		// Instantiate the media helper
		$mediaHelper = new JHelperMedia;

		if ($_SERVER['CONTENT_LENGTH'] > ($params->get('upload_maxsize', 0) * 1024 * 1024)
			|| $_SERVER['CONTENT_LENGTH'] > $mediaHelper->toBytes(ini_get('upload_max_filesize'))
			|| $_SERVER['CONTENT_LENGTH'] > $mediaHelper->toBytes(ini_get('post_max_size'))
			|| $_SERVER['CONTENT_LENGTH'] > $mediaHelper->toBytes(ini_get('memory_limit')))
		{
			$response = array(
				'status'  => '0',
				'message' => JText::_('COM_MEDIA_ERROR_WARNFILETOOLARGE'),
				'error'   => JText::_('COM_MEDIA_ERROR_WARNFILETOOLARGE')
			);
			echo json_encode($response);

			return;
		}

		// Set FTP credentials, if given
		JClientHelper::setCredentialsFromRequest('ftp');

		if (isset($file['name']))
		{
			// Make the filename safe
			$file['name'] = JFile::makeSafe($file['name']);

			// We need a URL safe name
			$fileparts = pathinfo(COM_MEDIA_BASE . '/' . $folder . '/' . $file['name']);

			// Transform filename to punycode
			$fileparts['filename'] = JStringPunycode::toPunycode($fileparts['filename']);
			$tempExt = !empty($fileparts['extension']) ? strtolower($fileparts['extension']) : '';

			// Transform filename to punycode, then neglect otherthan non-alphanumeric characters & underscores. Also transform extension to lowercase
			$safeFileName = preg_replace(array("/[\\s]/", '/[^a-zA-Z0-9_\-]/'), array('_', ''), $fileparts['filename']) . '.' . $tempExt;

			// Create filepath with safe-filename
			$files['final'] = $fileparts['dirname'] . DIRECTORY_SEPARATOR . $safeFileName;
			$file['name']   = $safeFileName;

			$filepath = JPath::clean($files['final']);

			if (!$mediaHelper->canUpload($file, 'com_media'))
			{
				try
				{
					JLog::add('Invalid: ' . $filepath, JLog::INFO, 'upload');
				}
				catch (RuntimeException $exception)
				{
					// Informational log only
				}

				$response = array(
					'status'  => '0',
					'message' => JText::_('COM_MEDIA_ERROR_UNABLE_TO_UPLOAD_FILE'),
					'error'   => JText::_('COM_MEDIA_ERROR_UNABLE_TO_UPLOAD_FILE')
				);

				echo json_encode($response);

				return;
			}

			// Trigger the onContentBeforeSave event.
			JPluginHelper::importPlugin('content');
			$dispatcher  = JEventDispatcher::getInstance();
			$object_file = new JObject($file);
			$object_file->filepath = $filepath;
			$result = $dispatcher->trigger('onContentBeforeSave', array('com_media.file', &$object_file, true));

			if (in_array(false, $result, true))
			{
				// There are some errors in the plugins
				try
				{
					JLog::add(
						'Errors before save: ' . $object_file->filepath . ' : ' . implode(', ', $object_file->getErrors()),
						JLog::INFO,
						'upload'
					);
				}
				catch (RuntimeException $exception)
				{
					// Informational log only
				}

				$response = array(
					'status'  => '0',
					'message' => JText::plural('COM_MEDIA_ERROR_BEFORE_SAVE', count($errors = $object_file->getErrors()), implode('<br />', $errors)),
					'error'   => JText::plural('COM_MEDIA_ERROR_BEFORE_SAVE', count($errors = $object_file->getErrors()), implode('<br />', $errors))
				);

				echo json_encode($response);

				return;
			}

			if (JFile::exists($object_file->filepath))
			{
				// File exists
				try
				{
					JLog::add('File exists: ' . $object_file->filepath . ' by user_id ' . $user->id, JLog::INFO, 'upload');
				}
				catch (RuntimeException $exception)
				{
					// Informational log only
				}

				$response = array(
					'status'   => '0',
					'message'  => JText::_('COM_MEDIA_ERROR_FILE_EXISTS'),
					'error'    => JText::_('COM_MEDIA_ERROR_FILE_EXISTS'),
					'location' => str_replace(JPATH_ROOT, '',  $filepath)
				);

				echo json_encode($response);

				return;
			}
			elseif (!$user->authorise('core.create', 'com_media'))
			{
				// File does not exist and user is not authorised to create
				try
				{
					JLog::add('Create not permitted: ' . $object_file->filepath . ' by user_id ' . $user->id, JLog::INFO, 'upload');
				}
				catch (RuntimeException $exception)
				{
					// Informational log only
				}

				$response = array(
					'status'  => '0',
					'error'   => JText::_('COM_MEDIA_ERROR_CREATE_NOT_PERMITTED'),
					'message' => JText::_('COM_MEDIA_ERROR_CREATE_NOT_PERMITTED')
				);

				echo json_encode($response);

				return;
			}

			if (!JFile::upload($object_file->tmp_name, $object_file->filepath))
			{
				// Error in upload
				try
				{
					JLog::add('Error on upload: ' . $object_file->filepath, JLog::INFO, 'upload');
				}
				catch (RuntimeException $exception)
				{
					// Informational log only
				}

				$response = array(
					'status'  => '0',
					'message' => JText::_('COM_MEDIA_ERROR_UNABLE_TO_UPLOAD_FILE'),
					'error'   => JText::_('COM_MEDIA_ERROR_UNABLE_TO_UPLOAD_FILE')
				);

				echo json_encode($response);

				return;
			}
			else
			{
				// Trigger the onContentAfterSave event.
				$dispatcher->trigger('onContentAfterSave', array('com_media.file', &$object_file, true));

				try
				{
					JLog::add($folder, JLog::INFO, 'upload');
				}
				catch (RuntimeException $exception)
				{
					// Informational log only
				}

				$returnUrl = str_replace(JPATH_ROOT, '',  $object_file->filepath);

				$response = array(
					'status'   => '1',
					'message'  => JText::sprintf('COM_MEDIA_UPLOAD_COMPLETE', $returnUrl),
					'error'    => JText::sprintf('COM_MEDIA_UPLOAD_COMPLETE', $returnUrl),
					'location' => str_replace('\\', '/', $returnUrl)
				);

				echo json_encode($response);

				return;
			}
		}
		else
		{
			$response = array(
				'status'  => '0',
				'error'   => JText::_('COM_MEDIA_ERROR_BAD_REQUEST'),
				'message' => JText::_('COM_MEDIA_ERROR_BAD_REQUEST')
			);

			echo json_encode($response);

			return;
		}
	}
}
