<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

/**
 * Delete Controller for Media Manager files
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       3.5
*/
class MediaControllerMediaDelete extends JControllerBase
{
	/**
	 * Application object - Redeclared for proper typehinting
	 *
	 * @var    JApplicationCms
	 * @since  3.5
	 */
	protected $app;

	/**
	 * Method to delete media manager files.
	 *
	 * @return  mixed  Calls $app->redirect() for all cases except JSON
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

		$user	= JFactory::getUser();

		// Get some data from the request
		$tmpl	= $this->input->get('tmpl');
		$paths	= $this->input->get('rm', array(), 'array');
		$folder = $this->input->get('folder', '', 'path');

		// Nothing to delete
		if (empty($paths))
		{
			return true;
		}

		// Authorize the user
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

		$ret = true;

		foreach ($paths as $path)
		{
			if ($path !== JFile::makeSafe($path))
			{
				// Filename is not safe
				$filename = htmlspecialchars($path, ENT_COMPAT, 'UTF-8');
// 				$this->app->enqueueMessage(JText::sprintf('COM_MEDIA_ERROR_UNABLE_TO_DELETE_FILE_WARNFILENAME', substr($filename, strlen(COM_MEDIA_BASE))));
				$this->unableToDeleteMessage($filename);
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
					$this->app->enqueueMessage(JText::plural('COM_MEDIA_ERROR_BEFORE_DELETE', count($errors = $object_file->getErrors()), implode('<br />', $errors)));
					continue;
				}

				$ret &= JFile::delete($object_file->filepath);

				// Delete from table
				$model = new MediaModelMedia;
				$model->deleteMediaFromTable($object_file->filepath);

				// Trigger the onContentAfterDelete event.
				$dispatcher->trigger('onContentAfterDelete', array('com_media.file', &$object_file));
				$this->app->enqueueMessage(JText::sprintf('COM_MEDIA_DELETE_COMPLETE', substr($object_file->filepath, strlen(COM_MEDIA_BASE))));
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
						$this->app->enqueueMessage(JText::plural('COM_MEDIA_ERROR_BEFORE_DELETE', count($errors = $object_file->getErrors()), implode('<br />', $errors)), 'warning');
						continue;
					}

					$ret &= JFolder::delete($object_file->filepath);

					// Trigger the onContentAfterDelete event.
					$dispatcher->trigger('onContentAfterDelete', array('com_media.folder', &$object_file));
					$this->app->enqueueMessage(JText::sprintf('COM_MEDIA_DELETE_COMPLETE', substr($object_file->filepath, strlen(COM_MEDIA_BASE))));
				}
				else
				{
					// This makes no sense...
					$this->app->enqueueMessage(JText::sprintf('COM_MEDIA_ERROR_UNABLE_TO_DELETE_FOLDER_NOT_EMPTY', substr($object_file->filepath, strlen(COM_MEDIA_BASE))), 'warning');
				}
			}
		}

		$redirect = 'index.php?option=com_media&controller=media.display.media&folder=' . $folder;

		if ($tmpl == 'component')
		{
			// We are inside the iframe
			$redirect .= '&tmpl=component';
		}

		$this->app->redirect(JRoute::_($redirect, false));

		return $ret;

	}
	
	/**
	 * Enqueue error when delete failed
	 *
	 * @param  string  $name  Delete failed file name
	 *
	 * @since  3.5
	 */
	protected function unableToDeleteMessage($name)
	{
		$this->app->enqueueMessage(JText::sprintf('COM_MEDIA_ERROR_UNABLE_TO_DELETE_FILE_WARNFILENAME', substr($name, strlen(COM_MEDIA_BASE))));
	}
}
