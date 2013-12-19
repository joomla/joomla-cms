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
 * Base Display Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       3.2
*/
class MediaControllerMediaDisplay extends JControllerBase
{
	/**
	 * Application object - Redeclared for proper typehinting
	 *
	 * @var    JApplicationCms
	 * @since  3.2
	 */
	protected $app;

	/**
	 * Prefix for the view and model classes
	 *
	 * @var    string
	 * @since  3.2
	 */
	public $prefix = 'Media';

	/**
	 * Execute the controller.
	 *
	 * @return  mixed  A rendered view or true
	 *
	 * @since   3.2
	 */
	public function execute()
	{
		// Get the document object.
		$document = JFactory::getDocument();

		$componentFolder = $this->input->getWord('option', 'com_media');

		$viewName = $this->input->getWord('view', 'media');

		$viewFormat = $document->getType();

		$layoutName = $this->app->getUserStateFromRequest('media.list.layout', 'layout', 'thumbs', 'word');

		// Register the layout paths for the view
		$paths = new SplPriorityQueue;

		$paths->insert(JPATH_ADMINISTRATOR . '/components/' . $componentFolder . '/view/' . $viewName . '/tmpl', 1);

		$viewClass  = $this->prefix . 'View' . ucfirst($viewName) . ucfirst($viewFormat);
		$modelClass = $this->prefix . 'Model' . ucfirst($viewName);

		if (class_exists($viewClass))
		{
			$model = new $modelClass;

			// Access check.
			if (!JFactory::getUser()->authorise('core.admin', $model->getState('component.option')))
			{
				$this->app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');

				return;
			}

			$view = new $viewClass($model, $paths);

			$view->setLayout($layoutName);

			// Push document object into the view.
			$view->document = $document;

			// Reply for service requests
			if ($viewFormat == 'json')
			{
				return $view->render();
			}

			// Render view.
			echo $view->render();
		}

		return true;
	}
}
