<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

/**
 * Base Display Controller
 *
 * @package     Joomla.Libraries
 * @subpackage  Controller
 * @since       3.4
*/
class JControllerDisplay extends JControllerCms
{
	/*
	 * If true, the view output will be cached
	 *
	 * @var    boolean
	 * @since  3.4
	 */
	public $cacheable = false;

	/*
	 * An array of safe url parameters and their variable types
	 *
	 * @var    array
	 * @since  3.4
	 * @note   For valid values see JFilterInput::clean().
	 */
	public $urlparams = array();

	/**
	 * The view to display
	 *
	 * @var    JViewCms
	 * @since  3.4
	 */
	protected $view;

	/**
	 * Execute the controller.
	 *
	 * @return  boolean  True if controller finished execution, false if the controller did not
	 *                   finish execution. A controller might return false if some precondition for
	 *                   the controller to run has not been satisfied.
	 *
	 * @since   3.4
	 * @throws  RuntimeException
	 */
	public function execute()
	{
		!$this->app->isAdmin() ? : $this->permission = 'core.manage';

		// Get the view name if it hasn't already been set by a controller
		$this->viewName = $this->input->getWord('view', 'articles');
		$viewFormat     = $this->doc->getType();

		try
		{
			$model = $this->getModel();
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
		}

		// Access check.
		if (!empty($this->permission) && !JFactory::getUser()->authorise($this->permission, $model->getState('component.option')))
		{
			$this->app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');

			return false;
		}

		// Initialise the view class.
		$view = $this->getView($model, $this->prefix, $this->viewName, $viewFormat);

		// Render view.
		echo $view->render();

		return true;
	}

	/**
	 * Method to get a view, initiating it if it does not already exist.
	 * This method assumes auto-loading format is $prefix . 'View' . $name . $type
	 * The
	 *
	 * @param   JModelCmsInterface  $model   The model to be injected
	 * @param   string              $prefix  Option prefix exp. com_content
	 * @param   string              $name    Name of the view folder exp. articles
	 * @param   string              $type    Name of the file exp. html = html.php
	 * @param   array               $config  An array of config options
	 *
	 * @since   3.4
	 * @throws  RuntimeException
	 * @return  JViewCms
	 */
	protected function getView(JModelCmsInterface $model, $prefix = null, $name = null, $type = null, $config = array())
	{
		// Get the prefix if not given
		if (is_null($prefix))
		{
			$prefix = $this->getPrefix();
		}

		// Get the name if not given
		if (is_null($name))
		{
			$name = $this->config['subject'];
		}

		$this->config['view'] = $name;

		// Get the document type
		if (is_null($type))
		{
			$type   = $this->doc->getType();
		}

		$class = ucfirst($prefix) . 'View' . ucfirst($name) . ucfirst($type);

		if ($this->view instanceof $class)
		{
			return $this->view;
		}

		// If a custom class doesn't exist fall back to the Joomla class if it exists
		if (!class_exists($class))
		{
			$joomlaClass = 'JView' . ucfirst($type) . 'Cms';

			if (!class_exists($joomlaClass))
			{
				throw new RuntimeException(JText::sprintf('JLIB_APPLICATION_ERROR_VIEW_NOT_FOUND', $name, $type, $prefix));
			}

			// We've found a relevant Joomla class - use it.
			$class = $joomlaClass;
		}

		switch (strtolower($type))
		{
			case 'json':
				$view = new $class($model, $this->doc, $this->config);

				if (isset($this->config['useHypermedia']) && $this->config['useHypermedia'])
				{
					$this->doc->setHypermedia(true);
				}
				break;
			case 'html':
			default:
				$renderer = $this->getRenderer();

				// Initialise the view class
				$view = new $class($model, $this->doc, $renderer, $this->config);

				// If in html view then we set the layout
				$layoutName = $this->input->getWord('layout', 'default');
				$view->setLayout($layoutName);
				break
		}

		$this->view = $view;

		return $this->view;
	}

	/**
	 * Allows the renderer class to be injected into the view to be set
	 *
	 * @return  RendererInterface  The renderer object
	 *
	 * @since   3.4
	 */
	protected function getRenderer()
	{
		return null;
	}
}
