<?PHP
/**
 * patTemplate
 *
 * $Id: patTemplate.php 560 2005-10-19 17:22:00Z Jinx $
 *
 * powerful templating engine
 *
 * @version		3.1.0
 * @package		patTemplate
 * @author		Stephan Schmidt <schst@php.net>
 * @license		LGPL
 * @link		http://www.php-tools.net
 */

// ** Following line Joomla! specific **
require_once( dirname( __FILE__ ) . '/patErrorManager.php' );

/**
 * template already exists
 */
define( 'PATTEMPLATE_ERROR_TEMPLATE_EXISTS', 5010 );

/**
 * template does not exist
 */
define ( 'PATTEMPLATE_WARNING_NO_TEMPLATE', 5011 );

/**
 * unknown type
 */
define ( 'PATTEMPLATE_WARNING_UNKNOWN_TYPE', 5012 );

/**
 * base class for module could not be found
 */
define( 'PATTEMPLATE_ERROR_BASECLASS_NOT_FOUND', 5050 );

/**
 * module could not be found
 */
define( 'PATTEMPLATE_ERROR_MODULE_NOT_FOUND', 5051 );

/**
 * array expected
 */
define( 'PATTEMPLATE_ERROR_EXPECTED_ARRAY', 5052 );

/**
 * No input
 */
define( 'PATTEMPLATE_ERROR_NO_INPUT', 6000 );
/**
 * Recursion
 */
define( 'PATTEMPLATE_ERROR_RECURSION', 6010 );

/**
 * patTemplate
 *
 * powerful templating engine
 *
 * @version		3.1.0
 * @package		patTemplate
 * @author		Stephan Schmidt <schst@php.net>
 * @license		LGPL
 * @link		http://www.php-tools.net
 */
class patTemplate
{
   /**
	* standard system vars that identify pat tools
	* @var	array
	*/
	var	$_systemVars			=	array(
										'appName'		=>	'patTemplate',
										'appVersion'	=>	'3.1.0',
										'author'		=>	array(
																	'Stephan Schmidt <schst@php.net>'
																 )
									);

   /**
	* default attributes for new templates
	* @access	private
	* @var		array
	*/
	var	$_defaultAttributes	=	array(
										'type'			=>	'standard',
										'visibility'	=>	'visible',
										'loop'			=>	1,
										'unusedvars'	=>	'strip',
										'whitespace'	=>	'keep',
										'autoclear'		=>	'off',
										'autoload'		=>	'on'
									);

   /**
	* options for patTemplate
	*
	* Currently the following options are implemented:
	* - maintainBc defines, whether patTemplate should be backwards compatible.
	*   This means, that you may use 'default' and 'empty' for subtemplates.
	*
	* @access	private
	* @var		array
	*/
	var	$_options	=	array(
								'startTag'			=> '{',
								'endTag'			=> '}',
								'root'				=> array('__default' => '.'),
								'namespace'			=> 'patTemplate',
								'maintainBc'		=> true,
								'defaultFunction'	=> false
							 );

   /**
	* start tag
	*
	* @access	private
	* @var		string
	*/
	var $_startTag = '{';

   /**
	* end tag
	*
	* @access	private
	* @var		string
	*/
	var $_endTag = '}';

   /**
	* loaded modules
	*
	* Modules are:
	* - Readers
	* - Caches
	* - Variable modifiers
	* - Filters
	*
	* @access	private
	* @var		array
	*/
	var	$_modules		=	array();

   /**
	* directories, where modules can be stored
	* @access	private
	* @var		array
	*/
	var	$_moduleDirs	=	array();

   /**
	* stores all template names
	* @access	private
	* @var		array
	*/
	var	$_templateList	=	array();

   /**
	* stores all template data
	* @access	private
	* @var		array
	*/
	var	$_templates		=	array();

   /**
	* stores all global variables
	* @access	private
	* @var		array
	*/
	var	$_globals	=	array();

   /**
	* stores all local variables
	* @access	private
	* @var		array
	*/
	var	$_vars	=	array();

   /**
	* stores the name of the first template that has been
	* found
	*
	* @access	private
	* @var		string
	*/
	var	$_root;

   /**
	* output filters that should be used
	*
	* @access	private
	* @var		array
	*/
	var	$_outputFilters = array();

   /**
	* input filters that should be used
	*
	* @access	private
	* @var		array
	*/
	var	$_inputFilters = array();

   /**
	* template cache, that should be used
	*
	* @access	private
	* @var		array
	*/
	var	$_tmplCache = null;

   /**
	* placeholders, that have been discovered
	*
	* @access	private
	* @var		array
	*/
	var	$_discoveredPlaceholders = array();

   /**
	* Create a new patTemplate instance.
	*
	* The constructor accepts the type of the templates as sole parameter.
	* You may choose one of:
	* - html (default)
	* - tex
	*
	* The type influences the tags you are using in your templates.
	*
	* @access	public
	* @param	string	type (either html or tex)
	*/
	function patTemplate( $type = 'html' )
	{
		if( !defined( 'PATTEMPLATE_INCLUDE_PATH' ) ) {
			define( 'PATTEMPLATE_INCLUDE_PATH', dirname( __FILE__ ) . '/patTemplate' );
		}

		$this->setType( $type );
	}

   /**
	* sets an option
	*
	* Currently, the following options are supported
	* - maintainBc (true|false)
	* - namespace (string)
	*
	* @access	public
	* @param	string	option to set
	* @param	string	value of the option
	*/
	function setOption($option, $value)
	{
		$this->_options[$option] = $value;
	}

   /**
	* gets an option
	*
	* @access	public
	* @param	string	option to get
	* @return	mixed	value of the option
	*/
	function getOption( $option )
	{
		if (!isset($this->_options[$option])) {
			return null;
		}
		return $this->_options[$option];
	}

   /**
	* sets name of directory where templates are stored
	*
	* @access	public
	* @param	string	dir where templates are stored
	* @deprecated		please use patTemplate::setRoot() instead
	*/
	function setBasedir($basedir)
	{
		$this->setRoot($basedir);
	}

   /**
	* sets root base for the template
	*
	* The parameter depends on the reader you are using.
	*
	* @access	public
	* @param	string	root base of the templates
	*/
	function setRoot($root, $reader = '__default')
	{
		$this->_options['root'][$reader] = $root;
	}

   /**
	* gets name of root base for the templates
	*
	* @access	public
	* @return	mixed 		root base
	*/
	function getRoot($reader = '__default')
	{
		return	$this->_options['root'][$reader];
	}

   /**
	* sets namespace of patTemplate tags
	*
	* If you want to use more than one namespace, you may set this to
	* an array. All tags in these namespaces will be treated as patTemplate
	* tags.
	*
	* @access	public
	* @param	string|array	namespace(s)
	*/
	function setNamespace($ns)
	{
		$this->_options['namespace'] = $ns;
	}

   /**
	* gets namespace of patTemplate tags
	*
	* @access	public
	* @return	string|array	namespace(s)
	*/
	function getNamespace()
	{
		return $this->_options['namespace'];
	}

   /**
	* set default attribute
	*
	* @access	public
	* @param	string	attribute name
	* @param	mixed	attribute value
	*/
	function setDefaultAttribute( $name, $value )
	{
		$this->_defaultAttributes[$name]	=	$value;
	}

   /**
	* set default attributes
	*
	* @access	public
	* @param	array	attributes
	*/
	function setDefaultAttributes( $attributes )
	{
		$this->_defaultAttributes	=	array_merge( $this->_defaultAttributes, $attributes );
	}

   /**
	* get default attributes
	*
	* @access	public
	* @return	return default attributes
	*/
	function getDefaultAttributes()
	{
		return	$this->_defaultAttributes;
	}

   /**
	* set the type for the templates
	*
	* @access	public
	* @param	string	type (html or tex)
	* @return	boolean	true on success
	*/
	function setType( $type )
	{
		switch( strtolower( $type ) )
		{
			case "tex":
				$this->setTags( '<{', '}>' );
				break;
			case "html":
				$this->setTags( '{', '}' );
				break;
			default:
				return	patErrorManager::raiseWarning(
														PATTEMPLATE_WARNING_UNKNOWN_TYPE,
														"Unknown type '$type'. Please use 'html' or 'tex'."
													);
		}
		return true;
	}

   /**
	* set the start and end tag for variables
	*
	* @access	public
	* @param	string	start tag
	* @param	string	end tag
	* @return	boolean	true on success
	*/
	function setTags( $startTag, $endTag )
	{
		$this->_options['startTag']	=	$startTag;
		$this->_options['endTag']	=	$endTag;

		$this->_startTag	=	$startTag;
		$this->_endTag		=	$endTag;
		return true;
	}

   /**
	* get start tag for variables
	*
	* @access	public
	* @return	string	start tag
	*/
	function getStartTag()
	{
		return $this->_options['startTag'];
	}

   /**
	* get end tag for variables
	*
	* @access	public
	* @return	string	end tag
	*/
	function getEndTag()
	{
		return $this->_options['endTag'];
	}

