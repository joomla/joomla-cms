<?php
/**
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

/**
 * Folder Media Controller
 *
 * @package		Joomla.Administrator
 * @subpackage	com_media
 * @since 1.5
 */
class MediaControllerFolder extends JControllerLegacy
{

	/**
	 * Deletes paths from the current path
	 *
	 * @since 1.5
	 */
	public function delete()
	{
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

		$user	= JFactory::getUser();

		// Get some data from the request
		$tmpl	= JRequest::getCmd('tmpl');
		$paths	= JRequest::getVar('rm', array(), '', 'array');
		$folder = JRequest::getVar('folder', '', '', 'path');

		$redirect = 'index.php?option=com_media&folder=' . $folder;
		if ($tmpl == 'component') {
			// We are inside the iframe
			$redirect .= '&view=mediaList&tmpl=component';
		}
		$this->setRedirect($redirect);

		// Just return if there's nothing to do
		if (empty($paths))
		{
			return true;
		}

		if (!$user->authorise('core.delete', 'com_media'))
		{
			// User is not authorised to delete
			JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'));
			return false;
		}

		// Set FTP credentials, if given
		JClientHelper::setCredentialsFromRequest('ftp');

		// Initialise variables.
		$ret = true;

		JPluginHelper::importPlugin('content');
		$dispatcher	= JDispatcher::getInstance();
		if (count($paths))
		{
			foreach ($paths as $path)
			{
				if ($path !== JFile::makeSafe($path))
				{
					$dirname = htmlspecialchars($path, ENT_COMPAT, 'UTF-8');
					JError::raiseWarning(100, JText::sprintf('COM_MEDIA_ERROR_UNABLE_TO_DELETE_FOLDER_WARNDIRNAME', substr($dirname, strlen(COM_MEDIA_BASE))));
					continue;
				}

				$fullPath = JPath::clean(implode(DIRECTORY_SEPARATOR, array(COM_MEDIA_BASE, $folder, $path)));
				$object_file = new JObject(array('filepath' => $fullPath));
				if (is_file($fullPath))
				{
					// Trigger the onContentBeforeDelete event.
					$result = $dispatcher->trigger('onContentBeforeDelete', array('com_media.file', &$object_file));
					if (in_array(false, $result, true)) {
						// There are some errors in the plugins
						JError::raiseWarning(100, JText::plural('COM_MEDIA_ERROR_BEFORE_DELETE', count($errors = $object_file->getErrors()), implode('<br />', $errors)));
						continue;
					}

					$ret &= JFile::delete($fullPath);

					// Trigger the onContentAfterDelete event.
					$dispatcher->trigger('onContentAfterDelete', array('com_media.file', &$object_file));
					$this->setMessage(JText::sprintf('COM_MEDIA_DELETE_COMPLETE', substr($fullPath, strlen(COM_MEDIA_BASE))));
				}
				elseif (is_dir($fullPath))
				{
					$contents = JFolder::files($fullPath, '.', true, false, array('.svn', 'CVS', '.DS_Store', '__MACOSX', 'index.html'));
					if (empty($contents))
					{
						// Trigger the onContentBeforeDelete event.
						$result = $dispatcher->trigger('onContentBeforeDelete', array('com_media.folder', &$object_file));
						if (in_array(false, $result, true)) {
							// There are some errors in the plugins
							JError::raiseWarning(100, JText::plural('COM_MEDIA_ERROR_BEFORE_DELETE', count($errors = $object_file->getErrors()), implode('<br />', $errors)));
							continue;
						}

						$ret &= !JFolder::delete($fullPath);

						// Trigger the onContentAfterDelete event.
						$dispatcher->trigger('onContentAfterDelete', array('com_media.folder', &$object_file));
						$this->setMessage(JText::sprintf('COM_MEDIA_DELETE_COMPLETE', substr($fullPath, strlen(COM_MEDIA_BASE))));
					}
					else
					{
						//This makes no sense...
						JError::raiseWarning(100, JText::sprintf('COM_MEDIA_ERROR_UNABLE_TO_DELETE_FOLDER_NOT_EMPTY', substr($fullPath, strlen(COM_MEDIA_BASE))));
					}
				}
			}
			return $ret;
		}
	}

	/**
	 * Create a folder
	 *
	 * @param string $path Path of the folder to create
	 * @since 1.5
	 */
	public function create()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$user = JFactory::getUser();

		$folder			= JRequest::getCmd('foldername', '');
		$folderCheck	= JRequest::getVar('foldername', null, '', 'string', JREQUEST_ALLOWRAW);
		$parent			= JRequest::getVar('folderbase', '', '', 'path');

		$this->setRedirect('index.php?option=com_media&folder='.$parent.'&tmpl='.JRequest::getCmd('tmpl', 'index'));

		if (strlen($folder) > 0)
		{
			if (!$user->authorise('core.create', 'com_media'))
			{
				// User is not authorised to delete
				JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_CREATE_NOT_PERMITTED'));
				return false;
			}

			// Set FTP credentials, if given
			JClientHelper::setCredentialsFromRequest('ftp');

			JRequest::setVar('folder', $parent);

			if (($folderCheck !== null) && ($folder !== $folderCheck))
			{
				$this->setMessage(JText::_('COM_MEDIA_ERROR_UNABLE_TO_CREATE_FOLDER_WARNDIRNAME'));
				return false;
			}

			$path = JPath::clean(COM_MEDIA_BASE . '/' . $parent . '/' . $folder);
			if (!is_dir($path) && !is_file($path))
			{
				// Trigger the onContentBeforeSave event.
				$object_file = new JObject(array('filepath' => $path));
				JPluginHelper::importPlugin('content');
				$dispatcher	= JDispatcher::getInstance();
				$result = $dispatcher->trigger('onContentBeforeSave', array('com_media.folder', &$object_file));
				if (in_array(false, $result, true))
				{
					// There are some errors in the plugins
					JError::raiseWarning(100, JText::plural('COM_MEDIA_ERROR_BEFORE_SAVE', count($errors = $object_file->getErrors()), implode('<br />', $errors)));
					return false;
				}

				JFolder::create($path);
				$data = "<html>\n<body bgcolor=\"#FFFFFF\">\n</body>\n</html>";
				JFile::write($path . "/index.html", $data);

				// Trigger the onContentAfterSave event.
				$dispatcher->trigger('onContentAfterSave', array('com_media.folder', &$object_file, true));
				$this->setMessage(JText::sprintf('COM_MEDIA_CREATE_COMPLETE', substr($path, strlen(COM_MEDIA_BASE))));
			}
			JRequest::setVar('folder', ($parent) ? $parent.'/'.$folder : $folder);
		}
	}
}
