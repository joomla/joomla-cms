<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\View;

defined('_JEXEC') || die;

use ErrorException;
use Exception;
use FOF30\Container\Container;
use FOF30\Input\Input;
use FOF30\Model\Model;
use FOF30\View\Engine\EngineInterface;
use FOF30\View\Exception\CannotGetName;
use FOF30\View\Exception\EmptyStack;
use FOF30\View\Exception\ModelNotFound;
use FOF30\View\Exception\UnrecognisedExtension;
use Joomla\CMS\Language\Text;

/**
 * Class View
 *
 * A generic MVC view implementation
 *
 * @property-read  Input $input  The input object (magic __get returns the Input from the Container)
 */
class View
{
	public $baseurl = null;
	/**
	 * Current or most recently performed task.
	 * Currently public, it should be reduced to protected in the future
	 *
	 * @var  string
	 */
	public $task;
	/**
	 * The mapped task that was performed.
	 * Currently public, it should be reduced to protected in the future
	 *
	 * @var  string
	 */
	public $doTask;
	/**
	 * The name of the view
	 *
	 * @var    array
	 */
	protected $name = null;
	/**
	 * Registered models
	 *
	 * @var    array
	 */
	protected $modelInstances = [];
	/**
	 * The default model
	 *
	 * @var    string
	 */
	protected $defaultModel = null;
	/**
	 * Layout name
	 *
	 * @var    string
	 */
	protected $layout = 'default';
	/**
	 * Layout template
	 *
	 * @var    string
	 */
	protected $layoutTemplate = '_';
	/**
	 * The set of search directories for view templates
	 *
	 * @var   array
	 */
	protected $templatePaths = [];
	/**
	 * The name of the default template source file.
	 *
	 * @var   string
	 */
	protected $template = null;
	/**
	 * The output of the template script.
	 *
	 * @var   string
	 */
	protected $output = null;
	/**
	 * A cached copy of the configuration
	 *
	 * @var   array
	 */
	protected $config = [];
	/**
	 * The container attached to this view
	 *
	 * @var   Container
	 */
	protected $container;
	/**
	 * The object used to locate view templates in the filesystem
	 *
	 * @var   ViewTemplateFinder
	 */
	protected $viewFinder = null;
	/**
	 * Used when loading template files to avoid variable scope issues
	 *
	 * @var   null
	 */
	protected $_tempFilePath = null;
	/**
	 * Should I run the pre-render step?
	 *
	 * @var    boolean
	 */
	protected $doPreRender = true;

	/**
	 * Should I run the post-render step?
	 *
	 * @var    boolean
	 */
	protected $doPostRender = true;

	/**
	 * Maps view template extensions to view engine classes
	 *
	 * @var    array
	 */
	protected $viewEngineMap = [
		'.blade.php' => 'FOF30\\View\\Engine\\BladeEngine',
		'.php'       => 'FOF30\\View\\Engine\\PhpEngine',
	];

	/**
	 * All of the finished, captured sections.
	 *
	 * @var array
	 */
	protected $sections = [];

	/**
	 * The stack of in-progress sections.
	 *
	 * @var array
	 */
	protected $sectionStack = [];

	/**
	 * The number of active rendering operations.
	 *
	 * @var int
	 */
	protected $renderCount = 0;

	/**
	 * Aliases of view templates. For example:
	 *
	 * array('userProfile' => 'site://com_foobar/users/profile')
	 *
	 * allows you to do something like $this->loadAnyTemplate('userProfile') to display the frontend view template
	 * site://com_foobar/users/profile. You can also alias one view template with another, e.g.
	 * 'site://com_something/users/profile' => 'admin://com_foobar/clients/record'
	 *
	 * @var  array
	 */
	protected $viewTemplateAliases = [];

