<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Joomla.Media
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Base Thumbs Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       3.3
 */
class MediaControllerEditorThumbs extends JControllerBase
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
		$size             = $this->app->input->get('s');
		$thumbsFolder     = $this->app->input->get('t', null, 'FILE');
		$creationMethod   = $this->app->input->get('c', JImage::SCALE_INSIDE, 'int');

		$file   = $this->app->input->get('file');
		$folder = $this->app->input->get('folder', '', 'path');
		$id		= $this->app->input->get('id');

		if(!empty($thumbsFolder))
		{
			$thumbsFolder = JPath::clean(COM_MEDIA_BASE . '/' . $folder . '/' . $thumbsFolder);
		}

		$viewName = $this->input->getWord('view', 'editor');
		$modelClass = $this->prefix . 'Model' . ucfirst($viewName);

		$model = new $modelClass;

		if ($model->createThumbs($id, $size, $creationMethod, $thumbsFolder))
		{
			$this->app->enqueueMessage(JText::_('COM_MEDIA_EDITOR_FILE_THUMBS_SUCCESS'));
			$url = 'index.php?option=com_media&controller=media.display.editor&folder=' . $folder . '&file=' . $file . '&id=' . $id;
			$this->app->redirect(JRoute::_($url, false));
		}
		else
		{
			$this->app->enqueueMessage(JText::_('COM_MEDIA_EDITOR_FILE_THUMBS_ERROR'), 'error');
			$url = 'index.php?option=com_media&controller=media.display.editor&folder=' . $folder . '&file=' . $file . '&id=' . $id;
			$this->app->redirect(JRoute::_($url, false));
		}

		return;
	}

}
