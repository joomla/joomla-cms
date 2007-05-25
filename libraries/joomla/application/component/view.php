<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Application
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Base class for a Joomla View
 *
 * Class holding methods for displaying presentation data.
 *
 * @abstract
 * @package		Joomla.Framework
 * @subpackage	Application
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @author		Louis Landry <louis.landry@joomla.org>
 * @author 		Andrew Eddie
 * @since		1.5
 */
class JView extends JObject
{
	/**
	 * The name of the view
	 *
	 * @var		array
	 * @access protected
	 */
	var $_name = null;

	/**
	 * Registered models
	 *
	 * @var		array
	 * @access protected
	 */
	var $_models = array();

	/**
	 * The base path of the view
	 *
	 * @var		string
	 * @access 	protected
	 */
	var $_basePath = null;

	/**
	 * The default model
	 *
	 * @var	string
	 * @access protected
	 */
	var $_defaultModel = null;

	/**
	 * Layout name
	 *
	 * @var		string
	 * @access 	protected
	 */
	var $_layout = 'default';

	/**
	 * Layout extension
	 *
	 * @var		string
	 * @access 	protected
	 */
	var $_layoutExt = 'php';

   /**
	* The set of search directories for resources (templates)
	*
	* @var array
	* @access protected
	*/
	var $_path = array(
		'template' => array(),
		'helper' => array()
	);

   /**
	* The name of the default template source file.
	*
	* @var string
	* @access private
	*/
	var $_template = null;

   /**
	* The output of the template script.
	*
	* @var string
	* @access private
	*/
	var $_output = null;

	/**
	* Array of callbacks used to escape output.
	*
	* @var array
	* @access private
	*/
	var $_escape = array('htmlspecialchars');

