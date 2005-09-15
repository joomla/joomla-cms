<?PHP
/**
 * Base class for patTemplate readers
 *
 * $Id: Reader.php 138 2005-09-12 10:37:53Z eddieajau $
 *
 * This class is able to parse patTemplate tags from any string you hand it over
 * It will emulate some kind of SAX parsing by calling start-, end- and CData-handlers.
 *
 * @package		patTemplate
 * @subpackage	Readers
 * @author		Stephan Schmidt <schst@php.net>
 */
/**
 * No input
 */
define( 'PATTEMPLATE_READER_ERROR_NO_INPUT', 6000 );

/**
 * Unknown tag
 */
define( 'PATTEMPLATE_READER_ERROR_UNKNOWN_TAG', 6001 );

/**
 * Invalid tag (missing attribute)
 */
define( 'PATTEMPLATE_READER_ERROR_INVALID_TAG', 6002 );

/**
 * Closing tag is missing
 */
define( 'PATTEMPLATE_READER_ERROR_NO_CLOSING_TAG', 6003 );

/**
 * Invalid closing tag
 */
define( 'PATTEMPLATE_READER_ERROR_INVALID_CLOSING_TAG', 6004 );

/**
 * Invalid condition specified
 */
define( 'PATTEMPLATE_READER_ERROR_INVALID_CONDITION', 6005 );

/**
 * No name has been specified
 */
define( 'PATTEMPLATE_READER_ERROR_NO_NAME_SPECIFIED', 6010 );

/**
 * CData in a conditional template
 */
define( 'PATTEMPLATE_READER_NOTICE_INVALID_CDATA_SECTION', 6050 );

/**
 * template already exists
 */
define( 'PATTEMPLATE_READER_NOTICE_TEMPLATE_EXISTS', 6051 );

/**
 * Base class for patTemplate readers
 *
 * This class is able to parse patTemplate tags from any string you hand it over
 * It will emulate some kind of SAX parsing by calling start-, end- and CData-handlers.
 *
 * @abstract
 * @package		patTemplate
 * @subpackage	Readers
 * @author		Stephan Schmidt <schst@php.net>
 */
class patTemplate_Reader extends patTemplate_Module
{
   /**
	* reference to the patTemplate object that instantiated the module
	*
	* @access	protected
	* @var	object
	*/
	var	$_tmpl;

   /**
	* stack for all open elements
	* @access	private
	* @var	array
	*/
	var	$_elStack;

   /**
	* stack for all open templates
	* @access	private
	* @var	array
	*/
	var	$_tmplStack;

   /**
	* character data
	* @access	private
	* @var	array
	*/
	var	$_data;

   /**
	* tag depth
	* @access	private
	* @var	integer
	*/
	var	$_depth;

   /**
   	* templates that have been found
	* @access	protected
	* @var		array
	*/
	var $_templates	=	array();

   /**
   	* path to the template
	* @access	protected
	* @var		array
	*/
	var $_path	=	array();

   /**
	* start tag for variables
	* @access	private
	* @var		string
	*/
	var	$_startTag;

   /**
	* end tag for variables
	* @access	private
	* @var		string
	*/
	var	$_endTag;

   /**
	* default attributes
	*
	* @access	private
	* @var		array
	*/
	var	$_defaultAtts	=	array();

   /**
	* root attributes
	*
	* This is used when reading the template content
	* from an external file.
	*
	* @access	private
	* @var		array
	*/
	var	$_rootAtts	=	array();

   /**
	* inherit attributes
	*
	* @access	private
	* @var		array
	*/
	var	$_inheritAtts	=	array();

   /**
	* name of the first template that has been found
	*
	* @access	private
	* @var		string
	*/
	var	$_root = null;

   /**
	* all data that has been processed
	*
	* @access	private
	* @var		string
	*/
	var	$_processedData = null;

   /**
	* current input
	*
	* @access	private
	* @var		string
	*/
	var	$_currentInput = null;

   /**
	* all loaded functions
	*
	* @access	private
	* @var		array
	*/
	var	$_functions	=	array();

   /**
	* function aliases
	*
	* @access   private
	* @var	  array
	*/
	var $_funcAliases = array();

   /**
	* options
	*
	* @access   private
	* @var	  array
	*/
	var $_options = array();

   /**
	* reader is in use
	*
	* @access   private
	* @var	  boolean
	*/
	var $_inUse = false;

   /**
	* set a reference to the patTemplate object that instantiated the reader
	*
	* @access	public
	* @param	object		patTemplate object
	*/
	function setTemplateReference( &$tmpl )
	{
		$this->_tmpl = &$tmpl;
	}

   /**
	* read templates from any input
	*
	* @abstract	must be implemented in the template readers
	* @param	mixed	input to read from.
	*					This can be a string, a filename, a resource or whatever the derived class needs to read from
	* @param	array	options, not implemented in current versions, but future versions will allow passing of options
	* @return	array	template structure
	*/
	function readTemplates( $input, $options = array() )
	{
		return array();
	}

