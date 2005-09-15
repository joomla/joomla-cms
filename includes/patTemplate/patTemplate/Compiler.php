<?PHP
/**
 * Compiler for patTemplate
 *
 * $Id: Compiler.php 138 2005-09-12 10:37:53Z eddieajau $
 *
 * WARNING: This is still experimental!
 *
 * @package		patTemplate
 * @subpackage	Compiler
 * @author		Stephan Schmidt <schst@php.net>
 */

/**
 * Compiler for patTemplate
 *
 * $Id: Compiler.php 138 2005-09-12 10:37:53Z eddieajau $
 *
 * WARNING: This is still experimental!
 *
 * @package		patTemplate
 * @subpackage	Compiler
 * @author		Stephan Schmidt <schst@php.net>
 *
 * @todo		implement all template types
 * @todo		implement variable modifiers
 * @todo		implement getParsedTemplate
 * @todo		check for existing compiled template
 */
class patTemplate_Compiler extends patTemplate
{
   /**
	* list of all templates that already have been compiled
	*
	* @access	private
	* @var		array()
	*/
	var $_compiledTemplates = array();

   /**
	* file pointer to the compiled template
	*
	* @access	private
	* @var		resource
	*/
	var $_fp;

   /**
	* constructor
	*
	* Creates a new patTemplate Compiler
	*
	* @access	public
	* @param	string		type of the templates, either 'html' or 'tex'
	*/
	function patTemplate_Compiler( $type = 'html' )
	{
		$GLOBALS['patTemplate_Compiler']	=	&$this;
		patTemplate::patTemplate( $type );
	}

   /**
	* compile the currently loaded templates
	*
	* @access	public
	* @param	string	name of the input (filename, shm segment, etc.)
	*/
	function compile( $compileName = null )
	{
		$this->_varRegexp = '/'.$this->_startTag.'([^a-z:]+)'.$this->_endTag.'/U';
		$this->_depRegexp = '/'.$this->_startTag.'TMPL:([^a-z:]+)'.$this->_endTag.'/U';

		$compileFolder	=	$this->getOption( 'compileFolder' );
		$compileFile	=	sprintf( '%s/%s', $compileFolder, $compileName );

   		$this->_fp	=	fopen( $compileFile, 'w' );
		$this->_addToCode( '<?PHP' );
		$this->_addToCode( '/**' );
		$this->_addToCode( ' * compiled patTemplate file' );
		$this->_addToCode( ' *' );
		$this->_addToCode( ' * compiled on '. date( 'Y-m-d H:i:s' ) );
		$this->_addToCode( ' */' );
		$this->_addToCode( 'class compiledTemplate {' );

		foreach( $this->_templates as $template => $spec )
		{
			$this->compileTemplate( $template );
		}

		$this->_addToCode( '}' );
		$this->_addToCode( '?>' );
		fclose( $this->_fp );

		include_once $compileFile;
		return true;
	}

