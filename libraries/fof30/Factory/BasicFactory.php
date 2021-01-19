<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Factory;

defined('_JEXEC') || die;

use Exception;
use FOF30\Container\Container;
use FOF30\Controller\Controller;
use FOF30\Dispatcher\Dispatcher;
use FOF30\Factory\Exception\ControllerNotFound;
use FOF30\Factory\Exception\DispatcherNotFound;
use FOF30\Factory\Exception\ModelNotFound;
use FOF30\Factory\Exception\ToolbarNotFound;
use FOF30\Factory\Exception\TransparentAuthenticationNotFound;
use FOF30\Factory\Exception\ViewNotFound;
use FOF30\Model\Model;
use FOF30\Toolbar\Toolbar;
use FOF30\TransparentAuthentication\TransparentAuthentication;
use FOF30\View\View;
use FOF30\View\ViewTemplateFinder;
use RuntimeException;

/**
 * MVC object factory. This implements the basic functionality, i.e. creating MVC objects only if the classes exist in
 * the same component section (front-end, back-end) you are currently running in. The Dispatcher and Toolbar will be
 * created from default objects if specialised classes are not found in your application.
 */
class BasicFactory implements FactoryInterface
{
	/** @var  Container  The container we belong to */
	protected $container = null;

	/**
	 * Section used to build the namespace prefix.
	 *
	 * @var   string
	 */
	protected $section = 'auto';

	/**
	 * Public constructor for the factory object
	 *
	 * @param   Container  $container  The container we belong to
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * Create a new Controller object
	 *
	 * @param   string  $viewName  The name of the view we're getting a Controller for.
	 * @param   array   $config    Optional MVC configuration values for the Controller object.
	 *
	 * @return  Controller
	 */
	public function controller($viewName, array $config = [])
	{
		$controllerClass = $this->container->getNamespacePrefix($this->getSection()) . 'Controller\\' . ucfirst($viewName);

		try
		{
			return $this->createController($controllerClass, $config);
		}
		catch (ControllerNotFound $e)
		{
		}

		$controllerClass = $this->container->getNamespacePrefix($this->getSection()) . 'Controller\\' . ucfirst($this->container->inflector->singularize($viewName));

		$controller = $this->createController($controllerClass, $config);

		return $controller;
	}

	/**
	 * Create a new Model object
	 *
	 * @param   string  $viewName  The name of the view we're getting a Model for.
	 * @param   array   $config    Optional MVC configuration values for the Model object.
	 *
	 * @return  Model
	 */
	public function model($viewName, array $config = [])
	{
		$modelClass = $this->container->getNamespacePrefix($this->getSection()) . 'Model\\' . ucfirst($viewName);

		try
		{
			return $this->createModel($modelClass, $config);
		}
		catch (ModelNotFound $e)
		{
		}

		$modelClass = $this->container->getNamespacePrefix($this->getSection()) . 'Model\\' . ucfirst($this->container->inflector->singularize($viewName));

		$model = $this->createModel($modelClass, $config);

		return $model;
	}

	/**
	 * Create a new View object
	 *
	 * @param   string  $viewName  The name of the view we're getting a View object for.
	 * @param   string  $viewType  The type of the View object. By default it's "html".
	 * @param   array   $config    Optional MVC configuration values for the View object.
	 *
	 * @return  View
	 */
	public function view($viewName, $viewType = 'html', array $config = [])
	{
		$container = $this->container;
		$prefix    = $this->container->getNamespacePrefix($this->getSection());

		$viewClass = $prefix . 'View\\' . ucfirst($viewName) . '\\' . ucfirst($viewType);

		try
		{
			return $this->createView($viewClass, $config);
		}
		catch (ViewNotFound $e)
		{
		}

		$viewClass = $prefix . 'View\\' . ucfirst($container->inflector->singularize($viewName)) . '\\' . ucfirst($viewType);

		$view = $this->createView($viewClass, $config);

		return $view;
	}

	/**
	 * Creates a new Dispatcher
	 *
	 * @param   array  $config  The configuration values for the Dispatcher object
	 *
	 * @return  Dispatcher
	 */
	public function dispatcher(array $config = [])
	{
		$dispatcherClass = $this->container->getNamespacePrefix($this->getSection()) . 'Dispatcher\\Dispatcher';

		try
		{
			return $this->createDispatcher($dispatcherClass, $config);
		}
		catch (DispatcherNotFound $e)
		{
			// Not found. Return the default Dispatcher
			return new Dispatcher($this->container, $config);
		}
	}

