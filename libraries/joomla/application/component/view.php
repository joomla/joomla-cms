<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Application
 * @copyright Copyright Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
  */

// No direct access
defined('JPATH_BASE') or die();

/**
 * Base class for a Joomla View
 *
 * Class holding methods for displaying presentation data.
 *
 * @abstract
 * @package		Joomla.Framework
 * @subpackage	Application
 * @since		1.5
 */
abstract class JView extends JClass
{
	/**
	 * The name of the view
	 *
	 * @var		array
	 */
	protected $_name = null;

	/**
	 * Registered models
	 *
	 * @var		array
	 */
	protected $_models = array();

	/**
	 * The base path of the view
	 *
	 * @var		string
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
	 * @var		string
	 */
	protected $_layout = 'default';

	/**
	 * Layout extension
	 *
	 * @var		string
	 */
	protected $_layoutExt = 'php';

	/**
	 * The set of search directories for resources (templates)
	 *
	 * @var array
	 */
	protected $_path = array(
		'template' => array(),
		'helper' => array()
	);

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
	 */
	protected $_escape = 'htmlspecialchars';

	 /**
	 * Charset to use in escaping mechanisms; defaults to urf8 (UTF-8)
	 *
	 * @var string
	 */
	protected $_charset = 'UTF-8';

	public $baseurl = '';

	protected $_data = array();

	/**
	 * Constructor
	 */
	public function __construct($config = array())
	{
		//set the view name
		if (empty($this->_name))
		{
			if (array_key_exists('name', $config))  {
				$this->_name = $config['name'];
			} else {
				$this->_name = $this->getName();
			}
		}

		 // set the charset (used by the variable escaping functions)
		if (array_key_exists('charset', $config)) {
			$this->_charset = $config['charset'];
		}

		 // user-defined escaping callback
		if (array_key_exists('escape', $config)) {
			$this->setEscape($config['escape']);
		}

		// Set a base path for use by the view
		if (array_key_exists('base_path', $config)) {
			$this->_basePath	= $config['base_path'];
		} else {
			$this->_basePath	= JPATH_COMPONENT;
		}

		// set the default template search path
		if (array_key_exists('template_path', $config)) {
			// user-defined dirs
			$this->_setPath('template', $config['template_path']);
		} else {
			$this->_setPath('template', $this->_basePath.DS.'views'.DS.$this->getName().DS.'tmpl');
		}

		// set the default helper search path
		if (array_key_exists('helper_path', $config)) {
			// user-defined dirs
			$this->_setPath('helper', $config['helper_path']);
		} else {
			$this->_setPath('helper', $this->_basePath.DS.'helpers');
		}

		// set the layout
		if (array_key_exists('layout', $config)) {
			$this->setLayout($config['layout']);
		} else {
			$this->setLayout('default');
		}

		$this->baseurl = JURI::base(true);
	}

	/**
	 * Provides interception of default php error handling logic for objects.  Enforceing class definitions
	 *
	 * @throw Jexception
	 * @since	1.6
 	 */
	public function &__get($var) {
		if(isset($this->_data[$var])) {
			return $this->_data[$var];
		}
		JError::raiseNotice(0, 'Attempted to access undefined object propery', $var);
		return $this->$var;
	}

	/**
	 * Provides interception of default php error handling logic for objects.  Enforceing class definitions
	 *
	 * @throw Jexception
	 * @since	1.6
 	 */
	public function __set($var, $val) {
		JError::raiseNotice(0, 'Attempted to set undefined object propery', array('var'=>$var, 'val'=>$val));
		$this->_data[$var] = $val;
	}

	/**
	 * Execute and display a template script.
	 *
	 * @param string $tpl The name of the template file to parse;
	 * automatically searches through the template paths.
	 *
	 * @throws object An JError object.
	 * @see fetch()
	 */
	public function display($tpl = null)
	{
		$result = $this->loadTemplate($tpl);
		echo $result;
	}