   /**
	* load template from any input
	*
	* If the a template is loaded, the content will not get
	* analyzed but the whole content is returned as a string.
	*
	* @abstract	must be implemented in the template readers
	* @param	mixed	input to load from.
	*					This can be a string, a filename, a resource or whatever the derived class needs to read from
	* @param	array	options, not implemented in current versions, but future versions will allow passing of options
	* @return	string  template content
	*/
	function loadTemplate( $input, $options = array() )
	{
		return $input;
	}

   /**
	* set options
	*
	* @access	public
	* @param	array	array containing options
	*/
	function setOptions( $options )
	{
		$this->_startTag = $options['startTag'];
		$this->_endTag   = $options['endTag'];

		$this->_options  = $options;

		if (isset($options['functionAliases'])) {
			$this->_funcAliases = $options['functionAliases'];
		}
		array_map('strtolower', $this->_funcAliases);
	}

   /**
	* add an alias for a function
	*
	* @access   public
	* @param	string  alias
	* @param	string  function name
	*/
	function addFunctionAlias($alias, $function)
	{
		$this->_funcAliases[strtolower($alias)] = $function;
	}

   /**
	* set the root attributes
	*
	* @access	public
	* @param	array	array containing options
	*/
	function setRootAttributes( $attributes )
	{
		$this->_rootAtts = $attributes;
	}

   /**
	* parse templates from string
	*
	* @access	private
	* @param	string		string to parse
	* @return	array		templates
	*/
	function parseString( $string )
	{
		$this->_inUse = true;

		/**
		 * apply input filter before parsing
		 */
		$string = $this->_tmpl->applyInputFilters( $string );

		$this->_inheritAtts	  =	array();
		$this->_elStack		  =	array();
		$this->_data		  =	array( '' );
		$this->_tmplStack	  =	array();
		$this->_depth		  =	0;
		$this->_templates	  =	array();
		$this->_path		  =	array();
		$this->_processedData =	'';

		$this->_defaultAtts	= $this->_tmpl->getDefaultAttributes();

		if( !isset( $this->_defaultAtts['autoload'] ) ) {
			$this->_defaultAtts['autoload']	= 'on';
		}

		/**
		 * create a special root template
		 */
		$attributes			= $this->_rootAtts;
		$attributes['name']	= '__ptroot';

		$rootTemplate = $this->_initTemplate( $attributes );
		$this->_root  = null;
		unset( $rootTemplate['isRoot'] );

		array_push( $this->_tmplStack, $rootTemplate );

		$patNamespace = $this->_tmpl->getNamespace();
		if (is_array($patNamespace)) {
			$patNamespace = array_map('strtolower', $patNamespace);
		} else {
			$patNamespace = array(strtolower( $patNamespace ));
		}

		/**
		 *start parsing
		 */
		$regexp	=	'/(<(\/?)([[:alnum:]]+):([[:alnum:]]+)[[:space:]]*([^>]*)>)/im';

		$tokens	=	preg_split( $regexp, $string, -1, PREG_SPLIT_DELIM_CAPTURE );

		/**
		 * the first token is always character data
		 * Though it could just be empty
		 */
		if( $tokens[0] != '' ) {
			$this->_characterData( $tokens[0] );
		}

		$cnt	=	count( $tokens );
		$i		=	1;
		// process all tokens
		while( $i < $cnt ) {
			$fullTag	= $tokens[$i++];
			$closing	= $tokens[$i++];
			$namespace	= strtolower( $tokens[$i++] );
			$tagname	= strtolower( $tokens[$i++] );
			$attString	= $tokens[$i++];
			$empty		= substr( $attString, -1 );
			$data		= $tokens[$i++];

			/**
			 * check, whether it's a known namespace
			 * currently only the template namespace is possible
			 */
			 if( !in_array($namespace, $patNamespace) ) {
			 	$this->_characterData( $fullTag );
			 	$this->_characterData( $data );
				continue;
			 }

			/**
			 * is it a closing tag?
			 */
			if( $closing === '/' ) {
				$result	=	$this->_endElement( $namespace, $tagname );
				if( patErrorManager::isError( $result ) ) {
					return	$result;
				}
				$this->_characterData( $data );
				continue;
			}

			/**
			 * Is empty or opening tag!
			 */
			if( $empty === '/' ) {
				$attString	=	substr( $attString, 0, -1 );
			}

			$attributes	=	$this->_parseAttributes( $attString );
			$result 	=	$this->_startElement( $namespace, $tagname, $attributes );
			if( patErrorManager::isError( $result ) ) {
				return	$result;
			}

			/**
			 * check, if the tag is empty
			 */
			if( $empty === '/' ) {
				$result	=	$this->_endElement( $namespace, $tagname );
				if( patErrorManager::isError( $result ) ) {
					return	$result;
				}
			}

			$this->_characterData( $data );
		}

		$rootTemplate = array_pop( $this->_tmplStack );

		$this->_closeTemplate( $rootTemplate, $this->_data[0] );

		/**
		 * check for tags that are still open
		 */
		if( $this->_depth > 0 ) {
			$el	=	array_pop( $this->_elStack );
			return patErrorManager::raiseError(
				PATTEMPLATE_READER_ERROR_NO_CLOSING_TAG,
				$this->_createErrorMessage( "No closing tag for {$el['ns']}:{$el['name']} found" )
			);
		}

		$this->_inUse = false;

		return	$this->_templates;
	}

