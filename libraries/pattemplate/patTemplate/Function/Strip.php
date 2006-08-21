<?PHP
/**
 * patTemplate function that strips whitespace from a text
 * block. This is an implementation of Smarty's strip function.
 *
 * $Id: Strip.php 205 2004-05-13 21:27:27Z schst $
 *
 * @package		patTemplate
 * @subpackage	Functions
 * @author		Stephan Schmidt <schst@php.net>
 */

/**
 * patTemplate function that strips whitespace from a text
 * block. This is an implementation of Smarty's strip function.
 *
 * $Id: Strip.php 205 2004-05-13 21:27:27Z schst $
 *
 * @package		patTemplate
 * @subpackage	Functions
 * @author		Stephan Schmidt <schst@php.net>
 */
class patTemplate_Function_Strip extends patTemplate_Function
{
   /**
	* name of the function
	* @access	private
	* @var		string
	*/
	var $_name	=	'Strip';

   /**
	* call the function
	*
	* @access	public
	* @param	array	parameters of the function (= attributes of the tag)
	* @param	string	content of the tag
	* @return	string	content to insert into the template
	*/
	function call( $params, $content )
	{
		return preg_replace( '/\s+/m', ' ', $content );
	}
}
?>