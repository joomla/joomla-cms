<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Base Display Controller
 *
 * @since  3.2
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

		if ($this->app->isClient('administrator'))
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

		if ($this->app->isClient('administrator'))
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
			$model     = new $modelClass;
			$component = $model->getState()->get('component.option');

			// Make sure com_joomlaupdate and com_privacy can only be accessed by SuperUser
			if (in_array(strtolower($component), array('com_joomlaupdate', 'com_privacy'))
				&& !JFactory::getUser()->authorise('core.admin'))
			{
				$this->app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');

				return;
			}

			// Access check.
			if (!JFactory::getUser()->authorise('core.admin', $component)
				&& !JFactory::getUser()->authorise('core.options', $component))
			{
				$this->app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');

				return;
			}

			$view = new $viewClass($model, $paths);

			$view->setLayout($layoutName);

			// Push document object into the view.
			$view->document = $document;

			// Reply for service requests
			if ($viewFormat === 'json')
			{
				return $view->render();
			}

			// Render view.
			echo $view->render();
		}

		return true;
	}
}
