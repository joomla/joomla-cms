<?PHP
/**
* @version $Id: Sef.php 85 2005-09-15 23:12:03Z eddieajau $
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * JTemplate Translate function
 *
 * @package 	Joomla.Framework
 * @subpackage 	Template
 * @since		1.5
 */
class patTemplate_Function_Translate extends patTemplate_Function
{
   /**
	* name of the function
	* @access	private
	* @var		string
	*/
	var $_name	=	'Translate';

   /**
	* call the function
	*
	* @access	public
	* @param	array	parameters of the function (= attributes of the tag)
	* @param	string	content of the tag
	* @return	string	content to insert into the template
	* @author	Andrew Eddie
	* Function modifed for Joomla!
	*/
	function call( $params, $content )
	{
		$escape = isset( $params['escape'] ) ? $params['escape'] : '';


		// just use the Joomla translation tool
		if( count( $params ) > 0 && key_exists( 'key', $params ) ) {
			$text = JText::_( $params['key'] );
		} else {
			$text = JText::_( $content );
		}

		if ($escape == 'yes' || $escape == 'true') {
			$text = addslashes( $text );
		}
		return $text;
	}
}
?>