	/**
	 * Creates a new Toolbar
	 *
	 * @param   array  $config  The configuration values for the Toolbar object
	 *
	 * @return  Toolbar
	 */
	public function toolbar(array $config = [])
	{
		$toolbarClass = $this->container->getNamespacePrefix($this->getSection()) . 'Toolbar\\Toolbar';

		try
		{
			return $this->createToolbar($toolbarClass, $config);
		}
		catch (ToolbarNotFound $e)
		{
			// Not found. Return the default Toolbar
			return new Toolbar($this->container, $config);
		}
	}

	/**
	 * Creates a new TransparentAuthentication handler
	 *
	 * @param   array  $config  The configuration values for the TransparentAuthentication object
	 *
	 * @return  TransparentAuthentication
	 */
	public function transparentAuthentication(array $config = [])
	{
		$authClass = $this->container->getNamespacePrefix($this->getSection()) . 'TransparentAuthentication\\TransparentAuthentication';

		try
		{
			return $this->createTransparentAuthentication($authClass, $config);
		}
		catch (TransparentAuthenticationNotFound $e)
		{
			// Not found. Return the default TA
			return new TransparentAuthentication($this->container, $config);
		}
	}

	/**
	 * Creates a view template finder object for a specific View
	 *
	 * The default configuration is:
	 * Look for .php, .blade.php files; default layout "default"; no default sub-template;
	 * look only for the specified view; do NOT fall back to the default layout or sub-template;
	 * look for templates ONLY in site or admin, depending on where we're running from
	 *
	 * @param   View   $view    The view this view template finder will be attached to
	 * @param   array  $config  Configuration variables for the object
	 *
	 * @return  ViewTemplateFinder
	 *
	 * @throws Exception
	 */
	public function viewFinder(View $view, array $config = [])
	{
		// Initialise the configuration with the default values
		$defaultConfig = [
			'extensions'    => ['.php', '.blade.php'],
			'defaultLayout' => 'default',
			'defaultTpl'    => '',
			'strictView'    => true,
			'strictTpl'     => true,
			'strictLayout'  => true,
			'sidePrefix'    => 'auto',
		];

		$config = array_merge($defaultConfig, $config);

		// Apply fof.xml overrides
		$appConfig = $this->container->appConfig;
		$key       = "views." . ucfirst($view->getName()) . ".config";

		$fofXmlConfig = [
			'extensions'   => $appConfig->get("$key.templateExtensions", $config['extensions']),
			'strictView'   => $appConfig->get("$key.templateStrictView", $config['strictView']),
			'strictTpl'    => $appConfig->get("$key.templateStrictTpl", $config['strictTpl']),
			'strictLayout' => $appConfig->get("$key.templateStrictLayout", $config['strictLayout']),
			'sidePrefix'   => $appConfig->get("$key.templateLocation", $config['sidePrefix']),
		];

		$config = array_merge($config, $fofXmlConfig);

		// Create the new view template finder object
		return new ViewTemplateFinder($view, $config);
	}

	/**
	 * @return string
	 */
	public function getSection()
	{
		return $this->section;
	}

	/**
	 * @param   string  $section
	 */
	public function setSection($section)
	{
		$this->section = $section;
	}

	/**
	 * Is scaffolding enabled?
	 *
	 * @return  bool
	 *
	 * @deprecated  3.6.0  Always returns false
	 */
	public function isScaffolding()
	{
		return false;
	}

	/**
	 * Set the scaffolding status
	 *
	 * @param   bool  $scaffolding
	 *
	 * @deprecated  Removed since 3.6.0, does nothing
	 */
	public function setScaffolding($scaffolding): void
	{
		// Ignored
	}

	/**
	 * Is saving the scaffolding result to disk enabled?
	 *
	 * @return   bool
	 *
	 * @deprecated  3.6.0  Always returns false
	 */
	public function isSaveScaffolding()
	{
		return false;
	}

	/**
	 * Set the status of saving the scaffolding result to disk.
	 *
	 * @param   bool  $saveScaffolding
	 *
	 * @deprecated  3.6.0  Does nothing
	 */
	public function setSaveScaffolding($saveScaffolding)
	{
		// Ignored
	}

	/**
	 * Should we save controller to disk?
	 *
	 * @param   bool  $state
	 *
	 * @deprecated  3.6.0  Does nothing
	 */
	public function setSaveControllerScaffolding($state)
	{
		// Ignored
	}