	/**
	 * Constructor.
	 *
	 * The $config array can contain the following overrides:
	 * name           string  The name of the view (defaults to the view class name)
	 * template_path  string  The path of the layout directory
	 * layout         string  The layout for displaying the view
	 * viewFinder     ViewTemplateFinder  The object used to locate view templates in the filesystem
	 * viewEngineMap  array   Maps view template extensions to view engine classes
	 *
	 * @param   Container  $container  The container we belong to
	 * @param   array      $config     The configuration overrides for the view
	 *
	 * @return  View
	 */
	public function __construct(Container $container, array $config = [])
	{
		$this->container = $container;

		$this->config = $config;

		// Get the view name
		if (isset($this->config['name']))
		{
			$this->name = $this->config['name'];
		}

		$this->name = $this->getName();

		// Set the default template search path
		if (array_key_exists('template_path', $this->config))
		{
			// User-defined dirs
			$this->setTemplatePath($this->config['template_path']);
		}
		else
		{
			$this->setTemplatePath($this->container->thisPath . '/View/' . ucfirst($this->name) . '/tmpl');
		}

		// Set the layout
		if (array_key_exists('layout', $this->config))
		{
			$this->setLayout($this->config['layout']);
		}

		// Apply the viewEngineMap
		if (isset($config['viewEngineMap']))
		{
			if (!is_array($config['viewEngineMap']))
			{
				$temp                    = explode(',', $config['viewEngineMap']);
				$config['viewEngineMap'] = [];

				foreach ($temp as $assignment)
				{
					$parts = explode('=>', $assignment, 2);

					if (count($parts) != 2)
					{
						continue;
					}

					$parts = array_map(function ($x) {
						return trim($x);
					}, $parts);

					$config['viewEngineMap'][$parts[0]] = $parts[1];
				}
			}

			$this->viewEngineMap = array_merge($this->viewEngineMap, $config['viewEngineMap']);
		}

		// Set the ViewFinder
		$this->viewFinder = $this->container->factory->viewFinder($this);

		if (isset($config['viewFinder']) && !empty($config['viewFinder']) && is_object($config['viewFinder']) && ($config['viewFinder'] instanceof ViewTemplateFinder))
		{
			$this->viewFinder = $config['viewFinder'];
		}

		// Apply the registered view template extensions to the view finder
		$this->viewFinder->setExtensions(array_keys($this->viewEngineMap));

		// Apply the base URL
		$this->baseurl = $this->container->platform->URIbase();
	}

	/**
	 * Magic get method. Handles magic properties:
	 * $this->input  mapped to $this->container->input
	 *
	 * @param   string  $name  The property to fetch
	 *
	 * @return  mixed|null
	 */
	public function __get($name)
	{
		// Handle $this->input
		if ($name == 'input')
		{
			return $this->container->input;
		}

		// Property not found; raise error
		$trace = debug_backtrace();
		trigger_error(
			'Undefined property via __get(): ' . $name .
			' in ' . $trace[0]['file'] .
			' on line ' . $trace[0]['line'],
			E_USER_NOTICE);

		return null;
	}

	/**
	 * Method to get the view name
	 *
	 * The model name by default parsed using the classname, or it can be set
	 * by passing a $config['name'] in the class constructor
	 *
	 * @return  string  The name of the model
	 *
	 * @throws  Exception
	 */
	public function getName()
	{
		if (empty($this->name))
		{
			$r = null;

			if (!preg_match('/(.*)\\\\View\\\\(.*)\\\\(.*)/i', get_class($this), $r))
			{
				throw new CannotGetName;
			}

			$this->name = $r[2];
		}

		return $this->name;
	}

	/**
	 * Escapes a value for output in a view script.
	 *
	 * @param   mixed  $var  The output to escape.
	 *
	 * @return  mixed  The escaped value.
	 */
	public function escape($var)
	{
		return htmlspecialchars($var, ENT_COMPAT, 'UTF-8');
	}

	/**
	 * Method to get data from a registered model or a property of the view
	 *
	 * @param   string  $property   The name of the method to call on the Model or the property to get
	 * @param   string  $default    The default value [optional]
	 * @param   string  $modelName  The name of the Model to reference [optional]
	 *
	 * @return  mixed  The return value of the method
	 */
	public function get($property, $default = null, $modelName = null)
	{
		// If $model is null we use the default model
		if (is_null($modelName))
		{
			$model = $this->defaultModel;
		}
		else
		{
			$model = $modelName;
		}

		// First check to make sure the model requested exists
		if (isset($this->modelInstances[$model]))
		{
			// Model exists, let's build the method name
			$method = 'get' . ucfirst($property);

			// Does the method exist?
			if (method_exists($this->modelInstances[$model], $method))
			{
				// The method exists, let's call it and return what we get
				$result = $this->modelInstances[$model]->$method();

				return $result;
			}
			else
			{
				$result = $this->modelInstances[$model]->$property();

				if (is_null($result))
				{
					return $default;
				}

				return $result;
			}
		}
		// If the model doesn't exist, try to fetch a View property
		else
		{
			if (@isset($this->$property))
			{
				return $this->$property;
			}
			else
			{
				return $default;
			}
		}
	}