	/**
	 * Assigns variables to the view script via differing strategies.
	 *
	 * This method is overloaded; you can assign all the properties of
	 * an object, an associative array, or a single value by name.
	 *
	 * You are not allowed to set variables that begin with an underscore;
	 * these are either private properties for JView or private variables
	 * within the template script itself.
	 *
	 * <code>
	 * $view = new JView();
	 *
	 * // assign directly
	 * $view->var1 = 'something';
	 * $view->var2 = 'else';
	 *
	 * // assign by name and value
	 * $view->assign('var1', 'something');
	 * $view->assign('var2', 'else');
	 *
	 * // assign by assoc-array
	 * $ary = array('var1' => 'something', 'var2' => 'else');
	 * $view->assign($obj);
	 *
	 * // assign by object
	 * $obj = new stdClass;
	 * $obj->var1 = 'something';
	 * $obj->var2 = 'else';
	 * $view->assign($obj);
	 *
	 * </code>
	 *
	 * @return bool True on success, false on failure.
	 */
	public function assign()
	{
		// get the arguments; there may be 1 or 2.
		$arg0 = @func_get_arg(0);
		$arg1 = @func_get_arg(1);

		// assign by object
		if (is_object($arg0))
		{
			// assign public properties
			foreach (get_object_vars($arg0) as $key => $val)
			{
				if (substr($key, 0, 1) != '_') {
					$this->$key = $val;
				}
			}
			return true;
		}

		// assign by associative array
		if (is_array($arg0))
		{
			foreach ($arg0 as $key => $val)
			{
				if (substr($key, 0, 1) != '_') {
					$this->$key = $val;
				}
			}
			return true;
		}

		// assign by string name and mixed value.

		// we use array_key_exists() instead of isset() becuase isset()
		// fails if the value is set to null.
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
	 * these are either private properties for JView or private variables
	 * within the template script itself.
	 *
	 * <code>
	 * $view = new JView();
	 *
	 * // assign by name and value
	 * $view->assignRef('var1', $ref);
	 *
	 * // assign directly
	 * $view->ref =& $var1;
	 * </code>
	 *
	 * @param string $key The name for the reference in the view.
	 * @param mixed &$val The referenced variable.
	 *
	 * @return bool True on success, false on failure.
	 */

	public function assignRef($key, &$val)
	{
		if (is_string($key) && substr($key, 0, 1) != '_')
		{
			if(array_key_exists($key, get_object_vars($this))) {
				$this->$key =& $val;
			} else {
				$this->_data[$key] =& $val;
			}
			return true;
		}

		return false;
	}

	/**
	 * Escapes a value for output in a view script.
	 *
	 * If escaping mechanism is one of htmlspecialchars or htmlentities, uses
	 * {@link $_encoding} setting.
	 *
	 * @param  mixed $var The output to escape.
	 * @return mixed The escaped value.
	 */
	public function escape($var)
	{
		if (in_array($this->_escape, array('htmlspecialchars', 'htmlentities'))) {
			return call_user_func($this->_escape, $var, ENT_COMPAT, $this->_charset);
		}

		return call_user_func($this->_escape, $var);
	}

	/**
	 * Method to get data from a registered model or a property of the view
	 *
	 * @param	string	The name of the method to call on the model, or the property to get
	 * @param	string	The name of the model to reference, or the default value [optional]
	 * @return mixed	The return value of the method
	 */
	public function &get($property, $default = null)
	{
		// If $model is null we use the default model
		if (is_null($default)) {
			$model = $this->_defaultModel;
		} else {
			$model = strtolower($default);
		}

		// First check to make sure the model requested exists
		if (isset($this->_models[$model]))
		{
			// Model exists, lets build the method name
			$method = 'get'.ucfirst($property);

			// Does the method exist?
			if (method_exists($this->_models[$model], $method))
			{
				// The method exists, lets call it and return what we get
				$result = $this->_models[$model]->$method();
				return $result;
			}

		}

		// degrade to JObject::get
		$result = parent::get($property, $default);
		return $result;

	}

	/**
	 * Method to get the model object
	 *
	 * @param	string	$name	The name of the model (optional)
	 * @return	mixed			JModel object
	 */
	public function &getModel($name = null)
	{
		if ($name === null) {
			$name = $this->_defaultModel;
		}
		return $this->_models[strtolower($name)];
	}

	/**
	 * Get the layout.
	 *
	 * @return string The layout name
	 */
	public function getLayout()
	{
		return $this->_layout;
	}

	/**
	 * Method to get the view name
	 *
	 * The model name by default parsed using the classname, or it can be set
	 * by passing a $config['name'] in the class constructor
	 *
	 * @return	string The name of the model
	 */
	public function getName()
	{
		$name = $this->_name;

		if (empty($name))
		{
			$r = null;
			if (!preg_match('/View((view)*(.*(view)?.*))$/i', get_class($this), $r)) {
				throw new JException('Cannot get or parse class name', 500, E_ERROR, get_class($this), true);
			}
			if (strpos($r[3], "view"))
			{
				JError::raiseWarning('SOME_ERROR_CODE',"JView::getName() : Your classname contains the substring 'view'. ".
											"This causes problems when extracting the classname from the name of your objects view. " .
											"Avoid Object names with the substring 'view'.");
			}
			$name = strtolower($r[3]);
		}

		return $name;
	}

	/**
	 * Method to add a model to the view.  We support a multiple model single
	 * view system by which models are referenced by classname.  A caveat to the
	 * classname referencing is that any classname prepended by JModel will be
	 * referenced by the name without JModel, eg. JModelCategory is just
	 * Category.
	 *
	 * @param	object	$model		The model to add to the view.
	 * @param	boolean	$default	Is this the default model?
	 * @return	object				The added model
	 */
	public function &setModel(&$model, $default = false)
	{
		$name = strtolower($model->getName());
		$this->_models[$name] = &$model;

		if ($default) {
			$this->_defaultModel = $name;
		}
		return $model;
	}

	/**
	 * Sets the layout name to use
	 *
	 * @param	string $template The template name.
	 * @return	string Previous value
	 */

	public function setLayout($layout)
	{
		$previous		= $this->_layout;
		$this->_layout = $layout;
		return $previous;
	}

	/**
	 * Allows a different extension for the layout files to be used
	 *
	 * @param	string	The extension
	 * @return	string	Previous value
	 */
	public function setLayoutExt($value)
	{
		$previous	= $this->_layoutExt;
		if ($value = preg_replace('#[^A-Za-z0-9]#', '', trim($value))) {
			$this->_layoutExt = $value;
		}
		return $previous;
	}

	 /**
	 * Sets the _escape() callback.
	 *
	 * @param mixed $spec The callback for _escape() to use.
	 */
	public function setEscape($spec)
	{
		$this->_escape = $spec;
	}

	/**
	 * Adds to the stack of view script paths in LIFO order.
	 *
	 * @param string|array The directory (-ies) to add.
	 * @return void
	 */
	public function addTemplatePath($path)
	{
		$this->_addPath('template', $path);
	}

	/**
	 * Adds to the stack of helper script paths in LIFO order.
	 *
	 * @param string|array The directory (-ies) to add.
	 * @return void
	 */
	public function addHelperPath($path)
	{
		$this->_addPath('helper', $path);
	}

	/**
	 * Load a template file -- first look in the templates folder for an override
	 *
	 * @param string $tpl The name of the template source file ...
	 * automatically searches the template paths and compiles as needed.
	 * @return string The output of the the template script.
	 */
	public function loadTemplate($tpl = null)
	{
		$appl		= JFactory::getApplication();
		$option		= JApplicationHelper::getComponentName();

		// clear prior output
		$this->_output = null;

		//create the template file name based on the layout
		$file = isset($tpl) ? $this->_layout.'_'.$tpl : $this->_layout;
		// clean the file name
		$file = preg_replace('/[^A-Z0-9_\.-]/i', '', $file);
		$tpl  = preg_replace('/[^A-Z0-9_\.-]/i', '', $tpl);

		// load the template script
		jimport('joomla.filesystem.path');
		$filetofind	= $this->_createFileName('template', array('name' => $file));
		$this->_template = JPath::find($this->_path['template'], $filetofind);

		if ($this->_template != false)
		{
			// unset so as not to introduce into template scope
			unset($tpl);
			unset($file);

			// never allow a 'this' property
			if (isset($this->this)) {
				unset($this->this);
			}

			// start capturing output into a buffer
			ob_start();
			// include the requested template filename in the local scope
			// (this will execute the view logic).
			include $this->_template;

			// done with the requested template; get the buffer and
			// clear it.
			$this->_output = ob_get_contents();
			ob_end_clean();

			return $this->_output;
		}
		else {
			throw new JException('Layout file not found', 500, E_ERROR, $file, true);
		}
	}

	/**
	 * Load a helper file
	 *
	 * @param string $tpl The name of the helper source file ...
	 * automatically searches the helper paths and compiles as needed.
	 * @return boolean Returns true if the file was loaded
	 */
	public function loadHelper($hlp = null)
	{
		// clean the file name
		$file = preg_replace('/[^A-Z0-9_\.-]/i', '', $hlp);

		// load the template script
		jimport('joomla.filesystem.path');
		$helper = JPath::find($this->_path['helper'], $this->_createFileName('helper', array('name' => $file)));

		if ($helper != false)
		{
			// include the requested template filename in the local scope
			include_once $helper;
		}
	}

	/**
	 * Sets an entire array of search paths for templates or resources.
	 *
	 * @param string $type The type of path to set, typically 'template'.
	 * @param string|array $path The new set of search paths.  If null or
	 * false, resets to the current directory only.
	 */
	protected function _setPath($type, $path)
	{
		$component	= JApplicationHelper::getComponentName();
		$appl		= JFactory::getApplication();

		// clear out the prior search dirs
		$this->_path[$type] = array();

		// actually add the user-specified directories
		$this->_addPath($type, $path);

		// always add the fallback directories as last resort
		switch (strtolower($type))
		{
			case 'template':
			{
				// set the alternative template search dir
				if (isset($appl))
				{
					$component	= preg_replace('/[^A-Z0-9_\.-]/i', '', $component);
					$fallback	= JPATH_BASE.DS.'templates'.DS.$appl->getTemplate().DS.'html'.DS.$component.DS.$this->getName();
					$this->_addPath('template', $fallback);
				}
			}	break;
		}
	}

	/**
	 * Adds to the search path for templates and resources.
	 *
	 * @param string|array $path The directory or stream to search.
	 */
	protected function _addPath($type, $path)
	{
		// just force to array
		settype($path, 'array');

		// loop through the path directories
		foreach ($path as $dir)
		{
			// no surrounding spaces allowed!
			$dir = trim($dir);

			// add trailing separators as needed
			if (substr($dir, -1) != DIRECTORY_SEPARATOR) {
				// directory
				$dir .= DIRECTORY_SEPARATOR;
			}

			// add to the top of the search dirs
			array_unshift($this->_path[$type], $dir);
		}
	}

	/**
	 * Create the filename for a resource
	 *
	 * @param string 	$type  The resource type to create the filename for
	 * @param array 	$parts An associative array of filename information
	 * @return string The filename
	 */
	private function _createFileName($type, $parts = array())
	{
		$filename = '';

		switch($type)
		{
			case 'template' :
				$filename = strtolower($parts['name']).'.'.$this->_layoutExt;
				break;

			default :
				$filename = strtolower($parts['name']).'.php';
				break;
		}
		return $filename;
	}
}
