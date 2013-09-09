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
 * This file handles file/folder renaming.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       3.2
 */

class MediaControllerRename extends JControllerBase
{
	/**
	 * Implement method in interface JControllerBase
	 *
	 * @return  boolean        This object redirect to $redirect and $message
	 *
	 * @since   3.2
	 */
	public function execute()
	{
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));
		$app = $this->getApplication();
		$input = $app->input;
		$model = new MediaModelSync;
		$renameValue = $input->get('renameValue');

		$user = JFactory::getUser();

		// Get some data from the request
		$tmpl = $input->get('tmpl');
		$paths = $input->get('rm', array(), 'array');
		$folder = $input->get('folder', '', 'path');

		$message = '';
		$redirect = '';

		$redirect = 'index.php?option=com_media&folder=' . $folder;

		if ($tmpl == 'component')
		{
			// We are inside the iframe
			$redirect .= '&view=mediaList&tmpl=component';
		}

		// Just return if there's nothing to do
		if (empty($paths))
		{
			return true;
		}

		if ((!$user->authorise('core.delete', 'com_media')) || (!$user->authorise('core.create', 'com_media')))
		{
			// User is not authorised to delete
			JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_RENAME_NOT_PERMITTED'));
			$app->redirect($redirect, $message);
		}

		// Set FTP credentials, if given
		JClientHelper::setCredentialsFromRequest('ftp');

		JPluginHelper::importPlugin('content');
		$dispatcher = JEventDispatcher::getInstance();

		if (count($paths) != 1) // This should never happen
		{
			JError::raiseWarning(100, JText::_('COM_MEDIA_ERROR_RENAME_MORE_THAN_ONE'));
			$app->redirect($redirect, $message);
		}

		if (count($paths))
		{
			// Should have only 1 path now
			foreach ($paths as $path)
			{
				if ($path !== JFile::makeSafe($path))
				{
					$dirname = htmlspecialchars($path, ENT_COMPAT, 'UTF-8');
					JError::raiseWarning(100, JText::sprintf('COM_MEDIA_ERROR_UNABLE_TO_RENAME', substr($dirname, strlen(COM_MEDIA_BASE))));
					$app->redirect($redirect, $message);
				}

				$fullPath = JPath::clean(implode(DIRECTORY_SEPARATOR, array(COM_MEDIA_BASE, $folder, $path)));
				$renamePath = JPath::clean(implode(DIRECTORY_SEPARATOR, array(COM_MEDIA_BASE, $folder, $renameValue)));

				if (!$renameValue)
				{
					JError::raiseWarning(100, JText::_('COM_MEDIA_ERROR_NAME_CAN_NOT_BE_EMPTY'));
					$app->redirect($redirect, $message);
				}

				if (is_file($fullPath))
				{
					$params = JComponentHelper::getParams('com_media');
					jimport('joomla.filesystem.file');

					if ($renameValue !== JFile::makesafe($renameValue))
					{
						JError::raiseWarning(100, JText::_('COM_MEDIA_ERROR_NOT_SUPPORTED_EXTENSION'));
						$app->redirect($redirect, $message);
					}

					$format = strtolower(JFile::getExt($renameValue));
					$allowable = explode(',', $params->get('upload_extensions'));
					$ignored = explode(',', $params->get('ignore_extensions'));

					if (!in_array($format, $allowable) && !in_array($format, $ignored))
					{
						JError::raiseWarning(100, JText::_('COM_MEDIA_ERROR_NOT_SUPPORTED_EXTENSION'));
						$app->redirect($redirect, $message);
					}
				}

				if (file_exists($renamePath))
				{
					JError::raiseWarning(100, JText::_('COM_MEDIA_ERROR_CURRENT_FILE_EXISTED'));
					$app->redirect($redirect, $message);
				}
				else
				{
					$renameRes = rename($fullPath, $renamePath);
					$fullRelative = $folder == '' ? $path : $folder . "\\" . $path;
					$fullRenameRelative = $folder == '' ? $renameValue : $folder . "\\" . $renameValue;
					$model->renameDatabase($fullRelative, $fullRenameRelative);
					$message = JText::_('COM_MEDIA_RENAME_SUCCESSFUL');
				}

				if (!$renameRes)
				{
					JError::raiseWarning(100, JText::_('COM_MEDIA_ERROR_UNABLE_TO_RENAME'));
					$message = '';
					$app->redirect($redirect, $message);
				}
			}
		}

		$app->redirect($redirect, $message);
	}
}