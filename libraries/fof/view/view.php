<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  view
 * @copyright   Copyright (C) 2010 - 2014 Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('FOF_INCLUDED') or die;

/**
 * FrameworkOnFramework View class. The View is the MVC component which gets the
 * raw data from a Model and renders it in a way that makes sense. The usual
 * rendering is HTML, but you can also output JSON, CSV, XML, or even media
 * (images, videos, ...) and documents (Word, PDF, Excel...).
 *
 * @package  FrameworkOnFramework
 * @since    1.0
 */
abstract class FOFView extends FOFUtilsObject
{
	/**
	 * The name of the view
	 *
	 * @var    array
	 */
	protected $_name = null;

	/**
	 * Registered models
	 *
	 * @var    array
	 */
	protected $_models = array();

	/**
	 * The base path of the view
	 *
	 * @var    string
	 */
	protected $_basePath = null;

	/**
	 * The default model
	 *
	 * @var	string
	 */
	protected $_defaultModel = null;

	/**
	 * Layout name
	 *
	 * @var    string
	 */
	protected $_layout = 'default';

	/**
	 * Layout extension
	 *
	 * @var    string
	 */
	protected $_layoutExt = 'php';

	/**
	 * Layout template
	 *
	 * @var    string
	 */
	protected $_layoutTemplate = '_';

	/**
	 * The set of search directories for resources (templates)
	 *
	 * @var array
	 */
	protected $_path = array('template' => array(), 'helper' => array());

	/**
	 * The name of the default template source file.
	 *
	 * @var string
	 */
	protected $_template = null;

	/**
	 * The output of the template script.
	 *
	 * @var string
	 */
	protected $_output = null;

	/**
	 * Callback for escaping.
	 *
	 * @var string
	 * @deprecated 13.3
	 */
	protected $_escape = 'htmlspecialchars';

	/**
	 * Charset to use in escaping mechanisms; defaults to urf8 (UTF-8)
	 *
	 * @var string
	 */
	protected $_charset = 'UTF-8';

	/**
	 * The available renderer objects we can use to render views
	 *
	 * @var    array  Contains objects of the FOFRenderAbstract class
	 */
	public static $renderers = array();

	/**
	 * Cache of the configuration array
	 *
	 * @var    array
	 */
	protected $config = array();

	/**
	 * The input object of this view
	 *
	 * @var    FOFInput
	 */
	protected $input = null;