	/**
	 * Returns a named Model object
	 *
	 * @param   string  $name    The Model name. If null we'll use the modelName
	 *                           variable or, if it's empty, the same name as
	 *                           the Controller
	 *
	 * @return  Model  The instance of the Model known to this Controller
	 */
	public function getModel($name = null)
	{
		if (!empty($name))
		{
			$modelName = $name;
		}
		elseif (!empty($this->defaultModel))
		{
			$modelName = $this->defaultModel;
		}
		else
		{
			$modelName = $this->name;
		}

		if (!array_key_exists($modelName, $this->modelInstances))
		{
			throw new ModelNotFound($modelName, $this->name);
		}

		return $this->modelInstances[$modelName];
	}

	/**
	 * Pushes the default Model to the View
	 *
	 * @param   Model  $model  The model to push
	 */
	public function setDefaultModel(Model &$model)
	{
		$name = $model->getName();

		$this->setDefaultModelName($name);
		$this->setModel($this->defaultModel, $model);
	}

	/**
	 * Set the name of the Model to be used by this View
	 *
	 * @param   string  $modelName  The name of the Model
	 *
	 * @return  void
	 */
	public function setDefaultModelName($modelName)
	{
		$this->defaultModel = $modelName;
	}

	/**
	 * Pushes a named model to the View
	 *
	 * @param   string  $modelName  The name of the Model
	 * @param   Model   $model      The actual Model object to push
	 *
	 * @return  void
	 */
	public function setModel($modelName, Model &$model)
	{
		$this->modelInstances[$modelName] = $model;
	}

	/**
	 * Overrides the default method to execute and display a template script.
	 * Instead of loadTemplate is uses loadAnyTemplate.
	 *
	 * @param   string  $tpl  The name of the template file to parse
	 *
	 * @return  boolean  True on success
	 *
	 * @throws  Exception  When the layout file is not found
	 */
	public function display($tpl = null)
	{
		$eventName = 'onBefore' . ucfirst($this->doTask);
		$this->triggerEvent($eventName, [$tpl]);

		$preRenderResult = '';

		if ($this->doPreRender)
		{
			@ob_start();
			$this->preRender();
			$preRenderResult = @ob_get_contents();
			@ob_end_clean();
		}

		$templateResult = $this->loadTemplate($tpl);

		$eventName = 'onAfter' . ucfirst($this->doTask);
		$this->triggerEvent($eventName, [$tpl]);

		if (is_object($templateResult) && ($templateResult instanceof Exception))
		{
			throw $templateResult;
		}

		echo $preRenderResult . $templateResult;

		if ($this->doPostRender)
		{
			$this->postRender();
		}

		return true;
	}

	/**
	 * Get the layout.
	 *
	 * @return  string  The layout name
	 */
	public function getLayout()
	{
		return $this->layout;
	}

	/**
	 * Sets the layout name to use
	 *
	 * @param   string  $layout  The layout name or a string in format <template>:<layout file>
	 *
	 * @return  string  Previous value.
	 */
	public function setLayout($layout)
	{
		$previous = $this->layout;

		if (is_null($layout))
		{
			$layout = 'default';
		}

		if (strpos($layout, ':') === false)
		{
			$this->layout = $layout;
		}
		else
		{
			// Convert parameter to array based on :
			$temp         = explode(':', $layout);
			$this->layout = $temp[1];

			// Set layout template
			$this->layoutTemplate = $temp[0];
		}

		return $previous;
	}

