<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Media Manager Editor Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       3.2
 */
class MediaControllerEditor extends JControllerBase
{
	/**
	 * Implement method in interface JControllerBase
	 *
	 * @return  boolean        This object echo the view
	 *
	 * @since   3.2
	 */
	public function execute()
	{
		// Get the application
		$app = $this->getApplication();

		// Get the document object.
		$document = JFactory::getDocument();

		$viewName = $app->input->getWord('view', 'media');
		$viewFormat = $document->getType();
		$layoutName = $app->input->getWord('layout', 'default');

		$app->input->set('view', $viewName);

		// Register the layout paths for the view
		$paths = new SplPriorityQueue;
		$paths->insert(JPATH_COMPONENT . '/view/' . $viewName . '/tmpl', 'normal');

		$viewClass = 'MediaView' . ucfirst($viewName) . ucfirst($viewFormat);
		$modelClass = 'MediaModel' . ucfirst($viewName);

		if (false === class_exists($modelClass))
		{
			$modelClass = 'MediaModelDefault';
		}

		$view = new $viewClass(new $modelClass, $paths);
		$view->setLayout($layoutName);

		// Render our view.
		echo $view->render();

		return true;
	}
}