	/**
	 * The chosen renderer object
	 *
	 * @var    FOFRenderAbstract
	 */
	protected $rendererObject = null;

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
	 * Public constructor. Instantiates a FOFView object.
	 *
	 * @param   array  $config  The configuration data array
	 */
	public function __construct($config = array())
	{
		// Make sure $config is an array
		if (is_object($config))
		{
			$config = (array) $config;
		}
		elseif (!is_array($config))
		{
			$config = array();
		}

		// Get the input
		if (array_key_exists('input', $config))
		{
			if ($config['input'] instanceof FOFInput)
			{
				$this->input = $config['input'];
			}
			else
			{
				$this->input = new FOFInput($config['input']);
			}
		}
		else
		{
			$this->input = new FOFInput;
		}

		parent::__construct($config);

		$component = 'com_foobar';

		// Get the component name
		if (array_key_exists('input', $config))
		{
			if ($config['input'] instanceof FOFInput)
			{
				$tmpInput = $config['input'];
			}
			else
			{
				$tmpInput = new FOFInput($config['input']);
			}

			$component = $tmpInput->getCmd('option', '');
		}
		else
		{
			$tmpInput = $this->input;
		}

		if (array_key_exists('option', $config))
		{
			if ($config['option'])
			{
				$component = $config['option'];
			}
		}

		$config['option'] = $component;

		// Get the view name
		$view = null;
		if (array_key_exists('input', $config))
		{
			$view = $tmpInput->getCmd('view', '');
		}

		if (array_key_exists('view', $config))
		{
			if ($config['view'])
			{
				$view = $config['view'];
			}
		}

		$config['view'] = $view;

		// Set the component and the view to the input array

		if (array_key_exists('input', $config))
		{
			$tmpInput->set('option', $config['option']);
			$tmpInput->set('view', $config['view']);
		}

		// Set the view name

		if (array_key_exists('name', $config))
		{
			$this->_name = $config['name'];
		}
		else
		{
			$this->_name = $config['view'];
		}

		$tmpInput->set('view', $this->_name);
		$config['input'] = $tmpInput;
		$config['name'] = $this->_name;
		$config['view'] = $this->_name;

		// Get the component directories
		$componentPaths = FOFPlatform::getInstance()->getComponentBaseDirs($config['option']);

		// Set the charset (used by the variable escaping functions)

		if (array_key_exists('charset', $config))
		{
			FOFPlatform::getInstance()->logDeprecated('Setting a custom charset for escaping in FOFView\'s constructor is deprecated. Override FOFView::escape() instead.');
			$this->_charset = $config['charset'];
		}

		// User-defined escaping callback

		if (array_key_exists('escape', $config))
		{
			$this->setEscape($config['escape']);
		}

		// Set a base path for use by the view

		if (array_key_exists('base_path', $config))
		{
			$this->_basePath = $config['base_path'];
		}
		else
		{
			$this->_basePath = $componentPaths['main'];
		}

		// Set the default template search path

		if (array_key_exists('template_path', $config))
		{
			// User-defined dirs
			$this->_setPath('template', $config['template_path']);
		}
		else
		{
			$altView = FOFInflector::isSingular($this->getName()) ? FOFInflector::pluralize($this->getName()) : FOFInflector::singularize($this->getName());
			$this->_setPath('template', $this->_basePath . '/views/' . $altView . '/tmpl');
			$this->_addPath('template', $this->_basePath . '/views/' . $this->getName() . '/tmpl');
		}

		// Set the default helper search path

		if (array_key_exists('helper_path', $config))
		{
			// User-defined dirs
			$this->_setPath('helper', $config['helper_path']);
		}
		else
		{
			$this->_setPath('helper', $this->_basePath . '/helpers');
		}

		// Set the layout

		if (array_key_exists('layout', $config))
		{
			$this->setLayout($config['layout']);
		}
		else
		{
			$this->setLayout('default');
		}

		$this->config = $config;

		if (!FOFPlatform::getInstance()->isCli())
		{
			$this->baseurl = FOFPlatform::getInstance()->URIbase(true);

			$fallback = FOFPlatform::getInstance()->getTemplateOverridePath($component) . '/' . $this->getName();
			$this->_addPath('template', $fallback);
		}
	}

	/**
	 * Loads a template given any path. The path is in the format:
	 * [admin|site]:com_foobar/viewname/templatename
	 * e.g. admin:com_foobar/myview/default
	 *
	 * This function searches for Joomla! version override templates. For example,
	 * if you have run this under Joomla! 3.0 and you try to load
	 * admin:com_foobar/myview/default it will automatically search for the
	 * template files default.j30.php, default.j3.php and default.php, in this
	 * order.
	 *
	 * @param   string  $path         See above
	 * @param   array   $forceParams  A hash array of variables to be extracted in the local scope of the template file
	 *
	 * @return  boolean  False if loading failed
	 */
	public function loadAnyTemplate($path = '', $forceParams = array())
	{
		// Automatically check for a Joomla! version specific override
		$throwErrorIfNotFound = true;

		$suffixes = FOFPlatform::getInstance()->getTemplateSuffixes();

		foreach ($suffixes as $suffix)
		{
			if (substr($path, -strlen($suffix)) == $suffix)
			{
				$throwErrorIfNotFound = false;
				break;
			}
		}

		if ($throwErrorIfNotFound)
		{
			foreach ($suffixes as $suffix)
			{
				$result = $this->loadAnyTemplate($path . $suffix, $forceParams);

				if ($result !== false)
				{
					return $result;
				}
			}
		}

		$layoutTemplate = $this->getLayoutTemplate();

		// Parse the path
		$templateParts = $this->_parseTemplatePath($path);

		// Get the paths
		$componentPaths = FOFPlatform::getInstance()->getComponentBaseDirs($templateParts['component']);
		$templatePath   = FOFPlatform::getInstance()->getTemplateOverridePath($templateParts['component']);

		// Get the default paths
		$paths = array();
		$paths[] = $templatePath . '/' . $templateParts['view'];
		$paths[] = ($templateParts['admin'] ? $componentPaths['admin'] : $componentPaths['site']) . '/views/' . $templateParts['view'] . '/tmpl';

		if (isset($this->_path) || property_exists($this, '_path'))
		{
			$paths = array_merge($paths, $this->_path['template']);
		}
		elseif (isset($this->path) || property_exists($this, 'path'))
		{
			$paths = array_merge($paths, $this->path['template']);
		}

		// Look for a template override

		if (isset($layoutTemplate) && $layoutTemplate != '_' && $layoutTemplate != $template)
		{
			$apath = array_shift($paths);
			array_unshift($paths, str_replace($template, $layoutTemplate, $apath));
		}

		$filetofind = $templateParts['template'] . '.php';
        $filesystem = FOFPlatform::getInstance()->getIntegrationObject('filesystem');

		$this->_tempFilePath = $filesystem->pathFind($paths, $filetofind);

		if ($this->_tempFilePath)
		{
			// Unset from local scope
			unset($template);
			unset($layoutTemplate);
			unset($paths);
			unset($path);
			unset($filetofind);

			// Never allow a 'this' property

			if (isset($this->this))
			{
				unset($this->this);
			}

			// Force parameters into scope

			if (!empty($forceParams))
			{
				extract($forceParams);
			}

			// Start capturing output into a buffer
			ob_start();

			// Include the requested template filename in the local scope (this will execute the view logic).
			include $this->_tempFilePath;

			// Done with the requested template; get the buffer and clear it.
			$this->_output = ob_get_contents();
			ob_end_clean();

			return $this->_output;
		}
		else
		{
			if ($throwErrorIfNotFound)
			{
				return new Exception(JText::sprintf('JLIB_APPLICATION_ERROR_LAYOUTFILE_NOT_FOUND', $path), 500);
			}

			return false;
		}
	}