   /**
	* add a directory where patTemplate should search for
	* modules.
	*
	* You may either pass a string or an array of directories.
	*
	* patTemplate will be searching for a module in the same
	* order you added them. If the module cannot be found in
	* the custom folders, it will look in
	* patTemplate/$moduleType.
	*
	* @access	public
	* @param	string			module type
	* @param	string|array	directory or directories to search.
	*/
	function addModuleDir( $moduleType, $dir )
	{
		if( !isset( $this->_moduleDirs[$moduleType] ) )
			$this->_moduleDirs[$moduleType]	=	array();
		if( is_array( $dir ) )
			$this->_moduleDirs[$moduleType] = array_merge( $this->_moduleDirs[$moduleType], $dir );
		else
			array_push( $this->_moduleDirs[$moduleType], $dir );
	}

   /**
	* Sets an attribute of a template
	*
	* supported attributes: visibilty, loop, parse, unusedvars
	*
	* @param	string	$template	name of the template
	* @param	string	$attribute	name of the attribute
	* @param	mixed	$value	value of the attribute
	* @access	public
	* @see		setAttributes(),getAttribute(), clearAttribute()
	*/
	function setAttribute( $template, $attribute, $value )
	{
		$template	=	strtolower( $template );
		if( !isset( $this->_templates[$template] ) )
		{
			return	patErrorManager::raiseWarning(
													PATTEMPLATE_WARNING_NO_TEMPLATE,
													"Template '$template' does not exist."
												);
		}

		$attribute	=	strtolower( $attribute );
		$this->_templates[$template]['attributes'][$attribute]	=	$value;
		return true;
	}

   /**
	* Sets several attribute of a template
	*
	* $attributes has to be a assotiative arrays containing attribute/value pairs
	* supported attributes: visibilty, loop, parse, unusedvars
	*
	* @param	string	$template	name of the template
	* @param	array	$attributes	attribute/value pairs
	* @access	public
	* @see		setAttribute(), getAttribute(), clearAttribute()
	*/
	function setAttributes( $template, $attributes )
	{
		if( !is_array( $attributes ) )
		{
			return patErrorManager::raiseError( PATTEMPLATE_ERROR_EXPECTED_ARRAY, 'patTemplate::setAttributes: Expected array as second parameter, '.gettype( $attributes ).' given' );
		}

		$template	=	strtolower( $template );
		$attributes	=	array_change_key_case( $attributes );
		if( !isset( $this->_templates[$template] ) )
		{
			return	patErrorManager::raiseWarning(
													PATTEMPLATE_WARNING_NO_TEMPLATE,
													"Template '$template' does not exist."
												);
		}

		$this->_templates[$template]['attributes']	=	array_merge( $this->_templates[$template]['attributes'], $attributes );
		return true;
	}

   /**
	* Get all attributes of a template
	*
	* @param	string	name of the template
	* @return	array	attributes
	* @access	public
	*/
	function getAttributes( $template )
	{
		$template	=	strtolower( $template );
		if( !isset( $this->_templates[$template] ) )
		{
			return	patErrorManager::raiseWarning(
													PATTEMPLATE_WARNING_NO_TEMPLATE,
													"Template '$template' does not exist."
												);
		}
		return	$this->_templates[$template]['attributes'];
	}

   /**
	* Gets an attribute of a template
	*
	* supported attributes: visibilty, loop, parse, unusedvars
	*
	* @param	string	$template	name of the template
	* @param	string	$attribute	name of the attribute
	* @return	mixed	value of the attribute
	* @access	public
	* @see		setAttribute(), setAttributes(), clearAttribute()
	*/
	function getAttribute( $template, $attribute )
	{
		$template	=	strtolower( $template );
		$attribute	=	strtolower( $attribute );
		if( !isset( $this->_templates[$template] ) )
		{
			return	patErrorManager::raiseWarning(
													PATTEMPLATE_WARNING_NO_TEMPLATE,
													"Template '$template' does not exist."
												);
		}
		return	$this->_templates[$template]['attributes'][$attribute];
	}

   /**
	* Clears an attribute of a template
	*
	* supported attributes: visibilty, loop, parse, unusedvars
	*
	* @param	string	$template	name of the template
	* @param	string	$attribute	name of the attribute
	* @access	public
	* @see		setAttribute(), setAttributes(), getAttribute()
	*/
	function clearAttribute( $template, $attribute )
	{
		$template	=	strtolower( $template );
		$attribute	=	strtolower( $attribute );

		if( !isset( $this->_templates[$template] ) )
		{
			return	patErrorManager::raiseWarning(
													PATTEMPLATE_WARNING_NO_TEMPLATE,
													"Template '$template' does not exist."
												);
		}
		$this->_templates[$template]['attributes'][$attribute]	=	'';;
		return true;
	}

   /**
	* Prepare a template
	*
	* This can be used if you want to add variables to
	* a template, that has not been loaded yet.
	*
	* @access	public
	* @param	string	template name
	*/
	function prepareTemplate( $name )
	{
		$name	=	strtolower( $name );
		if( !isset( $this->_vars[$name] ) )
		{
			$this->_vars[$name]	=	array(
												'scalar'	=>	array(),
												'rows'		=>	array()
											);
		}
	}

   /**
	* add a variable to a template
	*
	* A variable may also be an indexed array, but _not_
	* an associative array!
	*
	* @access	public
	* @param	string	$template	name of the template
	* @param	string	$varname	name of the variable
	* @param	mixed	$value		value of the variable
	*/
	function addVar( $template, $varname, $value )
	{
		$template = strtolower( $template );
		$varname  = strtoupper( $varname );

		if( !is_array( $value ) ) {
			$this->_vars[$template]['scalar'][$varname] = $value;
			return true;
		}

		$cnt = count( $value );
		for ($i = 0; $i < $cnt; $i++) {
			if (!isset( $this->_vars[$template]['rows'][$i] )) {
				$this->_vars[$template]['rows'][$i] = array();
			}
			$this->_vars[$template]['rows'][$i][$varname] = $value[$i];
		}

		return true;
	}

   /**
	* get the value of a variable
	*
	* @access	public
	* @param	string	name of the template
	* @param	string	name of the variable
	* @return	string	value of the variable, null if the variable is not set
	*/
	function getVar( $template, $varname )
	{
		$template	=	strtolower( $template );
		$varname	=	strtoupper( $varname );

		if( isset( $this->_vars[$template]['scalar'][$varname] ) )
			return $this->_vars[$template]['scalar'][$varname];

		$value = array();

		$cnt = count( $this->_vars[$template]['rows'] );
		for( $i = 0; $i < $cnt; $i++ )
		{
			if( !isset( $this->_vars[$template]['rows'][$i][$varname] ) )
				continue;
			array_push( $value, $this->_vars[$template]['rows'][$i][$varname] );
		}
		if( !empty( $value ) )
			return $value;
		return null;
	}

   /**
	* clear the value of a variable
	*
	* @access	public
	* @param	string	name of the template
	* @param	string	name of the variable
	* @return   boolean
	* @see	  clearVars(), clearTemplate()
	*/
	function clearVar( $template, $varname )
	{
		$template	=	strtolower( $template );
		$varname	=	strtoupper( $varname );

		if (isset( $this->_vars[$template]['scalar'][$varname] )) {
			unset ($this->_vars[$template]['scalar'][$varname]);
			return true;
		}

		$result = false;
		$cnt = count( $this->_vars[$template]['rows'] );
		for ($i = 0; $i < $cnt; $i++) {
			if (!isset($this->_vars[$template]['rows'][$i][$varname])) {
				continue;
			}
			unset($this->_vars[$template]['rows'][$i][$varname]);
			$result = true;
		}
		return $result;
	}


   /**
	* Adds several variables to a template
	*
	* Each Template can have an unlimited amount of its own variables
	* $variables has to be an assotiative array containing variable/value pairs
	*
	* @param	string	$template	name of the template
	* @param	array	$variables	assotiative array of the variables
	* @param	string	$prefix	prefix for all variable names
	* @access	public
	* @see		addVar(), addRows(), addGlobalVar(), addGlobalVars()
	*/
	function addVars( $template, $variables, $prefix = '' )
	{
		$template	=	strtolower( $template );
		$prefix		=	strtoupper( $prefix );
		$variables	=	array_change_key_case( $variables, CASE_UPPER );

		foreach ($variables as $varname => $value) {
			$varname = $prefix.$varname;

			if (!is_array($value)) {
				if (!is_scalar($value)) {
					continue;
				}
				$this->_vars[$template]['scalar'][$varname] = $value;
				continue;
			}

			$cnt = count( $value );
			for( $i = 0; $i < $cnt; $i++ ) {
				if( !isset( $this->_vars[$template]['rows'][$i] ) )
					$this->_vars[$template]['rows'][$i]	=	array();

				$this->_vars[$template]['rows'][$i][$varname]	=	$value[$i];
			}
		}
	}

