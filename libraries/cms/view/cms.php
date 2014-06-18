<?php
/**
 * @package     Joomla.Cms
 * @subpackage  View
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\Registry\Registry as JRegistry;

/**
 * Prototype JView class.
 *
 * @package     Joomla.Libraries
 * @subpackage  View
 * @since       3.4
 */
abstract class JViewCms implements JView
{
	/**
	 * The data array to pass to the renderer engine
	 *
	 * @var    array
	 * @since  3.4
	 */
	protected $data = array();

	/**
	 * Key of the default model in the models array
	 *
	 * @var    string
	 * @since  3.4
	 */
	protected $defaultModel;

	/**
	 * The view layout.
	 *
	 * @var    string
	 * @since  3.4
	 */
	protected $layout = 'default';

	/**
	 * Associative array of model objects $models[$name]
	 *
	 * @var    array
	 * @since  3.4
	 */
	protected $models = array();

	/**
	 * The name of the view.
	 *
	 * @var    string
	 * @since  3.4
	 */
	protected $name;

	/**
	 * The name of the component.
	 *
	 * @var    string
	 * @since  3.4
	 */
	protected $option;

	/**
	 * Configuration options.
	 *
	 * @var    JRegistry
	 * @since  3.4
	 */
	protected $config;

	/**
	 * Method to instantiate the view.
	 *
	 * @param   JModelCmsInterface  $model   The model object.
	 * @param   array               $config  An array of config options. Should contain component
	 *                                       name and view name.
	 *
	 * @since   3.4
	 */
	public function __construct(JModelCmsInterface $model, array $config)
	{
		// Setup dependencies.
		$this->setModel($model, null, true);
		$this->config = new JRegistry($config);
		$this->name = $config['view'];
		$this->option = $config['option'];
	}

	/**
	 * Method to escape output.
	 *
	 * @param   string  $output  The output to escape.
	 *
	 * @return  string  The escaped output.
	 *
	 * @since   3.4
	 */
	public function escape($output)
	{
		return htmlspecialchars($output, ENT_COMPAT, 'UTF-8');
	}

	/**
	 * Retrieves the data array from the default model. Will
	 * automatically deal with the 3 CMS interfaces for single
	 * model items. For any other situations the method will
	 * need to be overwritten
	 *
	 * @return  array
	 *
	 * @since   3.4
	 */
	abstract public function getData();

	/**
	 * Method to get the view layout.
	 *
	 * @return  string  The layout name.
	 *
	 * @since   3.4
	 */
	public function getLayout()
	{
		return $this->layout;
	}

	/**
	 * Method to get the model object
	 *
	 * @param   string $name The name of the model (optional)
	 *
	 * @return  JModelCmsInterface
	 *
	 */
	public function getModel($name = null)
	{
		if ($name === null)
		{
			$name = $this->defaultModel;
		}

		return $this->models[$name];
	}

	/**
	 * Method to get the view name
	 *
	 * @return  string  The name of the model
	 *
	 * @since  3.4
	 */
	public function getName()
	{
		return $this->name; 
	}

	/**
	 * Method to get the option (component) name
	 *
	 * @return  string  The name of the component
	 *
	 * @since  3.4
	 */
	public function getOption()
	{
		return $this->option; 
	}

	/**
	 * Method to load the paths queue.
	 *
	 * @return  SplPriorityQueue  The paths for the layout
	 *
	 * @since   3.4
	 */
	protected function loadPaths()
	{
		// @todo investigate whether we should inject JApplicationCms in the constructor?
		// Find the root path - either site or administrator
		$app = JFactory::getApplication();
		$rootPath = $app->isAdmin() ? JPATH_ADMINISTRATOR : JPATH_SITE;
		$componentFolder = strtolower($this->getOption());
		$viewName = strtolower($this->getName());

		// Add the default paths. Use exponential priorities to allow developers to
		// insert their own paths in between
		$paths = new SplPriorityQueue;
		$paths->insert($rootPath . '/templates/' . $app->getTemplate() . '/html/' . $componentFolder . '/' . $viewName, 100);
		$paths->insert($rootPath . '/components/' . $componentFolder . '/view/' . $viewName . '/tmpl', 10);

		return $paths;
	}

	/**
	 * Method to render the view.
	 *
	 * @return  string  The rendered view.
	 *
	 * @since  3.4
	 */
	abstract public function render();

	/**
	 * Sets the data array
	 *
	 * @param   array  $data  The data array.
	 *
	 * @return  $this  Method allows chaining
	 *
	 * @since   3.4
	 */
	public function setData(array $data)
	{
		$this->data = $data;

		return $this;
	}

	/**
	 * Method to set the view layout.
	 *
	 * @param   string  $layout  The layout name.
	 *
	 * @return  $this  Method supports chaining.
	 *
	 * @since   3.4
	 */
	public function setLayout($layout)
	{
		$this->layout = $layout;

		return $this;
	}

	/**
	 * Method to add a model to the view.  We support a multiple model single
	 * view system by which models are referenced by class name.
	 *
	 * @param   JModelCmsInterface   $model    The model to add to the view.
	 * @param   string               $name     The name for model to be stored as (optional)
	 * @param   boolean              $default  Is this the default model? Defaults to false
	 *
	 * @return  void
	 */
	public function setModel(JModelCmsInterface $model, $name = null, $default = false)
	{
		if (!$name)
		{
			$name = strtolower($model->getName());
		}

		$this->models[$name] = $model;

		if ($default)
		{
			$this->defaultModel = $name;
		}
	}
}