	/**
	 * Our function uses loadAnyTemplate to provide smarter view template loading.
	 *
	 * @param   string   $tpl     The name of the template file to parse
	 * @param   boolean  $strict  Should we use strict naming, i.e. force a non-empty $tpl?
	 *
	 * @return  mixed  A string if successful, otherwise an Exception
	 */
	public function loadTemplate($tpl = null, $strict = false)
	{
		$result = '';

		$uris = $this->viewFinder->getViewTemplateUris([
			'component' => $this->container->componentName,
			'view'      => $this->getName(),
			'layout'    => $this->getLayout(),
			'tpl'       => $tpl,
			'strictTpl' => $strict,
		]);

		foreach ($uris as $uri)
		{
			try
			{
				$result = $this->loadAnyTemplate($uri);

				break;
			}
			catch (Exception $e)
			{
				$result = $e;
			}
		}

		if ($result instanceof Exception)
		{
			$this->container->platform->raiseError($result->getCode(), $result->getMessage());
		}

		return $result;
	}

	/**
	 * Loads a template given any path. The path is in the format componentPart://componentName/viewName/layoutName,
	 * for example
	 * site:com_example/items/default
	 * admin:com_example/items/default_subtemplate
	 * auto:com_example/things/chair
	 * any:com_example/invoices/printpreview
	 *
	 * @param   string    $uri          The template path
	 * @param   array     $forceParams  A hash array of variables to be extracted in the local scope of the template
	 *                                  file
	 * @param   callable  $callback     A method to post-process the 3ναluα+3d view template (I use leetspeak here
	 *                                  because of bad quality hosts with broken scanners)
	 * @param   bool      $noOverride   If true we will not load Joomla! template overrides
	 *
	 * @return  string  The output of the template
	 *
	 * @throws  Exception  When the layout file is not found
	 */
	public function loadAnyTemplate($uri = '', $forceParams = [], $callback = null, $noOverride = false)
	{
		if (isset($this->viewTemplateAliases[$uri]))
		{
			$uri = $this->viewTemplateAliases[$uri];
		}

		$layoutTemplate = $this->getLayoutTemplate();

		$extraPaths = [];

		if (!empty($this->templatePaths))
		{
			$extraPaths = $this->templatePaths;
		}

		// First get the raw view template path
		$path = $this->viewFinder->resolveUriToPath($uri, $layoutTemplate, $extraPaths, $noOverride);

		// Now get the parsed view template path
		$this->_tempFilePath = $this->getEngine($path)->get($path, $forceParams);

		// We will keep track of the amount of views being rendered so we can flush
		// the section after the complete rendering operation is done. This will
		// clear out the sections for any separate views that may be rendered.
		$this->incrementRender();

		// Get the processed template
		$contents = $this->processTemplate($forceParams);

		// Once we've finished rendering the view, we'll decrement the render count
		// so that each sections get flushed out next time a view is created and
		// no old sections are staying around in the memory of an environment.
		$this->decrementRender();

		$response = isset($callback) ? $callback($this, $contents) : null;

		if (!is_null($response))
		{
			$contents = $response;
		}

		// Once we have the contents of the view, we will flush the sections if we are
		// done rendering all views so that there is nothing left hanging over when
		// another view gets rendered in the future by the application developer.
		$this->flushSectionsIfDoneRendering();

		return $contents;
	}

	/**
	 * Increment the rendering counter.
	 *
	 * @return void
	 */
	public function incrementRender()
	{
		$this->renderCount++;
	}

	/**
	 * Decrement the rendering counter.
	 *
	 * @return void
	 */
	public function decrementRender()
	{
		$this->renderCount--;
	}

	/**
	 * Check if there are no active render operations.
	 *
	 * @return bool
	 */
	public function doneRendering()
	{
		return $this->renderCount == 0;
	}

