<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Joomla.Libraries
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Base Display Controller
 *
 * @package     Joomla.Site
 * @subpackage  com_config
 * @since       3.2
*/
class ConfigControllerDisplay extends JControllerBase
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
	public $prefix = 'Config';

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

		$componentFolder = $this->input->getWord('option', 'com_config');

		if ($this->app->isAdmin())
		{
			$viewName = $this->input->getWord('view', 'application');
		}
		else
		{
			$viewName = $this->input->getWord('view', 'config');
		}

		$viewFormat = $document->getType();
		$layoutName = $this->input->getWord('layout', 'default');

		// Register the layout paths for the view
		$paths = new SplPriorityQueue;

		if ($this->app->isAdmin())
		{
			$paths->insert(JPATH_ADMINISTRATOR . '/components/' . $componentFolder . '/view/' . $viewName . '/tmpl', 1);
		}
		else
		{
			$paths->insert(JPATH_BASE . '/components/' . $componentFolder . '/view/' . $viewName . '/tmpl', 1);
		}

		$viewClass  = $this->prefix . 'View' . ucfirst($viewName) . ucfirst($viewFormat);
		$modelClass = $this->prefix . 'Model' . ucfirst($viewName);

		if (class_exists($viewClass))
		{
			$model = new $modelClass;

			// Access check.
			if (!JFactory::getUser()->authorise('core.admin', $model->getState()->get('component.option')))
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