   /**
	* Clear all variables in a template
	*
	* This clears only variables, but does
	*
	* @access	public
	* @param	string	$template	name of the template
	* @return   boolean
	* @see		clearVar(), clearTemplate()
	*/
	function clearVars( $template )
	{
		$template = strtolower($template);
		$this->_vars[$template] = array(
										 'scalar' => array(),
										 'rows'   => array()
										);
		return true;
	}


   /**
	* Adds several rows of variables to a template
	*
	* Each Template can have an unlimited amount of its own variables
	* Can be used to add a database result as variables to a template
	*
	* @param	string	$template	name of the template
	* @param	array	$rows	array containing assotiative arrays with variable/value pairs
	* @param	string	$prefix	prefix for all variable names
	* @access	public
	* @see		addVar(), addVars(), addGlobalVar(), addGlobalVars()
	*/
	function addRows( $template, $rows, $prefix = '' )
	{
		$template	=	strtolower( $template );
		$prefix		=	strtoupper( $prefix );

		$cnt		=	count( $rows );
		for( $i = 0; $i < $cnt; $i++ )
		{
			if( !isset( $this->_vars[$template]['rows'][$i] ) )
				$this->_vars[$template]['rows'][$i]	=	array();

			$rows[$i]	=	array_change_key_case( $rows[$i], CASE_UPPER );

			foreach( $rows[$i] as $varname => $value )
			{
				$this->_vars[$template]['rows'][$i][$prefix.$varname]	=	$value;
			}
		}
	}

   /**
	* Adds an object to a template
	*
	* All properties of the object will be available as template variables.
	*
	* @access	public
	* @param	string			name of the template
	* @param	object|array	object or array of objects
	* @param	string			prefix for all variable names
	* @param	boolean			ignore private properties (starting with _)
	* @see		addVar(), addRows(), addGlobalVar(), addGlobalVars()
	*/
	function addObject( $template, $object, $prefix = '', $ignorePrivate = false )
	{
		if( is_array( $object ) ) {
			$rows = array();
			foreach($object as $o) {
				array_push( $rows, $this->getObjectVars($o, $ignorePrivate) );
			}

	   		return $this->addRows( $template, $rows, $prefix );
		} elseif (is_object($object)) {
			return $this->addVars( $template, $this->getObjectVars($object, $ignorePrivate), $prefix );
		}
		return false;
	}

   /**
	* get the vars from an object
	*
	* @access   private
	* @param	object
	* @param	boolean	 ignore private properties (starting with _)
	* @return   array
	*/
	function getObjectVars($obj, $ignorePrivate = false)
	{
		if (method_exists($obj, 'getVars')) {
			return $obj->getVars();
		}
		$vars = get_object_vars($obj);
		if ($ignorePrivate === false) {
			return $vars;
		}
		foreach ($vars as $var => $value) {
			if ($var{0} == '_') {
				unset($vars[$var]);
			}
		}
		return $vars;
	}

   /**
	* Adds a global variable
	*
	* Global variables are valid in all templates of this object.
	* A global variable has to be scalar, it will be converted to a string.
	*
	* @access	public
	* @param	string	$varname	name of the global variable
	* @param	string	$value		value of the variable
	* @return	boolean	true on success
	* @see		addGlobalVars(), addVar(), addVars(), addRows()
	*/
	function addGlobalVar( $varname, $value )
	{
		$this->_globals[strtoupper( $varname )]	=	( string )$value;
		return	true;
	}

   /**
	* Clears a global variable
	*
	* @access	public
	* @param	string	$varname	name of the global variable
	* @return	boolean	true on success
	* @see		clearVar(), clearVars(), clearGlobalVars()
	*/
	function clearGlobalVar( $varname )
	{
		$varname = strtoupper( $varname );
		if (!isset($this->_globals[$varname])) {
			return false;
		}
		unset($this->_globals[$varname]);
		return	true;
	}

   /**
	* Clears all global variables
	*
	* @access	public
	* @return	boolean	true on success
	* @see		clearVar(), clearVars(), clearGlobalVar()
	*/
	function clearGlobalVars()
	{
		$this->_globals = array();
		return	true;
	}

   /**
	* Adds several global variables
	*
	* Global variables are valid in all templates of this object.
	*
	* $variables is an associative array, containing name/value pairs of the variables.
	*
	* @access	public
	* @param	array	$variables	array containing the variables
	* @param	string	$prefix		prefix for variable names
	* @return	boolean	true on success
	* @see		addGlobalVar(), addVar(), addVars(), addRows()
	*/
	function addGlobalVars( $variables, $prefix = '' )
	{
		$variables	=	array_change_key_case( $variables, CASE_UPPER );
		$prefix		=	strtoupper( $prefix );
		foreach( $variables as $varname => $value )
		{
			$this->_globals[$prefix.$varname]	=	( string )$value;
		}

		return	true;
	}

   /**
	* get all global variables
	*
	* @access	public
	* @return	array	global variables
	*/
	function getGlobalVars()
	{
		return	$this->_globals;
	}

	/**
	* checks wether a template exists
	*
	* @access	public
	* @param	string		name of the template
	* @return	boolean		true, if the template exists, false otherwise
	*/
	function exists( $name )
	{
		return	in_array( strtolower( $name ), $this->_templateList );
	}

   /**
	* enable a template cache
	*
	* A template cache will improve performace, as the templates
	* do not have to be read on each request.
	*
	* @access	public
	* @param	string		name of the template cache
	* @param	array		parameters for the template cache
	* @return	boolean		true on success, patError otherwise
	*/
	function useTemplateCache( $cache, $params = array() )
	{
		if( !is_object( $cache ) )
		{
			$cache = &$this->loadModule( 'TemplateCache', $cache, $params );
		}
		if( patErrorManager::isError( $cache ) )
			return $cache;

		$this->_tmplCache = &$cache;
		return true;
	}

   /**
	* enable an output filter
	*
	* Output filters are used to modify the template
	* result before it is sent to the browser.
	*
	* They are applied, when displayParsedTemplate() is called.
	*
	* @access	public
	* @param	string		name of the output filter
	* @param	array		parameters for the output filter
	* @return	boolean		true on success, patError otherwise
	*/
	function applyOutputFilter( $filter, $params = array(), $template = null )
	{
		if (!is_object($filter)) {
			$filter = &$this->loadModule( 'OutputFilter', $filter, $params );
		}
		if (patErrorManager::isError($filter)) {
			return $filter;
		}

		if ($template === null) {
			$this->_outputFilters[] = &$filter;
			return true;
		}

		$template = strtolower($template);
		if (!$this->exists($template)) {
			return patErrorManager::raiseWarning(PATTEMPLATE_WARNING_NO_TEMPLATE, 'The selected template does not exist');
		}
		$this->_templates[$template]['attributes']['outputfilter'] = &$filter;
		return true;
	}

   /**
	* enable an input filter
	*
	* input filters are used to modify the template
	* stream before it is split into smaller templates-
	*
	* @access	public
	* @param	string		name of the input filter
	* @param	array		parameters for the input filter
	* @return	boolean		true on success, patError otherwise
	*/
	function applyInputFilter( $filter, $params = array() )
	{
		if( !is_object( $filter ) )
		{
			$filter = &$this->loadModule( 'InputFilter', $filter, $params );
		}
		if( patErrorManager::isError( $filter ) )
			return $filter;

		$this->_inputFilters[] = &$filter;
		return true;
	}

   /**
	* open a file and parse for patTemplate tags
	*
	* @access		public
	* @param		name of the file
	* @return		true, if the template could be parsed
	* @deprecated	Use patTemplate::readTemplatesFromInput() instead, as the method name is misleading
	* @see			readTemplatesFromInput()
	*/
	function readTemplatesFromFile( $filename )
	{
		return	$this->readTemplatesFromInput( $filename, 'File' );
	}

