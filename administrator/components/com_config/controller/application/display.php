<?php 
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Display Controller for global configuration
 *
 * @package     Joomla.Administrator
 * @subpackage  com_config
 * @since       3.2
*/
class ConfigControllerApplicationDisplay extends JControllerBase
{
	/**
	 * Method to display global configuration.
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

		$viewName     = $app->input->getWord('view', 'application');
		$viewFormat   = $document->getType();
		$layoutName   = $app->input->getWord('layout', 'default');

		$app->input->set('view', $viewName);

		// Register the layout paths for the view
		$paths = new SplPriorityQueue;
		$paths->insert(JPATH_COMPONENT . '/view/' . 'application' . '/tmpl', 'normal');

		$viewClass  = 'ConfigView' . ucfirst($viewName) . ucfirst($viewFormat);
		$modelClass = 'ConfigModel' . ucfirst($viewName);

		if (class_exists($viewClass))
		{

			if ($viewName != 'close')
			{
				$model = new $modelClass;

				// Access check.
				if (!JFactory::getUser()->authorise('core.admin', $model->getState('component.option')))
				{
					$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');

					return;

				}

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
