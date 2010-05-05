<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

/**
 * Weblinks Weblink Controller
 *
 * @package		Joomla.Administrator
 * @subpackage	com_media
 * @since		1.6
 */
class MediaControllerFile extends JController
{
	/**
	 * Upload a file
	 *
	 * @since 1.6
	 */
	function upload()
	{
		// Check for request forgeries
		if (!JRequest::checkToken('request')) {
			$response = array(
				'status' => '0',
				'error' => JText::_('JINVALID_TOKEN')
			);

			echo json_encode($response);
			return;
		}

		$app	= &JFactory::getApplication();
		$file	= JRequest::getVar('Filedata', '', 'files', 'array');
		$folder	= JRequest::getVar('folder', '', '', 'path');
		$format	= JRequest::getVar('format', 'html', '', 'cmd');
		$return	= JRequest::getVar('return-url', null, 'post', 'base64');
		$err	= null;

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');

		// Make the filename safe
		jimport('joomla.filesystem.file');
		$file['name']	= JFile::makeSafe($file['name']);

		if (isset($file['name'])) {
			$filepath = JPath::clean(COM_MEDIA_BASE.DS.$folder.DS.strtolower($file['name']));

			if (!MediaHelper::canUpload($file, $err)) {
				jimport('joomla.error.log');
				$log = &JLog::getInstance('upload.error.php');
				$log->addEntry(array('comment' => 'Invalid: '.$filepath.': '.$err));

				$response = array(
					'status' => '0',
					'error' => $err
				);

				echo json_encode($response);
				return;
			}

			if (JFile::exists($filepath)) {
				jimport('joomla.error.log');
				$log = &JLog::getInstance('upload.error.php');
				$log->addEntry(array('comment' => 'File already exists: '.$filepath));

				$response = array(
					'status' => '0',
					'error' => JText::_('COM_MEDIA_ERROR_FILE_EXISTS')
				);

				echo json_encode($response);
				return;
			}

			if (!JFile::upload($file['tmp_name'], $filepath)) {
				jimport('joomla.error.log');
				$log = &JLog::getInstance('upload.error.php');
				$log->addEntry(array('comment' => 'Cannot upload: '.$filepath));

				$response = array(
					'status' => '0',
					'error' => JText::_('COM_MEDIA_ERROR_BAD_REQUEST')
				);

				echo json_encode($response);
				return;
			} else {
				jimport('joomla.error.log');
				$log = &JLog::getInstance();
				$log->addEntry(array('comment' => $folder));

				$response = array(
					'status' => '1',
					'error' => JText::_('COM_MEDIA_UPLOAD_SUCCESSFUL')
				);

				echo json_encode($response);
				return;
			}

			$response = array(
				'status' => '0',
				'error' => JText::_('COM_MEDIA_ERROR_BAD_REQUEST')
			);

			echo json_encode($response);
			return;

		}
	}
}