   /**
	* open any input and parse for patTemplate tags
	*
	* @access	public
	* @param	string	name of the input (filename, shm segment, etc.)
	* @param	string	driver that is used as reader, you may also pass a Reader object
	* @param	array	additional options that will only be used for this template
	* @param	string	name of the template that should be used as a container, should not be used by public
	*					calls.
	* @return	boolean	true, if the template could be parsed, false otherwise
	*/
	function readTemplatesFromInput( $input, $reader = 'File', $options = null, $parseInto = null )
	{
		if ((string)$input === '') {
			return patErrorManager::raiseError(PATTEMPLATE_ERROR_NO_INPUT, 'No input to read has been passed.');
		}

		if (is_array($options)) {
			$options = array_merge( $this->_options, $options );
		} else {
			$options = $this->_options;
		}

		if (!is_null($parseInto)) {
			$parseInto	=	strtolower( $parseInto );
		}

		$templates = false;
		if ($this->_tmplCache !== null) {
			/**
			 * get the unique cache key
			 */
			$key = $this->_tmplCache->getKey($input, $options);

			$templates = $this->_loadTemplatesFromCache( $input, $reader, $options, $key );

			/**
			 * check for error returned from cache
			 */
			if (patErrorManager::isError($templates)) {
				return $templates;
			}
		}

		/**
		 * templates have not been loaded from cache
		 */
		if ($templates === false) {
			if (!is_object( $reader)) {
				$reader = &$this->loadModule('Reader', $reader);
				if (patErrorManager::isError($reader)) {
					return $reader;
				}
			}

			if ($reader->isInUse()) {
				$reader = &$this->loadModule( 'Reader', $reader->getName(), array(), true);
				if( patErrorManager::isError( $reader ) ) {
					return $reader;
				}
			}

			$reader->setOptions($options);

			/**
			 * set the root attributes
			 */
			if( !is_null( $parseInto ) )
			{
				$attributes = $this->getAttributes( $parseInto );
				if( !patErrorManager::isError( $attributes ) )
				{
					$reader->setRootAttributes( $attributes );
				}
			}

			$templates	=	$reader->readTemplates( $input );

			/**
			 * check for error returned from reader
			 */
			if( patErrorManager::isError( $templates ) )
				return $templates;

			/**
			 * store the
			 */
			if( $this->_tmplCache !== null )
			{
				$this->_tmplCache->write( $key, $templates );
			}
		}

		/**
		 * traverse all templates
		 */
		foreach( $templates as $name => $spec )
		{

			/**
			 * root template
			 */
			if( $name == '__ptroot' )
			{
				if( $parseInto === false )
				{
					continue;
				}
				if( !in_array( $parseInto, $this->_templateList ) )
					continue;

				$spec['loaded']		= true;
				$spec['attributes']	= $this->_templates[$parseInto]['attributes'];
				$name	=	$parseInto;
			}
			else
			{
				/**
				 * store the name
				 */
				array_push( $this->_templateList, $name );
			}

			/**
			 * if this is the first template that has been loaded
			 * set it as the root template
			 */
			if( $this->_root === null && is_null( $parseInto ) && isset( $spec['isRoot'] ) && $spec['isRoot'] == true )
			{
				$this->_root = $name;
			}

			/**
			 * set some default values
			 */
			$spec['iteration']			=	0;
			$spec['lastMode']			=	'w';
			$spec['result']				=	'';
			$spec['modifyVars']			=	array();
			$spec['copyVars']			=	array();
			$spec['defaultVars']		=	array();

			/**
			 * store the template
			 */
			$this->_templates[$name]	=	$spec;

			$this->prepareTemplate( $name );

			/**
			 * store the default values of the variables
			 */
			foreach( $spec['varspecs'] as $varname => $varspec )
			{
				if (isset($varspec['modifier'])) {
					$this->_templates[$name]['modifyVars'][$varname] = $varspec['modifier'];
				}

				if( isset( $varspec['copyfrom'] ) )
				{
					$this->_templates[$name]['copyVars'][$varname] = $varspec['copyfrom'];
				}

				if( !isset( $varspec['default'] ) )
					continue;

				$this->_templates[$name]['defaultVars'][$varname] = $varspec['default'];

				if( !is_null( $this->getVar( $name, $varname ) ) )
					continue;

				$this->addVar( $name, $varname, $varspec['default'] );
			}

			unset($this->_templates[$name]['varspecs']);

			/**
			 * autoload the template
			 *
			 * Some error management is needed here...
			 */
			if( isset( $this->_templates[$name]['attributes']['src'] ) && $this->_templates[$name]['attributes']['autoload'] == 'on' )
			{
				if( $this->_templates[$name]['loaded'] !== true )
				{
					if( $this->_templates[$name]['attributes']['parse'] == 'on' )
					{
						$this->readTemplatesFromInput( $this->_templates[$name]['attributes']['src'], $this->_templates[$name]['attributes']['reader'], $options, $name );
					}
					else
					{
						$this->loadTemplateFromInput( $this->_templates[$name]['attributes']['src'], $this->_templates[$name]['attributes']['reader'], null, $name );
					}
					$this->_templates[$name]['loaded']	=	true;
				}
			}
		}

		return true;
	}

   /**
	* load from template cache
	*
	* @access	private
	* @param	string	name of the input (filename, shm segment, etc.)
	* @param	string	driver that is used as reader, you may also pass a Reader object
	* @param	array	options for the reader
	* @param	string	cache key
	* @return	array|boolean	either an array containing the templates, or false
	*/
	function _loadTemplatesFromCache( $input, &$reader, $options, $key )
	{
		if( is_object( $reader ) )
			$statName   =   $reader->getName();
		else
			$statName	=	$reader;

		$stat	=	&$this->loadModule( 'Stat', $statName );
		$stat->setOptions( $options );

		/**
		 * get modification time
		 */
		$modTime   = $stat->getModificationTime( $input );
		$templates = $this->_tmplCache->load( $key, $modTime );

		return $templates;
	}

   /**
	* open any input and load content into template
	*
	* @access	public
	* @param	string	name of the input (filename, shm segment, etc.)
	* @param	string	driver that is used as reader
	* @param	string	name of the template that should be used as a container,
	* @return	boolean	true, if the template could be parsed, false otherwise
	*/
	function loadTemplateFromInput( $input, $reader = 'File', $options = null, $parseInto = false )
	{
		if( is_array( $options ) )
			$options = array_merge( $this->_options, $options );
		else
			$options = $this->_options;

		if( !is_null( $parseInto ) )
			$parseInto	=	strtolower( $parseInto );

		$reader	= &$this->loadModule( 'Reader', $reader );
		if( patErrorManager::isError( $reader ) )
		{
			return $reader;
		}
		$reader->setOptions($options);

		$result	= $reader->loadTemplate( $input );

		if( patErrorManager::isError( $result ) )
		{
			return $result;
		}

		$this->_templates[$parseInto]['content'] .= $result;
		$this->_templates[$parseInto]['loaded']   = true;
		return true;
	}

   /**
	* load a template that had autoload="off"
	*
	* This is needed, if you change the source of a template and want to
	* load it, after changing the attribute.
	*
	* @access	public
	* @param	string		template name
	* @return	boolean		true, if template could be loaded
	*/
	function  loadTemplate( $template )
	{
		$template = strtolower( $template );
		if( !isset( $this->_templates[$template] ) )
		{
			return	patErrorManager::raiseWarning(
													PATTEMPLATE_WARNING_NO_TEMPLATE,
													"Template '$template' does not exist."
												);
		}

		if( $this->_templates[$template]['loaded'] === true )
			return true;

		if( $this->_templates[$template]['attributes']['parse'] == 'on' )
		{
			return $this->readTemplatesFromInput( $this->_templates[$template]['attributes']['src'], $this->_templates[$template]['attributes']['reader'], null, $template );
		}
		else
		{
			return $this->loadTemplateFromInput( $this->_templates[$template]['attributes']['src'], $this->_templates[$template]['attributes']['reader'], null, $template );
		}
	}

   /**
	* loads a patTemplate module
	*
	* Modules are located in the patTemplate folder and include:
	* - Readers
	* - Caches
	* - Variable Modifiers
	* - Filters
	* - Functions
	* - Stats
	*
	* @access	public
	* @param	string	moduleType (Reader|TemplateCache|Modifier|OutputFilter|InputFilter)
	* @param	string	moduleName
	* @param	array	parameters for the module
	* @return	object
	*/
	function &loadModule( $moduleType, $moduleName, $params = array(), $new = false )
	{
		if( !isset( $this->_modules[$moduleType] ) )
			$this->_modules[$moduleType]	=	array();

		$sig = md5( $moduleName . serialize( $params ) );

		if( isset( $this->_modules[$moduleType][$sig] ) && $new === false ) {
			return	$this->_modules[$moduleType][$sig];
		}

		if( !class_exists( 'patTemplate_Module' ) )
		{
			$file	=	sprintf( "%s/Module.php", $this->getIncludePath() );
			if( !@include_once $file )
				return	patErrorManager::raiseError( PATTEMPLATE_ERROR_BASECLASS_NOT_FOUND, 'Could not load module base class.' );
		}

		$baseClass	=	'patTemplate_' . $moduleType;
		if( !class_exists( $baseClass ) )
		{
			$baseFile	=	sprintf( "%s/%s.php", $this->getIncludePath(), $moduleType );
			if( !@include_once $baseFile )
				return	patErrorManager::raiseError( PATTEMPLATE_ERROR_BASECLASS_NOT_FOUND, "Could not load base class for $moduleType ($baseFile)." );
		}

		$moduleClass	=	'patTemplate_' . $moduleType . '_' .$moduleName;
		if( !class_exists( $moduleClass ) )
		{
			if( isset( $this->_moduleDirs[$moduleType] ) )
				$dirs = $this->_moduleDirs[$moduleType];
			else
				$dirs = array();
			array_push( $dirs, $this->getIncludePath() .'/'. $moduleType );

			$found = false;
			foreach( $dirs as $dir )
			{
				$moduleFile	= sprintf( "%s/%s.php", $dir, str_replace( '_', '/', $moduleName ) );
				if (@include_once $moduleFile) {
					$found = true;
					break;
				}
			}

			if( !$found ) {
				return	patErrorManager::raiseError( PATTEMPLATE_ERROR_MODULE_NOT_FOUND, "Could not load module $moduleClass ($moduleFile)." );
			}
		}

		if( !class_exists( $moduleClass ) )
		{
			return	patErrorManager::raiseError( PATTEMPLATE_ERROR_MODULE_NOT_FOUND, "Module file $moduleFile does not contain class $moduleClass." );
		}

		$this->_modules[$moduleType][$sig]	=	&new $moduleClass;
		if( method_exists( $this->_modules[$moduleType][$sig], 'setTemplateReference' ) )
		{
			$this->_modules[$moduleType][$sig]->setTemplateReference( $this );
		}

		$this->_modules[$moduleType][$sig]->setParams( $params );

		return $this->_modules[$moduleType][$sig];
	}