   /**
	* compile a template
	*
	* @access	public
	* @param	string	name of the template
	*/
	function compileTemplate( $template )
	{
		$name	=	strtolower( $template );

		if( !isset( $this->_templates[$template] ) )
		{
			return	patErrorManager::raiseWarning(
													PATTEMPLATE_WARNING_NO_TEMPLATE,
													"Template '$name' does not exist."
												);
		}


		/**
		 * check, if the template has been loaded
		 * and load it if necessary.
		 */
		if( $this->_templates[$template]['loaded'] !== true )
		{
			if( $this->_templates[$template]['attributes']['parse'] == 'on' )
			{
				$result = $this->readTemplatesFromInput( $this->_templates[$template]['attributes']['src'], $this->_templates[$template]['attributes']['reader'], null, $template );
			}
			else
			{
				$result = $this->loadTemplateFromInput( $this->_templates[$template]['attributes']['src'], $this->_templates[$template]['attributes']['reader'], $template );
			}
			if( patErrorManager::isError( $result ) )
			{
				return $result;
			}
		}

		$this->_addToCode( '' );
		$this->_addToCode( '/**' );
		$this->_addToCode( ' * Compiled version of '.$template );
		$this->_addToCode( ' *' );
		$this->_addToCode( ' * Template type is '.$this->_templates[$template]['attributes']['type'] );
		$this->_addToCode( ' */' );


		/**
		 * start the output
		 */
		$this->_addToCode( 'function '.$template.'()' );
		$this->_addToCode( '{' );
		$this->_addToCode( '$this->_prepareCompiledTemplate( "'.$template.'" );', 1 );
		$this->_addToCode( '$this->prepareTemplate( "'.$template.'" );', 1 );

		/**
		 * attributes
		 */
		$this->_addToCode( '$this->_templates["'.$template.'"]["attributes"] = unserialize( \''.serialize($this->_templates[$template]['attributes']).'\' );', 1, 'Read the attributes' );

		/**
		 * copyVars
		 */
		$this->_addToCode( '$this->_templates["'.$template.'"]["copyVars"] = unserialize( \''.serialize($this->_templates[$template]['copyVars']).'\' );', 1, 'Read the copyVars' );

		/**
		 * check visibility
		 */
		$this->_addToCode( 'if( $this->_templates["'.$template.'"]["attributes"]["visibility"] != "hidden" ) {', 1, 'Check, whether template is hidden' );

			/**
			 * autoloop the template
			 */
   			$this->_addToCode( '$this->_templates["'.$template.'"]["iteration"] = 0;', 2, 'Reset the iteration' );

			$this->_addToCode( '$loop = count( $this->_vars["'.$template.'"]["rows"] );', 2, 'Get the amount of loops' );
			$this->_addToCode( '$loop = max( $loop, 1 );', 2 );
			$this->_addToCode( '$this->_templates["'.$template.'"]["loop"] = $loop;', 2 );

			$this->_addToCode( 'for( $i = 0; $i < $loop; $i++ ) {', 2, 'Traverse all variables.' );

				/**
				 * fetch the variables
				 */
				$this->_addToCode( 'unset( $this->_templates["'.$template.'"]["vars"] );', 3 );
				$this->_addToCode( '$this->_fetchVariables("'.$template.'");', 3 );

				/**
				 * different templates have to be compiled differently
				 */
				switch( $this->_templates[$template]['attributes']['type'] )
				{
					/**
					 * modulo template
					 */
					case 'modulo':
						$this->_compileModuloTemplate( $template );
						break;

					/**
					 * simple condition template
					 */
					case 'simplecondition':
						$this->_compileSimpleConditionTemplate( $template );
						break;

					/**
					 * condition template
					 */
					case 'condition':
						$this->_compileConditionTemplate( $template );
						break;

					/**
					 * standard template
					 */
					default:
						$this->_compileStandardTemplate( $template );
						break;
				}
				$this->_addToCode( '$this->_templates["'.$template.'"]["iteration"]++;', 3 );

			$this->_addToCode( '}', 2 );

		$this->_addToCode( '}', 1 );
		$this->_addToCode( '}' );

		/**
		 * remember this template
		 */
		array_push( $this->_compiledTemplates, $template );
	}

   /**
	* compile a standard template
	*
	* @access	private
	* @param	string		name of the template
	*/
	function _compileStandardTemplate( $template )
	{
		$content = $this->_templateToPHP( $this->_templates[$template]['content'], $template );
		$this->_addToCode( $content );
		return true;
	}

   /**
	* compile a modulo template
	*
	* A modulo template will be compiled into a switch/case
	* statement.
	*
	* @access	private
	* @param	string		name of the template
	* @todo		check special conditions (__first, __last, __default)
	*/
	function _compileModuloTemplate( $template )
	{
		$this->_compileBuiltinConditions( $template );


		$this->_addToCode( 'if( !$_displayed ) {', 3, 'Builtin condition has been displayed?' );

		/**
		 * build switch statement
		 */
		$this->_addToCode( 'switch( ( $this->_templates["'.$template.'"]["iteration"] + 1 ) % '.$this->_templates[$template]['attributes']['modulo'].' ) {', 4 );

		foreach( $this->_templates[$template]['subtemplates'] as $condition => $spec )
		{
			$this->_addToCode( 'case "'.$condition.'":', 5 );
			$content = $this->_templateToPHP( $spec['data'], $template );
			$this->_addToCode( $content );
			$this->_addToCode( 'break;', 6 );
		}
		$this->_addToCode( '}', 4 );
		$this->_addToCode( '}', 3 );
		return true;
	}

   /**
	* compile a simpleCondition template
	*
	* A simpleCondition template will be compiled into an 'if'
	* statement.
	*
	* @access	private
	* @param	string		name of the template
	*/
	function _compileSimpleConditionTemplate( $template )
	{
		$conditions	=	array();
		foreach( $this->_templates[$template]['attributes']['requiredvars'] as $var )
		{
			array_push( $conditions, 'isset( $this->_templates["'.$template.'"]["vars"]["'.$var.'"] )' );
		}

		/**
		 * build switch statement
		 */
		$this->_addToCode( 'if( '.implode( ' && ', $conditions ).' ) {', 3, 'Check for required variables' );

		$content = $this->_templateToPHP( $this->_templates[$template]['content'], $template );
		$this->_addToCode( $content );
		$this->_addToCode( '}', 3 );
		return true;
	}

