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
 * Base Filter Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       3.3
 */
class MediaControllerEditorFilter extends JControllerBase
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
		$filter = $this->app->input->get('filter');
		$value = $this->app->input->get('value', 0, 'int');

		$file   = $this->app->input->get('file');
		$folder = $this->app->input->get('folder');
		$id		= $this->app->input->get('id');

		$viewName = $this->input->getWord('view', 'editor');
		$modelClass = $this->prefix . 'Model' . ucfirst($viewName);

		$model = new $modelClass;

		if ($model->filterImage($id, $filter, $value))
		{
			$this->app->enqueueMessage(JText::_('COM_MEDIA_EDITOR_FILE_FILTER_SUCCESS'));
			$url = 'index.php?option=com_media&controller=media.display.editor&folder=' . $folder . '&file=' . $file . '&id=' . $id;
			$this->app->redirect(JRoute::_($url, false));
		}
		else
		{
			$this->app->enqueueMessage(JText::_('COM_MEDIA_EDITOR_FILE_FILTER_ERROR'), 'error');
			$url = 'index.php?option=com_media&controller=media.display.editor&folder=' . $folder . '&file=' . $file . '&id=' . $id;
			$this->app->redirect(JRoute::_($url, false));
		}
	}

}
