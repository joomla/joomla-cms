<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Media
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
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
 * @subpackage	Media
 * @since 1.5
 */
class MediaControllerFolder extends MediaController
{

	/**
	 * Deletes paths from the current path
	 *
	 * @param string $listFolder The image directory to delete a file from
	 * @since 1.5
	 */
	function delete()
	{
		JRequest::checkToken('request') or jexit(JText::_('JInvalid_Token'));

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');

		// Get some data from the request
		$app	= &JFactory::getApplication();
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
					JError::raiseWarning(100, JText::_('Unable to delete:').htmlspecialchars($path, ENT_COMPAT, 'UTF-8').' '.JText::_('WARNDIRNAME'));
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
						JError::raiseWarning(100, JText::_('Unable to delete:').$fullPath.' '.JText::_('Not Empty!'));
					}
				}
			}
		}
		if ($tmpl == 'component') {
			// We are inside the iframe
			$app->redirect('index.php?option=com_media&view=mediaList&folder='.$folder.'&tmpl=component');
		} else {
			$app->redirect('index.php?option=com_media&folder='.$folder);
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
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');

		$app			= &JFactory::getApplication();
		$folder			= JRequest::getCmd('foldername', '');
		$folderCheck	= JRequest::getVar('foldername', null, '', 'string', JREQUEST_ALLOWRAW);
		$parent			= JRequest::getVar('folderbase', '', '', 'path');

		JRequest::setVar('folder', $parent);

		if (($folderCheck !== null) && ($folder !== $folderCheck)) {
			$app->redirect('index.php?option=com_media&folder='.$parent, JText::_('WARNDIRNAME'));
		}

		if (strlen($folder) > 0) {
			$path = JPath::clean(COM_MEDIA_BASE.DS.$parent.DS.$folder);
			if (!is_dir($path) && !is_file($path))
			{
				jimport('joomla.filesystem.*');
				JFolder::create($path);
				$data = "<html>\n<body bgcolor=\"#FFFFFF\">\n</body>\n</html>";
				JFile::write($path.DS."index.html", $data);
			}
			JRequest::setVar('folder', ($parent) ? $parent.'/'.$folder : $folder);
		}
		$app->redirect('index.php?option=com_media&folder='.$parent);
	}
}