	/**
	 * Should we save controller scaffolding to disk?
	 *
	 * @return  bool  $state
	 *
	 * @deprecated  3.6.0  Always returns false
	 */
	public function isSaveControllerScaffolding()
	{
		return false;
	}

	/**
	 * Should we save model to disk?
	 *
	 * @param   bool  $state
	 *
	 * @deprecated  3.6.0  Does nothing
	 */
	public function setSaveModelScaffolding($state)
	{
		// Ignored
	}

	/**
	 * Should we save model scaffolding to disk?
	 *
	 * @return  bool  $state
	 *
	 * @deprecated  3.6.0  Always returns false
	 */
	public function isSaveModelScaffolding()
	{
		return false;
	}

	/**
	 * Should we save view to disk?
	 *
	 * @param   bool  $state
	 *
	 * @deprecated  3.6.0  Does nothing
	 */
	public function setSaveViewScaffolding($state)
	{
		// Ignored
	}

	/**
	 * Should we save view scaffolding to disk?
	 *
	 * @return  bool  $state
	 *
	 * @deprecated  3.6.0  Always returns false
	 */
	public function isSaveViewScaffolding()
	{
		return false;
	}

	/**
	 * Creates a Controller object
	 *
	 * @param   string  $controllerClass  The fully qualified class name for the Controller
	 * @param   array   $config           Optional MVC configuration values for the Controller object.
	 *
	 * @return  Controller
	 *
	 * @throws  RuntimeException  If the $controllerClass does not exist
	 */
	protected function createController($controllerClass, array $config = [])
	{
		if (!class_exists($controllerClass))
		{
			throw new ControllerNotFound($controllerClass);
		}

		return new $controllerClass($this->container, $config);
	}

	/**
	 * Creates a Model object
	 *
	 * @param   string  $modelClass  The fully qualified class name for the Model
	 * @param   array   $config      Optional MVC configuration values for the Model object.
	 *
	 * @return  Model
	 *
	 * @throws  RuntimeException  If the $modelClass does not exist
	 */
	protected function createModel($modelClass, array $config = [])
	{
		if (!class_exists($modelClass))
		{
			throw new ModelNotFound($modelClass);
		}

		return new $modelClass($this->container, $config);
	}

	/**
	 * Creates a View object
	 *
	 * @param   string  $viewClass  The fully qualified class name for the View
	 * @param   array   $config     Optional MVC configuration values for the View object.
	 *
	 * @return  View
	 *
	 * @throws  RuntimeException  If the $viewClass does not exist
	 */
	protected function createView($viewClass, array $config = [])
	{
		if (!class_exists($viewClass))
		{
			throw new ViewNotFound($viewClass);
		}

		return new $viewClass($this->container, $config);
	}

	/**
	 * Creates a Toolbar object
	 *
	 * @param   string  $toolbarClass  The fully qualified class name for the Toolbar
	 * @param   array   $config        The configuration values for the Toolbar object
	 *
	 * @return  Toolbar
	 *
	 * @throws  RuntimeException  If the $toolbarClass does not exist
	 */
	protected function createToolbar($toolbarClass, array $config = [])
	{
		if (!class_exists($toolbarClass))
		{
			throw new ToolbarNotFound($toolbarClass);
		}

		return new $toolbarClass($this->container, $config);
	}

	/**
	 * Creates a Dispatcher object
	 *
	 * @param   string  $dispatcherClass  The fully qualified class name for the Dispatcher
	 * @param   array   $config           The configuration values for the Dispatcher object
	 *
	 * @return  Dispatcher
	 *
	 * @throws  RuntimeException  If the $dispatcherClass does not exist
	 */
	protected function createDispatcher($dispatcherClass, array $config = [])
	{
		if (!class_exists($dispatcherClass))
		{
			throw new DispatcherNotFound($dispatcherClass);
		}

		return new $dispatcherClass($this->container, $config);
	}

	/**
	 * Creates a TransparentAuthentication object
	 *
	 * @param   string  $authClass  The fully qualified class name for the TransparentAuthentication
	 * @param   array   $config     The configuration values for the TransparentAuthentication object
	 *
	 * @return  TransparentAuthentication
	 *
	 * @throws  RuntimeException  If the $authClass does not exist
	 */
	protected function createTransparentAuthentication($authClass, $config)
	{
		if (!class_exists($authClass))
		{
			throw new TransparentAuthenticationNotFound($authClass);
		}

		return new $authClass($this->container, $config);
	}
}