   /**
	* checks whether a module exists.
	*
	* Modules are located in the patTemplate folder and include:
	* - Readers
	* - Caches
	* - Variable Modifiers
	* - Filters
	* - Functions
	* - Stats
	*
	* @access	public
	* @param	string	moduleType (Reader|TemplateCache|Modifier|OutputFilter|InputFilter)
	* @param	string	moduleName
	* @return	boolean
	*/
	function moduleExists( $moduleType, $moduleName )
	{
		if (isset($this->_moduleDirs[$moduleType])) {
			$dirs = $this->_moduleDirs[$moduleType];
		} else {
			$dirs = array();
		}
		array_push($dirs, $this->getIncludePath() .'/'. $moduleType);

		foreach ($dirs as $dir) {
			$moduleFile	= sprintf( "%s/%s.php", $dir, str_replace( '_', '/', $moduleName ) );
			if (!file_exists($moduleFile)) {
				continue;
			}
			if (!is_readable($moduleFile)) {
				continue;
			}
			return true;
		}
		return false;
	}

   /**
	* parses a template
	*
	* Parses a template and stores the parsed content.
	* mode can be "w" for write (delete already parsed content) or "a" for append (appends the
	* new parsed content to the already parsed content)
	*
	* @access	public
	* @param	string	name of the template
	* @param	string	mode for the parsing
	*/
	function parseTemplate( $template, $mode = 'w' )
	{
		$template = strtolower($template);

		if (!isset($this->_templates[$template])) {
			return	patErrorManager::raiseWarning(
													PATTEMPLATE_WARNING_NO_TEMPLATE,
													"Template '$template' does not exist."
												);
		}

		/**
		 * template is not visible
		 */
		if ($this->_templates[$template]['attributes']['visibility'] == 'hidden') {
			$this->_templates[$template]['result']	=	'';
			$this->_templates[$template]['parsed']	=	true;
			return true;
		}

		/**
		 * check, if the template has been loaded
		 * and load it if necessary.
		 */
		if ($this->_templates[$template]['loaded'] !== true) {
			if ($this->_templates[$template]['attributes']['parse'] == 'on') {
				$result = $this->readTemplatesFromInput( $this->_templates[$template]['attributes']['src'], $this->_templates[$template]['attributes']['reader'], null, $template );
			} else {
				$result = $this->loadTemplateFromInput( $this->_templates[$template]['attributes']['src'], $this->_templates[$template]['attributes']['reader'], null, $template );
			}
			if (patErrorManager::isError($result)) {
				return $result;
			}
		}

		/**
		 * check for autoclear
		 */
		if(
			isset( $this->_templates[$template]['attributes']['autoclear'] ) &&
			$this->_templates[$template]['attributes']['autoclear'] == 'yes' &&
			$mode === 'w' &&
			$this->_templates[$template]['lastMode'] != 'a'
		  ) {
			$this->_templates[$template]['parsed']	= false;
		}

		/**
		 * template has been parsed and mode is not 'append'
		 */
		if ($this->_templates[$template]['parsed'] === true && $mode === 'w') {
			return true;
		}

		$this->_templates[$template]['lastMode'] = $mode;

		$this->_initTemplate( $template );

		if (!isset($this->_vars[$template]['rows'])) {
			$this->_vars[$template]['rows']	=	array();
		}
		$loop = count( $this->_vars[$template]['rows'] );

		/**
		 * loop at least one times
		 */
		if ($loop < 1) {
			$loop = 1;
		}

		if (isset($this->_templates[$template]['attributes']['maxloop'])) {
			$loop = ceil( $loop / $this->_templates[$template]['attributes']['maxloop'] ) * $this->_templates[$template]['attributes']['maxloop'];
		}

		$this->_templates[$template]['loop'] = max( $this->_templates[$template]['attributes']['loop'], $loop );

		$start = 0;
		if (isset($this->_templates[$template]['attributes']['limit'])) {
			$p = strpos( $this->_templates[$template]['attributes']['limit'], ',' );
			if ($p === false) {
				$this->_templates[$template]['loop'] = min( $this->_templates[$template]['loop'], $this->_templates[$template]['attributes']['limit'] );
				$start = 0;
			} else {
				$start = substr( $this->_templates[$template]['attributes']['limit'], 0, $p );
				$end   = substr( $this->_templates[$template]['attributes']['limit'], $p+1 )+$start;

				$this->_templates[$template]['loop'] = min( $this->_templates[$template]['loop'], $end );
			}
		}

		/**
		 * template should be cleared before parsing
		 */
		if ($mode == 'w') {
			$this->_templates[$template]['result']	= '';
			$this->_templates[$template]['iteration'] = $start;
		}

		$loopCount = 0;
		for ($i = $start; $i < $this->_templates[$template]['loop']; $i++) {
			$finished  = false;

			unset( $this->_templates[$template]['vars'] );

			/**
			 * fetch the variables
			 */
			$this->_fetchVariables( $template );

			/**
			 * fetch the template
			 */
			$result = $this->_fetchTemplate($template);

			if ($result === false) {
				$this->_templates[$template]['iteration']++;
				continue;
			}

			/**
			 * parse
			 */
			$this->_parseVariables( $template );
			$result = $this->_parseDependencies( $template );
			if (patErrorManager::isError($result)) {
				return $result;
			}

			/**
			 * store result
			 */
			$this->_templates[$template]['result'] .= $this->_templates[$template]['work'];

			$this->_templates[$template]['iteration']++;

			++$loopCount;

			/**
			 * check for maximum loops
			 */
			if (isset($this->_templates[$template]['attributes']['maxloop'])) {
				if ($loopCount == $this->_templates[$template]['attributes']['maxloop'] && $i < ($loop-1)) {
					$loopCount = 0;
					$finished  = true;
					$this->_templates[$template]['parsed'] = true;
					$this->parseTemplate( $this->_templates[$template]['attributes']['parent'], 'a' );
					$this->_templates[$template]['parsed'] = false;
					$this->_templates[$template]['result'] = '';
				}
			}
		}

		if (!$finished && isset($this->_templates[$template]['attributes']['maxloop'])) {
			$this->_templates[$template]['parsed'] = true;
			$this->parseTemplate( $this->_templates[$template]['attributes']['parent'], 'a', false );
			$this->_templates[$template]['parsed'] = false;
			$this->_templates[$template]['result'] = '';
			$this->_templates[$this->_templates[$template]['attributes']['parent']]['work'] = '';
		}

		$this->_parseGlobals($template);

		$this->_handleUnusedVars($template);

		$this->_templates[$template]['parsed']	= true;

		if (isset($this->_templates[$template]['attributes']['autoclear']) && $this->_templates[$template]['attributes']['autoclear'] == 'yes') {
			$this->_vars[$template] = array(
											'scalar' => array(),
											'rows'   => array()
											);
		}

		if (isset($this->_templates[$template]['attributes']['outputfilter'])) {
			if (is_object($this->_templates[$template]['attributes']['outputfilter'])) {
				$filter = &$this->_templates[$template]['attributes']['outputfilter'];
			} else {
				$filter = &$this->loadModule('OutputFilter', $this->_templates[$template]['attributes']['outputfilter']);
			}

			if (patErrorManager::isError($filter)) {
				return $filter;
			}

			$this->_templates[$template]['result'] = $filter->apply($this->_templates[$template]['result']);
		}
		return true;
	}

   /**
	* Initialize a template
	*
	* This method checks the variable specifications and
	* copys variables from other templates.
	*
	* @access	private
	* @param	string	name of the template
	* @return	boolean	true on success
	*/
	function _initTemplate( $template )
	{
		foreach( $this->_templates[$template]['copyVars'] as $dest => $src )
		{
			/**
			 * copy from the same template
			 */
			if( !is_array( $src ) )
			{
				$srcTemplate = $template;
				$srcVar	  = $src;
			}
			else
			{
				$srcTemplate = $src[0];
				$srcVar	  = $src[1];
			}

			$copied = false;

			/**
			 * copy from another template
			 */
			if( isset( $this->_vars[$srcTemplate] ) )
			{
				if( isset( $this->_vars[$srcTemplate]['scalar'][$srcVar] ) )
				{
					$this->_vars[$template]['scalar'][$dest] = $this->_vars[$srcTemplate]['scalar'][$srcVar];
					continue;
				}

				$rows = count( $this->_vars[$srcTemplate]['rows'] );

				for( $i = 0; $i < $rows; $i++ )
				{
					if( !isset( $this->_vars[$srcTemplate]['rows'][$i][$srcVar] ) )
						continue;
					if( !isset( $this->_vars[$template]['rows'][$i] ) )
						$this->_vars[$template]['rows'][$i] = array();
					$this->_vars[$template]['rows'][$i][$dest] = $this->_vars[$srcTemplate]['rows'][$i][$srcVar];
					$copied = true;
				}
			}
			if( !$copied && isset( $this->_globals[$srcVar] ))
			{
				$this->_vars[$template]['scalar'][$dest] = $this->_globals[$srcVar];
			}

		}
		return true;
	}