   /**
	* parse an attribute string and build an array
	*
	* @access	private
	* @param	string	attribute string
	* @param	array	attribute array
	*/
	function _parseAttributes( $string )
	{
	 	static $entities = array(
									'&lt;' => '<',
									'&gt;' => '>',
									'&amp;' => '&',
									'&quot;' => '"',
									'&apos;' => '\''
								);

		$attributes	=	array();
		$match = array();
		preg_match_all('/([a-zA-Z_0-9]+)="((?:\\\.|[^"\\\])*)"/U', $string, $match);
		for ($i = 0; $i < count($match[1]); $i++) {
			$attributes[strtolower( $match[1][$i] )] = strtr( (string)$match[2][$i], $entities );
		}
		return	$attributes;
	}

   /**
	* handle start element
	*
	* @access	private
	* @param	string		element name
	* @param	array		attributes
	*/
	function _startElement( $ns, $name, $attributes )
	{
		array_push( $this->_elStack, array(
											'ns'			=>  $ns,
											'name'			=>	$name,
											'attributes'	=>	$attributes,
										)
				 );

		$this->_depth++;

		$this->_data[$this->_depth]	=	'';

		/**
		 * handle tag
		 */
		switch( $name )
		{
			/**
			 * template
			 */
			case 'tmpl':
				$result	=	$this->_initTemplate( $attributes );
				break;

			/**
			 * sub-template
			 */
			case 'sub':
				$result	=	$this->_initSubTemplate( $attributes );
				break;

			/**
			 * link
			 */
			case 'link':
				$result	=	$this->_initLink( $attributes );
				break;

			/**
			 * variable
			 */
			case 'var':
				$result	=	false;
				break;

			/**
			 * instance
			 */
			case 'instance':
			case 'comment':
				$result	=	false;
				break;

			/**
			 * any other tag
			 */
			default:
				if (isset($this->_funcAliases[strtolower($name)])) {
					$name = $this->_funcAliases[strtolower($name)];
				}
				$name = ucfirst( $name );

				if( !$this->_tmpl->moduleExists( 'Function', $name ) ) {

					if (isset($this->_options['defaultFunction']) && !empty($this->_options['defaultFunction'])) {
						$attributes['_originalTag'] = $name;
						$name = ucfirst($this->_options['defaultFunction']);
					} else {
						return patErrorManager::raiseError(
															PATTEMPLATE_READER_ERROR_UNKNOWN_TAG,
															$this->_createErrorMessage( "Unknown tag {$ns}:{$name}." )
														);
					}
				}
				$result = array(
								'type'	   => 'custom',
								'function'   => $name,
								'attributes' => $attributes
								);
				break;
		}

		if( patErrorManager::isError( $result ) ) {
			return	$result;
		}

		array_push( $this->_tmplStack, $result );
		return true;
	}

   /**
	* handle end element
	*
	* @access	private
	* @param	string		element name
	*/
	function _endElement( $ns, $name )
	{
		$el			=	array_pop( $this->_elStack );
		$data		=	$this->_getCData();
		$this->_depth--;

		if( $el['name'] != $name || $el['ns'] != $ns ) {
			return patErrorManager::raiseError(
				PATTEMPLATE_READER_ERROR_INVALID_CLOSING_TAG,
				$this->_createErrorMessage( "Invalid closing tag {$ns}:{$name}, {$el['ns']}:{$el['name']} expected" )
			);
		}

		$tmpl	=	array_pop( $this->_tmplStack );

		/**
		 * handle tag
		 */
		switch( $name )
		{
			/**
			 * template
			 */
			case 'tmpl':
				$this->_closeTemplate( $tmpl, $data );
				break;

			/**
			 * sub-template
			 */
			case 'sub':
				$this->_closeSubTemplate( $tmpl, $data );
				break;

			/**
			 * link
			 */
			case 'link':
				$this->_closeLink( $tmpl );
				break;

			/**
			 * variable
			 */
			case 'var':
				$this->_handleVariable( $el['attributes'], $data );
				break;

			/**
			 * instance
			 */
			case 'instance':
				break;

			/**
			 * comment
			 */
			case 'comment':
				$this->_handleComment( $el['attributes'], $data );
				break;

			/**
			 * custom function
			 */
			default:
				$name = ucfirst( $tmpl['function'] );

				if( !isset( $this->_functions[$name] ) ) {
					$this->_functions[$name] = $this->_tmpl->loadModule( 'Function', $name );
					$this->_functions[$name]->setReader( $this );
				}

				$result = $this->_functions[$name]->call( $tmpl['attributes'], $data );

				if( patErrorManager::isError( $result ) ) {
					return $result;
				}

				if( is_string( $result ) ) {
					$this->_characterData( $result, false );
				}
				break;
		}
		return true;
	}

