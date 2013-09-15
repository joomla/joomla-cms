<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_checkin
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Display Controller for checkin
 *
 * @package     Joomla.Administrator
 * @subpackage  com_checkin
 * @since       3.2
*/
class CacheControllerCacheDisplay extends JControllerBase
{
	/**
	 * @param   boolean   If true, the view output will be cached
	 * @param   array     An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JController  This object to support chaining.
	 *
	 * @since   3.2
	 */
	public function execute()
	{
		// Get the application
		$app = $this->getApplication();

		// Get the document object.
		$document     = JFactory::getDocument();

		$viewName     = $this->input->getWord('view', 'cache');
		$viewFormat   = $document->getType();
		$layoutName   = $this->input->getWord('layout', 'default');

		// Register the layout paths for the view
		$paths = new SplPriorityQueue;
		$paths->insert(JPATH_ADMINISTRATOR . '/components/com_cache/view/' . $viewName . '/tmpl', 'normal');

		$viewClass  = 'CacheView' . ucfirst($viewName) . ucfirst($viewFormat);
		$modelClass = 'CacheModel' . ucfirst($viewName);

		if (class_exists($viewClass))
		{

			$model = new $modelClass;

			// Access check.
			if (!JFactory::getUser()->authorise('core.admin', $model->getState('component.option')))
			{
				$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');

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