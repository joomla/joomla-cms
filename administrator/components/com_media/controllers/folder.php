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
 * @since 1.5
 */
class MediaControllerFolder extends JController
{

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
			foreach ($paths as $path) {
				if ($path !== JFile::makeSafe($path)) {
					$dirname = htmlspecialchars($path, ENT_COMPAT, 'UTF-8');
					JError::raiseWarning(100, JText::sprintf('COM_MEDIA_ERROR_UNABLE_TO_DELETE_FOLDER_WARNDIRNAME', $dirname));
					continue;
				}

				$fullPath = JPath::clean(COM_MEDIA_BASE.DS.$folder.DS.$path);
				if (is_file($fullPath)) {
					$ret |= !JFile::delete($fullPath);
				} else if (is_dir($fullPath)) {
					$files = JFolder::files($fullPath, '.', true);
					$canDelete = true;
					foreach ($files as $file) {
						if ($file != 'index.html') {
							$canDelete = false;
						}
					}
					if ($canDelete) {
						$ret |= !JFolder::delete($fullPath);
					} else {
						JError::raiseWarning(100, JText::sprintf('COM_MEDIA_ERROR_UNABLE_TO_DELETE_FOLDER_NOT_EMPTY',$fullPath));
					}
				}
			}
		}
		if ($tmpl == 'component') {
			// We are inside the iframe
			$this->setRedirect('index.php?option=com_media&view=mediaList&folder='.$folder.'&tmpl=component');
		} else {
			$this->setRedirect('index.php?option=com_media&folder='.$folder);
		}
	}

	/**
	 * Create a folder
	 *
	 * @param string $path Path of the folder to create
	 * @since 1.5
	 */
	function create()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');

		$folder			= JRequest::getCmd('foldername', '');
		$folderCheck	= JRequest::getVar('foldername', null, '', 'string', JREQUEST_ALLOWRAW);
		$parent			= JRequest::getVar('folderbase', '', '', 'path');

		JRequest::setVar('folder', $parent);

		if (($folderCheck !== null) && ($folder !== $folderCheck)) {
			$this->setRedirect('index.php?option=com_media&folder='.$parent, JText::_('COM_MEDIA_ERROR_UNABLE_TO_CREATE_FOLDER_WARNDIRNAME'));
			return;
		}

		if (strlen($folder) > 0) {
			$path = JPath::clean(COM_MEDIA_BASE.DS.$parent.DS.$folder);
			if (!is_dir($path) && !is_file($path)) {
				jimport('joomla.filesystem.*');
				JFolder::create($path);
				$data = "<html>\n<body bgcolor=\"#FFFFFF\">\n</body>\n</html>";
				JFile::write($path.DS."index.html", $data);
			}
			JRequest::setVar('folder', ($parent) ? $parent.'/'.$folder : $folder);
		}

		$this->setRedirect('index.php?option=com_media&folder='.$parent);
	}
}