   /**
	* handle character data
	*
	* @access	private
	* @param	string		data
	*/
	function _characterData( $data, $readFromTemplate = true )
	{
		$this->_data[$this->_depth]	.=	$data;

		if ($readFromTemplate) {
			$this->_processedData .= $data;
		}

		return	true;
	}

   /**
	* handle a Link
	*
	* @access	private
	* @param	array		attributes
	* @return	boolean		true on success
	*/
	function _initLink( $attributes )
	{
		/**
		 * needs a src attribute
		 */
		if( !isset( $attributes['src'] ) ) {
			return patErrorManager::raiseError(
												PATTEMPLATE_READER_ERROR_INVALID_TAG,
												$this->_createErrorMessage( "Attribute 'src' missing for link" )
												);
		}

		/**
		 * create a new template
		 */
		$tmpl	=	array(
							'type'			=>	'link',
							'src'			=>	$attributes['src'],
						);
		return $tmpl;
	}

   /**
	* close a link template
	*
	* It will be added to the dependecies of the parent template.
	*
	* @access	private
	* @param	array	template definition for the link
	*/
	function _closeLink( $tmpl )
	{
		/**
		 * add it to the dependencies
		 */
		if( !empty( $this->_tmplStack ) )
		{
			$this->_addToParentTag( 'dependencies', strtolower( $tmpl['src'] ) );
			$this->_characterData( sprintf( "%sTMPL:%s%s", $this->_startTag, strtoupper( $tmpl['src'] ), $this->_endTag ) );
		}

		return true;
	}

   /**
	* create a new template
	*
	* @access	private
	* @param	array		attributes
	* @return	boolean		true on success
	*/
	function _initTemplate( $attributes )
	{
		/**
		 * build name for the template
		 */
		if (!isset( $attributes['name'] )) {
			$name	=	$this->_buildTemplateName();
		} else {
			$name	=	strtolower( $attributes['name'] );
			unset( $attributes['name'] );
		}

		/**
		 * name must be unique
		 */
		if( isset( $this->_templates[$name] ) || $this->_tmpl->exists( $name ) ) {
			patErrorManager::raiseNotice(
										PATTEMPLATE_READER_NOTICE_TEMPLATE_EXISTS,
										$this->_createErrorMessage( "Template $name already exists" ),
										$name
										);
		}

		/**
		 * update the path
		 */
		array_push( $this->_path, $name );

		if( isset( $attributes['maxloop'] ) ) {
			if (!isset( $attributes['parent'] )) {
				$attributes['parent'] = $this->_getFromParentTemplate( 'name' );
			}
		}

		$attributes	= $this->_prepareTmplAttributes( $attributes, $name );

		array_push( $this->_inheritAtts, array(
												'whitespace' => $attributes['whitespace'],
												'unusedvars' => $attributes['unusedvars'],
												'autoclear'  => $attributes['autoclear']
											)
				 );

		/**
		 * create a new template
		 */
		$tmpl	=	array(
							'type'			=>	'tmpl',
							'name'			=>	$name,
							'attributes'	=>	$attributes,
							'content'		=>	'',
							'dependencies'	=>	array(),
							'varspecs'		=>	array(),
							'comments'		=>	array(),
							'loaded'		=>	false,
							'parsed'		=>	false,
							'input'			=>  $this->_name.'://'.$this->_currentInput
						);

		if( $this->_root == null ) {
			$this->_root = $name;
			$tmpl['isRoot'] = true;
		}


		/**
		 * prepare subtemplates
		 */
		switch( $attributes['type'] ) {
			case 'condition':
			case 'modulo':
				$tmpl['subtemplates']	=	array();
				break;
		}

		return $tmpl;
	}