	/**
	 * Go through a data array and render a subtemplate against each record (think master-detail views). This is
	 * accessible through Blade templates as @each
	 *
	 * @param   string  $viewTemplate  The view template to use for each subitem, format
	 *                                 componentPart://componentName/viewName/layoutName
	 * @param   array   $data          The array of data you want to render. It can be a DataModel\Collection, array,
	 *                                 ...
	 * @param   string  $eachItemName  How to call each item in the loaded subtemplate (passed through $forceParams)
	 * @param   string  $empty         What to display if the array is empty
	 *
	 * @return string
	 */
	public function renderEach($viewTemplate, $data, $eachItemName, $empty = 'raw|')
	{
		$result = '';

		// If is actually data in the array, we will loop through the data and append
		// an instance of the partial view to the final result HTML passing in the
		// iterated value of this data array, allowing the views to access them.
		if (count($data) > 0)
		{
			foreach ($data as $key => $value)
			{
				$data = ['key' => $key, $eachItemName => $value];

				$result .= $this->loadAnyTemplate($viewTemplate, $data);
			}
		}
		// If there is no data in the array, we will render the contents of the empty
		// view. Alternatively, the "empty view" could be a raw string that begins
		// with "raw|" for convenience and to let this know that it is a string. Or
		// a language string starting with text|.
		else
		{
			if (starts_with($empty, 'raw|'))
			{
				$result = substr($empty, 4);
			}
			elseif (starts_with($empty, 'text|'))
			{
				$result = Text::_(substr($empty, 5));
			}
			else
			{
				$result = $this->loadAnyTemplate($empty);
			}
		}

		return $result;
	}

	/**
	 * Start injecting content into a section.
	 *
	 * @param   string  $section
	 * @param   string  $content
	 *
	 * @return void
	 */
	public function startSection($section, $content = '')
	{
		if ($content === '')
		{
			if (ob_start())
			{
				$this->sectionStack[] = $section;
			}
		}
		else
		{
			$this->extendSection($section, $content);
		}
	}

	/**
	 * Stop injecting content into a section and return its contents.
	 *
	 * @return string
	 */
	public function yieldSection()
	{
		return $this->yieldContent($this->stopSection());
	}

	/**
	 * Stop injecting content into a section.
	 *
	 * @param   bool  $overwrite
	 *
	 * @return string
	 */
	public function stopSection($overwrite = false)
	{
		if (empty($this->sectionStack))
		{
			// Let's close the output buffering
			ob_get_clean();

			throw new EmptyStack();
		}

		$last = array_pop($this->sectionStack);

		if ($overwrite)
		{
			$this->sections[$last] = ob_get_clean();
		}
		else
		{
			$this->extendSection($last, ob_get_clean());
		}

		return $last;
	}

	/**
	 * Stop injecting content into a section and append it.
	 *
	 * @return string
	 */
	public function appendSection()
	{
		if (empty($this->sectionStack))
		{
			// Let's close the output buffering
			ob_get_clean();

			throw new EmptyStack();
		}

		$last = array_pop($this->sectionStack);

		if (isset($this->sections[$last]))
		{
			$this->sections[$last] .= ob_get_clean();
		}
		else
		{
			$this->sections[$last] = ob_get_clean();
		}

		return $last;
	}

	/**
	 * Get the string contents of a section.
	 *
	 * @param   string  $section
	 * @param   string  $default
	 *
	 * @return string
	 */
	public function yieldContent($section, $default = '')
	{
		$sectionContent = $default;

		if (isset($this->sections[$section]))
		{
			$sectionContent = $this->sections[$section];
		}

		return str_replace('@parent', '', $sectionContent);
	}

	/**
	 * Flush all of the section contents.
	 *
	 * @return void
	 */
	public function flushSections()
	{
		$this->sections = [];

		$this->sectionStack = [];
	}

	/**
	 * Flush all of the section contents if done rendering.
	 *
	 * @return void
	 */
	public function flushSectionsIfDoneRendering()
	{
		if ($this->doneRendering())
		{
			$this->flushSections();
		}
	}

	/**
	 * Get the layout template.
	 *
	 * @return  string  The layout template name
	 */
	public function getLayoutTemplate()
	{
		return $this->layoutTemplate;
	}

	/**
	 * Load a helper file
	 *
	 * @param   string  $helperClass   The last part of the name of the helper
	 *                                 class.
	 *
	 * @return  void
	 *
	 * @deprecated  3.0  Just use the class in your code. That's what the autoloader is for.
	 */
	public function loadHelper($helperClass = null)
	{
		// Get the helper class name
		$className = '\\' . $this->container->getNamespacePrefix() . 'Helper\\' . ucfirst($helperClass);

		// This trick autoloads the helper class. We can't instantiate it as
		// helpers are (supposed to be) abstract classes with static method
		// interfaces.
		class_exists($className);
	}

