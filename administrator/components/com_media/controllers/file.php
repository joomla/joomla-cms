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
 * Media File Controller
 *
 * @package		Joomla.Administrator
 * @subpackage	com_media
 * @since		1.5
 */
class MediaControllerFile extends JController
{
	/**
	 * Upload a file
	 *
	 * @since 1.5
	 */
	function upload()
	{
		// Check for request forgeries
		JRequest::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

		$app	= JFactory::getApplication();
		$file	= JRequest::getVar('Filedata', '', 'files', 'array');
		$folder	= JRequest::getVar('folder', '', '', 'path');
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
				JError::raiseNotice(100, JText::_($err));

				// REDIRECT
				if ($return) {
					$this->setRedirect(base64_decode($return).'&folder='.$folder);
				}

				return;

			}

			if (JFile::exists($filepath)) {
				JError::raiseNotice(100, JText::_('COM_MEDIA_ERROR_FILE_EXISTS'));

				// REDIRECT
				if ($return) {
					$this->setRedirect(base64_decode($return).'&folder='.$folder);
				}

				return;
			}

			if (!JFile::upload($file['tmp_name'], $filepath)) {
				JError::raiseWarning(100, JText::_('COM_MEDIA_ERROR_UNABLE_TO_UPLOAD_FILE'));

				// REDIRECT
				if ($return) {
					$this->setRedirect(base64_decode($return).'&folder='.$folder);
				}

				return;
			}
			else {
				$app->enqueueMessage(JText::_('COM_MEDIA_UPLOAD_COMPLETE'));

				// REDIRECT
				if ($return) {
					$this->setRedirect(base64_decode($return).'&folder='.$folder);
				}

				return;
			}
		}
		else {
			$this->setRedirect('index.php', 'Invalid Request', 'error');
		}
	}

	/**
	 * Deletes paths from the current path
	 *
	 * @param string $listFolder The image directory to delete a file from
	 * @since 1.5
	 */
	function delete()
	{
		JRequest::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');

		// Get some data from the request
		$tmpl	= JRequest::getCmd('tmpl');
		$paths	= JRequest::getVar('rm', array(), '', 'array');
		$folder = JRequest::getVar('folder', '', '', 'path');

		// Initialise variables.
		$msg = array();
		$ret = true;

		if (count($paths)) {
			foreach ($paths as $path)
			{
				if ($path !== JFile::makeSafe($path)) {
					$filename = htmlspecialchars($path, ENT_COMPAT, 'UTF-8');
					JError::raiseWarning(100, JText::sprintf('COM_MEDIA_ERROR_UNABLE_TO_DELETE_FILE_WARNFILENAME', $filename));

					continue;
				}

				$fullPath = JPath::clean(COM_MEDIA_BASE.DS.$folder.DS.$path);

				if (is_file($fullPath)) {
					$ret |= !JFile::delete($fullPath);
				}
				else if (is_dir($fullPath)) {
					$files = JFolder::files($fullPath, '.', true);
					$canDelete = true;

					foreach ($files as $file)
					{
						if ($file != 'index.html') {
							$canDelete = false;
						}
					}

					if ($canDelete) {
						$ret |= !JFolder::delete($fullPath);
					}
					else {
						//This makes no sense...
						JError::raiseWarning(100, JText::_('COM_MEDIA_ERROR_UNABLE_TO_DELETE').$fullPath.' '.JText::_('COM_MEDIA_ERROR_WARNNOTEMPTY'));
					}
				}
			}
		}

		if ($tmpl == 'component') {
			// We are inside the iframe
			$this->setRedirect('index.php?option=com_media&view=mediaList&folder='.$folder.'&tmpl=component');
		}
		else {
			$this->setRedirect('index.php?option=com_media&folder='.$folder);
		}
	}
}