   /**
	* prepare attributes
	*
	* @access	private
	* @param	array	attributes
	* @param	string	template name (only used for error messages)
	* @return	array	attributes
	*/
	function _prepareTmplAttributes( $attributes, $templatename )
	{
		/**
		 * do not prepare twice
		 */
		if( isset( $attributes['__prepared'] ) && $attributes['__prepared'] === true ) {
			return $attributes;
		}

		$attributes	= $this->_inheritAttributes( $attributes );

		/**
		 * get the attributes
		 */
		$attributes	= array_merge( $this->_tmpl->getDefaultAttributes(), $attributes );

		$attributes['type']	= strtolower( $attributes['type'] );

		if( !isset( $attributes['rowoffset'] ) ) {
			$attributes['rowoffset'] = 1;
		}

		if( !isset( $attributes['addsystemvars'] ) ) {
			$attributes['addsystemvars'] = false;
		} else {
			switch ($attributes['addsystemvars']) {
				case 'on':
				case 'boolean':
					$attributes['addsystemvars'] = 'boolean';
					break;
				case 'int':
				case 'integer':
					$attributes['addsystemvars'] = 'integer';
					break;
				case 'off':
					$attributes['addsystemvars'] = false;
					break;
			}
		}

		/**
		 * external template
		 */
		if( isset( $attributes['src'] ) ) {
		 	if( !isset( $attributes['parse'] ) )
				$attributes['parse']	=	'on';
		 	if( !isset( $attributes['reader'] ) )
				$attributes['reader']	=	$this->getName();
		 	if( !isset( $attributes['autoload'] ) )
				$attributes['autoload']	=	$this->_defaultAtts['autoload'];

		 	if (isset($attributes['relative']) && strtolower($attributes['relative'] === 'yes')) {
				$attributes['relative']	= $this->getCurrentInput();
		 	} else {
				$attributes['relative']	= false;
		 	}
		}

		/**
		 * varscope is set
		 */
		if( isset( $attributes['varscope'] ) ) {
		 	/**
			 * varscope is parent
			 */
		 	if( $attributes['varscope'] === '__parent' ) {
				$attributes['varscope'] = $this->_getFromParentTemplate( 'name' );
			}

			$attributes['varscope']	= strtolower( $attributes['varscope'] );
			if (strstr($attributes['varscope'], ',')) {
				$attributes['varscope'] = array_map('trim', explode(',', $attributes['varscope']));
			}
		}

		switch( $attributes['type'] ) {
			/**
			 * validate condition template
			 */
			case	'condition':
				if( !isset( $attributes['conditionvar'] ) ) {
					return patErrorManager::raiseError(
														PATTEMPLATE_READER_ERROR_INVALID_TAG,
														$this->_createErrorMessage( "Attribute 'conditionvar' missing for $templatename" )
														);
				}
				$attributes['conditionvar']	=	strtoupper( $attributes['conditionvar'] );

				if( strstr( $attributes['conditionvar'], '.' ) ) {
					list( $attributes['conditiontmpl'], $attributes['conditionvar'] ) = explode( '.', $attributes['conditionvar'] );
					$attributes['conditiontmpl'] = strtolower( $attributes['conditiontmpl'] );
				}

				$attributes['autoclear']	=	'yes';

				if (!isset( $attributes['useglobals'] )) {
					$attributes['useglobals']	=	'no';
				}
				break;

			/**
			 * validate simplecondition template
			 */
			case	'simplecondition':
				if( !isset( $attributes['requiredvars'] ) ) {
					return patErrorManager::raiseError(
														PATTEMPLATE_READER_ERROR_INVALID_TAG,
														$this->_createErrorMessage( "Attribute 'requiredvars' missing for $templatename" )
														);
				}
				$tmp = array_map( 'trim', explode( ',', $attributes['requiredvars'] ) );
				$attributes['requiredvars']   = array();
				foreach( $tmp as $var ) {

					$pos = strpos( $var, '=' );
					if ($pos !== false) {
						$val = trim(substr( $var, $pos+1 ));
						$var = trim(substr( $var, 0, $pos ));
					} else {
						$val = null;
					}
					$var = strtoupper($var);
					$pos = strpos( $var, '.' );

					if ($pos === false) {
						array_push( $attributes['requiredvars'], array( $templatename, $var, $val ) );
					} else {
						array_push( $attributes['requiredvars'], array(
																		strtolower( substr( $var, 0, $pos ) ),
																		substr( $var, $pos+1 ),
																		$val
																	)
								);
					}

				}
				$attributes['autoclear'] = 'yes';
				break;

			/**
			 * oddeven => switch to new modulo syntax
			 */
			case	'oddeven':
				$attributes['type']		 = 'modulo';
				$attributes['modulo']	 = 2;
				$attributes['autoclear'] = 'yes';
				break;

			/**
			 * modulo => requires a module attribute
			 */
			case	'modulo':
				if( !isset( $attributes['modulo'] ) ) {
					return patErrorManager::raiseError(
														PATTEMPLATE_READER_ERROR_INVALID_TAG,
														$this->_createErrorMessage( "Attribute 'modulo' missing for $templatename" )
														);
				}
				$attributes['autoclear'] = 'yes';
				break;

			/**
			 * standard template => do nothing
			 */
			case	'standard':
				break;

			/**
			 * unknown type
			 */
			default:
				return patErrorManager::raiseError(
													PATTEMPLATE_READER_ERROR_INVALID_TAG,
													$this->_createErrorMessage( "Unknown value for attribute type: {$attributes['type']}" )
													);
				break;
		}

		$attributes['__prepared'] = true;

		return $attributes;
	}

