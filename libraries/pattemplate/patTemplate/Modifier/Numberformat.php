<?PHP
/**
 * patTemplate modfifier Numberformat
 *
 * $Id$
 *
 * @package		patTemplate
 * @subpackage	Modifiers
 * @author		Stephan Schmidt <schst@php.net>
 */

/**
 * patTemplate modfifier Numberformat
 *
 * formats dates and times according to a format string.
 *
 * Possible attributes are:
 * - decimals (int)
 * - point
 * - separator
 *
 * See the PHP documentation for number_format() for
 * more information.
 *
 * @package		patTemplate
 * @subpackage	Modifiers
 * @author		Stephan Schmidt <schst@php.net>
 * @link		http://www.php.net/manual/en/function.strftime.php
 */
class patTemplate_Modifier_Numberformat extends patTemplate_Modifier
{
    var $defaults = array(
                        'decimals'  => 2,
                        'point'     => '.',
                        'separator' => ','
                    );
   /**
	* modify the value
	*
	* @access	public
	* @param	string		value
	* @return	string		modified value
	*/
	function modify($value, $params = array())
	{
	    $params = array_merge($this->defaults, $params);
	    return @number_format($value, $params['decimals'], $params['point'], $params['separator']);
	}
}
?>