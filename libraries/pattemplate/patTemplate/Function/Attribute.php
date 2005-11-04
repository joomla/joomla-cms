<?PHP
/**
 * patTemplate function to dynamically change the
 * value of _any_ attribute of the parent tag.
 *
 * $Id: Attribute.php 47 2005-09-15 02:55:27Z rhuk $
 *
 * @package		patTemplate
 * @subpackage	Functions
 * @author		Stephan Schmidt <schst@php.net>
 */

/**
 * patTemplate function to dynamically change the
 * value of _any_ attribute of the parent tag.
 *
 * Possible attributes:
 * - name => name of the attribute to change
 *
 * The enclosed data will be used as the value of the attribute.
 *
 * $Id: Attribute.php 47 2005-09-15 02:55:27Z rhuk $
 *
 * @package		patTemplate
 * @subpackage	Functions
 * @author		Stephan Schmidt <schst@php.net>
 */
class patTemplate_Function_Attribute extends patTemplate_Function
{
   /**
	* name of the function
	* @access	private
	* @var		string
	*/
	var $_name	=	'Attribute';

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
		if( isset( $params['name'] ) )
		{
			$this->_reader->_addToParentTag( 'attributes', $content, $params['name'] );
		}
		return '';
	}
}
?>