   /**
	* build a template name
	*
	* @access	private
	* @return	string	new template name
	*/
	function _buildTemplateName()
	{
		return strtolower( uniqid( 'tmpl' ) );
	}

   /**
	* close the current template
	*
	* @access	private
	* @return	boolean	true on success
	*/
	function _closeTemplate( $tmpl, $data )
	{
		$name = array_pop( $this->_path );

		$data = $this->_adjustWhitespace( $data, $tmpl['attributes']['whitespace'] );

		array_pop( $this->_inheritAtts );

		/**
		 * check for special templates
		 */
		switch( $tmpl['attributes']['type'] )
		{
			/**
			 * check for whitespace in conditional templates
			 * and raise a notice
			 */
			case	'condition':
			case	'modulo':
				if( trim( $data ) != '' ) {
					patErrorManager::raiseNotice(
													PATTEMPLATE_READER_NOTICE_INVALID_CDATA_SECTION,
													$this->_createErrorMessage( sprintf( 'No cdata is allowed inside a template of type %s (cdata was found in %s)', $tmpl['attributes']['type'], $tmpl['name'] ) )
												);
				}
				$data	=	null;
				break;
		}

		/**
		 * store the content
		 */
		$tmpl['content'] = $data;

		/**
		 * No external template
		 */
		if( !isset( $tmpl['attributes']['src'] ) ) {
			$tmpl['loaded']	=	true;
		}

		/**
		 * add it to the dependencies
		 */
		 if( !empty( $this->_tmplStack ) ) {
			$this->_addToParentTag( 'dependencies', $name );

			if( isset( $tmpl['attributes']['placeholder'] ) ) {
				// maintain BC
				if( $this->shouldMaintainBc() && $tmpl['attributes']['placeholder'] === 'none' ) {
					$tmpl['attributes']['placeholder'] = '__none';
				}

				if( $tmpl['attributes']['placeholder'] !== '__none' ) {
					$this->_characterData( $this->_startTag.(strtoupper( $tmpl['attributes']['placeholder'] ) ).$this->_endTag );
				}
			} else {
				$this->_characterData( sprintf( "%sTMPL:%s%s", $this->_startTag, strtoupper( $name ), $this->_endTag ) );
			}
		 }

		unset( $tmpl['name'] );
		unset( $tmpl['tag'] );

		$this->_templates[$name] = $tmpl;

		return true;
	}

   /**
	* create a new sub-template
	*
	* @access	private
	* @param	array		attributes
	* @return	boolean		true on success
	*/
	function _initSubTemplate( $attributes )
	{
		/**
		 * has to be embedded in a 'tmpl' tag
		 */
		if (!$this->_parentTagIs('tmpl')) {
			return patErrorManager::raiseError(
												PATTEMPLATE_READER_ERROR_INVALID_TAG,
												$this->_createErrorMessage( 'A subtemplate is only allowed in a TMPL tag' )
												);
		}

		/**
		 * needs a condition attribute
		 */
		if (!isset( $attributes['condition'] )) {
			return patErrorManager::raiseError(
												PATTEMPLATE_READER_ERROR_NO_CONDITION_SPECIFIED,
												$this->_createErrorMessage( 'Missing \'condition\' attribute for subtemplate' )
												);
		}
		$matches = array();
		$regexp = '/^'.$this->_startTag.'([^a-z]+[^\\\])'.$this->_endTag.'$/U';
		if (preg_match($regexp, $attributes['condition'], $matches)) {
			$attributes['var'] = $matches[1];
		}

		/**
		 * maintain BC
		 */
		if( $this->shouldMaintainBc() && in_array( $attributes['condition'], array( 'default', 'empty', 'odd', 'even' ) ) ) {
			$attributes['condition'] = '__' . $attributes['condition'];
		}

		if( $attributes['condition'] == '__odd' ) {
			$attributes['condition'] = 1;
		} elseif( $attributes['condition'] == '__even' ) {
			$attributes['condition'] = 0;
		}

		$parent	= array_pop( $this->_tmplStack );
		array_push( $this->_tmplStack, $parent );
		if ($parent['attributes']['type'] == 'modulo') {

			if( preg_match( '/^\d$/', $attributes['condition'] ) ) {
				if( (integer)$attributes['condition'] >= $parent['attributes']['modulo'] ) {
					return patErrorManager::raiseError(
														PATTEMPLATE_READER_ERROR_INVALID_CONDITION,
														$this->_createErrorMessage( 'Condition may only be between 0 and '.($parent['attributes']['modulo']-1) )
													);
				}
			}
		}

		$attributes = $this->_inheritAttributes( $attributes );

		$condition  = $attributes['condition'];
		unset( $attributes['condition'] );

		$subTmpl = array(
						'type'			=>	'sub',
						'condition'		=>	$condition,
						'data'			=>	'',
						'attributes'	=>	$attributes,
						'comments'		=>	array(),
						'dependencies'	=>	array()
						);

		return	$subTmpl;
	}