	/**
	 * Returns a reference to the container attached to this View
	 *
	 * @return Container
	 */
	public function &getContainer()
	{
		return $this->container;
	}

	public function getTask()
	{
		return $this->task;
	}

	/**
	 * @param   string  $task
	 *
	 * @return  $this   This for chaining
	 */
	public function setTask($task)
	{
		$this->task = $task;

		return $this;
	}

	public function getDoTask()
	{
		return $this->doTask;
	}

	/**
	 * @param   string  $task
	 *
	 * @return  $this   This for chaining
	 */
	public function setDoTask($task)
	{
		$this->doTask = $task;

		return $this;
	}

	/**
	 * Sets the pre-render flag
	 *
	 * @param   boolean  $value  True to enable the pre-render step
	 *
	 * @return  void
	 */
	public function setPreRender($value)
	{
		$this->doPreRender = $value;
	}

	/**
	 * Sets the post-render flag
	 *
	 * @param   boolean  $value  True to enable the post-render step
	 *
	 * @return  void
	 */
	public function setPostRender($value)
	{
		$this->doPostRender = $value;
	}

	/**
	 * Add an alias for a view template.
	 *
	 * @param   string  $viewTemplate  Existing view template, in the format
	 *                                 componentPart://componentName/viewName/layoutName
	 * @param   string  $alias         The alias of the view template (any string will do)
	 *
	 * @return void
	 */
	public function alias($viewTemplate, $alias)
	{
		$this->viewTemplateAliases[$alias] = $viewTemplate;
	}

	/**
	 * Add a JS script file to the page generated by the CMS.
	 *
	 * There are three combinations of defer and async (see http://www.w3schools.com/tags/att_script_defer.asp):
	 * * $defer false, $async true: The script is executed asynchronously with the rest of the page
	 *   (the script will be executed while the page continues the parsing)
	 * * $defer true, $async false: The script is executed when the page has finished parsing.
	 * * $defer false, $async false. (default) The script is loaded and executed immediately. When it finishes
	 *   loading the browser continues parsing the rest of the page.
	 *
	 * When you are using $defer = true there is no guarantee about the load order of the scripts. Whichever
	 * script loads first will be executed first. The order they appear on the page is completely irrelevant.
	 *
	 * @param   string   $uri      A path definition understood by parsePath, e.g. media://com_example/js/foo.js
	 * @param   string   $version  (optional) Version string to be added to the URL
	 * @param   string   $type     MIME type of the script
	 * @param   boolean  $defer    Adds the defer attribute, see above
	 * @param   boolean  $async    Adds the async attribute, see above
	 *
	 * @return  $this  Self, for chaining
	 */
	public function addJavascriptFile($uri, $version = null, $type = 'text/javascript', $defer = false, $async = false)
	{
		// Add an automatic version if $version is null. For no version parameter pass an empty string to $version.
		if (is_null($version))
		{
			$version = $this->container->mediaVersion;
		}

		$this->container->template->addJS($uri, $defer, $async, $version, $type);

		return $this;
	}

	/**
	 * Adds an inline JavaScript script to the page header
	 *
	 * @param   string  $script  The script content to add
	 * @param   string  $type    The MIME type of the script
	 *
	 * @return  $this  Self, for chaining
	 */
	public function addJavascriptInline($script, $type = 'text/javascript')
	{
		$this->container->template->addJSInline($script, $type);

		return $this;
	}

	/**
	 * Add a CSS file to the page generated by the CMS
	 *
	 * @param   string  $uri      A path definition understood by parsePath, e.g. media://com_example/css/foo.css
	 * @param   string  $version  (optional) Version string to be added to the URL
	 * @param   string  $type     MIME type of the stylesheeet
	 * @param   string  $media    Media target definition of the style sheet, e.g. "screen"
	 * @param   array   $attribs  Array of attributes
	 *
	 * @return  $this  Self, for chaining
	 */
	public function addCssFile($uri, $version = null, $type = 'text/css', $media = null, $attribs = [])
	{
		// Add an automatic version if $version is null. For no version parameter pass an empty string to $version.
		if (is_null($version))
		{
			$version = $this->container->mediaVersion;
		}

		$this->container->template->addCSS($uri, $version, $type, $media, $attribs);

		return $this;
	}

