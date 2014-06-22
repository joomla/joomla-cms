<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Joomla.Media
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

/**
 * Base Create Controller for folders
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       3.5
*/
class MediaControllerMedialistCreate extends JControllerBase
{
	/**
	 * Application object - Redeclared for proper typehinting
	 *
	 * @var    JApplicationCms
	 * @since  3.5
	 */
	protected $app;

	/**
	 * Prefix for the view and model classes
	 *
	 * @var    string
	 * @since  3.5
	 */
	public $prefix = 'Media';

	/**
	 * Execute the controller.
	 *
	 * @return  mixed  A rendered view or true
	 *
	 * @since   3.5
	 */
	public function execute()
	{
		// Check for request forgeries
		if (!JSession::checkToken())
		{
			$this->app->enqueueMessage(JText::_('JINVALID_TOKEN'));
			$this->app->redirect('index.php');
		}

		$user  = JFactory::getUser();

		$folder      = $this->input->get('foldername', '');
		$folderCheck = (string) $this->input->get('foldername', null, 'raw');
		$parent      = $this->input->get('folderbase', '', 'path');

		if (strlen($folder) > 0)
		{
			if (!$user->authorise('core.create', 'com_media'))
			{
				// User is not authorised to create
				$this->app->enqueueMessage(JText::_('JLIB_APPLICATION_ERROR_CREATE_NOT_PERMITTED'));

				return false;
			}

			// Set FTP credentials, if given
			JClientHelper::setCredentialsFromRequest('ftp');

			$this->input->set('folder', $parent);

			if (($folderCheck !== null) && ($folder !== $folderCheck))
			{
				$this->app->enqueueMessage(JText::_('COM_MEDIA_ERROR_UNABLE_TO_CREATE_FOLDER_WARNDIRNAME'), 'warning');

				return false;
			}

			$path = JPath::clean(COM_MEDIA_BASE . '/' . $parent . '/' . $folder);

			if (!is_dir($path) && !is_file($path))
			{
				// Trigger the onContentBeforeSave event.
				$object_file = new JObject(array('filepath' => $path));
				JPluginHelper::importPlugin('content');
				$dispatcher	= JEventDispatcher::getInstance();
				$result = $dispatcher->trigger('onContentBeforeSave', array('com_media.folder', &$object_file, true));

				if (in_array(false, $result, true))
				{
					// There are some errors in the plugins
					JError::raiseWarning(100, JText::plural('COM_MEDIA_ERROR_BEFORE_SAVE', count($errors = $object_file->getErrors()), implode('<br />', $errors)));

					return false;
				}

				if (JFolder::create($object_file->filepath))
				{
					$data = "<html>\n<body bgcolor=\"#FFFFFF\">\n</body>\n</html>";
					JFile::write($object_file->filepath . "/index.html", $data);

					// Trigger the onContentAfterSave event.
					$dispatcher->trigger('onContentAfterSave', array('com_media.folder', &$object_file, true));
					$this->app->enqueueMessage(JText::sprintf('COM_MEDIA_CREATE_COMPLETE', substr($object_file->filepath, strlen(COM_MEDIA_BASE))));
				}
			}

			$this->input->set('folder', ($parent) ? $parent . '/' . $folder : $folder);
		}
		else
		{
			// File name is of zero length (null).
			$this->app->enqueueMessage(JText::_('COM_MEDIA_ERROR_UNABLE_TO_CREATE_FOLDER_WARNDIRNAME'), 'warning');

			return false;
		}

		$this->app->redirect(JRoute::_('index.php?option=com_media&folder=' . $parent . '&tmpl=' . $this->input->get('tmpl', 'index'), false));

		return true;
	}
}