   /**
	* close subtemplate
	*
	* @access	private
	* @param	string		data
	* @return	boolean		true on success
	*/
	function _closeSubTemplate( $subTmpl, $data )
	{
		$data				=	$this->_adjustWhitespace( $data, $subTmpl['attributes']['whitespace'] );

		$subTmpl['data']	=	$data;
		$condition			=	$subTmpl['condition'];
		unset( $subTmpl['condition'] );

		$this->_addToParentTemplate( 'subtemplates',
									  $subTmpl,
									  $condition
									);
		return true;
	}

   /**
	* handle a variable
	*
	* @access	private
	* @param	array	attributes of the var tag
	* @param	string	cdata between the tags (will be used as default)
	* @return	boolean	true on success
	*/
	function _handleVariable( $attributes, $data )
	{
		if( !isset( $attributes['name'] ) ) {
			return patErrorManager::raiseError(
												PATTEMPLATE_READER_ERROR_NO_NAME_SPECIFIED,
												$this->_createErrorMessage( 'Variable needs a name attribute' )
												);
		}

		$specs = array();

		/**
		 * get name
		 */
		$name	=	strtoupper( $attributes['name'] );
		unset( $attributes['name'] );
		$specs['name']	=	$name;

		/**
		 * use data as default value
		 */
		if( isset( $attributes['default'] ) ) {
			$data 				=	$attributes['default'];
			$specs['default']	=	$data;
			unset( $attributes['default'] );
		} elseif (!empty( $data )) {
			$specs['default']	=	$data;
		}

		/**
		 * add it to template, if it's not hidden
		 */
		if (!isset( $attributes['hidden'] ) || $attributes['hidden'] == 'no') {
			$this->_characterData( $this->_startTag . strtoupper( $name ) . $this->_endTag );
		}

		if( isset( $attributes['hidden'] ) ) {
			unset( $attributes['hidden'] );
		}

		/**
		 * copy value from any other variable
		 */
		if (isset( $attributes['copyfrom'] )) {
			$specs['copyfrom'] = strtoupper( $attributes['copyfrom'] );

			if (strstr( $specs['copyfrom'], '.' )) {
				$specs['copyfrom']	= explode( '.', $specs['copyfrom'] );
				$specs['copyfrom'][0] = strtolower( $specs['copyfrom'][0] );
			}

			unset( $attributes['copyfrom'] );
		}

		if( isset( $attributes['modifier'] ) ) {
			$modifier = $attributes['modifier'];
			unset( $attributes['modifier'] );

			$type = isset( $attributes['modifiertype'] ) ? $attributes['modifiertype'] : 'auto';

			if( isset( $attributes['modifiertype'] ) )
				unset( $attributes['modifiertype'] );

			$specs['modifier'] = array( 'mod' => $modifier, 'type' => $type, 'params' => $attributes );
		}

		if (!empty( $specs )) {
			$this->_addToParentTemplate(
										'varspecs',
										$specs,
										$name
										);
		}
		return true;
	}


   /**
	* handle a comment
	*
	* @access	private
	* @param	array	attributes of the comment tag
	* @param	string	cdata between the tags (will be used as default)
	* @return	boolean	true on success
	*/
	function _handleComment( $attributes, $data )
	{
		$this->_addToParentTag( 'comments', $data );
	}

   /**
	* get the character data of the element
	*
	* @access	private
	* @return	string
	*/
	function _getCData()
	{
		if( $this->_depth == 0 ) {
			return	'';
		}
		return $this->_data[$this->_depth];
	}

   /**
	* add to a property of the parent template
	*
	* @access	private
	* @param	string	property to add to
	* @param	mixed	value to add
	* @param	string	key
	*/
	function _addToParentTemplate( $property, $value, $key = null )
	{
		$cnt = count( $this->_tmplStack );

		if ($cnt === 0) {
			return false;
		}

		$pos = $cnt - 1;
		while ($pos >= 0) {
			if ($this->_tmplStack[$pos]['type'] != 'tmpl') {
				$pos--;
				continue;
			}

			if ($key === null) {

				if (!in_array( $value, $this->_tmplStack[$pos][$property] )) {
					array_push( $this->_tmplStack[$pos][$property], $value );
				}
			} else {
				$this->_tmplStack[$pos][$property][$key] = $value;
			}

			return true;
		}

		return	false;
	}

