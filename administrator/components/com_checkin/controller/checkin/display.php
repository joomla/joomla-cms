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
class CheckinControllerCheckinDisplay extends JControllerBase
{
	/**
	 * Method to checkin.
	 *
	 * @return  bool	True on success, false on failure.
	 *
	 * @since   3.2
	 */
	public function execute()
	{

		// Get the application
		$app = $this->getApplication();

		// Get the document object.
		$document     = JFactory::getDocument();

		$viewName     = $this->input->getWord('view', 'checkin');
		$viewFormat   = $document->getType();
		$layoutName   = $this->input->getWord('layout', 'default');

		// Register the layout paths for the view
		$paths = new SplPriorityQueue;
		$paths->insert(JPATH_ADMINISTRATOR . '/components/com_checkin/view/' . $viewName . '/tmpl', 'normal');

		$viewClass  = 'CheckinView' . ucfirst($viewName) . ucfirst($viewFormat);
		$modelClass = 'CheckinModel' . ucfirst($viewName);

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
