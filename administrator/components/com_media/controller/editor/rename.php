<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Joomla.Media
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Base Rename Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       3.3
 */
class MediaControllerEditorRename extends JControllerBase
{
	/**
	 * Prefix for the view and model classes
	 *
	 * @var    string
	 * @since  3.3
	 */
	public $prefix = 'Media';

	/**
	 * Execute the controller.
	 *
	 * @return  mixed  A rendered view or true
	 *
	 * @since   3.3
	 */
	public function execute()
	{
		$newName = $this->app->input->get('new_name');

		$file   = $this->app->input->get('file');
		$folder = $this->app->input->get('folder');
		
		$path     = JPath::clean(COM_MEDIA_BASE . '/' . $file);
		
		if(!empty($folder))
		{
			$path     = JPath::clean(COM_MEDIA_BASE . '/' . $folder . '/' . $file);
		}

		$viewName = $this->input->getWord('view', 'editor');
		$modelClass = $this->prefix . 'Model' . ucfirst($viewName);

		$model = new $modelClass;

		if ($file == 'index.php')
		{
			$this->app->enqueueMessage(JText::_('COM_MEDIA_EDITOR_ERROR_RENAME_INDEX'), 'warning');
			$url = 'index.php?option=com_media&controller=media.display.editor&folder=' . $folder . '&file=' . $file;
			$this->app->redirect(JRoute::_($url, false));
		}
		elseif (!preg_match('/^[a-zA-Z0-9-_]+$/', $newName))
		{
			$this->app->enqueueMessage(JText::_('COM_MEDIA_EDITOR_INVALID_FILE_NAME'), 'error');
			$url = 'index.php?option=com_media&controller=media.display.editor&folder=' . $folder . '&file=' . $file;
			$this->app->redirect(JRoute::_($url, false));
		}
		elseif ($rename = $model->renameFile($path, $newName))
		{
			$this->app->enqueueMessage(JText::_('COM_MEDIA_EDITOR_FILE_RENAME_SUCCESS'));
			$url = 'index.php?option=com_media&controller=media.display.editor&folder=' . $folder . '&file=' . $rename;
			$this->app->redirect(JRoute::_($url, false));
		}
		else
		{
			$this->app->enqueueMessage(JText::_('COM_MEDIA_EDITOR_ERROR_FILE_RENAME'), 'error');
			$url = 'index.php?option=com_media&controller=media.display.editor&folder=' . $folder . '&file=' . $file;
			$this->app->redirect(JRoute::_($url, false));
		}

		return;
	}

}