   /**
	* get a property of the parent template
	*
	* @access	private
	* @param	string	property to add to
	* @return	mixed	value to add
	*/
	function _getFromParentTemplate( $property )
	{
		$cnt = count( $this->_tmplStack );

		if ($cnt === 0) {
			return false;
		}

		$pos = $cnt - 1;
		while ($pos >= 0) {
			if( $this->_tmplStack[$pos]['type'] != 'tmpl' ) {
				$pos--;
				continue;
			}

			if (isset( $this->_tmplStack[$pos][$property] )) {
				return $this->_tmplStack[$pos][$property];
			}

			return false;
		}
		return	false;
	}


   /**
	* add to a property of the parent tag
	*
	* @access	private
	* @param	string	property to add to
	* @param	mixed	value to add
	* @param	string	key
	*/
	function _addToParentTag( $property, $value, $key = null )
	{
		$cnt = count( $this->_tmplStack );

		if ($cnt === 0) {
			return false;
		}

		$pos = $cnt - 1;

		if ($key === null) {

			if (!in_array( $value, $this->_tmplStack[$pos][$property] )) {
				array_push( $this->_tmplStack[$pos][$property], $value );
			}
		} else {
			$this->_tmplStack[$pos][$property][$key] = $value;
		}

		return true;
	}

   /**
	* adjust whitespace in a CData block
	*
	* @access	private
	* @param	string		data
	* @param	string		behaviour
	* @return	string		data
	*/
	function _adjustWhitespace( $data, $behaviour )
	{
		switch( $behaviour ) {
			case 'trim':
				$data = str_replace( '\n', ' ', $data );
				$data = preg_replace( '/\s\s+/', ' ', $data );
				$data = trim( $data );
				break;
		}
		return	$data;
	}

   /**
	* inherit attributes from the parent template
	*
	* The following attributes are inherited automatically:
	* - whitespace
	* - unusedvars
	*
	* @access	private
	* @param	array	attributes
	* @param	array	attributes with inherited attributes
	* @return	array	new attribute collection
	*/
	function _inheritAttributes( $attributes )
	{
		if (!empty( $this->_inheritAtts )) {
			$parent = end( $this->_inheritAtts );
		} else {
			$parent = array(
								'whitespace' => $this->_defaultAtts['whitespace'],
								'unusedvars' => $this->_defaultAtts['unusedvars'],
								'autoclear'  => $this->_defaultAtts['autoclear']
							);
		}

		$attributes = array_merge( $parent, $attributes );

		return	$attributes;
	}

   /**
	* checks, whether the parent tag is of a certain type
	*
	* This is needed to ensure, that subtemplates are only
	* placed inside a template
	*
	* @access	private
	* @param	string	type (tmpl, sub, var, link)
	* @return	boolean
	*/
	function _parentTagIs( $type )
	{
		$parent	=	array_pop( $this->_tmplStack );
		if( $parent === null ) {
			return false;
		}
		array_push( $this->_tmplStack, $parent );

		if( $parent['type'] == $type ) {
			return true;
		}

		return false;
	}

   /**
	* get the current line number
	*
	* @access	private
	* @return	integer		line number
	*/
	function _getCurrentLine()
	{
		$line = count( explode( "\n", $this->_processedData ) );
		return $line;
	}

   /**
	* create an error message
	*
	* This method takes an error messages and appends the
	* current line number as well as a pointer to the input
	* (filename)
	*
	* @access	private
	* @param	string	base error message
	* @return	strin	error message
	*/
	function _createErrorMessage( $msg )
	{
		return sprintf( '%s in %s on line %d', $msg, $this->getCurrentInput(), $this->_getCurrentLine() );
	}

   /**
	* get the current input
	*
	* @access   public
	* @return   string
	*/
	function getCurrentInput()
	{
		return $this->_currentInput;
	}

   /**
	* tests whether the reader should maintain backwards compatibility
	*
	* If enabled, you can still use 'default', 'empty', 'odd' and 'even'
	* instead of '__default', '__empty', etc.
	*
	* This will be disabled by default in future versions.
	*
	* @access	public
	* @return	boolean
	*/
	function shouldMaintainBc()
	{
		if (!isset( $this->_options['maintainBc'] )) {
			return false;
		}
		return $this->_options['maintainBc'];
	}

   /**
	* returns, whether the reader currently is in use
	*
	* @access   public
	* @return   boolean
	*/
	function isInUse()
	{
		return $this->_inUse;
	}

   /**
	* get the template root for this reader
	*
	* @access  public
	* @return  string
	*/
	function getTemplateRoot()
	{
		if (!isset($this->_options['root'])) {
			return null;
		}
		if (isset($this->_options['root'][$this->_name])) {
			return $this->_options['root'][$this->_name];
		}
		if (isset($this->_options['root']['__default'])) {
			return $this->_options['root']['__default'];
		}
		return null;
	}
}
?>