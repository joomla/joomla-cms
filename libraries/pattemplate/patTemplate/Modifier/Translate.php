<?PHP
/**
 * patTemplate modfifier Translate
 *
 * $Id: Translate.php 154 2005-09-18 02:03:25Z eddieajau $
 *
 * @package		patTemplate
 * @subpackage	Modifiers
 * @author		Andrew Eddie <eddie.andrew@gmail.com>
 */

/**
 * Implements the Joomla translation function on a var
 *
 * @package		patTemplate
 * @subpackage	Modifiers
 * @author		Andrew Eddie <eddie.andrew@gmail.com>
 */
class patTemplate_Modifier_Translate extends patTemplate_Modifier
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
		return JText::_( $value );
	}
}
?>