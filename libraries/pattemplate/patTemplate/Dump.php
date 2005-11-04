<?PHP
/**
 * Base class for patTemplate dumpers
 *
 * $Id: Dump.php 47 2005-09-15 02:55:27Z rhuk $
 *
 * The dump functionality is separated from the main class
 * for performance reasons.
 *
 * @package		patTemplate
 * @subpackage	Dump
 * @author		Stephan Schmidt <schst@php.net>
 */

/**
 * Base class for patTemplate dumpers
 *
 * The dump functionality is separated from the main class
 * for performance reasons.
 *
 * @abstract
 * @package		patTemplate
 * @subpackage	Dump
 * @author		Stephan Schmidt <schst@php.net>
 */
class patTemplate_Dump extends patTemplate_Module
{
   /**
	* reference to the patTemplate object that instantiated the module
	*
	* @access	protected
	* @var	object
	*/
	var	$_tmpl;

   /**
	* set a reference to the patTemplate object that instantiated the reader
	*
	* @access	public
	* @param	object		patTemplate object
	*/
	function setTemplateReference( &$tmpl )
	{
		$this->_tmpl		=	&$tmpl;
	}

   /**
	* display the header
	*
	* @access	public
	*/
	function displayHeader()
	{
	}

   /**
	* dump the global variables
	*
	* @access	public
	* @param	array		array containing all global variables
	* @abstract
	*/
	function dumpGlobals( $globals )
	{
	}

   /**
	* dump the templates
	*
	* This method has to be implemented in the dumpers.
	*
	* @access	public
	* @abstract
	* @param	array	templates
	* @param	array	variables
	*/
	function dumpTemplates( $templates, $vars )
	{
	}

   /**
	* display the footer
	*
	* @access	public
	*/
	function displayFooter()
	{
	}

   /**
 	* flatten the variables
	*
	* This will convert the variable definitions
	* to a one-dimensional array. If there are
	* rows defined, they will be converted to a string
	* where the values are seperated with commas.
	*
	* @access	private
	* @param	array		variable definitions
	* @return	array		flattened variables
	*/
	function _flattenVars( $vars )
	{
		$flatten	=	array();
		foreach( $vars['scalar'] as $var => $value )
		{
			$flatten[$var]	=	$value;
		}
		foreach( $vars['rows'] as $row )
		{
			foreach( $row as $var => $value )
			{
				if( !isset( $flatten[$var] ) || !is_array( $flatten[$var] ) )
					$flatten[$var]	=	array();
				array_push( $flatten[$var], $value );
			}
		}

		foreach( $flatten as $var => $value )
		{
			if( !is_array( $value ) )
				continue;

			$flatten[$var] = '['.count($value).' rows] ('.implode( ', ', $value ).')';
		}

		return $flatten;
	}

   /**
	* extract all variables from a template
	*
	* @access	private
	* @param	string		template content
	* @return	array		array containing all variables
	*/
	function _extractVars( $template )
	{
		$pattern = '/'.$this->_tmpl->getStartTag().'([^a-z]+)'.$this->_tmpl->getEndTag().'/U';

		$matches = array();

		$result = preg_match_all( $pattern, $template, $matches );
		if( $result == false )
			return array();

		$vars = array();
		foreach( $matches[1] as $var )
		{
			if( strncmp( $var, 'TMPL:', 5 ) === 0 )
				continue;
			array_push( $vars, $var );
		}
		return array_unique( $vars );
	}
}
?>