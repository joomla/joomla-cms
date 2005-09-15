<?PHP
/**
 * patTemplate reader that reads HTML_Template_IT files
 *
 * $Id: IT.php 138 2005-09-12 10:37:53Z eddieajau $
 *
 * @package		patTemplate
 * @subpackage	Readers
 * @author		Stephan Schmidt <schst@php.net>
 */

/**
 * patTemplate reader that reads HTML_Template_IT files
 *
 * @package		patTemplate
 * @subpackage	Readers
 * @author		Stephan Schmidt <schst@php.net>
 */
class patTemplate_Reader_IT extends patTemplate_Reader
{
   /**
	* reader name
	* @access	private
	* @var		string
	*/
	var	$_name	=	'IT';

   /**
	* files that have been used
	* @access	private
	* @var		array
	*/
	var	$_files	=	array();

   /**
	* parse templates from string
	*
	* @access	private
	* @param	string		string to parse
	* @return	array		templates
	*/
	function parseString( $string )
	{
		/**
		 * apply input filter before parsing
		 */
		$string = $this->_tmpl->applyInputFilters( $string );

		$this->_inheritAtts	=	array();
		$this->_elStack		=	array();
		$this->_data		=	array( '' );
		$this->_tmplStack	=	array();
		$this->_depth		=	0;
		$this->_templates	=	array();
		$this->_path		=	array();
		$this->_processedData	=	'';

		$this->_defaultAtts	=	$this->_tmpl->getDefaultAttributes();

		if( !isset( $this->_defaultAtts['autoload'] ) )
			$this->_defaultAtts['autoload']	=	'on';

		/**
		 * create a special root template
		 */
		$attributes		= $this->_rootAtts;
		$attributes['name']	= '__global';

		$rootTemplate	= $this->_initTemplate( $attributes );

		array_push( $this->_tmplStack, $rootTemplate );

		/**
		 *start parsing
		 */
		$patNamespace	=	strtolower( $this->_tmpl->getNamespace() );

		$regexp	=	'/(<!-- (BEGIN|END) ([a-zA-Z]+) -->)/m';

		$tokens	=	preg_split( $regexp, $string, -1, PREG_SPLIT_DELIM_CAPTURE );

		/**
		 * the first token is always character data
		 * Though it could just be empty
		 */
		if( $tokens[0] != '' )
			$this->_characterData( $tokens[0] );

		$cnt	=	count( $tokens );
		$i		=	1;
		// process all tokens
		while( $i < $cnt )
		{
			$fullTag	=	$tokens[$i++];
			$closing	=	strtoupper( $tokens[$i++] ) == 'END' ? true : false;
			$tmplName	=	$tokens[$i++];
			$namespace  =   $patNamespace;
			$tagname	=	'tmpl';
			$data		=	$tokens[$i++];

			/**
			 * is it a closing tag?
			 */
			if( $closing === true )
			{
				$result	=	$this->_endElement( $namespace, $tagname );
				if( patErrorManager::isError( $result ) )
				{
					return	$result;
				}
				$this->_characterData( $data );
				continue;
			}

			$attributes	=	array( 'name' => $tmplName );
			$result 	=	$this->_startElement( $namespace, $tagname, $attributes );
			if( patErrorManager::isError( $result ) )
			{
				return	$result;
			}

			$this->_characterData( $data );
		}

		$rootTemplate = array_pop( $this->_tmplStack );

		$this->_closeTemplate( $rootTemplate, $this->_data[0] );

		/**
		 * check for tags that are still open
		 */
		if( $this->_depth > 0 )
		{
			$el	=	array_pop( $this->_elStack );
			return patErrorManager::raiseError(
				PATTEMPLATE_READER_ERROR_NO_CLOSING_TAG,
				$this->_createErrorMessage( "No closing tag for {$el['ns']}:{$el['name']} found" )
			);
		}

		return	$this->_templates;
	}

   /**
	* read templates from any input
	*
	* @final
	* @access	public
	* @param	string	file to parse
	* @return	array	templates
	*/
	function readTemplates( $input )
	{
		$this->_currentInput = $input;
		$fullPath	=	$this->_resolveFullPath( $input );
		if( patErrorManager::isError( $fullPath ) )
			return $fullPath;
		$content	=	$this->_getFileContents( $fullPath );
		if( patErrorManager::isError( $content ) )
			return $content;

		$templates	=	$this->parseString( $content );

		return	$templates;
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
	* @return	string  template content
	*/
	function loadTemplate( $input )
	{
		$fullPath	=	$this->_resolveFullPath( $input );
		if( patErrorManager::isError( $fullPath ) )
			return $fullPath;
		return $this->_getFileContents( $fullPath );
	}

   /**
	* resolve path for a template
	*
	* @access	private
	* @param	string		filename
	* @return	string		full path
	*/
	function _resolveFullPath( $filename )
	{
		$baseDir  = $this->getTemplateRoot();
		$fullPath = $baseDir . '/' . $filename;
		return	$fullPath;
	}

   /**
	* get the contents of a file
	*
	* @access	private
	* @param	string		filename
	* @return	string		file contents
	*/
	function _getFileContents( $file )
	{
		if( !file_exists( $file ) || !is_readable( $file ) )
		{
			return patErrorManager::raiseError(
										PATTEMPLATE_READER_ERROR_NO_INPUT,
										"Could not load templates from $file."
										);
		}

		if( function_exists( 'file_get_contents' ) )
			$content	=	@file_get_contents( $file );
		else
			$content	=	implode( '', file( $file ) );

		/**
		 * store the file name
		 */
		array_push( $this->_files, $file );

		return	$content;
	}
}
?>