	/**
	 * Adds an inline stylesheet (inline CSS) to the page header
	 *
	 * @param   string  $css   The stylesheet content to add
	 * @param   string  $type  The MIME type of the script
	 *
	 * @return  $this  Self, for chaining
	 */
	public function addCssInline($css, $type = 'text/css')
	{
		$this->container->template->addCSSInline($css, $type);

		return $this;
	}

	/**
	 * Sets an entire array of search paths for templates or resources.
	 *
	 * @param   mixed  $path  The new search path, or an array of search paths.  If null or false, resets to the current
	 *                        directory only.
	 *
	 * @return  void
	 */
	protected function setTemplatePath($path)
	{
		// Clear out the prior search dirs
		$this->templatePaths = [];

		// Actually add the user-specified directories
		$this->addTemplatePath($path);

		// Set the alternative template search dir
		$templatePath = JPATH_THEMES;
		$fallback     = $templatePath . '/' . $this->container->platform->getTemplate() . '/html/' . $this->container->componentName . '/' . $this->name;
		$this->addTemplatePath($fallback);

		// Get extra directories through event dispatchers
		$extraPathsResults = $this->container->platform->runPlugins('onGetViewTemplatePaths', [
			$this->container->componentName,
			$this->getName(),
		]);

		if (is_array($extraPathsResults) && !empty($extraPathsResults))
		{
			foreach ($extraPathsResults as $somePaths)
			{
				if (!empty($somePaths))
				{
					foreach ($somePaths as $aPath)
					{
						$this->addTemplatePath($aPath);
					}
				}
			}
		}
	}

	/**
	 * Adds to the search path for templates and resources.
	 *
	 * @param   mixed  $path  The directory or stream, or an array of either, to search.
	 *
	 * @return  void
	 */
	protected function addTemplatePath($path)
	{
		// Just force to array
		$path = (array) $path;

		// Loop through the path directories
		foreach ($path as $dir)
		{
			// No surrounding spaces allowed!
			$dir = trim($dir);

			// Add trailing separators as needed
			if (substr($dir, -1) != DIRECTORY_SEPARATOR)
			{
				// Directory
				$dir .= DIRECTORY_SEPARATOR;
			}

			// Add to the top of the search dirs
			array_unshift($this->templatePaths, $dir);
		}
	}

	/**
	 * Append content to a given section.
	 *
	 * @param   string  $section
	 * @param   string  $content
	 *
	 * @return void
	 */
	protected function extendSection($section, $content)
	{
		if (isset($this->sections[$section]))
		{
			$content = str_replace('@parent', $content, $this->sections[$section]);
		}

		$this->sections[$section] = $content;
	}

	/**
	 * Evaluates the template described in the _tempFilePath property
	 *
	 * @param   array  $forceParams  Forced parameters
	 *
	 * @return string
	 * @throws Exception
	 */
	protected function processTemplate(array &$forceParams)
	{
		// If the engine returned raw content, return the raw content immediately
		if ($this->_tempFilePath['type'] == 'raw')
		{
			return $this->_tempFilePath['content'];
		}

		if (substr($this->_tempFilePath['content'], 0, 4) == 'raw|')
		{
			return substr($this->_tempFilePath['content'], 4);
		}

		$obLevel = ob_get_level();

		ob_start();

		// We'll process the contents of the view inside a try/catch block so we can
		// flush out any stray output that might get out before an error occurs or
		// an exception is thrown. This prevents any partial views from leaking.
		try
		{
			$this->includeTemplateFile($forceParams);
		}
		catch (Exception $e)
		{
			$this->handleViewException($e, $obLevel);
		}

		return ob_get_clean();
	}

	/**
	 * Handle a view exception.
	 *
	 * @param   Exception  $e        The exception to handle
	 * @param   int        $obLevel  The target output buffering level
	 *
	 * @return  void
	 *
	 * @throws  $e
	 */
	protected function handleViewException(Exception $e, $obLevel)
	{
		while (ob_get_level() > $obLevel)
		{
			ob_end_clean();
		}

		$message = $e->getMessage() . ' (View template: ' . realpath($this->_tempFilePath['content']) . ')';

		$newException = new ErrorException($message, 0, 1, $e->getFile(), $e->getLine(), $e);

		throw $newException;
	}