	/**
	 * Overrides the default method to execute and display a template script.
	 * Instead of loadTemplate is uses loadAnyTemplate which allows for automatic
	 * Joomla! version overrides. A little slice of awesome pie!
	 *
	 * @param   string  $tpl  The name of the template file to parse
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 */
	public function display($tpl = null)
	{
		FOFPlatform::getInstance()->setErrorHandling(E_ALL, 'ignore');

		$result = $this->loadTemplate($tpl);

		if ($result instanceof Exception)
		{
            FOFPlatform::getInstance()->raiseError($result->getCode(), $result->getMessage());

			return $result;
		}

		echo $result;
	}

	/**
	 * Assigns variables to the view script via differing strategies.
	 *
	 * This method is overloaded; you can assign all the properties of
	 * an object, an associative array, or a single value by name.
	 *
	 * You are not allowed to set variables that begin with an underscore;
	 * these are either private properties for FOFView or private variables
	 * within the template script itself.
	 *
	 * @return  boolean  True on success, false on failure.
	 *
	 * @deprecated  13.3 Use native PHP syntax.
	 */
	public function assign()
	{
		FOFPlatform::getInstance()->logDeprecated(__CLASS__ . '::' . __METHOD__ . ' is deprecated. Use native PHP syntax.');

		// Get the arguments; there may be 1 or 2.
		$arg0 = @func_get_arg(0);
		$arg1 = @func_get_arg(1);

		// Assign by object

		if (is_object($arg0))
		{
			// Assign public properties
			foreach (get_object_vars($arg0) as $key => $val)
			{
				if (substr($key, 0, 1) != '_')
				{
					$this->$key = $val;
				}
			}

			return true;
		}

		// Assign by associative array

		if (is_array($arg0))
		{
			foreach ($arg0 as $key => $val)
			{
				if (substr($key, 0, 1) != '_')
				{
					$this->$key = $val;
				}
			}

			return true;
		}

		// Assign by string name and mixed value. We use array_key_exists() instead of isset()
		// because isset() fails if the value is set to null.

		if (is_string($arg0) && substr($arg0, 0, 1) != '_' && func_num_args() > 1)
		{
			$this->$arg0 = $arg1;

			return true;
		}

		// $arg0 was not object, array, or string.
		return false;
	}

	/**
	 * Assign variable for the view (by reference).
	 *
	 * You are not allowed to set variables that begin with an underscore;
	 * these are either private properties for FOFView or private variables
	 * within the template script itself.
	 *
	 * @param   string  $key   The name for the reference in the view.
	 * @param   mixed   &$val  The referenced variable.
	 *
	 * @return  boolean  True on success, false on failure.
	 *
	 * @deprecated  13.3  Use native PHP syntax.
	 */
	public function assignRef($key, &$val)
	{
		FOFPlatform::getInstance()->logDeprecated(__CLASS__ . '::' . __METHOD__ . ' is deprecated. Use native PHP syntax.');

		if (is_string($key) && substr($key, 0, 1) != '_')
		{
			$this->$key = &$val;

			return true;
		}

		return false;
	}