   /**
	* parse all variables in a template
	*
	* @access	private
	* @param	string
	*/
	function _parseVariables( $template )
	{
		/**
		 * modify variables before parsing
		 */
		$this->_applyModifers($template, $this->_templates[$template]['vars']);

		foreach( $this->_templates[$template]['vars'] as $key => $value )
		{
			if( is_array( $value ) )
			{
				if( count( $this->_templates[$template]['currentDependencies'] ) == 1 )
				{
					$child	=	$this->_templates[$template]['currentDependencies'][0];
				}
				else
				{
					if( isset( $this->_templates[$template]['attributes']['child'] ) )
						$child = $this->_templates[$template]['attributes']['child'];
					else
						continue;
				}

				$this->setAttribute( $child, 'autoclear', 'yes' );
				$this->addVar( $child, $key, $value );
				continue;
			}

			$var  = $this->_startTag.$key.$this->_endTag;
			$this->_templates[$template]['work'] = str_replace( $var, $value, $this->_templates[$template]['work'] );
		}
		return true;
	}

   /**
	* parse global variables in the template
	*
	* @access   private
	* @param	string	  name of the template
	* @return   boolean
	*/
	function _parseGlobals($template)
	{
		$globalVars = $this->_globals;
		$this->_applyModifers($template, $globalVars);

		foreach( $globalVars as $key => $value )
		{
			if( is_array( $value ) )
			{
				continue;
			}

			$var  = $this->_startTag.$key.$this->_endTag;
			$this->_templates[$template]['result'] = str_replace( $var, $value, $this->_templates[$template]['result'] );
		}
		return true;
	}

   /**
	* apply variable modifiers
	*
	* The variables will be passed by reference.
	*
	* @access   private
	* @param	string	  name of the template (use modifiers from this template)
	* @param	array	   variables to which the modifiers should be applied
	* @return   boolean
	*/
	function _applyModifers($template, &$vars)
	{
		foreach ($this->_templates[$template]['modifyVars'] as $varname => $modifier) {
			if (!isset($vars[$varname])) {
				continue;
			}

			if (($modifier['type'] === 'php' || $modifier['type'] === 'auto' ) && is_callable($modifier['mod'])) {
				$vars[$varname] = call_user_func($modifier['mod'], $vars[$varname]);
				continue;
			}

			if ($modifier['type'] === 'php') {
				continue;
			}

			$mod = &$this->loadModule( 'Modifier', ucfirst( $modifier['mod'] ) );
			$vars[$varname] = $mod->modify( $vars[$varname], $modifier['params'] );
		}

		// apply the default modifier
		if (isset($this->_templates[$template]['attributes']['defaultmodifier'])) {

			$defaultModifier = $this->_templates[$template]['attributes']['defaultmodifier'];
			if (is_callable($defaultModifier)) {
				$type = 'php';
			} else {
				$type = 'custom';
				$defaultModifier = &$this->loadModule('Modifier', ucfirst($defaultModifier));
			}


			foreach (array_keys($vars) as $varname) {
				if (isset($this->_templates[$template]['modifyVars'][$varname])) {
					continue;
				}
				if ($type === 'php') {
					$vars[$varname] = call_user_func($defaultModifier, $vars[$varname]);
				} else {
					$vars[$varname] = $defaultModifier->modify($vars[$varname], array());
				}
			}
		}

		return true;
	}

   /**
	* parse all dependencies in a template
	*
	* @access	private
	* @param	string
	*/
	function _parseDependencies($template)
	{
		$countDep	=	count( $this->_templates[$template]['currentDependencies'] );
		for ($i = 0; $i < $countDep; $i++) {
			$depTemplate = $this->_templates[$template]['currentDependencies'][$i];
			if ($depTemplate == $template) {
				return patErrorManager::raiseError(PATTEMPLATE_ERROR_RECURSION, 'You have an error in your template "' . $template . '", which leads to recursion');
			}
			$this->parseTemplate($depTemplate);
			$var	= $this->_startTag.'TMPL:'.strtoupper( $depTemplate) .$this->_endTag;
			$this->_templates[$template]['work'] = str_replace( $var, $this->_templates[$depTemplate]['result'], $this->_templates[$template]['work'] );
		}
		return true;
	}

   /**
	* fetch plain template
	*
	* The template content will be stored in the template
	* configuration so it can be used by other
	* methods.
	*
	* @access	private
	* @param	string	template name
	* @return	boolean
	*/
	function _fetchTemplate( $template )
	{
		switch( $this->_templates[$template]['attributes']['type'] )
		{
			/**
			 * condition template
			 */
			case 'condition':
				$value = $this->_getConditionValue($template, $this->_templates[$template]['attributes']['conditionvar']);
				if ($value === false) {
					$this->_templates[$template]['work']				= '';
					$this->_templates[$template]['currentDependencies']	= array();
				} else {
					$this->_templates[$template]['work']				= $this->_templates[$template]['subtemplates'][$value]['data'];
					$this->_templates[$template]['currentDependencies']	= $this->_templates[$template]['subtemplates'][$value]['dependencies'];
				}
				break;

			/**
			 * condition template
			 */
			case 'simplecondition':
				foreach( $this->_templates[$template]['attributes']['requiredvars'] as $var )
				{
					// different template scope
					if( $var[0] !== $template ) {
						$this->_fetchVariables($var[0]);
					}
					$value = null;
					// fetch the local variable
					if( isset( $this->_templates[$var[0]]['vars'][$var[1]] )
					  && strlen( $this->_templates[$var[0]]['vars'][$var[1]] ) > 0 ) {
					   $value = $this->_templates[$var[0]]['vars'][$var[1]];
					}
					if (isset($this->_templates[$template]['attributes']['useglobals'])) {
						if(isset($this->_globals[$var[1]]) && strlen($this->_globals[$var[1]]) > 1) {
							$value = $this->_globals[$var[1]];
						}
					}
					if ($value !== null) {
                        if ($var[2] === null) {
                        	continue;
                        } else {
                        	// Joomla! addition 23-June-2005
                        	// value wrapped in ## uses regex for comparison
							$condition = $var[2];
							if (substr( $condition, 0, 1 ) == '#' && substr( $condition, -1, 1 ) == '#' ) {
								if (preg_match( $condition, $value )) {
									continue;
								}
							} else if ($condition == $value) {
								continue;
							}
                        	/* Pat Original
                            if ($var[2] == $value) {
                               	continue;
                            }
                            */
                        }
                    }

					$this->_templates[$template]['work']				= '';
					$this->_templates[$template]['currentDependencies']	= array();
					break 2;
				}
				$this->_templates[$template]['work'] 				= $this->_templates[$template]['content'];
				$this->_templates[$template]['currentDependencies']	= $this->_templates[$template]['dependencies'];
				break;

			/**
			 * modulo template
			 */
			case 'modulo':
				// check for empty template

				if ($this->_hasVariables($template)) {
					$value = (string)($this->_templates[$template]['iteration'] + 1 ) % $this->_templates[$template]['attributes']['modulo'];
				} else {
					$value = '__empty';
				}

				$value = $this->_getConditionValue($template, $value, false);
				if ($value === false) {
					$this->_templates[$template]['work']				= '';
					$this->_templates[$template]['currentDependencies']	= array();
				} else {
					$this->_templates[$template]['work']				= $this->_templates[$template]['subtemplates'][$value]['data'];
					$this->_templates[$template]['currentDependencies']	= $this->_templates[$template]['subtemplates'][$value]['dependencies'];
				}
				break;

			/**
			 * standard template
			 */
			default:
				$this->_templates[$template]['work'] 				=	$this->_templates[$template]['content'];
				$this->_templates[$template]['currentDependencies']	=	$this->_templates[$template]['dependencies'];
				break;
		}
		return true;
	}

   /**
	* check, whether a template contains variables
	*
	* @access   private
	* @param	string  template name
	* @return   boolean
	*/
	function _hasVariables($template)
	{
		if (!empty($this->_vars[$template]['scalar'])) {
			return true;
		}
		if (isset($this->_vars[$template]['rows'][$this->_templates[$template]['iteration']])) {
			return true;
		}
		return false;
	}

