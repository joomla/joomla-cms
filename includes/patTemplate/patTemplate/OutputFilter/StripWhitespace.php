<?PHP
/**
 * patTemplate StripWhitespace output filter
 *
 * $Id$
 *
 * Will remove all whitespace and replace it with a single space.
 *
 * @package		patTemplate
 * @subpackage	Filters
 * @author		Stephan Schmidt <schst@php.net>
 */

/**
 * patTemplate StripWhitespace output filter
 *
 * $Id$
 *
 * Will remove all whitespace and replace it with a single space.
 *
 * @package		patTemplate
 * @subpackage	Filters
 * @author		Stephan Schmidt <schst@php.net>
 */
class patTemplate_OutputFilter_StripWhitespace extends patTemplate_OutputFilter
{
   /**
	* filter name
	*
	* @access	protected
	* @abstract
	* @var	string
	*/
	var	$_name	=	'StripWhitespace';

   /**
	* remove all whitespace from the output
	*
	* @access	public
	* @param	string		data
	* @return	string		data without whitespace
	*/
	function apply( $data )
	{
		$data = str_replace( "\n", ' ', $data );
		$data = preg_replace( '/\s\s+/', ' ', $data );

		return $data;
	}
}
?>