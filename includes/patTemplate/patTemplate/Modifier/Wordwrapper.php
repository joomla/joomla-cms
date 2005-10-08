<?PHP
/**
 * patTemplate modfifier Wordwrapper
 *
 * $Id$
 *
 * @package		patTemplate
 * @subpackage	Modifiers
 * @author		Stephan Schmidt <schst@php.net>
 */

/**
 * patTemplate modfifier Wordwrapper
 *
 * Wraps lines of long texts.
 *
 * Possible attributes are:
 * - width (integer)
 * - break (string)
 * - cut (yes|no)
 * - nl2br (yes|no)
 *
 * See the PHP documentation for wordwrap() for
 * more information.
 *
 * @package		patTemplate
 * @subpackage	Modifiers
 * @author		Stephan Schmidt <schst@php.net>
 * @link		http://www.php.net/manual/en/function.wordwrap.php
 */
class patTemplate_Modifier_Wordwrapper extends patTemplate_Modifier
{
   /**
	* modify the value
	*
	* @access	public
	* @param	string		value
	* @return	string		modified value
	*/
	function modify( $value, $params = array() )
	{
		/**
		 * width
		 */
		if( !isset( $params['width'] ) )
			$params['width']	=	72;
		settype( $params['width'], 'integer' );

		/**
		 * character used for linebreaks
		 */
		if( !isset( $params['break'] ) )
			$params['break']	=	"\n";

		/**
		 * cut at the specified width
		 */
		if( !isset( $params['cut'] ) )
			$params['cut']	=	'no';

		$params['cut'] = ($params['cut'] === 'yes') ? true : false;

		$value = wordwrap( $value, $params['width'], $params['break'], $params['cut'] );

		if( isset( $params['nl2br'] ) && $params['nl2br'] === 'yes' )
			$value	=	nl2br( $value );

		return $value;
	}
}
?>