   /**
	* fetch the value of a condition variable
	*
	* _fetchVariables() has to be called before this
	* method is being called.
	*
	* @access	private
	* @param	string	template name
	* @param	string	condition value
	* @param	boolean	flag that indicates whether value is the name of the variable that should be resolved
	*
	* @todo		split this method into smaller check methods that will be called according to
	*			a priority list
	*/
	function _getConditionValue( $template, $value, $isVar = true )
	{
		if ($isVar === true) {
			if (isset($this->_templates[$template]['attributes']['conditiontmpl'])) {
				$_template = $this->_templates[$template]['attributes']['conditiontmpl'];
				$this->_fetchVariables($_template);
			} else {
				$_template = $template;
			}

			/**
			 * get the value from the template variables
			 */
			if (!isset($this->_templates[$_template]['vars'][$value]) || strlen($this->_templates[$_template]['vars'][$value]) === 0) {
				if ($this->_templates[$template]['attributes']['useglobals'] == 'yes' || $this->_templates[$template]['attributes']['useglobals'] == 'useglobals') {
					if (isset( $this->_globals[$value] ) && strlen( $this->_globals[$value] ) > 0) {
						$value = $this->_globals[$value];
					} else {
						$value = '__empty';
					}
				} else {
					$value = '__empty';
				}
			} else {
				$value = $this->_templates[$_template]['vars'][$value];
			}
		} else {
			$_template = $template;
		}

		// if value is empty and a template for empty has been defined, this
		// has priority
		if ($value === '__empty' && isset($this->_templates[$template]['subtemplates']['__empty'])) {
			return $value;
		}

		// only one iteration (but not empty), use the __single condition
		if ($value !== '__empty' && $this->_templates[$_template]['loop'] === 1) {
			if( isset($this->_templates[$template]['subtemplates']['__single'])) {
				return '__single';
			}
		} else {

			// is __first?
			if( $this->_templates[$_template]['iteration'] == 0 ) {
				if( isset( $this->_templates[$template]['subtemplates']['__first'] ) ) {
					return '__first';
				}
			}

			/**
			 * is __last?
			 */
			if (isset($this->_templates[$_template]['loop'])) {
				$max = $this->_templates[$_template]['loop'] - 1;
				if( $this->_templates[$_template]['iteration'] == $max ) {
					if( isset( $this->_templates[$template]['subtemplates']['__last'] ) ) {
						return '__last';
					}
				}
			}
		}

		// search for exact match
		foreach (array_keys($this->_templates[$template]['subtemplates']) as $key) {
			if (isset($this->_templates[$template]['subtemplates'][$key]['attributes']['var'])) {
				$var = $this->_templates[$template]['subtemplates'][$key]['attributes']['var'];
				if (isset($this->_templates[$template]['vars'][$var])) {
					$current = $this->_templates[$template]['vars'][$var];
				} else {
					$current = null;
				}
			} else {
				$current = $key;
			}

			if ((string)$value === (string)$current) {
				return $key;
			}
		}

		/**
		 * is __default?
		 */
		if( isset( $this->_templates[$template]['subtemplates']['__default'] ) ) {
			return '__default';
		}

		return false;
	}

   /**
	* fetch variables for a template
	*
	* The variables will be stored in the template
	* configuration so they can be used by other
	* methods.
	*
	* @access	private
	* @param	string	template name
	* @return	boolean
	*/
	function _fetchVariables( $template )
	{
		/**
		 * variables already have been fetched
		 */
		if (isset($this->_templates[$template]['vars'])) {
			return true;
		}

		$iteration = $this->_templates[$template]['iteration'];

		$vars = array();
		if( isset( $this->_templates[$template]['attributes']['varscope'] ) )
		{
			if (!is_array($this->_templates[$template]['attributes']['varscope'])) {
				$this->_templates[$template]['attributes']['varscope'] = array($this->_templates[$template]['attributes']['varscope']);
			}
			foreach ($this->_templates[$template]['attributes']['varscope'] as $scopeTemplate) {
				if ($this->exists($scopeTemplate)) {
					$this->_fetchVariables( $scopeTemplate );
					$vars = array_merge($this->_templates[$scopeTemplate]['vars'], $vars);
				} else {
					patErrorManager::raiseWarning(PATTEMPLATE_WARNING_NO_TEMPLATE, 'Template \''.$scopeTemplate.'\' does not exist, referenced in varscope attribute of template \''.$template.'\'');
				}
			}
		} else {
			$vars	=	array();
		}

		/**
		 * get the scalar variables
		 */
		if( isset( $this->_vars[$template] ) && isset( $this->_vars[$template]['scalar'] ) )
		{
			$vars = array_merge( $vars, $this->_vars[$template]['scalar'] );
		}

		/**
		 * get the row variables
		 */
		if( isset( $this->_vars[$template]['rows'][$iteration] ) )
		{
			$vars = array_merge( $vars, $this->_vars[$template]['rows'][$iteration] );
		}

		/**
		 * add some system variables
		 */
		$currentRow				=	$iteration + $this->_templates[$template]['attributes']['rowoffset'];
		$vars['PAT_ROW_VAR']	=	$currentRow;

		if( $this->_templates[$template]['attributes']['type'] == 'modulo' )
		{
			$vars['PAT_MODULO_REP']	=	ceil( $currentRow / $this->_templates[$template]['attributes']['modulo'] );
			$vars['PAT_MODULO']		=	( $this->_templates[$template]['iteration'] + 1 ) % $this->_templates[$template]['attributes']['modulo'];
		}

		if( $this->_templates[$template]['attributes']['addsystemvars'] !== false )
		{
			$vars['PATTEMPLATE_VERSION'] = $this->_systemVars['appVersion'];
			$vars['PAT_LOOPS']		=	$this->_templates[$template]['loop'];

			switch ($this->_templates[$template]['attributes']['addsystemvars'])
			{
				case 'boolean':
					$trueValue  = 'true';
					$falseValue = 'false';
					break;
				case 'integer':
					$trueValue  = '1';
					$falseValue = '0';
					break;
				default:
					$trueValue  = $this->_templates[$template]['attributes']['addsystemvars'];
					$falseValue = '';
					break;
			}

			$vars['PAT_IS_ODD']		= ( $currentRow % 2 == 1 ) ? $trueValue : $falseValue;
			$vars['PAT_IS_EVEN']	= ( $currentRow % 2 == 0 ) ? $trueValue : $falseValue;
			$vars['PAT_IS_FIRST']	= ( $currentRow == 1 ) ? $trueValue : $falseValue;
			$vars['PAT_IS_LAST']	= ( $currentRow == $this->_templates[$template]['loop'] ) ? $trueValue : $falseValue;
			$vars['PAT_ROW_TYPE']	= ( $currentRow % 2 == 1 ) ? 'odd' : 'even';
		}

		$this->_templates[$template]['vars'] = $vars;
		return true;
	}

   /**
	* handle all unused variables in a template
	*
	* This is influenced by the 'unusedvars' attribute of the
	* template
	*
	* @access	private
	* @param	string
	*/
	function _handleUnusedVars( $template )
	{
		$regexp = '/([^\\\])('.$this->_startTag.'[^a-z]+[^\\\]'.$this->_endTag.')/U';

		switch( $this->_templates[$template]['attributes']['unusedvars'] )
		{
			case 'comment':
				$this->_templates[$template]['result'] = preg_replace( $regexp, '<!-- \\1\\2 -->', $this->_templates[$template]['result'] );
				break;
			case 'strip':
				$this->_templates[$template]['result'] = preg_replace( $regexp, '\\1', $this->_templates[$template]['result'] );
				break;
			case 'nbsp':
				$this->_templates[$template]['result'] = preg_replace( $regexp, '\\1&nbsp;', $this->_templates[$template]['result'] );
				break;
			case 'ignore':
				break;
			default:
				$this->_templates[$template]['result'] = preg_replace( $regexp, '\\1'.$this->_templates[$template]['attributes']['unusedvars'], $this->_templates[$template]['result'] );
				break;
		}

		// replace quoted variables
		$regexp = '/[\\\]'.$this->_startTag.'([^a-z]+)[\\\]'.$this->_endTag.'/U';
		$this->_templates[$template]['result'] = preg_replace( $regexp, $this->_startTag.'\\1'.$this->_endTag, $this->_templates[$template]['result'] );

		return true;
	}

   /**
	* returns a parsed Template
	*
	* If the template already has been parsed, it just returns the parsed template.
	* If the template has not been loaded, it will be loaded.
	*
	* @access	public
	* @param	string	 name of the template
	* @param	boolean  whether to apply output filters
	* @return	string	 Content of the parsed template
	* @see		displayParsedTemplate()
	*/
	function getParsedTemplate( $name = null, $applyFilters = false )
	{
		if (is_null($name)) {
			$name = $this->_root;
		}

		$name = strtolower( $name );
		$result = $this->parseTemplate( $name );

		if (patErrorManager::isError( $result )) {
			return $result;
		}

		if ($applyFilters === false) {
			return $this->_templates[$name]['result'];
		}

		$result = $this->_templates[$name]['result'];

		$cnt = count ($this->_outputFilters);
		for ($i = 0; $i < $cnt; $i++) {
			$result = $this->_outputFilters[$i]->apply( $result );
		}

		return $result;
	}

