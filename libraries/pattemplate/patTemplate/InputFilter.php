<?PHP
/**
 * Base class for patTemplate input filter
 *
 * $Id: InputFilter.php 47 2005-09-15 02:55:27Z rhuk $
 *
 * An input filter is used to modify the stream
 * before it has been processed by patTemplate_Reader.
 *
 * @package		patTemplate
 * @subpackage	Filters
 * @author		Stephan Schmidt <schst@php.net>
 */

/**
 * Base class for patTemplate input filter
 *
 * $Id: InputFilter.php 47 2005-09-15 02:55:27Z rhuk $
 *
 * An input filter is used to modify the stream
 * before it has been processed by patTemplate_Reader.
 *
 * @abstract
 * @package		patTemplate
 * @subpackage	Filters
 * @author		Stephan Schmidt <schst@php.net>
 */
class patTemplate_InputFilter extends patTemplate_Module
{
   /**
	* apply the filter
	*
	* @access	public
	* @param	string		data
	* @return	string		filtered data
	*/
	function apply( $data )
	{
		return $data;
	}
}
?>