	/**
	 * Constructor
	 *
	 * @access	protected
	 */
	function __construct($config = array())
	{
		//set the view name
		if (empty( $this->_name ))
		{
			if (isset($config['name']))  {
				$this->_name = $config['name'];
			}
			else
			{
				$r = null;
				if (!preg_match('/View((view)*(.*(view)?.*))$/i', get_class($this), $r)) {
					JError::raiseError (500, "JView::__construct() : Can't get or parse class name.");
				}
				$this->_name = strtolower( $r[3] );
			}
		}

		// Set a base path for use by the view
		if (isset($config['base_path'])) {
			$this->_basePath	= $config['base_path'];
		} else {
			$this->_basePath	= JPATH_COMPONENT;
		}

		// set the default template search path
		if (isset($config['template_path'])) {
			// user-defined dirs
			$this->_setPath('template', $config['template_path']);
		} else {
			$this->_setPath('template', null);
		}

		// set the default template search path
		if (isset($config['helper_path'])) {
			// user-defined dirs
			$this->_setPath('helper', $config['helper_path']);
		} else {
			$this->_setPath('helper', null);
		}

		// set the layout
		if (isset($config['layout'])) {
			$this->setLayout($config['layout']);
		}
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
	function display($tpl = null)
	{
		$result = $this->loadTemplate($tpl);
		if (JError::isError($result)) {
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
	* these are either private properties for JView or private variables
	* within the template script itself.
	*
	* <code>
	* $view =& new JView();
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
	* @access public
	* @return bool True on success, false on failure.
	*/
	function assign()
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
	* @access public
	*
	* @param string $key The name for the reference in the view.
	* @param mixed &$val The referenced variable.
	*
	* @return bool True on success, false on failure.
	*/

	function assignRef($key, &$val)
	{
		if (is_string($key) && substr($key, 0, 1) != '_')
		{
			$this->$key =& $val;
			return true;
		}

		return false;
	}

	/**
	* Applies escaping to a value.
	*
	* You can override the predefined escaping callbacks by passing
	* added parameters as replacement callbacks.
	*
	* <code>
	* // use predefined callbacks
	* $result = $view->escape($value);
	*
	* // use replacement callbacks
	* $result = $view->escape(
	*	 $value,
	*	 'stripslashes',
	*	 'htmlspecialchars',
	*	 array('StaticClass', 'method'),
	*	 array($object, $method)
	* );
	* </code>
	*
	* @access public
	* @param mixed $value The value to be escaped.
	* @return mixed
	*/
	function escape($value)
	{
		// were custom callbacks passed?
		if (func_num_args() == 1)
		{
			// no, only a value was passed.
			// loop through the predefined callbacks.
			foreach ($this->_escape as $func)
			{
				// this if() shaves 0.001sec off of 300 calls.
				if (is_string($func)) {
					$value = $func($value);
				} else {
					$value = call_user_func($func, $value);
				}
			}
		}
		else
		{
			// yes, use the custom callbacks
			$callbacks = func_get_args();

			// drop $value
			array_shift($callbacks);

			// loop through custom callbacks.
			foreach ($callbacks as $func)
			{
				// this if() shaves 0.001sec off of 300 calls.
				if (is_string($func)) {
					$value = $func($value);
				} else {
					$value = call_user_func($func, $value);
				}
			}

		}

		return $value;
	}

	/**
	 * Method to get data from a registered model
	 *
	 * @access	public
	 * @param	string	The name of the method to call on the model
	 * @param	string	The name of the model to reference [optional]
	 * @return mixed	The return value of the method
	 */
	function &get( $method, $model = null )
	{
		$result = false;

		// If $model is null we use the default model
		if (is_null($model)) {
			$model = $this->_defaultModel;
		} else {
			$model = strtolower( $model );
		}

		// First check to make sure the model requested exists
		if (isset( $this->_models[$model] ))
		{
			// Model exists, lets build the method name
			$method = 'get'.ucfirst($method);

			// Does the method exist?
			if (method_exists($this->_models[$model], $method))
			{
				// The method exists, lets call it and return what we get
				$result = $this->_models[$model]->$method();
			}
			else
			{
				// Method wasn't found... throw a warning and return false
				JError::raiseWarning( 0, "Unknown Method $model::$method() was not found");
				$result = false;
			}
		}
		else
		{
			// degrade to JObject::get
			$result = parent::get( $method, $model );
		}

		return $result;
	}

	/**
	 * Allows a different extension for the layout files to be used
	 *
	 * @access	public
	 * @param	string	The extension
	 * @return	string	Previous value
	 * @since	1.5
	 */
	function setLayoutExt( $value )
	{
		$previous	= $this->_layoutExt;
		if ($value = preg_replace( '#[^A-Za-z0-9]#', '', trim( $value ) )) {
			$this->_layoutExt = $value;
		}
		return $previous;
	}

	/**
	 * Method to add a model to the view.  We support a multiple model single
	 * view system by which models are referenced by classname.  A caveat to the
	 * classname referencing is that any classname prepended by JModel will be
	 * referenced by the name without JModel, eg. JModelCategory is just
	 * Category.
	 *
	 * @access	public
	 * @param	object	$model		The model to add to the view.
	 * @param	boolean	$default	Is this the default model?
	 * @return	object				The added model
	 */
	function &setModel( &$model, $default = false )
	{
		$name = strtolower($model->getName());
		$this->_models[$name] = &$model;

		if ($default) {
			$this->_defaultModel = $name;
		}
		return $model;
	}

	/**
	 * Method to get the model object
	 *
	 * @access	public
	 * @param	string	$name	The name of the model (optional)
	 * @return	mixed			JModel object
	 */
	function &getModel( $name = null )
	{
		if ($name === null) {
			$name = $this->_defaultModel;
		}
		return $this->_models[strtolower( $name )];
	}

	/**
	* Sets the layout name to use
	*
	* @access	public
	* @param	string $template The template name.
	* @return	string Previous value
	* @since	1.5
	*/

	function setLayout($layout)
	{
		$previous		= $this->_layout;
		$this->_layout = $layout;
		return $previous;
	}

	/**
	* Get the layout.
	*
	* @access public
	* @return string The layout name
	*/

	function getLayout()
	{
		return $this->_layout;
	}

	/**
	 * Method to get the view name
	 *
	 * The model name by default parsed using the classname, or it can be set
	 * by passing a $config['nameÕ] in the class constructor
	 *
	 * @access	public
	 * @return	string The name of the model
	 * @since	1.5
	 */
	function getName()
	{
		return $this->_name;
	}

	/**
	 * Adds to the stack of view script paths in LIFO order.
	 *
	 * @param string|array The directory (-ies) to add.
	 * @return void
	 */
	function addTemplatePath($path)
	{
		$this->_addPath('template', $path);
	}

	/**
	 * Adds to the stack of helper script paths in LIFO order.
	 *
	 * @param string|array The directory (-ies) to add.
	 * @return void
	 */
	function addHelperPath($path)
	{
		$this->_addPath('helper', $path);
	}

	/**
	* Clears then sets the callbacks to use when calling JView::escape().
	*
	* Each parameter passed to this function is treated as a separate
	* callback.  For example:
	*
	* <code>
	* $view->setEscape(
	*	 'stripslashes',
	*	 'htmlspecialchars',
	*	 array('StaticClass', 'method'),
	*	 array($object, $method)
	* );
	* </code>
	*
	* @access public
	*/
	function setEscape()
	{
		$this->_escape = (array) @func_get_args();
	}


	/**
	* Adds to the callbacks used when calling JView::escape().
	*
	* Each parameter passed to this function is treated as a separate
	* callback.  For example:
	*
	* <code>
	* $savant->addEscape(
	*	 'stripslashes',
	*	 'htmlspecialchars',
	*	 array('StaticClass', 'method'),
	*	 array($object, $method)
	* );
	* </code>
	*
	* @access public
	*
	* @return void
	*
	*/
	function addEscape()
	{
		$args = (array) @func_get_args();
		$this->_escape = array_merge($this->_escape, $args);
	}

	/**
	 * Load a template file -- first look in the templates folder for an override
	 *
	 * @access	public
	 * @param string $tpl The name of the template source file ...
	 * automatically searches the template paths and compiles as needed.
	 * @return string The output of the the template script.
	 */
	function loadTemplate( $tpl = null)
	{
		global $mainframe, $option;

		// clear prior output
		$this->_output = null;

		//create the template file name based on the layout
		$file = isset($tpl) ? $this->_layout.'_'.$tpl : $this->_layout;
		// clean the file name
		$file = preg_replace('/[^A-Z0-9_\.-]/i', '', $file);
		$tpl  = preg_replace('/[^A-Z0-9_\.-]/i', '', $tpl);

		// load the template script
		jimport('joomla.filesystem.path');
		$this->_template = JPath::find($this->_path['template'], $this->_createFileName('template', array('name' => $file)));
		if ($this->_template == false)
		{
			$file2 = !count($tpl) ? 'default_'.$tpl : 'default';
			$this->_template = JPath::find($this->_path['template'], $this->_createFileName('template', array('name' => $file2)));
		}

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
			return JError::raiseError( 500, 'Layout "' . $file . '" not found' );
		}
	}

	/**
	 * Load a helper file
	 *
	 * @access	public
	 * @param string $tpl The name of the helper source file ...
	 * automatically searches the helper paths and compiles as needed.
	 * @return boolean Returns true if the file was loaded
	 */
	function loadHelper( $hlp = null)
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
	* @access protected
	* @param string $type The type of path to set, typically 'template'.
	* @param string|array $path The new set of search paths.  If null or
	* false, resets to the current directory only.
	*/
	function _setPath($type, $path)
	{
		global $mainframe, $option;

		// clear out the prior search dirs
		$this->_path[$type] = array();

		// always add the fallback directories as last resort
		switch (strtolower($type))
		{
			case 'template':
			{
				// the current directory
				$viewName = preg_replace('/[^A-Z0-9_\.-]/i', '', $this->_name);
				$this->_addPath($type, $this->_basePath.DS.'views'.DS.$viewName.DS.'tmpl');

				// set the alternative template search dir
				if (isset($mainframe))
				{
					$option = preg_replace('/[^A-Z0-9_\.-]/i', '', $option);
					$fallback = JPATH_BASE.DS.'templates'.DS.$mainframe->getTemplate().DS.'html'.DS.$option.DS.$viewName;
					$this->_addPath('template', $fallback);
				}
			}	break;

			case 'helper':
			{
				$this->_addPath($type, $this->_basePath.DS.'helpers');
			} break;
		}

		// actually add the user-specified directories
		$this->_addPath($type, $path);
	}

   /**
	* Adds to the search path for templates and resources.
	*
	* @access protected
	* @param string|array $path The directory or stream to search.
	*/
	function _addPath($type, $path)
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
	 * @access private
	 * @param string 	$type  The resource type to create the filename for
	 * @param array 	$parts An associative array of filename information
	 * @return string The filename
	 * @since 1.5
	 */
	function _createFileName($type, $parts = array())
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