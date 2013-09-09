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
 * This file handles file deletions.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       3.2
 */
class MediaControllerDelete extends JControllerBase
{
	public $media;

	/**
	 * Implement method in interface JControllerBase
	 *
	 * @return  boolean        This object redirects to $redirect and $message.
	 *
	 * @since   3.2
	 */
	public function execute()
	{
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

		define('DS', DIRECTORY_SEPARATOR);

		$user = JFactory::getUser();
		$app = $this->getApplication();
		$model = new MediaModelSync;
		$input = $app->input;

		// Get some data from the request
		$tmpl = $input->get('tmpl');
		$paths = $input->get('rm', array(), 'array');
		$folder = $input->get('folder', '', 'path');

		$redirect = 'index.php?option=com_media&folder=' . $folder;
		$message  = '';

		if ($tmpl == 'component')
		{
			// We are inside the iframe
			$redirect .= '&view=mediaList&tmpl=component';
		}

		// Just return if there's nothing to do
		if (empty($paths))
		{
			$app->redirect($redirect, $message);
		}

		if (!$user->authorise('core.delete', 'com_media'))
		{
			// User is not authorised to delete
			JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'));

			return false;
		}

		// Set FTP credentials, if given
		JClientHelper::setCredentialsFromRequest('ftp');

		$ret = true;
		$message = '';

		JPluginHelper::importPlugin('content');
		$dispatcher = JEventDispatcher::getInstance();

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

				if (is_file($object_file->filepath))
				{
					// Trigger the onContentBeforeDelete event.
					$result = $dispatcher->trigger('onContentBeforeDelete', array('com_media.file', &$object_file));

					if (in_array(false, $result, true))
					{
						// There are some errors in the plugins
						JError::raiseWarning(
							100, JText::plural('COM_MEDIA_ERROR_BEFORE_DELETE', count($errors = $object_file->getErrors()), implode('<br />', $errors))
						);
						continue;
					}

					$ret &= JFile::delete($object_file->filepath);
					$model->deleteImage(str_replace(JPATH_SITE . DS . 'images' . DS, '', $object_file->filepath));

					// Trigger the onContentAfterDelete event.
					$dispatcher->trigger('onContentAfterDelete', array('com_media.file', &$object_file));
					$message = JText::_('COM_MEDIA_DELETE_COMPLETE');
				}
				elseif (is_dir($object_file->filepath))
				{
					$contents = JFolder::files($object_file->filepath, '.', true, false, array('.svn', 'CVS', '.DS_Store', '__MACOSX', 'index.html'));

					if (empty($contents))
					{
						// Trigger the onContentBeforeDelete event.
						$result = $dispatcher->trigger('onContentBeforeDelete', array('com_media.folder', &$object_file));

						if (in_array(false, $result, true))
						{
							// There are some errors in the plugins
							JError::raiseWarning(
								100, JText::plural('COM_MEDIA_ERROR_BEFORE_DELETE', count($errors = $object_file->getErrors()), implode('<br />', $errors))
							);
							continue;
						}

						$ret &= !JFolder::delete($object_file->filepath);

						// Trigger the onContentAfterDelete event.
						$dispatcher->trigger('onContentAfterDelete', array('com_media.folder', &$object_file));
						$message = JText::_('COM_MEDIA_DELETE_COMPLETE');
					}
					else
					{
						// This makes no sense...
						JError::raiseWarning(
							100, JText::sprintf('COM_MEDIA_ERROR_UNABLE_TO_DELETE_FOLDER_NOT_EMPTY', substr($object_file->filepath, strlen(COM_MEDIA_BASE)))
						);
					}
				}
			}
		}

		$app->redirect($redirect, $message);
	}
}