	/**
	 * Get the appropriate view engine for the given view template path.
	 *
	 * @param   string  $path  The path of the view template
	 *
	 * @return  EngineInterface
	 *
	 * @throws  UnrecognisedExtension
	 */
	protected function getEngine($path)
	{
		foreach ($this->viewEngineMap as $extension => $engine)
		{
			if (substr($path, -strlen($extension)) == $extension)
			{
				return new $engine($this);
			}
		}

		throw new UnrecognisedExtension($path);
	}

	/**
	 * Get the extension used by the view file.
	 *
	 * @param   string  $path
	 *
	 * @return string
	 */
	protected function getExtension($path)
	{
		$extensions = array_keys($this->viewEngineMap);

		return array_first($extensions, function ($key, $value) use ($path) {
			return ends_with($path, $value);
		});
	}

	/**
	 * Triggers an object-specific event. The event runs both locally –if a suitable method exists– and through the
	 * Joomla! plugin system. A true/false return value is expected. The first false return cancels the event.
	 *
	 * EXAMPLE
	 * Component: com_foobar, Object name: item, Event: onBeforeSomething, Arguments: array(123, 456)
	 * The event calls:
	 * 1. $this->onBeforeSomething(123, 456)
	 * 2. Joomla! plugin event onComFoobarViewItemBeforeSomething($this, 123, 456)
	 *
	 * @param   string  $event      The name of the event, typically named onPredicateVerb e.g. onBeforeKick
	 * @param   array   $arguments  The arguments to pass to the event handlers
	 *
	 * @return  bool
	 */
	protected function triggerEvent($event, array $arguments = [])
	{
		$result = true;

		// If there is an object method for this event, call it
		if (method_exists($this, $event))
		{
			switch (count($arguments))
			{
				case 0:
					$result = $this->{$event}();
					break;
				case 1:
					$result = $this->{$event}($arguments[0]);
					break;
				case 2:
					$result = $this->{$event}($arguments[0], $arguments[1]);
					break;
				case 3:
					$result = $this->{$event}($arguments[0], $arguments[1], $arguments[2]);
					break;
				case 4:
					$result = $this->{$event}($arguments[0], $arguments[1], $arguments[2], $arguments[3]);
					break;
				case 5:
					$result = $this->{$event}($arguments[0], $arguments[1], $arguments[2], $arguments[3], $arguments[4]);
					break;
				default:
					$result = call_user_func_array([$this, $event], $arguments);
					break;
			}
		}

		if ($result === false)
		{
			return false;
		}

		// All other event handlers live outside this object, therefore they need to be passed a reference to this
		// objects as the first argument.
		array_unshift($arguments, $this);

		// If we have an "on" prefix for the event (e.g. onFooBar) remove it and stash it for later.
		$prefix = '';

		if (substr($event, 0, 2) == 'on')
		{
			$prefix = 'on';
			$event  = substr($event, 2);
		}

		// Get the component/model prefix for the event
		$prefix .= 'Com' . ucfirst($this->container->bareComponentName) . 'View';
		$prefix .= ucfirst($this->getName());

		// The event name will be something like onComFoobarItemsBeforeSomething
		$event = $prefix . $event;

		// Call the Joomla! plugins
		$results = $this->container->platform->runPlugins($event, $arguments);

		if (!empty($results))
		{
			foreach ($results as $result)
			{
				if ($result === false)
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Runs before rendering the view template, echoing HTML to put before the
	 * view template's generated HTML
	 *
	 * @return void
	 */
	protected function preRender()
	{
		// You need to implement this in children classes
	}

	/**
	 * Runs after rendering the view template, echoing HTML to put after the
	 * view template's generated HTML
	 *
	 * @return  void
	 */
	protected function postRender()
	{
		// You need to implement this in children classes
	}

	/**
	 * This method makes sure the current scope isn't polluted with variables when including a view template
	 *
	 * @param   array  $forceParams  Forced parameters
	 *
	 * @return  void
	 */
	private function includeTemplateFile(array &$forceParams)
	{
		// Extract forced parameters
		if (!empty($forceParams))
		{
			extract($forceParams);
		}

		include $this->_tempFilePath['content'];
	}
}