	/**
	 * Escapes a value for output in a view script.
	 *
	 * If escaping mechanism is either htmlspecialchars or htmlentities, uses
	 * {@link $_encoding} setting.
	 *
	 * @param   mixed  $var  The output to escape.
	 *
	 * @return  mixed  The escaped value.
	 */
	public function escape($var)
	{
		if (in_array($this->_escape, array('htmlspecialchars', 'htmlentities')))
		{
			return call_user_func($this->_escape, $var, ENT_COMPAT, $this->_charset);
		}

		return call_user_func($this->_escape, $var);
	}

	/**
	 * Method to get data from a registered model or a property of the view
	 *
	 * @param   string  $property  The name of the method to call on the model or the property to get
	 * @param   string  $default   The name of the model to reference or the default value [optional]
	 *
	 * @return  mixed  The return value of the method
	 */
	public function get($property, $default = null)
	{
		// If $model is null we use the default model
		if (is_null($default))
		{
			$model = $this->_defaultModel;
		}
		else
		{
			$model = strtolower($default);
		}

		// First check to make sure the model requested exists
		if (isset($this->_models[$model]))
		{
			// Model exists, let's build the method name
			$method = 'get' . ucfirst($property);

			// Does the method exist?
			if (method_exists($this->_models[$model], $method))
			{
				// The method exists, let's call it and return what we get
				$result = $this->_models[$model]->$method();

				return $result;
			}
		}

		// Degrade to FOFUtilsObject::get
		$result = parent::get($property, $default);

		return $result;
	}

	/**
	 * Method to get the model object
	 *
	 * @param   string  $name  The name of the model (optional)
	 *
	 * @return  mixed  FOFModel object
	 */
	public function getModel($name = null)
	{
		if ($name === null)
		{
			$name = $this->_defaultModel;
		}

		return $this->_models[strtolower($name)];
	}

	/**
	 * Get the layout.
	 *
	 * @return  string  The layout name
	 */
	public function getLayout()
	{
		return $this->_layout;
	}

	/**
	 * Get the layout template.
	 *
	 * @return  string  The layout template name
	 */
	public function getLayoutTemplate()
	{
		return $this->_layoutTemplate;
	}

	/**
	 * Method to get the view name
	 *
	 * The model name by default parsed using the classname, or it can be set
	 * by passing a $config['name'] in the class constructor
	 *
	 * @return  string  The name of the model
	 */
	public function getName()
	{
		if (empty($this->_name))
		{
			$classname = get_class($this);
			$viewpos = strpos($classname, 'View');

			if ($viewpos === false)
			{
				throw new Exception(JText::_('JLIB_APPLICATION_ERROR_VIEW_GET_NAME'), 500);
			}

			$this->_name = strtolower(substr($classname, $viewpos + 4));
		}

		return $this->_name;
	}

	/**
	 * Method to add a model to the view.
	 *
	 * @param   FOFMOdel  $model    The model to add to the view.
     * @param   boolean   $default  Is this the default model?
     * @param   String    $name     optional index name to store the model
	 *
	 * @return  object   The added model.
	 */
	public function setModel($model, $default = false, $name = null)
	{
		if (is_null($name))
		{
			$name = $model->getName();
		}

		$name = strtolower($name);

		$this->_models[$name] = $model;

		if ($default)
		{
			$this->_defaultModel = $name;
		}

		return $model;
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
		$previous = $this->_layout;

		if (strpos($layout, ':') === false)
		{
			$this->_layout = $layout;
		}
		else
		{
			// Convert parameter to array based on :
			$temp = explode(':', $layout);
			$this->_layout = $temp[1];

			// Set layout template
			$this->_layoutTemplate = $temp[0];
		}

		return $previous;
	}

	/**
	 * Allows a different extension for the layout files to be used
	 *
	 * @param   string  $value  The extension.
	 *
	 * @return  string   Previous value
	 */
	public function setLayoutExt($value)
	{
		$previous = $this->_layoutExt;

		if ($value = preg_replace('#[^A-Za-z0-9]#', '', trim($value)))
		{
			$this->_layoutExt = $value;
		}

		return $previous;
	}