   /**
	* compile a condition template
	*
	* A condition template will be compiled into an 'switch/case'
	* statement.
	*
	* @access	private
	* @param	string		name of the template
	*/
	function _compileConditionTemplate( $template )
	{
		/**
		 * __first, __last
		 */
		$this->_compileBuiltinConditions( $template );

		$this->_addToCode( 'if( !$_displayed ) {', 3, 'Builtin condition has been displayed?' );

		/**
		 * build switch statement
		 */
		$this->_addToCode( 'switch( $this->_templates["'.$template.'"]["vars"]["'.$this->_templates[$template]["attributes"]["conditionvar"].'"] ) {', 4 );

		foreach( $this->_templates[$template]['subtemplates'] as $condition => $spec )
		{
			if( $condition == '__default' )
			{
				$this->_addToCode( 'default:', 5 );
			}
			else
			{
				$this->_addToCode( 'case "'.$condition.'":', 5 );
			}
			$content = $this->_templateToPHP( $spec['data'], $template );
			$this->_addToCode( $content );
			$this->_addToCode( 'break;', 6 );
		}
		$this->_addToCode( '}', 4 );
		$this->_addToCode( '}', 3 );
		return true;
	}

   /**
	* compile built-in conditions
	*
	* This will create the neccessary PHP code for:
	* - __first
	* - __last
	*
	* @access	private
	* @param	string	template name
	*/
	function _compileBuiltinConditions( $template )
	{
		$this->_addToCode( '$_displayed = false;', 3 );

		if( isset( $this->_templates[$template]['subtemplates']['__first'] ) )
		{
			$this->_addToCode( 'if( $this->_templates["'.$template.'"]["iteration"] == 0 ) {', 3, 'Check for first entry' );
			$content = $this->_templateToPHP( $this->_templates[$template]['subtemplates']['__first']['data'], $template );
			$this->_addToCode( $content );
			$this->_addToCode( '$_displayed = true;', 4 );
			$this->_addToCode( '}', 3 );
		}

		if( isset( $this->_templates[$template]['subtemplates']['__last'] ) )
		{
			$this->_addToCode( 'if( $this->_templates["'.$template.'"]["iteration"] == ($this->_templates["'.$template.'"]["loop"]-1) ) {', 3, 'Check for last entry' );
			$content = $this->_templateToPHP( $this->_templates[$template]['subtemplates']['__last']['data'], $template );
			$this->_addToCode( $content );
			$this->_addToCode( '$_displayed = true;', 4 );
			$this->_addToCode( '}', 3 );
		}
	}

   /**
	* build PHP code from a template
	*
	* This will replace the variables in a template with
	* PHP Code.
	*
	* @access	private
	* @param	string		template content
	* @param	string		name of the template
	* @return	string		PHP code
	*/
	function _templateToPHP( $content, $template )
	{
		$content = preg_replace( $this->_varRegexp, '<?PHP echo $this->_getVar( "'.$template.'", "$1"); ?>', $content  );
		$content = preg_replace( $this->_depRegexp, '<?PHP compiledTemplate::$1(); ?>', $content  );
		$content = '?>'.$content.'<?PHP';
		return $content;
	}


   /**
	* display the compiled template
	*
	* This is a replacement for patTemplate::displayParsedTemplate.
	*
	* @access	public
	* @param	string		name of the template to display
	*/
	function displayParsedTemplate( $name = null )
	{
		if( is_null( $name ) )
			$name = $this->_root;

		$name	=	strtolower( $name );

		if( !is_callable( 'compiledTemplate', $name ) )
		{
			die( 'Unknown template' );
		}

		compiledTemplate::$name();
	}

   /**
	* add a line to the compiled code
	*
	* @access	public
	* @param	string		line to add
	* @param	integer		indentation
	* @return	void
	*/
	function _addToCode( $line, $indent = 0, $comment = null )
	{
		if( !is_null( $comment ) )
		{
			fputs( $this->_fp, "\n" );
			if( $indent > 0 )
				fputs( $this->_fp, str_repeat( "\t", $indent ) );
			fputs( $this->_fp, "/* $comment */\n" );
		}
		if( $indent > 0 )
			fputs( $this->_fp, str_repeat( "\t", $indent ) );
		fputs( $this->_fp, $line."\n" );
	}

   /**
	* function, used by the compiler to get a value of a variable
	*
	* Checks, whether the value is locally or globally set
	*
	* @access	private
	* @param	string		template
	* @param	string		variable name
	*
	* @todo		check for 'unusedvars' attribute
	*/
	function _getVar( $template, $varname )
	{
		if( isset( $this->_templates[$template]['vars'][$varname] ) )
			return $this->_templates[$template]['vars'][$varname];

		if( isset( $this->_globals[$this->_startTag.$varname.$this->_endTag] ) )
			return $this->_globals[$this->_startTag.$varname.$this->_endTag];

		return '';
	}

   /**
	* prepare a template for the compiler
	*
	* @access	private
	* @param	string		template name
	*/
	function _prepareCompiledTemplate( $template )
	{
		$this->_templates[$template]	=	array(
													'attributes' => array(),
													'copyVars'   => array(),
												);
	}
}
?>