   /**
	* displays a parsed Template
	*
	* If the template has not been loaded, it will be loaded.
	*
	* @see		getParsedTemplate()
	* @param	string	name of the template
	* @param	boolean  whether to apply output filters
	* @return	boolean	true on success
	* @access	public
	*/
	function displayParsedTemplate($name = null, $applyFilters = true)
	{
		$result = $this->getParsedTemplate($name, $applyFilters);

		/**
		 * error happened
		 */
		if (patErrorManager::isError($result)) {
			return $result;
		}

		echo $result;
		return true;
	}

   /**
	* parse a template and push the result into a variable of any other
	* template
	*
	* If the template already has been parsed, it will just be pushed into the variable.
	* If the template has not been loaded, it will be loaded.
	*
	* @access	public
	* @param	string	name of the template
	* @return	string	Content of the parsed template
	* @param	boolean	if set to true, the value will be appended to the value already stored.
	* @see		getParsedTemplate()
	* @see		addVar()
	*/
	function parseIntoVar( $srcTmpl, $destTmpl, $var, $append = false )
	{
		$srcTmpl  =	strtolower( $srcTmpl );
		$destTmpl =	strtolower( $destTmpl );
		$var	  = strtoupper($var);

		$result	=	$this->parseTemplate( $srcTmpl );

		if( patErrorManager::isError( $result ) )
			return $result;

		if( $append !== true || !isset( $this->_vars[$destTmpl]['scalar'][$var] ) )
			$this->_vars[$destTmpl]['scalar'][$var] = '';

		$this->_vars[$destTmpl]['scalar'][$var] .= $this->_templates[$srcTmpl]['result'];

		return true;
	}

   /**
	* clears a parsed Template
	*
	* Parsed Content, variables and the loop attribute are cleared
	*
	* If you will not be using this template anymore, then you should
	* call freeTemplate()
	*
	* @access	public
	* @param	string	name of the template
	* @param	boolean		set this to true to clear all child templates, too
	* @see		clearAllTemplates()
	* @see		freeTemplate()
	*/
	function clearTemplate( $name, $recursive = false )
	{
		$name	=	strtolower( $name );
		$this->_templates[$name]['parsed']		=	false;
		$this->_templates[$name]['work']		=	'';
		$this->_templates[$name]['iteration']	=	0;
		$this->_templates[$name]['result']		=	'';
		$this->_vars[$name]						=	array(
														'scalar'	=>	array(),
														'rows'		=>	array()
													);

		if (!empty($this->_templates[$name]['defaultVars'])) {
			foreach ($this->_templates[$name]['defaultVars'] as $varname => $value) {
				$this->addVar($name, $varname, $value);
			}
		}

		/**
		 * clear child templates as well
		 */
		if( $recursive === true )
		{
			$deps = $this->_getDependencies( $name );
			foreach( $deps as $dep )
			{
				$this->clearTemplate( $dep, true );
			}
		}
		return true;
	}

   /**
	* clears all templates
	*
	* @access	public
	* @uses		clearTemplate()
	*/
	function clearAllTemplates()
	{
		$templates	=	array_keys( $this->_templates );
		$cnt		=	count( $templates );
		for( $i = 0; $i < $cnt; $i++ )
		{
			$this->clearTemplate( $templates[$i] );
		}
		return true;
	}

   /**
	* frees a template
	*
	* All memory consumed by the template
	* will be freed.
	*
	* @access	public
	* @param	string	name of the template
	* @param	boolean	clear dependencies of the template
	* @see		freeAllTemplates()
	*/
	function freeTemplate( $name, $recursive = false )
	{
		$name	=	strtolower( $name );
		$key = array_search( $name, $this->_templateList );
		if( $key === false )
		{
			return	patErrorManager::raiseWarning(
													PATTEMPLATE_WARNING_NO_TEMPLATE,
													"Template '$name' does not exist."
												);
		}

		unset( $this->_templateList[$key] );
		$this->_templateList = array_values( $this->_templateList );

		/**
		 * free child templates as well
		 */
		if( $recursive === true )
		{
			$deps = $this->_getDependencies( $name );
			foreach( $deps as $dep )
			{
				$this->freeTemplate( $dep, true );
			}
		}

		unset( $this->_templates[$name] );
		unset( $this->_vars[$name] );
		if (isset($this->_discoveredPlaceholders[$name])) {
			unset($this->_discoveredPlaceholders[$name]);
		}

		return true;
	}

   /**
	* frees all templates
	*
	* All memory consumed by the templates
	* will be freed.
	*
	* @access	public
	* @see		freeTemplate()
	*/
	function freeAllTemplates()
	{
		$this->_templates	 = array();
		$this->_vars		 = array();
		$this->_templateList = array();
	}

   /**
	* get _all_ dependencies of a template,
	* regardless of the subtemplates
	*
	* @access	private
	* @param	string	template name
	* @return	array	list of all subtemplates
	*/
	function _getDependencies( $template )
	{
		$deps = array();
		if( isset( $this->_templates[$template]['dependencies'] ) )
			$deps = $this->_templates[$template]['dependencies'];

		if( isset( $this->_templates[$template]['subtemplates'] ) )
		{
			foreach( $this->_templates[$template]['subtemplates'] as $sub )
			{
				if( isset( $sub['dependencies'] ) )
					$deps = array_merge( $deps, $sub['dependencies'] );
			}
		}
		$deps = array_unique( $deps );
		return $deps;
	}

   /**
	* Displays useful information about all or named templates
	*
	* This method breaks BC, as it now awaits an array instead of
	* unlimited parameters.
	*
	* @param	mixed	array of templates that should be dumped, or null if you
	*					want all templates to be dumped
	* @param	string	dumper
	* @access	public
	*/
	function dump( $restrict = null, $dumper = 'Html' )
	{
		if( is_string( $restrict ) )
			$restrict = array( $restrict );

		$dumper	=	&$this->loadModule( 'Dump', $dumper );

		if( patErrorManager::isError( $dumper ) )
		{
			return	$dumper;
		}

		if( is_null( $restrict ) )
		{
			$templates = $this->_templates;
			$vars	  = $this->_vars;
		}
		else
		{
			$restrict = array_map( 'strtolower', $restrict );

			$templates = array();
			$vars	  = array();

			foreach( $this->_templates as $name => $spec )
			{
				if( !in_array( $name, $restrict ) )
					continue;
				$templates[$name] = $spec;
				$vars[$name]	  = $this->_vars[$name];
			}
		}

		$dumper->displayHeader();
		$dumper->dumpGlobals( $this->_globals );
		$dumper->dumpTemplates( $templates, $vars );
		$dumper->displayFooter();

		return	true;
	}

   /**
	* get the include path
	*
	* @access	public
	* @return   string
	*/
	function getIncludePath()
	{
		return	PATTEMPLATE_INCLUDE_PATH;
	}

   /**
	* apply input filters that have been set
	*
	* This is being called by the readers.
	*
	* @access	public
	* @param	string		template
	* @return	string		filtered templeta
	*/
	function applyInputFilters( $template )
	{
		$cnt = count( $this->_inputFilters );
		for( $i = 0; $i < $cnt; $i++ )
		{
			$template = $this->_inputFilters[$i]->apply( $template );
		}
		return $template;
	}

   /**
	* checks, whether a placeholder exists in a template
	*
	* @access   public
	* @param	string	  name of the placeholder
	* @param	string	  name of the template
	* @param	boolean	 whether to use the cached result of a previous call
	*/
	function placeholderExists($placeholder, $tmpl, $cached = true)
	{
		$tmpl = strtolower($tmpl);
		$placeholder = strtoupper($placeholder);

		if (!$this->exists($tmpl)) {
			return false;
		}

		if ($cached === true) {
			if (isset($this->_discoveredPlaceholders[$tmpl]) && isset($this->_discoveredPlaceholders[$tmpl][$placeholder])) {
				return $this->_discoveredPlaceholders[$tmpl][$placeholder];
			}
		}

		if (isset($this->_templates[$tmpl]['subtemplates'])) {
			$content = '';
			foreach ($this->_templates[$tmpl]['subtemplates'] as $temp) {
				if (!isset($temp['data'])) {
					continue;
				}
				$content .= $temp['data'];
			}
		} else {
			$content = $this->_templates[$tmpl]['content'];
		}

		$search = $this->_startTag . $placeholder . $this->_endTag;
		if (strstr($content, $search) !== false) {
			$this->_discoveredPlaceholders[$tmpl][$placeholder] = true;
			return true;
		}
		$this->_discoveredPlaceholders[$tmpl][$placeholder] = false;
		return false;
	}

   /**
	* Convert the template to its string representation.
	*
	* This method allows you to just echo the patTemplate
	* object in order to display the template.
	*
	* Requires PHP5
	*
	* <code>
	* $tmpl = new patTemplate();
	* $tmpl->readTemplatesFromFile( 'myfile.tmpl' );
	* echo $tmpl;
	* </code>
	*
	* @access	private
	* @return	string
	*/
	function __toString()
	{
		return $this->getParsedTemplate();
	}
}
?>