	/**
	 * Sets the _escape() callback.
	 *
	 * @param   mixed  $spec  The callback for _escape() to use.
	 *
	 * @return  void
	 *
	 * @deprecated  2.1  Override FOFView::escape() instead.
	 */
	public function setEscape($spec)
	{
		FOFPlatform::getInstance()->logDeprecated(__CLASS__ . '::' . __METHOD__ . ' is deprecated. Override FOFView::escape() instead.');

		$this->_escape = $spec;
	}

	/**
	 * Adds to the stack of view script paths in LIFO order.
	 *
	 * @param   mixed  $path  A directory path or an array of paths.
	 *
	 * @return  void
	 */
	public function addTemplatePath($path)
	{
		$this->_addPath('template', $path);
	}

	/**
	 * Adds to the stack of helper script paths in LIFO order.
	 *
	 * @param   mixed  $path  A directory path or an array of paths.
	 *
	 * @return  void
	 */
	public function addHelperPath($path)
	{
		$this->_addPath('helper', $path);
	}

	/**
	 * Overrides the built-in loadTemplate function with an FOF-specific one.
	 * Our overriden function uses loadAnyTemplate to provide smarter view
	 * template loading.
	 *
	 * @param   string   $tpl     The name of the template file to parse
	 * @param   boolean  $strict  Should we use strict naming, i.e. force a non-empty $tpl?
	 *
	 * @return  mixed  A string if successful, otherwise a JError object
	 */
	public function loadTemplate($tpl = null, $strict = false)
	{
		$paths = FOFPlatform::getInstance()->getViewTemplatePaths(
			$this->input->getCmd('option', ''),
			$this->input->getCmd('view', ''),
			$this->getLayout(),
			$tpl,
			$strict
		);

		foreach ($paths as $path)
		{
			$result = $this->loadAnyTemplate($path);

			if (!($result instanceof Exception))
			{
				break;
			}
		}

		if ($result instanceof Exception)
		{
            FOFPlatform::getInstance()->raiseError($result->getCode(), $result->getMessage());
		}

		return $result;
	}

	/**
	 * Parses a template path in the form of admin:/component/view/layout or
	 * site:/component/view/layout to an array which can be used by
	 * loadAnyTemplate to locate and load the view template file.
	 *
	 * @param   string  $path  The template path to parse
	 *
	 * @return  array  A hash array with the parsed path parts
	 */
	private function _parseTemplatePath($path = '')
	{
		$parts = array(
			'admin'		 => 0,
			'component'	 => $this->config['option'],
			'view'		 => $this->config['view'],
			'template'	 => 'default'
		);

		if (substr($path, 0, 6) == 'admin:')
		{
			$parts['admin'] = 1;
			$path = substr($path, 6);
		}
		elseif (substr($path, 0, 5) == 'site:')
		{
			$path = substr($path, 5);
		}

		if (empty($path))
		{
			return;
		}

		$pathparts = explode('/', $path, 3);

		switch (count($pathparts))
		{
			case 3:
				$parts['component'] = array_shift($pathparts);

			case 2:
				$parts['view'] = array_shift($pathparts);

			case 1:
				$parts['template'] = array_shift($pathparts);
				break;
		}

		return $parts;
	}

	/**
	 * Get the renderer object for this view
	 *
	 * @return  FOFRenderAbstract
	 */
	public function &getRenderer()
	{
		if (!($this->rendererObject instanceof FOFRenderAbstract))
		{
			$this->rendererObject = $this->findRenderer();
		}

		return $this->rendererObject;
	}

	/**
	 * Sets the renderer object for this view
	 *
	 * @param   FOFRenderAbstract  &$renderer  The render class to use
	 *
	 * @return  void
	 */
	public function setRenderer(FOFRenderAbstract &$renderer)
	{
		$this->rendererObject = $renderer;
	}

