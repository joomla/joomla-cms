<?php 
/**
 * @package     Joomla.Site
 * @subpackage  com_services
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Display Controller for global configuration
 *
 * @package     Joomla.Site
 * @subpackage  com_services
 * @since       3.2
*/
class ServicesControllerConfigDisplay extends JControllerBase
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

		$viewName     = $app->input->getWord('view', 'config');
		$viewFormat   = $document->getType();
		$layoutName   = $app->input->getWord('layout', 'default');

		$app->input->set('view', $viewName);

		// Register the layout paths for the view
		$paths = new SplPriorityQueue;
		$paths->insert(JPATH_COMPONENT . '/view/' . 'config' . '/tmpl', 'normal');

		$viewClass  = 'ServicesView' . ucfirst($viewName) . ucfirst($viewFormat);
		$modelClass = 'ServicesModel' . ucfirst($viewName);

		if ($view = new $viewClass)
		{

			if ($viewName != 'close')
			{
				$model = new $modelClass;

				// Access check.
				if (!JFactory::getUser()->authorise('core.admin', $model->getState('component.option')))
				{
					return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
				}

				// Set model
				$view->setModel($model, true);
			}

			$view->setLayout($layoutName);

			// Push document object into the view.
			$view->document = $document;

			// Render view.
			echo $view->render();
		}
		return true;
	}

}
