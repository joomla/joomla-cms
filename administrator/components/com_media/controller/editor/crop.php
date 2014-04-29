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
 * Base Crop Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       3.3
 */
class MediaControllerEditorCrop extends JControllerBase
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
		$x     = $this->app->input->get('x');
		$y     = $this->app->input->get('y');
		$w     = $this->app->input->get('w');
		$h     = $this->app->input->get('h');

		$file   = $this->app->input->get('file');
		$folder = $this->app->input->get('folder', '', 'path');
		$id		= $this->app->input->get('id');

		$viewName = $this->input->getWord('view', 'editor');
		$modelClass = $this->prefix . 'Model' . ucfirst($viewName);

		$model = new $modelClass;

		if (empty($w) && empty($h) && empty($x) && empty($y))
		{
			$this->app->enqueueMessage(JText::_('COM_MEDIA_EDITOR_CROP_AREA_ERROR'), 'error');
			$url = 'index.php?option=com_media&controller=media.display.editor&folder=' . $folder . '&file=' . $file . '&id=' . $id;
			$this->app->redirect(JRoute::_($url, false));
		}
		elseif ($model->cropImage($id, $w, $h, $x, $y))
		{
			$this->app->enqueueMessage(JText::_('COM_MEDIA_EDITOR_FILE_CROP_SUCCESS'));
			$url = 'index.php?option=com_media&controller=media.display.editor&folder=' . $folder . '&file=' . $file . '&id=' . $id;
			$this->app->redirect(JRoute::_($url, false));
		}
		else
		{
			$this->app->enqueueMessage(JText::_('COM_MEDIA_EDITOR_FILE_CROP_ERROR'), 'error');
			$url = 'index.php?option=com_media&controller=media.display.editor&folder=' . $folder . '&file=' . $file . '&id=' . $id;
			$this->app->redirect(JRoute::_($url, false));
		}


		return;
	}

}