	/**
	 * Finds a suitable renderer
	 *
	 * @return  FOFRenderAbstract
	 */
	protected function findRenderer()
	{
        $filesystem     = FOFPlatform::getInstance()->getIntegrationObject('filesystem');

		// Try loading the stock renderers shipped with FOF

		if (empty(self::$renderers) || !class_exists('FOFRenderJoomla', false))
		{
			$path = dirname(__FILE__) . '/../render/';
			$renderFiles = $filesystem->folderFiles($path, '.php');

			if (!empty($renderFiles))
			{
				foreach ($renderFiles as $filename)
				{
					if ($filename == 'abstract.php')
					{
						continue;
					}

					@include_once $path . '/' . $filename;

					$camel = FOFInflector::camelize($filename);
					$className = 'FOFRender' . ucfirst(FOFInflector::getPart($camel, 0));
					$o = new $className;

					self::registerRenderer($o);
				}
			}
		}

		// Try to detect the most suitable renderer
		$o = null;
		$priority = 0;

		if (!empty(self::$renderers))
		{
			foreach (self::$renderers as $r)
			{
				$info = $r->getInformation();

				if (!$info->enabled)
				{
					continue;
				}

				if ($info->priority > $priority)
				{
					$priority = $info->priority;
					$o = $r;
				}
			}
		}

		// Return the current renderer
		return $o;
	}

	/**
	 * Registers a renderer object with the view
	 *
	 * @param   FOFRenderAbstract  &$renderer  The render object to register
	 *
	 * @return  void
	 */
	public static function registerRenderer(FOFRenderAbstract &$renderer)
	{
		self::$renderers[] = $renderer;
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
	 * Load a helper file
	 *
	 * @param   string  $hlp  The name of the helper source file automatically searches the helper paths and compiles as needed.
	 *
	 * @return  void
	 */
	public function loadHelper($hlp = null)
	{
		// Clean the file name
		$file = preg_replace('/[^A-Z0-9_\.-]/i', '', $hlp);

		// Load the template script using the default Joomla! features
        $filesystem = FOFPlatform::getInstance()->getIntegrationObject('filesystem');

		$helper = $filesystem->pathFind($this->_path['helper'], $this->_createFileName('helper', array('name' => $file)));

		if ($helper == false)
		{
			$componentPaths = FOFPlatform::getInstance()->getComponentBaseDirs($this->config['option']);
			$path = $componentPaths['main'] . '/helpers';
			$helper = $filesystem->pathFind($path, $this->_createFileName('helper', array('name' => $file)));

			if ($helper == false)
			{
				$path = $path = $componentPaths['alt'] . '/helpers';
				$helper = $filesystem->pathFind($path, $this->_createFileName('helper', array('name' => $file)));
			}
		}

		if ($helper != false)
		{
			// Include the requested template filename in the local scope
			include_once $helper;
		}
	}

	/**
	 * Returns the view's option (component name) and view name in an
	 * associative array.
	 *
	 * @return  array
	 */
	public function getViewOptionAndName()
	{
		return array(
			'option' => $this->config['option'],
			'view'	 => $this->config['view'],
		);
	}

	/**
	 * Sets an entire array of search paths for templates or resources.
	 *
	 * @param   string  $type  The type of path to set, typically 'template'.
	 * @param   mixed   $path  The new search path, or an array of search paths.  If null or false, resets to the current directory only.
	 *
	 * @return  void
	 */
	protected function _setPath($type, $path)
	{
		// Clear out the prior search dirs
		$this->_path[$type] = array();

		// Actually add the user-specified directories
		$this->_addPath($type, $path);

		// Always add the fallback directories as last resort
		switch (strtolower($type))
		{
			case 'template':
				// Set the alternative template search dir

				if (!FOFPlatform::getInstance()->isCli())
				{
					$fallback = FOFPlatform::getInstance()->getTemplateOverridePath($this->input->getCmd('option', '')) . '/' . $this->getName();
					$this->_addPath('template', $fallback);
				}

				break;
		}
	}

	/**
	 * Adds to the search path for templates and resources.
	 *
	 * @param   string  $type  The type of path to add.
	 * @param   mixed   $path  The directory or stream, or an array of either, to search.
	 *
	 * @return  void
	 */
	protected function _addPath($type, $path)
	{
		// Just force to array
		settype($path, 'array');

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
			array_unshift($this->_path[$type], $dir);
		}
	}

	/**
	 * Create the filename for a resource
	 *
	 * @param   string  $type   The resource type to create the filename for
	 * @param   array   $parts  An associative array of filename information
	 *
	 * @return  string  The filename
	 */
	protected function _createFileName($type, $parts = array())
	{
		$filename = '';

		switch ($type)
		{
			case 'template':
				$filename = strtolower($parts['name']) . '.' . $this->_layoutExt;
				break;

			default:
				$filename = strtolower($parts['name']) . '.php';
				break;
		}

		return $filename;
	}
}
